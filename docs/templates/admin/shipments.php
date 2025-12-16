<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">🚚 Shipments</h1>
    <p class="admin-page-subtitle">Track and manage all shipments</p>
  </div>
  <div>
    <a href="/admin/shipments/create" class="btn btn-primary">+ Create Manual Shipment</a>
  </div>
</div>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px;">
  <?php
  $pending = count(array_filter($shipments, fn($s) => $s['status'] === 'pending'));
  $inTransit = count(array_filter($shipments, fn($s) => $s['status'] === 'in_transit'));
  $customsHold = count(array_filter($shipments, fn($s) => ($s['customs_hold'] ?? 0) == 1));
  $delivered = count(array_filter($shipments, fn($s) => $s['status'] === 'delivered'));
  ?>
  <div class="stat-card">
    <div class="stat-icon" style="background: #fef3c7;">📦</div>
    <div class="stat-content">
      <div class="stat-value"><?= $pending ?></div>
      <div class="stat-label">Pending</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background: #dbeafe;">🚛</div>
    <div class="stat-content">
      <div class="stat-value"><?= $inTransit ?></div>
      <div class="stat-label">In Transit</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background: #fee2e2;">⚠️</div>
    <div class="stat-content">
      <div class="stat-value"><?= $customsHold ?></div>
      <div class="stat-label">Customs Hold</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background: #dcfce7;">✅</div>
    <div class="stat-content">
      <div class="stat-value"><?= $delivered ?></div>
      <div class="stat-label">Delivered</div>
    </div>
  </div>
</div>

<!-- Shipments Table -->
<div class="card">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h3 class="card-title">All Shipments</h3>
    <div style="display: flex; gap: 12px;">
      <input type="text" id="searchShipments" placeholder="Search tracking number..." class="form-control" style="width: 250px;">
    </div>
  </div>
  <div class="card-body" style="padding: 0;">
    <?php if (empty($shipments)): ?>
    <div style="padding: 48px; text-align: center; color: #64748b;">
      <div style="font-size: 3rem; margin-bottom: 16px;">📦</div>
      <p>No shipments yet</p>
    </div>
    <?php else: ?>
    <table class="data-table">
      <thead>
        <tr>
          <th>Tracking Number</th>
          <th>Order</th>
          <th>Customer</th>
          <th>Carrier</th>
          <th>Status</th>
          <th>Customs</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($shipments as $shipment): 
          $billing = json_decode($shipment['billing_address'] ?? '{}', true);
        ?>
        <tr>
          <td>
            <div style="font-weight: 600; font-family: monospace;">
              <?= htmlspecialchars($shipment['tracking_number']) ?>
            </div>
          </td>
          <td>
            <?php if ($shipment['order_id']): ?>
            <a href="/admin/orders/<?= $shipment['order_id'] ?>" style="color: #2563eb;">
              <?= htmlspecialchars($shipment['order_number']) ?>
            </a>
            <?php else: ?>
            <span style="color: #94a3b8; font-style: italic;">Manual Shipment</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="font-weight: 500;"><?= htmlspecialchars($billing['company'] ?? 'N/A') ?></div>
            <div style="font-size: 0.85rem; color: #64748b;"><?= htmlspecialchars($billing['name'] ?? '') ?></div>
          </td>
          <td><?= htmlspecialchars($shipment['carrier'] ?? 'Streicher Logistics') ?></td>
          <td>
            <span class="order-status-badge status-<?= $shipment['status'] ?>">
              <?= ucfirst(str_replace('_', ' ', $shipment['status'])) ?>
            </span>
          </td>
          <td>
            <?php if ($shipment['customs_hold'] ?? false): ?>
            <span style="background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">
              ⚠️ Hold
            </span>
            <?php else: ?>
            <span style="color: #22c55e;">✓ Clear</span>
            <?php endif; ?>
          </td>
          <td>
            <div><?= date('M j, Y', strtotime($shipment['created_at'])) ?></div>
            <div style="font-size: 0.85rem; color: #64748b;"><?= date('g:i A', strtotime($shipment['created_at'])) ?></div>
          </td>
          <td>
            <div style="display: flex; gap: 8px;">
              <?php if ($shipment['order_id']): ?>
              <a href="/admin/orders/<?= $shipment['order_id'] ?>" class="btn btn-sm btn-outline">View Order</a>
              <?php endif; ?>
              <a href="/admin/shipments/<?= $shipment['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a>
              <a href="/track?tracking=<?= urlencode($shipment['tracking_number']) ?>" target="_blank" class="btn btn-sm btn-primary">Track</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script>
document.getElementById('searchShipments')?.addEventListener('input', function(e) {
  const search = e.target.value.toLowerCase();
  document.querySelectorAll('.data-table tbody tr').forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(search) ? '' : 'none';
  });
});
</script>
