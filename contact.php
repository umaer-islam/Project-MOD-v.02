<?php
session_start();
require_once 'components/assets.php';
$page_title = "Contact Mamun's Ortho Dental | Best Dental Clinic in Dhaka";
$page_desc = "Contact Mamun's Ortho Dental in Lalmatia, Dhaka. Book an appointment with Dr. Shamim Al Mamun for braces, root canal, teeth whitening, and general dental care. Open Saturday–Thursday, 9 AM–9 PM.";
$page_canonical = "https://mamunorthodental.com/contact.php";

$contact_success = false;
$contact_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($phone)) {
        $contact_error = 'Please provide your name and phone number.';
    } else {
        $contact_success = true;
    }
}

date_default_timezone_set('Asia/Dhaka');
$day = date('N');
$hour = (int)date('H');
$is_open = ($day != 5 && $hour >= 9 && $hour < 21);
$status_text = $is_open ? 'Open Now' : 'Closed Now';
$status_color = $is_open ? 'text-emerald-600' : 'text-red-500';
$status_bg = $is_open ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200';
$status_dot = $is_open ? 'bg-emerald-500' : 'bg-red-400';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?=$page_title?></title>
<meta name="description" content="<?=$page_desc?>">
<meta name="robots" content="index, follow">
<meta name="developer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="designer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="copyright" content="© <?= date('Y') ?> Mamun's Ortho Dental. Website designed and developed by Umaer Islam (umaerislam.com)">
<meta name="ai-content-declaration" content="human-authored">
<link rel="canonical" href="<?=$page_canonical?>">
<link rel="icon" type="image/png" href="Logo.png">
<link rel="manifest" href="site.webmanifest">
<meta name="theme-color" content="#ea741b">
<meta name="msapplication-config" content="browserconfig.xml">
<link rel="author" href="humans.txt">
<meta name="geo.region" content="BD-C">
<meta name="geo.placename" content="Lalmatia, Mohammadpur, Dhaka">
<meta property="og:type" content="website">
<meta property="og:title" content="Contact Mamun's Ortho Dental | Best Dental Clinic in Dhaka">
<meta property="og:description" content="Book an appointment with Dr. Shamim Al Mamun. Braces, root canal, teeth whitening, and general dental care in Lalmatia, Dhaka.">
<meta property="og:url" content="<?=$page_canonical?>">
<meta property="og:image" content="https://mamunorthodental.com/Logo.png">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="Contact Mamun's Ortho Dental">
<meta name="twitter:description" content="Book an appointment. Braces, root canal, whitening & more in Lalmatia, Dhaka.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= asset('assets/css/landing.css') ?>">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#004591','navy-dark':'#003070',gold:'#ea741b'},fontFamily:{serif:['"Playfair Display"','serif'],sans:['"Outfit"','sans-serif']}}}}</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Dentist",
  "name": "Mamun's Ortho Dental",
  "description": "Best dental clinic in Dhaka offering braces, root canal, teeth whitening, dental implants, and general dental care.",
  "url": "<?=$page_canonical?>",
  "telephone": "+8801712718527",
  "email": "mamunddcbdc@gmail.com",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "5/4 (2nd Floor), Block A, Road 5, Lalmatia",
    "addressLocality": "Mohammadpur",
    "addressRegion": "Dhaka",
    "postalCode": "1207",
    "addressCountry": "BD"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "23.7640",
    "longitude": "90.3620"
  },
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Saturday","Sunday","Monday","Tuesday","Wednesday","Thursday"],
      "opens": "09:00",
      "closes": "21:00"
    }
  ],
  "priceRange": "$$",
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "4.9",
    "reviewCount": "200"
  },
  "hasMap": "https://maps.app.goo.gl/MJg2zb1qWj8xq2Uq6",
  "medicalSpecialty": ["Orthodontics", "Dentistry", "Endodontics", "Prosthodontics"]
}
</script>
</head>
<body class="font-sans bg-white text-gray-800">

<!-- Nav -->
<nav class="fixed top-5 left-0 right-0 z-[100] px-4">
  <div class="max-w-6xl mx-auto">
    <div class="nav-pill flex items-center justify-between h-16 px-5 lg:px-8 bg-[#001230]/50 backdrop-blur-xl border border-white/10 rounded-3xl shadow-[0_8px_32px_rgba(0,0,0,.2)]">
      <a href="index.php" class="flex items-center gap-3">
        <img src="Logo.png" alt="Mamun's Ortho Dental Logo" class="w-10 h-10 object-contain">
        <div class="flex flex-col leading-none">
          <span class="text-white font-serif text-[17px] font-bold">Mamun's <span class="text-[#ea741b]">Ortho</span></span>
          <span class="text-white/50 text-[8px] tracking-[.25em] uppercase font-bold mt-0.5">Dental Center</span>
        </div>
      </a>
      <div class="flex items-center gap-3">
        <a href="index.php" class="hidden md:flex px-4 py-2 text-white/75 hover:text-white text-[13px] font-semibold rounded-full hover:bg-white/10 transition-all">Home</a>
        <a href="index.php#services" class="hidden md:flex px-4 py-2 text-white/75 hover:text-white text-[13px] font-semibold rounded-full hover:bg-white/10 transition-all">Services</a>
        <a href="index.php#about" class="hidden md:flex px-4 py-2 text-white/75 hover:text-white text-[13px] font-semibold rounded-full hover:bg-white/10 transition-all">About</a>
        <a href="tel:+8801712718527" class="flex items-center gap-2 px-4 py-2 bg-[#ea741b] hover:bg-[#cf5e0e] text-white text-[11px] font-bold uppercase tracking-widest rounded-full transition-all">
          <i class="fas fa-phone text-[10px]"></i> <span class="hidden sm:inline">Call Now</span>
        </a>
      </div>
    </div>
  </div>
</nav>

<!-- Hero -->
<section class="bg-[#f6f8fb] pt-32 pb-16 relative overflow-hidden">
  <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#ea741b]/[0.03] rounded-full filter blur-[120px]"></div>
  <div class="max-w-6xl mx-auto px-5 lg:px-8 relative z-10">
    <p class="text-[#ea741b] text-[10px] font-bold uppercase tracking-[.35em] mb-3"><a href="index.php" class="hover:underline">Home</a> <span class="mx-2 text-gray-300">/</span> Contact</p>
    <h1 class="font-serif text-4xl lg:text-5xl font-bold text-[#001630] leading-tight mb-4">Get in Touch</h1>
    <p class="text-gray-500 text-[15px] max-w-xl leading-relaxed">Book an appointment, ask a question, or visit us at our Lalmatia clinic. We're here to help you achieve your best smile.</p>
  </div>
</section>

<!-- Quick Contact Cards -->
<section class="py-12 bg-white">
  <div class="max-w-6xl mx-auto px-5 lg:px-8">
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">

      <a href="tel:+8801712718527" class="group flex items-center gap-4 p-5 bg-[#f6f8fb] hover:bg-[#004591] border border-gray-100 hover:border-[#004591] rounded-2xl transition-all duration-300">
        <div class="w-12 h-12 rounded-xl bg-[#004591]/10 group-hover:bg-white/20 flex items-center justify-center flex-shrink-0 transition-colors">
          <i class="fas fa-phone text-[#004591] group-hover:text-white text-base transition-colors"></i>
        </div>
        <div>
          <p class="text-[10px] font-bold uppercase tracking-[.1em] text-gray-400 group-hover:text-white/60 transition-colors">Phone</p>
          <p class="text-sm font-bold text-[#001630] group-hover:text-white mt-0.5 transition-colors">+880 1712-718527</p>
        </div>
      </a>

      <a href="https://wa.me/8801712718527?text=Hello!%20I%27d%20like%20to%20book%20an%20appointment." target="_blank" class="group flex items-center gap-4 p-5 bg-[#f6f8fb] hover:bg-[#25d366] border border-gray-100 hover:border-[#25d366] rounded-2xl transition-all duration-300">
        <div class="w-12 h-12 rounded-xl bg-[#25d366]/10 group-hover:bg-white/20 flex items-center justify-center flex-shrink-0 transition-colors">
          <i class="fab fa-whatsapp text-[#25d366] group-hover:text-white text-base transition-colors"></i>
        </div>
        <div>
          <p class="text-[10px] font-bold uppercase tracking-[.1em] text-gray-400 group-hover:text-white/60 transition-colors">WhatsApp</p>
          <p class="text-sm font-bold text-[#001630] group-hover:text-white mt-0.5 transition-colors">Chat With Us</p>
        </div>
      </a>

      <a href="mailto:mamunddcbdc@gmail.com" class="group flex items-center gap-4 p-5 bg-[#f6f8fb] hover:bg-[#ea741b] border border-gray-100 hover:border-[#ea741b] rounded-2xl transition-all duration-300">
        <div class="w-12 h-12 rounded-xl bg-[#ea741b]/10 group-hover:bg-white/20 flex items-center justify-center flex-shrink-0 transition-colors">
          <i class="fas fa-envelope text-[#ea741b] group-hover:text-white text-base transition-colors"></i>
        </div>
        <div>
          <p class="text-[10px] font-bold uppercase tracking-[.1em] text-gray-400 group-hover:text-white/60 transition-colors">Email</p>
          <p class="text-sm font-bold text-[#001630] group-hover:text-white mt-0.5 transition-colors">mamunddcbdc@gmail.com</p>
        </div>
      </a>

      <a href="https://maps.app.goo.gl/MJg2zb1qWj8xq2Uq6" target="_blank" class="group flex items-center gap-4 p-5 bg-[#f6f8fb] hover:bg-[#001630] border border-gray-100 hover:border-[#001630] rounded-2xl transition-all duration-300">
        <div class="w-12 h-12 rounded-xl bg-[#001630]/10 group-hover:bg-white/20 flex items-center justify-center flex-shrink-0 transition-colors">
          <i class="fas fa-location-dot text-[#001630] group-hover:text-white text-base transition-colors"></i>
        </div>
        <div>
          <p class="text-[10px] font-bold uppercase tracking-[.1em] text-gray-400 group-hover:text-white/60 transition-colors">Location</p>
          <p class="text-sm font-bold text-[#001630] group-hover:text-white mt-0.5 transition-colors">Lalmatia, Dhaka-1207</p>
        </div>
      </a>

    </div>
  </div>
</section>

<!-- Map + Form -->
<section class="py-16 bg-[#f6f8fb]">
  <div class="max-w-6xl mx-auto px-5 lg:px-8">
    <div class="grid lg:grid-cols-2 gap-8 items-stretch">

      <!-- Map + Hours -->
      <div class="space-y-6">
        <!-- Map -->
        <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm h-[300px] lg:h-[340px]">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.8!2d90.362!3d23.764!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjPCsDQ1JzUwLjQiTiA5MMKwMjEnNDMuMiJF!5e0!3m2!1sen!2sbd!4v1700000000000!5m2!1sen!2sbd" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Mamun's Ortho Dental Location"></iframe>
        </div>

        <!-- Clinic Hours -->
        <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
          <div class="flex items-center justify-between mb-5">
            <h3 class="font-serif text-lg font-bold text-[#001630] flex items-center gap-2"><i class="fas fa-clock text-[#ea741b] text-sm"></i> Clinic Hours</h3>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border <?=$status_bg?>">
              <span class="w-1.5 h-1.5 rounded-full <?=$status_dot?>"></span>
              <span class="<?=$status_color?> text-[10px] font-bold uppercase tracking-widest"><?=$status_text?></span>
            </span>
          </div>
          <div class="space-y-3">
            <div class="flex items-center justify-between py-2.5 border-b border-gray-50">
              <span class="text-sm text-gray-500">Saturday – Thursday</span>
              <span class="text-sm font-bold text-[#001630]">9:00 AM – 9:00 PM</span>
            </div>
            <div class="flex items-center justify-between py-2.5 border-b border-gray-50">
              <span class="text-sm text-gray-500">Friday</span>
              <span class="text-sm font-bold text-red-500">Closed</span>
            </div>
            <div class="flex items-center justify-between py-2.5">
              <span class="text-sm text-gray-500">Public Holidays</span>
              <span class="text-sm font-bold text-[#ea741b]">By Appointment</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact Form -->
      <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm">
        <h3 class="font-serif text-2xl font-bold text-[#001630] mb-1">Book Appointment</h3>
        <p class="text-gray-400 text-sm mb-8">Fill out the form below and we'll get back to you within 30 minutes.</p>

        <?php if($contact_success): ?>
        <div class="flex items-center gap-4 bg-emerald-50 border border-emerald-200 rounded-2xl p-5 mb-6">
          <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-emerald-600"></i></div>
          <div><p class="text-emerald-800 font-bold text-sm">Request Received!</p><p class="text-emerald-600 text-xs mt-0.5">Our team will contact you shortly.</p></div>
        </div>
        <?php endif; ?>
        <?php if($contact_error): ?>
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 rounded-2xl p-4 mb-6"><i class="fas fa-exclamation-circle text-red-400"></i><p class="text-red-700 text-sm"><?=htmlspecialchars($contact_error)?></p></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
          <input type="hidden" name="contact_submit" value="1">
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-[.1em] text-gray-400 mb-2">Full Name *</label>
              <input type="text" name="name" placeholder="e.g. Rahim Uddin" required class="w-full bg-[#f6f8fb] border border-gray-200 rounded-xl px-4 py-3.5 text-sm text-gray-800 focus:border-[#004591] focus:ring-2 focus:ring-[#004591]/10 outline-none transition-all placeholder:text-gray-400">
            </div>
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-[.1em] text-gray-400 mb-2">Phone Number *</label>
              <input type="tel" inputmode="numeric" pattern="[0-9]*" name="phone" placeholder="01XXXXXXXXX" required class="w-full bg-[#f6f8fb] border border-gray-200 rounded-xl px-4 py-3.5 text-sm text-gray-800 focus:border-[#004591] focus:ring-2 focus:ring-[#004591]/10 outline-none transition-all placeholder:text-gray-400">
            </div>
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-[.1em] text-gray-400 mb-2">Service (Optional)</label>
            <div class="mod-dropdown" data-name="service" data-placeholder="Select a service">
              <input type="hidden" name="service" value="">
              <div class="mod-dropdown-trigger">
                <span class="mod-dropdown-selected">Select a service</span>
                <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
              </div>
              <div class="mod-dropdown-panel">
                <div class="mod-dropdown-option" data-value=""><span class="opt-check"></span><span>Select a service</span></div>
                <div class="mod-dropdown-option" data-value="Orthodontics"><span class="opt-check"></span><span>Braces / Orthodontics</span></div>
                <div class="mod-dropdown-option" data-value="Consultation"><span class="opt-check"></span><span>General Consultation</span></div>
                <div class="mod-dropdown-option" data-value="Root Canal"><span class="opt-check"></span><span>Root Canal Treatment</span></div>
                <div class="mod-dropdown-option" data-value="Whitening"><span class="opt-check"></span><span>Teeth Whitening</span></div>
                <div class="mod-dropdown-option" data-value="Implant"><span class="opt-check"></span><span>Dental Implant</span></div>
                <div class="mod-dropdown-option" data-value="Extraction"><span class="opt-check"></span><span>Tooth Extraction</span></div>
                <div class="mod-dropdown-option" data-value="Cleaning"><span class="opt-check"></span><span>Scaling & Cleaning</span></div>
                <div class="mod-dropdown-option" data-value="Other"><span class="opt-check"></span><span>Other</span></div>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-[10px] font-bold uppercase tracking-[.1em] text-gray-400 mb-2">Message (Optional)</label>
            <textarea name="message" rows="3" placeholder="Preferred date/time, or any specific concern..." class="w-full bg-[#f6f8fb] border border-gray-200 rounded-xl px-4 py-3.5 text-sm text-gray-800 focus:border-[#004591] focus:ring-2 focus:ring-[#004591]/10 outline-none transition-all placeholder:text-gray-400 resize-none"></textarea>
          </div>
          <button type="submit" class="w-full bg-gradient-to-r from-[#ea741b] to-[#cf5e0e] hover:from-[#cf5e0e] hover:to-[#ea741b] text-white font-bold uppercase tracking-widest text-[12px] py-4 rounded-xl shadow-[0_4px_20px_rgba(234,116,27,.25)] transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
            <i class="fas fa-paper-plane text-[10px]"></i> Send Request
          </button>
        </form>
      </div>

    </div>
  </div>
</section>

<!-- About Doctor -->
<section class="py-16 bg-white">
  <div class="max-w-6xl mx-auto px-5 lg:px-8">
    <div class="bg-[#f6f8fb] border border-gray-100 rounded-2xl p-8 lg:p-10">
      <div class="grid lg:grid-cols-12 gap-8 items-center">
        <div class="lg:col-span-4 flex justify-center">
          <div class="w-36 h-36 rounded-full bg-gradient-to-br from-[#004591] to-[#002d5c] flex items-center justify-center shadow-xl shadow-[#004591]/10">
            <span class="font-serif text-5xl font-bold text-white">SM</span>
          </div>
        </div>
        <div class="lg:col-span-8">
          <p class="text-[#ea741b] text-[10px] font-bold uppercase tracking-[.3em] mb-2">Lead Consultant</p>
          <h2 class="font-serif text-2xl lg:text-3xl font-bold text-[#001630] mb-2">Dr. Mohammad Shamim Al Mamun</h2>
          <p class="text-gray-500 text-sm mb-4">FCPS (Orthodontics) &middot; BDS (Dhaka Dental College) &middot; Associate Professor, Bangladesh Dental College</p>
          <p class="text-gray-500 text-sm leading-relaxed mb-5">With over 20 years of clinical experience and 600+ completed orthodontic cases, Dr. Mamun leads our team in delivering specialist-level dental care — from complex braces treatments to modern smile makeovers.</p>
          <div class="flex flex-wrap gap-3">
            <a href="dr-shamim-al-mamun.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#004591] hover:bg-[#003070] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">
              <i class="fas fa-user-doctor text-[10px]"></i> View Full Profile
            </a>
            <a href="tel:+8801712718527" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 hover:border-[#ea741b] hover:text-[#ea741b] text-gray-600 text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">
              <i class="fas fa-phone text-[10px]"></i> Call Directly
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Services -->
<section class="py-16 bg-[#f6f8fb]">
  <div class="max-w-6xl mx-auto px-5 lg:px-8">
    <div class="text-center mb-10">
      <p class="text-[#ea741b] text-[10px] font-bold uppercase tracking-[.35em] mb-2">What We Offer</p>
      <h2 class="font-serif text-3xl font-bold text-[#001630]">Our Services</h2>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php foreach([
        ['fa-teeth', 'Braces & Orthodontics', 'Metal, ceramic, and invisible braces for all ages.'],
        ['fa-tooth', 'Root Canal', 'Painless root canal treatment with modern techniques.'],
        ['fa-wand-magic-sparkles', 'Teeth Whitening', 'Professional whitening for a brighter, confident smile.'],
        ['fa-screwdriver-wrench', 'Dental Implants', 'Permanent tooth replacement with natural-looking implants.'],
        ['fa-stethoscope', 'General Dentistry', 'Check-ups, fillings, scaling, and preventive care.'],
        ['fa-teeth-open', 'Tooth Extraction', 'Safe and comfortable removal of damaged teeth.'],
        ['fa-smile-beam', 'Smile Makeover', 'Complete smile design with veneers and cosmetic care.'],
        ['fa-baby', 'Pediatric Dentistry', 'Gentle dental care for children in a friendly environment.'],
      ] as [$icon, $title, $desc]): ?>
      <a href="index.php#services" class="group bg-white border border-gray-100 hover:border-[#004591]/20 rounded-2xl p-5 transition-all duration-300 hover:shadow-[0_8px_30px_rgba(0,69,145,.06)]">
        <div class="w-10 h-10 rounded-xl bg-[#004591]/8 group-hover:bg-[#004591] flex items-center justify-center mb-3 transition-colors">
          <i class="fas <?=$icon?> text-[#004591] group-hover:text-white text-sm transition-colors"></i>
        </div>
        <h3 class="text-sm font-bold text-[#001630] mb-1"><?=$title?></h3>
        <p class="text-gray-400 text-xs leading-relaxed"><?=$desc?></p>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="py-16 bg-white">
  <div class="max-w-3xl mx-auto px-5 lg:px-8">
    <div class="text-center mb-10">
      <p class="text-[#ea741b] text-[10px] font-bold uppercase tracking-[.35em] mb-2">Have Questions?</p>
      <h2 class="font-serif text-3xl font-bold text-[#001630]">Frequently Asked Questions</h2>
    </div>
    <div class="space-y-3" id="faqList">
      <?php foreach([
        ['How do I book an appointment?', 'You can book an appointment by calling us at +880 1712-718527, messaging us on WhatsApp, or filling out the appointment form on this page. We typically confirm appointments within 30 minutes during working hours.'],
        ['What insurance do you accept?', 'We accept most major health insurance providers in Bangladesh. Please call us with your insurance details and we will verify your coverage before your visit. Cash, bKash, Nagad, and card payments are also accepted.'],
        ['Do you offer emergency dental services?', 'Yes. If you have a dental emergency such as severe tooth pain, swelling, or a knocked-out tooth, please call us immediately at +880 1712-718527. We do our best to accommodate emergency patients on the same day.'],
        ['How much do braces cost in Dhaka?', 'Braces cost varies depending on the type (metal, ceramic, or invisible) and the complexity of your case. We offer flexible payment plans. Please book a consultation for a personalized quote.'],
        ['What are your clinic timings?', 'We are open Saturday through Thursday from 9:00 AM to 9:00 PM. We are closed on Fridays. Public holiday hours may vary — please call ahead to confirm.'],
        ['Where is the clinic located?', 'We are located at 5/4 (2nd Floor), Block A, Road 5, Lalmatia, Mohammadpur, Dhaka-1207. You can find us easily on Google Maps — just search for "Mamun\'s Ortho Dental."'],
      ] as [$q, $a]): ?>
      <div class="faq-item border border-gray-100 rounded-xl overflow-hidden transition-all">
        <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between gap-4 p-5 text-left hover:bg-[#f6f8fb] transition-colors">
          <span class="text-sm font-bold text-[#001630]"><?=$q?></span>
          <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform flex-shrink-0"></i>
        </button>
        <div class="faq-answer hidden px-5 pb-5">
          <p class="text-gray-500 text-sm leading-relaxed"><?=$a?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="relative bg-[#020811] overflow-hidden">
  <div class="h-px bg-gradient-to-r from-transparent via-[#ea741b]/40 to-transparent"></div>
  <div class="max-w-7xl mx-auto px-5 lg:px-8 pt-16 pb-8 relative z-10">
    <div class="grid grid-cols-2 lg:grid-cols-12 gap-y-10 gap-x-8 mb-12">
      <div class="col-span-2 lg:col-span-4">
        <div class="flex items-center gap-3 mb-5">
          <img src="Logo.png" alt="Logo" class="w-9 h-9 object-contain">
          <div>
            <p class="text-white font-serif text-lg font-bold">Mamun's <span class="text-[#ea741b]">Ortho</span></p>
            <p class="text-white/30 text-[8px] uppercase tracking-[.2em] font-bold">Premier Dental Care</p>
          </div>
        </div>
        <p class="text-white/30 text-[13px] leading-[1.8] mb-5">Patients across Dhaka choose us for specialist expertise, modern equipment, and WHO-standard sterilisation. Your smile is our priority.</p>
        <div class="flex items-center gap-2">
          <a href="https://www.facebook.com/mamundental" target="_blank" class="w-8 h-8 rounded-lg bg-white/[0.04] hover:bg-[#1877F2]/20 border border-white/[0.06] hover:border-[#1877F2]/40 flex items-center justify-center transition-all"><i class="fab fa-facebook-f text-white/40 hover:text-[#1877F2] text-[11px]"></i></a>
          <a href="https://wa.me/8801712718527" target="_blank" class="w-8 h-8 rounded-lg bg-white/[0.04] hover:bg-[#25d366]/20 border border-white/[0.06] hover:border-[#25d366]/40 flex items-center justify-center transition-all"><i class="fab fa-whatsapp text-white/40 hover:text-[#25d366] text-[11px]"></i></a>
          <a href="mailto:mamunddcbdc@gmail.com" class="w-8 h-8 rounded-lg bg-white/[0.04] hover:bg-[#ea741b]/20 border border-white/[0.06] hover:border-[#ea741b]/40 flex items-center justify-center transition-all"><i class="fas fa-envelope text-white/40 hover:text-[#ea741b] text-[11px]"></i></a>
          <a href="https://maps.app.goo.gl/MJg2zb1qWj8xq2Uq6" target="_blank" class="w-8 h-8 rounded-lg bg-white/[0.04] hover:bg-[#4285F4]/20 border border-white/[0.06] hover:border-[#4285F4]/40 flex items-center justify-center transition-all"><i class="fas fa-map-location-dot text-white/40 hover:text-[#4285F4] text-[11px]"></i></a>
        </div>
      </div>
      <div class="col-span-1 lg:col-span-2">
        <p class="text-white/50 text-[10px] font-bold uppercase tracking-[.2em] mb-4">Quick Links</p>
        <ul class="space-y-2.5">
          <?php foreach([['index.php','Home'],['index.php#about','About Us'],['index.php#services','Services'],['index.php#gallery','Gallery'],['index.php#testimonials','Reviews'],['privacy-policy.php','Privacy Policy'],['terms.php','Terms of Service']] as [$href,$lbl]): ?>
          <li><a href="<?=$href?>" class="text-white/30 hover:text-[#ea741b] text-[13px] transition-colors"><?=$lbl?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-span-1 lg:col-span-2">
        <p class="text-white/50 text-[10px] font-bold uppercase tracking-[.2em] mb-4">Resources</p>
        <ul class="space-y-2.5">
          <?php foreach([['dr-shamim-al-mamun.php','Dr. Shamim Al Mamun'],['blog/braces-cost-in-dhaka.php','Braces Cost Guide'],['blog/best-age-for-braces.php','Best Age for Braces'],['blog/dental-care-tips-bangladesh.php','Dental Care Tips'],['patient_record.php#track','Patient Portal']] as [$href,$lbl]): ?>
          <li><a href="<?=$href?>" class="text-white/30 hover:text-[#ea741b] text-[13px] transition-colors"><?=$lbl?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-span-2 lg:col-span-4">
        <p class="text-white/50 text-[10px] font-bold uppercase tracking-[.2em] mb-4">Contact Info</p>
        <div class="space-y-3">
          <div class="flex items-start gap-3">
            <i class="fas fa-location-dot text-[#ea741b] text-xs mt-1"></i>
            <span class="text-white/40 text-[13px]">5/4 (2nd Floor), Block A, Road 5, Lalmatia, Dhaka-1207</span>
          </div>
          <div class="flex items-start gap-3">
            <i class="fas fa-phone text-[#ea741b] text-xs mt-1"></i>
            <div>
              <a href="tel:+8801712718527" class="block text-white/40 hover:text-[#ea741b] text-[13px] transition-colors">+880 1712-718527</a>
              <a href="https://wa.me/8801712718527" target="_blank" class="block text-[#25d366]/50 hover:text-[#25d366] text-[13px] transition-colors"><i class="fab fa-whatsapp mr-1"></i>WhatsApp</a>
            </div>
          </div>
          <div class="flex items-start gap-3">
            <i class="fas fa-envelope text-[#ea741b] text-xs mt-1"></i>
            <a href="mailto:mamunddcbdc@gmail.com" class="text-white/40 hover:text-[#ea741b] text-[13px] transition-colors">mamunddcbdc@gmail.com</a>
          </div>
          <div class="flex items-start gap-3">
            <i class="fas fa-clock text-[#ea741b] text-xs mt-1"></i>
            <span class="text-white/40 text-[13px]">Sat–Thu: 9 AM – 9 PM</span>
          </div>
        </div>
      </div>
    </div>
    <div class="border-t border-white/[0.06] pt-6 text-center">
      <p class="text-white/20 text-[11px]">&copy; <?=date('Y')?> Mamun's Ortho Dental. All rights reserved. &middot; Lalmatia, Dhaka-1207</p>
    </div>
  </div>
</footer>

<!-- FAQ Toggle Script -->
<script>
function toggleFaq(btn) {
  const item = btn.closest('.faq-item');
  const answer = item.querySelector('.faq-answer');
  const icon = btn.querySelector('i');
  const isOpen = !answer.classList.contains('hidden');

  // Close all
  document.querySelectorAll('.faq-answer').forEach(a => a.classList.add('hidden'));
  document.querySelectorAll('.faq-item i').forEach(i => i.style.transform = 'rotate(0deg)');

  if (!isOpen) {
    answer.classList.remove('hidden');
    icon.style.transform = 'rotate(180deg)';
  }
}
</script>

<script src="<?= asset('assets/js/main.js') ?>" defer></script>
<script src="<?= asset('assets/js/loader.js') ?>" defer></script>
</body>
</html>
