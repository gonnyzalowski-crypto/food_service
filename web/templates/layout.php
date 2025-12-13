<?php
$title = $title ?? 'Streicher GmbH - Industrial Parts & Equipment';
$lang = $_SESSION['lang'] ?? 'de';
$cartCount = 0;
if (isset($_SESSION['cart_id']) && isset($pdo)) {
    $stmt = $pdo->prepare('SELECT SUM(quantity) as count FROM cart_items WHERE cart_id = ?');
    $stmt->execute([$_SESSION['cart_id']]);
    $cartCount = (int)($stmt->fetchColumn() ?: 0);
} elseif (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum(array_column($_SESSION['cart'], 'qty'));
}
?>
<!doctype html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="description" content="Streicher GmbH - Premium industrial parts and equipment for petroleum, mechanical engineering, and heavy industry. German engineering excellence.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/styles.css?v=<?= time() ?>">
  <link rel="icon" type="image/png" href="/assets/favicon.png">
  <link rel="apple-touch-icon" href="/assets/logo.png">
</head>
<body>

<!-- Skip Link for Accessibility -->
<a href="#main-content" class="skip-link">Skip to main content</a>

<!-- Mobile Nav Overlay -->
<div class="mobile-nav-overlay"></div>

<!-- Header -->
<header class="site-header">
  <!-- Desktop Header Top -->
  <div class="header-top desktop-only">
    <div class="header-top-inner">
      <div>
        <span>🇩🇪 <?= __('made_in_germany') ?></span>
        <span style="margin-left: 24px;">✉️ store@streichergmbh.com</span>
      </div>
      <div style="display: flex; align-items: center; gap: 16px;">
        <a href="/news"><?= __('news') ?></a>
        <a href="/events"><?= __('events') ?></a>
        <a href="/contact"><?= __('contact') ?></a>
        <a href="/mediathek"><?= __('media') ?></a>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="/account"><?= __('my_account') ?></a>
          <a href="/logout"><?= __('logout') ?></a>
        <?php else: ?>
          <a href="/login"><?= __('login') ?></a>
        <?php endif; ?>
        <div class="lang-switcher">
          <a href="?lang=de" class="lang-btn <?= $lang === 'de' ? 'active' : '' ?>">DE</a>
          <a href="?lang=en" class="lang-btn <?= $lang === 'en' ? 'active' : '' ?>">EN</a>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Mobile Header Bar -->
  <div class="mobile-header">
    <button class="mobile-menu-toggle" aria-label="Toggle menu">
      <span class="hamburger-icon"></span>
    </button>
    <a href="/" class="mobile-logo">
      <img src="/assets/logo.png" alt="Streicher" style="height: 36px; width: auto;">
    </a>
    <div class="mobile-header-actions">
      <div class="lang-switcher-mobile">
        <a href="?lang=de" class="<?= $lang === 'de' ? 'active' : '' ?>">DE</a>
        <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
      </div>
      <a href="/cart" class="mobile-cart-btn">
        🛒<?php if ($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
      </a>
    </div>
  </div>
  
  <!-- Desktop Header Main -->
  <div class="header-main desktop-only">
    <a href="/" class="logo">
      <img src="/assets/logo.png" alt="Streicher" class="logo-img" style="height: 48px; width: auto;">
    </a>
    
    <nav class="header-nav">
      <a href="/profile"><?= __('profile') ?></a>
      <a href="/business-sectors"><?= __('business_sectors') ?></a>
      <a href="/reference-projects"><?= __('references') ?></a>
      <a href="/catalog"><?= __('products') ?></a>
      <a href="/hse-q">HSE-Q</a>
      <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <a href="/admin">Admin</a>
      <?php endif; ?>
      <a href="/cart" class="cart-link">
        🛒 <?= __('cart') ?>
        <?php if ($cartCount > 0): ?>
          <span class="cart-count"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>
    </nav>
  </div>
  
  <!-- Mobile Slide-out Navigation -->
  <nav class="mobile-nav">
    <div class="mobile-nav-header">
      <span>Menu</span>
      <button class="mobile-nav-close" aria-label="Close menu">✕</button>
    </div>
    <div class="mobile-nav-links">
      <a href="/profile"><?= __('profile') ?></a>
      <a href="/business-sectors"><?= __('business_sectors') ?></a>
      <a href="/reference-projects"><?= __('references') ?></a>
      <a href="/catalog"><?= __('products') ?></a>
      <a href="/hse-q">HSE-Q</a>
      <?php if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <a href="/admin">Admin</a>
      <?php endif; ?>
    </div>
    <div class="mobile-nav-footer">
      <a href="/cart" class="mobile-nav-cart">
        🛒 <?= __('cart') ?>
        <?php if ($cartCount > 0): ?><span>(<?= $cartCount ?>)</span><?php endif; ?>
      </a>
      <div class="mobile-nav-secondary">
        <a href="/news"><?= __('news') ?></a>
        <a href="/events"><?= __('events') ?></a>
        <a href="/contact"><?= __('contact') ?></a>
        <a href="/mediathek"><?= __('media') ?></a>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="/account"><?= __('my_account') ?></a>
          <a href="/logout"><?= __('logout') ?></a>
        <?php else: ?>
          <a href="/login"><?= __('login') ?></a>
        <?php endif; ?>
      </div>
      <div class="mobile-nav-info">
        <span>🇩🇪 <?= __('made_in_germany') ?></span>
        <span>✉️ store@streichergmbh.com</span>
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
        <img src="/assets/logo.png" alt="Streicher" style="height: 48px; width: auto;">
      </div>
      <p>
        <?= $lang === 'de' 
          ? 'Streicher GmbH ist ein führender Anbieter hochwertiger Industrieteile und Ausrüstungen für die Erdöl-, Maschinenbau- und Schwerindustrie. Mit über 50 Jahren deutscher Ingenieurskunst liefern wir Präzisionskomponenten weltweit.'
          : 'Streicher GmbH is a leading supplier of high-quality industrial parts and equipment for the petroleum, mechanical engineering, and heavy industry sectors. With over 50 years of German engineering excellence, we deliver precision components worldwide.' ?>
      </p>
      <p style="margin-top: 16px;">
        <strong><?= $lang === 'de' ? 'Hauptsitz:' : 'Headquarters:' ?></strong><br>
        Industriestraße 45<br>
        93055 Regensburg, Germany
      </p>
    </div>
    
    <div>
      <h4 class="footer-title"><?= __('products') ?></h4>
      <ul class="footer-links">
        <li><a href="/catalog?category=hydraulic-systems"><?= $lang === 'de' ? 'Hydrauliksysteme' : 'Hydraulic Systems' ?></a></li>
        <li><a href="/catalog?category=drilling-equipment"><?= $lang === 'de' ? 'Bohrausrüstung' : 'Drilling Equipment' ?></a></li>
        <li><a href="/catalog?category=pipeline-components"><?= $lang === 'de' ? 'Pipeline-Komponenten' : 'Pipeline Components' ?></a></li>
        <li><a href="/catalog?category=compressors"><?= $lang === 'de' ? 'Kompressoren' : 'Compressors' ?></a></li>
        <li><a href="/catalog?category=pumping-systems"><?= $lang === 'de' ? 'Pumpsysteme' : 'Pumping Systems' ?></a></li>
        <li><a href="/catalog?category=spare-parts"><?= $lang === 'de' ? 'Ersatzteile' : 'Spare Parts' ?></a></li>
      </ul>
    </div>
    
    <div>
      <h4 class="footer-title"><?= __('company') ?></h4>
      <ul class="footer-links">
        <li><a href="/about"><?= __('about_us') ?></a></li>
        <li><a href="/about"><?= __('certifications') ?></a></li>
        <li><a href="/careers"><?= __('careers') ?></a></li>
        <li><a href="/contact"><?= __('contact') ?></a></li>
        <li><a href="/quote"><?= __('request_quote') ?></a></li>
      </ul>
    </div>
    
    <div>
      <h4 class="footer-title"><?= __('support') ?></h4>
      <ul class="footer-links">
        <li><a href="/track"><?= __('track_shipment') ?></a></li>
        <li><a href="/account"><?= __('my_orders') ?></a></li>
        <li><a href="/returns"><?= __('returns_warranty') ?></a></li>
        <li><a href="/faq"><?= __('faq') ?></a></li>
        <li><a href="/support"><?= __('technical_support') ?></a></li>
      </ul>
    </div>
    
    <div>
      <h4 class="footer-title"><?= __('legal') ?></h4>
      <ul class="footer-links">
        <li><a href="/privacy"><?= __('privacy_policy') ?></a></li>
        <li><a href="/assets/privacy-policy.pdf" target="_blank"><?= $lang === 'de' ? 'Datenschutz (PDF)' : 'Privacy Policy (PDF)' ?></a></li>
        <li><a href="/terms"><?= __('terms_conditions') ?></a></li>
        <li><a href="/shipping"><?= __('shipping_info') ?></a></li>
        <li><a href="/returns"><?= __('returns_policy') ?></a></li>
      </ul>
    </div>
  </div>
  
  <div class="footer-bottom">
    <div>
      © <?= date('Y') ?> Streicher GmbH. <?= __('all_rights_reserved') ?> 
      <a href="/privacy" style="color: inherit; margin-left: 16px;"><?= __('privacy_policy') ?></a>
      <a href="/terms" style="color: inherit; margin-left: 16px;"><?= __('terms_conditions') ?></a>
    </div>
    <div>
      <span>VAT ID: DE123456789</span>
      <span style="margin-left: 16px;">ISO 9001:2015 <?= __('certified') ?></span>
    </div>
  </div>
</footer>

<script>
// Global cart functionality
window.StreicherCart = {
  async add(sku, qty = 1) {
    const res = await fetch('/api/cart', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({sku, qty})
    });
    if (res.ok) {
      const data = await res.json();
      this.updateCount(data.cart_count || 0);
      return true;
    }
    return false;
  },
  
  updateCount(count) {
    const badge = document.querySelector('.cart-count');
    if (badge) {
      badge.textContent = count;
      badge.style.display = count > 0 ? 'inline' : 'none';
    }
  }
};

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
</script>
</body>
</html>
