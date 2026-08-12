<?php
session_start();
require_once '../database/connection.php';
require_once '../components/assets.php';
$page_title = "Root Canal Treatment in Dhaka — Pain-Free Endodontics";
$page_desc = "Expert root canal treatment in Lalmatia, Dhaka. Pain-free endodontics by experienced dental surgeons at Mamun's Ortho Dental. Save your natural tooth. Book today.";
$page_canonical = "https://mamunorthodental.com/services/root-canal-treatment-dhaka.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?=$page_title?> | Mamun's Ortho Dental</title>
<meta name="description" content="<?=$page_desc?>">
<meta name="keywords" content="root canal dhaka, root canal treatment lalmatia, endodontics dhaka, tooth pain treatment, save tooth dhaka">
<meta name="robots" content="index, follow">
<meta name="developer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="designer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="copyright" content="© <?= date('Y') ?> Mamun's Ortho Dental. Website designed and developed by Umaer Islam (umaerislam.com)">
<meta name="ai-content-declaration" content="human-authored">
<link rel="canonical" href="<?=$page_canonical?>">
<meta property="og:type" content="article">
<meta property="og:title" content="<?=$page_title?>">
<meta property="og:description" content="<?=$page_desc?>">
<meta property="og:url" content="<?=$page_canonical?>">
<meta property="og:image" content="https://mamunorthodental.com/Logo.png">
<link rel="icon" type="image/png" href="../Logo.png">
<link rel="manifest" href="../site.webmanifest">
<meta name="theme-color" content="#ea741b">
<meta name="msapplication-config" content="../browserconfig.xml">
<link rel="author" href="../humans.txt">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= asset('../assets/css/landing.css') ?>">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#004591','navy-dark':'#003070',gold:'#ea741b'},fontFamily:{serif:['"Playfair Display"','serif'],sans:['"Outfit"','sans-serif']}}}}</script>
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"MedicalWebPage","name":"Root Canal Treatment in Dhaka","description":"<?=$page_desc?>","url":"<?=$page_canonical?>","creator":{"@type":"Person","name":"Umaer Islam","url":"https://umaerislam.com"},"mainEntity":{"@type":"MedicalProcedure","name":"Root Canal Treatment","procedureType":"http://schema.org/NoninvasiveProcedure","bodyLocation":"Tooth root","howPerformed":"Removal of infected pulp tissue, cleaning and shaping of root canals, and sealing with biocompatible material."},"breadcrumb":{"@type":"BreadcrumbList","itemListElement":[{"@type":"ListItem","position":1,"name":"Home","item":"https://mamunorthodental.com/"},{"@type":"ListItem","position":2,"name":"Services","item":"https://mamunorthodental.com/#services"},{"@type":"ListItem","position":3,"name":"Root Canal","item":"<?=$page_canonical?>"}]}}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Is root canal treatment painful?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "No. Modern root canal treatment is virtually painless. We use highly effective local anesthesia so you feel no discomfort during the procedure. Most patients report it feels similar to getting a standard filling."
      }
    },
    {
      "@type": "Question",
      "name": "How long does a root canal take?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "A root canal typically takes 30 to 45 minutes for a single-rooted tooth and 60 to 90 minutes for a multi-rooted tooth (molar). Most treatments are completed in a single visit, but complex cases may require two."
      }
    },
    {
      "@type": "Question",
      "name": "How much does a root canal cost in Dhaka?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "In Dhaka, Bangladesh, root canal treatment cost ranges from ৳5,000 to ৳15,000 depending on the tooth's position (front vs. molar) and complexity. Note that dental crown placement is a separate cost."
      }
    },
    {
      "@type": "Question",
      "name": "Can I avoid a root canal with antibiotics?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "No. While antibiotics can temporarily suppress the infection and reduce swelling, they cannot cure the source of the infection inside the tooth pulp. A root canal is required to physically remove the dead pulp and save the tooth."
      }
    }
  ]
}
</script>
</head>
<body class="font-sans bg-white text-gray-800">
<!-- Developed by Umaer Islam (https://umaerislam.com) -->
<nav class="fixed top-5 left-0 right-0 z-[100] px-4"><div class="max-w-6xl mx-auto"><div class="nav-pill flex items-center justify-between h-16 px-5 lg:px-8 bg-[#001230]/50 backdrop-blur-xl border border-white/10 rounded-3xl shadow-[0_8px_32px_rgba(0,0,0,.2)]"><a href="../index.php" class="flex items-center gap-3"><img src="../Logo.png" alt="Mamun's Ortho Dental" class="w-10 h-10 object-contain"><div class="flex flex-col leading-none"><span class="text-white font-serif text-[17px] font-bold">Mamun's <span class="text-[#ea741b]">Ortho</span></span><span class="text-white/50 text-[8px] tracking-[.25em] uppercase font-bold mt-0.5">Dental Center</span></div></a><div class="flex items-center gap-3"><a href="../index.php" class="hidden md:flex px-4 py-2 text-white/75 hover:text-white text-[13px] font-semibold rounded-full hover:bg-white/10 transition-all">Home</a><a href="../index.php#contact" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-[#ea741b] to-[#cf5e0e] text-white text-[11px] font-bold uppercase tracking-widest rounded-full hover:scale-105 transition-all"><i class="fas fa-calendar-check text-xs"></i> Book Now</a></div></div></div></nav>

<section class="bg-gradient-to-br from-[#000e22] via-[#002a60] to-[#004591] pt-32 pb-16">
  <div class="max-w-5xl mx-auto px-5 lg:px-8 relative z-10">
    <nav aria-label="Breadcrumb" class="mb-6"><ol class="flex items-center gap-2 text-white/40 text-xs"><li><a href="../index.php" class="hover:text-white">Home</a></li><li><i class="fas fa-chevron-right text-[8px]"></i></li><li><a href="../index.php#services" class="hover:text-white">Services</a></li><li><i class="fas fa-chevron-right text-[8px]"></i></li><li class="text-[#ea741b] font-semibold">Root Canal</li></ol></nav>
    <div class="gold-bar mb-4"></div>
    <p class="text-[#ea741b] text-[11px] font-bold uppercase tracking-[.3em] mb-3">Endodontics</p>
    <h1 class="font-serif text-4xl lg:text-5xl font-bold text-white leading-tight mb-4">Root Canal Treatment<br><span class="text-[#ea741b] italic font-light">in Dhaka, Bangladesh</span></h1>
    <p class="text-white/55 text-lg max-w-2xl mb-8">Pain-free root canal therapy to save infected teeth. Modern techniques, experienced doctors, and comfortable care at Mamun's Ortho Dental, Lalmatia.</p>
    <a href="../index.php#contact" class="btn-glow inline-flex items-center gap-2 px-8 py-4 text-white font-bold text-sm uppercase tracking-widest rounded-2xl"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
  </div>
</section>

<article class="py-20">
  <div class="max-w-4xl mx-auto px-5 lg:px-8 prose prose-lg max-w-none text-gray-600">
    <!-- Medical Reviewer Badge (E-E-A-T) -->
    <div class="flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-2xl p-4 mb-8 not-prose">
      <div class="w-10 h-10 rounded-full bg-[#ea741b]/10 flex items-center justify-center text-[#ea741b] flex-shrink-0"><i class="fas fa-user-doctor"></i></div>
      <div class="text-xs text-gray-500 leading-snug">
        <p class="font-bold text-[#004591] text-sm">Medical Reviewer: Dr. Mohammad Shamim Al Mamun</p>
        <p>BDS (DDC), FCPS (Orthodontics) · Associate Professor & Head of Dept. of Orthodontics, Bangladesh Dental College · BMDC Reg. No. A-2133</p>
      </div>
    </div>

    <h2 class="font-serif text-2xl font-bold text-[#004591]">What is Root Canal Treatment?</h2>
    <!-- AEO Answer Snippet -->
    <div class="bg-amber-50/50 border-l-4 border-[#ea741b] p-4 rounded-r-xl my-4 text-sm font-medium text-gray-700 leading-relaxed not-prose">
      <strong>Quick Summary:</strong> A root canal is an endodontic procedure that saves an infected or decaying tooth. It involves removing the infected inner pulp tissue, cleaning and shaping the root canals, and sealing them. It completely relieves toothache and prevents tooth loss.
    </div>
    <p>A <strong>root canal</strong> (endodontic treatment) is a procedure to save a tooth that has become badly infected or decayed. The treatment removes the infected <strong>pulp tissue</strong> inside the tooth, cleans and shapes the root canals, and seals them with biocompatible material. A crown is then placed to protect the tooth.</p>

    <h2 class="font-serif text-2xl font-bold text-[#004591] !mt-10">When Do You Need a Root Canal?</h2>
    <!-- AEO Answer Snippet -->
    <div class="bg-amber-50/50 border-l-4 border-[#ea741b] p-4 rounded-r-xl my-4 text-sm font-medium text-gray-700 leading-relaxed not-prose">
      <strong>Quick Summary:</strong> You need a root canal if you experience severe toothache when chewing, prolonged sensitivity to hot/cold, tooth discoloration, gum swelling, or a pimple-like bump on the gums (dental abscess).
    </div>
    <ul>
      <li><strong>Severe toothache</strong> that worsens with pressure or hot/cold</li>
      <li><strong>Prolonged sensitivity</strong> to temperature</li>
      <li><strong>Darkening</strong> of the tooth</li>
      <li><strong>Swollen gums</strong> near the affected tooth</li>
      <li><strong>Pimple on the gums</strong> (dental abscess)</li>
    </ul>

    <h2 class="font-serif text-2xl font-bold text-[#004591] !mt-10">Our Root Canal Process</h2>
    <ol>
      <li><strong>Diagnosis</strong> — X-ray and examination to assess infection severity</li>
      <li><strong>Anesthesia</strong> — Local numbing for a completely pain-free experience</li>
      <li><strong>Pulp Removal</strong> — Infected tissue carefully removed</li>
      <li><strong>Canal Cleaning</strong> — Thorough cleaning and shaping with modern instruments</li>
      <li><strong>Sealing</strong> — Canals sealed with biocompatible material</li>
      <li><strong>Crown Placement</strong> — Protective <a href="../index.php#services" class="text-[#ea741b] hover:underline">crown</a> fitted for long-term durability</li>
    </ol>

    <h2 class="font-serif text-2xl font-bold text-[#004591] !mt-10">Frequently Asked Questions</h2>
    <div class="space-y-3 not-prose">
      <?php foreach([
        ['Is root canal treatment painful?','Modern root canal treatment is virtually painless. We use effective local anesthesia so you feel no pain during the procedure. Most patients report less discomfort than a simple filling.'],
        ['How long does a root canal take?','A single-rooted tooth takes about 30–45 minutes. Multi-rooted teeth (molars) may take 60–90 minutes. Some cases require 2 visits.'],
        ['How much does root canal cost in Dhaka?','Root canal cost in Dhaka typically ranges from ৳5,000 to ৳15,000 depending on the tooth location and complexity. Crown cost is additional. Contact us for exact pricing.'],
        ['Can I avoid root canal with antibiotics?','Antibiotics can temporarily reduce infection but cannot cure it. Root canal is the definitive treatment to save the tooth. Delaying treatment leads to abscess and eventual tooth loss.']
      ] as [$q,$a]): ?>
      <details class="group bg-[#F8FAFD] border border-gray-100 rounded-2xl overflow-hidden">
        <summary class="flex items-center justify-between p-5 cursor-pointer font-bold text-[#004591] text-sm hover:bg-gray-50 transition-colors"><?=$q?><i class="fas fa-chevron-down text-[#ea741b] text-xs transition-transform group-open:rotate-180"></i></summary>
        <div class="px-5 pb-5 text-gray-600 text-sm leading-relaxed"><?=$a?></div>
      </details>
      <?php endforeach; ?>
    </div>

    <div class="not-prose mt-12 bg-[#004591] text-white rounded-2xl p-8 text-center">
      <h3 class="font-serif text-2xl font-bold mb-3">Tooth Pain? Don't Wait.</h3>
      <p class="text-white/70 text-sm mb-6">Early root canal treatment saves your natural tooth. Visit Mamun's Ortho Dental, Lalmatia, Dhaka.</p>
      <a href="../index.php#contact" class="btn-glow inline-flex items-center gap-2 px-8 py-4 text-white font-bold text-sm uppercase tracking-widest rounded-2xl"><i class="fas fa-calendar-plus"></i> Book Emergency Visit</a>
    </div>

    <div class="not-prose mt-8 p-5 bg-[#F8FAFD] border border-gray-100 rounded-2xl">
      <h4 class="font-bold text-[#004591] text-sm mb-2">Related Services</h4>
      <ul class="text-sm text-gray-600 space-y-1">
        <li>&bull; <a href="scaling-polishing-dhaka.php" class="text-[#ea741b] hover:underline">Scaling &amp; Polishing</a> &mdash; Prevent infections with regular cleanings</li>
        <li>&bull; <a href="teeth-whitening-dhaka.php" class="text-[#ea741b] hover:underline">Teeth Whitening</a> &mdash; Brighten your smile after treatment</li>
      </ul>
    </div>
  </div>
</article>

<footer class="bg-[#000e22] py-10"><div class="max-w-6xl mx-auto px-5 text-center"><a href="../index.php" class="inline-flex items-center gap-3 mb-4"><img src="../Logo.png" alt="Mamun's Ortho Dental" class="w-8 h-8 object-contain"><span class="text-white font-serif text-lg font-bold">Mamun's <span class="text-[#ea741b]">Ortho</span> Dental</span>