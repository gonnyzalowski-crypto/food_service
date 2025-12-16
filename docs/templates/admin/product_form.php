<div class="admin-header">
  <div>
    <a href="/admin/products" style="color: #64748b; text-decoration: none;">← Back to Products</a>
    <h1 style="margin: 8px 0 0 0;"><?= $product ? 'Edit Product' : 'New Product' ?></h1>
  </div>
</div>

<form action="<?= $product ? '/admin/products/' . $product['id'] . '/edit' : '/admin/products/new' ?>" method="POST" enctype="multipart/form-data">
  <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;" class="product-form-grid">
    <div>
      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Product Information</h3>
        </div>
        <div class="card-body">
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label">SKU *</label>
              <input type="text" name="sku" class="form-control" required value="<?= htmlspecialchars($product['sku'] ?? '') ?>" placeholder="e.g., HYD-PWR-5000">
            </div>
            <div class="form-group">
              <label class="form-label">Category</label>
              <select name="category_id" class="form-control">
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label">Product Name *</label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($product['name'] ?? '') ?>" placeholder="e.g., Hydraulic Power Unit 5000 HP">
          </div>
          
          <div class="form-group">
            <label class="form-label">Short Description *</label>
            <input type="text" name="short_desc" class="form-control" required value="<?= htmlspecialchars($product['short_desc'] ?? '') ?>" placeholder="Brief product summary (1-2 sentences)">
          </div>
          
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Detailed product description"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
          </div>
          
          <div class="form-group">
            <label class="form-label">Long Description</label>
            <textarea name="long_description" class="form-control" rows="8" placeholder="Full product details, features, applications, etc."><?= htmlspecialchars($product['long_description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
      
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Features</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Key Features (one per line)</label>
            <textarea name="features" class="form-control" rows="5" placeholder="500 HP motor&#10;5000 PSI max pressure&#10;Remote monitoring"><?php
              $features = json_decode($product['features'] ?? '[]', true) ?: [];
              echo htmlspecialchars(implode("\n", $features));
            ?></textarea>
          </div>
        </div>
      </div>
    </div>
    
    <div>
      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Pricing</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Unit Price (USD) *</label>
            <input type="number" name="unit_price" class="form-control" required step="0.01" min="0" value="<?= htmlspecialchars($product['unit_price'] ?? '') ?>" placeholder="0.00">
          </div>
          <div class="form-group">
            <label class="form-label">Minimum Order Quantity</label>
            <input type="number" name="moq" class="form-control" min="1" value="<?= htmlspecialchars($product['moq'] ?? '1') ?>">
          </div>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Details</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Manufacturer</label>
            <input type="text" name="manufacturer" class="form-control" value="<?= htmlspecialchars($product['manufacturer'] ?? 'Gordon Food Service') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Weight (kg)</label>
            <input type="number" name="weight_kg" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($product['weight_kg'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Warranty (months)</label>
            <input type="number" name="warranty_months" class="form-control" min="0" value="<?= htmlspecialchars($product['warranty_months'] ?? '24') ?>">
          </div>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Product Image</h3>
        </div>
        <div class="card-body">
          <?php if (!empty($product['image_url'])): ?>
          <div style="margin-bottom: 16px;">
            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="Current image" style="max-width: 100%; max-height: 150px; border-radius: 8px; object-fit: cover;">
            <p style="font-size: 0.85rem; color: #64748b; margin-top: 8px;">Current image</p>
          </div>
          <?php endif; ?>
          <div class="form-group">
            <label class="form-label">Upload New Image</label>
            <input type="file" name="product_image" class="form-control" accept="image/*" style="padding: 8px;">
            <p style="font-size: 0.8rem; color: #64748b; margin-top: 4px;">JPG, PNG, WebP. Max 5MB.</p>
          </div>
        </div>
      </div>
      
      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Status</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="checkbox" name="is_active" value="1" <?= ($product['is_active'] ?? true) ? 'checked' : '' ?>>
              <span>Active (visible in catalog)</span>
            </label>
          </div>
          <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="checkbox" name="is_featured" value="1" <?= ($product['is_featured'] ?? false) ? 'checked' : '' ?>>
              <span>Featured (show on homepage)</span>
            </label>
          </div>
        </div>
      </div>
      
      <?php if ($product): ?>
      <div class="card mb-4" style="border: 1px solid #fee2e2;">
        <div class="card-header" style="background: #fef2f2;">
          <h3 class="card-title" style="color: #991b1b;">Danger Zone</h3>
        </div>
        <div class="card-body">
          <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 12px;">Permanently delete this product. This action cannot be undone.</p>
          <button type="button" onclick="if(confirm('Are you sure you want to delete this product?')) { document.getElementById('delete-form').submit(); }" class="btn btn-sm" style="background: #dc2626; color: white;">Delete Product</button>
        </div>
      </div>
      <?php endif; ?>
      
      <button type="submit" class="btn btn-primary btn-lg btn-block">
        <?= $product ? 'Update Product' : 'Create Product' ?>
      </button>
    </div>
  </div>
</form>

<?php if ($product): ?>
<form id="delete-form" action="/admin/products/<?= $product['id'] ?>/delete" method="POST" style="display: none;"></form>
<?php endif; ?>

<style>
@media (max-width: 768px) {
  .product-form-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>
