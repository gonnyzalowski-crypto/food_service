<div class="admin-header">
  <div>
    <h1 style="margin: 0;">Orders</h1>
    <p style="margin: 4px 0 0 0; color: #64748b;">
      <?php if ($currentStatus): ?>
        Showing: <?= get_status_label($currentStatus) ?>
      <?php else: ?>
        All orders
      <?php endif; ?>
    </p>
  </div>
</div>

<!-- Status Filter -->
<div class="card mb-4">
  <div class="card-body" style="display: flex; gap: 8px; flex-wrap: wrap;">
    <a href="/admin/orders" class="btn <?= !$currentStatus ? 'btn-primary' : 'btn-outline' ?>">All</a>
    <a href="/admin/orders?status=awaiting_payment" class="btn <?= $currentStatus === 'awaiting_payment' ? 'btn-primary' : 'btn-outline' ?>">Awaiting Payment</a>
    <a href="/admin/orders?status=payment_uploaded" class="btn <?= $currentStatus === 'payment_uploaded' ? 'btn-primary' : 'btn-outline' ?>">Payment Uploaded</a>
    <a href="/admin/orders?status=payment_confirmed" class="btn <?= $currentStatus === 'payment_confirmed' ? 'btn-primary' : 'btn-outline' ?>">Ready to Ship</a>
    <a href="/admin/orders?status=shipped" class="btn <?= $currentStatus === 'shipped' ? 'btn-primary' : 'btn-outline' ?>">Shipped</a>
    <a href="/admin/orders?status=delivered" class="btn <?= $currentStatus === 'delivered' ? 'btn-primary' : 'btn-outline' ?>">Delivered</a>
  </div>
</div>

<!-- Orders Table -->
<div class="card">
  <?php if (empty($orders)): ?>
  <div class="card-body text-center" style="padding: 64px;">
    <div style="font-size: 3rem; margin-bottom: 16px;">📦</div>
    <h3>No orders found</h3>
    <p style="color: #64748b;">
      <?php if ($currentStatus): ?>
        No orders with status "<?= get_status_label($currentStatus) ?>".
      <?php else: ?>
        No orders have been placed yet.
      <?php endif; ?>
    </p>
  </div>
  <?php else: ?>
  <div style="overflow-x: auto;">
    <table class="data-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Date</th>
          <th>Customer</th>
          <th>Total</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): 
          $billing = json_decode($order['billing_address'] ?? '{}', true) ?: [];
        ?>
        <tr>
          <td>
            <a href="/admin/orders/<?= $order['id'] ?>" style="font-weight: 600; color: #0284c7; text-decoration: none;">
              <?= htmlspecialchars($order['order_number']) ?>
            </a>
          </td>
          <td>
            <div><?= date('M j, Y', strtotime($order['created_at'])) ?></div>
            <div style="font-size: 0.85rem; color: #64748b;"><?= date('g:i A', strtotime($order['created_at'])) ?></div>
          </td>
          <td>
            <div style="font-weight: 500;"><?= htmlspecialchars($billing['company'] ?? $billing['name'] ?? 'N/A') ?></div>
            <div style="font-size: 0.85rem; color: #64748b;"><?= htmlspecialchars($billing['email'] ?? '') ?></div>
          </td>
          <td style="font-weight: 600;"><?= format_price((float)$order['total']) ?></td>
          <td>
            <span class="order-status-badge status-<?= str_replace('_', '-', $order['status']) ?>">
              <?= get_status_label($order['status']) ?>
            </span>
          </td>
          <td>
            <div style="display: flex; gap: 8px;">
              <a href="/admin/orders/<?= $order['id'] ?>" class="btn btn-sm btn-outline">View</a>
              <?php if ($order['status'] === 'payment_uploaded'): ?>
              <form action="/admin/orders/<?= $order['id'] ?>/confirm-payment" method="POST" style="display: inline;">
                <button type="submit" class="btn btn-sm btn-success">Confirm Payment</button>
              </form>
              <?php elseif ($order['status'] === 'payment_confirmed'): ?>
              <a href="/admin/orders/<?= $order['id'] ?>#ship" class="btn btn-sm btn-primary">Ship</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
