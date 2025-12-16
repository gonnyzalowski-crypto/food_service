<?php
$title = $title ?? 'Admin - Gordon Food Service';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/styles.css">
</head>
<body>
<!-- Mobile Admin Topbar (outside grid) -->
<div class="admin-topbar">
  <button class="admin-menu-toggle" aria-label="Toggle admin menu">☰</button>
  <div style="font-weight: 700; letter-spacing: 0.5px;">Gordon Food Service Admin</div>
  <a href="/supply" style="font-size: 0.85rem; color: #e2e8f0; text-decoration: none;">← Back to Supply Portal</a>
</div>

<div class="admin-overlay"></div>

<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div class="admin-sidebar-header">
      <a href="/admin" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: white;">
        <div class="logo-icon" style="width: 40px; height: 40px; font-size: 1.25rem;">S</div>
        <div>
          <div style="font-weight: 700;">GORDON FOOD SERVICE</div>
          <div style="font-size: 0.75rem; color: #94a3b8;">Admin Panel</div>
        </div>
      </a>
    </div>
    
    <nav class="admin-nav">
      <a href="/admin" class="<?= $_SERVER['REQUEST_URI'] === '/admin' ? 'active' : '' ?>">
        📊 Dashboard
      </a>
      <a href="/admin/supply-requests" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/supply-requests') === 0 ? 'active' : '' ?>">
        🛥️ Supply Requests
      </a>
      <a href="/admin/contractors" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/contractors') === 0 ? 'active' : '' ?>">
        🏢 Contractors
      </a>
      <a href="/admin/settings" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') === 0 ? 'active' : '' ?>">
        ⚙️ Settings
      </a>
      <div style="border-top: 1px solid rgba(255,255,255,0.1); margin: 24px 0;"></div>
      <a href="/supply" target="_blank">
        🌐 View Supply Portal
      </a>
      <a href="/admin/logout">
        🚪 Logout
      </a>
    </nav>
  </aside>
  
  <!-- Main Content -->
  <main class="admin-content">
    <?= $content ?? '' ?>
  </main>
</div>

<script>
// Admin sidebar toggle
(function() {
  const toggle = document.querySelector('.admin-menu-toggle');
  const overlay = document.querySelector('.admin-overlay');
  const body = document.body;
  if (toggle) {
    toggle.addEventListener('click', () => {
      body.classList.toggle('admin-nav-open');
    });
  }
  if (overlay) {
    overlay.addEventListener('click', () => body.classList.remove('admin-nav-open'));
  }
})();
</script>
</body>
</html>
