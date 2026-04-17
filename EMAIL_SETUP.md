# Email Setup Guide - KETECH MEDIA Website

## Overview
Your contact form is now set up to send emails! Here's what was configured:

### Files Created/Modified
- ✅ **api/contact.php** - Handles form submissions and sends emails
- ✅ **config.php** - Email configuration (updated)
- ✅ **contact.html** - Contact form with IDs and feedback display
- ✅ **script.js** - Form submission handler
- ✅ **style.css** - Status message styling

---

## How It Works

1. **User submits** the contact form on `/contact.html`
2. **JavaScript** collects the form data and sends it to `/api/contact.php` as JSON
3. **PHP script** validates the data and sends:
   - **Email to you** (ketechmedia@hotmail.com) with the user's message
   - **Confirmation email** to the user thanking them for their submission
4. **Log file** created at `/logs/contact_submissions.log` tracking all submissions

---

## Configuration

### Edit config.php
Update these settings with your actual email addresses:

```php
define('SENDER_EMAIL', 'ketechmedia@hotmail.com');     // Your email
define('SENDER_NAME', 'KETECH MEDIA');                  // Your company name
define('RECEIVE_EMAIL', 'ketechmedia@hotmail.com');     // Where to receive contact forms

// Optional: Enable SMTP for better email delivery
define('SMTP_ENABLED', false);  // Set to true if mail() doesn't work
```

---

## Quick Start

### Option 1: Using PHP's Built-in mail() Function (Default)
This works on XAMPP by default. No additional setup needed!

1. **Test the form**: Go to http://localhost/NW KETECH/contact.html
2. **Fill out the form** and click "Send Message"
3. You should receive an email within seconds

**If emails don't arrive:**
- Check your **spam/junk folder**
- Verify XAMPP's mail configuration (see Option 2)

---

### Option 2: Using Gmail SMTP (Recommended for Better Delivery)

If the built-in mail() function isn't working:

#### Step 1: Generate Gmail App Password
1. Go to [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
2. Select "Mail" and "Windows Computer"
3. Copy the generated 16-character password

#### Step 2: Update config.php
```php
define('SMTP_ENABLED', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-16-char-app-password');
define('SENDER_EMAIL', 'your-email@gmail.com');
define('RECEIVE_EMAIL', 'your-email@gmail.com');
```

#### Step 3: Install PHPMailer (runs automatically with composer)
```bash
composer require phpmailer/phpmailer
```

#### Step 4: Update api/contact.php
Replace the `mail()` calls with PHPMailer (see example below)

---

## Testing the Email Functionality

### Test Email 1 - Contact Form
1. Navigate to: http://localhost/NW%20KETECH/contact.html
2. Fill in the form:
   - Name: John Doe
   - Email: test@example.com
   - Phone: +1 234 567 8900
   - Service: Website Development
   - Message: This is a test message
3. Click "Send Message"
4. You should see: ✅ "Your message has been sent successfully!"

### Test Email 2 - Check Logs
View submission logs:
```
/logs/contact_submissions.log
```

---

## Troubleshooting

### Problem: "Error sending message"

**Solution 1: Check PHP mail() configuration**
1. Open: `c:\xampp\php\php.ini`
2. Find the line starting with `SMTP =`
3. Ensure it's set: `SMTP = smtp.gmail.com` (or your mail server)
4. Restart XAMPP

**Solution 2: Enable GMail's "Less Secure Apps"**
1. Go to [myaccount.google.com/lesssecureapps](https://myaccount.google.com/lesssecureapps)
2. Toggle "Allow less secure app access" to ON
3. Then use your Gmail password in config.php

**Solution 3: Check firewall/antivirus**
- Some security software blocks SMTP on port 587
- Try using port 25 or 465 instead

---

## Features

✅ **Responsive form** - Works on desktop, tablet, mobile  
✅ **Form validation** - Checks required fields & email format  
✅ **Real-time feedback** - Success/error messages  
✅ **Auto-confirmation** - User gets thank you email  
✅ **Logging** - All submissions stored in logs/  
✅ **HTML emails** - Professional formatted emails  
✅ **Error handling** - Graceful handling of failures  

---

## Email Preview

### User's Confirmation Email
```
Subject: We received your message - KETECH MEDIA

Hi [Name],

We have received your message and will get back to you as soon as possible.

Service Interest: Website Development

In the meantime, feel free to reach out to us via:
📧 Email: ketechmedia@hotmail.com
📞 Phone: +237 679 673 906 | +237 670 826 673
💬 WhatsApp: +237 670 826 673

Best regards,
KETECH MEDIA Team
```

---

## Support

For issues with:
- **Email not sending**: Check SMTP settings in config.php
- **Confirmation emails**: Verify SENDER_EMAIL is configured
- **Form not submitting**: Check browser console for JavaScript errors (F12)
- **Logs not created**: Ensure `/logs/` directory exists and is writable

---

## Next Steps

1. ✅ Update email addresses in `config.php`
2. Test the form on your live site
3. Monitor `/logs/contact_submissions.log` for submissions
4. Consider setting up SMTP if mail() doesn't work reliably
5. Customize confirmation email template in `api/contact.php` as needed

Enjoy receiving emails from your website! 🎉
