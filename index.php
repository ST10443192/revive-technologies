<?php
/**
 * index.php
 * Public home page - anyone can view this without logging in.
 */
require_once 'includes/functions.php';

$page_title = 'Home';
include 'includes/header_public.php';
?>

<!-- ================= HERO ================= -->
<section class="hero">
    <div class="hero-content reveal-fade in-view">
        <div class="hero-logo-card">
            <img src="assets/img/logo.png" alt="Bee's Events & Decor">
        </div>
        <h1>Where Every Event Becomes <em>Extraordinary</em></h1>
        <p>Premium décor, flawless execution, and unforgettable experiences — tailored exclusively for you, powered by our network of vetted freelance event professionals.</p>
        <div class="hero-actions">
            <a href="#services" class="btn btn-gold">Browse Our Services</a>
            <a href="#contact" class="btn btn-outline-light">Get a Quotation</a>
        </div>
    </div>
</section>

<!-- ================= STATS ================= -->
<section class="section" style="padding-bottom:0;">
    <div class="container grid grid-4">
        <div class="stat-block reveal"><div class="num">500+</div><div class="label">Events Delivered</div></div>
        <div class="stat-block reveal"><div class="num">50+</div><div class="label">Vetted Freelancers</div></div>
        <div class="stat-block reveal"><div class="num">15+</div><div class="label">Years Combined Experience</div></div>
        <div class="stat-block reveal"><div class="num">98%</div><div class="label">Client Satisfaction</div></div>
    </div>
</section>

<!-- ================= SERVICES ================= -->
<section class="section" id="services">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">What We Offer</span>
            <h2>Our Services</h2>
            <p>From kids' birthday parties to traditional ceremonies and corporate galas — we make every moment beautiful.</p>
        </div>
        <div class="grid grid-4">
            <div class="service-card reveal" style="background-image:url('https://images.unsplash.com/photo-1519741497674-611481863552?w=500');">
                <div class="service-body"><h3>Wedding Décor</h3><p>Elegant arches, florals &amp; table styling.</p></div>
            </div>
            <div class="service-card reveal" style="background-image:url('https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=500');">
                <div class="service-body"><h3>Baby Showers &amp; Reveals</h3><p>Sweet, personalised setups for your big reveal.</p></div>
            </div>
            <div class="service-card reveal" style="background-image:url('https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=500');">
                <div class="service-body"><h3>Kids &amp; Adult Parties</h3><p>Colourful, themed celebrations for all ages.</p></div>
            </div>
            <div class="service-card reveal" style="background-image:url('https://images.unsplash.com/photo-1511578314322-379afb476865?w=500');">
                <div class="service-body"><h3>Corporate &amp; Gala Events</h3><p>Professional staging for galas &amp; conferences.</p></div>
            </div>
        </div>
    </div>
</section>

<!-- ================= OUR STORY ================= -->
<section class="section blurred-decor-section" style="--bg-img:url('https://images.unsplash.com/photo-1519167758481-83f550bb49b3?q=80&amp;w=1600');">
    <div class="container reveal" style="text-align:center;max-width:760px;">
        <span class="eyebrow" style="color:#cfc7bd;">Our Story</span>
        <h2 style="color:#fff;font-size:38px;">Crafting Beauty,<br>One Event at a Time</h2>
        <p style="color:#e7e3dd;font-size:16px;line-height:1.7;margin:18px 0 30px 0;">
            Founded with a passion for elegance, Bee's Events &amp; Décor has spent over 15 years transforming spaces across South Africa into breathtaking experiences — all the way from Hammanskraal. From intimate backyard baby showers to grand traditional weddings, our team pours artistry into every detail.
        </p>
        <a href="auth/register.php" class="btn btn-outline-light">Work With Us &rarr;</a>
    </div>
</section>

<!-- ================= GALLERY ================= -->
<section class="section" id="gallery" style="background:#fff;">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">Portfolio</span>
            <h2>Our Signature Work</h2>
            <p>Kids' parties, baby showers, gender reveals, traditional ceremonies, weddings and more.</p>
        </div>

        <div class="tab-filter" id="galleryTabs">
            <a href="#" class="active" onclick="filterGallery(event,'all')">All</a>
            <a href="#" onclick="filterGallery(event,'kids')">Kids Party</a>
            <a href="#" onclick="filterGallery(event,'baby')">Baby Shower</a>
            <a href="#" onclick="filterGallery(event,'reveal')">Gender Reveal</a>
            <a href="#" onclick="filterGallery(event,'traditional')">Traditional</a>
            <a href="#" onclick="filterGallery(event,'wedding')">Wedding</a>
            <a href="#" onclick="filterGallery(event,'adult')">Adult Party</a>
        </div>

        <div class="grid grid-3" id="galleryGrid">
            <img class="reveal" data-cat="wedding" src="https://images.unsplash.com/photo-1519741497674-611481863552?w=600" style="border-radius:10px;height:260px;object-fit:cover;">
            <img class="reveal" data-cat="baby" src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=600" style="border-radius:10px;height:260px;object-fit:cover;">
            <img class="reveal" data-cat="kids" src="https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=600" style="border-radius:10px;height:260px;object-fit:cover;">
            <img class="reveal" data-cat="adult" src="https://images.unsplash.com/photo-1511578314322-379afb476865?w=600" style="border-radius:10px;height:260px;object-fit:cover;">
            <img class="reveal" data-cat="reveal" src="https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=600" style="border-radius:10px;height:260px;object-fit:cover;">
            <img class="reveal" data-cat="traditional" src="https://images.unsplash.com/photo-1478146059778-26028b07395a?w=600" style="border-radius:10px;height:260px;object-fit:cover;">
            <img class="reveal" data-cat="wedding" src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?w=600" style="border-radius:10px;height:260px;object-fit:cover;">
        </div>
    </div>
</section>

<script>
function filterGallery(e, cat) {
    e.preventDefault();
    document.querySelectorAll('#galleryTabs a').forEach(a => a.classList.remove('active'));
    e.currentTarget.classList.add('active');
    document.querySelectorAll('#galleryGrid img').forEach(img => {
        img.style.display = (cat === 'all' || img.dataset.cat === cat) ? 'block' : 'none';
    });
}
</script>

<!-- ================= TESTIMONIALS ================= -->
<section class="section" id="testimonials">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">Client Love</span>
            <h2>What Our Clients Say</h2>
        </div>
        <div class="grid grid-3">
            <div class="card reveal">
                <div class="stars">★★★★★</div>
                <p><em>"The décor team transformed our reception into something out of a dream. Every detail was perfection."</em></p>
                <strong>Nomvula Dlamini</strong><br><span style="color:var(--text-muted);font-size:13px;">Wedding &middot; Sandton</span>
            </div>
            <div class="card reveal">
                <div class="stars">★★★★★</div>
                <p><em>"Our annual awards dinner has never looked this spectacular. Professional, on-time, and detail-oriented."</em></p>
                <strong>Thabo Nkosi</strong><br><span style="color:var(--text-muted);font-size:13px;">Corporate Gala &middot; Johannesburg</span>
            </div>
            <div class="card reveal">
                <div class="stars">★★★★★</div>
                <p><em>"From the initial consultation to the final reveal, everything was seamless. The most beautiful party I've attended."</em></p>
                <strong>Priya Patel</strong><br><span style="color:var(--text-muted);font-size:13px;">Birthday Celebration &middot; Pretoria</span>
            </div>
        </div>
    </div>
</section>

<!-- ================= JOIN OUR TEAM ================= -->
<section class="section blurred-decor-section" style="--bg-img:url('https://images.unsplash.com/photo-1511578314322-379afb476865?q=80&amp;w=1600');">
    <div class="container reveal" style="text-align:center;max-width:760px;">
        <span class="eyebrow" style="color:#cfc7bd;">Join Our Team</span>
        <h2 style="color:#fff;font-size:36px;">Passionate About Décor? <span style="color:#c9c4bd;">Work With Us.</span></h2>
        <p style="color:#e7e3dd;font-size:16px;line-height:1.7;margin:18px 0 30px 0;">
            We're always looking for talented floral designers, balloon artists, stylists, and setup coordinators across South Africa. Join our freelancer network and get paid to do what you love.
        </p>
        <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;margin-bottom:40px;">
            <a href="auth/register.php?role=freelancer" class="btn btn-outline-light">🛠️ Apply as a Freelancer</a>
            <a href="auth/login.php" class="btn" style="background:rgba(255,255,255,.12);color:#fff;">Already a freelancer? Sign In &rarr;</a>
        </div>
        <div class="grid grid-4" style="max-width:600px;margin:0 auto;">
            <div style="text-align:center;">
                <div class="join-icon">✨</div>
                <div style="color:#e7e3dd;font-size:13px;margin-top:8px;">Flexible Hours</div>
            </div>
            <div style="text-align:center;">
                <div class="join-icon">👥</div>
                <div style="color:#e7e3dd;font-size:13px;margin-top:8px;">500+ Events</div>
            </div>
            <div style="text-align:center;">
                <div class="join-icon">📍</div>
                <div style="color:#e7e3dd;font-size:13px;margin-top:8px;">All of SA</div>
            </div>
            <div style="text-align:center;">
                <div class="join-icon">⭐</div>
                <div style="color:#e7e3dd;font-size:13px;margin-top:8px;">Top Pay Rates</div>
            </div>
        </div>
    </div>
</section>

<!-- ================= CONTACT ================= -->
<section class="section section-dark" id="contact">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">Get In Touch</span>
            <h2>Let's Plan Your Event</h2>
            <p>Ready to create something unforgettable? Reach out and our team will be in touch within 24 hours.</p>
        </div>
        <div class="two-col">
            <div>
                <p><strong>📞 Phone</strong><br>+27 11 123 4567</p>
                <p><strong>✉️ Email</strong><br>hello@revivetech.co.za</p>
                <p><strong>📍 Location</strong><br>Sandton, Johannesburg, South Africa</p>
            </div>
            <div class="card" style="background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.15);">
                <form method="POST" action="auth/register.php">
                    <div class="form-grid">
                        <div class="field"><label>First Name</label><input type="text" name="first_name" placeholder="First name"></div>
                        <div class="field"><label>Last Name</label><input type="text" name="last_name" placeholder="Last name"></div>
                    </div>
                    <div class="field"><label>Email</label><input type="email" name="email" placeholder="you@example.com"></div>
                    <div class="field"><label>Event Type</label><input type="text" name="event_type" placeholder="e.g. Wedding, Birthday, Baby Shower"></div>
                    <div class="field"><label>Message</label><textarea name="message" placeholder="Tell us about your event vision..."></textarea></div>
                    <button type="submit" class="btn btn-gold btn-block">Send Enquiry &amp; Create Account</button>
                </form>
                <div style="text-align:center;margin-top:22px;">
                    <span class="eyebrow" style="color:#cfd6e6;">Follow Us</span>
                    <div style="display:flex;gap:12px;justify-content:center;margin-top:10px;">
                        <a href="#" class="social-icon" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12Z"/></svg></a>
                        <a href="#" class="social-icon" aria-label="Instagram"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></a>
                        <a href="#" class="social-icon" aria-label="WhatsApp"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm5.6 14.3c-.2.7-1.4 1.4-2 1.5-.5.1-1.2.1-1.9-.1-.4-.1-1-.3-1.7-.6-3-1.3-5-4.3-5.1-4.5-.2-.2-1.2-1.6-1.2-3.1s.8-2.2 1.1-2.5c.3-.3.6-.4.8-.4h.6c.2 0 .4 0 .6.5l.9 2.1c.1.2.1.4 0 .6l-.4.6c-.1.2-.3.4-.1.7.2.3.9 1.5 1.9 2.4 1.3 1.2 2.4 1.5 2.7 1.7.3.2.5.1.6-.1l.7-.8c.2-.3.4-.2.7-.1l1.9.9c.2.1.4.2.5.3.1.2.1.9-.1 1.6Z"/></svg></a>
                        <a href="#" class="social-icon" aria-label="TikTok"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M16.6 2h-3.2v13.9a2.9 2.9 0 1 1-2.1-2.8v-3.3a6.2 6.2 0 1 0 5.3 6.1V8.6a7.6 7.6 0 0 0 4.4 1.4V6.8a4.4 4.4 0 0 1-4.4-4.4V2Z"/></svg></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer_public.php'; ?>
