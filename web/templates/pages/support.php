<div class="breadcrumb">
  <a href="/">Home</a> <span>/</span>
  <span>Technical Support</span>
</div>

<div class="page-header text-center">
  <h1 class="page-title">Technical Support</h1>
  <p class="page-subtitle">Expert assistance for all your industrial equipment needs</p>
</div>

<!-- Support Options -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 48px;">
  <div class="card text-center" style="padding: 32px;">
    <div style="font-size: 3rem; margin-bottom: 16px;">📞</div>
    <h3 style="margin: 0 0 8px 0;">Phone Support</h3>
    <p style="color: #64748b; margin-bottom: 16px;">Speak directly with our technical experts</p>
    <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 16px;">+49 (0) 941 123 4571</div>
    <div style="color: #64748b; font-size: 0.9rem;">Mon-Fri: 8AM - 6PM CET</div>
  </div>
  
  <div class="card text-center" style="padding: 32px;">
    <div style="font-size: 3rem; margin-bottom: 16px;">✉️</div>
    <h3 style="margin: 0 0 8px 0;">Email Support</h3>
    <p style="color: #64748b; margin-bottom: 16px;">Get detailed technical assistance via email</p>
    <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 16px;">store@streichergmbh.com</div>
    <div style="color: #64748b; font-size: 0.9rem;">Response within 24 hours</div>
  </div>
  
  <div class="card text-center" style="padding: 32px; background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white;">
    <div style="font-size: 3rem; margin-bottom: 16px;">🚨</div>
    <h3 style="margin: 0 0 8px 0;">Emergency Support</h3>
    <p style="margin-bottom: 16px; opacity: 0.9;">24/7 critical equipment support</p>
    <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 16px;">+49 (0) 941 123 4599</div>
    <div style="font-size: 0.9rem; opacity: 0.9;">Available 24/7/365</div>
  </div>
</div>

<!-- Support Services -->
<div class="card mb-4">
  <div class="card-header">
    <h3 class="card-title">Our Support Services</h3>
  </div>
  <div class="card-body">
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 32px;">
      <div>
        <h4 style="display: flex; align-items: center; gap: 12px; margin: 0 0 12px 0;">
          <span style="font-size: 1.5rem;">🔧</span> Installation Support
        </h4>
        <p style="color: #64748b; margin: 0;">
          Expert guidance for equipment installation, commissioning, and startup. Our engineers can provide remote assistance or on-site support.
        </p>
      </div>
      <div>
        <h4 style="display: flex; align-items: center; gap: 12px; margin: 0 0 12px 0;">
          <span style="font-size: 1.5rem;">🛠️</span> Maintenance Support
        </h4>
        <p style="color: #64748b; margin: 0;">
          Preventive maintenance schedules, troubleshooting guides, and repair assistance to keep your equipment running at peak performance.
        </p>
      </div>
      <div>
        <h4 style="display: flex; align-items: center; gap: 12px; margin: 0 0 12px 0;">
          <span style="font-size: 1.5rem;">📋</span> Technical Documentation
        </h4>
        <p style="color: #64748b; margin: 0;">
          Access to operation manuals, technical specifications, spare parts catalogs, and maintenance procedures.
        </p>
      </div>
      <div>
        <h4 style="display: flex; align-items: center; gap: 12px; margin: 0 0 12px 0;">
          <span style="font-size: 1.5rem;">🎓</span> Training Programs
        </h4>
        <p style="color: #64748b; margin: 0;">
          Comprehensive training for operators and maintenance personnel, available on-site or at our training facility.
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Submit Support Request -->
<div class="card mb-4">
  <div class="card-header">
    <h3 class="card-title">Submit a Support Request</h3>
  </div>
  <div class="card-body">
    <form action="/support" method="POST">
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
        <div class="form-group">
          <label class="form-label">Name *</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Company *</label>
          <input type="text" name="company" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email *</label>
          <input type="email" name="email" class="form-control" required>
        </div>
      </div>
      
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div class="form-group">
          <label class="form-label">Equipment Model/SKU</label>
          <input type="text" name="equipment" class="form-control" placeholder="e.g., HYD-PWR-5000">
        </div>
        <div class="form-group">
          <label class="form-label">Priority</label>
          <select name="priority" class="form-control">
            <option value="low">Low - General inquiry</option>
            <option value="medium">Medium - Issue affecting operations</option>
            <option value="high">High - Equipment down</option>
            <option value="critical">Critical - Safety concern</option>
          </select>
        </div>
      </div>
      
      <div class="form-group">
        <label class="form-label">Describe Your Issue *</label>
        <textarea name="issue" class="form-control" rows="5" required placeholder="Please describe the issue in detail, including any error messages or symptoms..."></textarea>
      </div>
      
      <button type="submit" class="btn btn-primary btn-lg">Submit Support Request</button>
    </form>
  </div>
</div>

<!-- Resources -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Self-Service Resources</h3>
  </div>
  <div class="card-body">
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;">
      <a href="/faq" style="text-decoration: none; color: inherit; text-align: center; padding: 24px; background: #f8fafc; border-radius: 8px;">
        <div style="font-size: 2rem; margin-bottom: 8px;">❓</div>
        <div style="font-weight: 600;">FAQ</div>
        <div style="font-size: 0.85rem; color: #64748b;">Common questions</div>
      </a>
      <a href="#" style="text-decoration: none; color: inherit; text-align: center; padding: 24px; background: #f8fafc; border-radius: 8px;">
        <div style="font-size: 2rem; margin-bottom: 8px;">📚</div>
        <div style="font-weight: 600;">Documentation</div>
        <div style="font-size: 0.85rem; color: #64748b;">Technical manuals</div>
      </a>
      <a href="#" style="text-decoration: none; color: inherit; text-align: center; padding: 24px; background: #f8fafc; border-radius: 8px;">
        <div style="font-size: 2rem; margin-bottom: 8px;">🎥</div>
        <div style="font-weight: 600;">Video Tutorials</div>
        <div style="font-size: 0.85rem; color: #64748b;">How-to guides</div>
      </a>
      <a href="/catalog?category=spare-parts" style="text-decoration: none; color: inherit; text-align: center; padding: 24px; background: #f8fafc; border-radius: 8px;">
        <div style="font-size: 2rem; margin-bottom: 8px;">⚙️</div>
        <div style="font-weight: 600;">Spare Parts</div>
        <div style="font-size: 0.85rem; color: #64748b;">Order parts online</div>
      </a>
    </div>
  </div>
</div>
