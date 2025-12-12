<div class="breadcrumb">
  <a href="/">Home</a> <span>/</span>
  <span>Contact Us</span>
</div>

<div class="page-header text-center">
  <h1 class="page-title">Contact Us</h1>
  <p class="page-subtitle">Get in touch with our team for sales inquiries, technical support, or general questions</p>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success mb-4" style="max-width: 800px; margin: 0 auto 32px;">
  <div class="alert-title">Message Sent Successfully!</div>
  <p style="margin: 4px 0 0 0;">Thank you for contacting us. Our team will respond within 24 hours.</p>
  <?php if (!empty($ticketNumber)): ?>
  <p style="margin: 8px 0 0 0;"><strong>Your ticket number:</strong> <?= htmlspecialchars($ticketNumber) ?></p>
  <?php endif; ?>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px; max-width: 1200px; margin: 0 auto;">
  <!-- Contact Form -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Send Us a Message</h3>
    </div>
    <div class="card-body">
      <form action="/contact" method="POST">
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input type="text" name="name" class="form-control" required placeholder="Your name">
        </div>
        <div class="form-group">
          <label class="form-label">Company Name</label>
          <input type="text" name="company" class="form-control" placeholder="Your company">
        </div>
        <div class="form-group">
          <label class="form-label">Email Address *</label>
          <input type="email" name="email" class="form-control" required placeholder="email@company.com">
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="tel" name="phone" class="form-control" placeholder="+49 123 456 7890">
        </div>
        <div class="form-group">
          <label class="form-label">Subject *</label>
          <select name="subject" class="form-control" required>
            <option value="">Select a subject</option>
            <option value="sales">Sales Inquiry</option>
            <option value="support">Technical Support</option>
            <option value="quote">Request Quote</option>
            <option value="order">Order Status</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Message *</label>
          <textarea name="message" class="form-control" rows="5" required placeholder="How can we help you?"></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-lg btn-block">Send Message</button>
      </form>
    </div>
  </div>
  
  <!-- Contact Info -->
  <div>
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Headquarters</h3>
      </div>
      <div class="card-body">
        <div style="display: flex; gap: 16px; margin-bottom: 20px;">
          <div style="font-size: 2rem;">🏢</div>
          <div>
            <div style="font-weight: 600;">Streicher GmbH</div>
            <div style="color: #64748b;">
              Industriestraße 45<br>
              93049 Regensburg<br>
              Germany
            </div>
          </div>
        </div>
        <div style="display: flex; gap: 16px; margin-bottom: 20px;">
          <div style="font-size: 2rem;">📞</div>
          <div>
            <div style="font-weight: 600;">Phone</div>
            <div style="color: #64748b;">+49 991 330-00</div>
          </div>
        </div>
        <div style="display: flex; gap: 16px;">
          <div style="font-size: 2rem;">✉️</div>
          <div>
            <div style="font-weight: 600;">Email</div>
            <div style="color: #64748b;">store@streichergmbh.com</div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Business Hours</h3>
      </div>
      <div class="card-body">
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
          <span>Monday - Friday</span>
          <span style="font-weight: 600;">8:00 AM - 6:00 PM CET</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
          <span>Saturday</span>
          <span style="font-weight: 600;">9:00 AM - 1:00 PM CET</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
          <span>Sunday</span>
          <span style="font-weight: 600;">Closed</span>
        </div>
        <div class="alert alert-info mt-3" style="margin-bottom: 0;">
          <strong>24/7 Emergency Support</strong> available for critical equipment issues.
        </div>
      </div>
    </div>
  </div>
</div>
