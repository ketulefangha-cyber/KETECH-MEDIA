<?php
/**
 * Cainiao Webhook Handler
 * Receives real-time tracking updates from Cainiao
 * Configure this URL in Cainiao dashboard: https://yoursite.com/api/cainiao_webhook.php
 */

require_once '../config.php';

// Verify webhook signature (Security)
function verifyCainiaoSignature($data, $signature) {
    $sign = hash_hmac('md5', $data, CAINIAO_SECRET_KEY);
    return $sign === $signature;
}

// Handle webhook request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents('php://input');
    $headers = getallheaders();
    $signature = isset($headers['X-Cainiao-Signature']) ? $headers['X-Cainiao-Signature'] : '';
    
    // Verify signature
    if (!verifyCainiaoSignature($rawData, $signature)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid signature']);
        exit;
    }
    
    // Parse webhook data
    $data = json_decode($rawData, true);
    
    // Process tracking update
    if ($data['type'] === 'track_update') {
        $trackingNumber = $data['waybillCode'];
        $status = $data['status'];
        $location = $data['location'];
        $timestamp = $data['timestamp'];
        
        // Store update in database or cache
        storeTrackingUpdate($trackingNumber, $status, $location, $timestamp);
        
        // Log the update
        logWebhookUpdate($trackingNumber, $status);
        
        // Send success response
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Webhook processed']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}

function storeTrackingUpdate($trackingNumber, $status, $location, $timestamp) {
    // Example: Store in JSON file (for simple setup)
    // For production, use a database
    
    $updateFile = dirname(__FILE__) . '/../data/tracking_updates.json';
    $updates = file_exists($updateFile) ? json_decode(file_get_contents($updateFile), true) : [];
    
    if (!isset($updates[$trackingNumber])) {
        $updates[$trackingNumber] = [];
    }
    
    $updates[$trackingNumber][] = [
        'status' => $status,
        'location' => $location,
        'timestamp' => $timestamp,
        'received_at' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents($updateFile, json_encode($updates, JSON_PRETTY_PRINT));
}

function logWebhookUpdate($trackingNumber, $status) {
    $logMessage = date('Y-m-d H:i:s') . " | Webhook Update | Tracking: $trackingNumber | Status: $status\n";
    file_put_contents(dirname(__FILE__) . '/../logs/webhook_log.txt', $logMessage, FILE_APPEND);
}

?>
