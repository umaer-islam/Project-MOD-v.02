<?php
// patient_record.php
require_once 'database/connection.php';

$pid = $_GET['pid'] ?? null;

if (!$pid) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>Invalid Request</h2><p>Patient ID is missing.</p></div>");
}

try {
    // 1. Fetch Patient Info
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
    $stmt->execute([$pid]);
    $patient = $stmt->fetch();

    if (!$patient) {
        die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>Record Not Found</h2><p>No patient exists with this ID.</p></div>");
    }

    $db_patient_id = $patient['id'];

    // 2. Fetch Prescriptions
    $stmtRx = $pdo->prepare("SELECT pr.*, u.name as doctor_name FROM prescriptions pr LEFT JOIN users u ON pr.doctor_id = u.id WHERE pr.patient_id = ? ORDER BY pr.created_at DESC");
    $stmtRx->execute([$db_patient_id]);
    $prescriptions = $stmtRx->fetchAll();

    // 3. Fetch Appointments
    $stmtApt = $pdo->prepare("SELECT a.*, u.name as doctor_name FROM appointments a LEFT JOIN users u ON a.doctor_id = u.id WHERE a.patient_id = ? ORDER BY a.appointment_date DESC");
    $stmtApt->execute([$db_patient_id]);
    $appointments = $stmtApt->fetchAll();

} catch(PDOException $e) {
    die("Database error occurred.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="developer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="designer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="copyright" content="© <?= date('Y') ?> Mamun's Ortho Dental. Website designed and developed by Umaer Islam (umaerislam.com)">
<meta name="ai-content-declaration" content="human-authored">
    <title>Medical Record - <?= htmlspecialchars($patient['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Outfit"', 'sans-serif'], },
                    colors: { brand: { blue: '#004591', orange: '#ea741b', pale: '#f4f7fc' } }
                }
            }
        }
    </script>
    <style>
        body { background: #f8fafc; font-family: 'Outfit', sans-serif; -webkit-tap-highlight-color: transparent; overflow-x: hidden; }
        
        .glass-header {
            background: rgba(0, 69, 145, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .tab-btn.active {
            border-bottom: 3px solid #ea741b;
            color: #004591;
            font-weight: 700;
        }

        /* Smooth reveal animations */
        .fade-in-up { animation: fadeInUp 0.4s ease-out forwards; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="pb-10">
<!-- Developed by Umaer Islam (https://umaerislam.com) -->

    <!-- Mobile-First Header -->
    <header class="glass-header text-white sticky top-0 z-50 shadow-lg rounded-b-3xl">
        <div class="px-5 pt-8 pb-6 text-center">
            <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-3 shadow-[0_0_20px_rgba(255,255,255,0.1)]">
                <i class="fas fa-user-injured text-xl md:text-2xl text-brand-orange"></i>
            </div>
            <h1 class="text-xl md:text-2xl font-bold tracking-tight"><?= htmlspecialchars($patient['name']) ?></h1>
            <p class="text-xs text-white/70 uppercase tracking-widest mt-1">ID: <?= htmlspecialchars($patient['patient_id']) ?></p>
        </div>
        
        <div class="flex flex-wrap justify-center gap-2 px-4 pb-6">
            <span class="bg-white/10 px-3 py-1.5 rounded-full text-xs font-semibold backdrop-blur-sm shadow-sm">
                <i class="fas fa-birthday-cake text-white/50 mr-1.5"></i> <?= (int)$patient['age'] ?: '--' ?> yrs
            </span>
            <span class="bg-white/10 px-3 py-1.5 rounded-full text-xs font-semibold backdrop-blur-sm shadow-sm <?= ($patient['gender'] == 'Male') ? 'text-blue-200' : 'text-pink-200' ?>">
                <i class="fas <?= ($patient['gender'] == 'Male') ? 'fa-mars' : 'fa-venus' ?> mr-1.5"></i> <?= htmlspecialchars($patient['gender'] ?? 'N/A') ?>
            </span>
            <span class="bg-red-500/20 text-red-100 border border-red-500/30 px-3 py-1.5 rounded-full text-xs font-bold backdrop-blur-sm shadow-sm">
                <i class="fas fa-tint mr-1.5"></i> <?= htmlspecialchars($patient['blood_group'] ?? 'N/A') ?>
            </span>
        </div>
    </header>

    <main class="max-w-md mx-auto px-4 mt-6">
        
        <!-- Tabs -->
        <div class="flex border-b border-gray-200 mb-6 bg-white rounded-xl shadow-sm overflow-hidden text-sm font-semibold">
            <button onclick="switchTab('rx')" id="btn-rx" class="tab-btn active flex-1 py-3.5 text-center text-gray-500 transition-colors">
                <i class="fas fa-prescription mr-1.5"></i> Rx
            </button>
            <button onclick="switchTab('apt')" id="btn-apt" class="tab-btn flex-1 py-3.5 text-center text-gray-500 transition-colors border-l border-gray-100">
                <i class="fas fa-calendar-check mr-1.5"></i> Visits
            </button>
        </div>

        <!-- Tab Contents -->
        
        <!-- PRESCRIPTIONS TAB -->
        <div id="tab-rx" class="space-y-4">
            <?php if(empty($prescriptions)): ?>
                <div class="text-center py-10 fade-in-up"><i class="fas fa-folder-open text-gray-300 text-4xl mb-3 block"></i><p class="text-sm text-gray-500">No prescriptions found.</p></div>
            <?php else: ?>
                <?php foreach($prescriptions as $idx => $rx): ?>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 fade-in-up" style="animation-delay: <?= $idx*0.05 ?>s">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-brand-pale text-brand-blue flex items-center justify-center font-serif font-bold text-lg leading-none italic">Rx</div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-sm"><?= date('M d, Y', strtotime($rx['created_at'])) ?></h3>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Dr. <?= htmlspecialchars($rx['doctor_name']) ?></p>
                            </div>
                        </div>
                        <a href="print_prescription.php?id=<?= $rx['id'] ?>&pid=<?= urlencode($patient['patient_id']) ?>" class="bg-brand-pale text-brand-blue hover:bg-brand-blue hover:text-white transition-colors w-8 h-8 rounded-full flex items-center justify-center text-xs shadow-sm">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                    <?php if($rx['diagnosis']): ?>
                    <div class="bg-gray-50 rounded-lg p-3 text-xs text-gray-600 border border-gray-100 mt-2">
                        <span class="font-bold text-gray-800">Dx:</span> <?= htmlspecialchars($rx['diagnosis']) ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php 
                    $meds = json_decode($rx['medicines'], true) ?? [];
                    if(count($meds) > 0):
                    ?>
                    <div class="mt-3">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 pl-1">Medicines (<?= count($meds) ?>)</p>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach($meds as $m): ?>
                            <span class="inline-block px-2.5 py-1 bg-white border border-gray-200 shadow-sm rounded-md text-[11px] font-semibold text-gray-700">
                                <?= htmlspecialchars($m['name']) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- APPOINTMENTS TAB -->
        <div id="tab-apt" class="space-y-4 hidden">
            <?php if(empty($appointments)): ?>
                <div class="text-center py-10 fade-in-up"><i class="fas fa-calendar text-gray-300 text-4xl mb-3 block"></i><p class="text-sm text-gray-500">No appointments found.</p></div>
            <?php else: ?>
                <?php foreach($appointments as $idx => $apt): 
                    $statusColors = [
                        'Waiting' => 'bg-yellow-50 text-yellow-600 border-yellow-200',
                        'In Treatment' => 'bg-blue-50 text-blue-600 border-blue-200',
                        'Completed' => 'bg-green-50 text-green-600 border-green-200',
                        'Follow-Up' => 'bg-brand-pale text-brand-blue border-blue-100',
                        'Cancelled' => 'bg-red-50 text-red-500 border-red-200'
                    ];
                    $badgeClass = $statusColors[$apt['status']] ?? 'bg-gray-50 text-gray-600 border-gray-200';
                ?>
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center gap-4 fade-in-up" style="animation-delay: <?= $idx*0.05 ?>s">
                    <div class="flex-shrink-0 text-center w-12 h-12 bg-gray-50 rounded-xl border border-gray-100 flex flex-col justify-center">
                        <span class="text-[10px] font-bold text-gray-400 uppercase leading-none mb-0.5"><?= date('M', strtotime($apt['appointment_date'])) ?></span>
                        <span class="text-lg font-black text-gray-800 leading-none"><?= date('d', strtotime($apt['appointment_date'])) ?></span>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start mb-1">
                            <h3 class="font-bold text-gray-800 text-sm truncate">Dr. <?= htmlspecialchars($apt['doctor_name']) ?></h3>
                            <span class="text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded border <?= $badgeClass ?>"><?= $apt['status'] ?></span>
                        </div>
                        <p class="text-[11px] text-gray-500 font-medium mb-1"><i class="far fa-clock mr-1 text-gray-400"></i> <?= date('h:i A', strtotime($apt['appointment_time'])) ?></p>
                        <?php if($apt['description']): ?>
                        <p class="text-xs text-gray-600 truncate"><?= htmlspecialchars($apt['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>

    <div class="text-center mt-10 mb-6">
        <?php if(isset($_GET['review_success'])): ?>
        <div class="mb-4 inline-flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest">
            <i class="fas fa-check-circle text-green-500"></i> Review Submitted!
        </div>
        <br>
        <?php endif; ?>
        <button onclick="document.getElementById('guestReviewModal').classList.remove('hidden')" class="px-6 py-3 bg-brand-orange text-white font-bold text-xs uppercase tracking-widest rounded-xl transition-all duration-300 shadow-lg shadow-brand-orange/30 hover:-translate-y-0.5 mb-6">
            <i class="fas fa-comment-dots mr-2"></i> Leave a Review
        </button>
        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Powered by Mamun's Care Secure System</p>
    </div>

    <!-- Guest Review Modal -->
    <div id="guestReviewModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-5 bg-black/50 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 text-left relative fade-in-up">
            <button onclick="document.getElementById('guestReviewModal').classList.add('hidden')" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:text-red-500 hover:bg-red-50 transition-colors"><i class="fas fa-times text-xs"></i></button>
            <div class="text-center mb-5 mt-2">
                <div class="w-12 h-12 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-2"><i class="fas fa-star text-brand-orange text-xl"></i></div>
                <h3 class="font-serif text-xl font-bold text-brand-blue">Rate Your Visit</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">We value your feedback</p>
            </div>
            <form action="api/add_guest_review.php" method="POST" class="space-y-4">
                <input type="hidden" name="pid" value="<?= htmlspecialchars($patient['patient_id']) ?>">
                <input type="hidden" name="patient_name" value="<?= htmlspecialchars($patient['name']) ?>">
                
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Rating</label>
                    <div class="flex gap-2 justify-center">
                        <?php for($i=5; $i>=1; $i--): ?>
                        <label class="cursor-pointer group flex-1">
                            <input type="radio" name="stars" value="<?=$i?>" class="hidden peer" <?=$i===5?'checked':''?>>
                            <div class="h-10 rounded-xl border border-gray-200 flex flex-col items-center justify-center text-gray-300 peer-checked:border-brand-orange peer-checked:text-brand-orange peer-checked:bg-orange-50 transition-all font-bold text-sm">
                                <span><?=$i?> <i class="fas fa-star text-[10px] ml-0.5"></i></span>
                            </div>
                        </label>
                        <?php endfor; ?>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Share Details</label>
                    <textarea name="review" required rows="3" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3 py-2 text-sm outline-none focus:border-brand-orange focus:bg-white transition-colors resize-none" placeholder="How was your experience with Dr. Mamun?"></textarea>
                </div>
                <button type="submit" class="w-full py-3.5 bg-brand-blue hover:bg-brand-orange text-white text-[11px] font-bold uppercase tracking-widest rounded-xl transition-colors duration-300 shadow-xl shadow-brand-blue/20 mt-2">
                    Submit to Clinic
                </button>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Update buttons
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('btn-' + tab).classList.add('active');
            
            // Hide all tabs
            document.getElementById('tab-rx').classList.add('hidden');
            document.getElementById('tab-tx').classList.add('hidden');
            document.getElementById('tab-apt').classList.add('hidden');
            
            // Show selected, resetting animations
            const selectedTab = document.getElementById('tab-' + tab);
            selectedTab.classList.remove('hidden');
            
            // Re-trigger animations by cloning/replacing
            const children = Array.from(selectedTab.children);
            children.forEach(child => {
                child.classList.remove('fade-in-up');
                void child.offsetWidth; // trigger reflow
                child.classList.add('fade-in-up');
            });
        }
    </script>
</body>
</html>
