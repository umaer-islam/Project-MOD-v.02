<?php
session_start();
require_once 'database/connection.php';
require_once 'components/activity_logger.php';
require_once 'components/assets.php';
require_once 'components/cache.php';
require_once 'components/rate_limiter.php';
require_once 'components/math_captcha.php';

//  Contact Form Handler 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name    = trim($_POST['contact_name']    ?? '');
    $country = trim($_POST['contact_country_code'] ?? '+880');
    $phone   = trim($_POST['contact_phone']   ?? '');
    $service = trim($_POST['contact_service'] ?? '');
    $message = trim($_POST['contact_message'] ?? '');
    $captcha_key    = $_POST['captcha_key'] ?? '';
    $captcha_answer = $_POST['captcha_answer'] ?? '';

    /* Combine country code + phone if phone doesn't already start with + */
    if ($phone && strpos($phone, '+') === false) {
        $phone = $country . $phone;
    }

    $redirectTo = '?#contact';
    if ($name && $phone) {
        // Verify CAPTCHA
        if (!MathCaptcha::verify($captcha_key, $captcha_answer)) {
            header('Location: ' . $redirectTo . '&error=' . urlencode('Incorrect CAPTCHA answer. Please try again.'));
            exit;
        } else {
            // Rate limit: 3 submissions per 10 minutes per IP
            $rateLimiter = new RateLimiter($pdo);
            $rateCheck = $rateLimiter->check('contact_form', 3, 600, 600);
            if (!$rateCheck['allowed']) {
                $minutes = ceil($rateCheck['retry_after'] / 60);
                header('Location: ' . $redirectTo . '&error=' . urlencode("Too many submissions. Please try again in {$minutes} minutes."));
                exit;
            } else {
                $rateLimiter->record('contact_form');
                if ($pdo !== null) {
                    try {
                        $pdo->prepare("INSERT INTO contact_inquiries (name,phone,service,message) VALUES (?,?,?,?)")
                            ->execute([$name,$phone,$service,$message]);
                        log_activity($pdo, 'VISITOR_CONTACT', "Contact form submitted by {$name} (Phone: {$phone}, Service: {$service})", null, 'Public Visitor');
                        header('Location: ' . $redirectTo . '&success=' . urlencode('Request sent successfully! We will contact you within 30 minutes.'));
                        exit;
                    } catch (Exception $e) {
                        header('Location: ' . $redirectTo . '&error=' . urlencode('Database error. Could not submit inquiry.'));
                        exit;
                    }
                }
            }
        }
    } else {
        header('Location: ' . $redirectTo . '&error=' . urlencode('Please fill in your name and phone number.'));
        exit;
    }
}

// Generate CAPTCHA for the form
$captcha = MathCaptcha::generate();

//  Runtime table creation (safe, idempotent) 
if ($pdo) {
    $tables = [
        "SELECT 1 FROM contact_inquiries LIMIT 1" =>
            "CREATE TABLE IF NOT EXISTS contact_inquiries (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, phone VARCHAR(30) NOT NULL, service VARCHAR(255), message TEXT, status ENUM('unread', 'read') DEFAULT 'unread', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "SELECT status FROM contact_inquiries LIMIT 1" =>
            "ALTER TABLE contact_inquiries ADD COLUMN status ENUM('unread', 'read') DEFAULT 'unread' AFTER message",
        "SELECT 1 FROM gallery LIMIT 1" =>
            "CREATE TABLE IF NOT EXISTS gallery (id INT AUTO_INCREMENT PRIMARY KEY, image_path VARCHAR(255) NOT NULL, caption VARCHAR(255), sort_order INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "SELECT 1 FROM before_after_cases LIMIT 1" =>
            "CREATE TABLE IF NOT EXISTS before_after_cases (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, description TEXT, before_image VARCHAR(255) NOT NULL, after_image VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        "SELECT icon FROM services LIMIT 1" =>
            "ALTER TABLE services ADD COLUMN icon VARCHAR(50) DEFAULT 'fa-tooth' AFTER name",
        "SELECT 1 FROM users WHERE role = 'admin' LIMIT 1" =>
            "ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'doctor', 'receptionist') DEFAULT 'receptionist'; UPDATE users SET role = 'admin' WHERE id = 1;",
        "SELECT 1 FROM testimonials WHERE status = 'Pending' LIMIT 1" =>
            "ALTER TABLE testimonials MODIFY COLUMN status ENUM('Published', 'Hidden', 'Pending') DEFAULT 'Pending';",
    ];
    foreach ($tables as $check => $create) {
        try { $pdo->query($check); } catch (PDOException $e) { try { $pdo->exec($create); } catch (Exception $ex) {} }
    }
}

// Data fetching — cached for 5 minutes (public data rarely changes)
$announcements = $services_list = $testimonials = $gallery_images = $cases = [];
if ($pdo) {
    try { $announcements  = cache_remember('pub:announcements', 300, fn() => $pdo->query("SELECT title,description FROM announcements WHERE visibility='Public' AND (expiry_date IS NULL OR expiry_date>=CURDATE()) ORDER BY date_posted DESC LIMIT 3")->fetchAll()); } catch(Exception $e){}
    try { $services_list  = cache_remember('pub:services', 300, fn() => $pdo->query("SELECT name,description,icon FROM services WHERE status='Active' ORDER BY created_at ASC LIMIT 10")->fetchAll()); } catch(Exception $e){}
    try { $testimonials   = cache_remember('pub:testimonials', 300, fn() => $pdo->query("SELECT patient_name as name,location as loc,stars,review as text FROM testimonials WHERE status='Published' ORDER BY created_at DESC LIMIT 6")->fetchAll()); } catch(Exception $e){}
    try { $gallery_images = cache_remember('pub:gallery', 300, fn() => $pdo->query("SELECT * FROM gallery ORDER BY sort_order ASC, created_at DESC LIMIT 12")->fetchAll()); } catch(Exception $e){}
    try { $cases          = cache_remember('pub:cases', 300, fn() => $pdo->query("SELECT * FROM before_after_cases ORDER BY created_at DESC LIMIT 6")->fetchAll()); } catch(Exception $e){}
}

// Fallback data
if (empty($services_list)) $services_list = [
    ['icon'=>'fa-teeth',           'name'=>'Orthodontic Treatment',   'description'=>'Braces system, Clear Aligner system, and Removable appliance system for all ages.'],
    ['icon'=>'fa-shield-halved',   'name'=>'Scaling & Polishing',     'description'=>'Professional cleaning to remove tartar and plaque for healthier gums.'],
    ['icon'=>'fa-star',            'name'=>'Tooth Whitening',         'description'=>'Advanced whitening treatments for a brighter, more confident smile.'],
    ['icon'=>'fa-teeth-open',      'name'=>'Root Canal Treatment',    'description'=>'Precision endodontics to save infected teeth and relieve pain.'],
    ['icon'=>'fa-tooth',           'name'=>'Tooth Extraction',        'description'=>'Safe, gentle extractions including wisdom teeth removal.'],
    ['icon'=>'fa-pen-nib',         'name'=>'Cosmetic Filling',        'description'=>'Tooth-colored composite fillings for a natural, seamless look.'],
    ['icon'=>'fa-crown',           'name'=>'Crown & Bridge',          'description'=>'Custom-crafted crowns and bridges to restore damaged or missing teeth.'],
    ['icon'=>'fa-grin-beam',       'name'=>'Denture',                 'description'=>'Removable tooth replacement solutions — partial or full dentures.'],
    ['icon'=>'fa-circle-half-stroke','name'=>'Occlusal Splint',       'description'=>'Custom bite guards to treat jaw disorders, bruxism, and TMJ issues.'],
];
if (empty($testimonials)) $testimonials = [
    ['name'=>'Tanvir Ahmed',   'loc'=>'Dhaka',       'stars'=>5, 'text'=>'Dr. Mamun fixed my crooked teeth with braces and the result is amazing. Highly recommended!'],
    ['name'=>'Nusrat Jahan',   'loc'=>'Lalmatia',    'stars'=>5, 'text'=>'Got my dental implants done here. Absolutely painless. The clinic is spotlessly clean.'],
    ['name'=>'Karim Hossain',  'loc'=>'Mohammadpur', 'stars'=>5, 'text'=>'Brought my 8-year-old — Dr. was so gentle, she was not scared at all! Excellent kids clinic.'],
    ['name'=>'Sabrina Islam',  'loc'=>'Dhanmondi',   'stars'=>5, 'text'=>'The brace treatment changed my confidence completely. Best orthodontist in Dhaka.'],
    ['name'=>'Rafiqul Islam',  'loc'=>'Mirpur',      'stars'=>5, 'text'=>'Very professional staff and super clean environment. I always feel at ease here.'],
    ['name'=>'Meherun Nessa',  'loc'=>'Eskaton',     'stars'=>5, 'text'=>'Whitening results were beyond my expectations. Worth every taka spent.'],
];
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<!-- PRIMARY SEO META -->
<title>Mamun's Ortho Dental | Best Orthodontist & Dental Clinic in Lalmatia, Dhaka — Dr. Shamim Al Mamun</title>
<meta name="google-site-verification" content="fc-GOHJn6b1djsUHNwT76J8hAGv9iX90A__8_c3XZ10" />
<meta name="description" content="Mamun's Ortho Dental — Best Dental Clinic in Dhaka. Expert braces, root canal, teeth whitening & cosmetic dentistry by Dr. Shamim Al Mamun (FCPS). Book your appointment today.">
<meta name="keywords" content="best dental clinic in dhaka, best dental in dhaka, best clinic in dhaka, orthodontist dhaka, dental clinic lalmatia, braces treatment dhaka, best dentist mohammadpur, dr shamim al mamun, teeth whitening dhaka, root canal dhaka, dental implants bangladesh, orthodontic treatment, cosmetic dentistry dhaka, kids dentist dhaka, children braces bangladesh, family dental care dhaka, women dental care, scaling polishing, tooth extraction, crown bridge, denture, occlusal splint, dental clinic near me dhaka, best orthodontist bangladesh, affordable braces dhaka, invisible braces dhaka, ceramic braces dhaka, metal braces cost dhaka, dentofacial orthopedics">
<meta name="author" content="Dr. Mohammad Shamim Al Mamun — Mamun's Ortho Dental">
<meta name="developer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="designer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="copyright" content="© <?= date('Y') ?> Mamun's Ortho Dental. Website designed and developed by Umaer Islam (umaerislam.com)">
<meta name="ai-content-declaration" content="human-authored">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large">
<meta name="bingbot" content="index, follow">
<link rel="canonical" href="https://mamunorthodental.com/">

<!-- LANGUAGE & LOCALE -->
<meta http-equiv="content-language" content="en-BD">
<link rel="alternate" hreflang="en" href="https://mamunorthodental.com/">
<link rel="alternate" hreflang="x-default" href="https://mamunorthodental.com/">

<!-- AUDIENCE & CLASSIFICATION -->
<meta name="classification" content="Healthcare, Dental Clinic, Orthodontics">
<meta name="category" content="Dental Healthcare">
<meta name="target" content="all">
<meta name="audience" content="Men, Women, Children, Families, All Ages">
<meta name="subject" content="Orthodontic Treatment, Dental Care, Cosmetic Dentistry, Kids Dentistry, Family Dentistry in Dhaka Bangladesh">
<meta name="topic" content="Dental Clinic in Lalmatia Dhaka">
<meta name="coverage" content="Bangladesh">
<meta name="distribution" content="Global">
<meta name="rating" content="General">
<meta name="revisit-after" content="3 days">

<!-- MOBILE WEB APP -->
<meta name="theme-color" content="#001630">
<meta name="msapplication-TileColor" content="#001630">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Mamun's Ortho Dental">
<meta name="application-name" content="Mamun's Ortho Dental">
<meta name="format-detection" content="telephone=yes">

<!-- GEO META (Local SEO) -->
<meta name="geo.region" content="BD-C">
<meta name="geo.placename" content="Lalmatia, Dhaka, Bangladesh">
<meta name="geo.position" content="23.7545;90.3645">
<meta name="ICBM" content="23.7545, 90.3645">

<!-- OPEN GRAPH (Facebook / LinkedIn / WhatsApp) -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="Mamun's Ortho Dental">
<meta property="og:title" content="Best Orthodontist & Dental Clinic in Lalmatia, Dhaka — Mamun's Ortho Dental">
<meta property="og:description" content="Expert orthodontics, braces for kids & adults, root canal, teeth whitening & cosmetic dentistry in Lalmatia, Dhaka. Family dental care for men, women & children by Dr. Shamim Al Mamun (FCPS). 20+ years experience, 15,000+ patients.">
<meta property="og:url" content="https://mamunorthodental.com/">
<meta property="og:image" content="https://mamunorthodental.com/Logo.png">
<meta property="og:image:width" content="512">
<meta property="og:image:height" content="512">
<meta property="og:image:alt" content="Mamun's Ortho Dental — Premier Dental Clinic in Lalmatia, Dhaka">
<meta property="og:locale" content="en_BD">
<meta property="og:see_also" content="https://www.facebook.com/mamundental">

<!-- TWITTER CARD -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Mamun's Ortho Dental — Best Orthodontist in Dhaka">
<meta name="twitter:description" content="Expert braces for kids, teens & adults. Root canal, whitening & cosmetic dentistry in Lalmatia, Dhaka. Family dental care. 20+ years experience. Book now.">
<meta name="twitter:image" content="https://mamunorthodental.com/Logo.png">
<meta name="twitter:image:alt" content="Mamun's Ortho Dental Clinic Logo">

<!-- FAVICONS -->
<link rel="icon" type="image/png" href="Logo.png">
<link rel="manifest" href="site.webmanifest">
<meta name="theme-color" content="#ea741b">
<meta name="msapplication-TileColor" content="#ea741b">
<meta name="msapplication-config" content="browserconfig.xml">
<link rel="author" href="humans.txt">
<link rel="apple-touch-icon" href="Logo.png">
<link rel="shortcut icon" href="Logo.png" type="image/png">

<!--  PRECONNECT & PRELOAD (Performance)  -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&display=swap" as="style">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= asset('assets/css/landing.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/loader.css') ?>">

<!--  TAILWIND CONFIG  -->
<script src="https://cdn.tailwindcss.com" defer></script>
<script>
tailwind.config = { theme: { extend: {
    colors: { navy:'#004591','navy-dark':'#003070',gold:'#ea741b' },
    fontFamily: { serif:['"Playfair Display"','serif'], sans:['"Outfit"','sans-serif'] }
}}}
</script>

<!--  JSON-LD STRUCTURED DATA (Schema.org)  -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": ["LocalBusiness", "Dentist", "MedicalBusiness"],
      "@id": "https://mamunorthodental.com/#clinic",
      "name": "Mamun's Ortho Dental",
      "alternateName": ["Mamun's Orthodental", "Mamuns Ortho Dental"],
      "description": "Best dental clinic in Dhaka. Mamun's Ortho Dental in Lalmatia offers expert orthodontic braces, root canal, teeth whitening, scaling & polishing, cosmetic dentistry for men, women & children.",
      "url": "https://mamunorthodental.com/",
      "telephone": "+8801712718527",
      "email": "mamunddcbdc@gmail.com",
      "image": "https://mamunorthodental.com/Logo.png",
      "logo": "https://mamunorthodental.com/Logo.png",
      "priceRange": "$$",
      "currenciesAccepted": "BDT",
      "paymentAccepted": "Cash, bKash, Nagad, Card",
      "hasMap": "https://maps.app.goo.gl/MJg2zb1qWj8xq2Uq6",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "5/4 (2nd Floor), Block A, Road 5, Lalmatia",
        "addressLocality": "Dhaka",
        "addressRegion": "Dhaka Division",
        "postalCode": "1207",
        "addressCountry": "BD"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": 23.7545,
        "longitude": 90.3645
      },
      "areaServed": [
        {"@type": "City", "name": "Dhaka"},
        {"@type": "Place", "name": "Lalmatia"},
        {"@type": "Place", "name": "Mohammadpur"},
        {"@type": "Place", "name": "Dhanmondi"},
        {"@type": "Place", "name": "Mirpur"}
      ],
      "audience": {
        "@type": "MedicalAudience",
        "audienceType": "Patients",
        "suggestedGender": "Unisex",
        "suggestedMinAge": 3,
        "suggestedMaxAge": 99
      },
      "openingHoursSpecification": [
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": ["Saturday","Sunday","Monday","Tuesday","Wednesday","Thursday"],
          "opens": "09:00",
          "closes": "21:00"
        }
      ],
      "medicalSpecialty": ["Orthodontics", "Dentistry", "DentofacialOrthopedics", "PediatricDentistry"],
      "availableService": [
        {"@type": "MedicalProcedure", "name": "Orthodontic Treatment (Metal, Ceramic & Invisible Braces)", "bodyLocation": "Mouth"},
        {"@type": "MedicalProcedure", "name": "Scaling & Polishing", "bodyLocation": "Teeth"},
        {"@type": "MedicalProcedure", "name": "Professional Tooth Whitening", "bodyLocation": "Teeth"},
        {"@type": "MedicalProcedure", "name": "Root Canal Treatment", "bodyLocation": "Tooth"},
        {"@type": "MedicalProcedure", "name": "Tooth Extraction", "bodyLocation": "Mouth"},
        {"@type": "MedicalProcedure", "name": "Cosmetic Filling", "bodyLocation": "Teeth"},
        {"@type": "MedicalProcedure", "name": "Crown & Bridge", "bodyLocation": "Teeth"},
        {"@type": "MedicalProcedure", "name": "Denture", "bodyLocation": "Mouth"},
        {"@type": "MedicalProcedure", "name": "Occlusal Splint (TMJ)", "bodyLocation": "Jaw"},
        {"@type": "MedicalProcedure", "name": "Dental Implants", "bodyLocation": "Jaw"},
        {"@type": "MedicalProcedure", "name": "Pediatric Dentistry", "bodyLocation": "Mouth"}
      ],
      "founder": {"@id": "https://mamunorthodental.com/#doctor"},
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.9",
        "bestRating": "5",
        "reviewCount": "800",
        "ratingCount": "800"
      },
      "sameAs": [
        "https://www.facebook.com/mamundental",
        "https://wa.me/8801712718527",
        "https://maps.app.goo.gl/MJg2zb1qWj8xq2Uq6"
      ],
      "contactPoint": [
        {"@type": "ContactPoint", "telephone": "+8801712718527", "contactType": "customer service", "areaServed": "BD", "availableLanguage": ["Bengali", "English"], "contactOption": ["TollFree", "HearingImpairedSupported"]},
        {"@type": "ContactPoint", "telephone": "+8801712718527", "contactType": "appointments", "areaServed": "BD", "availableLanguage": ["Bengali", "English"]}
      ]
    },
    {
      "@type": "Physician",
      "@id": "https://mamunorthodental.com/#doctor",
      "name": "Dr. Mohammad Shamim Al Mamun",
      "alternateName": ["Dr. Shamim Al Mamun", "Dr. Mamun"],
      "description": "Consultant Orthodontist & Dentofacial Orthopedics specialist. BDS (Dhaka Dental College), FCPS (Orthodontics). 20+ years treating men, women & children.",
      "image": "https://mamunorthodental.com/Logo.png",
      "url": "https://mamunorthodental.com/dr-shamim-al-mamun.php",
      "telephone": "+8801712718527",
      "medicalSpecialty": ["Orthodontics", "DentofacialOrthopedics", "Implantology"],
      "qualification": [
        {"@type": "EducationalOccupationalCredential", "credentialCategory": "degree", "name": "BDS", "educationalLevel": "Bachelor of Dental Surgery", "recognizedBy": {"@type": "Organization", "name": "Dhaka Dental College"}},
        {"@type": "EducationalOccupationalCredential", "credentialCategory": "degree", "name": "FCPS (Orthodontics)", "recognizedBy": {"@type": "Organization", "name": "Bangladesh College of Physicians and Surgeons"}}
      ],
      "memberOf": [
        {"@type": "Organization", "name": "Bangladesh Dental College", "description": "Associate Professor & Head, Dept. of Orthodontics"},
        {"@type": "Organization", "name": "Labaid Hospital, Dhanmondi", "description": "Consultant Orthodontist"},
        {"@type": "Organization", "name": "Apollo Clinic, Dhanmondi", "description": "Consultant"}
      ],
      "worksFor": {"@id": "https://mamunorthodental.com/#clinic"},
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "5/4 (2nd Floor), Block A, Road 5, Lalmatia",
        "addressLocality": "Dhaka",
        "postalCode": "1207",
        "addressCountry": "BD"
      }
    },
    {
      "@type": "FAQPage",
      "@id": "https://mamunorthodental.com/#faq",
      "mainEntity": [
        {"@type": "Question", "name": "What age groups does Mamun's Ortho Dental treat?", "acceptedAnswer": {"@type": "Answer", "text": "We welcome patients of all ages — children (from age 3), teenagers, adults, and seniors. Our clinic provides pediatric dentistry, orthodontic braces for teens and adults, and dental care for men, women, and families."}},
        {"@type": "Question", "name": "What are the clinic opening hours?", "acceptedAnswer": {"@type": "Answer", "text": "Saturday to Thursday, 9:00 AM to 9:00 PM. Closed on Fridays."}},
        {"@type": "Question", "name": "Where is the clinic located?", "acceptedAnswer": {"@type": "Answer", "text": "5/4 (2nd Floor), Block A, Road 5, Lalmatia, Dhaka-1207. Accessible from Mohammadpur, Dhanmondi, Mirpur and surrounding areas."}},
        {"@type": "Question", "name": "What types of braces are available?", "acceptedAnswer": {"@type": "Answer", "text": "Metal braces, ceramic braces, and invisible/clear aligners for children and adults."}},
        {"@type": "Question", "name": "How to book an appointment?", "acceptedAnswer": {"@type": "Answer", "text": "Call or WhatsApp +880 1712-718527, email mamunddcbdc@gmail.com, or use the online booking form. Walk-ins are also welcome during clinic hours."}},
        {"@type": "Question", "name": "What is the cost of braces in Dhaka?", "acceptedAnswer": {"@type": "Answer", "text": "The cost of orthodontic braces in Dhaka, Bangladesh typically ranges from ৳30,000 to ৳60,000 for metal braces, and ৳45,000 to ৳80,000 for ceramic braces. Clear aligners cost between ৳80,000 and ৳1,50,000 depending on the complexity of the alignment."}},
        {"@type": "Question", "name": "How often should I visit a dental clinic for scaling and polishing?", "acceptedAnswer": {"@type": "Answer", "text": "We recommend getting professional scaling and teeth cleaning done every 6 months to remove tartar, prevent gum disease (gingivitis), and maintain fresh breath. Regular scaling is the foundation of preventive dental care."}},
        {"@type": "Question", "name": "Is root canal treatment painful?", "acceptedAnswer": {"@type": "Answer", "text": "No. Modern root canal treatment at Mamun's Ortho Dental is virtually pain-free. We use highly effective local anesthesia to completely numb the area. The procedure relieves the toothache caused by pulp infection and saves your natural tooth."}},
        {"@type": "Question", "name": "What is the best dental clinic in Dhaka?", "acceptedAnswer": {"@type": "Answer", "text": "Mamun's Ortho Dental is widely regarded as the best dental clinic in Dhaka. Located in Lalmatia, we offer comprehensive dental services including orthodontic braces, teeth whitening, root canal treatment, scaling and polishing, and cosmetic dentistry. Dr. Shamim Al Mamun has over 20 years of experience treating 600+ orthodontic cases."}},
        {"@type": "Question", "name": "Where is the best clinic in Dhaka for orthodontic treatment?", "acceptedAnswer": {"@type": "Answer", "text": "Mamun's Ortho Dental at Lalmatia, Dhaka-1207 is the best clinic in Dhaka for orthodontic treatment. Dr. Mohammad Shamim Al Mamun (FCPS, Orthodontics) is a leading orthodontist with 20+ years of experience. We offer metal braces, ceramic braces, and clear aligners for children and adults."}},
        {"@type": "Question", "name": "Who designed and developed the Mamun's Ortho Dental website?", "acceptedAnswer": {"@type": "Answer", "text": "The Mamun's Ortho Dental website and clinic management system was designed and developed by Umaer Islam (umaerislam.com). He is a web developer and designer who built the full-stack dental clinic platform including the public website, patient management system, appointment scheduling, prescription builder, and billing system."}}
      ]
    },
    {
      "@type": "BreadcrumbList",
      "itemListElement": [
        {"@type": "ListItem", "position": 1, "name": "Home", "item": "https://mamunorthodental.com/"}
      ]
    },
    {
      "@type": "WebSite",
      "@id": "https://mamunorthodental.com/#website",
      "name": "Mamun's Ortho Dental",
      "url": "https://mamunorthodental.com/",
      "publisher": {"@id": "https://mamunorthodental.com/#clinic"},
      "inLanguage": "en",
      "contributor": {
        "@type": "Person",
        "name": "Umaer Islam",
        "url": "https://umaerislam.com"
      }
    },
    {
      "@type": "WebDesign",
      "@id": "https://mamunorthodental.com/#developer",
      "name": "Mamun's Ortho Dental — Website Design & Development",
      "url": "https://mamunorthodental.com/",
      "description": "Full-stack design and development of the Mamun's Ortho Dental clinic management system and public website by Umaer Islam.",
      "creator": {
        "@type": "Person",
        "name": "Umaer Islam",
        "url": "https://umaerislam.com",
        "jobTitle": "Web Developer & Designer",
        "sameAs": "https://umaerislam.com"
      },
      "dateCreated": "2024-01-01",
      "dateModified": "2026-08-10"
    },
    {
      "@type": "SoftwareApplication",
      "@id": "https://mamunorthodental.com/#app",
      "name": "Mamun's Ortho Dental Clinic Management System",
      "description": "A comprehensive dental clinic management system featuring patient records, appointment scheduling, prescription management, billing, and a public-facing website with SEO optimization.",
      "applicationCategory": "HealthApplication",
      "operatingSystem": "Web Browser",
      "url": "https://mamunorthodental.com/",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "BDT"
      },
      "author": {
        "@type": "Person",
        "name": "Umaer Islam",
        "url": "https://umaerislam.com"
      },
      "creator": {
        "@type": "Person",
        "name": "Umaer Islam",
        "url": "https://umaerislam.com"
      }
    }
  ]
}
</script>


<!--  GOOGLE ANALYTICS (Replace UA-XXXXX with real ID)  -->
<!-- <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','G-XXXXXXXXXX');</script> -->

<!--  GOOGLE SEARCH CONSOLE VERIFICATION (Replace with real token)  -->
<!-- <meta name="google-site-verification" content="YOUR_VERIFICATION_TOKEN"> -->

<style>
/* Legacy compat  main styles are in assets/css/landing.css */
.ba-slider { position:relative; overflow:hidden; }
.ba-divider { position:absolute; top:0; bottom:0; left:50%; width:3px; background:#ea741b; z-index:10; transform:translateX(-50%); }
.ba-handle { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:36px; height:36px; background:#ea741b; border-radius:50%; z-index:11; display:flex; align-items:center; justify-content:center; box-shadow:0 0 0 4px rgba(234,116,27,.3); }
.ba-before { position:absolute; inset:0; width:50%; overflow:hidden; }
.ba-before img { width:200%; height:100%; object-fit:cover; object-position:left; }
/* form-input alias for contact-input */
.form-input { width:100%; background:rgba(255,255,255,.08); border:1.5px solid rgba(255,255,255,.18); border-radius:14px; padding:14px 18px; font-size:14px; color:#fff; font-family:'Outfit',sans-serif; transition:border-color .3s,background .3s,box-shadow .3s; outline:none; }
.form-input::placeholder { color:rgba(255,255,255,.4); }
.form-input:focus { border-color:#ea741b; background:rgba(255,255,255,.12); box-shadow:0 0 0 3px rgba(234,116,27,.15); }
.form-select option { color:#004591; background:#fff; }

/* Lazy section loading */
.lazy-section { opacity: 0; transform: translateY(24px); transition: opacity 0.7s ease, transform 0.7s ease; }
.lazy-section.lazy-section--visible { opacity: 1; transform: translateY(0); }
</style>
</head>
<body>
<!-- ═══ CINEMATIC LOADER ═══ -->
<div class="mod-loader" aria-hidden="true">
    <div class="mod-loader__glow"></div>

    <div class="mod-loader__corner mod-loader__corner--tl"></div>
    <div class="mod-loader__corner mod-loader__corner--tr"></div>
    <div class="mod-loader__corner mod-loader__corner--bl"></div>
    <div class="mod-loader__corner mod-loader__corner--br"></div>

    <div class="mod-loader__logo">
        <div class="mod-loader__ring"></div>
        <div class="mod-loader__orbit">
            <div class="mod-loader__orbit-dot"></div>
        </div>
        <div class="mod-loader__shine"></div>

        <div class="mod-loader__svg-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="-80 -80 660 555">
                <path class="cls-1" fill="#e97524" d="M281.86,18.94S-27.78-56.75,163.91,392.37c0,0-295.74-485.71,117.96-373.43Z"/>
                <path class="cls-3" fill="#19488e" d="M182.62,45.86s57.19,37.01,135.41-10.93c0,0,104.29-53.83,118.59,36.17,0,0,21.52,149.57-82.77,321.29,0,0,88.31-257.22,39.53-324.51,0,0-14.3-30.28-96.72,11.77,0,0-67.43,31.68-114.03-33.78Z"/>
                <path class="cls-2" fill="#9e9e9e" d="M232.87,96.86s33.72,22.47,79.85-6.64c0,0,61.5-32.68,69.93,21.96,0,0,19.18,143.03-42.31,247.29,0,0,45.58-208.4,16.82-249.25,0,0-8.43-18.38-57.03,7.15,0,0-39.76,19.24-67.24-20.51Z"/>
                <path class="cls-2" fill="#9e9e9e" d="M12.25,139.28s-77.38,75.13,205.78,94.2v20.75h23.55v-53.27h-23.27v19.55s-228.77-1.61-206.06-81.23Z"/>
                <path class="cls-2" fill="#9e9e9e" d="M487.78,139.28s77.13,75.13-205.11,94.2v20.75h-23.47v-53.27h23.19v19.55s228.03-1.61,205.39-81.23Z"/>
            </svg>
        </div>
    </div>

    <div class="mod-loader__text">
        <div class="mod-loader__brand-line">
            <div class="mod-loader__brand">Mamun's Ortho Dental</div>
        </div>
        <div class="mod-loader__sub">Premier Dental Care</div>
    </div>

    <div class="mod-loader__progress">
        <div class="mod-loader__fill"></div>
    </div>
</div>
<!-- Developed by Umaer Islam (https://umaerislam.com) -->
<div id="scrollProgress"></div>

<!--  NAVBAR  -->
<nav id="navbar" class="fixed top-5 left-0 right-0 z-[100] px-4">
  <div class="max-w-6xl mx-auto relative">
    <div class="nav-pill flex items-center justify-between h-16 md:h-[72px] px-5 lg:px-8 bg-[#001230]/50 backdrop-blur-xl border border-white/10 rounded-3xl shadow-[0_8px_32px_rgba(0,0,0,.2)] relative z-20">
      <a href="#" class="flex items-center gap-3 group">
        <img src="Logo.png" alt="Mamun's Ortho Dental Logo - Best Orthodontist & Dental Clinic in Lalmatia Dhaka" class="w-10 h-10 object-contain shadow-lg group-hover:scale-105 transition-transform drop-shadow-md">
        <div class="flex flex-col leading-none">
          <span class="text-white font-serif text-[17px] md:text-xl font-bold">Mamun's <span class="text-[#ea741b]">Ortho</span></span>
          <span class="text-white/50 text-[8px] tracking-[.25em] uppercase font-bold mt-0.5">Dental Center</span>
        </div>
      </a>
      <div class="hidden lg:flex items-center gap-1 absolute left-1/2 -translate-x-1/2">
        <?php foreach([['#about','About'],['#services','Services'],['#gallery','Gallery'],['#doctors','Doctors'],['#testimonials','Reviews'],['#beforeafter','Before & After'],['#track','Track'],['#contact','Contact']] as [$h,$l]): ?>
        <a href="<?=$h?>" class="px-4 py-2 text-white/75 hover:text-white text-[13px] font-semibold rounded-full hover:bg-white/10 transition-all"><?=$l?></a>
        <?php endforeach; ?>
      </div>
      <div class="flex items-center gap-3">
        <a href="login_page.php" target="_blank" class="hidden md:flex w-10 h-10 rounded-full bg-white/5 border border-white/10 hover:bg-white/10 items-center justify-center text-white/60 hover:text-white transition-all" title="Portal"><i class="fas fa-user-md text-sm"></i></a>
        <a href="#contact" class="hidden sm:inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-[#ea741b] to-[#cf5e0e] text-white text-[11px] font-bold uppercase tracking-widest rounded-full shadow-[0_4px_20px_rgba(234,116,27,.4)] transition-all hover:scale-105"><i class="fas fa-calendar-check text-xs"></i> Book Now</a>
        <button id="menuToggle" class="lg:hidden w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-white hover:bg-white/10 transition-all"><i class="fas fa-bars text-sm"></i></button>
      </div>
    </div>
    <div id="mobileMenu" class="lg:hidden bg-[#001d40]/95 backdrop-blur-2xl rounded-3xl border border-white/10 shadow-2xl z-10 p-2">
      <div class="bg-white/5 rounded-2xl p-4 space-y-1">
        <?php foreach([['#about','About'],['#services','Services'],['#gallery','Gallery'],['#doctors','Doctors'],['#testimonials','Reviews'],['#beforeafter','Before & After'],['#track','Track'],['#contact','Contact']] as [$h,$l]): ?>
        <a href="<?=$h?>" class="mobile-nav-link block px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-xl text-sm font-semibold transition-all"><?=$l?></a>
        <?php endforeach; ?>
        <div class="pt-4 mt-2 border-t border-white/10 flex flex-col gap-2">
          <a href="#contact" class="mobile-nav-link flex items-center justify-center gap-2 px-5 py-3.5 bg-gradient-to-r from-[#ea741b] to-[#cf5e0e] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl"><i class="fas fa-calendar-check text-xs"></i> Book Appointment</a>
          <a href="login_page.php" class="flex items-center justify-center gap-2 px-5 py-3.5 bg-white/5 border border-white/10 text-white text-[11px] font-bold uppercase tracking-widest rounded-xl"><i class="fas fa-user-md text-xs"></i> Doctor Portal</a>
        </div>
      </div>
    </div>
  </div>
</nav>

<!--  HERO  -->
<div class="cursor-dot" id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>
<div class="cursor-glow" id="cursorGlow"></div>
<div class="hero__light" id="heroLight"></div>

<section class="hero" id="heroSection">
  <canvas class="hero__grid" id="heroGrid"></canvas>
  <div class="hero__aurora hero__aurora--1"></div>
  <div class="hero__aurora hero__aurora--2"></div>
  <div class="hero__aurora hero__aurora--3"></div>
  <div id="heroOrbs"></div>

  <div class="hero__inner">
    <div>
      <div class="hero__eyebrow anim-fade-up d1">
        <div class="hero__eyebrow-dash"></div>
        <span class="hero__eyebrow-text">Lalmatia, Dhaka</span>
      </div>

      <h1 class="hero__title">
        <span class="hero__title-row"><span class="hero__title-inner anim-title-reveal d2">Best Dental Clinic</span></span>
        <span class="hero__title-row"><span class="hero__title-inner hero__title-accent anim-title-reveal d3">in Dhaka</span></span>
      </h1>

      <p class="hero__sub anim-fade-up d4">Best dental clinic in Dhaka — expert orthodontics, dental implants, and cosmetic dentistry in Lalmatia, Dhaka.</p>

      <div class="hero__specialize anim-fade-up d5">
        <span class="hero__specialize-label">We specialize in</span>
        <div class="hero__specialize-words" id="heroRotatingText">
          <span class="hero__specialize-word active">Orthodontic Braces</span>
          <span class="hero__specialize-word">Teeth Whitening</span>
          <span class="hero__specialize-word">Dental Implants</span>
          <span class="hero__specialize-word">Root Canal Treatment</span>
          <span class="hero__specialize-word">Cosmetic Dentistry</span>
        </div>
      </div>

      <div class="hero__btns anim-fade-up d6">
        <a href="#services" class="btn btn--gold"><i class="fas fa-arrow-right"></i> View Services</a>
        <a href="https://wa.me/8801712718527?text=Hello!%20I%27d%20like%20to%20book%20an%20appointment%20at%20Mamun%27s%20Ortho%20Dental." target="_blank" class="btn btn--ghost"><i class="fab fa-whatsapp"></i> WhatsApp Us</a>
      </div>

      <div class="hero__stats anim-fade-up d7">
        <div class="hero__stat">
          <p class="hero__stat-num"><span class="counter" data-target="15000">0</span><span>+</span></p>
          <p class="hero__stat-label">Happy Patients</p>
        </div>
        <div class="hero__stat">
          <p class="hero__stat-num"><span class="counter" data-target="20">0</span><span>+</span></p>
          <p class="hero__stat-label">Years Experience</p>
        </div>
        <div class="hero__stat">
          <p class="hero__stat-num"><span class="counter" data-target="600">0</span><span>+</span></p>
          <p class="hero__stat-label">Ortho Cases</p>
        </div>
      </div>
    </div>

    <div>
      <div class="hero__card anim-fade-right d8">
        <div class="hero__card-head">
          <div class="hero__card-icon"><i class="fas fa-tooth"></i></div>
          <div>
            <p class="hero__card-heading">Book Today</p>
            <p class="hero__card-subtext">No waiting — instant confirmation</p>
          </div>
        </div>
        <ul class="hero__services">
          <?php foreach(array_slice($services_list,0,4) as $s): ?>
          <li class="hero__service"><div class="hero__service-left"><div class="hero__service-dot"></div><span class="hero__service-name"><?=htmlspecialchars($s['name'])?></span></div><span class="hero__service-status">Available</span></li>
          <?php endforeach; ?>
        </ul>
        <a href="#contact" class="hero__card-btn">Schedule Appointment</a>
      </div>
      <div class="hero__trust anim-fade-up d9">
        <div class="hero__trust-item">
          <div class="hero__trust-icon hero__trust-icon--green"><i class="fas fa-shield-halved"></i></div>
          <div class="hero__trust-text"><strong>Safe &amp; Sterile</strong>WHO Standards</div>
        </div>
        <div class="hero__trust-item">
          <div class="hero__trust-icon hero__trust-icon--gold"><i class="fas fa-star"></i></div>
          <div class="hero__trust-text">
            <div class="hero__trust-stars"><?php for($i=0;$i<5;$i++) echo '<i class="fas fa-star"></i>'; ?></div>
            <strong>4.9 / 5.0</strong>800+ Reviews
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Animated Elements -->
  <div class="hero__icons">
    <div class="hero__icon hero__icon--tooth"><i class="fas fa-tooth"></i></div>
    <div class="hero__icon hero__icon--smile"><i class="fas fa-face-smile"></i></div>
    <div class="hero__icon hero__icon--brace"><i class="fas fa-teeth"></i></div>
    <div class="hero__icon hero__icon--implant"><i class="fas fa-tooth"></i></div>
    <div class="hero__icon hero__icon--crown"><i class="fas fa-crown"></i></div>
  </div>
  <div class="hero__pulse hero__pulse--1"></div>
  <div class="hero__pulse hero__pulse--2"></div>
  <div class="hero__pulse hero__pulse--3"></div>
  <div class="hero__lines">
    <div class="hero__line hero__line--1"></div>
    <div class="hero__line hero__line--2"></div>
    <div class="hero__line hero__line--3"></div>
  </div>
  <div class="hero__glow-dots">
    <div class="hero__glow-dot hero__glow-dot--1"></div>
    <div class="hero__glow-dot hero__glow-dot--2"></div>
    <div class="hero__glow-dot hero__glow-dot--3"></div>
    <div class="hero__glow-dot hero__glow-dot--4"></div>
  </div>
  <div class="hero__particles" id="heroParticlesWrap"></div>

  <div class="hero__scroll anim-fade d9" id="heroScrollHint">
    <div class="hero__scroll-bar"></div>
    <span class="hero__scroll-label">Scroll</span>
  </div>

  <div class="hero__wave"><svg viewBox="0 0 1440 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" style="width:100%;display:block"><path d="M0,40 C360,80 1080,0 1440,40 L1440,80 L0,80 Z" fill="#ffffff"/></svg></div>
</section>


<!--  ANNOUNCEMENTS  -->
<?php if(!empty($announcements)): ?>
<section class="bg-white pt-8 pb-2 z-30 relative"><div class="max-w-5xl mx-auto px-5"><div class="bg-amber-50 border border-amber-100 rounded-2xl px-6 py-4 flex items-center gap-4 relative overflow-hidden"><div class="absolute left-0 top-0 bottom-0 w-1.5 bg-amber-400 rounded-l-2xl"></div><div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 ml-2"><i class="fas fa-bullhorn text-amber-500 animate-pulse"></i></div><div class="flex-1 overflow-hidden"><p class="text-[10px] font-bold uppercase tracking-widest text-amber-500 mb-0.5">Clinic Notice</p><div class="overflow-hidden h-[20px]"><div id="announceTicker" style="transition:transform .5s ease"><?php foreach($announcements as $ann): ?><div class="h-[20px] flex items-center text-sm truncate"><strong class="text-gray-800 mr-2"><?=htmlspecialchars($ann['title'])?>:</strong><span class="text-gray-600"><?=htmlspecialchars($ann['description'])?></span></div><?php endforeach; ?></div></div></div></div></div></section>
<script>const _t=document.getElementById('announceTicker');let _ti=0;if(<?=count($announcements)?>  >1&&_t)setInterval(()=>{_ti=(_ti+1)%<?=count($announcements)?>;_t.style.transform=`translateY(-${_ti*20}px)`;},3500);</script>
<?php endif; ?>

<!--  ABOUT  -->
<section id="about" class="relative overflow-hidden">

  <!--  Part 1: Story Block (Dark)  -->
  <div class="bg-[#001630] relative py-28 pb-40">
    <!-- Background texture -->
    <div class="absolute inset-0 opacity-[.03]"><svg width="100%" height="100%"><defs><pattern id="g2" width="60" height="60" patternUnits="userSpaceOnUse"><path d="M 60 0 L 0 0 0 60" fill="none" stroke="white" stroke-width=".5"/></pattern></defs><rect width="100%" height="100%" fill="url(#g2)"/></svg></div>
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#ea741b]/10 rounded-full filter blur-[120px] -translate-y-1/2 translate-x-1/3"></div>
    <div class="absolute bottom-0 left-1/4 w-[500px] h-[500px] bg-[#004591]/12 rounded-full filter blur-[130px]"></div>

    <div class="max-w-5xl mx-auto px-5 lg:px-8 relative z-10">
      <!-- Header -->
      <div class="text-center mb-16 reveal">
        <div class="gold-bar mx-auto mb-5"></div>
        <p class="text-[#ea741b] text-[11px] font-bold uppercase tracking-[.3em] mb-4 flex items-center justify-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-[#ea741b] animate-pulse"></span> About Our Clinic</p>
        <h2 class="font-serif text-4xl lg:text-[56px] font-bold text-white leading-[1.1] mb-6">Mamun's Orthodental</h2>
        <p class="text-white/25 text-sm font-semibold uppercase tracking-[.2em]">Est. 2004 · Dhaka, Bangladesh</p>
      </div>

      <!-- Narrative - editorial two-column on desktop -->
      <div class="grid md:grid-cols-2 gap-x-16 gap-y-10 mb-6 reveal">
        <!-- Column 1: The Practice & Services -->
        <div>
          <p class="text-white/80 text-[17px] leading-[1.85] font-light mb-6">
            <strong class="text-white font-bold text-xl block mb-2">Best Dental Clinic in Dhaka — Trusted Since 2004</strong>
            <strong class="text-[#ea741b] font-semibold">Mamun's Orthodental</strong> is a prominent, state-of-the-art dental practice specializing in advanced dental surgery and <em class="text-white not-italic font-medium">Dentofacial Orthopedics</em>&mdash;the science of correcting tooth alignment and guiding jaw development for a perfect facial profile.
          </p>
          
          <div class="bg-white/5 border border-white/10 rounded-2xl p-5 backdrop-blur-sm shadow-xl">
            <h4 class="text-white font-serif text-lg mb-4 flex items-center gap-2"><i class="fas fa-crown text-[#ea741b] text-sm"></i> Core Specialties</h4>
            <ul class="space-y-3">
              <li class="flex items-start gap-3 text-white/60 text-sm leading-relaxed"><i class="fas fa-check text-[#ea741b] mt-1 text-xs"></i> <span><strong class="text-white/90">Orthodontics:</strong> Precision braces (metal, ceramic, invisible) for children and adults.</span></li>
              <li class="flex items-start gap-3 text-white/60 text-sm leading-relaxed"><i class="fas fa-check text-[#ea741b] mt-1 text-xs"></i> <span><strong class="text-white/90">Cosmetic Dentistry:</strong> Signature smile designs, veneers, and teeth whitening.</span></li>
              <li class="flex items-start gap-3 text-white/60 text-sm leading-relaxed"><i class="fas fa-check text-[#ea741b] mt-1 text-xs"></i> <span><strong class="text-white/90">Advanced Care:</strong> Permanent implants, complex root canals, and restorative crowns.</span></li>
            </ul>
          </div>
        </div>

        <!-- Column 2: The Doctor -->
        <div class="relative">
          <div class="absolute -left-8 top-0 bottom-0 w-px bg-gradient-to-b from-[#ea741b]/50 to-transparent hidden md:block"></div>
          <p class="text-[#ea741b] text-[11px] font-bold uppercase tracking-[.2em] mb-2">Meet The Expert</p>
          <h3 class="text-white font-serif text-2xl md:text-3xl font-bold mb-4">Dr. Mohammad Shamim Al Mamun</h3>
          <p class="text-white/60 text-[15px] leading-[1.85] mb-6">
            Highly regarded for mastering complex alignment cases, Dr. Mamun is celebrated by patients for his <span class="text-white/90 font-medium">calm and caring environment</span> and his commitment to discussing all treatment options thoroughly before any procedure.
          </p>
          <div class="space-y-5">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-xl bg-[#ea741b]/10 flex items-center justify-center flex-shrink-0 border border-[#ea741b]/20 shadow-[0_0_15px_rgba(234,116,27,0.15)]"><i class="fas fa-graduation-cap text-[#ea741b] text-lg"></i></div>
              <div>
                <p class="text-white/90 text-sm font-semibold uppercase tracking-wider mb-1">Academic Brilliance</p>
                <p class="text-white/50 text-[13.5px] leading-relaxed">BDS (2008) from Dhaka Dental College. FCPS in Orthodontics (2012) specializing in straightening teeth and Smile Design.</p>
              </div>
            </div>
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-xl bg-[#ea741b]/10 flex items-center justify-center flex-shrink-0 border border-[#ea741b]/20 shadow-[0_0_15px_rgba(234,116,27,0.15)]"><i class="fas fa-user-tie text-[#ea741b] text-lg"></i></div>
              <div>
                <p class="text-white/90 text-sm font-semibold uppercase tracking-wider mb-1">Professional Leadership</p>
                <p class="text-white/50 text-[13.5px] leading-relaxed">Associate Professor & Head of Orthodontics at Bangladesh Dental College. Consultant at Labaid Dental & Apollo Clinic Dhanmondi.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Divider line -->
      <div class="w-full h-px bg-gradient-to-r from-transparent via-white/10 to-transparent my-10 reveal"></div>
    </div>
  </div>

  <!--  Part 2: Stats Ribbon (overlapping) -->
  <div class="relative z-20 -mt-24">
    <div class="max-w-6xl mx-auto px-5 lg:px-8">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5" data-stagger>

        <!-- Stat 1: Orthodontic Patients -->
        <div class="about-stat-tile group" style="--accent:#ea741b">
          <div class="about-stat-tile-inner bg-gradient-to-br from-[#ea741b] to-[#cf5e0e] text-white">
            <div class="about-stat-icon-ring">
              <i class="fas fa-teeth text-lg"></i>
            </div>
            <h3 class="font-serif text-[44px] lg:text-5xl font-bold leading-none counter-value" data-target="600">0</h3>
            <p class="text-white/85 text-[10px] font-bold uppercase tracking-[.2em] mt-2">Orthodontic Patients</p>
            <div class="mt-3 inline-flex items-center gap-1.5 px-2.5 py-1 bg-white/15 rounded-full">
              <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
              <span class="text-[8px] font-bold uppercase tracking-widest text-white/80">Signature</span>
            </div>
          </div>
        </div>

        <!-- Stat 2: Dental Patients -->
        <div class="about-stat-tile group" style="--accent:#004591">
          <div class="about-stat-tile-inner bg-[#002a60] border border-white/10 text-white">
            <div class="about-stat-icon-ring" style="background:rgba(234,116,27,.12)">
              <i class="fas fa-users text-[#ea741b] text-lg"></i>
            </div>
            <h3 class="font-serif text-[44px] lg:text-5xl font-bold leading-none counter-value" data-target="15000">0</h3>
            <p class="text-white/55 text-[10px] font-bold uppercase tracking-[.2em] mt-2">Dental Patients</p>
            <div class="mt-3 flex items-center gap-1.5 text-[#ea741b]">
              <i class="fas fa-arrow-trend-up text-[10px]"></i>
              <span class="text-[9px] font-bold uppercase tracking-wider">& counting</span>
            </div>
          </div>
        </div>

        <!-- Stat 3: Years Experience -->
        <div class="about-stat-tile group" style="--accent:#004591">
          <div class="about-stat-tile-inner bg-[#001d40] border border-white/10 text-white">
            <div class="about-stat-icon-ring" style="background:rgba(234,116,27,.12)">
              <i class="fas fa-award text-[#ea741b] text-lg"></i>
            </div>
            <h3 class="font-serif text-[44px] lg:text-5xl font-bold leading-none counter-value" data-target="20">0</h3>
            <p class="text-white/55 text-[10px] font-bold uppercase tracking-[.2em] mt-2">Years of Experience</p>
            <div class="mt-3 flex gap-0.5">
              <?php for($i=0;$i<5;$i++) echo '<i class="fas fa-star text-[#ea741b] text-[9px]"></i>'; ?>
            </div>
          </div>
        </div>

        <!-- Stat 4: Services -->
        <div class="about-stat-tile group" style="--accent:#ea741b">
          <div class="about-stat-tile-inner bg-gradient-to-br from-white/10 to-white/[.04] backdrop-blur-xl border border-white/10 text-white">
            <div class="about-stat-icon-ring" style="background:rgba(234,116,27,.12)">
              <i class="fas fa-hand-holding-medical text-[#ea741b] text-lg"></i>
            </div>
            <h3 class="font-serif text-[44px] lg:text-5xl font-bold leading-none counter-value text-[#ea741b]" data-target="15">0</h3>
            <p class="text-[#ea741b] text-[10px] font-bold uppercase tracking-[.2em] mt-2">Services Offered</p>
            <div class="mt-3 inline-flex items-center gap-1 text-[#ea741b]">
              <i class="fas fa-check-circle text-[9px]"></i>
              <span class="text-[9px] font-bold uppercase tracking-wider">Full-Spectrum</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Part 3: Specialties Showcase (White) -->
  <div id="services" class="bg-white pt-20 pb-28 relative">
    <div class="max-w-7xl mx-auto px-5 lg:px-8">
      
      <div class="text-center mb-14 reveal">
        <p class="text-[#ea741b] text-[11px] font-bold uppercase tracking-[.3em] mb-3">Our Services</p>
        <h3 class="font-serif text-3xl lg:text-4xl font-bold text-[#004591] leading-tight">What We <span class="italic font-light text-[#ea741b]">Provide</span></h3>
        <p class="text-gray-400 text-sm mt-3 max-w-lg mx-auto">From orthodontic transformations to restorative care  -  comprehensive dental solutions under one roof.</p>
      </div>

      <!--  Featured: Orthodontic Treatment -->
      <div class="mb-8 reveal">
        <div class="about-svc-card group relative overflow-hidden">
          <div class="h-1.5 rounded-t-2xl bg-gradient-to-r from-[#ea741b] to-[#cf5e0e]"></div>
          <div class="p-8 lg:p-10">
            <div class="flex flex-col lg:flex-row lg:items-center gap-8">
              <div class="flex-1">
                <div class="flex items-center gap-4 mb-4">
                  <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#FFF7F0] to-[#FFF0E5] flex items-center justify-center shadow-sm group-hover:scale-110 group-hover:-rotate-3 transition-all duration-400">
                    <i class="fas fa-teeth text-[#ea741b] text-2xl"></i>
                  </div>
                  <div>
                    <span class="inline-block px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest mb-1 border border-[#ea741b]/20 text-[#ea741b] bg-[#ea741b]/5">Core Specialty</span>
                    <h4 class="font-serif text-2xl font-bold text-[#004591] group-hover:text-[#ea741b] transition-colors">Orthodontic Treatment</h4>
                  </div>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed mb-5">Our flagship service precision orthodontics to correct tooth alignment and jaw development for children and adults alike.</p>
                <a href="#contact" class="inline-flex items-center gap-1.5 text-[#ea741b] text-[11px] font-bold uppercase tracking-widest hover:gap-2.5 transition-all">Book Consultation <i class="fas fa-arrow-right text-[9px]"></i></a>
              </div>
              <div class="flex flex-wrap gap-3">
                <?php foreach([
                  ['fa-brackets-curly', 'Braces System',           'Metal, ceramic & self-ligating brackets'],
                  ['fa-align-center',   'Clear Aligner System',    'Virtually invisible removable aligners'],
                  ['fa-rotate',         'Removable Appliances',    'Functional & removable corrective devices']
                ] as [$subIc, $subTitle, $subDesc]): ?>
                <div class="flex-1 min-w-[160px] bg-[#F8FAFD] border border-gray-100 rounded-2xl p-4 hover:border-[#ea741b]/20 hover:shadow-md transition-all">
                  <div class="w-9 h-9 rounded-xl bg-[#ea741b]/10 flex items-center justify-center mb-3">
                    <i class="fas <?=$subIc?> text-[#ea741b] text-sm"></i>
                  </div>
                  <p class="text-[#004591] font-bold text-sm mb-0.5"><?=$subTitle?></p>
                  <p class="text-gray-400 text-xs leading-relaxed"><?=$subDesc?></p>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Remaining Services Grid -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6" data-stagger>
        <?php foreach([
          ['icon'=>'fa-shield-halved',      'title'=>'Scaling & Polishing',   'tag'=>'Preventive',      'desc'=>'Professional cleaning to remove tartar and plaque for healthier gums.',  'color'=>'#004591', 'bg'=>'from-[#EEF4FC] to-[#E3EDFA]', 'url'=>'services/scaling-polishing-dhaka.php'],
          ['icon'=>'fa-star',               'title'=>'Tooth Whitening',       'tag'=>'Cosmetic',        'desc'=>'Advanced whitening treatments for a brighter, more confident smile.',    'color'=>'#ea741b', 'bg'=>'from-[#FFF7F0] to-[#FFF0E5]', 'url'=>'services/teeth-whitening-dhaka.php'],
          ['icon'=>'fa-teeth-open',          'title'=>'Root Canal Treatment', 'tag'=>'Endodontics',     'desc'=>'Precision endodontics to save infected teeth and relieve pain.',          'color'=>'#004591', 'bg'=>'from-[#EEF4FC] to-[#E3EDFA]', 'url'=>'services/root-canal-treatment-dhaka.php'],
          ['icon'=>'fa-tooth',              'title'=>'Tooth Extraction',      'tag'=>'Oral Surgery',    'desc'=>'Safe, gentle extractions including wisdom teeth removal.',                'color'=>'#ea741b', 'bg'=>'from-[#FFF7F0] to-[#FFF0E5]', 'url'=>null],
          ['icon'=>'fa-pen-nib',            'title'=>'Cosmetic Filling',      'tag'=>'Restorative',     'desc'=>'Tooth-colored composite fillings for a natural, seamless look.',           'color'=>'#004591', 'bg'=>'from-[#EEF4FC] to-[#E3EDFA]', 'url'=>null],
          ['icon'=>'fa-crown',              'title'=>'Crown & Bridge',        'tag'=>'Prosthodontics',  'desc'=>'Custom-crafted crowns and bridges to restore damaged or missing teeth.',   'color'=>'#ea741b', 'bg'=>'from-[#FFF7F0] to-[#FFF0E5]', 'url'=>null],
          ['icon'=>'fa-grin-beam',           'title'=>'Denture',              'tag'=>'Replacement',     'desc'=>'Removable tooth replacement solutions — partial or full dentures.',       'color'=>'#004591', 'bg'=>'from-[#EEF4FC] to-[#E3EDFA]', 'url'=>null],
          ['icon'=>'fa-circle-half-stroke',  'title'=>'Occlusal Splint',      'tag'=>'TMJ Care',        'desc'=>'Custom bite guards to treat jaw disorders, bruxism, and TMJ issues.',      'color'=>'#ea741b', 'bg'=>'from-[#FFF7F0] to-[#FFF0E5]', 'url'=>null]
        ] as $i => $svc): ?>
        <div class="about-svc-card group">
          <div class="h-1 rounded-t-2xl" style="background:<?=$svc['color']?>"></div>
          <div class="p-7">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br <?=$svc['bg']?> flex items-center justify-center mb-5 group-hover:scale-110 group-hover:-rotate-3 transition-all duration-400 shadow-sm">
              <i class="fas <?=$svc['icon']?> text-xl" style="color:<?=$svc['color']?>"></i>
            </div>
            <span class="inline-block px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest mb-3 border" style="color:<?=$svc['color']?>;border-color:<?=$svc['color']?>20;background:<?=$svc['color']?>08"><?=$svc['tag']?></span>
            <h4 class="font-serif text-xl font-bold text-[#004591] mb-2 group-hover:text-[#ea741b] transition-colors"><?=$svc['title']?></h4>
            <p class="text-gray-500 text-sm leading-relaxed"><?=$svc['desc']?></p>
            <div class="mt-5 flex items-center gap-3">
              <?php if($svc['url']): ?>
              <a href="<?=$svc['url']?>" class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-widest hover:gap-2.5 transition-all" style="color:<?=$svc['color']?>">Learn More <i class="fas fa-arrow-right text-[9px]"></i></a>
              <?php endif; ?>
              <a href="#contact" class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-widest hover:gap-2.5 transition-all text-gray-400 hover:text-[#ea741b]">Book Now <i class="fas fa-arrow-right text-[9px]"></i></a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>

</section>

<!--  GALLERY  -->
<section id="gallery" class="py-28 bg-white lazy-section">
  <div class="max-w-7xl mx-auto px-5 lg:px-8">
    <div class="text-center mb-16 reveal"><div class="gold-bar mx-auto mb-5"></div><p class="text-[#ea741b] text-[11px] font-bold uppercase tracking-[.3em] mb-3">Inside Our Clinic</p><h2 class="font-serif text-4xl lg:text-5xl font-bold text-[#004591]">Clinic Gallery</h2><p class="text-gray-500 mt-4 max-w-xl mx-auto">A modern, clean environment designed for your comfort and safety.</p></div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" data-stagger style="grid-auto-rows:160px">
      <?php if(!empty($gallery_images)): ?>
        <?php foreach($gallery_images as $i=>$img): $big=($i===0); ?>
        <div class="gallery-item rounded-2xl overflow-hidden cursor-pointer <?=$big?'col-span-2 row-span-2':''?> bg-[#F4F7FC] relative group" onclick="openLightbox('<?=htmlspecialchars($img['image_path'])?>','<?=htmlspecialchars($img['caption']??'')?>') ">
          <img src="<?=htmlspecialchars($img['image_path'])?>" alt="<?=htmlspecialchars($img['caption']??'Gallery')?>" loading="lazy" class="w-full h-full object-cover">
          <?php if($img['caption']): ?><div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4 translate-y-2 opacity-0 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300"><p class="text-white font-semibold text-sm"><?=htmlspecialchars($img['caption'])?></p></div><?php endif; ?>
          <div class="absolute inset-0 group-hover:bg-[#004591]/10 transition-colors flex items-center justify-center"><i class="fas fa-expand text-white opacity-0 group-hover:opacity-100 text-xl md:text-2xl transition-opacity drop-shadow-lg"></i></div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <?php foreach([['Reception','fa-concierge-bell',true],['Treatment Room','fa-tooth',false],['Waiting Lounge','fa-couch',false],['Sterilisation','fa-shield-halved',false],['X-Ray Room','fa-x-ray',false],['Consultation','fa-user-doctor',false],['Dental Chair','fa-chair',false],['Kids Corner','fa-child',false]] as $i=>[$cap,$ic,$big]): ?>
        <div class="<?=$big?'col-span-2 row-span-2':''?> bg-gradient-to-br <?=$i%2===0?'from-[#e8f0fa] to-[#d0e0f5]':'from-[#fff4ec] to-[#fde8d4]'?> rounded-2xl flex flex-col items-center justify-center border border-gray-100"><i class="fas <?=$ic?> text-2xl md:text-3xl <?=$i%2===0?'text-[#004591]/30':'text-[#ea741b]/30'?> mb-2"></i><p class="text-gray-400 text-xs font-semibold text-center px-3"><?=$cap?></p><p class="text-gray-300 text-[10px] mt-1">Upload from admin</p></div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>
<div id="lightbox" onclick="closeLightbox()"><button onclick="event.stopPropagation();closeLightbox()" class="absolute top-5 right-5 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-xl z-10 transition-all"><i class="fas fa-times"></i></button><img id="lightboxImg" src="" alt="Mamun's Ortho Dental Clinic Gallery" loading="lazy" class="max-w-[90vw] max-h-[88vh] rounded-2xl object-contain"><p id="lightboxCaption" class="text-white/70 text-sm text-center mt-3 absolute bottom-5 left-0 right-0"></p></div>

<!-- ═══ DOCTORS ═══ -->
<section id="doctors" class="doc-section">
  <div class="max-w-6xl mx-auto px-5 lg:px-8">

    <!-- Header -->
    <div class="doc-header reveal">
      <p class="doc-eyebrow">Our Specialists</p>
      <h2 class="doc-title">Expert Care, <span class="doc-title-accent">Real Results</span></h2>
    </div>

    <!-- Featured — Dr. Shamim -->
    <a href="dr-shamim-al-mamun.php" class="doc-hero reveal">
      <div class="doc-hero-left">
        <div class="doc-hero-avatar">
          <i class="fas fa-user-doctor"></i>
        </div>
        <div class="doc-hero-meta">
          <span class="doc-hero-badge"><i class="fas fa-crown"></i> Lead</span>
          <div class="doc-hero-rating">
            <div class="doc-stars"><?php for($i=0;$i<5;$i++) echo '<i class="fas fa-star"></i>'; ?></div>
            <span>4.9/5</span>
          </div>
        </div>
      </div>

      <div class="doc-hero-content">
        <div class="doc-hero-tags">
          <span class="doc-tag doc-tag--active"><span class="doc-tag-dot"></span> Available</span>
          <span class="doc-tag">Founder & Chief Specialist</span>
        </div>

        <h3 class="doc-hero-name">Dr. Mohammad Shamim Al Mamun</h3>
        <p class="doc-hero-role">Consultant Orthodontist & Dentofacial Orthopedics</p>

        <div class="doc-hero-divider"></div>

        <div class="doc-hero-details">
          <div class="doc-hero-creds">
            <?php foreach([['BDS','Dhaka Dental College'],['FCPS','Orthodontics'],['Implantology','Certified']] as [$d,$s]): ?>
            <div class="doc-hero-cred">
              <p class="doc-hero-cred-title"><?=$d?></p>
              <p class="doc-hero-cred-sub"><?=$s?></p>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="doc-hero-positions">
            <p><strong>Assoc. Prof. & Head</strong> — Bangladesh Dental College</p>
            <p><strong>Consultant</strong> — Labaid Dental & Apollo Clinic</p>
          </div>

          <div class="doc-hero-stats">
            <div><span>20+</span><p>Years</p></div>
            <div><span>600+</span><p>Ortho Cases</p></div>
            <div><span>15K+</span><p>Patients</p></div>
          </div>
        </div>

        <span class="doc-hero-cta">View Full Profile <i class="fas fa-arrow-right"></i></span>
      </div>
    </a>

    <!-- Dr. Sabnam -->
    <div class="doc-card reveal">
      <div class="doc-card-left">
        <div class="doc-card-avatar">
          <i class="fas fa-user-nurse"></i>
        </div>
      </div>
      <div class="doc-card-content">
        <div>
          <span class="doc-card-badge">Senior Dental Surgeon</span>
          <h3 class="doc-card-name">Dr. Kohinoor Sabnam</h3>
          <p class="doc-card-role">General & Preventive Dentistry</p>
        </div>
        <div class="doc-card-info">
          <div class="doc-card-creds">
            <?php foreach([['BDS','City Dental College'],['MSc','Microbiology']] as [$d,$s]): ?>
            <div class="doc-hero-cred">
              <p class="doc-hero-cred-title"><?=$d?></p>
              <p class="doc-hero-cred-sub"><?=$s?></p>
            </div>
            <?php endforeach; ?>
          </div>
          <p class="doc-card-desc">Expert general dental care including restorative treatments, extractions, and preventive dentistry with a gentle, patient-first approach.</p>
          <a href="#contact" class="doc-card-cta">Book Appointment <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
    </div>

  </div>
</section>


<!-- TESTIMONIALS -->
<section id="testimonials" class="py-28 bg-[#F8FAFD] lazy-section">
  <div class="max-w-7xl mx-auto px-5 lg:px-8">
    <div class="text-center mb-16 reveal"><div class="gold-bar mx-auto mb-5"></div><p class="text-[#ea741b] text-[11px] font-bold uppercase tracking-[.3em] mb-3">Patient Stories</p><h2 class="font-serif text-4xl lg:text-5xl font-bold text-[#004591]">Real Smiles, Real Stories</h2><p class="text-gray-500 mt-4 max-w-xl mx-auto">Hear from the patients who trusted us with their smiles.</p></div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6" data-stagger>
      <?php foreach($testimonials as $r): ?>
      <div class="testimonial-card">
        <div class="quote-mark">&ldquo;</div>
        <div class="flex gap-0.5 mb-4"><?php for($i=0;$i<($r['stars']??5);$i++) echo '<i class="fas fa-star text-[#ea741b] text-sm"></i>'; ?></div>
        <p class="text-gray-600 text-sm leading-relaxed mb-5 italic">&ldquo;<?=htmlspecialchars($r['text'])?>&rdquo;</p>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-[#004591] flex items-center justify-center text-white text-sm font-bold flex-shrink-0"><?=strtoupper(substr($r['name'],0,1))?></div>
          <div><p class="font-bold text-[#004591] text-sm"><?=htmlspecialchars($r['name'])?></p><p class="text-gray-400 text-xs"><?=htmlspecialchars($r['loc']??'')?>, Dhaka</p></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- BEFORE & AFTER -->
<section id="beforeafter" class="py-28 bg-white lazy-section">
  <div class="max-w-7xl mx-auto px-5 lg:px-8">
    <div class="text-center mb-16 reveal"><div class="gold-bar mx-auto mb-5"></div><p class="text-[#ea741b] text-[11px] font-bold uppercase tracking-[.3em] mb-3">Real Transformations</p><h2 class="font-serif text-4xl lg:text-5xl font-bold text-[#004591]">Before &amp; After</h2><p class="text-gray-500 mt-4 max-w-xl mx-auto">See the real results our patients have experienced with our treatments.</p></div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8" data-stagger>
      <?php if(!empty($cases)): ?>
        <?php foreach($cases as $c): ?>
        <div class="ba-card">
          <div class="grid grid-cols-2 gap-0.5 bg-gray-200">
            <div class="relative overflow-hidden"><div class="aspect-[4/3]"><img src="<?=htmlspecialchars($c['before_image'])?>" alt="Before" loading="lazy" class="w-full h-full object-cover"></div><span class="absolute bottom-2 left-2 bg-red-500 text-white text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full">Before</span></div>
            <div class="relative overflow-hidden"><div class="aspect-[4/3]"><img src="<?=htmlspecialchars($c['after_image'])?>" alt="After" loading="lazy" class="w-full h-full object-cover"></div><span class="absolute bottom-2 left-2 bg-green-500 text-white text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full">After</span></div>
          </div>
          <div class="p-6">
            <h3 class="font-serif text-lg font-bold text-[#004591] mb-2"><?=htmlspecialchars($c['title'])?></h3>
            <?php if($c['description']): ?><p class="text-gray-500 text-sm leading-relaxed"><?=htmlspecialchars($c['description'])?></p><?php endif; ?>
            <a href="#contact" class="mt-4 inline-flex items-center gap-1.5 text-[#ea741b] text-[11px] font-bold uppercase tracking-widest hover:gap-2.5 transition-all">Get Similar Results <i class="fas fa-arrow-right text-[9px]"></i></a>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <?php foreach([['Orthodontic Brace Transformation','18 months of comprehensive orthodontic treatment resulting in a perfectly aligned smile.'],['Teeth Whitening Results','Professional whitening session delivering 8 shades brighter results in a single visit.'],['Dental Implant Case','Full implant restoration providing a natural-looking permanent replacement for missing teeth.']] as [$title,$desc]): ?>
        <div class="ba-card">
          <div class="grid grid-cols-2 gap-0.5 bg-gray-200">
            <div class="aspect-[4/3] bg-gradient-to-br from-[#e8f0fa] to-[#c5d8f0] flex items-center justify-center relative"><i class="fas fa-tooth text-[#004591]/20 text-5xl"></i><span class="absolute bottom-2 left-2 bg-red-500 text-white text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full">Before</span></div>
            <div class="aspect-[4/3] bg-gradient-to-br from-[#fff4ec] to-[#fad4b4] flex items-center justify-center relative"><i class="fas fa-tooth text-[#ea741b]/20 text-5xl"></i><span class="absolute bottom-2 left-2 bg-green-500 text-white text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full">After</span></div>
          </div>
          <div class="p-6">
            <h3 class="font-serif text-lg font-bold text-[#004591] mb-2"><?=$title?></h3>
            <p class="text-gray-500 text-sm leading-relaxed"><?=$desc?></p>
            <p class="mt-3 text-[10px] text-gray-300 font-semibold uppercase tracking-widest">Sample Add real cases from admin</p>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!--  PATIENT TRACKING  -->
<section id="track" class="py-24 bg-[#001630] relative overflow-hidden">
  <div class="absolute inset-0 opacity-[.03]"><svg width="100%" height="100%"><defs><pattern id="trackGrid" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="white" stroke-width=".5"/></pattern></defs><rect width="100%" height="100%" fill="url(#trackGrid)"/></svg></div>
  <div class="absolute top-0 right-1/4 w-[500px] h-[500px] bg-[#ea741b]/8 rounded-full filter blur-[120px]"></div>
  <div class="absolute bottom-0 left-1/3 w-[400px] h-[400px] bg-[#004591]/10 rounded-full filter blur-[100px]"></div>

  <div class="max-w-4xl mx-auto px-5 lg:px-8 relative z-10 text-center">
    <div class="reveal">
      <div class="gold-bar mx-auto mb-5"></div>
      <p class="text-[#ea741b] text-[11px] font-bold uppercase tracking-[.3em] mb-3 flex items-center justify-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-[#ea741b] animate-pulse"></span> Patient Portal
      </p>
      <h2 class="font-serif text-4xl lg:text-5xl font-bold text-white leading-tight mb-4">Track Your Treatment</h2>
      <p class="text-white/40 text-sm max-w-lg mx-auto mb-10">Enter your Patient ID to view your prescriptions, appointments, payments, and complete treatment history.</p>
    </div>

    <div class="reveal max-w-xl mx-auto">
      <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-[0_8px_32px_rgba(0,0,0,.3)]">
        <form id="trackForm" class="space-y-5" onsubmit="return trackPatient()">
          <div class="relative">
            <div class="absolute left-5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-xl bg-[#ea741b]/10 flex items-center justify-center">
              <i class="fas fa-id-card text-[#ea741b]"></i>
            </div>
            <input
              type="text"
              id="trackInput"
              placeholder="Enter your Patient ID (e.g. MOD-1234)"
              class="w-full pl-16 pr-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white text-sm font-medium placeholder:text-white/30 focus:outline-none focus:border-[#ea741b]/50 focus:bg-white/8 focus:ring-2 focus:ring-[#ea741b]/10 transition-all"
              maxlength="9"
              autocomplete="off"
              required
            >
          </div>
          <div id="trackError" class="hidden text-red-400 text-xs font-medium text-left px-2"></div>
          <button type="submit" class="w-full py-4 bg-gradient-to-r from-[#ea741b] to-[#cf5e0e] hover:from-[#cf5e0e] hover:to-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-2xl transition-all duration-300 shadow-lg shadow-[#ea741b]/20 hover:shadow-[#ea741b]/40 hover:-translate-y-0.5 flex items-center justify-center gap-2">
            <i class="fas fa-search text-xs"></i> Track My Records
          </button>
        </form>
        <div class="mt-5 pt-5 border-t border-white/5 flex items-center justify-center gap-6 text-white/25 text-[11px]">
          <span class="flex items-center gap-1.5"><i class="fas fa-shield-halved text-[#ea741b]/50"></i> Secure & Private</span>
          <span class="flex items-center gap-1.5"><i class="fas fa-clock text-[#ea741b]/50"></i> Instant Access</span>
          <span class="flex items-center gap-1.5"><i class="fas fa-mobile-screen text-[#ea741b]/50"></i> Mobile Friendly</span>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
function trackPatient() {
  const input = document.getElementById('trackInput');
  const error = document.getElementById('trackError');
  const val = input.value.trim().toUpperCase();

  error.classList.add('hidden');
  input.classList.remove('border-red-500/50');

  if (!val) {
    error.textContent = 'Please enter your Patient ID.';
    error.classList.remove('hidden');
    input.classList.add('border-red-500/50');
    return false;
  }

  if (!/^MOD-\d{4}$/.test(val)) {
    error.textContent = 'Invalid format. Patient ID must be MOD-XXXX (e.g. MOD-1234).';
    error.classList.remove('hidden');
    input.classList.add('border-red-500/50');
    return false;
  }

  window.location.href = 'patient_record.php?pid=' + val;
  return false;
}

document.getElementById('trackInput').addEventListener('input', function() {
  this.value = this.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
});
</script>

<!--  CONTACT  -->
<?php
date_default_timezone_set('Asia/Dhaka');
$day = date('N'); // 1=Mon, 5=Fri
$hour = date('H');
$is_open = ($day != 5 && $hour >= 9 && $hour < 21);
$status_text = $is_open ? 'Open Now' : 'Currently Closed';
$status_color = $is_open ? 'text-emerald-400' : 'text-rose-400';
$status_bg = $is_open ? 'bg-emerald-500/10 border-emerald-500/20' : 'bg-rose-500/10 border-rose-500/20';
$status_dot = $is_open ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400';
?>
<section id="contact" class="py-24 lg:py-32 bg-[#020813] relative overflow-hidden">
  <!-- Dynamic atmospheric background -->
  <div class="absolute inset-0 opacity-20"><svg width="100%" height="100%"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(#grid)"/></svg></div>
  <div class="absolute top-1/4 -right-1/4 w-[800px] h-[800px] bg-[#ea741b] rounded-full blur-[200px] opacity-[0.07] pointer-events-none"></div>
  <div class="absolute bottom-0 -left-1/4 w-[600px] h-[600px] bg-[#004591] rounded-full blur-[180px] opacity-10 pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-5 lg:px-8 relative z-10">
    <div class="grid lg:grid-cols-12 gap-12 lg:gap-20 items-center">
      
      <!-- Left Column: Smart Contact Information -->
      <div class="lg:col-span-6 reveal-left">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-1.5 h-6 bg-gradient-to-b from-[#ea741b] to-[#cf5e0e] rounded-full"></div>
          <p class="text-white/60 text-[11px] font-bold uppercase tracking-[.3em]">Let's Connect</p>
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border backdrop-blur-md <?=$status_bg?>">
            <span class="w-1.5 h-1.5 rounded-full <?=$status_dot?> shadow-[0_0_8px_currentColor]"></span>
            <span class="<?=$status_color?> text-[9px] font-bold uppercase tracking-widest"><?=$status_text?></span>
          </div>
        </div>
        
        <h2 class="font-serif text-4xl lg:text-5xl font-bold text-white leading-[1.15] mb-8">
          Start Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ea741b] to-amber-400 italic pr-2">Smile Journey</span> Today.
        </h2>
        
        <p class="text-white/50 text-[15px] leading-relaxed max-w-md mb-10">
          Reach out to our experts for a personalized consultation. We ensure an elite, comfortable experience from your very first visit.
        </p>
        
        <div class="grid sm:grid-cols-2 gap-5" data-stagger>
          <!-- Location -->
          <div class="sm:col-span-2 group">
            <div class="bg-white/[0.03] hover:bg-white/[0.05] border border-white/[0.08] hover:border-white/20 backdrop-blur-xl rounded-[24px] p-6 transition-all duration-300 relative overflow-hidden">
              <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-transparent via-[#004591] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              <div class="flex items-start gap-5">
                <div class="w-12 h-12 rounded-2xl bg-[#004591]/20 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-500 shadow-lg shadow-[#004591]/20">
                  <i class="fas fa-map-location-dot text-[#4da6ff] text-xl"></i>
                </div>
                <div>
                  <p class="text-white/40 text-[10px] font-bold uppercase tracking-widest mb-1.5">Clinic Location</p>
                  <p class="text-white/90 font-medium text-[15px] leading-relaxed">5/4 (2nd Floor), Block A, Road 5<br>Lalmatia, Dhaka-1207.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Phone -->
          <div class="group">
            <div class="bg-white/[0.03] hover:bg-white/[0.05] border border-white/[0.08] hover:border-[#ea741b]/30 backdrop-blur-xl rounded-[24px] p-6 transition-all duration-300 h-full relative overflow-hidden">
              <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-transparent via-[#ea741b] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              <div class="w-10 h-10 rounded-xl bg-[#ea741b]/10 flex items-center justify-center mb-4 group-hover:-translate-y-1 transition-transform duration-300">
                <i class="fas fa-phone-volume text-[#ea741b] text-lg"></i>
              </div>
              <p class="text-white/40 text-[10px] font-bold uppercase tracking-widest mb-1.5">Direct Line</p>
              <a href="tel:+8801712718527" class="block text-white/90 hover:text-[#ea741b] font-medium text-[14px] transition-colors mb-1">+880 1712-718527</a>
              <a href="https://wa.me/8801712718527?text=Hello!%20I%27d%20like%20to%20book%20an%20appointment." target="_blank" class="inline-flex items-center gap-1.5 text-[#25d366]/80 hover:text-[#25d366] text-[12px] font-medium transition-colors"><i class="fab fa-whatsapp"></i> Chat on WhatsApp</a>
            </div>
          </div>

          <!-- Email -->
          <div class="group">
            <div class="bg-white/[0.03] hover:bg-white/[0.05] border border-white/[0.08] hover:border-[#25d366]/30 backdrop-blur-xl rounded-[24px] p-6 transition-all duration-300 h-full relative overflow-hidden">
              <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-transparent via-[#25d366] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              <div class="w-10 h-10 rounded-xl bg-[#25d366]/10 flex items-center justify-center mb-4 group-hover:-translate-y-1 transition-transform duration-300">
                <i class="fas fa-envelope-open-text text-[#25d366] text-lg"></i>
              </div>
              <p class="text-white/40 text-[10px] font-bold uppercase tracking-widest mb-1.5">Email Support</p>
              <a href="mailto:mamunddcbdc@gmail.com" class="block text-white/90 hover:text-[#25d366] font-medium text-[13px] break-all transition-colors">mamunddcbdc@gmail.com</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Premium Form -->
      <div class="lg:col-span-6 relative reveal-right">
        <!-- Elegant glowing aura -->
        <div class="absolute -inset-1 bg-gradient-to-b from-[#ea741b]/30 via-[#004591]/20 to-transparent rounded-[36px] blur-xl opacity-70 -z-10"></div>
        
        <div class="bg-[#040d1e]/80 backdrop-blur-2xl border border-white/10 rounded-[32px] p-8 sm:p-10 shadow-2xl relative overflow-hidden">
          <!-- Decorative top highlight -->
          <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-[#ea741b] to-transparent opacity-50"></div>
          
          <div class="mb-8">
            <h3 class="font-serif text-2xl font-bold text-white mb-2">Request an Appointment</h3>
            <p class="text-white/40 text-sm">Fill in your details, and we'll get back to you shortly.</p>
          </div>

          <form method="POST" class="space-y-5" id="contact">
            <input type="hidden" name="contact_submit" value="1">
            
            <div class="relative group">
              <input type="text" name="contact_name" id="contact_name" placeholder=" " required class="block w-full bg-white/[0.02] border border-white/[0.08] rounded-2xl px-5 pt-6 pb-2 text-white text-[15px] outline-none focus:bg-white/[0.04] focus:border-[#ea741b]/50 transition-all peer">
              <label for="contact_name" class="absolute left-5 top-4 text-white/30 text-xs uppercase tracking-wider transition-all peer-placeholder-shown:text-[14px] peer-placeholder-shown:top-4 peer-placeholder-shown:normal-case peer-focus:text-xs peer-focus:top-2 peer-focus:uppercase peer-focus:text-[#ea741b] pointer-events-none font-medium">Your Full Name</label>
            </div>

            <div class="relative group">
              <div class="mod-dropdown relative" id="contactCountryCode" data-name="contact_country_code" data-placeholder="Code" style="position:absolute;left:0;top:0;bottom:0;z-index:2;width:auto">
                <input type="hidden" name="contact_country_code" value="+880">
                <div class="mod-dropdown-trigger" style="height:100%;border:none;border-radius:16px 0 0 16px;border-right:1px solid rgba(255,255,255,0.08);padding:0 14px;display:flex;align-items:center;background:transparent">
                  <span class="mod-dropdown-selected" style="font-size:15px;white-space:nowrap">🇧🇩 +880</span>
                  <svg class="mod-dropdown-chevron" style="margin-left:6px" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                </div>
                <div class="mod-dropdown-panel" style="left:0;min-width:220px">
                  <div class="mod-dropdown-option is-selected" data-value="+880"><span class="opt-check"></span><span>🇧🇩 +880 Bangladesh</span></div>
                  <div class="mod-dropdown-option" data-value="+1"><span class="opt-check"></span><span>🇺🇸 +1 USA</span></div>
                  <div class="mod-dropdown-option" data-value="+44"><span class="opt-check"></span><span>🇬🇧 +44 UK</span></div>
                  <div class="mod-dropdown-option" data-value="+91"><span class="opt-check"></span><span>🇮🇳 +91 India</span></div>
                  <div class="mod-dropdown-option" data-value="+971"><span class="opt-check"></span><span>🇦🇪 +971 UAE</span></div>
                  <div class="mod-dropdown-option" data-value="+966"><span class="opt-check"></span><span>🇸🇦 +966 Saudi</span></div>
                  <div class="mod-dropdown-option" data-value="+65"><span class="opt-check"></span><span>🇸🇬 +65 Singapore</span></div>
                  <div class="mod-dropdown-option" data-value="+60"><span class="opt-check"></span><span>🇲🇾 +60 Malaysia</span></div>
                  <div class="mod-dropdown-option" data-value="+61"><span class="opt-check"></span><span>🇦🇺 +61 Australia</span></div>
                  <div class="mod-dropdown-option" data-value="+81"><span class="opt-check"></span><span>🇯🇵 +81 Japan</span></div>
                  <div class="mod-dropdown-option" data-value="+82"><span class="opt-check"></span><span>🇰🇷 +82 South Korea</span></div>
                  <div class="mod-dropdown-option" data-value="+86"><span class="opt-check"></span><span>🇨🇳 +86 China</span></div>
                  <div class="mod-dropdown-option" data-value="+49"><span class="opt-check"></span><span>🇩🇪 +49 Germany</span></div>
                  <div class="mod-dropdown-option" data-value="+33"><span class="opt-check"></span><span>🇫🇷 +33 France</span></div>
                  <div class="mod-dropdown-option" data-value="+39"><span class="opt-check"></span><span>🇮🇹 +39 Italy</span></div>
                  <div class="mod-dropdown-option" data-value="+7"><span class="opt-check"></span><span>🇷🇺 +7 Russia</span></div>
                  <div class="mod-dropdown-option" data-value="+20"><span class="opt-check"></span><span>🇪🇬 +20 Egypt</span></div>
                  <div class="mod-dropdown-option" data-value="+234"><span class="opt-check"></span><span>🇳🇬 +234 Nigeria</span></div>
                </div>
              </div>
              <input type="tel" name="contact_phone" id="contact_phone" placeholder=" " required class="block w-full bg-white/[0.02] border border-white/[0.08] rounded-2xl pl-[110px] pr-5 pt-6 pb-2 text-white text-[15px] outline-none focus:bg-white/[0.04] focus:border-[#ea741b]/50 transition-all peer">
              <label for="contact_phone" class="absolute left-[115px] top-4 text-white/30 text-xs uppercase tracking-wider transition-all peer-placeholder-shown:text-[14px] peer-placeholder-shown:top-4 peer-placeholder-shown:normal-case peer-focus:text-xs peer-focus:top-2 peer-focus:uppercase peer-focus:text-[#ea741b] pointer-events-none font-medium">Phone Number</label>
            </div>

              <div class="relative group">
                <div class="mod-dropdown" data-name="contact_service">
                  <input type="hidden" name="contact_service" value="">
                  <div class="mod-dropdown-trigger">
                    <span class="mod-dropdown-label">Treatment</span>
                    <span class="mod-dropdown-selected">Select Service...</span>
                    <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                  </div>
                  <div class="mod-dropdown-panel">
                    <div class="mod-dropdown-option" data-value="">
                      <span class="opt-check"></span>
                      <span>Select Service...</span>
                    </div>
                    <?php foreach($services_list as $s): ?>
                    <div class="mod-dropdown-option" data-value="<?= htmlspecialchars($s['name']) ?>">
                      <span class="opt-check"></span>
                      <span><?= htmlspecialchars($s['name']) ?></span>
                    </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>

            <div class="relative group">
              <textarea name="contact_message" id="contact_message" rows="3" placeholder=" " class="block w-full bg-white/[0.02] border border-white/[0.08] rounded-2xl px-5 pt-6 pb-2 text-white text-[15px] outline-none focus:bg-white/[0.04] focus:border-[#ea741b]/50 transition-all peer resize-none"></textarea>
              <label for="contact_message" class="absolute left-5 top-4 text-white/30 text-xs uppercase tracking-wider transition-all peer-placeholder-shown:text-[14px] peer-placeholder-shown:top-4 peer-placeholder-shown:normal-case peer-focus:text-xs peer-focus:top-2 peer-focus:uppercase peer-focus:text-[#ea741b] pointer-events-none font-medium">Message or preferred time...</label>
            </div>

            <!-- CAPTCHA -->
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-4">
              <p class="text-white/40 text-xs mb-2"><i class="fas fa-shield-halved mr-1"></i> Spam check: <?= htmlspecialchars($captcha['question']) ?></p>
              <input type="hidden" name="captcha_key" value="<?= htmlspecialchars($captcha['key']) ?>">
              <input type="number" name="captcha_answer" required placeholder="Your answer" class="w-full bg-white/[0.04] border border-white/[0.08] rounded-xl px-4 py-2.5 text-white text-sm outline-none focus:border-[#ea741b]/50 transition-all">
            </div>

            <button type="submit" class="w-full py-4 relative overflow-hidden rounded-2xl group mt-2">
              <div class="absolute inset-0 bg-gradient-to-r from-[#ea741b] to-[#cf5e0e] transition-transform duration-300 group-hover:scale-[1.02]"></div>
              <div class="relative flex items-center justify-center gap-2 text-white font-bold text-[12px] uppercase tracking-widest">
                <span>Submit Request</span>
                <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
              </div>
            </button>
            <p class="text-center text-white/20 text-[10px] uppercase tracking-widest mt-4 flex items-center justify-center gap-1.5"><i class="fas fa-lock"></i> Your information is strictly confidential</p>
          </form>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══ CLINIC OVERVIEW (SEO) ═══ -->
<section class="py-24 bg-white relative overflow-hidden border-t border-gray-100 lazy-section">
  <!-- Subtle background pattern -->
  <div class="absolute inset-0 opacity-[.02]"><svg width="100%" height="100%"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1.5" fill="#004591"/></pattern></defs><rect width="100%" height="100%" fill="url(#dots)"/></svg></div>

  <div class="max-w-7xl mx-auto px-5 lg:px-8 relative z-10">
    <!-- Section Header -->
    <div class="text-center mb-16 reveal">
      <div class="gold-bar mx-auto mb-5"></div>
      <p class="text-[#ea741b] text-[11px] font-bold uppercase tracking-[.3em] mb-3">Complete Care in Dhaka</p>
      <h2 class="font-serif text-3xl lg:text-4xl font-bold text-[#004591]">Trusted Dental Clinic in Lalmatia, Mohammadpur, Dhaka</h2>
    </div>

    <div class="grid lg:grid-cols-12 gap-8 reveal">
      
      <!-- Left Column: About & Why Choose Us -->
      <div class="lg:col-span-7 flex flex-col gap-8">
        
        <!-- About Box (Premium Light) -->
        <div class="bg-[#F8FAFD] rounded-3xl p-8 lg:p-10 border border-[#004591]/5 shadow-sm hover:shadow-md transition-shadow flex-1">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-2xl bg-[#004591]/10 flex items-center justify-center text-[#004591]"><img src="Logo.png" alt="Mamun's Ortho Dental Logo" loading="lazy" class="w-7 h-7 object-contain"></div>
            <div>
              <h3 class="font-serif text-2xl font-bold text-[#004591]">Mamun's Ortho Dental</h3>
              <p class="text-[#ea741b] text-[10px] font-bold uppercase tracking-widest mt-0.5">Premier Dental Care Center</p>
            </div>
          </div>
          <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed">
            <p>Mamun's Ortho Dental is a premier dental care center located at <strong>5/4 (2nd Floor), Block A, Road 5, Lalmatia, Mohammadpur, Dhaka-1207</strong>, Bangladesh. Founded and led by <a href="dr-shamim-al-mamun.php" class="text-[#ea741b] font-semibold hover:underline">Dr. Mohammad Shamim Al Mamun</a> — a renowned Consultant Orthodontist with over 20 years of clinical experience — our clinic has served more than <strong>15,000 dental patients</strong> and successfully treated <strong>600+ orthodontic cases</strong>.</p>
            <p>As a specialist in <strong>Dentofacial Orthopedics</strong>, Dr. Mamun holds a BDS from Dhaka Dental College and FCPS in Orthodontics from the Bangladesh College of Physicians and Surgeons. He currently serves as Associate Professor & Head of the Department of Orthodontics at Bangladesh Dental College and is a Consultant Orthodontist at Labaid Hospital, Dhanmondi.</p>
          </div>
        </div>

        <!-- Why Choose Us Box (Premium Dark/Glassmorphic) -->
        <div class="bg-gradient-to-br from-[#001230] to-[#002a60] rounded-3xl p-8 lg:p-10 text-white relative overflow-hidden group hover:shadow-2xl transition-all">
          <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-[#ea741b] rounded-full blur-[90px] opacity-20 group-hover:opacity-40 transition-opacity"></div>
          <div class="relative z-10">
            <h3 class="font-serif text-2xl font-bold mb-4">Why Choose Mamun's Ortho Dental?</h3>
            <p class="text-white/70 text-sm leading-relaxed mb-6">Patients across <strong>Dhaka</strong> — from Lalmatia, Mohammadpur, Dhanmondi, Mirpur, and Eskaton — choose us for our combination of specialist expertise, modern equipment, strict WHO-standard sterilisation protocols, and a warm, family-friendly environment. Whether you need braces treatment in Dhaka, a root canal in Lalmatia, or teeth whitening near Mohammadpur, our experienced team delivers precision care with compassion.</p>
            
            <div class="bg-white/5 border border-white/10 rounded-2xl p-5 mb-5">
              <p class="text-white/90 text-sm leading-relaxed">Mamun's Ortho Dental is open <strong>Saturday to Thursday, 9:00 AM – 9:00 PM</strong> (closed on Fridays). Walk-ins welcome. To book an appointment, call us or use the contact form above. We are conveniently located in Lalmatia, Dhaka-1207, just minutes from Mohammadpur bus stand.</p>
            </div>
            
            <div class="flex flex-wrap gap-3">
              <span class="inline-flex items-center gap-2 px-4 py-2 bg-[#ea741b]/20 text-[#ea741b] border border-[#ea741b]/30 rounded-full text-[10px] font-bold uppercase tracking-widest"><i class="fas fa-shield-halved"></i> WHO-Standard Sterilisation</span>
              <span class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 text-white border border-white/10 rounded-full text-[10px] font-bold uppercase tracking-widest"><i class="fas fa-user-md"></i> Specialist Expertise</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Services List -->
      <div class="lg:col-span-5 h-full">
        <div class="bg-white border border-gray-100 rounded-3xl p-8 lg:p-10 shadow-[0_8px_30px_rgba(0,0,0,0.04)] h-full flex flex-col">
          <h3 class="font-serif text-2xl font-bold text-[#004591] mb-2">Our Dental Services in Dhaka</h3>
          <p class="text-gray-500 text-sm mb-6">We offer a comprehensive range of 9 specialized dental treatments including:</p>
          
          <ul class="space-y-4 flex-1">
            <?php foreach([
              ['icon'=>'fa-teeth', 'name'=>'Orthodontic Treatment', 'desc'=>'Braces, Clear Aligners & Removable Appliances'],
              ['icon'=>'fa-shield-halved', 'name'=>'Scaling & Polishing', 'desc'=>'Professional teeth cleaning'],
              ['icon'=>'fa-star', 'name'=>'Tooth Whitening', 'desc'=>'Professional brightening treatment'],
              ['icon'=>'fa-teeth-open', 'name'=>'Root Canal Treatment', 'desc'=>'Pain-free endodontics'],
              ['icon'=>'fa-tooth', 'name'=>'Tooth Extraction', 'desc'=>'Safe & gentle procedures'],
              ['icon'=>'fa-pen-nib', 'name'=>'Cosmetic Filling', 'desc'=>'Natural tooth-colored restorations'],
              ['icon'=>'fa-crown', 'name'=>'Crown & Bridge', 'desc'=>'Custom prosthodontic solutions'],
              ['icon'=>'fa-grin-beam', 'name'=>'Denture', 'desc'=>'Removable tooth replacement'],
              ['icon'=>'fa-circle-half-stroke', 'name'=>'Occlusal Splint', 'desc'=>'TMJ & bruxism treatment']
            ] as $s): ?>
            <li class="flex items-start gap-4 group cursor-default">
              <div class="w-10 h-10 rounded-xl bg-[#F8FAFD] border border-gray-100 flex items-center justify-center text-[#ea741b] group-hover:bg-[#ea741b] group-hover:text-white group-hover:border-[#ea741b] transition-all shrink-0"><i class="fas <?=$s['icon']?> text-sm group-hover:scale-110 transition-transform"></i></div>
              <div>
                <h4 class="font-bold text-[#004591] text-sm group-hover:text-[#ea741b] transition-colors"><?=$s['name']?></h4>
                <p class="text-gray-500 text-xs mt-0.5 leading-snug"><?=$s['desc']?></p>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
          
          <div class="mt-8 pt-6 border-t border-gray-100 text-center">
            <a href="#contact" class="inline-flex items-center gap-2 text-[#ea741b] text-[11px] font-bold uppercase tracking-widest hover:gap-3 transition-all">Book Your Treatment <i class="fas fa-arrow-right text-[10px]"></i></a>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══ FOOTER ═══ -->
<footer class="relative bg-[#020811] overflow-hidden">
  <!-- Decorative top border -->
  <div class="h-px bg-gradient-to-r from-transparent via-[#ea741b]/40 to-transparent"></div>
  
  <!-- Atmospheric glow -->
  <div class="absolute top-0 left-1/4 w-[500px] h-[500px] bg-[#ea741b] rounded-full blur-[250px] opacity-[0.03] pointer-events-none"></div>
  <div class="absolute bottom-0 right-1/4 w-[400px] h-[400px] bg-[#004591] rounded-full blur-[200px] opacity-[0.04] pointer-events-none"></div>

  <!-- Main Footer Content -->
  <div class="max-w-7xl mx-auto px-5 lg:px-8 pt-20 pb-10 relative z-10">
    
    <!-- Top Row: Brand + Newsletter CTA -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-8 mb-16 pb-12 border-b border-white/[0.06]">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#ea741b]/20 to-[#ea741b]/5 border border-[#ea741b]/20 flex items-center justify-center shadow-lg shadow-[#ea741b]/10">
          <img src="Logo.png" alt="Mamun's Ortho Dental Logo" loading="lazy" class="w-9 h-9 object-contain drop-shadow-md">
        </div>
        <div>
          <p class="text-white font-serif text-xl font-bold tracking-tight">Mamun's <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ea741b] to-amber-400">Ortho</span> Dental</p>
          <p class="text-white/20 text-[9px] uppercase tracking-[.25em] font-bold mt-0.5">Premier Dental Care &middot; Lalmatia, Dhaka</p>
        </div>
      </div>
      <a href="#contact" class="group inline-flex items-center gap-3 px-7 py-3.5 bg-gradient-to-r from-[#ea741b] to-[#cf5e0e] hover:from-[#cf5e0e] hover:to-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-2xl transition-all duration-300 shadow-lg shadow-[#ea741b]/20 hover:shadow-[#ea741b]/40 hover:-translate-y-0.5">
        <i class="fas fa-calendar-check"></i> Book Appointment
        <i class="fas fa-arrow-right text-[9px] group-hover:translate-x-1 transition-transform"></i>
      </a>
    </div>

    <!-- Grid: 4 Columns -->
    <div class="grid grid-cols-2 lg:grid-cols-12 gap-y-12 gap-x-8 lg:gap-12 mb-16">
      
      <!-- Column 1: About (wider) -->
      <div class="col-span-2 lg:col-span-4">
        <p class="text-white/50 text-[10px] font-bold uppercase tracking-[.2em] mb-5 flex items-center gap-2">
          <span class="w-4 h-px bg-[#ea741b]"></span> About
        </p>
        <p class="text-white/35 text-[13px] leading-[1.8] mb-6">Patients across Dhaka choose us for specialist expertise, modern equipment, and WHO-standard sterilisation. Whether you need braces, a root canal, or teeth whitening &mdash; our team delivers precision care with compassion.</p>
        <div class="flex items-center gap-3">
          <a href="https://www.facebook.com/mamundental" target="_blank" class="w-9 h-9 rounded-xl bg-white/[0.04] hover:bg-[#1877F2]/20 border border-white/[0.06] hover:border-[#1877F2]/40 flex items-center justify-center transition-all duration-300 group">
            <i class="fab fa-facebook-f text-white/40 group-hover:text-[#1877F2] text-xs transition-colors"></i>
          </a>
          <a href="https://wa.me/8801712718527" target="_blank" class="w-9 h-9 rounded-xl bg-white/[0.04] hover:bg-[#25d366]/20 border border-white/[0.06] hover:border-[#25d366]/40 flex items-center justify-center transition-all duration-300 group">
            <i class="fab fa-whatsapp text-white/40 group-hover:text-[#25d366] text-xs transition-colors"></i>
          </a>
          <a href="mailto:mamunddcbdc@gmail.com" class="w-9 h-9 rounded-xl bg-white/[0.04] hover:bg-[#ea741b]/20 border border-white/[0.06] hover:border-[#ea741b]/40 flex items-center justify-center transition-all duration-300 group">
            <i class="fas fa-envelope text-white/40 group-hover:text-[#ea741b] text-xs transition-colors"></i>
          </a>
          <a href="https://maps.app.goo.gl/MJg2zb1qWj8xq2Uq6" target="_blank" class="w-9 h-9 rounded-xl bg-white/[0.04] hover:bg-[#4285F4]/20 border border-white/[0.06] hover:border-[#4285F4]/40 flex items-center justify-center transition-all duration-300 group">
            <i class="fas fa-map-location-dot text-white/40 group-hover:text-[#4285F4] text-xs transition-colors"></i>
          </a>
        </div>
      </div>

      <!-- Column 2: Quick Links -->
      <div class="col-span-1 lg:col-span-2">
        <p class="text-white/50 text-[10px] font-bold uppercase tracking-[.2em] mb-5 flex items-center gap-2">
          <span class="w-4 h-px bg-[#ea741b]"></span> Explore
        </p>
        <ul class="space-y-3">
          <?php foreach([
            ['#about','About Us'],['#services','Services'],['#gallery','Gallery'],
            ['#testimonials','Reviews'],['#contact','Appointment'],
            ['privacy-policy.php','Privacy Policy'],['terms.php','Terms of Service']
          ] as [$href,$lbl]): ?>
          <li><a href="<?=$href?>" class="text-white/30 hover:text-[#ea741b] text-[13px] transition-colors duration-300 flex items-center gap-2 group"><i class="fas fa-chevron-right text-[7px] text-[#ea741b]/0 group-hover:text-[#ea741b] transition-all"></i> <?=$lbl?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Column 3: Resources -->
      <div class="col-span-1 lg:col-span-2">
        <p class="text-white/50 text-[10px] font-bold uppercase tracking-[.2em] mb-5 flex items-center gap-2">
          <span class="w-4 h-px bg-[#ea741b]"></span> Resources
        </p>
        <ul class="space-y-3">
          <?php foreach([
            ['dr-shamim-al-mamun.php','Dr. Shamim Al Mamun'],
            ['blog/braces-cost-in-dhaka.php','Braces Cost Guide'],
            ['blog/best-age-for-braces.php','Best Age for Braces'],
            ['blog/dental-care-tips-bangladesh.php','Dental Care Tips']
          ] as [$href,$lbl]): ?>
          <li><a href="<?=$href?>" class="text-white/30 hover:text-[#ea741b] text-[13px] transition-colors duration-300 flex items-center gap-2 group"><i class="fas fa-chevron-right text-[7px] text-[#ea741b]/0 group-hover:text-[#ea741b] transition-all"></i> <?=$lbl?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Column 4: Contact Info -->
      <div class="col-span-2 lg:col-span-4">
        <p class="text-white/50 text-[10px] font-bold uppercase tracking-[.2em] mb-5 flex items-center gap-2">
          <span class="w-4 h-px bg-[#ea741b]"></span> Reach Us
        </p>
        <div class="space-y-4">
          <div class="flex items-start gap-4 group">
            <div class="w-9 h-9 rounded-xl bg-white/[0.04] border border-white/[0.06] flex items-center justify-center flex-shrink-0 group-hover:bg-[#004591]/10 group-hover:border-[#004591]/20 transition-all">
              <i class="fas fa-location-dot text-[#ea741b] text-xs"></i>
            </div>
            <div>
              <p class="text-white/50 text-[13px] leading-relaxed">5/4 (2nd Floor), Block A, Road 5, Lalmatia, Dhaka-1207</p>
            </div>
          </div>
          <div class="flex items-start gap-4 group">
            <div class="w-9 h-9 rounded-xl bg-white/[0.04] border border-white/[0.06] flex items-center justify-center flex-shrink-0 group-hover:bg-[#ea741b]/10 group-hover:border-[#ea741b]/20 transition-all">
              <i class="fas fa-phone text-[#ea741b] text-xs"></i>
            </div>
            <div class="space-y-0.5">
              <a href="tel:+8801712718527" class="block text-white/50 hover:text-[#ea741b] text-[13px] transition-colors">+880 1712-718527</a>
              <a href="https://wa.me/8801712718527?text=Hello!%20I%27d%20like%20to%20book%20an%20appointment." target="_blank" class="block text-[#25d366]/50 hover:text-[#25d366] text-[13px] transition-colors"><i class="fab fa-whatsapp mr-1"></i>WhatsApp</a>
            </div>
          </div>
          <div class="flex items-start gap-4 group">
            <div class="w-9 h-9 rounded-xl bg-white/[0.04] border border-white/[0.06] flex items-center justify-center flex-shrink-0 group-hover:bg-[#25d366]/10 group-hover:border-[#25d366]/20 transition-all">
              <i class="fas fa-envelope text-[#ea741b] text-xs"></i>
            </div>
            <a href="mailto:mamunddcbdc@gmail.com" class="text-white/50 hover:text-[#ea741b] text-[13px] transition-colors pt-2">mamunddcbdc@gmail.com</a>
          </div>
          <div class="flex items-start gap-4 group">
            <div class="w-9 h-9 rounded-xl bg-white/[0.04] border border-white/[0.06] flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-500/10 group-hover:border-emerald-500/20 transition-all">
              <i class="fas fa-clock text-[#ea741b] text-xs"></i>
            </div>
            <div class="pt-2">
              <p class="text-white/50 text-[13px]">Sat &ndash; Thu: 9:00 AM &ndash; 9:00 PM</p>
              <p class="text-white/25 text-[11px] mt-0.5">Friday: Closed</p>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Bottom Bar -->
    <div class="border-t border-white/[0.05] pt-8">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-white/20 text-[11px] tracking-wider">&copy; <?=date('Y')?> Mamun's Ortho Dental. All rights reserved.</p>
        <div class="flex items-center gap-6">
          <a href="login_page.php" class="text-white/15 hover:text-white/40 text-[11px] transition-colors flex items-center gap-1.5"><i class="fas fa-lock text-[8px]"></i> Doctor Portal</a>
          <span class="text-white/10">|</span>
          <p class="text-white/15 text-[11px] flex items-center gap-1.5">
            Designed & Developed by <a href="https://umaerislam.com" target="_blank" rel="dofollow" class="text-[#ea741b] hover:text-[#f5973e] font-semibold transition-colors ml-0.5">Umaer Islam</a>
          </p>
        </div>
      </div>
      <!-- AEO/GEO Developer Attribution (visible, crawlable) -->
      <div class="mt-4 pt-4 border-t border-white/[0.04] text-center">
        <p class="text-white/15 text-[10px] leading-relaxed">
          This website and clinic management system was designed, developed, and is maintained by
          <a href="https://umaerislam.com" target="_blank" rel="dofollow" class="text-[#ea741b]/60 hover:text-[#ea741b] transition-colors">Umaer Islam</a>
          — a full-stack web developer specializing in healthcare applications. Visit <a href="https://umaerislam.com" target="_blank" rel="dofollow" class="text-[#ea741b]/60 hover:text-[#ea741b] transition-colors">umaerislam.com</a> for more projects.
        </p>
      </div>
    </div>

  </div>
</footer>

<!--  JAVASCRIPT  -->
<script>
//  Navbar & Scroll Progress  
const navbar = document.getElementById('navbar');
const scrollProgress = document.getElementById('scrollProgress');
window.addEventListener('scroll', () => {
  // Navbar blur/shrink
  navbar.classList.toggle('scrolled', window.scrollY > 60);
  
  // Progress bar
  if(scrollProgress) {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    scrollProgress.style.width = scrolled + '%';
  }
});

//  Parallax Elements (uses CSS variable to avoid overriding animation)
const parallaxElements = document.querySelectorAll('[data-parallax]');
if(parallaxElements.length) {
  window.addEventListener('scroll', () => {
    const scrolled = window.scrollY;
    parallaxElements.forEach(el => {
      const speed = el.dataset.parallax || 0.2;
      el.style.setProperty('--parallax-y', `${scrolled * speed}px`);
    });
  }, { passive: true });
}

//  Mobile menu toggle  
const menuToggle = document.getElementById('menuToggle');
const mobileMenu = document.getElementById('mobileMenu');
if(menuToggle) menuToggle.addEventListener('click', e => { e.stopPropagation(); mobileMenu.classList.toggle('open'); });
document.addEventListener('click', e => { if(mobileMenu && !mobileMenu.contains(e.target) && e.target!==menuToggle) mobileMenu.classList.remove('open'); });
document.querySelectorAll('.mobile-nav-link').forEach(l => l.addEventListener('click', () => mobileMenu.classList.remove('open')));

//  Lightbox  
function openLightbox(src, caption) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightboxCaption').textContent = caption;
  document.getElementById('lightbox').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('open');
  document.getElementById('lightboxImg').src = '';
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if(e.key==='Escape') closeLightbox(); });

//  Scroll reveal (supports multiple animation classes)  
const revealSelectors = '.reveal, .reveal-left, .reveal-right, .reveal-scale, [data-stagger]';
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if(e.isIntersecting) { e.target.classList.add('visible'); revealObserver.unobserve(e.target); }
  });
}, { threshold: 0.1 });
document.querySelectorAll(revealSelectors).forEach(el => revealObserver.observe(el));

//  Animated Counters  
function animateCounters() {
  document.querySelectorAll('.counter-value[data-target]').forEach(el => {
    const target = parseInt(el.dataset.target);
    if(isNaN(target) || el.dataset.counted) return;
    el.dataset.counted = '1';
    const duration = 2000;
    const step = Math.max(1, Math.floor(target / (duration / 16)));
    let current = 0;
    const fmt = v => v.toLocaleString() + '+';
    function tick() {
      current += step;
      if(current >= target) { el.textContent = fmt(target); return; }
      el.textContent = fmt(current);
      requestAnimationFrame(tick);
    }
    tick();
  });
}
const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => { if(e.isIntersecting) { animateCounters(); counterObserver.unobserve(e.target); } });
}, { threshold: 0.2 });
const heroStats = document.querySelector('.hero__stats') || document.querySelector('.hero-stats');
if(heroStats) counterObserver.observe(heroStats);
// Also observe the About bento stats grid
document.querySelectorAll('#about [data-stagger]').forEach(el => counterObserver.observe(el));

// ═══ NEW HERO — Interactive Background
(function() {
    var heroEl = document.querySelector('.hero');
    if (!heroEl) return;

    /* ─── Wait for loader to finish, then reveal hero ─── */
    function revealHero() {
        heroEl.classList.add('hero--revealed');
        /* Start counters after reveal */
        setTimeout(function() {
            heroEl.querySelectorAll('.counter[data-target]').forEach(function(c) {
                var target = +c.dataset.target;
                var cur = 0;
                var step = Math.max(1, Math.floor(target / 50));
                var iv = setInterval(function() {
                    cur += step;
                    if (cur >= target) { cur = target; clearInterval(iv); }
                    c.textContent = cur.toLocaleString();
                }, 30);
            });
        }, 800);
    }

    /* If loader already gone or never existed, reveal immediately */
    if (!document.querySelector('.mod-loader')) {
        setTimeout(revealHero, 100);
    } else {
        document.addEventListener('loader:done', function() {
            setTimeout(revealHero, 100);
        });
        /* Fallback: reveal after 4s no matter what */
        setTimeout(revealHero, 4000);
    }

    /* ─── Custom Cursor ─── */
    var dot = document.getElementById('cursorDot');
    var ring = document.getElementById('cursorRing');
    var glow = document.getElementById('cursorGlow');
    var heroLight = document.getElementById('heroLight');
    var mx = -100, my = -100, dx = -100, dy = -100, gx = -100, gy = -100;

    document.addEventListener('mousemove', function(e) { mx = e.clientX; my = e.clientY; });

    var targets = document.querySelectorAll('a, button, .btn, .hero__service, .hero__card-btn');
    targets.forEach(function(el) {
        el.addEventListener('mouseenter', function() { if(dot) dot.classList.add('hovering'); if(ring) ring.classList.add('hovering'); });
        el.addEventListener('mouseleave', function() { if(dot) dot.classList.remove('hovering'); if(ring) ring.classList.remove('hovering'); });
    });

    /* ─── Interactive Grid (canvas) ─── */
    var canvas = document.getElementById('heroGrid');
    var ctx = canvas ? canvas.getContext('2d') : null;
    var gridSpacing = 40, gridCols, gridRows, heroRect;
    var mouseHeroX = -999, mouseHeroY = -999;
    var heroVisible = true;

    function resizeCanvas() {
        if (!canvas) return;
        heroRect = document.querySelector('.hero').getBoundingClientRect();
        canvas.width = heroRect.width;
        canvas.height = heroRect.height;
        gridCols = Math.ceil(canvas.width / gridSpacing) + 1;
        gridRows = Math.ceil(canvas.height / gridSpacing) + 1;
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    var heroEl = document.querySelector('.hero');
    if (heroEl) {
        heroEl.addEventListener('mousemove', function(e) {
            heroRect = canvas.getBoundingClientRect();
            mouseHeroX = e.clientX - heroRect.left;
            mouseHeroY = e.clientY - heroRect.top;
        });
        heroEl.addEventListener('mouseleave', function() { mouseHeroX = -999; mouseHeroY = -999; });
    }

    function drawGrid() {
        if (!ctx || !heroVisible) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        var radius = 120;
        for (var r = 0; r < gridRows; r++) {
            for (var c = 0; c < gridCols; c++) {
                var bx = c * gridSpacing, by = r * gridSpacing;
                var dxm = bx - mouseHeroX, dym = by - mouseHeroY;
                var dist = Math.sqrt(dxm * dxm + dym * dym);
                var force = Math.max(0, 1 - dist / radius);
                var px = bx + dxm * force * 0.3, py = by + dym * force * 0.3;
                var alpha = 0.08 + force * 0.25;
                var size = 1 + force * 2;
                ctx.beginPath();
                ctx.arc(px, py, size, 0, Math.PI * 2);
                ctx.fillStyle = force > 0.1 ? 'rgba(234,116,27,' + alpha + ')' : 'rgba(255,255,255,' + alpha + ')';
                ctx.fill();
            }
        }
        requestAnimationFrame(drawGrid);
    }
    drawGrid();

    /* Pause animations when hero is off-screen */
    var heroObs = new IntersectionObserver(function(entries) {
        heroVisible = entries[0].isIntersecting;
    }, { threshold: 0 });
    heroObs.observe(heroEl);

    /* ─── Aurora Blobs (cursor-reactive) ─── */
    var auroras = document.querySelectorAll('.hero__aurora');
    if (heroEl) {
        heroEl.addEventListener('mousemove', function(e) {
            var rect = heroEl.getBoundingClientRect();
            var cx = (e.clientX - rect.left) / rect.width - 0.5;
            var cy = (e.clientY - rect.top) / rect.height - 0.5;
            auroras.forEach(function(a, i) {
                var s = (i + 1) * 25;
                a.style.transform = 'translate(' + (cx * s) + 'px,' + (cy * s) + 'px)';
            });
        });
    }

    /* ─── Floating Orbs (cursor-repel) ─── */
    var orbContainer = document.getElementById('heroOrbs');
    var orbs = [];
    if (orbContainer && heroEl) {
        for (var i = 0; i < 12; i++) {
            var el = document.createElement('div');
            var sz = i % 3 === 0 ? 'lg' : i % 2 === 0 ? 'md' : 'sm';
            el.className = 'hero__orb hero__orb--' + sz;
            var sx = Math.random() * 100, sy = Math.random() * 100;
            el.style.left = sx + '%';
            el.style.top = sy + '%';
            el.style.animation = 'orbFloat ' + (4 + Math.random() * 6) + 's ease-in-out infinite ' + (Math.random() * 5) + 's';
            orbContainer.appendChild(el);
            orbs.push({ el: el, baseX: sx, baseY: sy, curX: 0, curY: 0 });
        }
        heroEl.addEventListener('mousemove', function(e) {
            var rect = heroEl.getBoundingClientRect();
            var mxh = e.clientX - rect.left, myh = e.clientY - rect.top;
            orbs.forEach(function(o) {
                var obx = o.baseX / 100 * rect.width, oby = o.baseY / 100 * rect.height;
                var ddx = obx - mxh, ddy = oby - myh;
                var dist = Math.sqrt(ddx * ddx + ddy * ddy);
                var repel = Math.max(0, 1 - dist / 200);
                o.curX = ddx * repel * 0.4;
                o.curY = ddy * repel * 0.4;
            });
        });
    }

    function updateOrbs() {
        if (!heroVisible) { requestAnimationFrame(updateOrbs); return; }
        orbs.forEach(function(o) {
            o.curX *= 0.92; o.curY *= 0.92;
            o.el.style.marginLeft = o.curX + 'px';
            o.el.style.marginTop = o.curY + 'px';
        });
        requestAnimationFrame(updateOrbs);
    }
    updateOrbs();

    /* ─── Render Loop ─── */
    function tick() {
        if (dot) { dot.style.left = mx + 'px'; dot.style.top = my + 'px'; }
        dx += (mx - dx) * 0.12; dy += (my - dy) * 0.12;
        if (ring) { ring.style.left = dx + 'px'; ring.style.top = dy + 'px'; }
        gx += (mx - gx) * 0.04; gy += (my - gy) * 0.04;
        if (glow) { glow.style.left = gx + 'px'; glow.style.top = gy + 'px'; }
        if (heroLight && heroVisible) { heroLight.style.left = gx + 'px'; heroLight.style.top = gy + 'px'; }
        requestAnimationFrame(tick);
    }
    tick();

    /* ─── Cursor Trail ─── */
    var lastEmit = 0;
    document.addEventListener('mousemove', function(e) {
        var now = Date.now();
        if (now - lastEmit < 50) return;
        lastEmit = now;
        var p = document.createElement('div');
        p.style.cssText = 'position:fixed;top:0;left:0;width:4px;height:4px;background:#ea741b;border-radius:50%;pointer-events:none;z-index:9997;transform:translate(-50%,-50%);opacity:0.6;transition:opacity 0.6s ease,transform 0.6s ease;';
        p.style.left = e.clientX + 'px'; p.style.top = e.clientY + 'px';
        document.body.appendChild(p);
        requestAnimationFrame(function() { p.style.opacity = '0'; p.style.transform = 'translate(-50%,-50%) scale(0)'; });
        setTimeout(function() { if (p.parentNode) p.parentNode.removeChild(p); }, 600);
    });

    /* ─── Floating Particles ─── */
    var pc = document.getElementById('heroParticlesWrap');
    if (pc) {
        for (var j = 0; j < 20; j++) {
            var pp = document.createElement('div');
            pp.className = 'hero__particle';
            pp.style.left = Math.random() * 100 + '%';
            pp.style.animationDuration = (8 + Math.random() * 12) + 's';
            pp.style.animationDelay = (Math.random() * 10) + 's';
            pp.style.width = pp.style.height = (1 + Math.random() * 2) + 'px';
            pc.appendChild(pp);
        }
    }

    /* ─── Button Ripple ─── */
    document.querySelectorAll('.btn').forEach(function(btn) {
        btn.addEventListener('pointerdown', function(e) {
            var r = btn.getBoundingClientRect();
            btn.style.setProperty('--ripple-x', ((e.clientX - r.left) / r.width * 100) + '%');
            btn.style.setProperty('--ripple-y', ((e.clientY - r.top) / r.height * 100) + '%');
        });
    });

    /* ─── Scroll Parallax ─── */
    var heroInner = document.querySelector('.hero__inner');
    var scrollLabel = document.getElementById('heroScrollHint');
    var ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(function() {
                var scrollY = window.scrollY;
                var heroH = document.querySelector('.hero').offsetHeight;
                if (scrollY < heroH) {
                    var pct = scrollY / heroH;
                    if (heroInner) { heroInner.style.transform = 'translateY(' + (scrollY * 0.15) + 'px)'; heroInner.style.opacity = 1 - pct * 0.6; }
                    if (scrollLabel) scrollLabel.style.opacity = Math.max(0, 1 - pct * 3);
                }
                ticking = false;
            });
            ticking = true;
        }
    });

    /* ─── Rotating Words ─── */
    var words = document.querySelectorAll('.hero__specialize-word');
    var wi = 0;
    if (words.length) {
        setInterval(function() {
            var n = (wi + 1) % words.length;
            words[wi].classList.add('exit'); words[wi].classList.remove('active');
            words[n].classList.add('active'); words[n].classList.remove('exit');
            wi = n;
        }, 2800);
    }
})();

//  Smooth scroll for nav links
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if(target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  });
});

//  Lazy section loading — reveal sections only when near viewport
const lazySectionObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.classList.add('lazy-section--visible');
      lazySectionObserver.unobserve(e.target);
    }
  });
}, { rootMargin: '200px 0px' });
document.querySelectorAll('.lazy-section').forEach(el => lazySectionObserver.observe(el));
</script>

<!-- ═══════════════════════════════════════════════════════
     COOKIE CONSENT BANNER (GDPR / Privacy)
════════════════════════════════════════════════════════════ -->
<div id="cookieBanner" style="
  position: fixed; bottom: 0; left: 0; right: 0; z-index: 99999;
  background: linear-gradient(135deg, #001630 0%, #0a2d52 100%);
  color: #fff; padding: 20px 32px;
  display: flex; align-items: center; gap: 24px; flex-wrap: wrap;
  box-shadow: 0 -4px 30px rgba(0,0,0,0.35);
  border-top: 2px solid rgba(200,169,110,0.4);
  font-family: 'Outfit', sans-serif; font-size: 0.875rem; line-height: 1.6;
  transform: translateY(100%); transition: transform 0.5s cubic-bezier(0.16,1,0.3,1);
" role="dialog" aria-label="Cookie consent" aria-live="polite">
  <div style="flex:1; min-width:220px;">
    <strong style="color:#c8a96e; font-size:0.95rem;">&#127850; We use cookies</strong><br>
    We use strictly necessary session cookies to keep you logged in and for form security. No tracking or advertising cookies are used.
    <a href="privacy-policy.php" style="color:#c8a96e; text-decoration:underline; margin-left:6px;">Privacy Policy</a>
  </div>
  <div style="display:flex; gap:12px; flex-shrink:0;">
    <button onclick="acceptCookies()" id="cookieAcceptBtn" style="
      background: #c8a96e; color: #001630; border: none; border-radius: 8px;
      padding: 10px 24px; font-family: 'Outfit', sans-serif; font-weight: 700;
      font-size: 0.875rem; cursor: pointer; transition: background .2s;
    ">Accept</button>
    <button onclick="declineCookies()" id="cookieDeclineBtn" style="
      background: transparent; color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.25);
      border-radius: 8px; padding: 10px 20px; font-family: 'Outfit', sans-serif;
      font-size: 0.875rem; cursor: pointer; transition: all .2s;
    ">Decline non-essential</button>
  </div>
</div>

<script>
// Cookie Consent Logic
(function() {
  var consent = localStorage.getItem('cookie_consent');
  if (!consent) {
    setTimeout(function() {
      document.getElementById('cookieBanner').style.transform = 'translateY(0)';
    }, 1200);
  }
})();

function acceptCookies() {
  localStorage.setItem('cookie_consent', 'accepted');
  hideBanner();
}
function declineCookies() {
  localStorage.setItem('cookie_consent', 'declined');
  hideBanner();
}
function hideBanner() {
  var b = document.getElementById('cookieBanner');
  b.style.transform = 'translateY(110%)';
  setTimeout(function() { b.style.display = 'none'; }, 500);
}

/* ═══════════════════════════════════════════════════════
   CUSTOM DROPDOWN — Reusable
   ═══════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.mod-dropdown').forEach(initModDropdown);

  function initModDropdown(root) {
    const trigger  = root.querySelector('.mod-dropdown-trigger');
    const selected = root.querySelector('.mod-dropdown-selected');
    const panel    = root.querySelector('.mod-dropdown-panel');
    const input    = root.querySelector('input[type="hidden"]');
    const options  = root.querySelectorAll('.mod-dropdown-option');

    function open() {
      root.classList.add('is-open');
      /* scroll selected into view */
      const active = root.querySelector('.mod-dropdown-option.is-selected');
      if (active) active.scrollIntoView({ block: 'nearest' });
    }
    function close() { root.classList.remove('is-open'); }
    function toggle() { root.classList.contains('is-open') ? close() : open(); }

    trigger.addEventListener('click', (e) => { e.stopPropagation(); toggle(); });

    options.forEach(opt => {
      opt.addEventListener('click', () => {
        const val = opt.dataset.value;
        const txt = opt.querySelector('span:last-child').textContent;
        input.value = val;
        selected.textContent = txt;
        root.classList.toggle('has-value', val !== '');
        options.forEach(o => o.classList.remove('is-selected'));
        opt.classList.add('is-selected');
        close();
      });
    });

    /* close on outside click */
    document.addEventListener('click', (e) => { if (!root.contains(e.target)) close(); });

    /* keyboard nav */
    root.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') { close(); trigger.focus(); }
    });
  }
});

</script>

<!-- Public Toast -->
<div id="publicToast" style="position:fixed;bottom:32px;left:50%;transform:translateX(-50%) translateY(20px);padding:16px 28px;background:#fff;border-radius:16px;display:flex;align-items:center;gap:12px;box-shadow:0 12px 40px rgba(0,0,0,0.15);opacity:0;transition:all 0.4s cubic-bezier(0.16,1,0.3,1);pointer-events:none;z-index:99999;max-width:90vw;font-family:'Outfit',sans-serif;">
</div>

<script>
(function() {
    var toast = document.getElementById('publicToast');
    var params = new URLSearchParams(window.location.search);
    var successMsg = params.get('success');
    var errorMsg = params.get('error');

    if (!toast || (!successMsg && !errorMsg)) return;

    var msg = successMsg || errorMsg;
    var isOk = !!successMsg;
    var icon = isOk
        ? '<div style="width:36px;height:36px;border-radius:10px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-check" style="color:#10b981;font-size:14px"></i></div>'
        : '<div style="width:36px;height:36px;border-radius:10px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fas fa-exclamation" style="color:#ef4444;font-size:14px"></i></div>';
    var textColor = isOk ? '#065f46' : '#991b1b';

    toast.innerHTML = icon + '<span style="font-size:14px;font-weight:600;color:' + textColor + ';white-space:nowrap">' + msg + '</span><button onclick="this.parentElement.style.opacity=0;this.parentElement.style.pointerEvents=\'none\'" style="margin-left:8px;background:none;border:none;color:#9ca3af;cursor:pointer;font-size:12px;padding:4px"><i class="fas fa-times"></i></button>';
    toast.style.opacity = '1';
    toast.style.pointerEvents = 'auto';
    toast.style.transform = 'translateX(-50%) translateY(0)';

    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.pointerEvents = 'none';
        toast.style.transform = 'translateX(-50%) translateY(20px)';
    }, 5000);

    /* Clean URL */
    params.delete('success');
    params.delete('error');
    var qs = params.toString();
    var newUrl = window.location.pathname + (qs ? '?' + qs : '') + window.location.hash;
    window.history.replaceState({}, '', newUrl);

    /* Auto-scroll to contact form */
    setTimeout(function() {
        var contactSection = document.getElementById('contact') || document.querySelector('form[method="POST"]');
        if (contactSection) contactSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 300);
})();
</script>

<script src="<?= asset('assets/js/main.js') ?>" defer></script>
<script src="<?= asset('assets/js/loader.js') ?>" defer></script>
</body>
</html>
