<?php
session_start();
require_once '../database/connection.php';
require_once '../components/assets.php';
$page_title = "Orthodontic Braces in Dhaka | Metal, Ceramic & Aligners";
$page_desc = "Get expert braces treatment in Lalmatia, Dhaka by Dr. Shamim Al Mamun (FCPS Orthodontics). Metal braces, ceramic braces & clear aligners. 600+ successful cases. Book consultation today.";
$page_keywords = "braces treatment dhaka, orthodontic braces lalmatia, metal braces cost dhaka, ceramic braces bangladesh, clear aligners dhaka, best orthodontist dhaka, dr shamim al mamun braces";
$page_canonical = "https://mamunorthodental.com/services/orthodontic-braces-treatment-dhaka.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?=$page_title?> | Mamun's Ortho Dental</title>
<meta name="description" content="<?=$page_desc?>">
<meta name="keywords" content="<?=$page_keywords?>">
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
{
  "@context": "https://schema.org",
  "@type": "MedicalWebPage",
  "name": "Orthodontic Braces Treatment in Dhaka",
  "description": "<?=$page_desc?>",
  "url": "<?=$page_canonical?>",
  "creator": {"@type": "Person", "name": "Umaer Islam", "url": "https://umaerislam.com"},
  "mainEntity": {
    "@type": "MedicalProcedure",
    "name": "Orthodontic Treatment",
    "procedureType": "http://schema.org/NoninvasiveProcedure",
    "bodyLocation": "Teeth and Jaw",
    "howPerformed": "Application of fixed or removable orthodontic appliances including metal braces, ceramic braces, and clear aligners to correct tooth alignment and jaw development.",
    "preparation": "Initial consultation, dental X-rays, and treatment planning by specialist orthodontist.",
    "followup": "Regular adjustment visits every 4-6 weeks throughout treatment duration."
  },
  "breadcrumb": {
    "@type": "BreadcrumbList",
    "itemListElement": [
      {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://mamunorthodental.com/"},
      {"@type": "ListItem", "position": 2, "name": "Services", "item": "https://mamunorthodental.com/#services"},
      {"@type": "ListItem", "position": 3, "name": "Braces Treatment", "item": "<?=$page_canonical?>"}
    ]
  }
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "How much do braces cost in Dhaka?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "The cost of braces in Dhaka, Bangladesh starts from ৳30,000 to ৳60,000 for metal braces, ৳45,000 to ৳80,000 for ceramic braces, and ৳80,000 to ৳1,50,000 for clear aligners depending on the complexity of alignment."
      }
    },
    {
      "@type": "Question",
      "name": "What is the best age to get braces?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "The ideal age to get braces is between 10 and 14 years old when permanent teeth have erupted and the jaw is still developing. However, orthodontic treatment is highly effective at any age, and early evaluation is recommended from age 7."
      }
    },
    {
      "@type": "Question",
      "name": "How long does braces treatment take?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Braces treatment usually takes between 12 to 24 months. Simple cases may be completed in 6 to 10 months, while highly complex misalignments may take up to 30 months."
      }
    },
    {
      "@type": "Question",
      "name": "Do braces hurt?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "There may be mild soreness for 2-3 days after the initial braces placement and after monthly adjustments. This discomfort is temporary and easily managed with standard over-the-counter pain relievers."
      }
    },
    {
      "@type": "Question",
      "name": "Can adults get braces?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes, orthodontic braces are highly effective for adults. Many patients in their 20s, 30s, and older choose ceramic braces or clear aligners to achieve a perfectly aligned smile."
      }
    }
  ]
}
</script>
</head>
<body class="font-sans bg-white text-gray-800">
<!-- Developed by Umaer Islam (https://umaerislam.com) -->

<!-- NAV -->
<nav class="fixed top-5 left-0 right-0 z-[100] px-4">
  <div class="max-w-6xl mx-auto">
    <div class="nav-pill flex items-center justify-between h-16 px-5 lg:px-8 bg-[#001230]/50 backdrop-blur-xl border border-white/10 rounded-3xl shadow-[0_8px_32px_rgba(0,0,0,.2)]">
      <a href="../index.php" class="flex items-center gap-3"><img src="../Logo.png" alt="Mamun's Ortho Dental Logo" class="w-10 h-10 object-contain"><div class="flex flex-col leading-none"><span class="text-white font-serif text-[17px] font-bold">Mamun's <span class="text-[#ea741b]">Ortho</span></span><span class="text-white/50 text-[8px] tracking-[.25em] uppercase font-bold mt-0.5">Dental Center</span></div></a>
      <div class="flex items-center gap-3">
        <a href="../index.php" class="hidden md:flex px-4 py-2 text-white/75 hover:text-white text-[13px] font-semibold rounded-full hover:bg-white/10 transition-all">Home</a>
        <a href="../dr-shamim-al-mamun.php" class="hidden md:flex px-4 py-2 text-white/75 hover:text-white text-[13px] font-semibold rounded-full hover:bg-white/10 transition-all">Doctor</a>
        <a href="../index.php#contact" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-[#ea741b] to-[#cf5e0e] text-white text-[11px] font-bold uppercase tracking-widest rounded-full shadow-[0_4px_20px_rgba(234,116,27,.4)] hover:scale-105 transition-all"><i class="fas fa-calendar-check text-xs"></i> Book Now</a>
      </div>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="bg-gradient-to-br from-[#000e22] via-[#002a60] to-[#004591] pt-32 pb-16 relative overflow-hidden">
  <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#ea741b]/10 rounded-full filter blur-[120px] -translate-y-1/3 translate-x-1/3"></div>
  <div class="max-w-5xl mx-auto px-5 lg:px-8 relative z-10">
    <nav aria-label="Breadcrumb" class="mb-6"><ol class="flex items-center gap-2 text-white/40 text-xs"><li><a href="../index.php" class="hover:text-white">Home</a></li><li><i class="fas fa-chevron-right text-[8px]"></i></li><li><a href="../index.php#services" class="hover:text-white">Services</a></li><li><i class="fas fa-chevron-right text-[8px]"></i></li><li class="text-[#ea741b] font-semibold">Braces Treatment</li></ol></nav>
    <div class="gold-bar mb-4"></div>
    <p class="text-[#ea741b] text-[11px] font-bold uppercase tracking-[.3em] mb-3">Core Specialty</p>
    <h1 class="font-serif text-4xl lg:text-5xl font-bold text-white leading-tight mb-4">Orthodontic Braces Treatment<br><span class="text-[#ea741b] italic font-light">in Dhaka, Bangladesh</span></h1>
    <p class="text-white/55 text-lg max-w-2xl mb-8">Expert orthodontic treatment by Dr. Shamim Al Mamun — metal braces, ceramic braces, and clear aligners for children and adults. 600+ successful cases in Lalmatia, Dhaka.</p>
    <a href="../index.php#contact" class="btn-glow inline-flex items-center gap-2 px-8 py-4 text-white font-bold text-sm uppercase tracking-widest rounded-2xl"><i class="fas fa-calendar-plus"></i> Book Free Consultation</a>
  </div>
</section>

<!-- CONTENT -->
<article class="py-20">
  <div class="max-w-5xl mx-auto px-5 lg:px-8">
    <div class="grid lg:grid-cols-3 gap-12">
      <div class="lg:col-span-2 prose prose-lg max-w-none text-gray-600">

        <!-- Medical Reviewer Badge (E-E-A-T) -->
        <div class="flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-2xl p-4 mb-8 not-prose">
          <div class="w-10 h-10 rounded-full bg-[#ea741b]/10 flex items-center justify-center text-[#ea741b] flex-shrink-0"><i class="fas fa-user-doctor"></i></div>
          <div class="text-xs text-gray-500 leading-snug">
            <p class="font-bold text-[#004591] text-sm">Medical Reviewer: Dr. Mohammad Shamim Al Mamun</p>
            <p>BDS (DDC), FCPS (Orthodontics) · Associate Professor & Head of Dept. of Orthodontics, Bangladesh Dental College · BMDC Reg. No. A-2133</p>
          </div>
        </div>

        <h2 class="font-serif text-2xl font-bold text-[#004591]">What is Orthodontic Treatment?</h2>
        <!-- AEO Answer Snippet -->
        <div class="bg-amber-50/50 border-l-4 border-[#ea741b] p-4 rounded-r-xl my-4 text-sm font-medium text-gray-700 leading-relaxed not-prose">
          <strong>Quick Summary:</strong> Orthodontic treatment is a specialized dental procedure that corrects misaligned teeth and jaw development (Dentofacial Orthopedics). By using braces or clear aligners, it improves facial aesthetics, bite function, and long-term oral health, preventing cavities and jaw issues.
        </div>
        <p><strong>Orthodontic treatment</strong> is a specialized branch of dentistry that focuses on correcting <strong>misaligned teeth and jaws</strong>. At <strong>Mamun's Ortho Dental in Lalmatia, Dhaka</strong>, we offer comprehensive orthodontic solutions including braces and aligners to help you achieve a straighter, healthier smile.</p>
        <p>Our clinic focuses on <strong>Dentofacial Orthopedics</strong> — the science of correcting both tooth alignment and jaw development — under the expert care of <a href="../dr-shamim-al-mamun.php" class="text-[#ea741b] font-semibold hover:underline">Dr. Mohammad Shamim Al Mamun</a>, a Consultant Orthodontist with <strong>FCPS in Orthodontics</strong> and over <strong>20 years of clinical experience</strong>.</p>

        <h2 class="font-serif text-2xl font-bold text-[#004591] !mt-10">Types of Braces Available</h2>
        <!-- AEO Answer Snippet -->
        <div class="bg-amber-50/50 border-l-4 border-[#ea741b] p-4 rounded-r-xl my-4 text-sm font-medium text-gray-700 leading-relaxed not-prose">
          <strong>Quick Summary:</strong> At our Lalmatia clinic, we offer three main types of braces: metal braces (cost-effective and efficient for complex alignments), ceramic braces (tooth-colored, aesthetic brackets for discreet alignment), and clear aligners (virtually invisible, removable custom trays for maximum comfort).
        </div>
        
        <h3 class="font-serif text-xl font-bold text-[#004591]">1. Metal Braces (Traditional)</h3>
        <p><strong>Metal braces</strong> remain the most popular and cost-effective option for orthodontic treatment in Dhaka. They use high-grade stainless steel brackets and archwires to gradually move teeth into the correct position. Modern metal braces are smaller, more comfortable, and more efficient than ever before.</p>
        <ul>
          <li>Most affordable option</li>
          <li>Effective for complex cases</li>
          <li>Treatment duration: 12–24 months</li>
        </ul>

        <h3 class="font-serif text-xl font-bold text-[#004591]">2. Ceramic Braces</h3>
        <p><strong>Ceramic braces</strong> work the same way as metal braces but use tooth-colored or clear brackets that blend with your natural teeth. They are a popular choice for adult patients who want a less noticeable orthodontic option.</p>
        <ul>
          <li>Less visible than metal braces</li>
          <li>Equally effective results</li>
          <li>Ideal for image-conscious adults</li>
        </ul>

        <h3 class="font-serif text-xl font-bold text-[#004591]">3. Clear Aligner System</h3>
        <p><strong>Clear aligners</strong> are virtually invisible, removable trays that gradually straighten teeth without brackets or wires. They are custom-made for each patient and offer maximum comfort and convenience.</p>
        <ul>
          <li>Nearly invisible</li>
          <li>Removable for eating and brushing</li>
          <li>Comfortable with no metal irritation</li>
        </ul>

        <h2 class="font-serif text-2xl font-bold text-[#004591] !mt-10">Benefits of Braces Treatment</h2>
        <ul>
          <li><strong>Straighter teeth</strong> and improved facial aesthetics</li>
          <li><strong>Better bite alignment</strong> — prevents jaw pain and TMJ issues</li>
          <li><strong>Easier cleaning</strong> — straighter teeth are less prone to decay</li>
          <li><strong>Increased confidence</strong> — a beautiful smile transforms self-esteem</li>
          <li><strong>Long-term oral health</strong> — proper alignment prevents future dental problems</li>
        </ul>

        <h2 class="font-serif text-2xl font-bold text-[#004591] !mt-10">Braces Treatment Process at Our Clinic</h2>
        <ol>
          <li><strong>Initial Consultation</strong> — Complete examination, X-rays, and treatment planning</li>
          <li><strong>Treatment Plan Discussion</strong> — Options, timeline, and cost explained transparently</li>
          <li><strong>Braces Placement</strong> — Comfortable, painless application by Dr. Mamun</li>
          <li><strong>Regular Adjustments</strong> — Monthly visits for wire adjustments and monitoring</li>
          <li><strong>Braces Removal & Retention</strong> — Retainers provided for lasting results</li>
        </ol>

        <h2 class="font-serif text-2xl font-bold text-[#004591] !mt-10">Frequently Asked Questions</h2>

        <div class="not-prose mt-4 p-5 bg-[#F8FAFD] border border-gray-100 rounded-2xl">
          <h4 class="font-bold text-[#004591] text-sm mb-2">Related Services</h4>
          <ul class="text-sm text-gray-600 space-y-1">
            <li>&bull; <a href="scaling-polishing-dhaka.php" class="text-[#ea741b] hover:underline">Scaling &amp; Polishing</a> &mdash; Clean teeth before braces placement</li>
            <li>&bull; <a href="teeth-whitening-dhaka.php" class="text-[#ea741b] hover:underline">Teeth Whitening</a> &mdash; Brighten your smile after braces removal</li>
          </ul>
        </div>
        
        <div class="space-y-3 not-prose">
          <?php foreach([
            ['How much do braces cost in Dhaka?', 'The cost of braces in Dhaka varies depending on the type (metal, ceramic, or clear aligners) and the complexity of your case. Metal braces generally start from ৳30,000–৳60,000, while ceramic and aligner options may cost more. Contact us for a personalised quote after consultation.'],
            ['What is the best age to get braces?', 'While braces can be applied at any age, the ideal time for an orthodontic evaluation is around age 7. For most patients, treatment between ages 10–14 produces the best results as the jaw is still developing. However, adult orthodontics is equally effective.'],
            ['How long does braces treatment take?', 'Treatment duration typically ranges from 12 to 24 months, depending on the severity of misalignment. Simple cases may be completed in as little as 6 months, while complex cases may take up to 30 months.'],
            ['Do braces hurt?', 'There may be mild discomfort for 2-3 days after placement and after each adjustment. Modern techniques and materials minimize pain significantly. Dr. Mamun uses a gentle approach to ensure patient comfort.'],
            ['Can adults get braces?', 'Absolutely. There is no age limit for orthodontic treatment. Many of our patients are adults in their 30s, 40s, and even 50s who achieve excellent results with braces or clear aligners.']
          ] as [$q, $a]): ?>
          <details class="group bg-[#F8FAFD] border border-gray-100 rounded-2xl overflow-hidden">
            <summary class="flex items-center justify-between p-5 cursor-pointer font-bold text-[#004591] text-sm hover:bg-gray-50 transition-colors"><?=$q?><i class="fas fa-chevron-down text-[#ea741b] text-xs transition-transform group-open:rotate-180"></i></summary>
            <div class="px-5 pb-5 text-gray-600 text-sm leading-relaxed"><?=$a?></div>
          </details>
          <?php endforeach; ?>
        </div>

      </div>

      <!-- Sidebar -->
      <div class="lg:col-span-1 space-y-6">
        <div class="bg-[#004591] text-white rounded-2xl p-6 sticky top-28">
          <h3 class="font-bold text-sm uppercase tracking-widest mb-4 text-white/60">Book Consultation</h3>
          <p class="text-white/80 text-sm mb-4">Get a personalised treatment plan from Dr. Shamim Al Mamun.</p>
          <a href="../index.php#contact" class="block w-full text-center py-3 bg-[#ea741b] hover:bg-[#cf5e0e] text-white text-[10px] font-bold uppercase tracking-widest rounded-xl transition-all mb-3">Book Appointment</a>
          <a href="https://wa.me/8801712718527?text=Hello!%20I%27d%20like%20to%20inquire%20about%20orthodontic%20braces." target="_blank" class="block w-full text-center py-3 bg-[#25d366]/20 hover:bg-[#25d366]/30 text-white text-[10px] font-bold uppercase tracking-widest rounded-xl transition-all"><i class="fab fa-whatsapp text-xs mr-1"></i> WhatsApp Us</a>
          <div class="mt-6 pt-4 border-t border-white/10 text-xs text-white/40 space-y-2">
            <p><i class="fas fa-map-marker-alt text-[#ea741b] w-4"></i> Lalmatia, Dhaka-1207</p>
            <p><i class="fas fa-clock text-[#ea741b] w-4"></i> Sat–Thu, 9 AM – 9 PM</p>
          </div>
        </div>

        <div class="bg-[#F8FAFD] border border-gray-100 rounded-2xl p-6">
          <h3 class="font-bold text-[#004591] text-sm uppercase tracking-widest mb-4">Other Services</h3>
          <ul class="space-y-2">
            <li><a href="scaling-polishing-dhaka.php" class="flex items-center gap-2 text-sm text-gray-600 hover:text-[#ea741b] transition-colors py-1"><i class="fas fa-chevron-right text-[8px] text-[#ea741b]"></i> Scaling &amp; Polishing</a></li>
            <li><a href="teeth-whitening-dhaka.php" class="flex items-center gap-2 text-sm text-gray-600 hover:text-[#ea741b] transition-colors py-1"><i class="fas fa-chevron-right text-[8px] text-[#ea741b]"></i> Tooth Whitening</a></li>
            <li><a href="root-canal-treatment-dhaka.php" class="flex items-center gap-2 text-sm text-gray-600 hover:text-[#ea741b] transition-colors py-1"><i class="fas fa-chevron-right text-[8px] text-[#ea741b]"></i> Root Canal Treatment</a></li>
            <li><a href="../index.php#services" class="flex items-center gap-2 text-sm text-gray-600 hover:text-[#ea741b] transition-colors py-1"><i class="fas fa-chevron-right text-[8px] text-[#ea741b]"></i> Tooth Extraction</a></li>
            <li><a href="../index.php#services" class="flex items-center gap-2 text-sm text-gray-600 hover:text-[#ea741b] transition-colors py-1"><i class="fas fa-chevron-right text-[8px] text-[#ea741b]"></i> Cosmetic Filling</a></li>
            <li><a href="../index.php#services" class="flex items-center gap-2 text-sm text-gray-600 hover:text-[#ea741b] transition-colors py-1"><i class="fas fa-chevron-right text-[8px] text-[#ea741b]"></i> Crown &amp; Bridge</a></li>
            <li><a href="../index.php#services" class="flex items-center gap-2 text-sm text-gray-600 hover:text-[#ea741b] transition-colors py-1"><i class="fas fa-chevron-right text-[8px] text-[#ea741b]"></i> Denture</a></li>
            <li><a href="../index.php#services" class="flex items-center gap-2 text-sm text-gray-600 hover:text-[#ea741b] transition-colors py-1"><i class="fas fa-chevron-right text-[8px] text-[#ea741b]"></i> Occlusal Splint</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</article>

<!-- FOOTER -->
<footer class="bg-[#000e22] py-10">
  <div class="max-w-6xl mx-auto px-5 text-center">
    <a href="../index.php" class="inline-flex items-center gap-3 mb-4"><img src="../Logo.png" alt="Mamun's Ortho Dental" class="w-8 h-8 object-contain"><span class="text-white font-serif text-lg font-bold">