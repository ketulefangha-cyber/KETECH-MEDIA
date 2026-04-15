<?php
/**
 * Unified Shipping Tracking API
 * Handles requests from multiple shipping carriers
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Get tracking number from request
$trackingNumber = isset($_GET['tracking']) ? sanitize_input($_GET['tracking']) : '';
$carrier = isset($_GET['carrier']) ? sanitize_input($_GET['carrier']) : 'auto';

if (empty($trackingNumber)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tracking number is required']);
    exit;
}

// Route to appropriate carrier
try {
    // If ENABLE_MOCK_DATA is true, use mock data for testing
    if (ENABLE_MOCK_DATA) {
        $result = getMockTrackingData($trackingNumber);
    } else {
        // Detect carrier if not specified
        if ($carrier === 'auto') {
            $carrier = detectCarrier($trackingNumber);
        }
        
        // Get real tracking data based on carrier
        switch (strtolower($carrier)) {
            case 'dhl':
                $result = trackWithDHL($trackingNumber);
                break;
            case 'fedex':
                $result = trackWithFedEx($trackingNumber);
                break;
            case 'ups':
                $result = trackWithUPS($trackingNumber);
                break;
            case '4px':
                $result = trackWith4PX($trackingNumber);
                break;
            case 'cainiao':
                $result = trackWithCainiao($trackingNumber);
                break;
            default:
                $result = getMockTrackingData($trackingNumber);
        }
    }
    
    // Log the request
    if (LOG_TRACKING_REQUESTS) {
        logTrackingRequest($trackingNumber, $carrier, $result);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error retrieving tracking information: ' . $e->getMessage()]);
}

/**
 * DHL Tracking
 */
function trackWithDHL($trackingNumber) {
    $apiKey = DHL_API_KEY;
    $url = DHL_API_URL . '?trackingNumbers=' . urlencode($trackingNumber) . '&apiKey=' . $apiKey;
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $apiKey)
    ));
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($httpCode !== 200) {
        throw new Exception('DHL API returned status code ' . $httpCode);
    }
    
    $data = json_decode($response, true);
    
    // Transform DHL response to standard format
    return formatDHLResponse($data);
}

/**
 * FedEx Tracking
 */
function trackWithFedEx($trackingNumber) {
    $token = getFedExAuthToken();
    
    $url = FEDEX_API_URL;
    
    $payload = json_encode([
        'includeDetailedScans' => true,
        'trackingInfo' => [
            [
                'trackingNumberInfo' => [
                    'trackingNumber' => $trackingNumber
                ]
            ]
        ]
    ]);
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ),
        CURLOPT_POSTFIELDS => $payload
    ));
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($httpCode !== 200) {
        throw new Exception('FedEx API returned status code ' . $httpCode);
    }
    
    $data = json_decode($response, true);
    
    // Transform FedEx response to standard format
    return formatFedExResponse($data);
}

/**
 * UPS Tracking
 */
function trackWithUPS($trackingNumber) {
    $url = UPS_API_URL;
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url . '?trackNumbers=' . urlencode($trackingNumber),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => array(
            'AccessLicenseNumber: ' . UPS_ACCESS_KEY,
            'Username: ' . UPS_USERNAME,
            'Password: ' . UPS_PASSWORD
        )
    ));
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($httpCode !== 200) {
        throw new Exception('UPS API returned status code ' . $httpCode);
    }
    
    $data = json_decode($response, true);
    
    // Transform UPS response to standard format
    return formatUPSResponse($data);
}

/**
 * 4PX Express Tracking
 */
function trackWith4PX($trackingNumber) {
    $url = FOURPX_API_URL;
    
    $payload = json_encode([
        'number' => $trackingNumber,
        'apiKey' => FOURPX_API_KEY
    ]);
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
        CURLOPT_POSTFIELDS => $payload
    ));
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($httpCode !== 200) {
        throw new Exception('4PX API returned status code ' . $httpCode);
    }
    
    $data = json_decode($response, true);
    
    // Transform 4PX response to standard format
    return format4PXResponse($data);
}

/**
 * Cainiao (Alibaba) Tracking
 */
function trackWithCainiao($trackingNumber) {
    $url = CAINIAO_API_URL;
    
    $timestamp = time() * 1000;
    $sign = generateCainiaoSignature($trackingNumber, $timestamp);
    
    $payload = json_encode([
        'waybillCode' => $trackingNumber,
        'appKey' => CAINIAO_APP_KEY,
        'timestamp' => $timestamp,
        'sign' => $sign
    ]);
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
        CURLOPT_POSTFIELDS => $payload
    ));
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($httpCode !== 200) {
        throw new Exception('Cainiao API returned status code ' . $httpCode);
    }
    
    $data = json_decode($response, true);
    
    // Transform Cainiao response to standard format
    return formatCainiaoResponse($data);
}

/**
 * Detect carrier from tracking number format
 */
function detectCarrier($trackingNumber) {
    // DHL: 10 numeric digits
    if (preg_match('/^\d{10}$/', $trackingNumber)) {
        return 'dhl';
    }
    
    // FedEx: 12, 14, or 15 digits
    if (preg_match('/^\d{12}$|^\d{14}$|^\d{15}$/', $trackingNumber)) {
        return 'fedex';
    }
    
    // UPS: 1Z followed by 16 characters
    if (preg_match('/^1Z[A-Z0-9]{16}$/', $trackingNumber)) {
        return 'ups';
    }
    
    // 4PX: Usually contains specific patterns
    if (preg_match('/^4PX/', $trackingNumber)) {
        return '4px';
    }
    
    // Cainiao: Usually numeric
    if (preg_match('/^\d{13,}$/', $trackingNumber)) {
        return 'cainiao';
    }
    
    // Default to mock data
    return 'auto';
}

/**
 * Format DHL response to standard format
 */
function formatDHLResponse($data) {
    // Transform API response to unified format
    if (isset($data['shipments']) && count($data['shipments']) > 0) {
        $shipment = $data['shipments'][0];
        
        return [
            'success' => true,
            'carrier' => 'DHL',
            'trackingID' => $shipment['id'] ?? '',
            'status' => $shipment['status'] ?? 'Unknown',
            'location' => $shipment['origin']['address']['city'] ?? '',
            'delivery' => $shipment['expectedDelivery'] ?? '',
            'timeline' => formatTimeline($shipment['events'] ?? [])
        ];
    }
    
    return getMockTrackingData($_GET['tracking'] ?? '');
}

/**
 * Format FedEx response to standard format
 */
function formatFedExResponse($data) {
    if (isset($data['output']['completeTrackResults']) && count($data['output']['completeTrackResults']) > 0) {
        $result = $data['output']['completeTrackResults'][0];
        
        return [
            'success' => true,
            'carrier' => 'FedEx',
            'trackingID' => $result['trackingNumber'] ?? '',
            'status' => $result['summary']['status'] ?? 'Unknown',
            'location' => $result['summary']['lastLocation']['city'] ?? '',
            'delivery' => $result['summary']['estimatedDeliveryTimestamp'] ?? '',
            'timeline' => formatFedExTimeline($result['scanEvents'] ?? [])
        ];
    }
    
    return getMockTrackingData($_GET['tracking'] ?? '');
}

/**
 * Format UPS response to standard format
 */
function formatUPSResponse($data) {
    if (isset($data['trackResponse']['shipment']) && count($data['trackResponse']['shipment']) > 0) {
        $shipment = $data['trackResponse']['shipment'][0];
        
        return [
            'success' => true,
            'carrier' => 'UPS',
            'trackingID' => $shipment['shipper']['number'] ?? '',
            'status' => $shipment['currentStatus']['status'] ?? 'Unknown',
            'location' => $shipment['currentStatus']['location']['city'] ?? '',
            'delivery' => $shipment['deliveryDetail']['date'] ?? '',
            'timeline' => formatUPSTimeline($shipment['package'][0]['activity'] ?? [])
        ];
    }
    
    return getMockTrackingData($_GET['tracking'] ?? '');
}

/**
 * Format 4PX response to standard format
 */
function format4PXResponse($data) {
    if ($data['status'] === 'ok') {
        $track = $data['data']['track'] ?? [];
        
        return [
            'success' => true,
            'carrier' => '4PX Express',
            'trackingID' => $track['number'] ?? '',
            'status' => $track['status'] ?? 'Unknown',
            'location' => $track['lastLocation'] ?? '',
            'delivery' => $track['estimatedDelivery'] ?? '',
            'timeline' => format4PXTimeline($track['events'] ?? [])
        ];
    }
    
    return getMockTrackingData($_GET['tracking'] ?? '');
}

/**
 * Format Cainiao response to standard format
 */
function formatCainiaoResponse($data) {
    if ($data['success'] === true) {
        $logistics = $data['data']['waybillLogisticDetailsDto'] ?? [];
        
        return [
            'success' => true,
            'carrier' => 'Cainiao',
            'trackingID' => $logistics['waybillCode'] ?? '',
            'status' => $logistics['logisticStatus'] ?? 'Unknown',
            'location' => $logistics['currentLocation'] ?? '',
            'delivery' => $logistics['estimatedDeliveryTime'] ?? '',
            'timeline' => formatCainiaoTimeline($logistics['traces'] ?? [])
        ];
    }
    
    return getMockTrackingData($_GET['tracking'] ?? '');
}

/**
 * Mock tracking data for testing
 */
function getMockTrackingData($trackingNumber) {
    $mockDatabase = [
        'KETECH-CN-12345' => [
            'success' => true,
            'carrier' => 'DHL',
            'trackingID' => 'KETECH-CN-12345',
            'status' => 'In Transit',
            'location' => 'Shanghai Port, China',
            'delivery' => '2026-04-25',
            'timeline' => [
                ['status' => 'Order Received', 'location' => 'Shanghai, China', 'date' => '2026-04-05', 'completed' => true],
                ['status' => 'Customs Cleared', 'location' => 'Shanghai Port', 'date' => '2026-04-08', 'completed' => true],
                ['status' => 'Shipped from Port', 'location' => 'Shanghai, China', 'date' => '2026-04-10', 'completed' => true],
                ['status' => 'In Transit', 'location' => 'At Sea', 'date' => '2026-04-15', 'completed' => true],
                ['status' => 'Port Arrival', 'location' => 'Douala Port, Cameroon', 'date' => '2026-04-20', 'completed' => false],
                ['status' => 'Delivery', 'location' => 'Destination', 'date' => '2026-04-25', 'completed' => false]
            ]
        ],
        'KETECH-CN-54321' => [
            'success' => true,
            'carrier' => 'FedEx',
            'trackingID' => 'KETECH-CN-54321',
            'status' => 'Delivered',
            'location' => 'Recipient Location',
            'delivery' => '2026-04-10',
            'timeline' => [
                ['status' => 'Order Received', 'location' => 'Shanghai, China', 'date' => '2026-03-25', 'completed' => true],
                ['status' => 'Shipped from Port', 'location' => 'Shanghai, China', 'date' => '2026-03-30', 'completed' => true],
                ['status' => 'In Transit', 'location' => 'At Sea', 'date' => '2026-04-05', 'completed' => true],
                ['status' => 'Port Arrival', 'location' => 'Douala Port', 'date' => '2026-04-08', 'completed' => true],
                ['status' => 'Delivery', 'location' => 'Recipient Location', 'date' => '2026-04-10', 'completed' => true]
            ]
        ],
        'KETECH-CN-99999' => [
            'success' => true,
            'carrier' => 'UPS',
            'trackingID' => 'KETECH-CN-99999',
            'status' => 'Processing',
            'location' => 'Shanghai Warehouse',
            'delivery' => '2026-05-05',
            'timeline' => [
                ['status' => 'Order Received', 'location' => 'Shanghai, China', 'date' => '2026-04-12', 'completed' => true],
                ['status' => 'Processing', 'location' => 'Shanghai Warehouse', 'date' => '2026-04-18', 'completed' => false],
                ['status' => 'Shipped from Port', 'location' => 'Shanghai, China', 'date' => '2026-04-20', 'completed' => false],
                ['status' => 'In Transit', 'location' => 'At Sea', 'date' => '2026-04-28', 'completed' => false],
                ['status' => 'Delivery', 'location' => 'Destination', 'date' => '2026-05-05', 'completed' => false]
            ]
        ]
    ];
    
    return $mockDatabase[$trackingNumber] ?? [
        'success' => false,
        'error' => 'Tracking number not found'
    ];
}

/**
 * Helper functions for formatting timelines
 */
function formatTimeline($events) {
    $timeline = [];
    foreach ($events as $event) {
        $timeline[] = [
            'status' => $event['status'] ?? 'Unknown',
            'location' => $event['location'] ?? '',
            'date' => $event['timestamp'] ?? '',
            'completed' => true
        ];
    }
    return $timeline;
}

function formatFedExTimeline($events) {
    // Similar transformation for FedEx
    return formatTimeline($events);
}

function formatUPSTimeline($events) {
    // Similar transformation for UPS
    return formatTimeline($events);
}

function format4PXTimeline($events) {
    // Similar transformation for 4PX
    return formatTimeline($events);
}

function formatCainiaoTimeline($traces) {
    // Similar transformation for Cainiao
    return formatTimeline($traces);
}

/**
 * Utility functions
 */
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function logTrackingRequest($trackingNumber, $carrier, $result) {
    $logMessage = date('Y-m-d H:i:s') . " | Tracking: $trackingNumber | Carrier: $carrier | Status: " . ($result['success'] ? 'Success' : 'Failed') . "\n";
    
    if (file_exists(dirname(__FILE__) . '/logs')) {
        file_put_contents(dirname(__FILE__) . '/logs/tracking_log.txt', $logMessage, FILE_APPEND);
    }
}

function getFedExAuthToken() {
    // This should be implemented according to FedEx OAuth 2.0 flow
    // For now, returning a placeholder
    return 'YOUR_FEDEX_AUTH_TOKEN';
}

function generateCainiaoSignature($trackingNumber, $timestamp) {
    // Implement Cainiao signature generation
    return hash_hmac('md5', $trackingNumber . $timestamp, CAINIAO_SECRET_KEY);
}

?>
