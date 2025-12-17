<div class="admin-header">
  <div>
    <h1 style="margin: 0;">Dashboard</h1>
    <p style="margin: 4px 0 0 0; color: #64748b;">Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></p>
  </div>
  <div>
    <a href="/admin/orders?status=payment_uploaded" class="btn btn-primary">
      Review Pending Payments (<?= (int)$stats['pending_payments'] ?>)
    </a>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-value"><?= number_format((int)$stats['total_orders']) ?></div>
    <div class="stat-label">Total Orders</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= (int)$stats['pending_payments'] ?></div>
    <div class="stat-label">Pending Payments</div>
    <?php if ($stats['pending_payments'] > 0): ?>
    <div class="stat-change" style="color: #ca8a04;">Requires attention</div>
    <?php endif; ?>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= format_price((float)$stats['total_revenue']) ?></div>
    <div class="stat-label">Total Revenue</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= number_format((int)$stats['total_products']) ?></div>
    <div class="stat-label">Active Products</div>
  </div>
</div>

<!-- Pending Payments Alert -->
<?php if (!empty($pendingPayments)): ?>
<div class="alert alert-warning mb-4">
  <div class="alert-title">⚠️ Payment Receipts Awaiting Review</div>
  <p style="margin: 4px 0 0 0;">
    <?= count($pendingPayments) ?> order(s) have uploaded payment receipts that need verification.
    <a href="/admin/orders?status=payment_uploaded" style="margin-left: 8px;">Review Now →</a>
  </p>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
  <!-- Recent Orders -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Recent Orders</h3>
      <a href="/admin/orders" class="btn btn-sm btn-outline">View All</a>
    </div>
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
          <?php foreach ($recentOrders as $order): ?>
          <tr>
            <td>
              <div style="font-weight: 500;"><?= htmlspecialchars($order['order_number']) ?></div>
            </td>
            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
            <td style="font-weight: 600;"><?= format_price((float)$order['total']) ?></td>
            <td>
              <span class="order-status-badge status-<?= str_replace('_', '-', $order['status']) ?>">
                <?= get_status_label($order['status']) ?>
              </span>
            </td>
            <td>
              <a href="/admin/orders/<?= $order['id'] ?>" class="btn btn-sm btn-outline">View</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  
  <!-- Quick Actions -->
  <div>
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
      </div>
      <div class="card-body">
        <a href="/admin/orders?status=payment_uploaded" class="btn btn-block btn-primary mb-2">
          💳 Review Payments
        </a>
        <a href="/admin/orders?status=payment_confirmed" class="btn btn-block btn-success mb-2">
          📦 Ship Orders
        </a>
        <a href="/admin/products/new" class="btn btn-block btn-outline mb-2">
          ➕ Add Product
        </a>
        <a href="/admin/live-chat" class="btn btn-block btn-outline">
          💬 Live Chat
        </a>
      </div>
    </div>
    
    <!-- Order Status Summary -->
    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title">Order Status</h3>
      </div>
      <div class="card-body">
        <?php
        $statusCounts = [
          'awaiting_payment' => 0,
          'payment_uploaded' => 0,
          'payment_confirmed' => 0,
          'shipped' => 0,
          'delivered' => 0,
        ];
        foreach ($recentOrders as $o) {
          if (isset($statusCounts[$o['status']])) {
            $statusCounts[$o['status']]++;
          }
        }
        ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
          <a href="/admin/orders?status=awaiting_payment" style="display: flex; justify-content: space-between; text-decoration: none; color: inherit;">
            <span>Awaiting Payment</span>
            <span class="order-status-badge status-awaiting-payment"><?= $statusCounts['awaiting_payment'] ?></span>
          </a>
          <a href="/admin/orders?status=payment_uploaded" style="display: flex; justify-content: space-between; text-decoration: none; color: inherit;">
            <span>Payment Uploaded</span>
            <span class="order-status-badge status-payment-uploaded"><?= $statusCounts['payment_uploaded'] ?></span>
          </a>
          <a href="/admin/orders?status=payment_confirmed" style="display: flex; justify-content: space-between; text-decoration: none; color: inherit;">
            <span>Ready to Ship</span>
            <span class="order-status-badge status-payment-confirmed"><?= $statusCounts['payment_confirmed'] ?></span>
          </a>
          <a href="/admin/orders?status=shipped" style="display: flex; justify-content: space-between; text-decoration: none; color: inherit;">
            <span>Shipped</span>
            <span class="order-status-badge status-shipped"><?= $statusCounts['shipped'] ?></span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
