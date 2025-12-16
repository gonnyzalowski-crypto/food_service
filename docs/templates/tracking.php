<?php
// Determine progress step
$progressStep = 1;
$isCustomsHold = false;
if ($shipment) {
    switch ($shipment['status']) {
        case 'shipped': $progressStep = 2; break;
        case 'in_transit': $progressStep = 3; break;
        case 'customs_hold': 
            $progressStep = 3; 
            $isCustomsHold = true;
            break;
        case 'out_for_delivery': $progressStep = 4; break;
        case 'delivered': $progressStep = 5; break;
        default: $progressStep = 2;
    }
}

$statusBannerClass = 'in-transit';
$statusBannerText = 'In Transit to Destination';
if ($shipment) {
    switch ($shipment['status']) {
        case 'shipped':
            $statusBannerClass = 'in-transit';
            $statusBannerText = 'Shipment Picked Up';
            break;
        case 'in_transit':
            $statusBannerClass = 'in-transit';
            $statusBannerText = 'In Transit to Destination';
            break;
        case 'customs_hold':
            $statusBannerClass = 'customs-hold';
            $statusBannerText = '⚠️ On Hold: Customs Clearance Required';
            break;
        case 'out_for_delivery':
            $statusBannerClass = 'out-for-delivery';
            $statusBannerText = 'Out for Delivery';
            break;
        case 'delivered':
            $statusBannerClass = 'delivered';
            $statusBannerText = 'Delivered';
            break;
    }
}

// Get shipping method display
$shippingMethods = [
    'air_freight' => '✈️ Air Freight',
    'sea_freight' => '🚢 Sea Freight',
    'local_van' => '🚐 Local Van Delivery',
    'motorcycle' => '🏍️ Motorcycle Courier',
];
$shippingMethodDisplay = $shippingMethods[$shipment['shipping_method'] ?? 'air_freight'] ?? '✈️ Air Freight';

// Get package type display
$packageTypes = [
    'crate' => '📦 Industrial Crate',
    'carton' => '📦 Carton Box',
    'pallet' => '🏗️ Pallet',
    'container' => '🚛 Container',
];
$packageTypeDisplay = $packageTypes[$shipment['package_type'] ?? 'crate'] ?? '📦 Industrial Crate';
?>

<div class="tracking-container">
  <!-- Search Form -->
  <?php if (!$shipment): ?>
  <div class="card mb-4">
    <div class="card-header">
      <h3 class="card-title">Track Your Shipment</h3>
    </div>
    <div class="card-body">
      <form action="/track" method="GET">
        <div class="form-group">
          <label class="form-label">Tracking Number</label>
          <input type="text" name="tracking" class="form-control" placeholder="Enter your tracking number (e.g., STR20241205ABCD123456)" value="<?= htmlspecialchars($trackingNumber ?? '') ?>" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Track Shipment</button>
      </form>
      
      <?php if ($trackingNumber && !$shipment): ?>
      <div class="alert alert-error mt-3">
        <div class="alert-title">Shipment Not Found</div>
        <p style="margin: 4px 0 0 0;">
          We couldn't find a shipment with tracking number <strong><?= htmlspecialchars($trackingNumber) ?></strong>. 
          Please check the number and try again.
        </p>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
  
  <?php if ($shipment): ?>
  <!-- Tracking Header -->
  <div class="tracking-header">
    <div class="tracking-number-display" style="display: flex; align-items: center; gap: 8px;">
      <?= htmlspecialchars($shipment['tracking_number']) ?>
      <button type="button" onclick="copyToClipboard('<?= htmlspecialchars($shipment['tracking_number']) ?>', this)" style="background: rgba(255,255,255,0.2); border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; color: white; font-size: 0.75rem;">📋 Copy</button>
    </div>
    <div class="tracking-carrier"><?= htmlspecialchars($shipment['carrier'] ?? 'Gordon Food Service Logistics') ?></div>
  </div>
  
  <!-- Status Banner -->
  <div class="tracking-status-banner <?= $statusBannerClass ?>">
    <?= $statusBannerText ?>
  </div>
  
  <!-- Progress Bar -->
  <div class="tracking-progress">
    <div class="tracking-progress-bar" data-progress="<?= $progressStep ?>">
      <div class="progress-step <?= $progressStep >= 1 ? 'completed' : '' ?>">
        <div class="progress-step-icon">📦</div>
        <div class="progress-step-label">Order<br>Placed</div>
      </div>
      <div class="progress-step <?= $progressStep >= 2 ? 'completed' : '' ?> <?= $progressStep === 2 ? 'active' : '' ?>">
        <div class="progress-step-icon">🏭</div>
        <div class="progress-step-label">Shipped</div>
      </div>
      <div class="progress-step <?= $progressStep >= 3 ? 'completed' : '' ?> <?= $progressStep === 3 ? 'active' : '' ?>">
        <div class="progress-step-icon">🚚</div>
        <div class="progress-step-label">In<br>Transit</div>
      </div>
      <div class="progress-step <?= $progressStep >= 4 ? 'completed' : '' ?> <?= $progressStep === 4 ? 'active' : '' ?>">
        <div class="progress-step-icon">📍</div>
        <div class="progress-step-label">Out for<br>Delivery</div>
      </div>
      <div class="progress-step <?= $progressStep >= 5 ? 'completed' : '' ?> <?= $progressStep === 5 ? 'active' : '' ?>">
        <div class="progress-step-icon">✅</div>
        <div class="progress-step-label">Delivered</div>
      </div>
    </div>
    
    <!-- Shipment Details -->
    <div class="shipment-details-grid">
      <div>
        <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 4px;">Origin</div>
        <div style="font-weight: 600;"><?= htmlspecialchars($shipment['origin_city'] ?? 'Regensburg') ?>, <?= htmlspecialchars($shipment['origin_country'] ?? 'DE') ?></div>
      </div>
      <div>
        <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 4px;">Destination</div>
        <div style="font-weight: 600;">
          <?php 
            $destCity = $shipment['destination_city'] ?? '';
            $destCountry = $shipment['destination_country'] ?? '';
            if ($destCity && $destCountry) {
              echo htmlspecialchars($destCity) . ', ' . htmlspecialchars($destCountry);
            } elseif ($destCity) {
              echo htmlspecialchars($destCity);
            } elseif ($destCountry) {
              echo htmlspecialchars($destCountry);
            } else {
              echo 'Pending';
            }
          ?>
        </div>
      </div>
      <div>
        <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 4px;">Shipping Method</div>
        <div style="font-weight: 600;"><?= $shippingMethodDisplay ?></div>
      </div>
      <div>
        <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 4px;">Package Type</div>
        <div style="font-weight: 600;"><?= $packageTypeDisplay ?></div>
      </div>
      <div>
        <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 4px;">Estimated Delivery</div>
        <div style="font-weight: 600;">
          <?php if (!empty($shipment['estimated_delivery'])): ?>
            <?= date('F j, Y', strtotime($shipment['estimated_delivery'])) ?>
          <?php else: ?>
            Calculating...
          <?php endif; ?>
        </div>
      </div>
      <div>
        <div style="color: #64748b; font-size: 0.85rem; margin-bottom: 4px;">Carrier</div>
        <div style="font-weight: 600;">🚛 Gordon Food Service Logistics</div>
      </div>
    </div>
  </div>
  
  <!-- Customs Hold Alert -->
  <?php if ($isCustomsHold): ?>
  <div class="customs-alert">
    <div class="customs-alert-header">
      <span class="customs-alert-icon">⚠️</span>
      <h3>Customs Clearance Required</h3>
    </div>
    <div class="customs-alert-body">
      <?php if (!empty($shipment['customs_memo'])): ?>
      <div class="customs-memo">
        <strong>Customs Notice:</strong>
        <p><?= nl2br(htmlspecialchars($shipment['customs_memo'])) ?></p>
      </div>
      <?php endif; ?>
      
      <?php if (!empty($shipment['customs_duty_amount'])): ?>
      <div class="customs-duty">
        <strong>Duty Amount Due:</strong>
        <span class="duty-amount"><?= number_format($shipment['customs_duty_amount'], 2) ?> <?= htmlspecialchars($shipment['customs_duty_currency'] ?? 'EUR') ?></span>
      </div>
      <?php endif; ?>
      
      <p class="customs-instructions">
        Your shipment is being held by customs authorities. Please review the documents below and upload any required documentation to proceed with clearance.
      </p>
      
      <button type="button" class="btn btn-warning btn-lg" onclick="openCommunicationModal()">
        📋 View Details & Upload Documents
      </button>
    </div>
  </div>
  <?php endif; ?>
  
  <!-- Communication Button (always visible when shipment exists) -->
  <?php if ($shipment): ?>
  <div class="communication-section">
    <button type="button" class="btn btn-outline" onclick="openCommunicationModal()">
      💬 Messages & Documents
      <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
      <span class="badge badge-danger"><?= $unreadCount ?></span>
      <?php endif; ?>
    </button>
  </div>
  <?php endif; ?>
  
  <!-- Tracking Events (USPS Style) -->
  <div class="tracking-events">
    <h3 class="tracking-events-title">Tracking History</h3>
    
    <?php if (empty($events)): ?>
    <div style="text-align: center; padding: 32px; color: #64748b;">
      <div style="font-size: 2rem; margin-bottom: 8px;">📭</div>
      <p>No tracking events yet. Check back soon for updates.</p>
    </div>
    <?php else: ?>
    <?php foreach ($events as $index => $event): 
      $timestamp = strtotime($event['timestamp'] ?? $event['ts'] ?? 'now');
      $isLatest = $index === 0;
    ?>
    <div class="tracking-event">
      <div class="tracking-event-time">
        <div class="tracking-event-date"><?= date('M j, Y', $timestamp) ?></div>
        <div class="tracking-event-hour"><?= date('g:i A', $timestamp) ?></div>
      </div>
      <div class="tracking-event-dot" style="<?= $isLatest ? 'background: #16a34a;' : '' ?>"></div>
      <div class="tracking-event-content">
        <div class="tracking-event-status">
          <?= htmlspecialchars($event['description'] ?? $event['status_label'] ?? $event['status'] ?? 'Update') ?>
        </div>
        <div class="tracking-event-location">
          <?= htmlspecialchars($event['location'] ?? $event['location_city'] ?? '') ?>
          <?php if (!empty($event['facility']) || !empty($event['location_facility'])): ?>
            - <?= htmlspecialchars($event['facility'] ?? $event['location_facility']) ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
  
  <!-- Track Another -->
  <div class="card mt-4">
    <div class="card-body">
      <form action="/track" method="GET" style="display: flex; gap: 12px;">
        <input type="text" name="tracking" class="form-control" placeholder="Enter another tracking number" style="flex: 1;">
        <button type="submit" class="btn btn-primary">Track</button>
      </form>
    </div>
  </div>
  <?php endif; ?>
  
  <!-- Help Section -->
  <div class="card mt-4">
    <div class="card-body">
      <h4 style="margin: 0 0 16px 0;">Need Help?</h4>
      <div class="help-grid">
        <div>
          <div style="font-size: 1.5rem; margin-bottom: 8px;">✉️</div>
          <div style="font-weight: 500;">Email Support</div>
          <div style="color: #64748b; font-size: 0.9rem;">store@Gordon Food Servicegmbh.com</div>
        </div>
        <div>
          <div style="font-size: 1.5rem; margin-bottom: 8px;">💬</div>
          <div style="font-weight: 500;">Live Chat</div>
          <div style="color: #64748b; font-size: 0.9rem;">Available 24/7</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Communication Modal -->
<?php if ($shipment): ?>
<div id="communicationModal" class="modal" style="display: none;">
  <div class="modal-backdrop" onclick="closeCommunicationModal()"></div>
  <div class="modal-content modal-lg">
    <div class="modal-header">
      <h3>📋 Shipment Communication</h3>
      <button type="button" class="modal-close" onclick="closeCommunicationModal()">&times;</button>
    </div>
    <div class="modal-body">
      <div class="tracking-number-badge">
        Tracking: <?= htmlspecialchars($shipment['tracking_number']) ?>
      </div>
      
      <!-- Messages Thread -->
      <div class="messages-container" id="messagesContainer">
        <?php if (empty($communications)): ?>
        <div class="no-messages">
          <div style="font-size: 3rem; margin-bottom: 16px;">💬</div>
          <p>No messages yet. Start a conversation with our logistics team.</p>
        </div>
        <?php else: ?>
        <?php foreach ($communications as $comm): ?>
        <div class="message-item <?= $comm['sender_type'] ?>">
          <div class="message-header">
            <span class="message-sender">
              <?php if ($comm['sender_type'] === 'admin'): ?>
                🏢 Gordon Food Service Logistics
              <?php elseif ($comm['sender_type'] === 'system'): ?>
                🤖 System
              <?php else: ?>
                👤 You
              <?php endif; ?>
            </span>
            <span class="message-time"><?= date('M j, Y g:i A', strtotime($comm['created_at'])) ?></span>
          </div>
          
          <?php if (!empty($comm['document_path'])): ?>
          <div class="message-document">
            <a href="<?= htmlspecialchars($comm['document_path']) ?>" target="_blank" class="document-link">
              📄 <?= htmlspecialchars($comm['document_name']) ?>
            </a>
            <?php if (!empty($comm['message'])): ?>
            <p><?= nl2br(htmlspecialchars($comm['message'])) ?></p>
            <?php endif; ?>
          </div>
          <?php elseif (!empty($comm['message'])): ?>
          <div class="message-body">
            <?= nl2br(htmlspecialchars($comm['message'])) ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
      
      <!-- Send Message Form -->
      <div class="send-message-form">
        <form action="/api/tracking/<?= htmlspecialchars($shipment['tracking_number']) ?>/message" method="POST" enctype="multipart/form-data" id="messageForm">
          <div class="form-group">
            <textarea name="message" class="form-control" rows="3" placeholder="Type your message here..."></textarea>
          </div>
          <div class="form-actions">
            <label class="file-upload-btn">
              📎 Attach Document
              <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display: none;" onchange="showFileName(this)">
            </label>
            <span id="fileName" class="file-name"></span>
            <button type="submit" class="btn btn-primary">Send Message</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
/* Customs Alert Styles */
.customs-alert {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  border: 2px solid #f59e0b;
  border-radius: 12px;
  margin: 24px 0;
  overflow: hidden;
}

.customs-alert-header {
  background: #f59e0b;
  color: white;
  padding: 16px 24px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.customs-alert-header h3 {
  margin: 0;
  font-size: 1.25rem;
}

.customs-alert-icon {
  font-size: 1.5rem;
}

.customs-alert-body {
  padding: 24px;
}

.customs-memo {
  background: white;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 16px;
  border-left: 4px solid #f59e0b;
}

.customs-memo p {
  margin: 8px 0 0 0;
}

.customs-duty {
  background: white;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.duty-amount {
  font-size: 1.5rem;
  font-weight: 700;
  color: #dc2626;
}

.customs-instructions {
  color: #92400e;
  margin-bottom: 16px;
}

.tracking-status-banner.customs-hold {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

/* Communication Section */
.communication-section {
  margin: 24px 0;
  text-align: center;
}

/* Modal Styles */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-backdrop {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
}

.modal-content {
  position: relative;
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-content.modal-lg {
  max-width: 800px;
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #64748b;
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

.tracking-number-badge {
  background: #f1f5f9;
  padding: 8px 16px;
  border-radius: 8px;
  font-family: monospace;
  font-size: 0.9rem;
  margin-bottom: 24px;
  text-align: center;
}

/* Messages Container */
.messages-container {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 24px;
  background: #f8fafc;
}

.no-messages {
  text-align: center;
  padding: 32px;
  color: #64748b;
}

.message-item {
  background: white;
  border-radius: 8px;
  padding: 12px 16px;
  margin-bottom: 12px;
  border-left: 4px solid #e2e8f0;
}

.message-item.admin {
  border-left-color: #0066cc;
}

.message-item.customer {
  border-left-color: #16a34a;
}

.message-item.system {
  border-left-color: #f59e0b;
  background: #fffbeb;
}

.message-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-size: 0.85rem;
}

.message-sender {
  font-weight: 600;
}

.message-time {
  color: #64748b;
}

.message-body {
  color: #334155;
  line-height: 1.6;
}

.message-document {
  background: #f1f5f9;
  padding: 12px;
  border-radius: 6px;
}

.document-link {
  display: inline-block;
  padding: 8px 16px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  text-decoration: none;
  color: #0066cc;
  margin-bottom: 8px;
}

.document-link:hover {
  background: #f1f5f9;
}

/* Send Message Form */
.send-message-form {
  border-top: 1px solid #e2e8f0;
  padding-top: 16px;
}

.form-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 12px;
}

.file-upload-btn {
  padding: 8px 16px;
  background: #f1f5f9;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
}

.file-upload-btn:hover {
  background: #e2e8f0;
}

.file-name {
  color: #64748b;
  font-size: 0.85rem;
  flex: 1;
}

.btn-warning {
  background: #f59e0b;
  color: white;
  border: none;
}

.btn-warning:hover {
  background: #d97706;
}

.badge-danger {
  background: #dc2626;
  color: white;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  margin-left: 8px;
}
</style>

<script>
function openCommunicationModal() {
  document.getElementById('communicationModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeCommunicationModal() {
  document.getElementById('communicationModal').style.display = 'none';
  document.body.style.overflow = '';
}

function showFileName(input) {
  const fileName = input.files[0]?.name || '';
  document.getElementById('fileName').textContent = fileName;
}

// Handle message form submission
document.getElementById('messageForm')?.addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  
  try {
    const res = await fetch(this.action, {
      method: 'POST',
      body: formData
    });
    
    if (res.ok) {
      // Reload page to show new message
      window.location.reload();
    } else {
      alert('Failed to send message. Please try again.');
    }
  } catch (err) {
    console.error('Error:', err);
    alert('Failed to send message. Please try again.');
  }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeCommunicationModal();
  }
});
</script>
<?php endif; ?>
