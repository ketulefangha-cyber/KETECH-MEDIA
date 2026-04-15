# Shipping Companies Integration Guide

This guide will help you integrate real tracking data from major shipping companies into your KETECH MEDIA website.

## Table of Contents
1. [DHL Integration](#dhl-integration)
2. [FedEx Integration](#fedex-integration)
3. [UPS Integration](#ups-integration)
4. [4PX Express Integration](#4px-express-integration)
5. [Cainiao Integration](#cainiao-integration)
6. [Testing & Troubleshooting](#testing--troubleshooting)

---

## DHL Integration

### Step 1: Create DHL Developer Account
1. Go to [DHL Developer Portal](https://developer.dhl.com/)
2. Click "Register" and create a free account
3. Fill in company details: KETECH MEDIA

### Step 2: Generate API Key
1. Log in to your DHL Developer account
2. Navigate to "My Projects" → "Create New Project"
3. Select "Track" API
4. Accept terms and create
5. Copy your **API Key**

### Step 3: Get Account Number
1. Contact DHL customer service or check your DHL account
2. Get your **DHL Account Number**

### Step 4: Add to config.php
```php
define('DHL_API_KEY', 'YOUR_ACTUAL_DHL_API_KEY');
define('DHL_ACCOUNT_NUMBER', 'YOUR_ACTUAL_ACCOUNT_NUMBER');
```

### Step 5: Test
- Tracking Number Format: 10 digits (e.g., `1234567890`)
- Use a real tracking number from your DHL shipments

---

## FedEx Integration

### Step 1: Create FedEx Business Account
1. Go to [FedEx Developer Portal](https://developer.fedex.com/)
2. Sign in with your FedEx account (or create one at fedex.com)
3. Accept the FedEx API license agreement

### Step 2: Register for API Access
1. Click "Try for Free" on the Track API
2. Complete registration form
3. Agree to terms and create credentials

### Step 3: Generate Authentication Credentials
1. Go to your FedEx Developer dashboard
2. Create a new project for "Track & Trace"
3. Get your:
   - **API Key**
   - **Secret Key**
   - **Account Number**

### Step 4: Setup OAuth 2.0 (Optional but Recommended)
FedEx uses OAuth 2.0. The API will handle token generation.

### Step 5: Add to config.php
```php
define('FEDEX_API_KEY', 'YOUR_ACTUAL_FEDEX_API_KEY');
define('FEDEX_SECRET_KEY', 'YOUR_ACTUAL_SECRET_KEY');
define('FEDEX_ACCOUNT_NUMBER', 'YOUR_FEDEX_ACCOUNT_NUMBER');
```

### Step 6: Test
- Tracking Number Format: 12, 14, or 15 digits
- Use a real FedEx tracking number

---

## UPS Integration

### Step 1: Create UPS Account
1. Go to [UPS Developer Kit (Tracking API)](https://www.ups.com/upsdeveloperkit)
2. Sign up for a free account
3. Verify your email

### Step 2: Request API Credentials
1. Log in to UPS Developer Portal
2. Click "Create Access Key"
3. Fill the form with your business information
4. UPS will provide:
   - **Access License Number**
   - **Username**
   - **Password**
   - **Account Number**

### Step 3: Enable Tracking API
1. In your UPS Developer account, go to "APIs"
2. Request access to "Tracking API"
3. Wait for approval (usually instant)

### Step 4: Add to config.php
```php
define('UPS_ACCESS_KEY', 'YOUR_ACTUAL_ACCESS_KEY');
define('UPS_USERNAME', 'YOUR_ACTUAL_USERNAME');
define('UPS_PASSWORD', 'YOUR_ACTUAL_PASSWORD');
```

### Step 5: Test
- Tracking Number Format: 1Z followed by 16 characters (e.g., `1Z999AA10123456784`)

---

## 4PX Express Integration

### Step 1: Create 4PX Account
1. Go to [4PX Website](https://www.4px.com/)
2. Click "Sign Up" → "Business Account"
3. Fill in your company details: KETECH MEDIA
4. Complete verification

### Step 2: Access API Management
1. Log in to your 4PX account
2. Go to "Settings" → "API Management"
3. Click "Apply for API"

### Step 3: Generate API Key
1. Fill in the API application form
2. Select "Track & Trace" service
3. 4PX will generate:
   - **API Key**
   - **Secret Key**

### Step 4: Activate API
1. Once approved, enable the API in your account settings
2. Copy both keys to a secure location

### Step 5: Add to config.php
```php
define('FOURPX_API_KEY', 'YOUR_ACTUAL_4PX_API_KEY');
define('FOURPX_SECRET_KEY', 'YOUR_ACTUAL_4PX_SECRET_KEY');
```

### Step 6: Test
- Tracking Number Format: Usually starts with "4PX"
- Example: `4PX1234567890CN`

---

## Cainiao Integration

### Step 1: Create Alibaba Account
1. Go to [Alibaba International](https://www.alibaba.com/)
2. Sign up for an account
3. Access [Cainiao Logistics Platform](https://global.cainiao.com/)

### Step 2: Setup Developer Access
1. Log in to Cainiao
2. Go to "My Account" → "API Access"
3. Click "Activate API"

### Step 3: Generate App Key
1. In API settings, click "Create Application"
2. Fill in application details
3. Cainiao will generate:
   - **App Key**
   - **Secret Key**

### Step 4: Configure Callback URL
1. Set your callback URL (important for real-time updates):
   ```
   https://yourwebsite.com/api/cainiao_webhook.php
   ```

### Step 5: Add to config.php
```php
define('CAINIAO_APP_KEY', 'YOUR_ACTUAL_CAINIAO_APP_KEY');
define('CAINIAO_SECRET_KEY', 'YOUR_ACTUAL_CAINIAO_SECRET_KEY');
```

### Step 6: Test
- Tracking Number Format: Usually 13+ digits
- Example: `1234567890123`

---

## Testing & Troubleshooting

### Step 1: Enable Mock Data (During Testing)
In `config.php`, ensure this is set to `true`:
```php
define('ENABLE_MOCK_DATA', true);
```

### Step 2: Test with Demo Numbers
The system comes with these test tracking numbers:
- `KETECH-CN-12345` (In Transit via DHL)
- `KETECH-CN-54321` (Delivered via FedEx)
- `KETECH-CN-99999` (Processing via UPS)

### Step 3: Enable Logging
To help with debugging, enable logging:
```php
define('LOG_TRACKING_REQUESTS', true);
```

Logs will be saved in `/logs/tracking_log.txt`

### Step 4: Check Your Setup
1. Verify all API keys are correctly entered in `config.php`
2. Ensure cURL is enabled on your server (usually default in XAMPP)
3. Check that the `api/` folder exists
4. Test file permissions (the PHP files should be readable)

### Common Issues

**Issue: "API returned status code 401"**
- Solution: Check your API credentials in `config.php`
- Make sure your API account is active

**Issue: "Timeout error"**
- Solution: Check internet connection
- The shipping company's API might be slow; increase timeout to 30 seconds

**Issue: Tracking number not found**
- Solution: Verify the tracking number format
- Use a real tracking number from your account
- Check that you're using the correct carrier

### Step 5: Switch to Real API
Once testing is complete:
```php
define('ENABLE_MOCK_DATA', false);// Change from true to false
```

Now your site will use REAL tracking data from shipping companies!

---

## Security Notes

⚠️ **IMPORTANT:**
1. Never commit `config.php` with real API keys to public repositories
2. Add `config.php` to your `.gitignore`
3. Use environment variables for production:
   ```php
   define('DHL_API_KEY', getenv('DHL_API_KEY'));
   ```
4. Restrict API IP addresses when possible
5. Use HTTPS for all tracking requests
6. Set up rate limiting to prevent abuse

---

## Support

- **DHL Support:** [developer.dhl.com](https://developer.dhl.com/)
- **FedEx Support:** [developer.fedex.com](https://developer.fedex.com/)
- **UPS Support:** [ups.com/upsdeveloperkit](https://www.ups.com/upsdeveloperkit)
- **4PX Support:** [4px.com](https://www.4px.com/)
- **Cainiao Support:** [cainiao.com](https://www.cainiao.com/)

---

## Files Created

- `config.php` - API credentials configuration
- `api/track.php` - Main tracking API backend
- `API_SETUP_GUIDE.md` - This guide (documentation.md)

## Next Steps

1. ✅ We've provided the backend infrastructure
2. 🔗 Get API keys from shipping companies (follow this guide)
3. 🔑 Add your API keys to `config.php`
4. ✏️ Change `ENABLE_MOCK_DATA` to `false` when ready
5. 🧪 Test with real tracking numbers
6. 🚀 Deploy to production

---

**Version:** 1.0  
**Last Updated:** April 2026  
**Created by:** KETECH MEDIA
