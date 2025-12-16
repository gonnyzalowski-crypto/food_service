<div class="admin-header">
  <div>
    <h1 style="margin: 0;">Products</h1>
    <p style="margin: 4px 0 0 0; color: #64748b;"><?= count($products) ?> products</p>
  </div>
  <a href="/admin/products/new" class="btn btn-primary">+ Add Product</a>
</div>

<div class="card">
  <div style="overflow-x: auto;">
    <table class="data-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>SKU</th>
          <th>Category</th>
          <th>Price</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $product): ?>
        <tr>
          <td>
            <div style="display: flex; align-items: center; gap: 12px;">
              <div style="width: 50px; height: 50px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">⚙️</div>
              <div>
                <div style="font-weight: 500;"><?= htmlspecialchars($product['name']) ?></div>
                <?php if (!empty($product['is_featured'])): ?>
                <span style="font-size: 0.75rem; background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 4px;">Featured</span>
                <?php endif; ?>
              </div>
            </div>
          </td>
          <td style="font-family: monospace; color: #64748b;"><?= htmlspecialchars($product['sku']) ?></td>
          <td><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></td>
          <td style="font-weight: 600;"><?= format_price((float)$product['unit_price']) ?></td>
          <td>
            <?php if (!empty($product['is_active'])): ?>
            <span class="order-status-badge status-delivered">Active</span>
            <?php else: ?>
            <span class="order-status-badge status-cancelled">Inactive</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display: flex; gap: 8px;">
              <a href="/product?sku=<?= htmlspecialchars($product['sku']) ?>" target="_blank" class="btn btn-sm btn-outline">View</a>
              <a href="/admin/products/<?= $product['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
