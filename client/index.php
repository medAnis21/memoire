<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Sonatrach — Énergie pour l'Algérie</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="style.css" />
</head>
<body>

<!-- ── NAV ── -->
<nav>
  <a href="#" class="nav-logo">
    <span class="nav-logo-text">SONATRACH</span>
  </a>
  <ul class="nav-links" id="navLinks">
    <?php if (!empty($_SESSION['user_id'])): ?>
    <li><a href="../stagaire/stagaire-info.php">Stagaires</a></li>
    <?php endif; ?>
    <li><a href="#about">About</a></li>
    <li><a href="#activities">Activities</a></li>
    <li><a href="#numbers">Key Figures</a></li>
    <li><a href="#news">News</a></li>
    <li><a href="#contact">Contact</a></li>
    <?php if (!empty($_SESSION['user_id'])): ?>
      <li><a href="../auth/logout.php" class="nav-cta">Logout</a></li>
    <?php else: ?>
      <li><a href="../auth/index.php" class="nav-cta">Login</a></li>
    <?php endif; ?>
  </ul>
  <button class="hamburger" id="hamburger" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
</nav>

<!-- ── HERO ── -->
<section class="hero" id="home">
  <div class="hero-bg-lines"></div>
  <div class="hero-orb"></div>
  <div class="hero-content">
    <div class="hero-badge">Africa's Largest Energy Company</div>
    <h1>Powering Algeria,<br /><span>Energizing the World</span></h1>
    <p>Founded in 1963, Sonatrach leads exploration, production, and marketing of hydrocarbons — driving Algeria's economic sovereignty and global energy partnerships.</p>
    <div class="hero-btns">
      <a href="#activities" class="btn-primary">Explore Activities</a>
      <a href="#about" class="btn-outline">Our Story</a>
    </div>
  </div>
  <div class="hero-stats">
    <div class="hero-stat">
      <strong>60+</strong>
      <span>Years of operation</span>
    </div>
    <div class="hero-stat">
      <strong>120K</strong>
      <span>Employees worldwide</span>
    </div>
    <div class="hero-stat">
      <strong>#1</strong>
      <span>Energy company in Africa</span>
    </div>
    <div class="hero-stat">
      <strong>22 000 km</strong>
      <span>Pipeline network</span>
    </div>
  </div>
</section>

<!-- ── ABOUT ── -->
<section class="about" id="about">
  <div class="reveal">
    <p class="section-label">Who We Are</p>
    <h2 class="section-title">The Backbone of Algeria's Economy</h2>
    <p class="section-sub">Sonatrach is the national state-owned hydrocarbon company of Algeria — the African Major. Responsible for 97.5% of Algeria's export earnings, it is a fully integrated group across the entire hydrocarbon value chain.</p>
  </div>
  <div class="about-grid reveal">
    <div class="about-visual">
      <div class="about-img-placeholder">
        <svg viewBox="0 0 460 340" xmlns="http://www.w3.org/2000/svg">
          <rect width="460" height="340" fill="#F0E8D0"/>
          <ellipse cx="80" cy="310" rx="120" ry="60" fill="#E8D8B0"/>
          <ellipse cx="260" cy="320" rx="150" ry="55" fill="#DFD0A0"/>
          <ellipse cx="420" cy="305" rx="100" ry="50" fill="#E8D8B0"/>
          <circle cx="380" cy="60" r="40" fill="#E8B84B" opacity="0.55"/>
          <circle cx="380" cy="60" r="28" fill="#C8942A" opacity="0.4"/>
          <rect x="0" y="220" width="460" height="14" rx="7" fill="#5C3D1A" opacity="0.7"/>
          <rect x="0" y="228" width="460" height="4" rx="2" fill="#C8942A" opacity="0.5"/>
          <g transform="translate(100, 140)">
            <polygon points="30,0 0,80 60,80" fill="none" stroke="#2E2110" stroke-width="2.5"/>
            <line x1="30" y1="0" x2="30" y2="80" stroke="#2E2110" stroke-width="1.5"/>
            <line x1="10" y1="26" x2="50" y2="26" stroke="#2E2110" stroke-width="1.5"/>
            <line x1="16" y1="53" x2="44" y2="53" stroke="#2E2110" stroke-width="1.5"/>
            <rect x="22" y="80" width="16" height="20" rx="2" fill="#C8942A"/>
          </g>
          <ellipse cx="300" cy="200" rx="38" ry="12" fill="#C8942A" opacity="0.5"/>
          <rect x="262" y="150" width="76" height="50" rx="4" fill="#C8942A" opacity="0.7"/>
          <ellipse cx="300" cy="150" rx="38" ry="12" fill="#E8B84B" opacity="0.6"/>
          <circle cx="120" cy="227" r="7" fill="#C8942A"/>
          <circle cx="240" cy="227" r="7" fill="#C8942A"/>
          <circle cx="360" cy="227" r="7" fill="#C8942A"/>
        </svg>
      </div>
      <div class="about-badge">
        <strong>1963</strong>
        <span>Founded</span>
      </div>
    </div>
    <div class="about-text">
      <p class="section-label">Our Mission</p>
      <h3 class="section-title" style="font-size:clamp(22px,3vw,32px)">Developing Algeria's hydrocarbon potential for the nation</h3>
      <p style="font-size:15px;color:var(--text-muted);line-height:1.75;">As the main industrial investor in Algeria, Sonatrach actively participates in supporting the local economy through its SH2030 strategy — aiming to become one of the world's top five most profitable national oil companies.</p>
      <ul>
        <li>Largest company in Africa, operating in over 15 countries</li>
        <li>3rd largest exporter of liquefied petroleum gas (LPG) globally</li>
        <li>4th largest exporter of liquefied natural gas (LNG) worldwide</li>
        <li>Committed to renewable energy and sustainable development</li>
        <li>$59 billion national investment target by 2030</li>
      </ul>
    </div>
  </div>
</section>

<!-- ── ACTIVITIES ── -->
<section class="activities" id="activities">
  <div class="activities-header reveal">
    <div>
      <p class="section-label">What We Do</p>
      <h2 class="section-title">Our Core Activities</h2>
    </div>
    <p class="section-sub" style="max-width:360px;">From subsurface exploration to global marketing, Sonatrach controls every stage of the value chain.</p>
  </div>
  <div class="activities-grid">
    <div class="activity-card reveal">
      <div class="activity-icon">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"/></svg>
      </div>
      <h3>Exploration & Production</h3>
      <p>Operating in the Sahara's major basins — Hassi Messaoud, Hassi R'Mel, Berkine — and internationally across Libya, Peru, Venezuela and more.</p>
    </div>
    <div class="activity-card reveal">
      <div class="activity-icon">
        <svg viewBox="0 0 24 24"><path d="M3 3l18 18M10.5 6.5C10.5 6.5 15 7 15 12c0 3-2 5-5 5"/><path d="M6 17.5C6 17.5 4 15 4 12c0-4 3-7 8-7"/></svg>
      </div>
      <h3>Pipeline Transport</h3>
      <p>Managing over 22,000 km of oil and gas pipelines linking Saharan fields to coastal terminals and export routes to Europe.</p>
    </div>
    <div class="activity-card reveal">
      <div class="activity-icon">
        <svg viewBox="0 0 24 24"><path d="M2 20h20M5 20V10l7-7 7 7v10"/><path d="M9 20v-5h6v5"/></svg>
      </div>
      <h3>Refining & Petrochemicals</h3>
      <p>Five refineries, LNG liquefaction facilities at Skikda and Arzew, LPG plants, and a growing petrochemical portfolio.</p>
    </div>
    <div class="activity-card reveal">
      <div class="activity-icon">
        <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
      </div>
      <h3>Marketing & Trading</h3>
      <p>Commercializing hydrocarbons and by-products across European markets, with key supply agreements with Spain, Italy, and France.</p>
    </div>
    <div class="activity-card reveal">
      <div class="activity-icon">
        <svg viewBox="0 0 24 24"><path d="M12 2v6M12 22v-6M4.93 4.93l4.24 4.24M14.83 14.83l4.24 4.24M2 12h6M22 12h-6M4.93 19.07l4.24-4.24M14.83 9.17l4.24-4.24"/></svg>
      </div>
      <h3>Renewable Energy</h3>
      <p>Investing in photovoltaic plants and clean energy as part of Algeria's energy transition — including the 10 MWp Hassi Berkine solar installation.</p>
    </div>
    <div class="activity-card reveal">
      <div class="activity-icon">
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <h3>Human Capital</h3>
      <p>120,000 employees across Algeria and the world, supported by training programs and academic partnerships to build local expertise.</p>
    </div>
  </div>
</section>

<!-- ── NUMBERS ── -->
<section class="numbers" id="numbers">
  <div class="reveal">
    <p class="section-label" style="color:var(--gold)">Sonatrach in Figures</p>
    <h2 class="section-title">Scale That Defines a Nation</h2>
  </div>
  <div class="numbers-grid reveal">
    <div class="number-item">
      <strong id="cnt-employees">0</strong>
      <span>Employees</span>
    </div>
    <div class="number-item">
      <strong id="cnt-km">0</strong>
      <span>Pipeline km</span>
    </div>
    <div class="number-item">
      <strong id="cnt-sub">0</strong>
      <span>Subsidiaries</span>
    </div>
    <div class="number-item">
      <strong id="cnt-countries">0</strong>
      <span>Countries</span>
    </div>
    <div class="number-item">
      <strong id="cnt-ports">0</strong>
      <span>Export ports</span>
    </div>
  </div>
</section>

<!-- ── NEWS ── -->
<section class="news" id="news">
  <div class="news-header reveal">
    <div>
      <p class="section-label">Latest</p>
      <h2 class="section-title">News & Press Releases</h2>
    </div>
    <a href="#" class="btn-primary" style="white-space:nowrap;">View All News</a>
  </div>
  <div class="news-grid reveal">
    <div class="news-card featured">
      <div class="news-img">
        <svg viewBox="0 0 400 225" xmlns="http://www.w3.org/2000/svg">
          <rect width="400" height="225" fill="#E8D8B0"/>
          <rect y="160" width="400" height="65" fill="#DFD0A0"/>
          <circle cx="340" cy="45" r="32" fill="#E8B84B" opacity="0.5"/>
          <rect x="60" y="80" width="30" height="80" rx="3" fill="#5C3D1A" opacity="0.6"/>
          <rect x="100" y="60" width="30" height="100" rx="3" fill="#5C3D1A" opacity="0.6"/>
          <rect x="140" y="90" width="30" height="70" rx="3" fill="#5C3D1A" opacity="0.6"/>
          <rect x="180" y="50" width="30" height="110" rx="3" fill="#C8942A" opacity="0.7"/>
          <rect x="220" y="70" width="30" height="90" rx="3" fill="#5C3D1A" opacity="0.6"/>
          <rect x="260" y="100" width="30" height="60" rx="3" fill="#5C3D1A" opacity="0.6"/>
        </svg>
      </div>
      <div class="news-body">
        <p class="news-tag">Strategy</p>
        <h3>Sonatrach Reaffirms SH2030: $59 Billion Investment Plan for Algeria's Energy Future</h3>
        <p class="news-date">April 15, 2025</p>
      </div>
    </div>
    <div class="news-card">
      <div class="news-img">
        <svg viewBox="0 0 200 112" xmlns="http://www.w3.org/2000/svg">
          <rect width="200" height="112" fill="#E0D5C0"/>
          <circle cx="100" cy="56" r="30" fill="#C8942A" opacity="0.3"/>
          <circle cx="100" cy="56" r="18" fill="#C8942A" opacity="0.5"/>
          <path d="M100 30 L100 82 M74 56 L126 56" stroke="#5C3D1A" stroke-width="2"/>
        </svg>
      </div>
      <div class="news-body">
        <p class="news-tag">Exploration</p>
        <h3>New Gas Discovery Near Hassi R'Mel Field</h3>
        <p class="news-date">March 28, 2025</p>
      </div>
    </div>
    <div class="news-card">
      <div class="news-img">
        <svg viewBox="0 0 200 112" xmlns="http://www.w3.org/2000/svg">
          <rect width="200" height="112" fill="#E8F0DC"/>
          <circle cx="100" cy="56" r="35" fill="#639922" opacity="0.2"/>
          <path d="M80 70 Q100 30 120 70" fill="none" stroke="#639922" stroke-width="2.5"/>
          <circle cx="100" cy="52" r="6" fill="#639922" opacity="0.6"/>
        </svg>
      </div>
      <div class="news-body">
        <p class="news-tag">Renewables</p>
        <h3>Solar Plant at Hassi Berkine Begins Full Operation</h3>
        <p class="news-date">February 10, 2025</p>
      </div>
    </div>
  </div>
</section>

<!-- ── CONTACT ── -->
<section class="contact" id="contact">
  <div class="reveal">
    <p class="section-label">Get in Touch</p>
    <h2 class="section-title">Contact Sonatrach</h2>
    <p class="section-sub">For partnership inquiries, press requests, or general information.</p>
  </div>
  <div class="contact-grid reveal">
    <div class="contact-info">
      <h3>Headquarters</h3>
      <div class="contact-detail">
        <div class="contact-detail-icon">
          <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <div>
          <strong>Address</strong>
          <p>Djenane El Malik, Hydra<br/>Algiers 16035, Algeria</p>
        </div>
      </div>
      <div class="contact-detail">
        <div class="contact-detail-icon">
          <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.62 3.38 2 2 0 0 1 3.6 1.21h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.84a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7a2 2 0 0 1 1.72 2.03z"/></svg>
        </div>
        <div>
          <strong>Phone</strong>
          <p>+213 (0) 21 54 70 00</p>
        </div>
      </div>
      <div class="contact-detail">
        <div class="contact-detail-icon">
          <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <div>
          <strong>Email</strong>
          <p>contact@sonatrach.dz</p>
        </div>
      </div>
    </div>
    <div class="contact-form">
      <div class="form-row">
        <div class="form-group">
          <label>First Name</label>
          <input type="text" placeholder="Ahmed" />
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" placeholder="Benali" />
        </div>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" placeholder="ahmed@example.com" />
      </div>
      <div class="form-group">
        <label>Subject</label>
        <select>
          <option>Partnership Inquiry</option>
          <option>Press & Media</option>
          <option>Employment</option>
          <option>General Information</option>
        </select>
      </div>
      <div class="form-group">
        <label>Message</label>
        <textarea rows="4" placeholder="Your message here..."></textarea>
      </div>
      <button class="form-submit" id="formBtn">Send Message</button>
      <p class="form-msg" id="formMsg"></p>
    </div>
  </div>
</section>

<!-- ── FOOTER ── -->
<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="nav-logo" style="margin-bottom:0">
        <svg width="36" height="36" viewBox="0 0 40 40" fill="none"><circle cx="20" cy="20" r="20" fill="#C8942A"/><path d="M12 28 L20 10 L28 28" stroke="#1A1208" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14.5 22 H25.5" stroke="#1A1208" stroke-width="2" stroke-linecap="round"/><circle cx="20" cy="10" r="2.5" fill="#1A1208"/></svg>
        <span class="nav-logo-text">SONATRACH</span>
      </div>
      <p>Algeria's national energy company — powering the nation since 1963. Africa's largest oil and gas group, present in over 15 countries.</p>
    </div>
    <div class="footer-col">
      <h4>Company</h4>
      <ul>
        <li><a href="#about">About Us</a></li>
        <li><a href="#activities">Activities</a></li>
        <li><a href="#numbers">Key Figures</a></li>
        <li><a href="#">Governance</a></li>
        <li><a href="#">SH2030 Strategy</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Sectors</h4>
      <ul>
        <li><a href="#">Upstream</a></li>
        <li><a href="#">Downstream</a></li>
        <li><a href="#">Pipeline</a></li>
        <li><a href="#">Commercialization</a></li>
        <li><a href="#">Renewables</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Resources</h4>
      <ul>
        <li><a href="#">Press Room</a></li>
        <li><a href="#">Annual Reports</a></li>
        <li><a href="#">Tenders</a></li>
        <li><a href="#">Careers</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2025 Sonatrach. All rights reserved.</p>
    <p>Website template — for educational purposes</p>
  </div>
</footer>

<script src="script.js"></script>
</body>
</html>
