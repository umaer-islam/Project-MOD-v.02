<?php
session_start();
require_once 'database/connection.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo "Prescription not found.";
    exit;
}

$pr = null;
$medicines = [];

if ($pdo !== null) {
    try {
        $stmt = $pdo->prepare("
            SELECT pr.*, 
                   p.name as patient_name, p.patient_id as p_id, p.age, p.phone,
                   d.name as doctor_name, d.degrees as doctor_degrees, d.rx_template_path, d.signature_path 
            FROM prescriptions pr 
            JOIN patients p ON pr.patient_id = p.id 
            LEFT JOIN users d ON pr.doctor_id = d.id 
            WHERE pr.id = ?
        ");
        $stmt->execute([$id]);
        $pr = $stmt->fetch();
        
        if ($pr) {
            $medicines = json_decode($pr['medicines'], true) ?? [];
        } else {
            echo "Prescription record not found.";
            exit;
        }
    } catch (PDOException $e) {
        echo "Database error: " . htmlspecialchars($e->getMessage());
        exit;
    }
} else {
    echo "Database connection failed.";
    exit;
}

// Check if follow_up or rx_date columns are empty or missing in query results
$follow_up_display = !empty($pr['follow_up']) ? $pr['follow_up'] : '';
$rx_date_display = !empty($pr['rx_date']) ? date('d/m/Y', strtotime($pr['rx_date'])) : date('d/m/Y', strtotime($pr['created_at']));

// Generate patient-specific QR code URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$baseUrl = $protocol . $_SERVER['HTTP_HOST'];
$patientRecordUrl = $baseUrl . '/patient_record.php?pid=' . urlencode($pr['p_id']);
$qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&color=004591&data=' . urlencode($patientRecordUrl);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Prescription - <?= htmlspecialchars($pr['patient_name']) ?></title>
    <!-- Poppins Font Loader -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS reset & baseline configurations */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Screen Preview Container ── */
        .no-print-bar {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 12px;
            z-index: 100;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #004591;
            color: #ffffff;
            font-family: sans-serif;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-action:hover {
            background: #ea741b;
            transform: translateY(-1px);
        }
        .btn-close-action {
            background: #ffffff;
            color: #475569;
        }
        .btn-close-action:hover {
            background: #f8fafc;
        }

        /* ── US Letter Canvas (612pt x 792pt) ── */
        .prescription-container {
            width: 612pt;
            height: 792pt;
            background-color: #ffffff;
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            border-radius: 4px;
            /* Default background rendering during browser preview */
            background-image: url('<?= !empty($pr['rx_template_path']) ? htmlspecialchars($pr['rx_template_path']) : '' ?>');
        }

        /* Patient Name: X=88, Y=140, W=190, H=22 */
        .c-name {
            position: absolute;
            left: 88pt;
            top: 140pt;
            width: 190pt;
            height: 22pt;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 11pt;
            line-height: 22pt;
            color: #000000;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        /* Age: X=330, Y=140, W=45, H=22 */
        .c-age {
            position: absolute;
            left: 330pt;
            top: 140pt;
            width: 45pt;
            height: 22pt;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 11pt;
            line-height: 22pt;
            color: #000000;
            overflow: hidden;
        }
        /* Date: X=500, Y=140, W=70, H=22 */
        .c-date {
            position: absolute;
            left: 500pt;
            top: 140pt;
            width: 70pt;
            height: 22pt;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 11pt;
            line-height: 22pt;
            color: #000000;
            overflow: hidden;
        }
        /* Rx Writing Area: X=265, Y=240, W=320, H=430 */
        .c-rx-section {
            position: absolute;
            left: 265pt;
            top: 240pt;
            width: 320pt;
            height: 430pt;
            overflow: hidden;
            color: #000000;
        }
        .c-med-entry {
            margin-bottom: 11pt;
            line-height: 1.4;
        }
        /* Medicine Name: 12pt Bold */
        .c-med-name {
            font-family: 'Poppins', sans-serif;
            font-size: 12pt;
            font-weight: 700;
            word-wrap: break-word;
        }
        /* Dosage/Frequency: 11pt */
        .c-med-freq {
            font-family: 'Poppins', sans-serif;
            font-size: 11pt;
            font-weight: 400;
            color: #334155;
        }
        /* Instruction: 10pt Italic */
        .c-med-inst {
            font-family: 'Poppins', sans-serif;
            font-size: 10pt;
            font-style: italic;
            color: #475569;
        }
        /* Chief Complaint / Diagnosis: X=20, Y=195, W=142, H=100 */
        .c-diagnosis {
            position: absolute;
            left: 20pt;
            top: 195pt;
            width: 142pt;
            height: 100pt;
            font-family: 'Poppins', sans-serif;
            color: #000000;
            overflow: hidden;
            text-align: left;
        }
        .c-diagnosis strong {
            display: block;
            font-size: 9.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #1e293b;
            margin-bottom: 2pt;
        }
        .c-diagnosis span {
            font-size: 9.5pt;
            line-height: 13pt;
            font-weight: 400;
        }
 
        /* Investigations: X=20, Y=305, W=142, H=100 */
        .c-investigations {
            position: absolute;
            left: 20pt;
            top: 305pt;
            width: 142pt;
            height: 100pt;
            font-family: 'Poppins', sans-serif;
            color: #000000;
            overflow: hidden;
            text-align: left;
        }
        .c-investigations strong {
            display: block;
            font-size: 9.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #1e293b;
            margin-bottom: 2pt;
        }
        .c-investigations span {
            font-size: 9.5pt;
            line-height: 13pt;
            font-weight: 400;
        }
 
        /* Advice: X=20, Y=415, W=142, H=150 */
        .c-advice {
            position: absolute;
            left: 20pt;
            top: 415pt;
            width: 142pt;
            height: 150pt;
            font-family: 'Poppins', sans-serif;
            color: #000000;
            overflow: hidden;
            text-align: left;
        }
        .c-advice strong {
            display: block;
            font-size: 9.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #1e293b;
            margin-bottom: 2pt;
        }
        .c-advice span {
            font-size: 9.5pt;
            line-height: 13pt;
            font-weight: 400;
            white-space: pre-wrap;
        }
 
        /* Follow-up: X=20, Y=575, W=142, H=30 */
        .c-followup {
            position: absolute;
            left: 20pt;
            top: 575pt;
            width: 142pt;
            height: 30pt;
            font-family: 'Poppins', sans-serif;
            font-size: 9.5pt;
            font-weight: 700;
            color: #000000;
            overflow: hidden;
            text-align: left;
        }

        /* QR Code: bottom-right corner */
        .c-qr {
            position: absolute;
            right: 20pt;
            bottom: 20pt;
            width: 50pt;
            height: 50pt;
        }
        .c-qr img {
            width: 100%;
            height: 100%;
            display: block;
        }


        /* ── PRINT MEDIA CONFIGURATION ── */
        @media print {
            body {
                background: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .prescription-container {
                box-shadow: none !important;
                margin: 0 !important;
                border: none !important;
                border-radius: 0 !important;
                background-image: none !important; /* CRITICAL: Never print background template! */
                background-color: transparent !important;
                width: 612pt !important;
                height: 792pt !important;
                page-break-after: always;
            }
            @page {
                size: letter portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Print / Close Action bar on top for preview modes -->
    <div class="no-print-bar">
        <button onclick="window.close()" class="btn-action btn-close-action">Close Preview</button>
        <button onclick="window.print()" class="btn-action"><i class="fas fa-print"></i> Print Prescription</button>
    </div>

    <!-- Master US Letter Page Container -->
    <div class="prescription-container">
        
        <!-- absolute positioned data matching coordinate targets -->
        <div class="c-name"><?= htmlspecialchars($pr['patient_name']) ?></div>
        <div class="c-age"><?= !empty($pr['age']) ? (int)$pr['age'] . ' Yrs' : '--' ?></div>
        <div class="c-date"><?= $rx_date_display ?></div>
        
        <!-- medicines list section -->
        <div class="c-rx-section">
            <?php foreach ($medicines as $m): ?>
                <div class="c-med-entry">
                    <div class="c-med-name"><?= htmlspecialchars($m['name']) ?></div>
                    <div class="c-med-freq">
                        <?= htmlspecialchars($m['frequency']) ?><?= !empty($m['duration']) ? ' &times; ' . htmlspecialchars($m['duration']) : '' ?>
                    </div>
                    <?php if (!empty($m['note'])): ?>
                        <div class="c-med-inst"><?= htmlspecialchars($m['note']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- diagnosis and complaints -->
        <div class="c-diagnosis">
            <?php if (!empty($pr['diagnosis'])): ?>
                <strong>Diagnosis</strong><span><?= nl2br(htmlspecialchars($pr['diagnosis'])) ?></span>
            <?php endif; ?>
        </div>

        <!-- investigations area -->
        <div class="c-investigations">
            <?php if (!empty($pr['investigations'])): ?>
                <strong>Investigations</strong><span><?= nl2br(htmlspecialchars($pr['investigations'])) ?></span>
            <?php endif; ?>
        </div>
        
        <!-- clinical advice -->
        <div class="c-advice">
            <?php if (!empty($pr['advice'])): ?>
                <strong>Advice / Notes</strong><span><?= nl2br(htmlspecialchars($pr['advice'])) ?></span>
            <?php endif; ?>
        </div>
        
        <!-- follow-up details -->
        <div class="c-followup">
            <?php if (!empty($follow_up_display)): ?>
                <strong><?= htmlspecialchars($follow_up_display) ?></strong>
            <?php endif; ?>
        </div>

        <!-- patient-specific QR code -->
        <div class="c-qr">
            <img src="<?= $qrCodeUrl ?>" alt="Patient QR Code" loading="eager">
        </div>


    </div>

    <!-- Automatically trigger browser printing dialog in letter size -->
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print();
            }, 800);
        });
    </script>
</body>
</html>
