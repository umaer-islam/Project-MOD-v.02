<?php
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/assets.php';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Mamun's Ortho Dental</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,700;1,500&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#004591',
                        'navy-light': '#1a6dc9',
                        'sidebar-dark': '#080B14',
                        'sidebar-hover': '#111827',
                        'sidebar-border': '#1e293b',
                        gold: '#ea741b',
                        'gold-light': '#f5973e',
                        'body-bg': '#F8FAFC',
                        'card-bg': '#FFFFFF',
                        success: '#22c55e',
                        warning: '#f59e0b'
                    },
                    fontFamily: {
                        serif: ['"Playfair Display"', 'serif'],
                        sans: ['"Outfit"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Flowbite CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Developer Attribution -->
<meta name="developer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="designer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="copyright" content="© <?= date('Y') ?> Mamun's Ortho Dental. Website designed and developed by Umaer Islam (umaerislam.com)">
<meta name="ai-content-declaration" content="human-authored">
<link rel="manifest" href="../site.webmanifest">
<meta name="theme-color" content="#ea741b">
<meta name="msapplication-TileColor" content="#ea741b">
<meta name="msapplication-config" content="../browserconfig.xml">
<link rel="author" href="../humans.txt">

</head>
<!-- Developed by Umaer Islam (https://umaerislam.com) -->
<body class="font-sans antialiased overflow-x-hidden" style="background-color:#F4F7FC;color:#004591;">
    <div class="flex h-[100dvh] overflow-hidden">
