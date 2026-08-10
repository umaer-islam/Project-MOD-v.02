<?php
require_once 'components/header.php';
restrict_access(['admin', 'doctor']);
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
require_once 'database/connection.php';

// Fetch current doctor settings
$doctor_id = $_SESSION['user_id'] ?? 0;
$doctor_name = '';
$doctor_degrees = '';
$rx_template = '';
$sig_image = '';

if ($pdo !== null) {
    // Schema migration checks
    try {
        $pdo->query("SELECT rx_template_path, signature_path FROM users LIMIT 1");
    } catch (PDOException $e) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN rx_template_path VARCHAR(255) DEFAULT NULL");
            $pdo->exec("ALTER TABLE users ADD COLUMN signature_path VARCHAR(255) DEFAULT NULL");
        } catch (Exception $ex) {}
    }

    try {
        $stmt = $pdo->prepare("SELECT name, degrees, rx_template_path, signature_path FROM users WHERE id = ?");
        $stmt->execute([$doctor_id]);
        $doc = $stmt->fetch();
        if ($doc) {
            $doctor_name = $doc['name'];
            $doctor_degrees = $doc['degrees'] ?? 'BDS';
            $rx_template = $doc['rx_template_path'] ?? '';
            $sig_image = $doc['signature_path'] ?? '';
        }
    } catch (Exception $e) {}
}
?>

<!-- poppins font loader -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<!-- load Bootstrap 5 via CSS link dynamically -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    /* Styling scopes to prevent Bootstrap from breaking internal layouts */
    #rxSystemScope {
        font-family: 'Outfit', 'Poppins', sans-serif;
        color: #004591;
    }
    #rxSystemScope .card-premium {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(0, 69, 145, 0.05);
        box-shadow: 0 4px 25px rgba(0, 69, 145, 0.04);
        padding: 24px;
        margin-bottom: 24px;
    }
    #rxSystemScope label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #7c7c7c;
        margin-bottom: 8px;
    }
    #rxSystemScope input, #rxSystemScope textarea, #rxSystemScope select {
        background: #F4F7FC;
        border: 1.5px solid transparent;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        color: #004591;
        outline: none;
        transition: all 0.3s ease;
    }
    #rxSystemScope input:focus, #rxSystemScope textarea:focus, #rxSystemScope select:focus {
        background: #ffffff;
        border-color: #ea741b;
        box-shadow: 0 0 0 3px rgba(234, 116, 27, 0.12);
    }
    #rxSystemScope .btn-navy {
        background: #004591;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        padding: 14px 28px;
        border-radius: 12px;
        border: none;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 69, 145, 0.2);
    }
    #rxSystemScope .btn-navy:hover {
        background: #ea741b;
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(234, 116, 27, 0.3);
    }
    #rxSystemScope .btn-orange-outline {
        background: transparent;
        color: #ea741b;
        border: 2px solid #ea741b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        padding: 12px 24px;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    #rxSystemScope .btn-orange-outline:hover {
        background: #ea741b;
        color: #ffffff;
    }
    #rxSystemScope .med-row {
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #edf2f7;
        padding: 16px;
        position: relative;
        margin-bottom: 12px;
    }
    #rxSystemScope .chk-pill {
        cursor: pointer;
        display: inline-block;
        padding: 6px 12px;
        border: 1.5px solid #E2E8F0;
        border-radius: 8px;
        background: #ffffff;
        font-size: 11px;
        font-weight: 600;
        color: #7c7c7c;
        transition: all 0.2s ease;
        user-select: none;
    }
    #rxSystemScope .chk-pill input {
        display: none;
    }
    #rxSystemScope .chk-pill:hover {
        border-color: #ea741b;
    }
    #rxSystemScope .chk-pill.active {
        background: #e8f0fa;
        border-color: #004591;
        color: #004591;
    }

    /* ── US Letter Preview Canvas (612pt x 792pt) ── */
    .preview-container {
        overflow-x: auto;
        padding: 10px;
        background: #cbd5e1;
        border-radius: 16px;
        border: 1px solid #94a3b8;
    }
    .letter-page {
        width: 612pt;
        height: 792pt;
        background-color: #ffffff;
        background-size: 100% 100%;
        background-position: center;
        background-repeat: no-repeat;
        position: relative;
        box-shadow: 0 10px 30px rgba(0,0,0,0.18);
        border-radius: 4px;
        margin: 0 auto;
        transform-origin: top center;
    }

    /* ── Coordinate Elements — aligned to actual PDF printable zones ── */

    /* Patient Name: */
    .c-name {
        position: absolute;
        left: 58pt;
        top: 132pt;
        width: 190pt;
        height: 22pt;
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 12pt;
        line-height: 22pt;
        color: #000000;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    /* Age: */
    .c-age {
        position: absolute;
        left: 330pt;
        top: 132pt;
        width: 45pt;
        height: 22pt;
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 13pt;
        line-height: 22pt;
        color: #000000;
        overflow: hidden;
    }
    /* Date: */
    .c-date {
        position: absolute;
        left: 470pt;
        top: 132pt;
        width: 70pt;
        height: 22pt;
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        font-size: 12pt;
        line-height: 22pt;
        color: #000000;
        overflow: hidden;
    }
    /* Rx Writing Area: */
    .c-rx-section {
        position: absolute;
        left: 200pt;
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
    /* Chief Complaint / Diagnosis: */
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

    /* Investigations: */
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

    /* Advice: */
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

    /* Follow-up: */
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

</style>

<main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto" id="rxSystemScope">
    
    <div class="row align-items-center mb-4">
        <div class="col-12 col-md-6">
            <p class="text-uppercase tracking-widest text-warning font-weight-bold mb-1" style="font-size:10px; letter-spacing: 0.25em;">PRESCRIPTION SYSTEM V2</p>
            <h1 class="h2 text-primary font-serif font-weight-bold m-0">Dynamic Prescription Builder</h1>
        </div>
        <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
            <span class="badge bg-secondary me-2 py-2 px-3 text-uppercase font-weight-bold" id="autosaveIndicator" style="font-size:10px;">Autosave Active</span>
            <button class="btn btn-outline-primary py-2 px-3 rounded-pill text-uppercase font-weight-bold" style="font-size:10px;" data-bs-toggle="modal" data-bs-target="#settingsModal">
                <i class="fas fa-image me-1"></i> Rx Background Template
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Input Form (Left) -->
        <div class="col-12 col-xl-6">
            <form id="rxSystemForm" action="api/save_prescription.php" method="POST" class="needs-validation" novalidate>
                
                <!-- Patient Block -->
                <div class="card-premium">
                    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-2">
                        <h4 class="h5 m-0 text-primary font-weight-bold"><i class="fas fa-user-injured me-2 text-warning"></i> Patient Registry</h4>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-12 col-md-8 position-relative">
                            <label for="pName">Patient Name *</label>
                            <input type="text" class="form-control" id="pName" name="patient_name" required placeholder="Type or search patient name..." autocomplete="off">
                            <input type="hidden" id="pId" name="patient_id" value="">
                            <div id="patientAjaxDropdown" class="absolute z-10 w-100 bg-white border rounded-3 shadow-lg mt-1 d-none" style="max-height:220px; overflow-y:auto;"></div>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="pAge">Age (Yrs)</label>
                            <input type="number" class="form-control" id="pAge" name="age" min="1" max="120" placeholder="e.g. 35">
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="mod-calendar" id="pDate" data-placeholder="Select date">
                                <input type="hidden" name="rx_date" value="<?= date('Y-m-d') ?>" required>
                                <div class="mod-calendar-trigger">
                                    <span class="mod-calendar-label">Prescription Date *</span>
                                    <div class="mod-calendar-value">
                                        <i class="fas fa-calendar-day mod-calendar-icon text-sm"></i>
                                        <span class="mod-calendar-text"></span>
                                        <span class="mod-calendar-clear"><i class="fas fa-times text-[8px]"></i></span>
                                    </div>
                                </div>
                                <div class="mod-calendar-panel">
                                    <div class="cal-header">
                                        <button type="button" class="cal-header-btn cal-prev"><i class="fas fa-chevron-left"></i></button>
                                        <div class="cal-month-year"></div>
                                        <button type="button" class="cal-today-btn">Today</button>
                                        <button type="button" class="cal-header-btn cal-next"><i class="fas fa-chevron-right"></i></button>
                                    </div>
                                    <div class="cal-weekdays"><span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span></div>
                                    <div class="cal-days"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="pPhone">Contact Phone</label>
                            <input type="text" class="form-control" id="pPhone" name="phone" placeholder="01XXXXXXXXX">
                        </div>
                    </div>
                </div>

                <!-- Diagnosis Block -->
                <div class="card-premium">
                    <div class="d-flex align-items-center mb-4 border-bottom pb-2">
                        <h4 class="h5 m-0 text-primary font-weight-bold"><i class="fas fa-stethoscope me-2 text-warning"></i> Clinical Notes</h4>
                    </div>
                    <div class="mb-3">
                        <label for="pDiagnosis">Chief Complaint / Diagnosis *</label>
                        <textarea class="form-control" id="pDiagnosis" name="diagnosis" required rows="2" placeholder="Describe symptoms or clinical diagnosis..."></textarea>
                    </div>
                    <div>
                        <label for="pInvestigations">Investigations Advised</label>
                        <textarea class="form-control" id="pInvestigations" name="investigations" rows="2" placeholder="e.g. OPG, RVG, CBCT, Blood Tests, HbA1c..."></textarea>
                        <div class="form-text text-muted" style="font-size:10px; margin-top:4px;">Dental imaging & lab investigations ordered</div>
                    </div>
                </div>

                <!-- Medicines Dynamic Repeater -->
                <div class="card-premium">
                    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-2">
                        <h4 class="h5 m-0 text-primary font-weight-bold"><i class="fas fa-pills me-2 text-warning"></i> Prescription Sheet (Rx)</h4>
                        <span class="badge bg-light text-secondary border py-2 px-3 text-uppercase font-weight-bold" style="font-size:10px;"><span id="medCounterDisplay">0</span>/15 Medicines</span>
                    </div>

                    <div id="medRepeaterContainer" class="mb-4">
                        <!-- Dynamic medicine rows dynamically added -->
                    </div>

                    <button type="button" class="btn btn-orange-outline w-100 py-3 text-uppercase font-weight-bold" id="addMedRowBtn">
                        <i class="fas fa-plus-circle me-1"></i> Add Medication Row
                    </button>
                </div>

                <!-- Advice & Follow-Up Block -->
                <div class="card-premium">
                    <div class="d-flex align-items-center mb-4 border-bottom pb-2">
                        <h4 class="h5 m-0 text-primary font-weight-bold"><i class="fas fa-notes-medical me-2 text-warning"></i> Advice & Follow-Up</h4>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="pAdvice">Clinical Advice</label>
                            <textarea class="form-control" id="pAdvice" name="advice" rows="2" placeholder="e.g. Avoid sweet food. Perform mouthwash twice daily."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="pFollowup">Review / Follow-Up Days</label>
                            <div class="mod-dropdown" id="pFollowup" data-name="follow_up" data-placeholder="None">
                                <input type="hidden" name="follow_up" value="">
                                <div class="mod-dropdown-trigger">
                                    <span class="mod-dropdown-selected">None</span>
                                    <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                                </div>
                                <div class="mod-dropdown-panel">
                                    <div class="mod-dropdown-option" data-value=""><span class="opt-check"></span><span>None</span></div>
                                    <div class="mod-dropdown-option" data-value="Review After: 3 Days"><span class="opt-check"></span><span>Review After: 3 Days</span></div>
                                    <div class="mod-dropdown-option" data-value="Review After: 5 Days"><span class="opt-check"></span><span>Review After: 5 Days</span></div>
                                    <div class="mod-dropdown-option" data-value="Review After: 7 Days"><span class="opt-check"></span><span>Review After: 7 Days</span></div>
                                    <div class="mod-dropdown-option" data-value="Review After: 10 Days"><span class="opt-check"></span><span>Review After: 10 Days</span></div>
                                    <div class="mod-dropdown-option" data-value="Review After: 15 Days"><span class="opt-check"></span><span>Review After: 15 Days</span></div>
                                    <div class="mod-dropdown-option" data-value="Review After: 1 Month"><span class="opt-check"></span><span>Review After: 1 Month</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit / Actions -->
                <div class="d-flex justify-content-between align-items-center mb-5 gap-3">
                    <button type="button" class="btn btn-outline-danger py-3 px-4 rounded-pill font-weight-bold text-uppercase" style="font-size:10px;" id="clearDraftBtn">
                        Clear Form
                    </button>
                    <button type="button" class="btn btn-navy py-3 px-5 rounded-pill font-weight-bold text-uppercase" id="submitSaveBtn">
                        <i class="fas fa-print me-2"></i> Save &amp; Print Prescription
                    </button>
                </div>

            </form>
        </div>

        <!-- US Letter Preview Canvas (Right) -->
        <div class="col-12 col-xl-6 text-center">
            <p class="text-uppercase tracking-widest text-warning font-weight-bold mb-2" style="font-size:10px; letter-spacing: 0.25em;">US Letter Paper Preview</p>
            <div class="preview-container mb-4">
                <div class="letter-page" id="rxLetterCanvas" style="background-image: url('<?= $rx_template ? htmlspecialchars($rx_template) : '' ?>');">
                    <!-- Absolute Text Fields overlaying coordinate metrics -->
                    <div class="c-name" id="cvName">---</div>
                    <div class="c-age" id="cvAge">--</div>
                    <div class="c-date" id="cvDate">--/--/----</div>
                    
                    <div class="c-rx-section" id="cvMedsContainer">
                        <!-- Render dynamic absolute medicines list -->
                    </div>

                    <div class="c-investigations" id="cvInvestigations"></div>
                    <div class="c-diagnosis" id="cvDiagnosis"></div>
                    <div class="c-advice" id="cvAdvice"></div>
                    <div class="c-followup" id="cvFollowup"></div>

                </div>
            </div>
        </div>
    </div>

    <!-- ═══ BACKGROUND TEMPLATE CONFIGURATION MODAL ═══ -->
    <div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:24px;">
                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="modal-title font-serif text-primary font-weight-bold">Rx Background Template Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4">
                    <form id="settingsUploadForm" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label font-weight-bold">Custom Pre-Printed Background (JPG, PNG)</label>
                            <input type="file" class="form-control" name="template_file" accept=".jpg,.jpeg,.png">
                            <div class="form-text text-muted" style="font-size:10px;">Used for visual canvas layout background. Will never print.</div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="saveSettingsBtn">Save Configurations</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</main>

<!-- Load Dynamic Elements Repeater Template -->
<template id="medRepeaterTemplate">
    <div class="med-row shadow-sm">
        <div class="row g-2 align-items-center mb-3">
            <div class="col-12 col-md-6">
                <input type="text" name="med_name[]" class="form-control med-name-input" required placeholder="Medicine Name (e.g. Cap. Cef-3 400mg)">
            </div>
            <div class="col-6 col-md-3">
                <input type="text" name="med_duration[]" class="form-control med-duration-input" placeholder="Duration (e.g. 7 Days)">
            </div>
            <div class="col-6 col-md-3">
                <input type="text" name="med_note[]" class="form-control med-note-input" placeholder="Note (e.g. After Meal)">
            </div>
        </div>
        <div class="d-flex flex-wrap gap-1 align-items-center">
            <?php 
            $checks = [
                ['Morning', 'chk-morning'],
                ['Noon', 'chk-noon'],
                ['Afternoon', 'chk-afternoon'],
                ['Night', 'chk-night'],
                ['Late Night', 'chk-latenight']
            ];
            foreach($checks as $chk):
            ?>
            <label class="chk-pill">
                <input type="checkbox" class="med-freq-box <?= $chk[1] ?>">
                <span><?= $chk[0] ?></span>
            </label>
            <?php endforeach; ?>
            <input type="hidden" name="med_frequency[]" class="med-frequency-hidden" value="(0-0-0-0-0)">
        </div>
        <button type="button" class="btn btn-close position-absolute top-2 end-2 remove-med-row-btn" style="padding:4px;"></button>
    </div>
</template>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // ── Elements & Selectors ──
    const form = document.getElementById('rxSystemForm');
    const pName = document.getElementById('pName');
    const pId = document.getElementById('pId');
    const pAge = document.getElementById('pAge');
    const pDateRoot = document.getElementById('pDate');
    const pDate = pDateRoot.querySelector('input[type="hidden"]');
    const pPhone = document.getElementById('pPhone');
    const pDiagnosis = document.getElementById('pDiagnosis');
    const pInvestigations = document.getElementById('pInvestigations');
    const pAdvice = document.getElementById('pAdvice');
    const pFollowupRoot = document.getElementById('pFollowup');
    const pFollowup = pFollowupRoot.querySelector('input[type="hidden"]');

    const cvName = document.getElementById('cvName');
    const cvAge = document.getElementById('cvAge');
    const cvDate = document.getElementById('cvDate');
    const cvMedsContainer = document.getElementById('cvMedsContainer');
    const cvInvestigations = document.getElementById('cvInvestigations');
    const cvDiagnosis = document.getElementById('cvDiagnosis');
    const cvAdvice = document.getElementById('cvAdvice');
    const cvFollowup = document.getElementById('cvFollowup');
    
    const patientDropdown = document.getElementById('patientAjaxDropdown');
    const medContainer = document.getElementById('medRepeaterContainer');
    const addMedBtn = document.getElementById('addMedRowBtn');
    const medTemplate = document.getElementById('medRepeaterTemplate');
    const counterDisplay = document.getElementById('medCounterDisplay');
    
    let medCount = 0;

    // ── Real-Time Preview Synchronization ──
    function syncPreview() {
        cvName.textContent = pName.value.trim() || '---';
        cvAge.textContent = pAge.value.trim() ? pAge.value.trim() + ' Yrs' : '--';
        
        // Date formatting: YYYY-MM-DD to DD/MM/YYYY
        if (pDate.value) {
            const parts = pDate.value.split('-');
            cvDate.textContent = parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : pDate.value;
        } else {
            cvDate.textContent = '--/--/----';
        }
        
        cvInvestigations.innerHTML = pInvestigations.value.trim() ? `<strong>Investigations</strong><span>${pInvestigations.value.trim().replace(/\n/g, '<br>')}</span>` : '';
        cvDiagnosis.innerHTML = pDiagnosis.value.trim() ? `<strong>Diagnosis</strong><span>${pDiagnosis.value.trim().replace(/\n/g, '<br>')}</span>` : '';
        cvAdvice.innerHTML = pAdvice.value.trim() ? `<strong>Advice / Notes</strong><span>${pAdvice.value.trim()}</span>` : '';
        cvFollowup.innerHTML = pFollowup.value ? `<strong>${pFollowup.value}</strong>` : '';
        
        // Medicines Listing Preview mapping coordinates precisely
        cvMedsContainer.innerHTML = '';
        const rows = medContainer.querySelectorAll('.med-row');
        rows.forEach(row => {
            const name = row.querySelector('.med-name-input').value.trim();
            const dur = row.querySelector('.med-duration-input').value.trim();
            const note = row.querySelector('.med-note-input').value.trim();
            const freq = row.querySelector('.med-frequency-hidden').value;
            
            if (name) {
                const entry = document.createElement('div');
                entry.className = 'c-med-entry';
                
                // Formulate duration representation
                const freqDur = `${freq}${dur ? ' × ' + dur : ''}`;
                
                entry.innerHTML = `
                    <div class="c-med-name">${name}</div>
                    <div class="c-med-freq">${freqDur}</div>
                    ${note ? `<div class="c-med-inst">${note}</div>` : ''}
                `;
                cvMedsContainer.appendChild(entry);
            }
        });
    }

    [pName, pAge, pDate, pDiagnosis, pInvestigations, pAdvice, pFollowup].forEach(el => {
        el.addEventListener('input', syncPreview);
        el.addEventListener('change', syncPreview);
    });

    // ── Medicine Dynamic Repeater Management ──
    function addMedRow(name='', dur='', note='', freq='(0-0-0-0-0)') {
        if (medCount >= 15) {
            alert("Maximum 15 medicines allowed.");
            return;
        }

        const clone = medTemplate.content.cloneNode(true);
        const row = clone.querySelector('.med-row');
        
        // Pre-fill inputs if editing or recovering draft
        row.querySelector('.med-name-input').value = name;
        row.querySelector('.med-duration-input').value = dur;
        row.querySelector('.med-note-input').value = note;
        row.querySelector('.med-frequency-hidden').value = freq;
        
        // Parse checkboxes status from freq
        const matches = freq.match(/\((\d)-(\d)-(\d)-(\d)-(\d)\)/);
        if (matches && matches.length === 6) {
            if (matches[1] === '1') setCheck(row.querySelector('.chk-morning'));
            if (matches[2] === '1') setCheck(row.querySelector('.chk-noon'));
            if (matches[3] === '1') setCheck(row.querySelector('.chk-afternoon'));
            if (matches[4] === '1') setCheck(row.querySelector('.chk-night'));
            if (matches[5] === '1') setCheck(row.querySelector('.chk-latenight'));
        }

        function setCheck(chk) {
            chk.checked = true;
            chk.parentElement.classList.add('active');
        }

        // Add Listeners
        row.querySelector('.remove-med-row-btn').addEventListener('click', () => {
            row.remove();
            medCount--;
            updateCounter();
            syncPreview();
            saveDraft();
        });

        // Sync inputs
        row.querySelectorAll('input').forEach(inp => {
            inp.addEventListener('input', () => {
                syncPreview();
                saveDraft();
            });
        });

        // Checkbox events mapping to (1-0-0-1-0) format
        const checks = row.querySelectorAll('.med-freq-box');
        const hiddenFreq = row.querySelector('.med-frequency-hidden');

        checks.forEach(chk => {
            chk.addEventListener('change', () => {
                chk.parentElement.classList.toggle('active', chk.checked);
                
                const morning = row.querySelector('.chk-morning').checked ? '1' : '0';
                const noon = row.querySelector('.chk-noon').checked ? '1' : '0';
                const afternoon = row.querySelector('.chk-afternoon').checked ? '1' : '0';
                const night = row.querySelector('.chk-night').checked ? '1' : '0';
                const latenight = row.querySelector('.chk-latenight').checked ? '1' : '0';
                
                hiddenFreq.value = `(${morning}-${noon}-${afternoon}-${night}-${latenight})`;
                syncPreview();
                saveDraft();
            });
        });

        medContainer.appendChild(row);
        medCount++;
        updateCounter();
        syncPreview();
    }

    function updateCounter() {
        counterDisplay.textContent = medCount;
        addMedBtn.style.display = medCount >= 15 ? 'none' : 'block';
    }

    addMedBtn.addEventListener('click', () => {
        addMedRow();
        saveDraft();
    });

    // ── Patient Search Autocomplete (AJAX) ──
    let ajaxTimer;
    pName.addEventListener('input', () => {
        const q = pName.value.trim();
        pId.value = ''; // Reset ID if doctor types manually
        
        clearTimeout(ajaxTimer);
        if (q.length < 2) {
            patientDropdown.classList.add('d-none');
            return;
        }

        ajaxTimer = setTimeout(() => {
            fetch(`api/search_patient.php?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(res => {
                    patientDropdown.innerHTML = '';
                    if (res.status === 'success' && res.data.length > 0) {
                        res.data.forEach(p => {
                            const div = document.createElement('div');
                            div.className = 'px-3 py-2 border-bottom hover:bg-light cursor-pointer';
                            div.innerHTML = `<p class="m-0 font-weight-bold text-primary">${p.name}</p><p class="m-0 text-muted" style="font-size:11px;">ID: ${p.patient_id} &middot; Phone: ${p.phone || 'N/A'}</p>`;
                            
                            div.addEventListener('click', () => {
                                pName.value = p.name;
                                pId.value = p.id;
                                pPhone.value = p.phone || '';
                                
                                // Retrieve Patient vitals
                                fetch(`api/get_patient_vitals.php?id=${p.id}`)
                                    .then(r => r.json())
                                    .then(v => {
                                        if (v.status === 'success') {
                                            pAge.value = v.data.age || '';
                                        }
                                        syncPreview();
                                        saveDraft();
                                    });
                                
                                patientDropdown.classList.add('d-none');
                            });
                            patientDropdown.appendChild(div);
                        });
                        patientDropdown.classList.remove('d-none');
                    } else {
                        patientDropdown.innerHTML = `<div class="px-3 py-3 text-muted italic" style="font-size:12px;">No matches. Patient will be created automatically.</div>`;
                        patientDropdown.classList.remove('d-none');
                    }
                });
        }, 300);
    });

    document.addEventListener('click', e => {
        if (!pName.contains(e.target) && !patientDropdown.contains(e.target)) patientDropdown.classList.add('d-none');
    });

    // ── Autosave Draft Caching ──
    const AUTOSAVE_KEY = `rx_draft_doc_${<?= $doctor_id ?>}`;
    
    function saveDraft() {
        const meds = [];
        medContainer.querySelectorAll('.med-row').forEach(row => {
            meds.push({
                name: row.querySelector('.med-name-input').value,
                dur: row.querySelector('.med-duration-input').value,
                note: row.querySelector('.med-note-input').value,
                freq: row.querySelector('.med-frequency-hidden').value
            });
        });

        const draft = {
            patient_id: pId.value,
            patient_name: pName.value,
            age: pAge.value,
            phone: pPhone.value,
            rx_date: pDate.value,
            diagnosis: pDiagnosis.value,
            investigations: pInvestigations.value,
            advice: pAdvice.value,
            follow_up: pFollowup.value,
            medicines: meds
        };
        localStorage.setItem(AUTOSAVE_KEY, JSON.stringify(draft));
        
        const ind = document.getElementById('autosaveIndicator');
        ind.textContent = 'Draft Saved';
        ind.className = 'badge bg-success me-2 py-2 px-3 text-uppercase font-weight-bold';
        setTimeout(() => {
            ind.textContent = 'Autosave Active';
            ind.className = 'badge bg-secondary me-2 py-2 px-3 text-uppercase font-weight-bold';
        }, 1500);
    }

    function recoverDraft() {
        const cached = localStorage.getItem(AUTOSAVE_KEY);
        if (cached) {
            try {
                const data = JSON.parse(cached);
                pId.value = data.patient_id || '';
                pName.value = data.patient_name || '';
                pAge.value = data.age || '';
                pPhone.value = data.phone || '';
                if(data.rx_date) pDate.value = data.rx_date;
                pDiagnosis.value = data.diagnosis || '';
                pInvestigations.value = data.investigations || '';
                pAdvice.value = data.advice || '';
                pFollowup.value = data.follow_up || '';
                setModDropdown(pFollowupRoot, data.follow_up || '');
                pFollowup.dispatchEvent(new Event('change'));
                
                medContainer.innerHTML = '';
                medCount = 0;
                if (data.medicines && data.medicines.length > 0) {
                    data.medicines.forEach(m => {
                        addMedRow(m.name, m.dur, m.note, m.freq);
                    });
                } else {
                    addMedRow();
                }
                syncPreview();
            } catch(e) {
                addMedRow();
            }
        } else {
            addMedRow(); // Add default empty row
        }
    }

    // Auto-save scheduler every 30 seconds
    setInterval(saveDraft, 30000);

    // Clear Draft
    document.getElementById('clearDraftBtn').addEventListener('click', () => {
        if(confirm("Are you sure you want to clear all form fields?")) {
            localStorage.removeItem(AUTOSAVE_KEY);
            form.reset();
            pId.value = '';
            pDate.value = new Date().toISOString().split('T')[0];
            medContainer.innerHTML = '';
            medCount = 0;
            addMedRow();
            syncPreview();
        }
    });

    // ── AJAX Configurations settings upload ──
    const settingsForm = document.getElementById('settingsUploadForm');
    settingsForm.addEventListener('submit', e => {
        e.preventDefault();
        const saveBtn = document.getElementById('saveSettingsBtn');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Uploading...';
        
        const fd = new FormData(settingsForm);
        fetch('api/upload_rx_settings.php', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Configurations';
            if (res.status === 'success') {
                alert("Configurations saved!");
                
                // Update live previews
                if (res.template_path) {
                    document.getElementById('rxLetterCanvas').style.backgroundImage = `url('${res.template_path}?t=${Date.now()}')`;
                }
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
                modal.hide();
            } else {
                alert("Error: " + res.message);
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Configurations';
            alert("An upload error occurred.");
        });
    });

    // ── AJAX Prescription Saving ──
    document.getElementById('submitSaveBtn').addEventListener('click', () => {
        // Run standard Validation
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            alert("Please fill in all required fields (Patient Name, Diagnosis, and Date).");
            return;
        }

        const submitBtn = document.getElementById('submitSaveBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Saving Prescription...`;

        const fd = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fas fa-print me-2"></i> Save &amp; Print Prescription`;
            
            if (res.status === 'success') {
                // Clear local draft cache
                localStorage.removeItem(AUTOSAVE_KEY);
                
                // Spawn the coordinated 1:1 print screen in a new window/tab
                window.open(`print_prescription.php?id=${res.prescription_id}`, '_blank');
                
                // Redirect parent to registry log
                window.location.href = 'prescriptions.php?success=Prescription saved successfully';
            } else {
                alert("Saving Error: " + res.message);
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fas fa-print me-2"></i> Save &amp; Print Prescription`;
            alert("An unexpected server error occurred.");
        });
    });

    // Recover draft cache on startup
    recoverDraft();

});
</script>

<?php require_once 'components/footer.php'; ?>
