<div class="admin-header">
  <div>
    <h1 style="margin: 0;">Supply Requests</h1>
    <p style="margin: 4px 0 0 0; color: #64748b;">
      <?php if (!empty($currentStatus)): ?>
        Showing: <?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string)$currentStatus))) ?>
      <?php else: ?>
        All supply requests
      <?php endif; ?>
    </p>
  </div>
  <a href="/admin/supply-requests/new" class="btn btn-primary">+ New Supply Request</a>
</div>

<div class="card mb-4">
  <div class="card-body" style="display: flex; gap: 8px; flex-wrap: wrap;">
    <a href="/admin/supply-requests" class="btn <?= empty($currentStatus) ? 'btn-primary' : 'btn-outline' ?>">All</a>
    <a href="/admin/supply-requests?status=awaiting_review" class="btn <?= ($currentStatus ?? null) === 'awaiting_review' ? 'btn-primary' : 'btn-outline' ?>">Awaiting Review</a>
    <a href="/admin/supply-requests?status=approved_awaiting_payment" class="btn <?= ($currentStatus ?? null) === 'approved_awaiting_payment' ? 'btn-primary' : 'btn-outline' ?>">Awaiting Payment</a>
    <a href="/admin/supply-requests?status=payment_submitted_processing" class="btn <?= ($currentStatus ?? null) === 'payment_submitted_processing' ? 'btn-primary' : 'btn-outline' ?>">Payment Processing</a>
    <a href="/admin/supply-requests?status=transaction_completed" class="btn <?= ($currentStatus ?? null) === 'transaction_completed' ? 'btn-primary' : 'btn-outline' ?>">Completed</a>
    <a href="/admin/supply-requests?status=declined" class="btn <?= ($currentStatus ?? null) === 'declined' ? 'btn-primary' : 'btn-outline' ?>">Declined</a>
  </div>
</div>

<div class="card">
  <?php if (empty($requests)): ?>
  <div class="card-body text-center" style="padding: 64px;">
    <div style="font-size: 3rem; margin-bottom: 16px;">🛥️</div>
    <h3>No supply requests found</h3>
    <p style="color: #64748b;">No supply requests match this filter.</p>
  </div>
  <?php else: ?>
  <div style="overflow-x: auto;">
    <table class="data-table">
      <thead>
        <tr>
          <th>Request</th>
          <th>Date</th>
          <th>Contractor</th>
          <th>Delivery</th>
          <th>Price</th>
          <th>Discounted</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
        <?php
          $types = json_decode($r['supply_types'] ?? '[]', true) ?: [];
          $supplyLabel = implode(', ', array_map(fn($t) => str_replace('_', ' ', (string)$t), $types));
        ?>
        <tr>
          <td>
            <a href="/admin/supply-requests/<?= (int)$r['id'] ?>" style="font-weight: 700; color: #0284c7; text-decoration: none;">
              <?= htmlspecialchars($r['request_number']) ?>
            </a>
            <div style="font-size: 0.85rem; color: #64748b;">Crew: <?= (int)$r['crew_size'] ?>, <?= (int)$r['duration_days'] ?> days</div>
          </td>
          <td>
            <div><?= date('M j, Y', strtotime($r['created_at'])) ?></div>
            <div style="font-size: 0.85rem; color: #64748b;"><?= date('g:i A', strtotime($r['created_at'])) ?></div>
          </td>
          <td>
            <div style="font-weight: 600;"><?= htmlspecialchars($r['company_name'] ?? '') ?></div>
            <div style="font-size: 0.85rem; color: #64748b;"><?= htmlspecialchars($r['contractor_code'] ?? '') ?></div>
            <div style="font-size: 0.85rem; color: #64748b;"><?= htmlspecialchars($r['full_name'] ?? '') ?></div>
          </td>
          <td style="font-size: 0.9rem; color: #334155;">
            <div><?= htmlspecialchars(str_replace('_', ' ', (string)$r['delivery_location'])) ?></div>
            <div style="font-size: 0.85rem; color: #64748b;">
              <?= htmlspecialchars(str_replace('_', ' ', (string)$r['delivery_speed'])) ?>
              <?php if (!empty($supplyLabel)): ?>
                · <?= htmlspecialchars($supplyLabel) ?>
              <?php endif; ?>
            </div>
          </td>
          <?php $basePrice = isset($r['base_price']) && $r['base_price'] !== null ? (float)$r['base_price'] : (float)$r['calculated_price']; ?>
          <td style="font-weight: 600; color: #64748b;"><?= format_price($basePrice, (string)($r['currency'] ?? 'USD')) ?></td>
          <td style="font-weight: 700;"><?= format_price((float)$r['calculated_price'], (string)($r['currency'] ?? 'USD')) ?></td>
          <td>
            <span class="order-status-badge status-<?= htmlspecialchars(str_replace('_', '-', (string)$r['status'])) ?>">
              <?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string)$r['status']))) ?>
            </span>
          </td>
          <td>
            <a class="btn btn-sm btn-outline" href="/admin/supply-requests/<?= (int)$r['id'] ?>">View</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
