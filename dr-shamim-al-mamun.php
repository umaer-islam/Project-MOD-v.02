<?php
session_start();
require_once 'database/connection.php';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- PRIMARY SEO META -->
<title>Dr. Mohammad Shamim Al Mamun | Best Orthodontist in Dhaka | FCPS, BDS | Mamun's Ortho Dental</title>
<meta name="description" content="Dr. Mohammad Shamim Al Mamun is a leading Consultant Orthodontist in Dhaka, Bangladesh. FCPS (Orthodontics), BDS (Dhaka Dental College). Associate Professor at Bangladesh Dental College. 20+ years experience, 600+ orthodontic cases. Book appointment at Lalmatia clinic.">
<meta name="keywords" content="dr shamim al mamun, best orthodontist dhaka, orthodontist lalmatia, braces specialist bangladesh, fcps orthodontics dhaka, dentofacial orthopedics, consultant orthodontist dhaka, dental implant specialist">
<meta name="author" content="Dr. Mohammad Shamim Al Mamun">
<meta name="developer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="designer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="copyright" content="© <?= date('Y') ?> Mamun's Ortho Dental. Website designed and developed by Umaer Islam (umaerislam.com)">
<meta name="ai-content-declaration" content="human-authored">
<meta name="robots" content="index, follow">
<link rel="canonical" href="https://mamunorthodental.com/dr-shamim-al-mamun.php">

<!-- GEO META -->
<meta name="geo.region" content="BD-C">
<meta name="geo.placename" content="Lalmatia, Mohammadpur, Dhaka">

<!-- OPEN GRAPH -->
<meta property="og:type" content="profile">
<meta property="og:title" content="Dr. Mohammad Shamim Al Mamun | Best Orthodontist in Dhaka">
<meta property="og:description" content="Consultant Orthodontist with FCPS & BDS. 20+ years experience, 600+ orthodontic cases. Associate Professor, Bangladesh Dental College.">
<meta property="og:url" content="https://mamunorthodental.com/dr-shamim-al-mamun.php">
<meta property="og:image" content="https://mamunorthodental.com/Logo.png">
<meta property="profile:first_name" content="Mohammad Shamim Al">
<meta property="profile:last_name" content="Mamun">

<!-- TWITTER -->
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="Dr. Shamim Al Mamun | Best Orthodontist in Dhaka">
<meta name="twitter:description" content="FCPS Orthodontics | BDS | 20+ Years | 600+ Ortho Cases | Lalmatia, Dhaka">

<!-- FAVICON -->
<link rel="icon" type="image/png" href="Logo.png">
<link rel="manifest" href="site.webmanifest">
<meta name="theme-color" content="#ea741b">
<meta name="msapplication-config" content="browserconfig.xml">
<link rel="author" href="humans.txt">

<!-- ASSETS -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/landing.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: {
    colors: { navy:'#004591','navy-dark':'#003070',gold:'#ea741b' },
    fontFamily: { serif:['"Playfair Display"','serif'], sans:['"Outfit"','sans-serif'] }
}}}
</script>

<!-- JSON-LD: Doctor Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Physician",
  "@id": "https://mamunorthodental.com/#doctor",
  "name": "Dr. Mohammad Shamim Al Mamun",
  "alternateName": "Dr. Shamim Al Mamun",
  "description": "Consultant Orthodontist & Dentofacial Orthopedics specialist in Dhaka, Bangladesh with 20+ years of clinical experience.",
  "image": "https://mamunorthodental.com/Logo.png",
  "url": "https://mamunorthodental.com/dr-shamim-al-mamun.php",
  "telephone": "+8801712718527",
  "identifier": {
    "@type": "PropertyValue",
    "name": "BMDC Registration Number",
    "value": "A-2133"
  },
  "medicalSpecialty": ["Orthodontics", "DentofacialOrthopedics", "Implantology"],
  "qualification": [
    {"@type": "EducationalOccupationalCredential", "name": "BDS (Bachelor of Dental Surgery)", "recognizedBy": {"@type": "Organization", "name": "Dhaka Dental College"}},
    {"@type": "EducationalOccupationalCredential", "name": "FCPS (Orthodontics)", "recognizedBy": {"@type": "Organization", "name": "Bangladesh College of Physicians and Surgeons"}}
  ],
  "memberOf": [
    {"@type": "Organization", "name": "Bangladesh Dental College"},
    {"@type": "Organization", "name": "Labaid Hospital, Dhanmondi"},
    {"@type": "Organization", "name": "Bangladesh Medical & Dental Council (BMDC)"}
  ],
  "worksFor": {
    "@type": "Dentist",
    "@id": "https://mamunorthodental.com/#clinic",
    "name": "Mamun's Ortho Dental",
    "url": "https://mamunorthodental.com/"
  },
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "5/2 (2nd Floor), Block A, Road 5, Lalmatia",
    "addressLocality": "Mohammadpur",
    "addressRegion": "Dhaka",
    "postalCode": "1207",
    "addressCountry": "BD"
  },
  "sameAs": [
    "https://www.facebook.com/mamundental"
  ]
}
</script>
</head>

<body class="font-sans bg-white text-gray-800">
<!-- Developed by Umaer Islam (https://umaerislam.com) -->

<!-- NAVBAR (simplified for inner pages) -->
<nav class="fixed top-5 left-0 right-0 z-[100] px-4">
  <div class="max-w-6xl mx-auto">
    <div class="nav-pill flex items-center justify-between h-16 md:h-[72px] px-5 lg:px-8 bg-[#001230]/50 backdrop-blur-xl border border-white/10 rounded-3xl shadow-[0_8px_32px_rgba(0,0,0,.2)]">
      <a href="index.php" class="flex items-center gap-3 group">
        <img src="Logo.png" alt="Mamun's Ortho Dental Logo - Best Dental Clinic Lalmatia Dhaka" class="w-10 h-10 object-contain shadow-lg group-hover:scale-105 transition-transform drop-shadow-md">
        <div class="flex flex-col leading-none">
          <span class="text-white font-serif text-[17px] md:text-xl font-bold">Mamun's <span class="text-[#ea741b]">Ortho</span></span>
          <span class="text-white/50 text-[8px] tracking-[.25em] uppercase font-bold mt-0.5">Dental Center</span>
        </div>
      </a>
      <div class="flex items-center gap-3">
        <a href="index.php" class="hidden md:flex px-4 py-2 text-white/75 hover:text-white text-[13px] font-semibold rounded-full hover:bg-white/10 transition-all">Home</a>
        <a href="index.php#services" class="hidden md:flex px-4 py-2 text-white/75 hover:text-white text-[13px] font-semibold rounded-full hover:bg-white/10 transition-all">Services</a>
        <a href="index.php#contact" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-[#ea741b] to-[#cf5e0e] text-white text-[11px] font-bold uppercase tracking-widest rounded-full shadow-[0_4px_20px_rgba(234,116,27,.4)] transition-all hover:scale-105"><i class="fas fa-calendar-check text-xs"></i> Book Now</a>
      </div>
    </div>
  </div>
</nav>

<!-- HERO BANNER -->
<section class="bg-gradient-to-br from-[#000e22] via-[#002a60] to-[#004591] pt-32 pb-20 relative overflow-hidden">
  <div class="absolute inset-0 opacity-[.03]"><svg width="100%" height="100%"><defs><pattern id="dg" width="60" height="60" patternUnits="userSpaceOnUse"><path d="M 60 0 L 0 0 0 60" fill="none" stroke="white" stroke-width=".5"/></pattern></defs><rect width="100%" height="100%" fill="url(#dg)"/></svg></div>
  <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#ea741b]/10 rounded-full filter blur-[120px] -translate-y-1/3 translate-x-1/3"></div>

  <div class="max-w-6xl mx-auto px-5 lg:px-8 relative z-10">
    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" class="mb-8">
      <ol class="flex items-center gap-2 text-white/40 text-xs">
        <li><a href="index.php" class="hover:text-white transition-colors">Home</a></li>
        <li><i class="fas fa-chevron-right text-[8px]"></i></li>
        <li><a href="index.php#doctors" class="hover:text-white transition-colors">Doctors</a></li>
        <li><i class="fas fa-chevron-right text-[8px]"></i></li>
        <li class="text-[#ea741b] font-semibold">Dr. Shamim Al Mamun</li>
      </ol>
    </nav>

    <div class="grid lg:grid-cols-12 gap-12 items-center">
      <!-- Photo / Avatar -->
      <div class="lg:col-span-4 flex justify-center">
        <div class="relative">
          <div class="w-56 h-56 lg:w-64 lg:h-64 rounded-3xl bg-gradient-to-br from-white/10 to-white/5 border border-white/15 backdrop-blur-xl flex items-center justify-center shadow-2xl">
            <i class="fas fa-user-doctor text-7xl text-white/60"></i>
          </div>
          <!-- BMDC Badge -->
          <div class="absolute -bottom-4 -right-4 bg-white rounded-2xl px-4 py-2.5 shadow-2xl">
            <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">BMDC Reg.</p>
            <p class="text-[#004591] font-bold text-sm">A-2133</p>
          </div>
          <!-- Specialty Badge -->
          <div class="absolute -top-3 -left-3 bg-[#ea741b] text-white text-[9px] font-bold uppercase tracking-widest px-3 py-1.5 rounded-xl shadow-lg">Orthodontist</div>
        </div>
      </div>

      <!-- Info -->
      <div class="lg:col-span-8">
        <div class="gold-bar mb-4"></div>
        <p class="text-[#ea741b] text-[11px] font-bold uppercase tracking-[.3em] mb-2">Consultant Orthodontist & Dentofacial Orthopedics Specialist</p>
        <h1 class="font-serif text-4xl lg:text-5xl font-bold text-white leading-tight mb-4">Dr. Mohammad Shamim<br>Al Mamun</h1>
        <p class="text-white/50 text-sm mb-6">BDS (Dhaka Dental College) | FCPS (Orthodontics, BCPS) | Special Training in Implantology</p>

        <!-- Quick Stats -->
        <div class="flex flex-wrap gap-4 mb-8">
          <div class="bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-center">
            <p class="text-white font-serif text-2xl font-bold">20+</p>
            <p class="text-white/40 text-[9px] font-bold uppercase tracking-widest">Years Exp.</p>
          </div>
          <div class="bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-center">
            <p class="text-white font-serif text-2xl font-bold">600+</p>
            <p class="text-white/40 text-[9px] font-bold uppercase tracking-widest">Ortho Cases</p>
          </div>
          <div class="bg-white/5 border border-white/10 rounded-2xl px-5 py-3 text-center">
            <p class="text-white font-serif text-2xl font-bold">15K+</p>
            <p class="text-white/40 text-[9px] font-bold uppercase tracking-widest">Patients</p>
          </div>
          <div class="bg-[#ea741b]/10 border border-[#ea741b]/30 rounded-2xl px-5 py-3 text-center">
            <p class="text-[#ea741b] font-serif text-2xl font-bold">4.9</p>
            <p class="text-[#ea741b]/60 text-[9px] font-bold uppercase tracking-widest">Rating</p>
          </div>
        </div>

        <a href="index.php#contact" class="btn-glow inline-flex items-center gap-2.5 px-8 py-4 text-white font-bold text-sm uppercase tracking-widest rounded-2xl"><i class="fas fa-calendar-plus"></i> Book Consultation with Dr. Mamun</a>
      </div>
    </div>
  </div>
</section>

<!-- BIOGRAPHY -->
<section class="py-20 bg-white">
  <div class="max-w-5xl mx-auto px-5 lg:px-8">
    <div class="grid lg:grid-cols-3 gap-12">
      <!-- Main Bio -->
      <div class="lg:col-span-2 prose prose-lg max-w-none">
        <h2 class="font-serif text-3xl font-bold text-[#004591] mb-6">About Dr. Shamim Al Mamun</h2>
        
        <p><strong>Dr. Mohammad Shamim Al Mamun</strong> is one of the most experienced and trusted <strong>orthodontists in Dhaka, Bangladesh</strong>. With over <strong>20 years of clinical experience</strong> in orthodontics and dentofacial orthopedics, he has successfully treated more than <strong>600 orthodontic patients</strong> and contributed to the dental health of over <strong>15,000 patients</strong> across Dhaka.</p>

        <p>Dr. Mamun earned his <strong>Bachelor of Dental Surgery (BDS)</strong> from the prestigious <strong>Dhaka Dental College</strong> and went on to complete his <strong>FCPS in Orthodontics</strong> from the <strong>Bangladesh College of Physicians and Surgeons (BCPS)</strong> the highest postgraduate medical qualification in Bangladesh. He also holds specialized training in <strong>Implantology</strong>.</p>

        <h3 class="font-serif text-xl font-bold text-[#004591] !mt-8">Academic & Professional Positions</h3>
        <ul class="space-y-2">
          <li><strong>Associate Professor & Head</strong>, Department of Orthodontics  <em>Bangladesh Dental College</em></li>
          <li><strong>Consultant Orthodontist</strong>  <em>Labaid Hospital, Dhanmondi, Dhaka</em></li>
          <li><strong>Founder & Lead Orthodontist</strong>  <em>Mamun's Ortho Dental, Lalmatia</em></li>
        </ul>

        <h3 class="font-serif text-xl font-bold text-[#004591] !mt-8">Areas of Expertise</h3>
        <p>Dr. Mamun specializes in <strong>Dentofacial Orthopedics</strong>  the science of correcting both tooth alignment and jaw development. His clinical focus includes:</p>
        <ul class="space-y-1">
          <li><strong>Orthodontic Braces</strong>  Metal, ceramic, and self-ligating bracket systems</li>
          <li><strong>Clear Aligner Therapy</strong>  Virtually invisible removable aligners</li>
          <li><strong>Removable Orthodontic Appliances</strong>  Functional and corrective devices</li>
          <li><strong>Dental Implant Planning</strong>  Surgical and prosthetic implant solutions</li>
          <li><strong>Complex Bite Correction</strong>  Overbite, underbite, crossbite, and open bite treatment</li>
        </ul>

        <h3 class="font-serif text-xl font-bold text-[#004591] !mt-8">Why Patients Choose Dr. Mamun</h3>
        <p>Patients from across Dhaka including <strong>Lalmatia</strong>, <strong>Mohammadpur</strong>, <strong>Dhanmondi</strong>, <strong>Mirpur</strong>, <strong>Uttara</strong>, and <strong>Gulshan</strong>  seek Dr. Mamun's expertise for his combination of academic rigor, gentle chairside manner, and consistently excellent treatment outcomes. His clinic in <strong>Lalmatia</strong> is equipped with modern digital imaging, strict WHO-standard sterilisation, and a comfortable, family-friendly environment.</p>

        <h3 class="font-serif text-xl font-bold text-[#004591] !mt-8">Frequently Asked Questions</h3>
        
        <div class="space-y-4 not-prose">
          <?php foreach([
            ['Where is Dr. Shamim Al Mamun\'s clinic located?', 'Dr. Mamun\'s clinic — Mamun\'s Ortho Dental — is located at 5/2 (2nd Floor), Block A, Road 5, Lalmatia, Mohammadpur, Dhaka-1207, Bangladesh. It is open Saturday to Thursday, 9 AM to 9 PM.'],
            ['What are Dr. Mamun\'s qualifications?', 'Dr. Mamun holds a BDS from Dhaka Dental College and FCPS in Orthodontics from BCPS. He is also specially trained in Implantology and serves as Associate Professor at Bangladesh Dental College.'],
            ['How much does braces treatment cost with Dr. Mamun?', 'Braces treatment cost varies depending on the type (metal, ceramic, or clear aligners) and complexity of the case. Please contact the clinic directly for a personalised consultation and quote.'],
            ['Does Dr. Mamun treat children for orthodontics?', 'Yes. Dr. Mamun treats orthodontic patients of all ages, including children as young as 7 years old. Early intervention can prevent more complex issues later.'],
            ['How can I book an appointment with Dr. Shamim Al Mamun?', 'You can book an appointment by calling the clinic, sending a WhatsApp message, or using the online booking form on our website.']
          ] as $i => [$q, $a]): ?>
          <details class="group bg-[#F8FAFD] border border-gray-100 rounded-2xl overflow-hidden">
            <summary class="flex items-center justify-between p-5 cursor-pointer font-bold text-[#004591] text-sm hover:bg-gray-50 transition-colors">
              <?=$q?>
              <i class="fas fa-chevron-down text-[#ea741b] text-xs transition-transform group-open:rotate-180"></i>
            </summary>
            <div class="px-5 pb-5 text-gray-600 text-sm leading-relaxed"><?=$a?></div>
          </details>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="lg:col-span-1 space-y-6">
        <!-- Qualifications Card -->
        <div class="bg-[#F8FAFD] border border-gray-100 rounded-2xl p-6">
          <h3 class="font-bold text-[#004591] text-sm uppercase tracking-widest mb-4">Qualifications</h3>
          <div class="space-y-3">
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-lg bg-[#ea741b]/10 flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-graduation-cap text-[#ea741b] text-xs"></i></div>
              <div><p class="font-bold text-[#004591] text-sm">BDS</p><p class="text-gray-400 text-xs">Dhaka Dental College</p></div>
            </div>
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-lg bg-[#ea741b]/10 flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-award text-[#ea741b] text-xs"></i></div>
              <div><p class="font-bold text-[#004591] text-sm">FCPS (Orthodontics)</p><p class="text-gray-400 text-xs">BCPS, Bangladesh</p></div>
            </div>
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-lg bg-[#ea741b]/10 flex items-center justify-center flex-shrink-0 mt-0.5"><i class="fas fa-tooth text-[#ea741b] text-xs"></i></div>
              <div><p class="font-bold text-[#004591] text-sm">Implantology</p><p class="text-gray-400 text-xs">Special Training</p></div>
            </div>
          </div>
        </div>

        <!-- Clinic Info Card -->
        <div class="bg-[#004591] text-white rounded-2xl p-6">
          <h3 class="font-bold text-sm uppercase tracking-widest mb-4 text-white/60">Visit the Clinic</h3>
          <div class="space-y-3 text-sm">
            <p class="flex items-start gap-2"><i class="fas fa-map-marker-alt text-[#ea741b] mt-0.5 text-xs w-4"></i> 5/4 (2nd Floor), Block A, Road 5, Lalmatia, Mohammadpur, Dhaka-1207</p>
            <p class="flex items-center gap-2"><i class="fas fa-clock text-[#ea741b] text-xs w-4"></i> Sat-Thu, 9 AM - 9 PM</p>
            <p class="flex items-center gap-2"><i class="fas fa-phone text-[#ea741b] text-xs w-4"></i> +880 1712-718527</p>
            <p class="flex items-center gap-2"><i class="fab fa-whatsapp text-[#25d366] text-xs w-4"></i> <a href="https://wa.me/8801712718527" target="_blank" class="hover:text-[#25d366] transition-colors">WhatsApp Us</a></p>
            <p class="flex items-center gap-2"><i class="fas fa-envelope text-[#ea741b] text-xs w-4"></i> mamunddcbdc@gmail.com</p>
          </div>
          <a href="index.php#contact" class="mt-6 block w-full text-center py-3 bg-[#ea741b] hover:bg-[#cf5e0e] text-white text-[10px] font-bold uppercase tracking-widest rounded-xl transition-all">Book Appointment</a>
        </div>

        <!-- Services Quick Links -->
        <div class="bg-[#F8FAFD] border border-gray-100 rounded-2xl p-6">
          <h3 class="font-bold text-[#004591] text-sm uppercase tracking-widest mb-4">Our Services</h3>
          <ul class="space-y-2">
            <?php foreach([
              'Orthodontic Treatment','Scaling & Polishing','Tooth Whitening',
              'Root Canal Treatment','Tooth Extraction','Cosmetic Filling',
              'Crown & Bridge','Denture','Occlusal Splint'
            ] as $svc): ?>
            <li><a href="index.php#services" class="flex items-center gap-2 text-sm text-gray-600 hover:text-[#ea741b] transition-colors py-1"><i class="fas fa-chevron-right text-[8px] text-[#ea741b]"></i> <?=$svc?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="py-16 bg-gradient-to-r from-[#004591] to-[#002a60]">
  <div class="max-w-4xl mx-auto px-5 text-center">
    <h2 class="font-serif text-3xl font-bold text-white mb-4">Ready to Transform Your Smile?</h2>
    <p class="text-white/60 text-sm mb-8 max-w-lg mx-auto">Book a consultation with Dr. Shamim Al Mamun at Mamun's Ortho Dental, Lalmatia, Dhaka. Your journey to a perfect smile starts here.</p>
    <div class="flex flex-wrap justify-center gap-4">
      <a href="index.php#contact" class="btn-glow inline-flex items-center gap-2 px-8 py-4 text-white font-bold text-sm uppercase tracking-widest rounded-2xl"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
      <a href="https://wa.me/8801712718527?text=Hello!%20I%27d%20like%20to%20book%20a%20consultation%20with%20Dr.%20Shamim." target="_blank" class="inline-flex items-center gap-2 px-8 py-4 border-2 border-[#25d366]/50 hover:border-[#25d366] text-white font-bold text-sm uppercase tracking-widest rounded-2xl transition-all hover:bg-[#25d366]/15"><i class="fab fa-whatsapp"></i> WhatsApp Us</a>
    </div>
  </div>
</section>

<!-- FOOTER (minimal) -->
<footer class="bg-[#000e22] py-10">
  <div class="max-w-6xl mx-auto px-5 text-center">
    <a href="index.php" class="inline-flex items-center gap-3 mb-4">
      <img src="Logo.png" alt="Mamun's Ortho Dental Logo" class="w-8 h-8 object-contain">
      <span class="text-white font-serif text-lg font-bold">Mamun's <span class="text-[#ea741b]">Ortho</span> Dental</span>
    </a>
    <p class="text-white/25 text-xs">&copy; <?=date('Y')?> Mamun's Ortho Dental. All rights reserved. | Lalmatia, Mohammadpur, Dhaka-1207</p>
  </div>
</footer>

</body>
</html>
