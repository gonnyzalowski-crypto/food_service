<?php $lang = $_SESSION['lang'] ?? 'de'; ?>
<div class="breadcrumb">
  <a href="/"><?= __('home') ?></a> <span>/</span>
  <a href="/catalog"><?= __('products') ?></a>
  <?php if ($currentCategory): ?>
  <span>/</span> <span><?= htmlspecialchars($currentCategory['name']) ?></span>
  <?php endif; ?>
</div>

<div style="display: grid; grid-template-columns: 280px 1fr; gap: 32px;">
  <!-- Sidebar -->
  <aside>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><?= __('categories') ?></h3>
      </div>
      <div class="card-body" style="padding: 0;">
        <a href="/catalog" style="display: block; padding: 12px 24px; text-decoration: none; color: <?= !$currentCategory ? '#dc2626' : '#334155' ?>; font-weight: <?= !$currentCategory ? '600' : '400' ?>; border-bottom: 1px solid #e2e8f0;">
          <?= __('all_products') ?>
        </a>
        <?php foreach ($categories as $cat): ?>
        <a href="/catalog?category=<?= htmlspecialchars($cat['slug']) ?>" 
           style="display: block; padding: 12px 24px; text-decoration: none; color: <?= ($currentCategory && $currentCategory['slug'] === $cat['slug']) ? '#dc2626' : '#334155' ?>; font-weight: <?= ($currentCategory && $currentCategory['slug'] === $cat['slug']) ? '600' : '400' ?>; border-bottom: 1px solid #e2e8f0;">
          <?= htmlspecialchars($cat['name']) ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    
    <!-- Search -->
    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title"><?= __('search') ?></h3>
      </div>
      <div class="card-body">
        <form action="/catalog" method="GET">
          <?php if ($currentCategory): ?>
          <input type="hidden" name="category" value="<?= htmlspecialchars($currentCategory['slug']) ?>">
          <?php endif; ?>
          <input type="text" name="search" class="form-control" placeholder="<?= __('search_products') ?>" value="<?= htmlspecialchars($search ?? '') ?>">
          <button type="submit" class="btn btn-primary btn-block mt-2"><?= __('search') ?></button>
        </form>
      </div>
    </div>
    
    <!-- Price Range Info -->
    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title"><?= __('price_range') ?></h3>
      </div>
      <div class="card-body">
        <p style="margin: 0; color: #64748b; font-size: 0.9rem;">
          <?= __('price_range_text') ?>
        </p>
        <a href="/quote" class="btn btn-outline btn-block mt-3"><?= __('request_custom_quote') ?></a>
      </div>
    </div>
  </aside>
  
  <!-- Products Grid -->
  <div>
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
      <div>
        <h1 class="page-title"><?= $currentCategory ? htmlspecialchars($currentCategory['name']) : __('all_products') ?></h1>
        <p class="page-subtitle">
          <?php if ($currentCategory): ?>
            <?= htmlspecialchars($currentCategory['description'] ?? '') ?>
          <?php else: ?>
            <?= __('browse_catalog_full') ?>
          <?php endif; ?>
        </p>
      </div>
      <div style="color: #64748b;">
        <?= count($products) ?> <?= count($products) !== 1 ? __('products_found') : __('product_found') ?>
      </div>
    </div>
    
    <?php if ($search): ?>
    <div class="alert alert-info mb-3">
      <?= __('showing_results') ?> <strong><?= htmlspecialchars($search) ?></strong>
      <a href="/catalog<?= $currentCategory ? '?category=' . htmlspecialchars($currentCategory['slug']) : '' ?>" style="margin-left: 16px;"><?= __('clear_search') ?></a>
    </div>
    <?php endif; ?>
    
    <?php if (empty($products)): ?>
    <div class="card">
      <div class="card-body text-center" style="padding: 64px;">
        <div style="font-size: 4rem; margin-bottom: 16px;">🔍</div>
        <h3><?= __('no_products_found') ?></h3>
        <p style="color: #64748b;"><?= __('try_adjusting') ?></p>
        <a href="/catalog" class="btn btn-primary mt-2"><?= __('view_all') ?></a>
      </div>
    </div>
    <?php else: ?>
    <div class="product-grid">
      <?php foreach ($products as $product): ?>
      <div class="product-card">
        <div class="product-card-image" style="<?= !empty($product['image_url']) ? 'background-image: url(' . htmlspecialchars($product['image_url']) . '); background-size: cover; background-position: center;' : '' ?>">
          <?php if (!empty($product['is_featured'])): ?>
          <span class="product-badge"><?= __('featured') ?></span>
          <?php endif; ?>
          <?php if (empty($product['image_url'])): ?>
          <div class="placeholder-icon">⚙️</div>
          <?php endif; ?>
        </div>
        <div class="product-card-body">
          <div class="product-category"><?= htmlspecialchars($product['category_name'] ?? __('equipment')) ?></div>
          <h3 class="product-card-title">
            <a href="/product?sku=<?= htmlspecialchars($product['sku']) ?>"><?= htmlspecialchars($product['name']) ?></a>
          </h3>
          <p class="product-card-desc"><?= htmlspecialchars($product['short_desc'] ?? '') ?></p>
          <div class="product-card-footer">
            <div>
              <span class="product-price-label"><?= __('starting_at') ?></span>
              <span class="product-price"><?= format_price((float)$product['unit_price']) ?></span>
            </div>
            <a href="/product?sku=<?= htmlspecialchars($product['sku']) ?>" class="btn btn-sm btn-primary"><?= __('view') ?></a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
