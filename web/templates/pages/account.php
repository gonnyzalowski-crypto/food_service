<div class="breadcrumb">
  <a href="/">Home</a> <span>/</span>
  <span>My Account</span>
</div>

<div class="page-header">
  <h1 class="page-title">My Account</h1>
  <p class="page-subtitle">Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Customer') ?></p>
</div>

<div style="display: grid; grid-template-columns: 280px 1fr; gap: 32px;">
  <!-- Sidebar -->
  <div>
    <div class="card">
      <div class="card-body" style="padding: 0;">
        <a href="/account" style="display: block; padding: 16px 24px; text-decoration: none; color: #dc2626; font-weight: 600; border-bottom: 1px solid #e2e8f0; background: #fef2f2;">
          📦 My Orders
        </a>
        <a href="/account/profile" style="display: block; padding: 16px 24px; text-decoration: none; color: #334155; border-bottom: 1px solid #e2e8f0;">
          👤 Profile Settings
        </a>
        <a href="/account/addresses" style="display: block; padding: 16px 24px; text-decoration: none; color: #334155; border-bottom: 1px solid #e2e8f0;">
          📍 Addresses
        </a>
        <a href="/account/quotes" style="display: block; padding: 16px 24px; text-decoration: none; color: #334155; border-bottom: 1px solid #e2e8f0;">
          📋 My Quotes
        </a>
        <a href="/logout" style="display: block; padding: 16px 24px; text-decoration: none; color: #64748b;">
          🚪 Logout
        </a>
      </div>
    </div>
  </div>
  
  <!-- Main Content -->
  <div>
    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px;">
      <div class="card" style="padding: 24px; text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: #dc2626;"><?= count($orders ?? []) ?></div>
        <div style="color: #64748b;">Total Orders</div>
      </div>
      <div class="card" style="padding: 24px; text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: #dc2626;">0</div>
        <div style="color: #64748b;">Pending Quotes</div>
      </div>
      <div class="card" style="padding: 24px; text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: #dc2626;">0</div>
        <div style="color: #64748b;">Active Shipments</div>
      </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Recent Orders</h3>
      </div>
      <?php if (empty($orders)): ?>
      <div class="card-body text-center" style="padding: 48px;">
        <div style="font-size: 3rem; margin-bottom: 16px;">📦</div>
        <h4>No orders yet</h4>
        <p style="color: #64748b; margin-bottom: 24px;">Start shopping to see your orders here.</p>
        <a href="/catalog" class="btn btn-primary">Browse Products</a>
      </div>
      <?php else: ?>
      <div style="overflow-x: auto;">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order</th>
              <th>Date</th>
              <th>Total</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
              <td style="font-weight: 600;"><?= htmlspecialchars($order['order_number']) ?></td>
              <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
              <td><?= format_price((float)$order['total']) ?></td>
              <td>
                <span class="order-status-badge status-<?= str_replace('_', '-', $order['status']) ?>">
                  <?= get_status_label($order['status']) ?>
                </span>
              </td>
              <td>
                <a href="/order/<?= $order['id'] ?>" class="btn btn-sm btn-outline">View</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
