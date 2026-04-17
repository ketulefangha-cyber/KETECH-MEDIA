<?php
/**
 * Contact Form Email Handler
 * Receives form data and sends email notifications
 */

header('Content-Type: application/json');

// Include configuration
require_once '../config.php';

// Handle only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON data from request
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$name = isset($input['name']) ? trim($input['name']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';
$phone = isset($input['phone']) ? trim($input['phone']) : '';
$service = isset($input['service']) ? trim($input['service']) : '';
$message = isset($input['message']) ? trim($input['message']) : '';

// Validation
if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Service interest mapping
$serviceMap = [
    'web' => 'Website Development',
    'mobile' => 'Mobile App Development',
    'branding' => 'Branding & Design',
    'shipping' => 'Shipment Tracking',
    'custom' => 'Custom Software',
    'other' => 'Other'
];

$serviceInterest = isset($serviceMap[$service]) ? $serviceMap[$service] : 'Not specified';

// Prepare email content
$emailSubject = "New Contact Form Submission from {$name}";

$emailBody = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #007bff; }
        .divider { border-top: 1px solid #ddd; margin: 20px 0; }
    </style>
</head>
<body>
    <div class=\"container\">
        <div class=\"header\">
            <h2>New Contact Form Submission</h2>
        </div>
        <div class=\"content\">
            <div class=\"field\">
                <span class=\"label\">Name:</span> {$name}
            </div>
            <div class=\"field\">
                <span class=\"label\">Email:</span> {$email}
            </div>
            <div class=\"field\">
                <span class=\"label\">Phone:</span> " . (!empty($phone) ? $phone : 'Not provided') . "
            </div>
            <div class=\"field\">
                <span class=\"label\">Service Interest:</span> {$serviceInterest}
            </div>
            <div class=\"divider\"></div>
            <div class=\"field\">
                <span class=\"label\">Message:</span><br>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
            </div>
            <div class=\"divider\"></div>
            <p style=\"font-size: 12px; color: #666;\">
                Submitted on: " . date('Y-m-d H:i:s') . "<br>
                From: {$_SERVER['REMOTE_ADDR']}
            </p>
        </div>
    </div>
</body>
</html>
";

// Prepare headers
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: " . SENDER_NAME . " <" . SENDER_EMAIL . ">\r\n";
$headers .= "Reply-To: {$email}\r\n";

// Send email to your inbox
$toEmail = RECEIVE_EMAIL;
$mailSuccess = mail($toEmail, $emailSubject, $emailBody, $headers);

if ($mailSuccess) {
    // Optionally send confirmation email to user
    $confirmationSubject = "We received your message - KETECH MEDIA";
    $confirmationBody = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
    </style>
</head>
<body>
    <div class=\"container\">
        <div class=\"header\">
            <h2>Thank You for Contacting KETECH MEDIA</h2>
        </div>
        <div class=\"content\">
            <p>Hi {$name},</p>
            <p>We have received your message and will get back to you as soon as possible.</p>
            <p><strong>Service Interest:</strong> {$serviceInterest}</p>
            <p>In the meantime, feel free to reach out to us via:</p>
            <ul>
                <li>📧 Email: ketechmedia@hotmail.com</li>
                <li>📞 Phone: +237 679 673 906 | +237 670 826 673</li>
                <li>💬 WhatsApp: +237 670 826 673</li>
            </ul>
            <p>Best regards,<br><strong>KETECH MEDIA Team</strong></p>
        </div>
    </div>
</body>
</html>
";

    $confirmationHeaders = "MIME-Version: 1.0\r\n";
    $confirmationHeaders .= "Content-type: text/html; charset=UTF-8\r\n";
    $confirmationHeaders .= "From: " . SENDER_NAME . " <" . SENDER_EMAIL . ">\r\n";
    
    mail($email, $confirmationSubject, $confirmationBody, $confirmationHeaders);

    // Log the submission
    logSubmission($name, $email, $phone, $service, $message);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Your message has been sent successfully! We will contact you soon.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was an error sending your message. Please try again.'
    ]);
}

/**
 * Log contact form submission
 */
function logSubmission($name, $email, $phone, $service, $message) {
    $logFile = dirname(__FILE__) . '/../logs/contact_submissions.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] Name: {$name} | Email: {$email} | Phone: {$phone} | Service: {$service}\n";
    
    // Create logs directory if it doesn't exist
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    error_log($logEntry, 3, $logFile);
}
?>
