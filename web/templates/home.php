<!-- Hero Section -->
<section class="hero-section">
  <div class="hero-container">
    <div class="hero-content">
      <div class="hero-badge">
        <span>🇩🇪 <?= $lang === 'de' ? 'Deutsche Ingenieurskunst' : 'German Engineering Excellence' ?></span>
      </div>
      <h1 class="hero-title">
        <?= $lang === 'de' ? 'Premium Industrieausrüstung für Öl & Gas' : 'Premium Industrial Equipment for Oil & Gas' ?>
      </h1>
      <p class="hero-subtitle">
        <?= $lang === 'de' 
          ? 'Streicher GmbH liefert erstklassige Hydrauliksysteme, Bohrausrüstung und Pipeline-Komponenten, denen Branchenführer weltweit seit 1972 vertrauen.'
          : 'Streicher GmbH delivers world-class hydraulic systems, drilling equipment, and pipeline components trusted by industry leaders worldwide since 1972.' ?>
      </p>
      <div class="hero-buttons">
        <a href="/catalog" class="btn btn-primary btn-lg"><?= $lang === 'de' ? 'Katalog durchsuchen' : 'Browse Catalog' ?></a>
        <a href="/quote" class="btn btn-outline-light btn-lg"><?= $lang === 'de' ? 'Angebot anfordern' : 'Request Quote' ?></a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat">
          <div class="hero-stat-number">50+</div>
          <div class="hero-stat-label"><?= $lang === 'de' ? 'Jahre Erfahrung' : 'Years Experience' ?></div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-number">100+</div>
          <div class="hero-stat-label"><?= $lang === 'de' ? 'Produkte' : 'Products' ?></div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-number">45+</div>
          <div class="hero-stat-label"><?= $lang === 'de' ? 'Länder' : 'Countries' ?></div>
        </div>
      </div>
    </div>
    <div class="hero-image">
      <img src="/images/photos/drilling.jpg" alt="<?= $lang === 'de' ? 'Bohrausrüstung' : 'Drilling Equipment' ?>">
    </div>
  </div>
</section>

<!-- Trusted By Section -->
<section class="clients-section">
  <div class="section-container">
    <p class="clients-label"><?= $lang === 'de' ? 'Vertraut von führenden Unternehmen weltweit' : 'Trusted by leading companies worldwide' ?></p>
    <div class="clients-grid">
      <div class="client-logo" title="Shell">
        <img src="https://logo.clearbit.com/shell.com" alt="Shell" onerror="this.parentElement.innerHTML='<span>Shell</span>'">
      </div>
      <div class="client-logo" title="BP">
        <img src="https://logo.clearbit.com/bp.com" alt="BP" onerror="this.parentElement.innerHTML='<span>BP</span>'">
      </div>
      <div class="client-logo" title="ExxonMobil">
        <img src="https://logo.clearbit.com/exxonmobil.com" alt="ExxonMobil" onerror="this.parentElement.innerHTML='<span>ExxonMobil</span>'">
      </div>
      <div class="client-logo" title="Chevron">
        <img src="https://logo.clearbit.com/chevron.com" alt="Chevron" onerror="this.parentElement.innerHTML='<span>Chevron</span>'">
      </div>
      <div class="client-logo" title="TotalEnergies">
        <img src="https://logo.clearbit.com/totalenergies.com" alt="TotalEnergies" onerror="this.parentElement.innerHTML='<span>TotalEnergies</span>'">
      </div>
      <div class="client-logo" title="ConocoPhillips">
        <img src="https://logo.clearbit.com/conocophillips.com" alt="ConocoPhillips" onerror="this.parentElement.innerHTML='<span>ConocoPhillips</span>'">
      </div>
      <div class="client-logo" title="Equinor">
        <img src="https://logo.clearbit.com/equinor.com" alt="Equinor" onerror="this.parentElement.innerHTML='<span>Equinor</span>'">
      </div>
      <div class="client-logo" title="Eni">
        <img src="https://logo.clearbit.com/eni.com" alt="Eni" onerror="this.parentElement.innerHTML='<span>Eni</span>'">
      </div>
      <div class="client-logo" title="Petrobras">
        <img src="https://logo.clearbit.com/petrobras.com.br" alt="Petrobras" onerror="this.parentElement.innerHTML='<span>Petrobras</span>'">
      </div>
      <div class="client-logo" title="Saudi Aramco">
        <img src="https://logo.clearbit.com/aramco.com" alt="Saudi Aramco" onerror="this.parentElement.innerHTML='<span>Aramco</span>'">
      </div>
    </div>
  </div>
</section>

<!-- Categories -->
<section class="categories-section">
  <div class="section-container">
    <div class="section-header">
      <h2 class="section-title"><?= $lang === 'de' ? 'Produktkategorien' : 'Product Categories' ?></h2>
      <p class="section-subtitle"><?= $lang === 'de' ? 'Entdecken Sie unser umfassendes Sortiment an Industrieausrüstung' : 'Explore our comprehensive range of industrial equipment' ?></p>
    </div>
    
    <div class="categories-grid">
      <?php 
      $categoryIcons = [
        'pipelines-plants' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 6h16M4 6v4c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V6M4 18h16M4 18v-4c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2v4"/></svg>',
        'mechanical-engineering' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>',
        'electrical-engineering' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
        'civil-engineering' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 20h20M4 20V10l8-6 8 6v10M9 20v-6h6v6"/><rect x="9" y="10" width="6" height="4"/></svg>',
        'raw-materials' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/></svg>',
        'drilling-technology' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L12 22M8 6h8M6 10h12M4 14h16M8 18h8"/><circle cx="12" cy="4" r="2"/></svg>',
        'hydraulic-systems' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>',
        'instrumentation' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="6"/></svg>',
        'engineering-software' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/><path d="M7 8l3 3-3 3M12 14h5"/></svg>',
      ];
      foreach ($categories as $cat): 
        $icon = $categoryIcons[$cat['slug']] ?? '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>';
      ?>
      <a href="/catalog?category=<?= htmlspecialchars($cat['slug']) ?>" class="category-card">
        <div class="category-icon"><?= $icon ?></div>
        <h3 class="category-name"><?= htmlspecialchars($cat['name']) ?></h3>
        <p class="category-desc"><?= htmlspecialchars($cat['description'] ?? '') ?></p>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Featured Products -->
<section class="products-section">
  <div class="section-container">
    <div class="section-header-flex">
      <div>
        <h2 class="section-title"><?= $lang === 'de' ? 'Ausgewählte Produkte' : 'Featured Products' ?></h2>
        <p class="section-subtitle"><?= $lang === 'de' ? 'Hochleistungsausrüstung für anspruchsvolle Anwendungen' : 'High-performance equipment for demanding applications' ?></p>
      </div>
      <a href="/catalog" class="btn btn-outline"><?= $lang === 'de' ? 'Alle Produkte →' : 'View All Products →' ?></a>
    </div>
    
    <div class="product-grid">
      <?php foreach ($products as $product): ?>
      <div class="product-card">
        <div class="product-card-image" style="<?= !empty($product['image_url']) ? 'background-image: url(' . htmlspecialchars($product['image_url']) . '); background-size: cover; background-position: center;' : '' ?>">
          <?php if (!empty($product['is_featured'])): ?>
          <span class="product-badge"><?= $lang === 'de' ? 'Empfohlen' : 'Featured' ?></span>
          <?php endif; ?>
          <?php if (empty($product['image_url'])): ?>
          <div class="placeholder-icon">⚙️</div>
          <?php endif; ?>
        </div>
        <div class="product-card-body">
          <div class="product-category"><?= htmlspecialchars($product['category_name'] ?? 'Equipment') ?></div>
          <h3 class="product-card-title">
            <a href="/product?sku=<?= htmlspecialchars($product['sku']) ?>"><?= htmlspecialchars($product['name']) ?></a>
          </h3>
          <p class="product-card-desc"><?= htmlspecialchars($product['short_desc'] ?? '') ?></p>
          <div class="product-card-footer">
            <div>
              <span class="product-price-label"><?= $lang === 'de' ? 'Ab' : 'Starting at' ?></span>
              <span class="product-price"><?= format_price((float)$product['unit_price']) ?></span>
            </div>
            <a href="/product?sku=<?= htmlspecialchars($product['sku']) ?>" class="btn btn-sm btn-primary"><?= $lang === 'de' ? 'Details' : 'View' ?></a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Trust Badges -->
<section class="trust-section">
  <div class="section-container">
    <h2 class="trust-title"><?= $lang === 'de' ? 'Warum Streicher wählen?' : 'Why Choose Streicher?' ?></h2>
    <div class="trust-grid">
      <div class="trust-item">
        <div class="trust-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="6"/><path d="M12 14v8M8 22h8"/></svg>
        </div>
        <div class="trust-content">
          <div class="trust-name">ISO 9001:2015</div>
          <div class="trust-desc"><?= $lang === 'de' ? 'Qualitätszertifiziert' : 'Quality Certified' ?></div>
        </div>
      </div>
      <div class="trust-item">
        <div class="trust-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </div>
        <div class="trust-content">
          <div class="trust-name">API <?= $lang === 'de' ? 'Zertifiziert' : 'Certified' ?></div>
          <div class="trust-desc"><?= $lang === 'de' ? 'Öl & Gas Standards' : 'Oil & Gas Standards' ?></div>
        </div>
      </div>
      <div class="trust-item">
        <div class="trust-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
        </div>
        <div class="trust-content">
          <div class="trust-name"><?= $lang === 'de' ? 'Weltweiter Versand' : 'Global Shipping' ?></div>
          <div class="trust-desc"><?= $lang === 'de' ? '45+ Länder' : '45+ Countries' ?></div>
        </div>
      </div>
      <div class="trust-item">
        <div class="trust-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L3 7v6c0 5.5 3.8 10.3 9 12 5.2-1.7 9-6.5 9-12V7l-9-5z"/></svg>
        </div>
        <div class="trust-content">
          <div class="trust-name"><?= $lang === 'de' ? '24 Monate Garantie' : '24-Month Warranty' ?></div>
          <div class="trust-desc"><?= $lang === 'de' ? 'Volle Abdeckung' : 'Full Coverage' ?></div>
        </div>
      </div>
      <div class="trust-item">
        <div class="trust-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
        </div>
        <div class="trust-content">
          <div class="trust-name"><?= $lang === 'de' ? '24/7 Support' : '24/7 Support' ?></div>
          <div class="trust-desc"><?= $lang === 'de' ? 'Expertenunterstützung' : 'Expert Assistance' ?></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
  <div class="section-container">
    <h2 class="cta-title"><?= $lang === 'de' ? 'Benötigen Sie maßgeschneiderte Ausrüstung oder Mengenrabatte?' : 'Need Custom Equipment or Bulk Pricing?' ?></h2>
    <p class="cta-subtitle">
      <?= $lang === 'de' 
        ? 'Unser Ingenieurteam hilft Ihnen, die perfekte Lösung für Ihre Projektanforderungen zu finden.'
        : 'Our engineering team can help you find the perfect solution for your project requirements.' ?>
    </p>
    <div class="cta-buttons">
      <a href="/quote" class="btn btn-primary btn-lg"><?= $lang === 'de' ? 'Angebot anfordern' : 'Request a Quote' ?></a>
      <a href="/contact" class="btn btn-secondary btn-lg"><?= $lang === 'de' ? 'Vertrieb kontaktieren' : 'Contact Sales' ?></a>
    </div>
  </div>
</section>
