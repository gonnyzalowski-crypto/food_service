<?php
$title = $title ?? 'Gordon Food Service - Galveston Wholesale Supply';
$lang = 'en-US';
?>
<!doctype html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="description" content="Gordon Food Service (Galveston) provides wholesale food, water, and offshore provisioning supplies for Gulf of Mexico operations within a 100-mile radius.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/styles.css?v=<?= time() ?>">
  <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">
  <link rel="apple-touch-icon" href="/assets/favicon.svg">
</head>
<body>

<!-- Skip Link for Accessibility -->
<a href="#main-content" class="skip-link">Skip to main content</a>

<!-- Mobile Nav Overlay -->
<div class="mobile-nav-overlay"></div>

<!-- Header -->
<header class="site-header" style="background: #0d0d0d; border-bottom: 1px solid #222;">
  <!-- Desktop Header Main -->
  <div class="header-main desktop-only" style="padding: 20px 40px; width: 100%;">
    <a href="/" class="logo" style="display: flex; align-items: center; gap: 14px; text-decoration: none;">
      <img src="/assets/logo.svg" alt="" style="width: 42px; height: 42px;">
      <span style="font-family: 'Inter', sans-serif; font-size: 1.8rem; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">Gordon Food Service</span>
    </a>
    
    <nav class="header-nav">
      <a href="/supply">Supply Portal</a>
      <a href="/contact"><?= __('contact') ?></a>
    </nav>
  </div>
  
  <!-- Mobile Header Bar -->
  <div class="mobile-header" style="background: #0d0d0d;">
    <button class="mobile-menu-toggle" aria-label="Toggle menu">
      <span class="hamburger-icon"></span>
    </button>
    <a href="/" class="mobile-logo" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
      <img src="/assets/logo.svg" alt="" style="width: 32px; height: 32px;">
      <span style="font-family: 'Inter', sans-serif; font-size: 1.15rem; font-weight: 800; color: #ffffff; letter-spacing: -0.3px;">Gordon Food Service</span>
    </a>
    <div class="mobile-header-actions"></div>
  </div>
  
  <!-- Mobile Slide-out Navigation -->
  <nav class="mobile-nav">
    <div class="mobile-nav-header">
      <span>Menu</span>
      <button class="mobile-nav-close" aria-label="Close menu">✕</button>
    </div>
    <div class="mobile-nav-links">
      <a href="/supply">Supply Portal</a>
      <a href="/contact"><?= __('contact') ?></a>
    </div>
    <div class="mobile-nav-footer">
      <div class="mobile-nav-secondary">
        <a href="/contact"><?= __('contact') ?></a>
        <a href="/supply">Supply Portal</a>
      </div>
      <div class="mobile-nav-info">
        <span>✉️ contact@gordonfoods.com</span>
        <span style="margin-left: 16px;">☎️ +1 213-653-0266</span>
      </div>
    </div>
  </nav>
</header>

<!-- Main Content -->
<main id="main-content" class="<?= ($isHomePage ?? false) ? 'home-page' : 'page-content' ?>">
  <?= $content ?? '' ?>
</main>

<!-- Footer -->
  <footer class="site-footer">
  <div class="footer-grid">
    <div class="footer-brand">
      <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
        <img src="/assets/logo.svg" alt="Gordon Food Service" style="height: 48px; width: auto;">
      </div>
      <p>
        Gordon Food Service (Galveston) supplies wholesale food, water, and essential provisions for onshore and offshore operations across the Gulf of Mexico. We support large orders, recurring deliveries, and time-critical provisioning within a 100-mile service radius.
      </p>
      <p style="margin-top: 16px;">
        <strong>Location:</strong><br>
        28th–36th St Port/Harborside industrial zone<br>
        Galveston, TX
      </p>
      <p style="margin-top: 16px;">
        <strong>Contact:</strong><br>
        contact@gordonfoods.com<br>
        +1 213-653-0266
      </p>
    </div>
    
    <div>
      <h4 class="footer-title">Supply Portal</h4>
      <ul class="footer-links">
        <li><a href="/supply">Access Supply Portal</a></li>
        <li><a href="/contact"><?= __('contact') ?></a></li>
        <li><a href="/quote"><?= __('request_quote') ?></a></li>
      </ul>
    </div>
    
    <div>
      <h4 class="footer-title">Services</h4>
      <ul class="footer-links">
        <li><a href="/services/offshore">Offshore Provisioning</a></li>
        <li><a href="/services/onshore">Onshore Wholesale</a></li>
        <li><a href="/services/recurring">Recurring Deliveries</a></li>
        <li><a href="/services/dispatch">Time-Critical Dispatch</a></li>
        <li><a href="/services/groceries">Groceries & Dry Goods</a></li>
        <li><a href="/services/toiletries">Toiletries & Hygiene</a></li>
      </ul>
    </div>
    
    <div>
      <h4 class="footer-title"><?= __('company') ?></h4>
      <ul class="footer-links">
        <li><a href="/about"><?= __('about_us') ?></a></li>
        <li><a href="/about"><?= __('certifications') ?></a></li>
        <li><a href="/careers"><?= __('careers') ?></a></li>
        <li><a href="/contact"><?= __('contact') ?></a></li>
      </ul>
    </div>
    
    <div>
      <h4 class="footer-title"><?= __('legal') ?></h4>
      <ul class="footer-links">
        <li><a href="/privacy"><?= __('privacy_policy') ?></a></li>
        <li><a href="/terms"><?= __('terms_conditions') ?></a></li>
      </ul>
    </div>
  </div>
  
  <div class="footer-bottom">
    <div>
      © <?= date('Y') ?> Gordon Food Service. <?= __('all_rights_reserved') ?> 
      <a href="/privacy" style="color: inherit; margin-left: 16px;"><?= __('privacy_policy') ?></a>
      <a href="/terms" style="color: inherit; margin-left: 16px;"><?= __('terms_conditions') ?></a>
    </div>
    <div>
      <span>Serving Gulf of Mexico operations within a 100-mile radius</span>
      <span style="margin-left: 16px;">Offshore delivery pricing available</span>
    </div>
  </div>
</footer>

<script>
// Mobile nav toggle
(function() {
  const menuToggle = document.querySelector('.mobile-menu-toggle');
  const menuClose = document.querySelector('.mobile-nav-close');
  const overlay = document.querySelector('.mobile-nav-overlay');
  const body = document.body;
  
  function openNav() {
    body.classList.add('nav-open');
  }
  
  function closeNav() {
    body.classList.remove('nav-open');
  }
  
  if (menuToggle) {
    menuToggle.addEventListener('click', openNav);
  }
  if (menuClose) {
    menuClose.addEventListener('click', closeNav);
  }
  if (overlay) {
    overlay.addEventListener('click', closeNav);
  }
})();

// Copy to clipboard utility function
function copyToClipboard(text, button) {
  navigator.clipboard.writeText(text).then(function() {
    const originalText = button.innerHTML;
    button.innerHTML = '✓ Copied!';
    button.style.background = 'rgba(34, 197, 94, 0.3)';
    setTimeout(function() {
      button.innerHTML = originalText;
      button.style.background = '';
    }, 2000);
  }).catch(function(err) {
    console.error('Failed to copy: ', err);
  });
}
</script>
</body>
</html>
