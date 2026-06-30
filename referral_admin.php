<?php
header('Content-Type: application/json');
@include_once __DIR__ . '/../config.php';

// simple admin key check
$admin_key = isset($_REQUEST['admin_key']) ? $_REQUEST['admin_key'] : '';
if (!defined('REFERRAL_ADMIN_KEY') || $admin_key !== REFERRAL_ADMIN_KEY) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$logFile = __DIR__ . '/../logs/referrals.json';

// load records
$records = [];
if (file_exists($logFile)) {
    $decoded = json_decode(file_get_contents($logFile), true);
    if (is_array($decoded)) $records = $decoded;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    echo json_encode(['success' => true, 'records' => $records]);
    exit;
}

// POST actions: update or delete
$action = isset($_POST['action']) ? $_POST['action'] : '';
$id = isset($_POST['id']) ? $_POST['id'] : '';

if (!$action || !$id) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$changed = false;
foreach ($records as &$r) {
    if ($r['id'] === $id) {
        if ($action === 'update') {
            $newStatus = isset($_POST['status']) ? $_POST['status'] : $r['status'];
            $r['status'] = $newStatus;
            $changed = true;
        } elseif ($action === 'delete') {
            $r['_delete'] = true;
            $changed = true;
        }
        break;
    }
}
if ($changed) {
    // filter deleted
    $records = array_values(array_filter($records, function($x){ return empty($x['_delete']); }));
    // save
    $fp = fopen($logFile, 'c+');
    if ($fp && flock($fp, LOCK_EX)) {
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($records, JSON_PRETTY_PRINT));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'No changes made']);
exit;

?>
