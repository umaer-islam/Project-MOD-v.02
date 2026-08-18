<?php
session_start();
require_once 'database/connection.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo "Prescription not found."; exit; }

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
            $clinical = json_decode($pr['clinical_notes'] ?? '{}', true) ?? [];
        } else {
            echo "Prescription record not found.";
            exit;
        }
    } catch (PDOException $e) {
        echo "Database error.";
        exit;
    }
} else {
    echo "Database connection failed.";
    exit;
}

$follow_up_display = !empty($pr['follow_up']) ? $pr['follow_up'] : '';
$rx_date_display = !empty($pr['rx_date']) ? date('d/m/Y', strtotime($pr['rx_date'])) : date('d/m/Y', strtotime($pr['created_at']));

$templatePath = 'prescription.svg';

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$baseUrl = $protocol . $_SERVER['HTTP_HOST'];
$patientRecordUrl = $baseUrl . '/patient_record.php?pid=' . urlencode($pr['p_id']);
$qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&color=004591&data=' . urlencode($patientRecordUrl);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription - <?= htmlspecialchars($pr['patient_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

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
            gap: 8px;
            background: #004591;
            color: #fff;
            font-family: sans-serif;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,.15);
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-action:hover { background: #ea741b; transform: translateY(-1px); }
        .btn-close { background: #fff; color: #475569; }
        .btn-close:hover { background: #f8fafc; }

        .rx-page {
            position: relative;
            width: 612pt;
            height: 792pt;
        }
        .rx-page img.rx-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: block;
        }

        .rx-overlay {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        .c-name { position: absolute; left: 78pt; top: 142pt; width: 150pt; height: 20pt; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 11pt; line-height: 20pt; color: #000; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        .c-age { position: absolute; left: 270pt; top: 142pt; width: 55pt; height: 20pt; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 11pt; line-height: 20pt; color: #000; overflow: hidden; }
        .c-date { position: absolute; left: 385pt; top: 142pt; width: 80pt; height: 20pt; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 11pt; line-height: 20pt; color: #000; overflow: hidden; }

        .c-rx-section { position: absolute; left: 195pt; top: 195pt; width: 380pt; height: 530pt; overflow: hidden; color: #000; }
        .c-med-entry { margin-bottom: 11pt; line-height: 1.4; }
        .c-med-name { font-family: 'Poppins', sans-serif; font-size: 12pt; font-weight: 700; word-wrap: break-word; }
        .c-med-freq { font-family: 'Poppins', sans-serif; font-size: 11pt; font-weight: 400; color: #334155; }
        .c-med-inst { font-family: 'Poppins', sans-serif; font-size: 10pt; font-style: italic; color: #475569; }

        .c-clinical-box { position: absolute; left: 10pt; width: 162pt; font-family: 'Poppins', sans-serif; color: #000; overflow: hidden; text-align: left; }
        .c-clinical-box strong { display: block; font-size: 8pt; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #1e293b; border-bottom: .5pt solid #ea741b; padding-bottom: 1pt; margin-bottom: 2pt; }
        .c-clinical-box span { font-size: 8.5pt; line-height: 11pt; font-weight: 400; }

        .c-complain { top: 185pt; height: 55pt; }
        .c-exam { top: 248pt; height: 55pt; }
        .c-history { top: 311pt; height: 55pt; }
        .c-investigations { top: 374pt; height: 55pt; }
        .c-diagnosis { top: 437pt; height: 55pt; }

        .c-advice { position: absolute; left: 10pt; top: 500pt; width: 162pt; height: 75pt; font-family: 'Poppins', sans-serif; color: #000; overflow: hidden; text-align: left; }
        .c-advice strong { display: block; font-size: 8pt; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #1e293b; border-bottom: .5pt solid #cbd5e1; padding-bottom: 1pt; margin-bottom: 2pt; }
        .c-advice span { font-size: 8.5pt; line-height: 11pt; font-weight: 400; white-space: pre-wrap; }

        .c-followup { position: absolute; left: 10pt; top: 583pt; width: 162pt; height: 28pt; font-family: 'Poppins', sans-serif; font-size: 9pt; font-weight: 700; color: #000; overflow: hidden; text-align: left; }

        .c-signature { position: absolute; right: 80pt; bottom: 80pt; width: 120pt; text-align: center; }
        .c-signature img { width: 100%; height: 40pt; object-fit: contain; display: block; }
        .c-signature-label { font-family: 'Poppins', sans-serif; font-size: 7pt; color: #64748b; margin-top: 1pt; }

        .c-qr { position: absolute; right: 30pt; bottom: 60pt; width: 50pt; height: 50pt; }
        .c-qr img { width: 100%; height: 100%; display: block; }

        @media print {
            body { background: none; margin: 0; padding: 0; }
            .no-print-bar { display: none !important; }
            .rx-page { width: 612pt; height: 792pt; page-break-after: always; }
            @page { size: letter portrait; margin: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button onclick="window.close()" class="btn-action btn-close">Close</button>
        <button onclick="window.print()" class="btn-action"><i class="fas fa-print"></i> Print</button>
    </div>

    <div class="rx-page">
        <img class="rx-bg" src="<?= htmlspecialchars($templatePath) ?>" alt="">

        <div class="rx-overlay">
            <div class="c-name"><?= htmlspecialchars($pr['patient_name']) ?></div>
            <div class="c-age"><?= !empty($pr['age']) ? (int)$pr['age'] . ' Yrs' : '--' ?></div>
            <div class="c-date"><?= $rx_date_display ?></div>

            <div class="c-rx-section">
                <?php foreach ($medicines as $m): ?>
                    <div class="c-med-entry">
                        <div class="c-med-name"><?= htmlspecialchars($m['name']) ?></div>
                        <div class="c-med-freq">
                            <?= htmlspecialchars(str_replace('+', ' + ', $m['frequency'])) ?><?= !empty($m['duration']) ? ' | ' . htmlspecialchars($m['duration']) : '' ?>
                        </div>
                        <?php if (!empty($m['note'])): ?>
                            <div class="c-med-inst"><?= htmlspecialchars($m['note']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="c-clinical-box c-complain">
                <?php if (!empty($clinical['complain'])): ?>
                    <strong>Complain</strong><span><?= nl2br(htmlspecialchars($clinical['complain'])) ?></span>
                <?php endif; ?>
            </div>
            <div class="c-clinical-box c-exam">
                <?php if (!empty($clinical['on_examination'])): ?>
                    <strong>On Examination</strong><span><?= nl2br(htmlspecialchars($clinical['on_examination'])) ?></span>
                <?php endif; ?>
            </div>
            <div class="c-clinical-box c-history">
                <?php if (!empty($clinical['medical_history'])): ?>
                    <strong>M/H</strong><span><?= nl2br(htmlspecialchars($clinical['medical_history'])) ?></span>
                <?php endif; ?>
            </div>
            <div class="c-clinical-box c-investigations">
                <?php if (!empty($pr['investigations'])): ?>
                    <strong>Investigations</strong><span><?= nl2br(htmlspecialchars($pr['investigations'])) ?></span>
                <?php endif; ?>
            </div>
            <div class="c-clinical-box c-diagnosis">
                <?php if (!empty($pr['diagnosis'])): ?>
                    <strong>W/D</strong><span><?= nl2br(htmlspecialchars($pr['diagnosis'])) ?></span>
                <?php endif; ?>
            </div>

            <div class="c-advice">
                <?php if (!empty($pr['advice'])): ?>
                    <strong>Advice</strong><span><?= nl2br(htmlspecialchars($pr['advice'])) ?></span>
                <?php endif; ?>
            </div>

            <div class="c-followup">
                <?php if (!empty($follow_up_display)): ?>
                    <strong><?= htmlspecialchars($follow_up_display) ?></strong>
                <?php endif; ?>
            </div>

            <?php if(!empty($pr['signature_path'])): ?>
            <div class="c-signature">
                <img src="<?= htmlspecialchars($pr['signature_path']) ?>" alt="Doctor Signature">
                <div class="c-signature-label">Dr. <?= htmlspecialchars($pr['doctor_name']) ?></div>
            </div>
            <?php endif; ?>

            <div class="c-qr">
                <img src="<?= $qrCodeUrl ?>" alt="QR" loading="eager">
            </div>
        </div>
    </div>

</body>
</html>
