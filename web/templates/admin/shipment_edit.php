<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">✏️ Edit Shipment</h1>
    <p class="admin-page-subtitle">Tracking: <?= htmlspecialchars($shipment['tracking_number']) ?></p>
  </div>
  <div>
    <a href="/admin/shipments" class="btn btn-outline">← Back to Shipments</a>
  </div>
</div>

<!-- Shipment Basic Info -->
<div class="card" style="margin-bottom: 24px;">
  <div class="card-header">
    <h3 class="card-title">Shipment Information</h3>
  </div>
  <div class="card-body">
    <form method="POST" action="/admin/shipments/<?= $shipment['id'] ?>/update-info">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <div class="form-group">
          <label class="form-label">Tracking Number</label>
          <input type="text" name="tracking_number" class="form-control" value="<?= htmlspecialchars($shipment['tracking_number']) ?>" readonly>
        </div>
        
        <div class="form-group">
          <label class="form-label">Carrier</label>
          <select name="carrier" class="form-control">
            <option value="Streicher Logistics" <?= $shipment['carrier'] === 'Streicher Logistics' ? 'selected' : '' ?>>Streicher Logistics</option>
            <option value="DHL Express" <?= $shipment['carrier'] === 'DHL Express' ? 'selected' : '' ?>>DHL Express</option>
            <option value="FedEx" <?= $shipment['carrier'] === 'FedEx' ? 'selected' : '' ?>>FedEx</option>
            <option value="UPS" <?= $shipment['carrier'] === 'UPS' ? 'selected' : '' ?>>UPS</option>
            <option value="TNT" <?= $shipment['carrier'] === 'TNT' ? 'selected' : '' ?>>TNT</option>
            <option value="Maersk" <?= $shipment['carrier'] === 'Maersk' ? 'selected' : '' ?>>Maersk (Sea Freight)</option>
            <option value="Other" <?= $shipment['carrier'] === 'Other' ? 'selected' : '' ?>>Other</option>
          </select>
        </div>
      </div>
      
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="pending" <?= $shipment['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="shipped" <?= $shipment['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
            <option value="in_transit" <?= $shipment['status'] === 'in_transit' ? 'selected' : '' ?>>In Transit</option>
            <option value="customs_hold" <?= $shipment['status'] === 'customs_hold' ? 'selected' : '' ?>>Customs Hold</option>
            <option value="out_for_delivery" <?= $shipment['status'] === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
            <option value="delivered" <?= $shipment['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label">Shipped Date & Time</label>
          <input type="datetime-local" name="shipped_at" class="form-control" 
                 value="<?= $shipment['shipped_at'] ? date('Y-m-d\TH:i', strtotime($shipment['shipped_at'])) : '' ?>">
        </div>
      </div>
      
      <div style="display: flex; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #e2e8f0;">
        <button type="submit" class="btn btn-primary">Update Information</button>
      </div>
    </form>
  </div>
</div>

<!-- Tracking History -->
<div class="card">
  <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h3 class="card-title">Tracking History</h3>
    <button type="button" class="btn btn-primary btn-sm" onclick="showAddEventModal()">+ Add Event</button>
  </div>
  <div class="card-body">
    <?php 
    $events = json_decode($shipment['events'] ?? '[]', true) ?: [];
    if (empty($events)): 
    ?>
    <div style="padding: 48px; text-align: center; color: #64748b;">
      <div style="font-size: 3rem; margin-bottom: 16px;">📋</div>
      <p>No tracking events yet</p>
    </div>
    <?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 16px;">
      <?php foreach ($events as $index => $event): ?>
      <div class="tracking-event-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
          <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
              <span class="order-status-badge status-<?= strtolower($event['status'] ?? 'update') ?>">
                <?= htmlspecialchars($event['status'] ?? 'UPDATE') ?>
              </span>
              <span style="color: #64748b; font-size: 0.9rem;">
                <?= date('M j, Y g:i A', strtotime($event['timestamp'])) ?>
              </span>
            </div>
            <div style="font-weight: 500; margin-bottom: 4px;">
              <?= htmlspecialchars($event['description'] ?? '') ?>
            </div>
            <div style="color: #64748b; font-size: 0.9rem;">
              📍 <?= htmlspecialchars($event['location'] ?? 'N/A') ?>
              <?php if (!empty($event['facility'])): ?>
                • <?= htmlspecialchars($event['facility']) ?>
              <?php endif; ?>
            </div>
          </div>
          <div style="display: flex; gap: 8px;">
            <button type="button" class="btn btn-sm btn-outline" onclick="editEvent(<?= $index ?>)">Edit</button>
            <button type="button" class="btn btn-sm btn-outline" style="color: #dc2626;" onclick="deleteEvent(<?= $index ?>)">Delete</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add Event Modal -->
<div id="addEventModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: white; border-radius: 12px; padding: 32px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
    <h3 style="margin: 0 0 24px 0; font-size: 1.5rem;">Add Tracking Event</h3>
    <form method="POST" action="/admin/shipments/<?= $shipment['id'] ?>/add-event">
      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label">Status Code *</label>
        <select name="status_code" class="form-control" required>
          <option value="SHIPPED">SHIPPED - Picked up from origin</option>
          <option value="IN_TRANSIT">IN_TRANSIT - In transit</option>
          <option value="CUSTOMS">CUSTOMS - Customs clearance</option>
          <option value="CUSTOMS_HOLD">CUSTOMS_HOLD - Held by customs</option>
          <option value="CUSTOMS_CLEARED">CUSTOMS_CLEARED - Cleared customs</option>
          <option value="OUT_FOR_DELIVERY">OUT_FOR_DELIVERY - Out for delivery</option>
          <option value="DELIVERED">DELIVERED - Delivered</option>
          <option value="EXCEPTION">EXCEPTION - Exception/Delay</option>
          <option value="UPDATE">UPDATE - General update</option>
        </select>
      </div>
      
      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label">Description *</label>
        <textarea name="description" class="form-control" rows="3" required placeholder="e.g., Package arrived at sorting facility"></textarea>
      </div>
      
      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label">Location *</label>
        <input type="text" name="location" class="form-control" required placeholder="e.g., Frankfurt, Germany">
      </div>
      
      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label">Facility Name</label>
        <input type="text" name="facility" class="form-control" placeholder="e.g., DHL Hub Frankfurt">
      </div>
      
      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label">Event Date & Time *</label>
        <input type="datetime-local" name="timestamp" class="form-control" required value="<?= date('Y-m-d\TH:i') ?>">
      </div>
      
      <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-outline" onclick="hideAddEventModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Event</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Event Modal -->
<div id="editEventModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: white; border-radius: 12px; padding: 32px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
    <h3 style="margin: 0 0 24px 0; font-size: 1.5rem;">Edit Tracking Event</h3>
    <form method="POST" action="/admin/shipments/<?= $shipment['id'] ?>/edit-event">
      <input type="hidden" name="event_index" id="editEventIndex">
      
      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label">Status Code *</label>
        <select name="status_code" id="editStatusCode" class="form-control" required>
          <option value="SHIPPED">SHIPPED - Picked up from origin</option>
          <option value="IN_TRANSIT">IN_TRANSIT - In transit</option>
          <option value="CUSTOMS">CUSTOMS - Customs clearance</option>
          <option value="CUSTOMS_HOLD">CUSTOMS_HOLD - Held by customs</option>
          <option value="CUSTOMS_CLEARED">CUSTOMS_CLEARED - Cleared customs</option>
          <option value="OUT_FOR_DELIVERY">OUT_FOR_DELIVERY - Out for delivery</option>
          <option value="DELIVERED">DELIVERED - Delivered</option>
          <option value="EXCEPTION">EXCEPTION - Exception/Delay</option>
          <option value="UPDATE">UPDATE - General update</option>
        </select>
      </div>
      
      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label">Description *</label>
        <textarea name="description" id="editDescription" class="form-control" rows="3" required></textarea>
      </div>
      
      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label">Location *</label>
        <input type="text" name="location" id="editLocation" class="form-control" required>
      </div>
      
      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label">Facility Name</label>
        <input type="text" name="facility" id="editFacility" class="form-control">
      </div>
      
      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label">Event Date & Time *</label>
        <input type="datetime-local" name="timestamp" id="editTimestamp" class="form-control" required>
      </div>
      
      <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
        <button type="button" class="btn btn-outline" onclick="hideEditEventModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Event</button>
      </div>
    </form>
  </div>
</div>

<script>
const events = <?= json_encode($events) ?>;

function showAddEventModal() {
  document.getElementById('addEventModal').style.display = 'flex';
}

function hideAddEventModal() {
  document.getElementById('addEventModal').style.display = 'none';
}

function showEditEventModal() {
  document.getElementById('editEventModal').style.display = 'flex';
}

function hideEditEventModal() {
  document.getElementById('editEventModal').style.display = 'none';
}

function editEvent(index) {
  const event = events[index];
  document.getElementById('editEventIndex').value = index;
  document.getElementById('editStatusCode').value = event.status || 'UPDATE';
  document.getElementById('editDescription').value = event.description || '';
  document.getElementById('editLocation').value = event.location || '';
  document.getElementById('editFacility').value = event.facility || '';
  
  // Convert timestamp to datetime-local format
  const timestamp = new Date(event.timestamp);
  const year = timestamp.getFullYear();
  const month = String(timestamp.getMonth() + 1).padStart(2, '0');
  const day = String(timestamp.getDate()).padStart(2, '0');
  const hours = String(timestamp.getHours()).padStart(2, '0');
  const minutes = String(timestamp.getMinutes()).padStart(2, '0');
  document.getElementById('editTimestamp').value = `${year}-${month}-${day}T${hours}:${minutes}`;
  
  showEditEventModal();
}

function deleteEvent(index) {
  if (confirm('Are you sure you want to delete this tracking event?')) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/shipments/<?= $shipment['id'] ?>/delete-event';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'event_index';
    input.value = index;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
  }
}

// Close modals on background click
document.getElementById('addEventModal').addEventListener('click', function(e) {
  if (e.target === this) hideAddEventModal();
});

document.getElementById('editEventModal').addEventListener('click', function(e) {
  if (e.target === this) hideEditEventModal();
});
</script>
