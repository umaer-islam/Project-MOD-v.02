<?php
$load_ui_components = true;
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
        border-radius: 16px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 20px rgba(0, 69, 145, 0.06);
        padding: 24px;
        margin-bottom: 20px;
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
        border: 1px solid #e2e8f0;
        padding: 16px;
        position: relative;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }
    #rxSystemScope .med-row:hover {
        border-color: #ea741b/30;
        box-shadow: 0 2px 12px rgba(234, 116, 27, 0.06);
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

    /* ── Frequency Mode Toggle ── */
    .freq-mode-toggle {
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border: 1.5px solid #E2E8F0;
        border-radius: 8px;
        background: #ffffff;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #7c7c7c;
        transition: all 0.2s ease;
        user-select: none;
    }
    .freq-mode-toggle:hover {
        border-color: #ea741b;
        color: #ea741b;
    }
    .freq-mode-toggle.active {
        background: #004591;
        border-color: #004591;
        color: #ffffff;
    }
    .freq-mode-panel { display: flex; flex-wrap: wrap; gap: 4px; align-items: center; }

    /* ── US Letter Preview Canvas (612pt x 792pt) ── */
    .preview-container {
        overflow-x: auto;
        padding: 16px;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        border-radius: 16px;
        border: 1px solid #e2e8f0;
    }
    .letter-page {
        width: 612pt;
        height: 792pt;
        background-color: #ffffff;
        background-size: 100% 100%;
        background-position: center;
        background-repeat: no-repeat;
        position: relative;
        box-shadow: 0 8px 30px rgba(0, 69, 145, 0.12);
        border-radius: 8px;
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

    /* ── Clinical Note Fields (Left Column) ── */
    .c-clinical-box {
        position: absolute;
        left: 20pt;
        width: 142pt;
        font-family: 'Poppins', sans-serif;
        color: #000000;
        overflow: hidden;
        text-align: left;
    }
    .c-clinical-box strong {
        display: block;
        font-size: 8pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #1e293b;
        margin-bottom: 1pt;
        border-bottom: 0.5pt solid #ea741b;
        padding-bottom: 1pt;
        margin-bottom: 2pt;
    }
    .c-clinical-box span {
        font-size: 8.5pt;
        line-height: 11pt;
        font-weight: 400;
    }

    .c-complain { top: 195pt; height: 48pt; }
    .c-exam { top: 248pt; height: 48pt; }
    .c-history { top: 301pt; height: 48pt; }
    .c-investigations { top: 354pt; height: 48pt; }
    .c-diagnosis { top: 407pt; height: 48pt; }

    /* Advice & Follow-up (Left Column, below clinical) */
    .c-advice { top: 465pt; height: 80pt; }
    .c-followup { top: 555pt; height: 25pt; }

</style>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto" id="rxSystemScope">
    
    <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Prescription</p>
            <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Prescription Builder</h1>
            <p class="text-[#7c7c7c] text-sm mt-1">Create, preview, and print prescriptions</p>
        </div>
        <div class="flex items-center gap-3 self-start sm:self-auto">
            <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 border border-green-200 text-[10px] font-bold uppercase tracking-widest py-2 px-3 rounded-xl" id="autosaveIndicator">
                <i class="fas fa-check-circle text-[10px]"></i> Autosave Active
            </span>
            <button class="inline-flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-[#ea741b] hover:text-white text-[#004591] text-[10px] font-bold uppercase tracking-widest rounded-xl border border-gray-200 hover:border-[#ea741b] shadow-sm transition-all duration-300" data-bs-toggle="modal" data-bs-target="#settingsModal">
                <i class="fas fa-image text-xs"></i> Rx Background
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Input Form (Left) -->
        <div class="col-12 col-xl-6">
            <form id="rxSystemForm" action="api/save_prescription.php" method="POST" class="needs-validation" novalidate>
                
                <!-- Patient Block -->
                <div class="card-premium">
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-xl bg-[#ea741b]/10 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user-injured text-[#ea741b] text-sm"></i>
                        </div>
                        <h4 class="text-[13px] font-bold text-[#004591] uppercase tracking-wider m-0">Patient Information</h4>
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

                <!-- Clinical Notes Block -->
                <div class="card-premium">
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-xl bg-[#ea741b]/10 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-stethoscope text-[#ea741b] text-sm"></i>
                        </div>
                        <h4 class="text-[13px] font-bold text-[#004591] uppercase tracking-wider m-0">Clinical Notes</h4>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="pComplain">Complain</label>
                            <textarea class="form-control" id="pComplain" name="complain" rows="2" placeholder="Patient's chief complaint..."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="pExamination">On Examination</label>
                            <textarea class="form-control" id="pExamination" name="on_examination" rows="2" placeholder="Clinical findings on examination..."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="pHistory">M/H (Medical History)</label>
                            <textarea class="form-control" id="pHistory" name="medical_history" rows="2" placeholder="Relevant medical history..."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="pInvestigations">Investigations</label>
                            <textarea class="form-control" id="pInvestigations" name="investigations" rows="2" placeholder="e.g. OPG, RVG, CBCT, Blood Tests, HbA1c..."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="pDiagnosis">W/D (Working Diagnosis)</label>
                            <textarea class="form-control" id="pDiagnosis" name="diagnosis" rows="2" placeholder="Working diagnosis..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Medicines Dynamic Repeater -->
                <div class="card-premium">
                    <div class="flex items-center justify-between mb-5 pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#ea741b]/10 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-pills text-[#ea741b] text-sm"></i>
                            </div>
                            <h4 class="text-[13px] font-bold text-[#004591] uppercase tracking-wider m-0">Prescription (Rx)</h4>
                        </div>
                        <span class="inline-flex items-center gap-1.5 bg-gray-50 text-gray-500 border border-gray-200 text-[10px] font-bold uppercase tracking-widest py-1.5 px-3 rounded-lg"><span id="medCounterDisplay" class="text-[#ea741b]">0</span>/15</span>
                    </div>

                    <div id="medRepeaterContainer" class="mb-4">
                        <!-- Dynamic medicine rows dynamically added -->
                    </div>

                    <button type="button" class="w-full inline-flex items-center justify-center gap-2 py-3 bg-white hover:bg-[#ea741b] text-[#ea741b] hover:text-white text-[11px] font-bold uppercase tracking-widest rounded-xl border-2 border-dashed border-[#ea741b]/30 hover:border-[#ea741b] transition-all duration-300" id="addMedRowBtn">
                        <i class="fas fa-plus-circle text-xs"></i> Add Medication
                    </button>
                </div>

                <!-- Advice & Follow-Up Block -->
                <div class="card-premium">
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-xl bg-[#ea741b]/10 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-notes-medical text-[#ea741b] text-sm"></i>
                        </div>
                        <h4 class="text-[13px] font-bold text-[#004591] uppercase tracking-wider m-0">Advice & Follow-Up</h4>
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
                <div class="flex justify-between items-center mb-8 gap-3">
                    <button type="button" class="inline-flex items-center gap-2 px-5 py-3 bg-white hover:bg-red-50 text-red-500 text-[10px] font-bold uppercase tracking-widest rounded-xl border border-red-200 hover:border-red-300 transition-all duration-300" id="clearDraftBtn">
                        <i class="fas fa-trash-alt text-xs"></i> Clear Form
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 px-7 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-[#004591]/20 hover:shadow-[#ea741b]/20 transition-all duration-300" id="submitSaveBtn">
                        <i class="fas fa-print text-xs"></i> Save &amp; Print
                    </button>
                </div>

            </form>
        </div>

        <!-- US Letter Preview Canvas (Right) -->
        <div class="col-12 col-xl-6 text-center">
            <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-2">Live Preview</p>
            <div class="preview-container mb-4">
                <div class="letter-page" id="rxLetterCanvas" style="background-image: url('<?= $rx_template ? htmlspecialchars($rx_template) : '' ?>');">
                    <!-- Absolute Text Fields overlaying coordinate metrics -->
                    <div class="c-name" id="cvName">---</div>
                    <div class="c-age" id="cvAge">--</div>
                    <div class="c-date" id="cvDate">--/--/----</div>
                    
                    <div class="c-rx-section" id="cvMedsContainer">
                        <!-- Render dynamic absolute medicines list -->
                    </div>

                    <div class="c-clinical-box c-complain" id="cvComplain"></div>
                    <div class="c-clinical-box c-exam" id="cvExam"></div>
                    <div class="c-clinical-box c-history" id="cvHistory"></div>
                    <div class="c-clinical-box c-investigations" id="cvInvestigations"></div>
                    <div class="c-clinical-box c-diagnosis" id="cvDiagnosis"></div>
                    <div class="c-clinical-box c-advice" id="cvAdvice"></div>
                    <div class="c-clinical-box c-followup" id="cvFollowup"></div>

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
        <!-- Frequency Mode Toggle -->
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="freq-mode-toggle" data-mode="freq" title="Morning/Noon/Night">
                <i class="fas fa-clock"></i> Freq
            </span>
            <span class="freq-mode-toggle" data-mode="hourly" title="Six/Eight/Twelve Hourly">
                <i class="fas fa-history"></i> Hourly
            </span>
        </div>
        <!-- Frequency Checkboxes (default) -->
        <div class="freq-mode-panel freq-panel-freq">
            <?php 
            $checks = [
                ['Morning', 'chk-morning'],
                ['Noon', 'chk-noon'],
                ['Night', 'chk-night']
            ];
            foreach($checks as $chk):
            ?>
            <label class="chk-pill">
                <input type="checkbox" class="med-freq-box <?= $chk[1] ?>">
                <span><?= $chk[0] ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <!-- Hourly Radio Buttons (hidden by default) -->
        <div class="freq-mode-panel freq-panel-hourly d-none">
            <label class="chk-pill">
                <input type="radio" name="med_hourly_temp" class="med-hourly-radio" value="Six Hourly">
                <span>6-Hourly</span>
            </label>
            <label class="chk-pill">
                <input type="radio" name="med_hourly_temp" class="med-hourly-radio" value="Eight Hourly">
                <span>8-Hourly</span>
            </label>
            <label class="chk-pill">
                <input type="radio" name="med_hourly_temp" class="med-hourly-radio" value="Twelve Hourly">
                <span>12-Hourly</span>
            </label>
        </div>
        <input type="hidden" name="med_frequency[]" class="med-frequency-hidden" value="0+0+0">
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
    const pComplain = document.getElementById('pComplain');
    const pExamination = document.getElementById('pExamination');
    const pHistory = document.getElementById('pHistory');
    const pInvestigations = document.getElementById('pInvestigations');
    const pDiagnosis = document.getElementById('pDiagnosis');
    const pAdvice = document.getElementById('pAdvice');
    const pFollowupRoot = document.getElementById('pFollowup');
    const pFollowup = pFollowupRoot.querySelector('input[type="hidden"]');

    const cvName = document.getElementById('cvName');
    const cvAge = document.getElementById('cvAge');
    const cvDate = document.getElementById('cvDate');
    const cvMedsContainer = document.getElementById('cvMedsContainer');
    const cvComplain = document.getElementById('cvComplain');
    const cvExam = document.getElementById('cvExam');
    const cvHistory = document.getElementById('cvHistory');
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
        
        // Clinical note boxes
        cvComplain.innerHTML = pComplain.value.trim() ? `<strong>Complain</strong><span>${pComplain.value.trim().replace(/\n/g, '<br>')}</span>` : '';
        cvExam.innerHTML = pExamination.value.trim() ? `<strong>On Examination</strong><span>${pExamination.value.trim().replace(/\n/g, '<br>')}</span>` : '';
        cvHistory.innerHTML = pHistory.value.trim() ? `<strong>M/H</strong><span>${pHistory.value.trim().replace(/\n/g, '<br>')}</span>` : '';
        cvInvestigations.innerHTML = pInvestigations.value.trim() ? `<strong>Investigations</strong><span>${pInvestigations.value.trim().replace(/\n/g, '<br>')}</span>` : '';
        cvDiagnosis.innerHTML = pDiagnosis.value.trim() ? `<strong>W/D</strong><span>${pDiagnosis.value.trim().replace(/\n/g, '<br>')}</span>` : '';
        cvAdvice.innerHTML = pAdvice.value.trim() ? `<strong>Advice</strong><span>${pAdvice.value.trim().replace(/\n/g, '<br>')}</span>` : '';
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
                
                // Formulate duration: "1 + 1 + 0 | 1 Week" or "Six Hourly | 3 Days"
                const formattedFreq = freq.replace(/\+/g, ' + ');
                const freqDur = dur ? `${formattedFreq} | ${dur}` : formattedFreq;
                
                entry.innerHTML = `
                    <div class="c-med-name">${name}</div>
                    <div class="c-med-freq">${freqDur}</div>
                    ${note ? `<div class="c-med-inst">${note}</div>` : ''}
                `;
                cvMedsContainer.appendChild(entry);
            }
        });
    }

    [pName, pAge, pDate, pComplain, pExamination, pHistory, pInvestigations, pDiagnosis, pAdvice, pFollowup].forEach(el => {
        el.addEventListener('input', syncPreview);
        el.addEventListener('change', syncPreview);
    });

    // ── Medicine Dynamic Repeater Management ──
    function addMedRow(name='', dur='', note='', freq='0+0+0') {
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
        
        // ── Frequency Mode Detection & Restore ──
        const restoreFreqPanel = row.querySelector('.freq-panel-freq');
        const restoreHourlyPanel = row.querySelector('.freq-panel-hourly');
        const allToggleBtns = row.querySelectorAll('.freq-mode-toggle');
        const isHourly = !freq.match(/(\d)\+(\d)\+(\d)/) && !freq.match(/\((\d)-/);

        if (isHourly && freq) {
            restoreFreqPanel.classList.add('d-none');
            restoreHourlyPanel.classList.remove('d-none');
            allToggleBtns.forEach(b => b.classList.toggle('active', b.dataset.mode === 'hourly'));
            const radio = row.querySelector(`.med-hourly-radio[value="${freq}"]`);
            if (radio) radio.checked = true;
        } else {
            restoreFreqPanel.classList.remove('d-none');
            restoreHourlyPanel.classList.add('d-none');
            allToggleBtns.forEach(b => b.classList.toggle('active', b.dataset.mode === 'freq'));
            const matches = freq.match(/(\d)\+(\d)\+(\d)/) || freq.match(/\((\d)-(\d)-(\d)(?:-\d)?(?:-\d)?\)/);
            if (matches && matches.length >= 4) {
                if (matches[1] === '1') setCheck(row.querySelector('.chk-morning'));
                if (matches[2] === '1') setCheck(row.querySelector('.chk-noon'));
                if (matches[3] === '1') setCheck(row.querySelector('.chk-night'));
            }
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

        // ── Frequency Mode Toggle Logic ──
        const toggleBtns = row.querySelectorAll('.freq-mode-toggle');
        const freqPanel = row.querySelector('.freq-panel-freq');
        const hourlyPanel = row.querySelector('.freq-panel-hourly');
        const hiddenFreq = row.querySelector('.med-frequency-hidden');

        toggleBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const mode = btn.dataset.mode;
                toggleBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                if (mode === 'freq') {
                    freqPanel.classList.remove('d-none');
                    hourlyPanel.classList.add('d-none');
                    // Uncheck hourly radios
                    row.querySelectorAll('.med-hourly-radio').forEach(r => r.checked = false);
                    // Build freq from checkboxes
                    const morning = row.querySelector('.chk-morning').checked ? '1' : '0';
                    const noon = row.querySelector('.chk-noon').checked ? '1' : '0';
                    const night = row.querySelector('.chk-night').checked ? '1' : '0';
                    hiddenFreq.value = `${morning}+${noon}+${night}`;
                } else {
                    hourlyPanel.classList.remove('d-none');
                    freqPanel.classList.add('d-none');
                    // Uncheck frequency checkboxes
                    row.querySelectorAll('.med-freq-box').forEach(c => { c.checked = false; c.parentElement.classList.remove('active'); });
                    hiddenFreq.value = '';
                }
                syncPreview();
                saveDraft();
            });
        });

        // Frequency checkbox events
        row.querySelectorAll('.med-freq-box').forEach(chk => {
            chk.addEventListener('change', () => {
                chk.parentElement.classList.toggle('active', chk.checked);
                const morning = row.querySelector('.chk-morning').checked ? '1' : '0';
                const noon = row.querySelector('.chk-noon').checked ? '1' : '0';
                const night = row.querySelector('.chk-night').checked ? '1' : '0';
                hiddenFreq.value = `${morning}+${noon}+${night}`;
                syncPreview();
                saveDraft();
            });
        });

        // Hourly radio events (click-to-deselect: click again to uncheck)
        row.querySelectorAll('.med-hourly-radio').forEach(radio => {
            radio.addEventListener('mousedown', () => {
                if (radio.checked) {
                    radio.checked = false;
                    radio.parentElement.classList.remove('active');
                    hiddenFreq.value = '';
                    syncPreview();
                    saveDraft();
                }
            });
            radio.addEventListener('change', () => {
                if (radio.checked) {
                    // Uncheck other radios in same group
                    row.querySelectorAll('.med-hourly-radio').forEach(r => {
                        if (r !== radio) r.parentElement.classList.remove('active');
                    });
                    hiddenFreq.value = radio.value;
                    radio.parentElement.classList.add('active');
                }
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
            complain: pComplain.value,
            on_examination: pExamination.value,
            medical_history: pHistory.value,
            investigations: pInvestigations.value,
            diagnosis: pDiagnosis.value,
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
                pComplain.value = data.complain || '';
                pExamination.value = data.on_examination || '';
                pHistory.value = data.medical_history || '';
                pInvestigations.value = data.investigations || '';
                pDiagnosis.value = data.diagnosis || '';
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
        const loadToast = AdminToast.show('Uploading template settings...', 'loading');
        
        const fd = new FormData(settingsForm);
        fetch('api/upload_rx_settings.php', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Configurations';
            AdminToast.dismiss(loadToast);
            if (res.status === 'success') {
                AdminToast.show("Configurations saved!", "success");
                
                // Update live previews
                if (res.template_path) {
                    document.getElementById('rxLetterCanvas').style.backgroundImage = `url('${res.template_path}?t=${Date.now()}')`;
                }
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
                modal.hide();
            } else {
                AdminToast.show("Error: " + res.message, "error");
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Configurations';
            AdminToast.dismiss(loadToast);
            AdminToast.show("An upload error occurred.", "error");
        });
    });

    // ── AJAX Prescription Saving ──
    document.getElementById('submitSaveBtn').addEventListener('click', () => {
        // Run standard Validation
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            AdminToast.show("Please fill in all required fields (Patient Name, Diagnosis, and Date).", "error");
            return;
        }

        const submitBtn = document.getElementById('submitSaveBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Saving Prescription...`;
        const loadToast = AdminToast.show('Saving prescription, please wait...', 'loading');

        const fd = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fas fa-print me-2"></i> Save &amp; Print Prescription`;
            AdminToast.dismiss(loadToast);
            
            if (res.status === 'success') {
                // Clear local draft cache
                localStorage.removeItem(AUTOSAVE_KEY);
                
                // Spawn the coordinated 1:1 print screen in a new window/tab
                window.open(`print_prescription.php?id=${res.prescription_id}`, '_blank');
                
                // Redirect parent to registry log
                window.location.href = 'prescriptions.php?success=Prescription saved successfully';
            } else {
                AdminToast.show("Saving Error: " + res.message, "error");
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fas fa-print me-2"></i> Save &amp; Print Prescription`;
            AdminToast.dismiss(loadToast);
            AdminToast.show("An unexpected server error occurred.", "error");
        });
    });


    // Recover draft cache on startup
    recoverDraft();

});
</script>

<?php require_once 'components/footer.php'; ?>
