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

// Email Configuration
define('SENDER_EMAIL', 'ketechmedia@hotmail.com');
define('SENDER_NAME', 'KETECH MEDIA');
define('RECEIVE_EMAIL', 'ketechmedia@hotmail.com'); // Where contact form emails go
define('SMTP_ENABLED', false); // Set to true if you want to use SMTP instead of mail()

// SMTP Configuration (optional - only needed if SMTP_ENABLED is true)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');

// Global Configuration
define('ENABLE_MOCK_DATA', true); // Set to false once you add real API keys
define('LOG_TRACKING_REQUESTS', true);
define('LOG_FILE', '/logs/tracking_log.txt');
// Referral admin key - change to a strong secret before using admin pages
define('REFERRAL_ADMIN_KEY', 'changeme');
// reCAPTCHA keys (optional) - set these to enable reCAPTCHA protection
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET', '');

?>
