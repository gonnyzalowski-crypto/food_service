<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">📈 Reports</h1>
    <p class="admin-page-subtitle">Sales analytics and business insights</p>
  </div>
  <div>
    <button class="btn btn-outline" onclick="window.print()">🖨️ Print Report</button>
  </div>
</div>

<!-- Summary Stats -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px;">
  <?php
  $totalRevenue = array_sum(array_column($salesByMonth, 'revenue'));
  $totalOrders = array_sum(array_column($salesByMonth, 'order_count'));
  $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
  $topProduct = $topProducts[0] ?? null;
  ?>
  <div class="stat-card">
    <div class="stat-icon" style="background: #dcfce7;">💰</div>
    <div class="stat-content">
      <div class="stat-value"><?= format_price($totalRevenue) ?></div>
      <div class="stat-label">Total Revenue</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background: #dbeafe;">📦</div>
    <div class="stat-content">
      <div class="stat-value"><?= $totalOrders ?></div>
      <div class="stat-label">Total Orders</div>
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
    <div class="stat-icon" style="background: #f3e8ff;">🏆</div>
    <div class="stat-content">
      <div class="stat-value" style="font-size: 1rem;"><?= htmlspecialchars($topProduct['sku'] ?? 'N/A') ?></div>
      <div class="stat-label">Top Product</div>
    </div>
  </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
  <!-- Sales by Month -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">📅 Monthly Sales</h3>
    </div>
    <div class="card-body" style="padding: 0;">
      <?php if (empty($salesByMonth)): ?>
      <div style="padding: 48px; text-align: center; color: #64748b;">
        <p>No sales data yet</p>
      </div>
      <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>Month</th>
            <th>Orders</th>
            <th>Revenue</th>
            <th>Trend</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $prevRevenue = 0;
          foreach ($salesByMonth as $i => $month): 
            $trend = $i < count($salesByMonth) - 1 ? ($month['revenue'] > $salesByMonth[$i + 1]['revenue'] ? 'up' : 'down') : 'neutral';
          ?>
          <tr>
            <td style="font-weight: 600;">
              <?= date('F Y', strtotime($month['month'] . '-01')) ?>
            </td>
            <td>
              <span style="background: #dbeafe; color: #1d4ed8; padding: 4px 12px; border-radius: 12px;">
                <?= (int)$month['order_count'] ?>
              </span>
            </td>
            <td style="font-weight: 600;"><?= format_price((float)$month['revenue']) ?></td>
            <td>
              <?php if ($trend === 'up'): ?>
              <span style="color: #22c55e;">📈 Up</span>
              <?php elseif ($trend === 'down'): ?>
              <span style="color: #ef4444;">📉 Down</span>
              <?php else: ?>
              <span style="color: #64748b;">➡️ Stable</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
  
  <!-- Orders by Status -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">📊 Orders by Status</h3>
    </div>
    <div class="card-body">
      <?php if (empty($ordersByStatus)): ?>
      <div style="text-align: center; color: #64748b; padding: 24px;">
        <p>No orders yet</p>
      </div>
      <?php else: ?>
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <?php 
        $statusColors = [
          'awaiting_payment' => '#f59e0b',
          'payment_uploaded' => '#3b82f6',
          'payment_confirmed' => '#22c55e',
          'processing' => '#8b5cf6',
          'shipped' => '#06b6d4',
          'delivered' => '#10b981',
          'cancelled' => '#ef4444',
        ];
        $statusIcons = [
          'awaiting_payment' => '⏳',
          'payment_uploaded' => '📤',
          'payment_confirmed' => '✅',
          'processing' => '⚙️',
          'shipped' => '🚚',
          'delivered' => '📦',
          'cancelled' => '❌',
        ];
        $total = array_sum(array_column($ordersByStatus, 'count'));
        foreach ($ordersByStatus as $status): 
          $pct = $total > 0 ? ($status['count'] / $total) * 100 : 0;
          $color = $statusColors[$status['status']] ?? '#64748b';
          $icon = $statusIcons[$status['status']] ?? '📋';
        ?>
        <div>
          <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
            <span style="display: flex; align-items: center; gap: 8px;">
              <?= $icon ?> <?= ucfirst(str_replace('_', ' ', $status['status'])) ?>
            </span>
            <span style="font-weight: 600;"><?= (int)$status['count'] ?></span>
          </div>
          <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
            <div style="height: 100%; width: <?= $pct ?>%; background: <?= $color ?>; border-radius: 4px;"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Top Products -->
<div class="card mt-4">
  <div class="card-header">
    <h3 class="card-title">🏆 Top Selling Products</h3>
  </div>
  <div class="card-body" style="padding: 0;">
    <?php if (empty($topProducts)): ?>
    <div style="padding: 48px; text-align: center; color: #64748b;">
      <p>No product sales data yet</p>
    </div>
    <?php else: ?>
    <table class="data-table">
      <thead>
        <tr>
          <th>Rank</th>
          <th>Product</th>
          <th>SKU</th>
          <th>Units Sold</th>
          <th>Revenue</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($topProducts as $i => $product): ?>
        <tr>
          <td>
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: <?= $i < 3 ? '#fef3c7' : '#f1f5f9' ?>; border-radius: 50%; font-weight: 700;">
              <?php if ($i === 0): ?>🥇<?php elseif ($i === 1): ?>🥈<?php elseif ($i === 2): ?>🥉<?php else: echo $i + 1; endif; ?>
            </span>
          </td>
          <td style="font-weight: 500;"><?= htmlspecialchars($product['product_name']) ?></td>
          <td style="font-family: monospace;"><?= htmlspecialchars($product['sku']) ?></td>
          <td>
            <span style="background: #dbeafe; color: #1d4ed8; padding: 4px 12px; border-radius: 12px;">
              <?= (int)$product['total_qty'] ?>
            </span>
          </td>
          <td style="font-weight: 600;"><?= format_price((float)$product['total_revenue']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
