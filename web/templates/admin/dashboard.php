<div class="admin-header">
  <div>
    <h1 style="margin: 0;">Dashboard</h1>
    <p style="margin: 4px 0 0 0; color: #64748b;">Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></p>
  </div>
  <div>
    <?php if ((int)($stats['pending_requests'] ?? 0) > 0): ?>
    <a href="/admin/supply-requests?status=awaiting_review" class="btn btn-primary">
      Review Pending Requests (<?= (int)$stats['pending_requests'] ?>)
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-value"><?= number_format((int)($stats['total_requests'] ?? 0)) ?></div>
    <div class="stat-label">Total Supply Requests</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= (int)($stats['pending_requests'] ?? 0) ?></div>
    <div class="stat-label">Pending Requests</div>
    <?php if (($stats['pending_requests'] ?? 0) > 0): ?>
    <div class="stat-change" style="color: #ca8a04;">Requires attention</div>
    <?php endif; ?>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= format_price((float)($stats['total_revenue'] ?? 0)) ?></div>
    <div class="stat-label">Total Revenue</div>
  </div>
  <div class="stat-card">
    <div class="stat-value"><?= number_format((int)($stats['active_contractors'] ?? 0)) ?></div>
    <div class="stat-label">Active Contractors</div>
  </div>
</div>

<!-- Pending Requests Alert -->
<?php if (!empty($pendingRequests)): ?>
<div class="alert alert-warning mb-4">
  <div class="alert-title">🛥️ Supply Requests Awaiting Review</div>
  <p style="margin: 4px 0 0 0;">
    <?= count($pendingRequests) ?> supply request(s) need your review and approval.
    <a href="/admin/supply-requests?status=awaiting_review" style="margin-left: 8px;">Review Now →</a>
  </p>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
  <!-- Recent Supply Requests -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Recent Supply Requests</h3>
      <a href="/admin/supply-requests" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div style="overflow-x: auto;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Request</th>
            <th>Contractor</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($recentRequests)): ?>
          <?php foreach ($recentRequests as $req): ?>
          <tr>
            <td>
              <div style="font-weight: 500;"><?= htmlspecialchars($req['request_number']) ?></div>
            </td>
            <td style="font-size: 0.9rem;">
              <div><?= htmlspecialchars($req['company_name'] ?? 'Unknown') ?></div>
              <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem;"><?= htmlspecialchars($req['full_name'] ?? '') ?></div>
            </td>
            <td><?= date('M j, Y', strtotime($req['created_at'])) ?></td>
            <td style="font-weight: 600; color: #00bfff;"><?= format_price((float)$req['calculated_price']) ?></td>
            <td>
              <?php
              $statusLabels = [
                'awaiting_review' => 'Awaiting review',
                'approved_awaiting_payment' => 'Awaiting payment',
                'payment_submitted_processing' => 'Processing',
                'transaction_completed' => 'Completed',
                'completed' => 'Completed',
                'shipped' => 'Shipped',
                'declined' => 'Declined',
              ];
              $statusLabel = $statusLabels[$req['status']] ?? ucfirst(str_replace('_', ' ', $req['status']));
              ?>
              <span class="order-status-badge status-<?= str_replace('_', '-', $req['status']) ?>">
                <?= htmlspecialchars($statusLabel) ?>
              </span>
            </td>
            <td>
              <a href="/admin/supply-requests/<?= $req['id'] ?>" class="btn btn-sm btn-outline">View</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php else: ?>
          <tr>
            <td colspan="6" style="text-align: center; color: rgba(255,255,255,0.5); padding: 24px;">No supply requests yet</td>
          </tr>
          <?php endif; ?>
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
        <a href="/admin/supply-requests?status=awaiting_review" class="btn btn-block btn-primary mb-2">
          🛥️ Review Supply Requests
        </a>
        <a href="/admin/contractors" class="btn btn-block btn-outline mb-2">
          🏢 Manage Contractors
        </a>
        <a href="/admin/contractors/new" class="btn btn-block btn-outline mb-2">
          ➕ Add Contractor
        </a>
        <a href="/admin/live-chat" class="btn btn-block btn-outline">
          💬 Live Chat
        </a>
      </div>
    </div>
    
    <!-- Request Status Summary -->
    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title">Request Status</h3>
      </div>
      <div class="card-body">
        <div style="display: flex; flex-direction: column; gap: 12px;">
          <a href="/admin/supply-requests?status=awaiting_review" style="display: flex; justify-content: space-between; text-decoration: none; color: inherit;">
            <span>Awaiting Review</span>
            <span class="order-status-badge status-awaiting-review"><?= (int)($stats['status_awaiting_review'] ?? 0) ?></span>
          </a>
          <a href="/admin/supply-requests?status=approved_awaiting_payment" style="display: flex; justify-content: space-between; text-decoration: none; color: inherit;">
            <span>Awaiting Payment</span>
            <span class="order-status-badge status-approved-awaiting-payment"><?= (int)($stats['status_awaiting_payment'] ?? 0) ?></span>
          </a>
          <a href="/admin/supply-requests?status=payment_submitted_processing" style="display: flex; justify-content: space-between; text-decoration: none; color: inherit;">
            <span>Processing</span>
            <span class="order-status-badge status-payment-submitted-processing"><?= (int)($stats['status_processing'] ?? 0) ?></span>
          </a>
          <a href="/admin/supply-requests?status=transaction_completed" style="display: flex; justify-content: space-between; text-decoration: none; color: inherit;">
            <span>Completed</span>
            <span class="order-status-badge status-transaction-completed"><?= (int)($stats['status_completed'] ?? 0) ?></span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
