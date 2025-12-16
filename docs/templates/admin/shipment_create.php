<div class="admin-page-header">
  <div>
    <h1 class="admin-page-title">📦 Create Manual Shipment</h1>
    <p class="admin-page-subtitle">Create a shipment without linking to an order</p>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Shipment Details</h3>
  </div>
  <div class="card-body">
    <form method="POST" action="/admin/shipments/create">
      
      <!-- Basic Information -->
      <div style="margin-bottom: 32px;">
        <h4 style="margin-bottom: 16px; font-size: 1.1rem; color: #1e293b;">Basic Information</h4>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
          <div class="form-group">
            <label class="form-label">Tracking Number</label>
            <input type="text" name="tracking_number" class="form-control" placeholder="Auto-generated if empty">
            <small style="color: #64748b;">Leave empty to auto-generate</small>
          </div>
          
          <div class="form-group">
            <label class="form-label">Carrier *</label>
            <select name="carrier" class="form-control" required>
              <option value="Streicher Logistics">Streicher Logistics</option>
              <option value="DHL Express">DHL Express</option>
              <option value="FedEx">FedEx</option>
              <option value="UPS">UPS</option>
              <option value="TNT">TNT</option>
              <option value="Maersk">Maersk (Sea Freight)</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
          <div class="form-group">
            <label class="form-label">Shipping Method *</label>
            <select name="shipping_method" class="form-control" required>
              <option value="air_freight">Air Freight - International Express</option>
              <option value="sea_freight">Sea Freight - Heavy Cargo</option>
              <option value="local_van">Local Van Delivery</option>
              <option value="motorcycle">Motorcycle Courier Express</option>
            </select>
          </div>
          
          <div class="form-group">
            <label class="form-label">Package Type *</label>
            <select name="package_type" class="form-control" required>
              <option value="crate">Wooden Crate</option>
              <option value="pallet">Pallet</option>
              <option value="box">Cardboard Box</option>
              <option value="container">Shipping Container</option>
            </select>
          </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div class="form-group">
            <label class="form-label">Status *</label>
            <select name="status" class="form-control" required>
              <option value="pending">Pending</option>
              <option value="shipped">Shipped</option>
              <option value="in_transit">In Transit</option>
              <option value="customs_hold">Customs Hold</option>
              <option value="out_for_delivery">Out for Delivery</option>
              <option value="delivered">Delivered</option>
            </select>
          </div>
          
          <div class="form-group">
            <label class="form-label">Shipped Date & Time</label>
            <input type="datetime-local" name="shipped_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
          </div>
        </div>
      </div>
      
      <!-- Customer Information -->
      <div style="margin-bottom: 32px;">
        <h4 style="margin-bottom: 16px; font-size: 1.1rem; color: #1e293b;">Customer Information</h4>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
          <div class="form-group">
            <label class="form-label">Customer Name *</label>
            <input type="text" name="customer_name" class="form-control" required>
          </div>
          
          <div class="form-group">
            <label class="form-label">Customer Email</label>
            <input type="email" name="customer_email" class="form-control">
          </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div class="form-group">
            <label class="form-label">Customer Phone</label>
            <input type="text" name="customer_phone" class="form-control">
          </div>
          
          <div class="form-group">
            <label class="form-label">Company Name</label>
            <input type="text" name="customer_company" class="form-control">
          </div>
        </div>
      </div>
      
      <!-- Location Information -->
      <div style="margin-bottom: 32px;">
        <h4 style="margin-bottom: 16px; font-size: 1.1rem; color: #1e293b;">Location Information</h4>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
          <div class="form-group">
            <label class="form-label">Origin City</label>
            <input type="text" name="origin_city" class="form-control" value="Regensburg">
          </div>
          
          <div class="form-group">
            <label class="form-label">Origin Country</label>
            <input type="text" name="origin_country" class="form-control" value="DE" maxlength="2">
          </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div class="form-group">
            <label class="form-label">Destination City *</label>
            <input type="text" name="destination_city" class="form-control" required>
          </div>
          
          <div class="form-group">
            <label class="form-label">Destination Country *</label>
            <input type="text" name="destination_country" class="form-control" required maxlength="2" placeholder="e.g., US, GB, FR">
          </div>
        </div>
      </div>
      
      <!-- Initial Tracking Event -->
      <div style="margin-bottom: 32px;">
        <h4 style="margin-bottom: 16px; font-size: 1.1rem; color: #1e293b;">Initial Tracking Event</h4>
        
        <div class="form-group" style="margin-bottom: 20px;">
          <label class="form-label">Event Description</label>
          <textarea name="initial_description" class="form-control" rows="3" placeholder="e.g., Shipment picked up from warehouse">Shipment created and ready for dispatch</textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div class="form-group">
            <label class="form-label">Event Location</label>
            <input type="text" name="initial_location" class="form-control" value="Regensburg, Germany">
          </div>
          
          <div class="form-group">
            <label class="form-label">Facility Name</label>
            <input type="text" name="initial_facility" class="form-control" value="Streicher Logistics Center">
          </div>
        </div>
      </div>
      
      <!-- Additional Notes -->
      <div style="margin-bottom: 32px;">
        <h4 style="margin-bottom: 16px; font-size: 1.1rem; color: #1e293b;">Additional Information</h4>
        
        <div class="form-group">
          <label class="form-label">Internal Notes</label>
          <textarea name="notes" class="form-control" rows="3" placeholder="Internal notes (not visible to customer)"></textarea>
        </div>
      </div>
      
      <!-- Actions -->
      <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e2e8f0;">
        <a href="/admin/shipments" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary">Create Shipment</button>
      </div>
    </form>
  </div>
</div>
