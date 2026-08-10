<?php
session_start();
$pageTitle = "Privacy Policy — Mamun's Ortho Dental";
$pageDesc  = "Read the Privacy Policy of Mamun's Ortho Dental. Learn how we collect, use, store, and protect your personal and medical data in compliance with Bangladesh data protection standards.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
<meta name="robots" content="index, follow">
<link rel="canonical" href="https://mamunorthodental.com/privacy-policy.php">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
<meta property="og:url" content="https://mamunorthodental.com/privacy-policy.php">
<meta property="og:image" content="https://mamunorthodental.com/Logo.png">
<meta property="og:site_name" content="Mamun's Ortho Dental">

<!-- Favicon -->
<link rel="icon" type="image/png" href="Logo.png">
<link rel="manifest" href="site.webmanifest">
<meta name="theme-color" content="#ea741b">
<meta name="msapplication-config" content="browserconfig.xml">
<link rel="author" href="humans.txt">
<link rel="apple-touch-icon" href="Logo.png">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --navy: #001630;
    --blue: #0a4d8c;
    --gold: #c8a96e;
    --text: #1e293b;
    --muted: #64748b;
    --bg: #f8fafc;
    --white: #ffffff;
    --border: #e2e8f0;
  }
  body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); line-height: 1.8; }

  /* NAV */
  .pp-nav {
    background: var(--navy);
    padding: 16px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .pp-nav a.brand { color: var(--gold); font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 700; text-decoration: none; }
  .pp-nav a.back { color: rgba(255,255,255,0.75); font-size: 0.875rem; text-decoration: none; display: flex; align-items: center; gap: 6px; transition: color .2s; }
  .pp-nav a.back:hover { color: var(--gold); }

  /* HERO */
  .pp-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--blue) 100%);
    padding: 72px 32px 56px;
    text-align: center;
    color: var(--white);
  }
  .pp-hero .badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: rgba(200,169,110,0.15); border: 1px solid rgba(200,169,110,0.4);
    color: var(--gold); font-size: 0.75rem; font-weight: 600; letter-spacing: 0.1em;
    text-transform: uppercase; padding: 6px 18px; border-radius: 100px; margin-bottom: 20px;
  }
  .pp-hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(2rem, 5vw, 3rem); font-weight: 700; margin-bottom: 16px; }
  .pp-hero p { color: rgba(255,255,255,0.75); font-size: 1rem; max-width: 600px; margin: 0 auto; }
  .pp-hero .meta { margin-top: 24px; display: flex; justify-content: center; gap: 24px; font-size: 0.8rem; color: rgba(255,255,255,0.55); flex-wrap: wrap; }
  .pp-hero .meta span { display: flex; align-items: center; gap: 6px; }

  /* CONTENT */
  .pp-wrap { max-width: 860px; margin: 0 auto; padding: 64px 24px 96px; }
  .pp-toc {
    background: var(--white); border: 1px solid var(--border); border-radius: 16px;
    padding: 32px; margin-bottom: 48px;
  }
  .pp-toc h2 { font-size: 1rem; font-weight: 700; color: var(--navy); margin-bottom: 16px; letter-spacing: .05em; text-transform: uppercase; }
  .pp-toc ol { padding-left: 20px; }
  .pp-toc li { margin-bottom: 8px; }
  .pp-toc a { color: var(--blue); text-decoration: none; font-size: 0.925rem; }
  .pp-toc a:hover { color: var(--gold); text-decoration: underline; }

  .pp-section { margin-bottom: 56px; scroll-margin-top: 80px; }
  .pp-section h2 {
    font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700;
    color: var(--navy); margin-bottom: 16px; padding-bottom: 12px;
    border-bottom: 2px solid var(--gold);
  }
  .pp-section h3 { font-size: 1rem; font-weight: 700; color: var(--blue); margin: 24px 0 8px; }
  .pp-section p { color: var(--text); font-size: 0.95rem; margin-bottom: 14px; }
  .pp-section ul { padding-left: 22px; margin-bottom: 14px; }
  .pp-section ul li { font-size: 0.95rem; margin-bottom: 6px; color: var(--text); }
  .pp-section a { color: var(--blue); }

  .info-box {
    background: #eff6ff; border-left: 4px solid var(--blue);
    border-radius: 8px; padding: 20px 24px; margin: 20px 0;
  }
  .info-box p { margin: 0; font-size: 0.9rem; color: var(--blue); }

  /* FOOTER */
  .pp-footer {
    background: var(--navy); color: rgba(255,255,255,0.55);
    text-align: center; padding: 32px 24px; font-size: 0.85rem;
  }
  .pp-footer a { color: var(--gold); text-decoration: none; }
  .pp-footer a:hover { text-decoration: underline; }
</style>
</head>
<body>

<!-- Nav -->
<nav class="pp-nav">
  <a href="index.php" class="brand">Mamun's Ortho Dental</a>
  <a href="index.php" class="back"><i class="fa fa-arrow-left"></i> Back to Home</a>
</nav>

<!-- Hero -->
<header class="pp-hero">
  <div class="badge"><i class="fa fa-shield-halved"></i> Legal Document</div>
  <h1>Privacy Policy</h1>
  <p>How we collect, use, store and protect your personal and medical information at Mamun's Ortho Dental.</p>
  <div class="meta">
    <span><i class="fa fa-calendar"></i> Effective Date: 1 June 2026</span>
    <span><i class="fa fa-clock"></i> Last Updated: 1 June 2026</span>
    <span><i class="fa fa-location-dot"></i> Lalmatia, Dhaka, Bangladesh</span>
  </div>
</header>

<!-- Content -->
<main class="pp-wrap">

  <!-- Table of Contents -->
  <nav class="pp-toc" aria-label="Table of Contents">
    <h2>Contents</h2>
    <ol>
      <li><a href="#who-we-are">Who We Are</a></li>
      <li><a href="#data-we-collect">Information We Collect</a></li>
      <li><a href="#how-we-use">How We Use Your Information</a></li>
      <li><a href="#data-sharing">Data Sharing &amp; Disclosure</a></li>
      <li><a href="#data-storage">Data Storage &amp; Security</a></li>
      <li><a href="#cookies">Cookies &amp; Tracking</a></li>
      <li><a href="#your-rights">Your Rights</a></li>
      <li><a href="#children">Children's Privacy</a></li>
      <li><a href="#retention">Data Retention</a></li>
      <li><a href="#contact">Contact &amp; Complaints</a></li>
    </ol>
  </nav>

  <!-- Sections -->
  <section class="pp-section" id="who-we-are">
    <h2>1. Who We Are</h2>
    <p><strong>Mamun's Ortho Dental</strong> ("we", "our", "us") is a private dental and orthodontic clinic operated by Dr. Mohammad Shamim Al Mamun (BDS, FCPS Orthodontics), located at Lalmatia, Dhaka, Bangladesh.</p>
    <p>This Privacy Policy explains how we handle personal data collected through our website (<a href="https://mamunorthodental.com">mamunorthodental.com</a>), our clinic management system, and during your interactions with our staff.</p>
    <p>The data controller for all personal data processed by this website and clinic system is:</p>
    <ul>
      <li><strong>Name:</strong> Mamun's Ortho Dental</li>
      <li><strong>Address:</strong> Lalmatia, Mohammadpur, Dhaka-1207, Bangladesh</li>
      <li><strong>Email:</strong> info@mamunorthodental.com</li>
      <li><strong>Phone:</strong> +880 (available on the Contact page)</li>
    </ul>
  </section>

  <section class="pp-section" id="data-we-collect">
    <h2>2. Information We Collect</h2>
    <h3>2.1 Information You Provide</h3>
    <ul>
      <li><strong>Contact inquiries:</strong> Name, phone number, email address, and the message you submit via our contact form.</li>
      <li><strong>Appointment booking:</strong> Name, age, gender, contact number, preferred date/time.</li>
      <li><strong>Patient records:</strong> Full name, date of birth, age, gender, address, phone, email, medical history, diagnosis, prescriptions, treatment notes, and payment records — collected during clinical visits.</li>
    </ul>
    <h3>2.2 Information Collected Automatically</h3>
    <ul>
      <li>IP address, browser type, device type, operating system.</li>
      <li>Pages visited, time spent, referral source (via server-side access logs only — no third-party analytics scripts are embedded by default).</li>
      <li>Session cookies required for login and system functionality.</li>
    </ul>
    <h3>2.3 Medical Data</h3>
    <div class="info-box">
      <p><i class="fa fa-circle-info"></i>&nbsp; Medical records (diagnoses, prescriptions, clinical notes) are classified as <strong>sensitive personal data</strong>. We handle them with heightened confidentiality, accessible only to authorised clinical staff.</p>
    </div>
  </section>

  <section class="pp-section" id="how-we-use">
    <h2>3. How We Use Your Information</h2>
    <ul>
      <li>To schedule and manage your appointments.</li>
      <li>To create, store, and print medical prescriptions and treatment records.</li>
      <li>To send appointment reminders or follow-up communications (if you have provided consent).</li>
      <li>To process and record payments and issue cash memos.</li>
      <li>To respond to enquiries submitted via the contact form.</li>
      <li>To comply with applicable healthcare regulations in Bangladesh.</li>
      <li>To improve the quality and security of our clinical services.</li>
    </ul>
    <p>We will <strong>never</strong> use your data for unsolicited marketing, sell it to third parties, or share it beyond what is described in this policy.</p>
  </section>

  <section class="pp-section" id="data-sharing">
    <h2>4. Data Sharing &amp; Disclosure</h2>
    <p>We do not sell, rent, or trade your personal information. We may share data only in the following limited circumstances:</p>
    <ul>
      <li><strong>Authorised clinic staff:</strong> Doctors, receptionists, and administrative staff who need it to provide your care.</li>
      <li><strong>Referral practitioners:</strong> If you are referred to a specialist, only the clinically relevant information is shared with your explicit knowledge.</li>
      <li><strong>Legal obligation:</strong> If required by Bangladeshi law, court order, or regulatory authority.</li>
      <li><strong>Hosting provider:</strong> Our website hosting provider may technically process data as a data processor; we ensure appropriate data processing agreements are in place.</li>
    </ul>
  </section>

  <section class="pp-section" id="data-storage">
    <h2>5. Data Storage &amp; Security</h2>
    <p>Patient records are stored on a secured local database server within Bangladesh. The following safeguards are in place:</p>
    <ul>
      <li>Password-protected access with role-based permissions (Admin, Doctor, Receptionist).</li>
      <li>Session-based authentication — all admin pages require valid login.</li>
      <li>Sensitive API endpoints are protected from public access via <code>robots.txt</code> and server configuration.</li>
      <li>The website operates over HTTPS (TLS) to encrypt data in transit.</li>
      <li>Database credentials are stored in a configuration file outside the public web root.</li>
      <li>Regular database backups are maintained.</li>
    </ul>
    <p>Despite these measures, no electronic storage system is 100% secure. We encourage you to contact us promptly if you suspect any unauthorised use of your data.</p>
  </section>

  <section class="pp-section" id="cookies">
    <h2>6. Cookies &amp; Tracking</h2>
    <p>We use the following types of cookies:</p>
    <ul>
      <li><strong>Strictly necessary cookies:</strong> PHP session cookies (<code>PHPSESSID</code>) required for login, access control, and form security. These cannot be disabled without breaking the site.</li>
      <li><strong>Preference cookies:</strong> A small cookie (<code>cookie_consent</code>) that stores your cookie consent choice so you are not asked repeatedly.</li>
    </ul>
    <p>We do <strong>not</strong> use Google Analytics, Facebook Pixel, or any third-party advertising trackers on this site. External fonts (Google Fonts) are loaded, which may result in your IP being sent to Google's servers under their own <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a>.</p>
    <p>You may clear cookies at any time through your browser settings.</p>
  </section>

  <section class="pp-section" id="your-rights">
    <h2>7. Your Rights</h2>
    <p>As a patient or website visitor, you have the right to:</p>
    <ul>
      <li><strong>Access:</strong> Request a copy of the personal data we hold about you.</li>
      <li><strong>Correction:</strong> Ask us to correct inaccurate or incomplete information.</li>
      <li><strong>Deletion:</strong> Request that your data be erased, subject to our legal obligations to retain medical records.</li>
      <li><strong>Objection:</strong> Object to certain types of processing (e.g., receiving communications).</li>
      <li><strong>Portability:</strong> Request your data in a portable format (e.g., printed summary of your patient record).</li>
    </ul>
    <p>To exercise any of these rights, please contact us at the details in Section 10. We will respond within 30 days.</p>
  </section>

  <section class="pp-section" id="children">
    <h2>8. Children's Privacy</h2>
    <p>We regularly treat children of all ages. Where the patient is a minor (under 18), we collect information from a parent or legal guardian. Parental consent is obtained before any treatment. We do not knowingly collect data from children online without parental involvement.</p>
  </section>

  <section class="pp-section" id="retention">
    <h2>9. Data Retention</h2>
    <p>We retain patient records in accordance with Bangladeshi healthcare standards:</p>
    <ul>
      <li><strong>Medical records &amp; prescriptions:</strong> Minimum 5 years from the last visit, or as required by law.</li>
      <li><strong>Contact form enquiries:</strong> 12 months, then deleted unless converted to a patient record.</li>
      <li><strong>Payment records:</strong> 7 years for accounting and tax compliance.</li>
      <li><strong>Server access logs:</strong> 30 days, then automatically overwritten.</li>
    </ul>
  </section>

  <section class="pp-section" id="contact">
    <h2>10. Contact &amp; Complaints</h2>
    <p>For privacy-related enquiries, data access requests, or complaints, please contact:</p>
    <ul>
      <li><strong>Mamun's Ortho Dental</strong></li>
      <li>Lalmatia, Mohammadpur, Dhaka-1207, Bangladesh</li>
      <li>Email: <a href="mailto:info@mamunorthodental.com">info@mamunorthodental.com</a></li>
    </ul>
    <p>We take all privacy concerns seriously and aim to resolve complaints promptly. If you are unsatisfied with our response, you may escalate your concern to the relevant Bangladeshi regulatory authority.</p>
    <p>This policy may be updated periodically. The "Last Updated" date at the top of this page will reflect any changes.</p>
  </section>

</main>

<!-- Footer -->
<footer class="pp-footer">
  <p>&copy; <?= date('Y') ?> Mamun's Ortho Dental. All rights reserved. &nbsp;|&nbsp;
    <a href="privacy-policy.php">Privacy Policy</a> &nbsp;|&nbsp;
    <a href="terms.php">Terms of Service</a> &nbsp;|&nbsp;
    <a href="index.php">Home</a>
  </p>
</footer>

</body>
</html>
