<?php
$page_title = 'Contact Us - Sri Shenbaga Crackers';
require_once 'includes/header.php';
$sent = false;
if ($_SERVER['REQUEST_METHOD']==='POST') $sent = true;
?>
<div class="section">
    <div class="container">
        <div class="section-title"><h2>Contact Us</h2><p>Get in touch with us for orders and queries</p></div>
        <div class="contact-grid">
            <div class="contact-info-box">
                <h3>Get In Touch</h3>
                <div class="contact-info-item"><i class="fas fa-phone"></i><div><strong>Phone 1</strong><br><?php echo SITE_PHONE1; ?></div></div>
                <div class="contact-info-item"><i class="fas fa-phone"></i><div><strong>Phone 2</strong><br><?php echo SITE_PHONE2; ?></div></div>
                <div class="contact-info-item"><i class="fas fa-envelope"></i><div><strong>Email</strong><br><?php echo SITE_EMAIL; ?></div></div>
                <div class="contact-info-item"><i class="fas fa-map-marker-alt"></i><div><strong>Location</strong><br>Near Sattur Tollgate, Sattur - 626203</div></div>
                <div class="contact-info-item"><i class="fab fa-whatsapp"></i><div><strong>WhatsApp</strong><br><?php echo SITE_PHONE1; ?></div></div>
            </div>
            <div class="form-card admin-form">
                <?php if ($sent): ?>
                <div class="alert alert-success"><i class="fas fa-check"></i> Message sent! We'll contact you soon.</div>
                <?php endif; ?>
                <h2>Send Message</h2>
                <div class="form-row">
                    <div class="form-group"><label>Your Name</label><input type="text" name="name" placeholder="Full name"></div>
                    <div class="form-group"><label>Phone</label><input type="tel" name="phone" placeholder="Mobile number"></div>
                </div>
                <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="Email address"></div>
                <div class="form-group"><label>Message</label><textarea name="message" rows="5" placeholder="Your message..."></textarea></div>
                <button type="submit" class="btn btn-red w-100" style="justify-content:center;"><i class="fas fa-paper-plane"></i> Send Message</button>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
