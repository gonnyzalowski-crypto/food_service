<?php
$lang = $_SESSION['lang'] ?? 'de';
$features = json_decode($product['features'] ?? '[]', true) ?: [];
$specs = json_decode($product['specifications'] ?? '[]', true) ?: [];
$galleryImages = json_decode($product['gallery_images'] ?? '[]', true) ?: [];
// Fallback to main image if no gallery
if (empty($galleryImages) && !empty($product['image_url'])) {
    $galleryImages = [$product['image_url']];
}
?>

<div class="breadcrumb">
  <a href="/"><?= __('home') ?></a> <span>/</span>
  <a href="/catalog"><?= __('products') ?></a> <span>/</span>
  <?php if (!empty($product['category_slug'])): ?>
  <a href="/catalog?category=<?= htmlspecialchars($product['category_slug']) ?>"><?= htmlspecialchars($product['category_name']) ?></a> <span>/</span>
  <?php endif; ?>
  <span><?= htmlspecialchars($product['name']) ?></span>
</div>

<div class="product-detail">
  <!-- Product Gallery -->
  <div class="product-gallery">
    <div class="product-main-image" id="mainProductImage">
      <?php if (!empty($galleryImages[0])): ?>
      <img src="<?= htmlspecialchars($galleryImages[0]) ?>" alt="<?= htmlspecialchars($product['name']) ?>" id="mainImage" style="width: 100%; height: 100%; object-fit: contain;">
      <?php else: ?>
      <div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 8rem; color: #94a3b8;">
        ⚙️
      </div>
      <?php endif; ?>
    </div>
    <div class="product-thumbnails">
      <?php foreach ($galleryImages as $index => $imgUrl): ?>
      <div class="product-thumbnail <?= $index === 0 ? 'active' : '' ?>" 
           data-image="<?= htmlspecialchars($imgUrl) ?>"
           onclick="changeMainImage(this, '<?= htmlspecialchars($imgUrl) ?>')">
        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($product['name']) ?> - Image <?= $index + 1 ?>" style="width: 100%; height: 100%; object-fit: cover;">
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  
  <!-- Product Info -->
  <div class="product-info">
    <div class="product-category" style="margin-bottom: 8px;"><?= htmlspecialchars($product['category_name'] ?? 'Industrial Equipment') ?></div>
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <div class="product-sku">SKU: <?= htmlspecialchars($product['sku']) ?></div>
    
    <div class="product-price-large"><?= format_price((float)$product['unit_price']) ?></div>
    <div class="product-price-note">
      <?= __('price_excludes') ?>
    </div>
    
    <div class="product-description">
      <?= nl2br(htmlspecialchars($product['description'] ?? $product['short_desc'] ?? '')) ?>
    </div>
    
    <?php if (!empty($features)): ?>
    <div style="margin-bottom: 24px;">
      <h3 style="font-size: 1rem; margin: 0 0 12px 0;"><?= __('key_features') ?></h3>
      <ul style="margin: 0; padding-left: 20px; color: #475569;">
        <?php foreach ($features as $feature): ?>
        <li style="margin-bottom: 8px;"><?= htmlspecialchars($feature) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($specs)): ?>
    <div class="product-specs">
      <h3><?= __('technical_specs') ?></h3>
      <?php foreach ($specs as $label => $value): ?>
      <div class="spec-row">
        <span class="spec-label"><?= htmlspecialchars($label) ?></span>
        <span class="spec-value"><?= htmlspecialchars($value) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Add to Cart -->
    <form class="add-to-cart-form" id="addToCartForm">
      <input type="hidden" name="sku" value="<?= htmlspecialchars($product['sku']) ?>">
      <input type="number" name="qty" value="1" min="1" class="qty-input">
      <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
        <?= __('add_to_cart') ?>
      </button>
    </form>
    
    <div id="cartMessage" class="alert alert-success" style="display: none;">
      <?= __('product_added') ?> <a href="/cart"><?= __('view_cart') ?></a>
    </div>
    
    <!-- Additional Info -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
      <div style="text-align: center;">
        <div style="font-size: 1.5rem; margin-bottom: 4px;">🚚</div>
        <div style="font-size: 0.85rem; color: #64748b;"><?= __('worldwide_shipping') ?></div>
      </div>
      <div style="text-align: center;">
        <div style="font-size: 1.5rem; margin-bottom: 4px;">🛡️</div>
        <div style="font-size: 0.85rem; color: #64748b;"><?= (int)($product['warranty_months'] ?? 24) ?> <?= __('month_warranty') ?></div>
      </div>
      <div style="text-align: center;">
        <div style="font-size: 1.5rem; margin-bottom: 4px;">📞</div>
        <div style="font-size: 0.85rem; color: #64748b;"><?= __('support_24_7') ?></div>
      </div>
    </div>
    
    <!-- Request Quote -->
    <div class="card mt-4" style="background: #f8fafc;">
      <div class="card-body">
        <h4 style="margin: 0 0 8px 0;"><?= __('need_custom_quote') ?></h4>
        <p style="margin: 0 0 16px 0; color: #64748b; font-size: 0.9rem;">
          <?= __('bulk_orders_text') ?>
        </p>
        <a href="/quote?product=<?= htmlspecialchars($product['sku']) ?>" class="btn btn-outline"><?= __('request_quote') ?></a>
      </div>
    </div>
  </div>
</div>

<!-- Long Description -->
<?php if (!empty($product['long_description'])): ?>
<div class="card mt-4">
  <div class="card-header">
    <h3 class="card-title"><?= __('product_details') ?></h3>
  </div>
  <div class="card-body">
    <div style="white-space: pre-wrap; line-height: 1.8;"><?= htmlspecialchars($product['long_description']) ?></div>
  </div>
</div>
<?php endif; ?>

<!-- Related Products -->
<?php if (!empty($relatedProducts)): ?>
<section style="margin-top: 64px;">
  <h2 style="margin-bottom: 24px;"><?= __('related_products') ?></h2>
  <div class="product-grid">
    <?php foreach ($relatedProducts as $related): ?>
    <div class="product-card">
      <div class="product-card-image">
        <?php if (!empty($related['image_url'])): ?>
        <img src="<?= htmlspecialchars($related['image_url']) ?>" alt="<?= htmlspecialchars($related['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
        <?php else: ?>
        <div class="placeholder-icon">⚙️</div>
        <?php endif; ?>
      </div>
      <div class="product-card-body">
        <div class="product-category"><?= htmlspecialchars($related['category_name'] ?? 'Equipment') ?></div>
        <h3 class="product-card-title">
          <a href="/product?sku=<?= htmlspecialchars($related['sku']) ?>"><?= htmlspecialchars($related['name']) ?></a>
        </h3>
        <p class="product-card-desc"><?= htmlspecialchars($related['short_desc'] ?? '') ?></p>
        <div class="product-card-footer">
          <span class="product-price"><?= format_price((float)$related['unit_price']) ?></span>
          <a href="/product?sku=<?= htmlspecialchars($related['sku']) ?>" class="btn btn-sm btn-primary">View</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<script>
// Gallery image switching
function changeMainImage(thumbnail, imageUrl) {
  // Update main image
  const mainImage = document.getElementById('mainImage');
  if (mainImage) {
    mainImage.src = imageUrl;
  }
  
  // Update active thumbnail
  document.querySelectorAll('.product-thumbnail').forEach(t => t.classList.remove('active'));
  thumbnail.classList.add('active');
}

document.getElementById('addToCartForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const data = {
    sku: formData.get('sku'),
    qty: parseInt(formData.get('qty'))
  };
  
  try {
    const res = await fetch('/api/cart', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(data)
    });
    
    if (res.ok) {
      const result = await res.json();
      document.getElementById('cartMessage').style.display = 'block';
      
      // Update cart count in header
      const cartCount = document.querySelector('.cart-count');
      if (cartCount) {
        cartCount.textContent = result.cart_count;
        cartCount.style.display = 'inline';
      }
      
      setTimeout(() => {
        document.getElementById('cartMessage').style.display = 'none';
      }, 5000);
    }
  } catch (err) {
    console.error('Add to cart failed:', err);
  }
});
</script>
