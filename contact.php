<?php
session_start();
$page_title = "Best Dental Clinic in Dhaka | Contact Mamun's Ortho Dental";
$page_desc = "Contact Mamun's Ortho Dental — best dental clinic in Dhaka. Book an appointment with Dr. Shamim Al Mamun for expert orthodontic and dental care. Open Saturday to Thursday.";
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
        // In a real scenario, this would insert into database or send an email
        // require_once 'database/connection.php';
        // $stmt = $pdo->prepare("INSERT INTO messages (sender_name, sender_phone, type, message) VALUES (?, ?, ?, ?)");
        // $stmt->execute([$name, $phone, $service, $message]);
        $contact_success = true;
    }
}
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/landing.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#004591','navy-dark':'#003070',gold:'#ea741b'},fontFamily:{serif:['"Playfair Display"','serif'],sans:['"Outfit"','sans-serif']}}}}</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ContactPage",
  "name": "Contact Mamun's Ortho Dental",
  "description": "<?=$page_desc?>",
  "url": "<?=$page_canonical?>",
  "mainEntity": {
    "@type": "Dentist",
    "name": "Mamun's Ortho Dental",
    "telephone": "+8801712718527",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "5/2 (2nd Floor), Block A, Road 5, Lalmatia",
      "addressLocality": "Mohammadpur",
      "addressRegion": "Dhaka",
      "postalCode": "1207",
      "addressCountry": "BD"
    }
  }
}
</script>
</head>
<body class="font-sans bg-white text-gray-800">
<!-- Developed by Umaer Islam (https://umaerislam.com) -->
<nav class="fixed top-5 left-0 right-0 z-[100] px-4"><div class="max-w-6xl mx-auto"><div class="nav-pill flex items-center justify-between h-16 px-5 lg:px-8 bg-[#001230]/50 backdrop-blur-xl border border-white/10 rounded-3xl shadow-[0_8px_32px_rgba(0,0,0,.2)]"><a href="index.php" class="flex items-center gap-3"><img src="Logo.png" alt="Mamun's Ortho Dental Logo" class="w-10 h-10 object-contain"><div class="flex flex-col leading-none"><span class="text-white font-serif text-[17px] font-bold">Mamun's <span class="text-[#ea741b]">Ortho</span></span><span class="text-white/50 text-[8px] tracking-[.25em] uppercase font-bold mt-0.5">Dental Center</span></div></a><div class="flex items-center gap-3"><a href="index.php" class="hidden md:flex px-4 py-2 text-white/75 hover:text-white text-[13px] font-semibold rounded-full hover:bg-white/10 transition-all">Home</a><a href="index.php#services" class="hidden md:flex px-4 py-2 text-white/75 hover:text-white text-[13px] font-semibold rounded-full hover:bg-white/10 transition-all">Services</a></div></div></div></nav>

<section class="bg-[#001630] pt-36 pb-28 relative overflow-hidden min-h-screen flex items-center">
  <div class="absolute inset-0 opacity-[.04]"><svg width="100%" height="100%"><defs><pattern id="topo" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M0 100 Q 25 50 50 100 T 100 100" fill="none" stroke="white" stroke-width="0.5"/><path d="M0 80 Q 25 30 50 80 T 100 80" fill="none" stroke="white" stroke-width="0.5"/></pattern></defs><rect width="100%" height="100%" fill="url(#topo)"/></svg></div>
  <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-[#001630] via-transparent to-[#000e22]"></div>

  <div class="max-w-7xl mx-auto px-5 lg:px-8 relative z-10 w-full">
    <div class="grid lg:grid-cols-12 gap-12 lg:gap-16 items-center">
      
      <!-- Left Column: Info -->
      <div class="lg:col-span-6 reveal-left">
        <div class="gold-bar mb-5"></div>
        <p class="text-[#ea741b] text-[11px] font-bold uppercase tracking-[.3em] mb-3">Contact Us</p>
        <h1 class="font-serif text-4xl lg:text-6xl font-bold text-white leading-tight mb-8">Best Dental Clinic<br><span class="text-[#ea741b] italic font-light">in Dhaka</span></h1>
        
        <div class="space-y-4 mb-8 text-white">
          <div class="bg-white/5 border border-white/10 rounded-2xl p-6 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-[#ea741b]/20 flex items-center justify-center shrink-0"><i class="fas fa-map-marker-alt text-[#ea741b]"></i></div>
            <div><p class="font-bold text-lg mb-1">Clinic Address</p><p class="text-white/60 text-sm leading-relaxed">5/2 (2nd Floor), Block A, Road 5, Lalmatia<br>Mohammadpur, Dhaka-1207</p></div>
          </div>
          <div class="bg-white/5 border border-white/10 rounded-2xl p-6 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-[#ea741b]/20 flex items-center justify-center shrink-0"><i class="fas fa-clock text-[#ea741b]"></i></div>
            <div><p class="font-bold text-lg mb-1">Opening Hours</p><p class="text-white/60 text-sm leading-relaxed">Saturday – Thursday: 9:00 AM – 9:00 PM<br>Friday: Closed</p></div>
          </div>
          <div class="bg-white/5 border border-white/10 rounded-2xl p-6 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-[#ea741b]/20 flex items-center justify-center shrink-0"><i class="fas fa-phone text-[#ea741b]"></i></div>
            <div><p class="font-bold text-lg mb-1">Contact Info</p><p class="text-white/60 text-sm leading-relaxed">Phone: +880 1712-718527<br><a href="https://wa.me/8801712718527" target="_blank" class="text-[#25d366] hover:underline"><i class="fab fa-whatsapp mr-1"></i>WhatsApp Us</a><br>Email: mamunddcbdc@gmail.com</p></div>
          </div>
        </div>
      </div>

      <!-- Right Column: Form -->
      <div class="lg:col-span-6 relative reveal-right">
        <div class="absolute -inset-4 bg-gradient-to-br from-[#ea741b]/20 to-[#004591]/20 rounded-[40px] blur-2xl -z-10"></div>
        <div class="bg-[#002147]/60 backdrop-blur-2xl border border-white/10 rounded-[32px] p-8 sm:p-10 shadow-2xl">
          <h3 class="font-serif text-2xl font-bold text-white mb-2 text-center">Book Appointment</h3>
          <p class="text-white/50 text-sm text-center mb-8">We usually confirm appointments within 30 minutes.</p>

          <?php if($contact_success): ?>
          <div class="flex items-center gap-4 bg-green-500/20 border border-green-400/30 rounded-2xl p-5 mb-6">
            <div class="w-10 h-10 rounded-xl bg-green-500/30 flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-green-300"></i></div>
            <div><p class="text-white font-bold text-sm">Request Received!</p><p class="text-white/65 text-xs">Our team will contact you shortly.</p></div>
          </div>
          <?php endif; ?>
          <?php if($contact_error): ?>
          <div class="flex items-center gap-3 bg-red-500/20 border border-red-400/30 rounded-2xl p-4 mb-6"><i class="fas fa-exclamation-circle text-red-300"></i><p class="text-white/85 text-sm"><?=htmlspecialchars($contact_error)?></p></div>
          <?php endif; ?>

          <form method="POST" class="space-y-4">
            <input type="hidden" name="contact_submit" value="1">
            <div class="grid sm:grid-cols-2 gap-4">
              <input type="text" name="name" placeholder="Your Name" required class="w-full bg-white/5 border border-white/20 rounded-xl px-5 py-4 text-white text-sm focus:border-[#ea741b] focus:outline-none transition-colors placeholder:text-white/30">
              <input type="tel" name="phone" placeholder="Phone Number" required class="w-full bg-white/5 border border-white/20 rounded-xl px-5 py-4 text-white text-sm focus:border-[#ea741b] focus:outline-none transition-colors placeholder:text-white/30">
            </div>
            <div class="mod-dropdown" data-name="service" data-placeholder="Select Service (Optional)">
              <input type="hidden" name="service" value="">
              <div class="mod-dropdown-trigger">
                <span class="mod-dropdown-selected">Select Service (Optional)</span>
                <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
              </div>
              <div class="mod-dropdown-panel">
                <div class="mod-dropdown-option" data-value=""><span class="opt-check"></span><span>Select Service (Optional)</span></div>
                <div class="mod-dropdown-option" data-value="Orthodontics"><span class="opt-check"></span><span>Braces / Orthodontics</span></div>
                <div class="mod-dropdown-option" data-value="Consultation"><span class="opt-check"></span><span>General Consultation</span></div>
                <div class="mod-dropdown-option" data-value="Root Canal"><span class="opt-check"></span><span>Root Canal</span></div>
                <div class="mod-dropdown-option" data-value="Whitening"><span class="opt-check"></span><span>Teeth Whitening</span></div>
                <div class="mod-dropdown-option" data-value="Other"><span class="opt-check"></span><span>Other</span></div>
              </div>
            </div>
            <textarea name="message" rows="3" placeholder="Message or preferred time (optional)" class="w-full bg-white/5 border border-white/20 rounded-xl px-5 py-4 text-white text-sm focus:border-[#ea741b] focus:outline-none transition-colors placeholder:text-white/30 resize-none"></textarea>
            <button type="submit" class="w-full bg-gradient-to-r from-[#ea741b] to-[#cf5e0e] hover:from-[#cf5e0e] hover:to-[#ea741b] text-white font-bold uppercase tracking-widest text-sm py-4 rounded-xl shadow-[0_4px_20px_rgba(234,116,27,.4)] transition-all hover:-translate-y-1">Confirm Request</button>
          </form>
        </div>
      </div>
    </div>
