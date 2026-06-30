<?php
@include_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referrals – KETECH MEDIA</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .referral-hero { padding: 80px 20px; text-align: center; }
        .referral-box { max-width:720px; margin: 20px auto; background: #fff; padding:24px; border-radius:10px; box-shadow:0 8px 30px rgba(0,0,0,0.08); }
        .referral-form .form-group { margin-bottom: 12px; }
        .referral-form input, .referral-form textarea { width:100%; padding:10px 12px; border:1px solid #eee; border-radius:6px; }
        .referral-submit { background:#ff6600; color:#fff; padding:12px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; }
        .note { font-size:14px; color:#666; margin-top:10px; }
    </style>
</head>
<div class="whatsapp-float">
    <a href="https://wa.me/237679673906" target="_blank">💬</a>
</div>
<body>
<header>
    <div class="logo">KETECH MEDIA</div>
    <nav id="navbar">
        <a href="index.html">Home</a>
        <a href="about.html">About</a>
        <a href="services.html">Services</a>
        <a href="real-estate.html">Real Estate</a>
        <a href="portfolio.html">Portfolio</a>
        <a href="shipping.html">Shipping</a>
        <a href="ecommerce.html">E-Commerce</a>
        <a href="referrals.php">Referrals</a>
        <a href="contact.html">Contact</a>
    </nav>
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
</header>

<section class="referral-hero">
    <h1>Recommend KETECH MEDIA — Earn Rewards</h1>
    <p>Share our services with friends and businesses. Earn rewards when they become a client.</p>
</section>

<section>
    <div class="referral-box">
        <h2>Submit a Referral</h2>
        <form id="referralForm" class="referral-form">
            <!-- Honeypot field (visible only to bots) -->
            <input type="text" name="hp" style="display:none;" autocomplete="off" />
            <!-- Timestamp for simple bot/time check (ms) -->
            <input type="hidden" name="ts" value="" />
            <div class="form-group">
                <label>Your name</label>
                <input type="text" name="referrer_name" required />
            </div>
            <div class="form-group">
                <label>Your email</label>
                <input type="email" name="referrer_email" required />
            </div>
            <div class="form-group">
                <label>Friend / Company name</label>
                <input type="text" name="referee_name" required />
            </div>
            <div class="form-group">
                <label>Friend / Company email</label>
                <input type="email" name="referee_email" required />
            </div>
            <div class="form-group">
                <label>Message (optional)</label>
                <textarea name="message"></textarea>
            </div>
            <?php if (defined('RECAPTCHA_SITE_KEY') && RECAPTCHA_SITE_KEY): ?>
                <div style="margin-bottom:12px;"><div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars(RECAPTCHA_SITE_KEY); ?>"></div></div>
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <?php endif; ?>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                <button type="submit" class="referral-submit">Send Referral</button>
                <div id="referralStatus" class="note"></div>
            </div>
        </form>
        <p class="note">Rewards: when your referral becomes a paying client we credit you with discounts, account credits, or other perks. We will notify you by email.</p>
    </div>
</section>

<footer>
    <p>&copy; 2026 KETECH MEDIA</p>
</footer>

<script>
document.getElementById('referralForm').addEventListener('submit', async function(e){
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    const status = document.getElementById('referralStatus');
    status.textContent = 'Sending...';
    try {
        const res = await fetch('api/referral.php', { method:'POST', body:data });
        const json = await res.json();
        if(json.success){
            status.textContent = 'Referral submitted — thank you! Your referral code: ' + (json.referral_code || '—');
            form.reset();
        } else {
            status.textContent = 'Error: ' + (json.message || 'Unable to submit.');
        }
    } catch(err){
        status.textContent = 'Network error. Try again later.';
    }
});

function toggleMenu(){ const navbar=document.getElementById('navbar'); navbar.style.display=(navbar.style.display==='flex'?'none':'flex'); }

// set timestamp when page is loaded
document.addEventListener('DOMContentLoaded', function(){
    const ts = document.querySelector('input[name="ts"]');
    if(ts) ts.value = Date.now();
});
</script>
</body>
</html>
