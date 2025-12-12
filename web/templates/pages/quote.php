<div class="breadcrumb">
  <a href="/">Home</a> <span>/</span>
  <span>Request Quote</span>
</div>

<div class="page-header text-center">
  <h1 class="page-title">Request a Quote</h1>
  <p class="page-subtitle">Get customized pricing for your industrial equipment needs</p>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success mb-4" style="max-width: 800px; margin: 0 auto 32px;">
  <div class="alert-title">Quote Request Submitted!</div>
  <p style="margin: 4px 0 0 0;">Thank you for your inquiry. Our sales team will prepare a detailed quote and contact you within 24-48 hours.</p>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 48px; max-width: 1200px; margin: 0 auto;">
  <!-- Quote Form -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Quote Request Form</h3>
    </div>
    <div class="card-body">
      <form action="/quote" method="POST">
        <h4 style="margin: 0 0 16px 0; font-size: 1rem; color: #64748b;">Contact Information</h4>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Job Title</label>
            <input type="text" name="title" class="form-control">
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Company Name *</label>
          <input type="text" name="company" class="form-control" required>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div class="form-group">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Phone *</label>
            <input type="tel" name="phone" class="form-control" required>
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Country *</label>
          <select name="country" class="form-control" required>
            <option value="">Select country</option>
            <option value="Germany">Germany</option>
            <option value="United States">United States</option>
            <option value="United Kingdom">United Kingdom</option>
            <option value="Netherlands">Netherlands</option>
            <option value="France">France</option>
            <option value="Saudi Arabia">Saudi Arabia</option>
            <option value="UAE">United Arab Emirates</option>
            <option value="Other">Other</option>
          </select>
        </div>
        
        <hr style="margin: 24px 0; border: none; border-top: 1px solid #e2e8f0;">
        
        <h4 style="margin: 0 0 16px 0; font-size: 1rem; color: #64748b;">Product Requirements</h4>
        
        <?php if ($product): ?>
        <div class="alert alert-info mb-3">
          <strong>Selected Product:</strong> <?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['sku']) ?>)
          <input type="hidden" name="product_sku" value="<?= htmlspecialchars($product['sku']) ?>">
        </div>
        <?php endif; ?>
        
        <div class="form-group">
          <label class="form-label">Product Category *</label>
          <select name="category" class="form-control" required>
            <option value="">Select category</option>
            <option value="hydraulic">Hydraulic Systems</option>
            <option value="drilling">Drilling Equipment</option>
            <option value="pipeline">Pipeline Components</option>
            <option value="compressors">Compressors</option>
            <option value="pumps">Pumping Systems</option>
            <option value="safety">Safety Equipment</option>
            <option value="instrumentation">Instrumentation</option>
            <option value="spare-parts">Spare Parts</option>
            <option value="multiple">Multiple Categories</option>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label">Quantity</label>
          <input type="number" name="quantity" class="form-control" min="1" value="1">
        </div>
        
        <div class="form-group">
          <label class="form-label">Project Description *</label>
          <textarea name="description" class="form-control" rows="4" required placeholder="Describe your project requirements, specifications, and any special needs..."></textarea>
        </div>
        
        <div class="form-group">
          <label class="form-label">Required Delivery Date</label>
          <input type="date" name="delivery_date" class="form-control">
        </div>
        
        <div class="form-group">
          <label class="form-label">Budget Range</label>
          <select name="budget" class="form-control">
            <option value="">Prefer not to say</option>
            <option value="50000-100000">$50,000 - $100,000</option>
            <option value="100000-250000">$100,000 - $250,000</option>
            <option value="250000-500000">$250,000 - $500,000</option>
            <option value="500000+">$500,000+</option>
          </select>
        </div>
        
        <div class="form-group">
          <label class="form-label">Additional Notes</label>
          <textarea name="notes" class="form-control" rows="3" placeholder="Any additional information..."></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg btn-block">Submit Quote Request</button>
      </form>
    </div>
  </div>
  
  <!-- Sidebar -->
  <div>
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Why Request a Quote?</h3>
      </div>
      <div class="card-body">
        <ul style="margin: 0; padding-left: 20px; color: #475569;">
          <li style="margin-bottom: 12px;">Custom pricing for your specific requirements</li>
          <li style="margin-bottom: 12px;">Volume discounts for bulk orders</li>
          <li style="margin-bottom: 12px;">Technical consultation included</li>
          <li style="margin-bottom: 12px;">Flexible payment terms available</li>
          <li>Dedicated account manager</li>
        </ul>
      </div>
    </div>
    
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Quick Response</h3>
      </div>
      <div class="card-body">
        <div style="text-align: center; padding: 16px 0;">
          <div style="font-size: 3rem; margin-bottom: 8px;">⏱️</div>
          <div style="font-size: 1.5rem; font-weight: 700;">24-48 Hours</div>
          <div style="color: #64748b;">Average response time</div>
        </div>
      </div>
    </div>
    
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Need Help?</h3>
      </div>
      <div class="card-body">
        <p style="margin: 0 0 16px 0; color: #64748b;">
          Our sales team is ready to assist you with your quote request.
        </p>
        <div style="margin-bottom: 12px;">
          <strong>📞 Sales Hotline</strong><br>
          <span style="color: #64748b;">+49 (0) 941 123 4570</span>
        </div>
        <div>
          <strong>✉️ Email</strong><br>
          <span style="color: #64748b;">store@streichergmbh.com</span>
        </div>
      </div>
    </div>
  </div>
</div>
