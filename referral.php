<?php
header('Content-Type: application/json');
// load config for email settings and admin key
@include_once __DIR__ . '/../config.php';
@include_once __DIR__ . '/_mailer.php';

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Collect and sanitize input
$referrer_name = isset($_POST['referrer_name']) ? trim($_POST['referrer_name']) : '';
$referrer_email = isset($_POST['referrer_email']) ? filter_var(trim($_POST['referrer_email']), FILTER_VALIDATE_EMAIL) : false;
$referee_name = isset($_POST['referee_name']) ? trim($_POST['referee_name']) : '';
$referee_email = isset($_POST['referee_email']) ? filter_var(trim($_POST['referee_email']), FILTER_VALIDATE_EMAIL) : false;
// basic spam/honeypot checks
$hp = isset($_POST['hp']) ? trim($_POST['hp']) : '';
$ts = isset($_POST['ts']) ? intval($_POST['ts']) : 0;
$ip = $_SERVER['REMOTE_ADDR'];

// rate limiting: max 5 submissions per hour per IP
$rateFile = __DIR__ . '/../logs/referral_rate.json';
$rateData = [];
if (file_exists($rateFile)) {
    $rc = json_decode(file_get_contents($rateFile), true);
    if (is_array($rc)) $rateData = $rc;
}
$now = time();
$window = 3600; // 1 hour
$maxPerWindow = 5;
$timestamps = isset($rateData[$ip]) ? array_filter($rateData[$ip], function($t) use ($now, $window){ return ($now - $t) <= $window; }) : [];
if (count($timestamps) >= $maxPerWindow) {
    echo json_encode(['success' => false, 'message' => 'Rate limit exceeded. Please try later.']);
    exit;
}
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// reject if honeypot filled
if (!empty($hp)) {
    echo json_encode(['success' => false, 'message' => 'Spam detected']);
    exit;
}

// require at least 3 seconds between form render and submit
if ($ts && (time() - intval($ts / 1000) < 3)) {
    echo json_encode(['success' => false, 'message' => 'Form submitted too quickly']);
    exit;
}

// reCAPTCHA verification (if configured)
if (defined('RECAPTCHA_SECRET') && RECAPTCHA_SECRET) {
    $token = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
    if (!$token) {
        echo json_encode(['success' => false, 'message' => 'reCAPTCHA required']);
        exit;
    }
    $verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode(RECAPTCHA_SECRET) . '&response=' . urlencode($token));
    $resp = json_decode($verify, true);
    if (empty($resp['success'])) {
        echo json_encode(['success' => false, 'message' => 'reCAPTCHA verification failed']);
        exit;
    }
}

if (!$referrer_name || !$referrer_email || !$referee_name || !$referee_email) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Prepare record
$record = [
    'id' => uniqid('ref_', true),
    'referrer_name' => htmlspecialchars($referrer_name, ENT_QUOTES, 'UTF-8'),
    'referrer_email' => $referrer_email,
    'referee_name' => htmlspecialchars($referee_name, ENT_QUOTES, 'UTF-8'),
    'referee_email' => $referee_email,
    'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
    'created_at' => date('c'),
    'status' => 'pending',
    // small referral code to show user
    'referral_code' => strtoupper(substr(md5(uniqid()), 0, 8))
];

$logDir = __DIR__ . '/../logs';
$logFile = $logDir . '/referrals.json';

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Read existing
$records = [];
if (file_exists($logFile)) {
    $contents = file_get_contents($logFile);
    $decoded = json_decode($contents, true);
    if (is_array($decoded)) $records = $decoded;
}

$records[] = $record;

// Save with locking
$saved = false;
$fp = fopen($logFile, 'c+');
if ($fp) {
    if (flock($fp, LOCK_EX)) {
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($records, JSON_PRETTY_PRINT));
        fflush($fp);
        flock($fp, LOCK_UN);
        $saved = true;
    }
    fclose($fp);
}

if ($saved) {
    // send notification email (best-effort)
    try {
        $to = defined('RECEIVE_EMAIL') ? RECEIVE_EMAIL : (defined('SENDER_EMAIL') ? SENDER_EMAIL : null);
        if ($to) {
            $subject = 'New Referral submitted - ' . $record['referrer_name'];
            $body = "Referral ID: {$record['id']}\n";
            $body .= "Referrer: {$record['referrer_name']} <{$record['referrer_email']}>\n";
            $body .= "Referee: {$record['referee_name']} <{$record['referee_email']}>\n";
            $body .= "Message: {$record['message']}\n";
            $headers = "From: " . (defined('SENDER_NAME') ? SENDER_NAME : 'Website') . " <" . (defined('SENDER_EMAIL') ? SENDER_EMAIL : '') . ">\r\n";
            // use SMTP helper if enabled
            if (defined('SMTP_ENABLED') && SMTP_ENABLED) {
                smtp_mail_send($to, $subject, $body);
            } else {
                @mail($to, $subject, $body, $headers);
            }
        }
    } catch (Exception $e) {
        // ignore email errors
    }
    // update rate file
    $timestamps[] = $now;
    $rateData[$ip] = $timestamps;
    file_put_contents($rateFile, json_encode($rateData, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'message' => 'Referral recorded', 'referral_code' => $record['referral_code']]);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Unable to save referral']);
    exit;
}

?>
