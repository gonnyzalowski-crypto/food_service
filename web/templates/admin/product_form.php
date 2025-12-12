<div class="admin-header">
  <div>
    <a href="/admin/products" style="color: #64748b; text-decoration: none;">← Back to Products</a>
    <h1 style="margin: 8px 0 0 0;"><?= $product ? 'Edit Product' : 'New Product' ?></h1>
  </div>
</div>

<form action="<?= $product ? '/admin/products/' . $product['id'] . '/edit' : '/admin/products/new' ?>" method="POST">
  <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
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
            <input type="text" name="manufacturer" class="form-control" value="<?= htmlspecialchars($product['manufacturer'] ?? 'Streicher') ?>">
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
      
      <button type="submit" class="btn btn-primary btn-lg btn-block">
        <?= $product ? 'Update Product' : 'Create Product' ?>
      </button>
    </div>
  </div>
</form>
