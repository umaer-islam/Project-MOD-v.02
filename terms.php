<?php
session_start();
$pageTitle = "Terms of Service — Mamun's Ortho Dental";
$pageDesc  = "Terms of Service for Mamun's Ortho Dental. Read the conditions of using our website and clinic services provided by Dr. Shamim Al Mamun in Lalmatia, Dhaka.";
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
<link rel="canonical" href="https://mamunorthodental.com/terms.php">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
<meta property="og:url" content="https://mamunorthodental.com/terms.php">
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

  .pp-nav {
    background: var(--navy); padding: 16px 32px;
    display: flex; align-items: center; justify-content: space-between;
  }
  .pp-nav a.brand { color: var(--gold); font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 700; text-decoration: none; }
  .pp-nav a.back { color: rgba(255,255,255,0.75); font-size: 0.875rem; text-decoration: none; display: flex; align-items: center; gap: 6px; transition: color .2s; }
  .pp-nav a.back:hover { color: var(--gold); }

  .pp-hero {
    background: linear-gradient(135deg, #0f172a 0%, var(--blue) 100%);
    padding: 72px 32px 56px; text-align: center; color: var(--white);
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

  .pp-wrap { max-width: 860px; margin: 0 auto; padding: 64px 24px 96px; }
  .pp-toc { background: var(--white); border: 1px solid var(--border); border-radius: 16px; padding: 32px; margin-bottom: 48px; }
  .pp-toc h2 { font-size: 1rem; font-weight: 700; color: var(--navy); margin-bottom: 16px; letter-spacing: .05em; text-transform: uppercase; }
  .pp-toc ol { padding-left: 20px; }
  .pp-toc li { margin-bottom: 8px; }
  .pp-toc a { color: var(--blue); text-decoration: none; font-size: 0.925rem; }
  .pp-toc a:hover { color: var(--gold); text-decoration: underline; }

  .pp-section { margin-bottom: 56px; scroll-margin-top: 80px; }
  .pp-section h2 { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: var(--navy); margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid var(--gold); }
  .pp-section h3 { font-size: 1rem; font-weight: 700; color: var(--blue); margin: 24px 0 8px; }
  .pp-section p { color: var(--text); font-size: 0.95rem; margin-bottom: 14px; }
  .pp-section ul { padding-left: 22px; margin-bottom: 14px; }
  .pp-section ul li { font-size: 0.95rem; margin-bottom: 6px; color: var(--text); }
  .pp-section a { color: var(--blue); }
  .warn-box { background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 20px 24px; margin: 20px 0; }
  .warn-box p { margin: 0; font-size: 0.9rem; color: #92400e; }

  .pp-footer { background: var(--navy); color: rgba(255,255,255,0.55); text-align: center; padding: 32px 24px; font-size: 0.85rem; }
  .pp-footer a { color: var(--gold); text-decoration: none; }
  .pp-footer a:hover { text-decoration: underline; }
</style>
</head>
<body>

<nav class="pp-nav">
  <a href="index.php" class="brand">Mamun's Ortho Dental</a>
  <a href="index.php" class="back"><i class="fa fa-arrow-left"></i> Back to Home</a>
</nav>

<header class="pp-hero">
  <div class="badge"><i class="fa fa-file-contract"></i> Legal Document</div>
  <h1>Terms of Service</h1>
  <p>By using our website or attending our clinic, you agree to the following terms and conditions.</p>
  <div class="meta">
    <span><i class="fa fa-calendar"></i> Effective Date: 1 June 2026</span>
    <span><i class="fa fa-clock"></i> Last Updated: 1 June 2026</span>
    <span><i class="fa fa-location-dot"></i> Dhaka, Bangladesh</span>
  </div>
</header>

<main class="pp-wrap">

  <nav class="pp-toc" aria-label="Table of Contents">
    <h2>Contents</h2>
    <ol>
      <li><a href="#acceptance">Acceptance of Terms</a></li>
      <li><a href="#services">Clinical Services</a></li>
      <li><a href="#website">Website Use</a></li>
      <li><a href="#appointments">Appointments &amp; Cancellations</a></li>
      <li><a href="#payments">Payments &amp; Fees</a></li>
      <li><a href="#medical-disclaimer">Medical Disclaimer</a></li>
      <li><a href="#intellectual-property">Intellectual Property</a></li>
      <li><a href="#limitation">Limitation of Liability</a></li>
      <li><a href="#governing-law">Governing Law</a></li>
      <li><a href="#changes">Changes to Terms</a></li>
      <li><a href="#contact">Contact Us</a></li>
    </ol>
  </nav>

  <section class="pp-section" id="acceptance">
    <h2>1. Acceptance of Terms</h2>
    <p>By accessing the website <a href="https://mamunorthodental.com">mamunorthodental.com</a> or by using the clinical services of <strong>Mamun's Ortho Dental</strong>, you agree to be bound by these Terms of Service. If you do not agree, please do not use this website or our services.</p>
    <p>These terms apply to all visitors, patients, and users of the website and clinic management system.</p>
  </section>

  <section class="pp-section" id="services">
    <h2>2. Clinical Services</h2>
    <p>Mamun's Ortho Dental provides dental and orthodontic clinical services including but not limited to: orthodontic treatment (braces, aligners), root canal treatment, teeth whitening, scaling &amp; polishing, dental extractions, fillings, crowns, bridges, dentures, and other dental procedures.</p>
    <ul>
      <li>All clinical services are provided by or under the supervision of registered dental professionals.</li>
      <li>Treatment plans are individualised and may change based on clinical assessment.</li>
      <li>Patients are entitled to a clear explanation of proposed treatments before consent.</li>
      <li>You must disclose all relevant medical history, allergies, and current medications to your treating clinician.</li>
    </ul>
  </section>

  <section class="pp-section" id="website">
    <h2>3. Website Use</h2>
    <p>You agree to use this website only for lawful purposes. You must not:</p>
    <ul>
      <li>Attempt to gain unauthorised access to any part of the system, database, or admin panel.</li>
      <li>Use automated bots, scrapers, or crawlers against the site beyond what is permitted in <code>robots.txt</code>.</li>
      <li>Submit false, misleading, or defamatory content via any form on this website.</li>
      <li>Interfere with the normal operation of the website or server.</li>
    </ul>
    <p>We reserve the right to terminate access to anyone who violates these conditions.</p>
  </section>

  <section class="pp-section" id="appointments">
    <h2>4. Appointments &amp; Cancellations</h2>
    <ul>
      <li>Appointments may be booked in person, by phone, or via the contact form on this website.</li>
      <li>Please provide at least <strong>24 hours' notice</strong> if you need to cancel or reschedule your appointment.</li>
      <li>Repeated no-shows may result in future appointments requiring a deposit.</li>
      <li>We reserve the right to reschedule appointments in cases of clinical emergency.</li>
    </ul>
  </section>

  <section class="pp-section" id="payments">
    <h2>5. Payments &amp; Fees</h2>
    <ul>
      <li>All fees are payable at the time of service unless a payment plan has been agreed in advance.</li>
      <li>Prices for treatments are provided as estimates and may vary based on clinical complexity.</li>
      <li>Receipts and cash memos are issued for all payments.</li>
      <li>We currently accept cash and mobile banking (bKash/Nagad). Card payment availability is subject to change.</li>
      <li>Refund requests are assessed case by case. Non-refundable fees will be clearly communicated before treatment begins.</li>
    </ul>
  </section>

  <section class="pp-section" id="medical-disclaimer">
    <h2>6. Medical Disclaimer</h2>
    <div class="warn-box">
      <p><i class="fa fa-triangle-exclamation"></i>&nbsp; <strong>Important:</strong> Content on this website is for general informational purposes only. It does not constitute medical advice, diagnosis, or treatment. Always consult a qualified dental professional for personal medical concerns.</p>
    </div>
    <p>While we strive to provide accurate and up-to-date information, clinical outcomes vary per individual. Mamun's Ortho Dental does not guarantee specific treatment results.</p>
    <p>In case of a dental emergency, please contact our clinic directly or visit the nearest hospital emergency department.</p>
  </section>

  <section class="pp-section" id="intellectual-property">
    <h2>7. Intellectual Property</h2>
    <p>All content on this website — including text, images, logos, graphics, design, and code — is the intellectual property of Mamun's Ortho Dental unless otherwise stated.</p>
    <ul>
      <li>You may not reproduce, distribute, or republish any content without prior written permission.</li>
      <li>The clinic logo and branding are trademarks of Mamun's Ortho Dental.</li>
      <li>Patient data and prescription content generated through our system remain the confidential property of the clinic and the respective patient.</li>
    </ul>
  </section>

  <section class="pp-section" id="limitation">
    <h2>8. Limitation of Liability</h2>
    <p>To the maximum extent permitted by law, Mamun's Ortho Dental and its staff shall not be liable for:</p>
    <ul>
      <li>Any indirect, incidental, or consequential damages arising from use of this website.</li>
      <li>Temporary unavailability of the website due to maintenance or technical issues.</li>
      <li>Any loss resulting from reliance on information provided on this website without consulting a dental professional.</li>
    </ul>
    <p>Our liability for clinical services is governed by the laws of Bangladesh and the standards of the Bangladesh Dental Council.</p>
  </section>

  <section class="pp-section" id="governing-law">
    <h2>9. Governing Law</h2>
    <p>These Terms of Service are governed by and construed in accordance with the laws of the <strong>People's Republic of Bangladesh</strong>. Any disputes arising under these terms shall be subject to the exclusive jurisdiction of the courts of Dhaka, Bangladesh.</p>
  </section>

  <section class="pp-section" id="changes">
    <h2>10. Changes to Terms</h2>
    <p>We reserve the right to modify these Terms of Service at any time. Changes will be posted on this page with an updated "Last Updated" date. Continued use of the website or clinic services after any change constitutes your acceptance of the new terms.</p>
  </section>

  <section class="pp-section" id="contact">
    <h2>11. Contact Us</h2>
    <p>If you have questions about these Terms, please contact:</p>
    <ul>
      <li><strong>Mamun's Ortho Dental</strong></li>
      <li>Lalmatia, Mohammadpur, Dhaka-1207, Bangladesh</li>
      <li>Email: <a href="mailto:info@mamunorthodental.com">info@mamunorthodental.com</a></li>
      <li>Website: <a href="https://mamunorthodental.com">mamunorthodental.com</a></li>
    </ul>
  </section>

</main>

<footer class="pp-footer">
  <p>&copy; <?= date('Y') ?> Mamun's Ortho Dental. All rights reserved. &nbsp;|&nbsp;
    <a href="privacy-policy.php">Privacy Policy</a> &nbsp;|&nbsp;
    <a href="terms.php">Terms of Service</a> &nbsp;|&nbsp;
    <a href="index.php">Home</a>
  </p>
</footer>

</body>
</html>
