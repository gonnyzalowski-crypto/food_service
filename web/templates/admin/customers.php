<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">👥 Customers</h1>
    <p class="admin-page-subtitle">Manage customer accounts and order history</p>
  </div>
</div>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px;">
  <?php
  $totalCustomers = count($customers);
  $totalRevenue = array_sum(array_column($customers, 'total_spent'));
  $avgOrderValue = $totalCustomers > 0 ? $totalRevenue / array_sum(array_column($customers, 'order_count')) : 0;
  $repeatCustomers = count(array_filter($customers, fn($c) => $c['order_count'] > 1));
  ?>
  <div class="stat-card">
    <div class="stat-icon" style="background: #dbeafe;">👥</div>
    <div class="stat-content">
      <div class="stat-value"><?= $totalCustomers ?></div>
      <div class="stat-label">Total Customers</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background: #dcfce7;">💰</div>
    <div class="stat-content">
      <div class="stat-value"><?= format_price($totalRevenue) ?></div>
      <div class="stat-label">Total Revenue</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background: #fef3c7;">📊</div>
    <div class="stat-content">
      <div class="stat-value"><?= format_price($avgOrderValue) ?></div>
      <div class="stat-label">Avg Order Value</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background: #f3e8ff;">🔄</div>
    <div class="stat-content">
      <div class="stat-value"><?= $repeatCustomers ?></div>
      <div class="stat-label">Repeat Customers</div>
    </div>
  </div>
</div>

<!-- Customers Table -->
<div class="card">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h3 class="card-title">All Customers</h3>
    <div style="display: flex; gap: 12px;">
      <input type="text" id="searchCustomers" placeholder="Search customers..." class="form-control" style="width: 250px;">
    </div>
  </div>
  <div class="card-body" style="padding: 0;">
    <?php if (empty($customers)): ?>
    <div style="padding: 48px; text-align: center; color: #64748b;">
      <div style="font-size: 3rem; margin-bottom: 16px;">👥</div>
      <p>No customers yet</p>
    </div>
    <?php else: ?>
    <table class="data-table">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Country</th>
          <th>Orders</th>
          <th>Total Spent</th>
          <th>Last Order</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($customers as $customer): ?>
        <tr>
          <td>
            <div style="display: flex; align-items: center; gap: 12px;">
              <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                <?= strtoupper(substr($customer['name'] ?? 'C', 0, 1)) ?>
              </div>
              <div>
                <div style="font-weight: 600;"><?= htmlspecialchars($customer['company'] ?? 'N/A') ?></div>
                <div style="font-size: 0.85rem; color: #64748b;"><?= htmlspecialchars($customer['name'] ?? '') ?></div>
              </div>
            </div>
          </td>
          <td>
            <a href="mailto:<?= htmlspecialchars($customer['email'] ?? '') ?>" style="color: #2563eb;">
              <?= htmlspecialchars($customer['email'] ?? 'N/A') ?>
            </a>
          </td>
          <td><?= htmlspecialchars($customer['phone'] ?? 'N/A') ?></td>
          <td>
            <span style="display: flex; align-items: center; gap: 6px;">
              <?php
              $flags = ['Germany' => '🇩🇪', 'Austria' => '🇦🇹', 'Switzerland' => '🇨🇭', 'Netherlands' => '🇳🇱', 'France' => '🇫🇷', 'United Kingdom' => '🇬🇧', 'United States' => '🇺🇸'];
              echo $flags[$customer['country']] ?? '🌍';
              ?>
              <?= htmlspecialchars($customer['country'] ?? 'N/A') ?>
            </span>
          </td>
          <td>
            <span style="background: #dbeafe; color: #1d4ed8; padding: 4px 12px; border-radius: 12px; font-weight: 600;">
              <?= (int)$customer['order_count'] ?>
            </span>
          </td>
          <td style="font-weight: 600;"><?= format_price((float)$customer['total_spent']) ?></td>
          <td>
            <div><?= date('M j, Y', strtotime($customer['last_order'])) ?></div>
            <div style="font-size: 0.85rem; color: #64748b;"><?= date('g:i A', strtotime($customer['last_order'])) ?></div>
          </td>
          <td>
            <a href="/admin/orders?email=<?= urlencode($customer['email'] ?? '') ?>" class="btn btn-sm btn-outline">View Orders</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script>
document.getElementById('searchCustomers')?.addEventListener('input', function(e) {
  const search = e.target.value.toLowerCase();
  document.querySelectorAll('.data-table tbody tr').forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(search) ? '' : 'none';
  });
});
</script>
