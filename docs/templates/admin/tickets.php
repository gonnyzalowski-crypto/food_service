<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">🎫 Support Tickets</h1>
    <p class="admin-page-subtitle">Manage customer inquiries and support requests</p>
  </div>
</div>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px;">
  <?php
  $newCount = count(array_filter($tickets, fn($t) => $t['status'] === 'new'));
  $inProgressCount = count(array_filter($tickets, fn($t) => $t['status'] === 'in_progress'));
  $resolvedCount = count(array_filter($tickets, fn($t) => $t['status'] === 'resolved'));
  $closedCount = count(array_filter($tickets, fn($t) => $t['status'] === 'closed'));
  ?>
  <a href="/admin/tickets?status=new" class="stat-card" style="text-decoration: none; color: inherit;">
    <div class="stat-icon" style="background: #fee2e2;">🆕</div>
    <div class="stat-content">
      <div class="stat-value"><?= $newCount ?></div>
      <div class="stat-label">New</div>
    </div>
  </a>
  <a href="/admin/tickets?status=in_progress" class="stat-card" style="text-decoration: none; color: inherit;">
    <div class="stat-icon" style="background: #fef3c7;">⏳</div>
    <div class="stat-content">
      <div class="stat-value"><?= $inProgressCount ?></div>
      <div class="stat-label">In Progress</div>
    </div>
  </a>
  <a href="/admin/tickets?status=resolved" class="stat-card" style="text-decoration: none; color: inherit;">
    <div class="stat-icon" style="background: #dcfce7;">✅</div>
    <div class="stat-content">
      <div class="stat-value"><?= $resolvedCount ?></div>
      <div class="stat-label">Resolved</div>
    </div>
  </a>
  <a href="/admin/tickets" class="stat-card" style="text-decoration: none; color: inherit;">
    <div class="stat-icon" style="background: #e0e7ff;">📊</div>
    <div class="stat-content">
      <div class="stat-value"><?= count($tickets) ?></div>
      <div class="stat-label">Total</div>
    </div>
  </a>
</div>

<!-- Filter -->
<?php if (!empty($currentStatus)): ?>
<div class="alert alert-info mb-3">
  Showing tickets with status: <strong><?= ucfirst(str_replace('_', ' ', $currentStatus)) ?></strong>
  <a href="/admin/tickets" style="margin-left: 16px;">Show all</a>
</div>
<?php endif; ?>

<!-- Tickets Table -->
<div class="card">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h3 class="card-title">All Tickets</h3>
    <input type="text" id="searchTickets" placeholder="Search tickets..." class="form-control" style="width: 250px;">
  </div>
  <div class="card-body" style="padding: 0;">
    <?php if (empty($tickets)): ?>
    <div style="padding: 48px; text-align: center; color: #64748b;">
      <div style="font-size: 3rem; margin-bottom: 16px;">🎫</div>
      <p>No support tickets yet</p>
    </div>
    <?php else: ?>
    <table class="data-table">
      <thead>
        <tr>
          <th>Ticket #</th>
          <th>Subject</th>
          <th>Customer</th>
          <th>Email</th>
          <th>Status</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tickets as $ticket): ?>
        <tr>
          <td>
            <div style="font-weight: 600; font-family: monospace;">
              <?= htmlspecialchars($ticket['ticket_number']) ?>
            </div>
          </td>
          <td>
            <div style="font-weight: 500;"><?= ucfirst(htmlspecialchars($ticket['subject'])) ?></div>
            <div style="font-size: 0.85rem; color: #64748b; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
              <?= htmlspecialchars(substr($ticket['message'], 0, 50)) ?>...
            </div>
          </td>
          <td>
            <div style="font-weight: 500;"><?= htmlspecialchars($ticket['name']) ?></div>
            <?php if ($ticket['company']): ?>
            <div style="font-size: 0.85rem; color: #64748b;"><?= htmlspecialchars($ticket['company']) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <a href="mailto:<?= htmlspecialchars($ticket['email']) ?>" style="color: #2563eb;">
              <?= htmlspecialchars($ticket['email']) ?>
            </a>
          </td>
          <td>
            <?php
            $statusColors = [
              'new' => 'background: #fee2e2; color: #dc2626;',
              'in_progress' => 'background: #fef3c7; color: #d97706;',
              'resolved' => 'background: #dcfce7; color: #16a34a;',
              'closed' => 'background: #e2e8f0; color: #64748b;',
            ];
            $statusStyle = $statusColors[$ticket['status']] ?? '';
            ?>
            <span style="<?= $statusStyle ?> padding: 4px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 500;">
              <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
            </span>
          </td>
          <td>
            <div><?= date('M j, Y', strtotime($ticket['created_at'])) ?></div>
            <div style="font-size: 0.85rem; color: #64748b;"><?= date('g:i A', strtotime($ticket['created_at'])) ?></div>
          </td>
          <td>
            <a href="/admin/tickets/<?= $ticket['id'] ?>" class="btn btn-sm btn-primary">View</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script>
document.getElementById('searchTickets')?.addEventListener('input', function(e) {
  const search = e.target.value.toLowerCase();
  document.querySelectorAll('.data-table tbody tr').forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(search) ? '' : 'none';
  });
});
</script>
