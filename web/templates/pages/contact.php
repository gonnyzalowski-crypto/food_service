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

<style>
  @media (max-width: 768px) {
    .contact-grid { grid-template-columns: 1fr !important; }
    .contact-grid > div:first-child { order: 2; }
    .contact-grid > div:last-child { order: 1; }
  }
</style>
<div class="contact-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px; max-width: 1200px; margin: 0 auto;">
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
            <div style="font-weight: 600; color: #ffffff;">Gordon Food Service</div>
            <div style="color: rgba(255,255,255,0.6);">
              28th–36th St Port/Harborside industrial zone<br>
              Galveston, TX
            </div>
          </div>
        </div>
        <div style="display: flex; gap: 16px; margin-bottom: 20px;">
          <div style="font-size: 2rem;">📞</div>
          <div>
            <div style="font-weight: 600; color: #ffffff;">Phone</div>
            <div style="color: rgba(255,255,255,0.6);">+1 213-653-0266</div>
          </div>
        </div>
        <div style="display: flex; gap: 16px;">
          <div style="font-size: 2rem;">✉️</div>
          <div>
            <div style="font-weight: 600; color: #ffffff;">Email</div>
            <div style="color: rgba(255,255,255,0.6);">contact@gorfos.com</div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Business Hours</h3>
      </div>
      <div class="card-body">
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: rgba(255,255,255,0.8);">
          <span>Monday - Friday</span>
          <span style="font-weight: 600; color: #ffffff;">8:00 AM - 6:00 PM CST</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: rgba(255,255,255,0.8);">
          <span>Saturday</span>
          <span style="font-weight: 600; color: #ffffff;">9:00 AM - 1:00 PM CST</span>
        </div>
        <div style="display: flex; justify-content: space-between; color: rgba(255,255,255,0.8);">
          <span>Sunday</span>
          <span style="font-weight: 600; color: #ffffff;">Closed</span>
        </div>
        <div style="margin-top: 16px; padding: 12px 16px; background: rgba(0,191,255,0.1); border: 1px solid rgba(0,191,255,0.3); border-radius: 8px;">
          <strong style="color: #00bfff;">24/7 Emergency Support</strong> <span style="color: rgba(255,255,255,0.7);">available for critical supply issues.</span>
        </div>
      </div>
    </div>
  </div>
</div>
