<?php
$contractor = $contractor ?? null;
$requests = $requests ?? [];
$error = $error ?? null;
$info = $info ?? null;
$showAll = (bool)($showAll ?? false);
?>

<div class="page-header">
  <h1 class="page-title">Supply Portal</h1>
  <p class="page-subtitle">Enter your contractor code to view supply history and request offshore provisioning.</p>
</div>

<?php if ($error): ?>
<div class="alert alert-error mb-4">
  <div class="alert-title">Access denied</div>
  <p style="margin: 4px 0 0 0;"><?= htmlspecialchars((string)$error) ?></p>
</div>
<?php endif; ?>

<?php if ($info): ?>
<div class="alert alert-info mb-4">
  <div class="alert-title">Info</div>
  <p style="margin: 4px 0 0 0;"><?= htmlspecialchars((string)$info) ?></p>
</div>
<?php endif; ?>

<?php if (!$contractor): ?>
<div class="card" style="max-width: 640px;">
  <div class="card-header">
    <h3 class="card-title">Enter Contractor Code</h3>
  </div>
  <div class="card-body">
    <form action="/supply/code" method="POST">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label">Contractor Code</label>
        <input type="text" name="contractor_code" class="form-control" placeholder="e.g., GFS-XXXX-XXXX" required>
      </div>
      <button class="btn btn-primary btn-lg" type="submit" style="width: 100%;">Access Supply Portal</button>
    </form>
    <div style="margin-top: 16px; color: #64748b; font-size: 0.9rem;">
      No code? Contact Gordon Food Service to register your contractor account.
    </div>
  </div>
</div>
<?php else: ?>
<div class="card mb-4">
  <div class="card-body" style="display: flex; justify-content: space-between; gap: 16px; align-items: center; flex-wrap: wrap;">
    <div>
      <div style="font-weight: 700; font-size: 1.1rem;"><?= htmlspecialchars($contractor['company_name'] ?? '') ?></div>
      <div style="color: #64748b;">Contractor: <?= htmlspecialchars($contractor['full_name'] ?? '') ?></div>
      <div style="color: #64748b; font-size: 0.9rem;">Code: <span style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;"><?= htmlspecialchars($contractor['contractor_code'] ?? '') ?></span></div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
      <?php if (!empty($contractor['discount_eligible'])): ?>
        <span class="order-status-badge" style="background: #dcfce7; color: #166534;">Discount: <?= htmlspecialchars((string)($contractor['discount_percent'] ?? '0')) ?>%</span>
      <?php endif; ?>
      <a class="btn btn-outline" href="/supply/logout">Exit</a>
    </div>
  </div>
</div>

<!-- Supply Metrics -->
<?php
$totalRequests = count($requests);
$completedRequests = count(array_filter($requests, fn($r) => in_array($r['status'] ?? '', ['transaction_completed', 'completed', 'shipped'])));
$pendingRequests = count(array_filter($requests, fn($r) => in_array($r['status'] ?? '', ['awaiting_review', 'approved_awaiting_payment', 'payment_submitted_processing'])));
$totalSpent = array_sum(array_map(fn($r) => in_array($r['status'] ?? '', ['transaction_completed', 'completed', 'shipped']) ? (float)($r['calculated_price'] ?? 0) : 0, $requests));

// Calculate monthly spending for the chart (last 6 months)
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01', strtotime("-$i months"));
    $monthEnd = date('Y-m-t', strtotime("-$i months"));
    $monthLabel = date('M', strtotime("-$i months"));
    $monthTotal = 0;
    foreach ($requests as $r) {
        if (in_array($r['status'] ?? '', ['transaction_completed', 'completed', 'shipped'])) {
            $completedDate = date('Y-m-d', strtotime($r['completed_at'] ?? $r['created_at']));
            if ($completedDate >= $monthStart && $completedDate <= $monthEnd) {
                $monthTotal += (float)($r['calculated_price'] ?? 0);
            }
        }
    }
    $monthlyData[] = ['label' => $monthLabel, 'value' => $monthTotal];
}
$maxValue = max(array_column($monthlyData, 'value')) ?: 1;
?>

<div class="card mb-4">
  <div class="card-header">
    <h3 class="card-title">Supply Metrics</h3>
  </div>
  <div class="card-body">
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
      <div style="background: rgba(255,255,255,0.05); border-radius: 12px; padding: 16px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: 700; color: #00bfff;"><?= $totalRequests ?></div>
        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">Total Requests</div>
      </div>
      <div style="background: rgba(255,255,255,0.05); border-radius: 12px; padding: 16px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: 700; color: #4ade80;"><?= $completedRequests ?></div>
        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">Completed</div>
      </div>
      <div style="background: rgba(255,255,255,0.05); border-radius: 12px; padding: 16px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: 700; color: #fbbf24;"><?= $pendingRequests ?></div>
        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">In Progress</div>
      </div>
      <div style="background: rgba(255,255,255,0.05); border-radius: 12px; padding: 16px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: 700; color: #00bfff;"><?= format_price($totalSpent, 'USD') ?></div>
        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">Total Spent</div>
      </div>
    </div>
    
    <!-- Smooth Area Chart -->
    <div style="margin-top: 16px;">
      <div style="font-size: 0.9rem; color: rgba(255,255,255,0.6); margin-bottom: 12px;">Monthly Spending (Last 6 Months)</div>
      <div style="position: relative; height: 120px; display: flex; align-items: flex-end; gap: 8px; padding-bottom: 24px;">
        <?php foreach ($monthlyData as $i => $month): ?>
        <?php $height = $maxValue > 0 ? ($month['value'] / $maxValue) * 100 : 0; ?>
        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px;">
          <div style="width: 100%; background: linear-gradient(180deg, #00bfff 0%, rgba(0,191,255,0.3) 100%); border-radius: 8px 8px 0 0; height: <?= max($height, 4) ?>%; min-height: 4px; transition: height 0.3s ease;"></div>
          <div style="font-size: 0.75rem; color: rgba(255,255,255,0.5); position: absolute; bottom: 0;"><?= $month['label'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 24px; align-items: start;">
  <div>
    <div class="card">
      <div class="card-header" style="display:flex; justify-content: space-between; align-items:center;">
        <h3 class="card-title">Supply History</h3>
        <div style="font-size: 0.9rem; color: #64748b;">
          <?= count($requests) ?> request<?= count($requests) !== 1 ? 's' : '' ?>
        </div>
      </div>
      <div class="card-body" style="padding: 0;">
        <?php if (empty($requests)): ?>
          <div style="padding: 32px; color: #64748b;">No supply requests found.</div>
        <?php else: ?>
          <table class="data-table">
            <thead>
              <tr>
                <th>Request</th>
                <th>Supplies</th>
                <th>Delivery</th>
                <th>Dates</th>
                <th>Price</th>
                <th>Discounted Price</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($requests as $r): ?>
              <tr>
                <td>
                  <div style="font-weight: 600;"><?= htmlspecialchars($r['request_number']) ?></div>
                  <div style="font-size: 0.85rem; color: rgba(255,255,255,0.5);">Crew: <?= (int)$r['crew_size'] ?>, <?= (int)$r['duration_days'] ?> days</div>
                </td>
                <td style="font-size: 0.9rem; color: rgba(255,255,255,0.7);">
                  <?php
                    $types = json_decode($r['supply_types'] ?? '[]', true) ?: [];
                    echo htmlspecialchars(implode(', ', array_map(fn($t) => str_replace('_', ' ', (string)$t), $types)));
                  ?>
                </td>
                <td style="font-size: 0.9rem; color: rgba(255,255,255,0.7);">
                  <div><?= htmlspecialchars(str_replace('_', ' ', $r['delivery_location'])) ?></div>
                  <div style="font-size: 0.85rem; color: rgba(255,255,255,0.5);"><?= htmlspecialchars(str_replace('_', ' ', $r['delivery_speed'])) ?></div>
                </td>
                <td style="font-size: 0.9rem; color: rgba(255,255,255,0.7);">
                  <div><?= $r['effective_date'] ? htmlspecialchars($r['effective_date']) : '—' ?></div>
                </td>
                <?php $basePrice = isset($r['base_price']) && $r['base_price'] !== null ? (float)$r['base_price'] : (float)$r['calculated_price']; ?>
                <td style="font-weight: 700; color: rgba(255,255,255,0.9);"><?= format_price($basePrice, (string)($r['currency'] ?? 'USD')) ?></td>
                <td style="font-weight: 800; color: #00bfff;"><?= format_price((float)$r['calculated_price'], (string)($r['currency'] ?? 'USD')) ?></td>
                <td>
                  <span class="order-status-badge status-<?= htmlspecialchars(str_replace('_', '-', (string)$r['status'])) ?>">
                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', (string)$r['status']))) ?>
                  </span>
                </td>
                <td>
                  <?php if (($r['status'] ?? '') === 'approved_awaiting_payment'): ?>
                    <button type="button" class="btn btn-sm btn-primary" onclick="openSupplyPaymentModal(<?= (int)$r['id'] ?>, <?= htmlspecialchars(json_encode((string)$r['request_number']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode(format_price((float)$r['calculated_price'], (string)($r['currency'] ?? 'USD'))), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode((string)($r['payment_instructions'] ?? '')), ENT_QUOTES) ?>)">Pay</button>
                  <?php elseif (($r['status'] ?? '') === 'payment_submitted_processing'): ?>
                    <span style="color: rgba(255,255,255,0.5); font-size: 0.9rem;">Processing</span>
                  <?php elseif (($r['status'] ?? '') === 'awaiting_review'): ?>
                    <span style="color: rgba(255,255,255,0.5); font-size: 0.9rem;">Waiting</span>
                  <?php elseif (in_array($r['status'] ?? '', ['transaction_completed', 'completed', 'shipped'])): ?>
                    <button type="button" onclick="openThankYouModal()" style="background: none; border: none; color: #4ade80; font-weight: 700; font-size: 0.9rem; cursor: pointer; text-decoration: underline;">Completed</button>
                  <?php elseif (($r['status'] ?? '') === 'declined'): ?>
                    <span style="color: #f87171; font-weight: 700; font-size: 0.9rem;">Declined</span>
                  <?php else: ?>
                    <span style="color: rgba(255,255,255,0.5); font-size: 0.9rem;">—</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div>
    <form class="card" action="/supply/request" method="POST">
      <div class="card-header">
        <h3 class="card-title">New Supply Request</h3>
      </div>
      <div class="card-body">
        <?= csrf_field() ?>

        <div class="form-group">
          <label class="form-label">Duration (days)</label>
          <input type="number" name="duration_days" class="form-control" min="14" value="14" required>
        </div>

        <div class="form-group">
          <label class="form-label">Crew Size</label>
          <input type="number" name="crew_size" class="form-control" min="1" value="10" required>
        </div>

        <div class="form-group">
          <label class="form-label">Supply Types & Quantities (kg)</label>
          <p style="font-size: 0.85rem; color: rgba(255,255,255,0.6); margin: 0 0 12px 0;">Enter quantity in kilograms for each supply type you need. Prices shown are per kg.</p>
          <div style="display: grid; gap: 12px;">
            <?php 
            $supplyPrices = $supplyPrices ?? [];
            foreach ($supplyPrices as $key => $item): 
              $itemName = $item['name'] ?? ucwords(str_replace('_', ' ', $key));
              $itemPrice = (float)($item['price'] ?? 0);
            ?>
            <div style="display: grid; grid-template-columns: 1fr auto auto; gap: 12px; align-items: center; background: rgba(255,255,255,0.05); padding: 12px; border-radius: 8px;">
              <div>
                <div style="font-weight: 600;"><?= htmlspecialchars($itemName) ?></div>
                <div style="font-size: 0.85rem; color: #00bfff;">$<?= number_format($itemPrice, 2) ?>/kg</div>
              </div>
              <input type="number" name="supply_quantities[<?= htmlspecialchars($key) ?>]" class="form-control" style="width: 100px;" min="0" step="0.1" value="0" placeholder="kg" oninput="updatePriceEstimate()">
              <input type="hidden" name="supply_types[]" value="<?= htmlspecialchars($key) ?>">
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Delivery Location</label>
          <select name="delivery_location" class="form-control" required>
            <option value="onshore">Onshore</option>
            <option value="offshore_rig">Offshore Rig</option>
            <option value="nearshore">Nearshore</option>
            <option value="pickup">Pickup</option>
            <option value="local">Local</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Delivery Speed</label>
          <select name="delivery_speed" class="form-control" required>
            <option value="standard">Standard</option>
            <option value="priority">Priority</option>
            <option value="emergency">Emergency</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Storage Life (months)</label>
          <input type="number" name="storage_life_months" class="form-control" min="1" value="6">
        </div>

        <div class="form-group">
          <label class="form-label">Effective Supply Date (optional)</label>
          <input type="date" name="effective_date" class="form-control">
        </div>

        <div class="form-group">
          <label class="form-label">Notes (optional)</label>
          <textarea name="notes" class="form-control" rows="3" placeholder="Rig name, dock instructions, crew constraints, etc."></textarea>
        </div>

        <div style="background: #1a1a1a; border: 1px solid #333; border-radius: 12px; padding: 16px; margin: 0;">
          <div style="color: #00bfff; font-weight: 600; font-size: 1rem; margin-bottom: 8px;">Estimated Price</div>
          <div id="priceEstimate" style="margin: 0;">
            <p style="margin: 0; color: rgba(255,255,255,0.6);">Enter quantities above to see price estimate.</p>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary btn-lg btn-block">Submit Supply Request</button>
      </div>
    </form>
  </div>
</div>

<div id="supplyPaymentModal" class="modal" style="display: none;">
  <div class="modal-backdrop" onclick="closeSupplyPaymentModal()" style="background: rgba(0,0,0,0.8);"></div>
  <div class="modal-content modal-lg" style="background: #1a1a1a; border: 1px solid #333; border-radius: 20px; max-width: 700px;">
    <div class="modal-header" style="border-bottom: 1px solid #333; padding: 20px 24px;">
      <h3 style="color: #00bfff; margin: 0; font-size: 1.5rem;">Payment Submission</h3>
      <button type="button" class="modal-close" onclick="closeSupplyPaymentModal()" style="background: rgba(255,255,255,0.1); border: none; color: #fff; width: 36px; height: 36px; border-radius: 50%; font-size: 1.2rem; cursor: pointer;">&times;</button>
    </div>
    <div class="modal-body" style="padding: 24px; background: #1a1a1a;">
      <div style="background: rgba(0,191,255,0.1); border: 1px solid rgba(0,191,255,0.3); border-radius: 10px; padding: 16px; margin-bottom: 20px;">
        <div style="color: #00bfff; font-weight: 600; margin-bottom: 4px;">Submit your payment details</div>
        <p style="margin: 0; color: rgba(255,255,255,0.7); font-size: 0.9rem;">Our team will process your transaction and confirm when complete.</p>
      </div>

      <div id="supplyPaymentRequestBadge" style="background: #333; padding: 12px 16px; border-radius: 10px; font-family: monospace; font-size: 0.9rem; text-align: center; color: #00bfff; margin-bottom: 20px;"></div>

      <div id="supplyPaymentInstructions" style="display:none; background: rgba(0,191,255,0.1); border: 1px solid rgba(0,191,255,0.3); border-radius: 10px; padding: 16px; margin-bottom: 20px;">
        <div style="color: #00bfff; font-weight: 600; margin-bottom: 4px;">Instructions</div>
        <p id="supplyPaymentInstructionsText" style="margin: 0; color: rgba(255,255,255,0.7); white-space: pre-wrap;"></p>
      </div>

      <form method="POST" action="/supply/payment" id="supplyPaymentForm" class="dark-form" style="max-width: 100%; padding: 0; background: transparent; border: none;">
        <?= csrf_field() ?>
        <input type="hidden" name="supply_request_id" id="supply_request_id" value="">

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
          <div>
            <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">Billing Full Name</label>
            <input type="text" name="billing_name" class="dark-input" style="width: 100%; padding: 14px 12px;" required>
          </div>
          <div>
            <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">Phone</label>
            <input type="text" name="phone" class="dark-input" style="width: 100%; padding: 14px 12px;" required>
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
          <div>
            <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">Address Line 1</label>
            <input type="text" name="address_line1" class="dark-input" style="width: 100%; padding: 14px 12px;" required>
          </div>
          <div>
            <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">Address Line 2 (optional)</label>
            <input type="text" name="address_line2" class="dark-input" style="width: 100%; padding: 14px 12px;">
          </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
          <div>
            <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">City</label>
            <input type="text" name="address_city" class="dark-input" style="width: 100%; padding: 14px 12px;" required>
          </div>
          <div>
            <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">State</label>
            <input type="text" name="address_state" class="dark-input" style="width: 100%; padding: 14px 12px;" required>
          </div>
          <div>
            <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">ZIP</label>
            <input type="text" name="address_zip" class="dark-input" style="width: 100%; padding: 14px 12px;" required>
          </div>
        </div>

        <div style="margin-bottom: 12px;">
          <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">Country</label>
          <input type="text" name="address_country" class="dark-input" style="width: 100%; padding: 14px 12px;" required value="United States">
        </div>

        <div style="border-top: 1px solid #333; margin: 24px 0; padding-top: 24px;">
          <div style="color: #00bfff; font-weight: 600; margin-bottom: 16px;">💳 Card Details</div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
          <div>
            <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">Card Brand</label>
            <select name="card_brand" class="dark-select" style="width: 100%; padding: 14px 12px;">
              <option value="">Select…</option>
              <option value="visa">Visa</option>
              <option value="mastercard">Mastercard</option>
              <option value="amex">American Express</option>
              <option value="discover">Discover</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div>
            <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">Name on Card</label>
            <input type="text" name="card_name" class="dark-input" style="width: 100%; padding: 14px 12px;" required>
          </div>
        </div>

        <div style="margin-bottom: 12px;">
          <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">Card Number</label>
          <input type="text" name="card_number" class="dark-input" style="width: 100%; padding: 14px 12px;" inputmode="numeric" autocomplete="cc-number" required>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 20px;">
          <div>
            <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">Exp Month</label>
            <input type="number" name="exp_month" class="dark-input" style="width: 100%; padding: 14px 12px;" min="1" max="12" required>
          </div>
          <div>
            <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">Exp Year</label>
            <input type="number" name="exp_year" class="dark-input" style="width: 100%; padding: 14px 12px;" min="<?= (int)date('Y') ?>" max="<?= (int)date('Y') + 25 ?>" required>
          </div>
          <div>
            <label style="display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 6px;">CVV</label>
            <input type="password" name="cvv" class="dark-input" style="width: 100%; padding: 14px 12px;" inputmode="numeric" autocomplete="cc-csc" required>
          </div>
        </div>

        <div style="display:flex; gap: 12px; justify-content: flex-end;">
          <button type="button" class="dark-btn-outline" onclick="closeSupplyPaymentModal()">Cancel</button>
          <button type="submit" class="dark-btn" id="supplyPaymentSubmit">Submit Payment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openSupplyPaymentModal(requestId, requestNumber, priceLabel, instructions) {
  const modal = document.getElementById('supplyPaymentModal');
  const badge = document.getElementById('supplyPaymentRequestBadge');
  const instrBox = document.getElementById('supplyPaymentInstructions');
  const instrText = document.getElementById('supplyPaymentInstructionsText');

  document.getElementById('supply_request_id').value = requestId;
  badge.textContent = `Request: ${requestNumber} · Price: ${priceLabel}`;

  const instr = (typeof instructions === 'string') ? instructions.trim() : '';
  if (instr) {
    instrText.textContent = instr;
    instrBox.style.display = '';
  } else {
    instrText.textContent = '';
    instrBox.style.display = 'none';
  }

  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeSupplyPaymentModal() {
  const modal = document.getElementById('supplyPaymentModal');
  modal.style.display = 'none';
  document.body.style.overflow = '';
}

document.getElementById('supplyPaymentForm')?.addEventListener('submit', function() {
  const btn = document.getElementById('supplyPaymentSubmit');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Submitting…';
  }
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeSupplyPaymentModal();
    closeThankYouModal();
  }
});

function openThankYouModal() {
  document.getElementById('thankYouModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeThankYouModal() {
  document.getElementById('thankYouModal').style.display = 'none';
  document.body.style.overflow = '';
}

// Per-kg prices for live estimation (loaded from database)
const pricesPerKg = <?= json_encode(array_map(fn($item) => (float)($item['price'] ?? 0), $supplyPrices ?? [])) ?>;

// Location multipliers
const locationMultipliers = {
  pickup: 0.85,
  local: 0.95,
  onshore: 1.0,
  nearshore: 1.15,
  offshore_rig: 1.35
};

// Speed multipliers
const speedMultipliers = {
  standard: 1.0,
  priority: 1.2,
  emergency: 1.45
};

function updatePriceEstimate() {
  const quantities = {};
  document.querySelectorAll('input[name^="supply_quantities["]').forEach(input => {
    const match = input.name.match(/supply_quantities\[([^\]]+)\]/);
    if (match) {
      quantities[match[1]] = parseFloat(input.value) || 0;
    }
  });

  const location = document.querySelector('select[name="delivery_location"]')?.value || 'onshore';
  const speed = document.querySelector('select[name="delivery_speed"]')?.value || 'standard';
  const storageMonths = parseInt(document.querySelector('input[name="storage_life_months"]')?.value) || 6;

  let subtotal = 0;
  let breakdown = [];

  for (const [type, kg] of Object.entries(quantities)) {
    if (kg > 0 && pricesPerKg[type]) {
      const price = pricesPerKg[type];
      const itemTotal = kg * price;
      subtotal += itemTotal;
      breakdown.push(`${type.replace(/_/g, ' ')}: ${kg}kg × $${price.toFixed(2)} = $${itemTotal.toFixed(2)}`);
    }
  }

  if (subtotal <= 0) {
    document.getElementById('priceEstimate').innerHTML = '<p style="margin: 0; color: rgba(255,255,255,0.7);">Enter quantities above to see price estimate.</p>';
    return;
  }

  const locMult = locationMultipliers[location] || 1.0;
  const speedMult = speedMultipliers[speed] || 1.0;
  let storageMult = 1.0;
  if (storageMonths >= 12) storageMult = 1.1;
  else if (storageMonths >= 6) storageMult = 1.05;

  const basePrice = subtotal * locMult * speedMult * storageMult;
  const discountPercent = <?= json_encode((float)($contractor['discount_percent'] ?? 0)) ?>;
  const discountEligible = <?= json_encode(!empty($contractor['discount_eligible'])) ?>;
  const finalPrice = discountEligible && discountPercent > 0 ? basePrice * (1 - discountPercent / 100) : basePrice;

  let html = '<div style="font-size: 0.9rem;">';
  html += '<div style="margin-bottom: 10px;">';
  breakdown.forEach(item => {
    html += '<div style="color: #e0e0e0; margin-bottom: 4px;">' + item + '</div>';
  });
  html += '</div>';
  html += '<div style="border-top: 1px solid #444; padding-top: 10px;">';
  html += '<div style="color: #ffffff; margin-bottom: 4px;">Subtotal: <strong style="color: #00bfff;">$' + subtotal.toFixed(2) + '</strong></div>';
  if (locMult !== 1.0) html += '<div style="color: #b0b0b0; margin-bottom: 2px;">Location (' + location.replace(/_/g, ' ') + '): ×' + locMult.toFixed(2) + '</div>';
  if (speedMult !== 1.0) html += '<div style="color: #b0b0b0; margin-bottom: 2px;">Speed (' + speed + '): ×' + speedMult.toFixed(2) + '</div>';
  if (storageMult !== 1.0) html += '<div style="color: #b0b0b0; margin-bottom: 2px;">Storage (' + storageMonths + ' months): ×' + storageMult.toFixed(2) + '</div>';
  html += '<div style="font-size: 1.1rem; margin-top: 10px; color: #ffffff;">Base Price: <strong style="color: #ff9500;">$' + basePrice.toFixed(2) + '</strong></div>';
  if (discountEligible && discountPercent > 0) {
    html += '<div style="color: #4ade80; margin-top: 4px;">Discount (' + discountPercent + '%): -$' + (basePrice - finalPrice).toFixed(2) + '</div>';
    html += '<div style="font-size: 1.3rem; color: #00bfff; font-weight: 700; margin-top: 8px;">Final Price: $' + finalPrice.toFixed(2) + '</div>';
  }
  html += '</div></div>';

  document.getElementById('priceEstimate').innerHTML = html;
}

// Add event listeners for location and speed changes
document.querySelector('select[name="delivery_location"]')?.addEventListener('change', updatePriceEstimate);
document.querySelector('select[name="delivery_speed"]')?.addEventListener('change', updatePriceEstimate);
document.querySelector('input[name="storage_life_months"]')?.addEventListener('input', updatePriceEstimate);
</script>

<!-- Thank You Modal -->
<div id="thankYouModal" class="modal" style="display: none;">
  <div class="modal-backdrop" onclick="closeThankYouModal()" style="background: rgba(0,0,0,0.8);"></div>
  <div class="modal-content" style="background: #1a1a1a; border: 1px solid #333; border-radius: 20px; max-width: 500px; text-align: center;">
    <div class="modal-header" style="border-bottom: 1px solid #333; padding: 20px 24px;">
      <h3 style="color: #4ade80; margin: 0; font-size: 1.5rem;">✓ Order Complete</h3>
      <button type="button" class="modal-close" onclick="closeThankYouModal()" style="background: rgba(255,255,255,0.1); border: none; color: #fff; width: 36px; height: 36px; border-radius: 50%; font-size: 1.2rem; cursor: pointer;">&times;</button>
    </div>
    <div class="modal-body" style="padding: 32px 24px; background: #1a1a1a;">
      <div style="font-size: 4rem; margin-bottom: 16px;">🎉</div>
      <h4 style="color: #ffffff; font-size: 1.3rem; margin: 0 0 16px 0;">Thank You for Your Patronage!</h4>
      <p style="color: rgba(255,255,255,0.7); font-size: 1rem; line-height: 1.6; margin: 0 0 24px 0;">
        We appreciate your business. Your order has been successfully processed and is now being prepared for delivery.
      </p>
      <div style="background: rgba(0,191,255,0.1); border: 1px solid rgba(0,191,255,0.3); border-radius: 10px; padding: 16px; margin-bottom: 24px;">
        <p style="color: rgba(255,255,255,0.8); font-size: 0.95rem; margin: 0;">
          📧 <strong style="color: #00bfff;">Delivery instructions</strong> will be sent to your email within the next <strong>24 hours</strong>.
        </p>
      </div>
      <p style="color: rgba(255,255,255,0.5); font-size: 0.85rem; margin: 0;">
        If you have any questions, please contact us at <a href="mailto:contact@gordonfoods.com" style="color: #00bfff;">contact@gordonfoods.com</a>
      </p>
    </div>
    <div style="padding: 16px 24px; border-top: 1px solid #333;">
      <button type="button" class="dark-btn" onclick="closeThankYouModal()" style="width: 100%;">Close</button>
    </div>
  </div>
</div>

<!-- Floating Live Chat Button -->
<button id="liveChatBtn" onclick="toggleLiveChat()" style="position: fixed; bottom: 24px; right: 24px; width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #00bfff, #0099cc); border: none; cursor: pointer; box-shadow: 0 4px 20px rgba(0,191,255,0.4); z-index: 1000; display: flex; align-items: center; justify-content: center; transition: transform 0.2s, box-shadow 0.2s;">
  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
  </svg>
  <span id="chatUnreadBadge" style="display: none; position: absolute; top: -4px; right: -4px; background: #f43f5e; color: white; font-size: 0.7rem; font-weight: 700; padding: 2px 6px; border-radius: 10px; min-width: 18px; text-align: center;"></span>
</button>

<!-- Live Chat Modal -->
<div id="liveChatModal" style="display: none; position: fixed; bottom: 100px; right: 24px; width: 380px; max-width: calc(100vw - 48px); height: 500px; max-height: calc(100vh - 150px); background: #1a1a1a; border: 1px solid #333; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); z-index: 1001; display: none; flex-direction: column; overflow: hidden;">
  <div style="background: linear-gradient(135deg, #00bfff, #0099cc); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
      <div style="font-weight: 700; color: #fff; font-size: 1.1rem;">Live Support</div>
      <div style="font-size: 0.8rem; color: rgba(255,255,255,0.8);">We typically reply within minutes</div>
    </div>
    <button onclick="toggleLiveChat()" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1.2rem;">&times;</button>
  </div>
  
  <div id="chatMessagesContainer" style="flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px;">
    <div style="text-align: center; color: rgba(255,255,255,0.5); padding: 20px; font-size: 0.9rem;">
      Loading messages...
    </div>
  </div>
  
  <div style="border-top: 1px solid #333; padding: 12px;">
    <form id="chatSendForm" onsubmit="sendChatMessage(event)" style="display: flex; gap: 8px;">
      <input type="text" id="chatMessageInput" placeholder="Type a message..." style="flex: 1; background: #2a2a2a; border: 1px solid #444; border-radius: 20px; padding: 10px 16px; color: #fff; font-size: 0.9rem;" required>
      <button type="submit" style="background: #00bfff; border: none; color: #000; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="22" y1="2" x2="11" y2="13"></line>
          <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
      </button>
    </form>
  </div>
</div>

<script>
let chatOpen = false;
let chatPollInterval = null;

function toggleLiveChat() {
  const modal = document.getElementById('liveChatModal');
  chatOpen = !chatOpen;
  modal.style.display = chatOpen ? 'flex' : 'none';
  
  if (chatOpen) {
    loadChatMessages();
    chatPollInterval = setInterval(loadChatMessages, 5000);
    document.getElementById('chatMessageInput').focus();
  } else {
    if (chatPollInterval) clearInterval(chatPollInterval);
  }
}

function loadChatMessages() {
  fetch('/supply/chat/messages')
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        renderChatMessages(data.messages);
      }
    })
    .catch(err => console.error('Failed to load messages:', err));
}

function renderChatMessages(messages) {
  const container = document.getElementById('chatMessagesContainer');
  
  if (!messages || messages.length === 0) {
    container.innerHTML = `
      <div style="text-align: center; color: rgba(255,255,255,0.5); padding: 40px 20px;">
        <div style="font-size: 2rem; margin-bottom: 12px;">👋</div>
        <div>Welcome! How can we help you today?</div>
        <div style="font-size: 0.8rem; margin-top: 8px;">Messages are kept for 7 days.</div>
      </div>
    `;
    return;
  }
  
  let html = '';
  messages.forEach(msg => {
    const isAdmin = msg.sender === 'admin';
    const time = new Date(msg.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
    html += `
      <div style="display: flex; ${isAdmin ? 'justify-content: flex-start;' : 'justify-content: flex-end;'}">
        <div style="max-width: 80%; padding: 10px 14px; border-radius: 16px; ${isAdmin ? 'background: rgba(255,255,255,0.1); color: #fff;' : 'background: #00bfff; color: #000;'}">
          <div style="word-wrap: break-word; font-size: 0.9rem;">${escapeHtml(msg.message).replace(/\n/g, '<br>')}</div>
          <div style="font-size: 0.7rem; margin-top: 4px; opacity: 0.7;">${time}</div>
        </div>
      </div>
    `;
  });
  
  container.innerHTML = html;
  container.scrollTop = container.scrollHeight;
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function sendChatMessage(e) {
  e.preventDefault();
  const input = document.getElementById('chatMessageInput');
  const message = input.value.trim();
  
  if (!message) return;
  
  input.disabled = true;
  
  fetch('/supply/chat/send', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'message=' + encodeURIComponent(message)
  })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        input.value = '';
        loadChatMessages();
      } else {
        alert(data.error || 'Failed to send message');
      }
    })
    .catch(err => {
      console.error('Failed to send message:', err);
      alert('Failed to send message. Please try again.');
    })
    .finally(() => {
      input.disabled = false;
      input.focus();
    });
}

// Hover effect for chat button
document.getElementById('liveChatBtn').addEventListener('mouseenter', function() {
  this.style.transform = 'scale(1.1)';
  this.style.boxShadow = '0 6px 30px rgba(0,191,255,0.5)';
});
document.getElementById('liveChatBtn').addEventListener('mouseleave', function() {
  this.style.transform = 'scale(1)';
  this.style.boxShadow = '0 4px 20px rgba(0,191,255,0.4)';
});
</script>
<?php endif; ?>
