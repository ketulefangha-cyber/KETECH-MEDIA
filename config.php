<?php
/**
 * Shipping API Configuration
 * Add your API keys here from the respective shipping companies
 */

// DHL API Configuration
define('DHL_API_KEY', 'YOUR_DHL_API_KEY_HERE');
define('DHL_ACCOUNT_NUMBER', 'YOUR_DHL_ACCOUNT_NUMBER');
define('DHL_API_URL', 'https://api.dhl.com/track/v1/tracking');

// FedEx API Configuration
define('FEDEX_API_KEY', 'YOUR_FEDEX_API_KEY_HERE');
define('FEDEX_SECRET_KEY', 'YOUR_FEDEX_SECRET_KEY_HERE');
define('FEDEX_ACCOUNT_NUMBER', 'YOUR_FEDEX_ACCOUNT_NUMBER');
define('FEDEX_API_URL', 'https://apis.fedex.com/track/v1/tracking');

// UPS API Configuration
define('UPS_ACCESS_KEY', 'YOUR_UPS_ACCESS_KEY');
define('UPS_USERNAME', 'YOUR_UPS_USERNAME');
define('UPS_PASSWORD', 'YOUR_UPS_PASSWORD');
define('UPS_API_URL', 'https://onlinetools.ups.com/track/v1/details/tracking');

// 4PX Express API Configuration
define('FOURPX_API_KEY', 'YOUR_4PX_API_KEY_HERE');
define('FOURPX_SECRET_KEY', 'YOUR_4PX_SECRET_KEY_HERE');
define('FOURPX_API_URL', 'https://api.4px.com/track');

// Cainiao (Alibaba) API Configuration
define('CAINIAO_APP_KEY', 'YOUR_CAINIAO_APP_KEY_HERE');
define('CAINIAO_SECRET_KEY', 'YOUR_CAINIAO_SECRET_KEY_HERE');
define('CAINIAO_API_URL', 'https://global.cainiao.com/smartlogitics/track/query');

// Global Configuration
define('ENABLE_MOCK_DATA', true); // Set to false once you add real API keys
define('LOG_TRACKING_REQUESTS', true);
define('LOG_FILE', '/logs/tracking_log.txt');

?>
