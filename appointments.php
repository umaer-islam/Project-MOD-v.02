<?php
require_once 'components/header.php';
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
require_once 'database/connection.php';

$success_msg = htmlspecialchars($_GET['success'] ?? '');
$error_msg   = htmlspecialchars($_GET['error'] ?? '');

try {
    $stmt = $pdo->query("SELECT a.*, p.name as patient_name, p.patient_id as p_id, p.phone as phone FROM appointments a JOIN patients p ON a.patient_id = p.id ORDER BY a.appointment_date ASC, a.appointment_time ASC LIMIT 50");
    $appointments = $stmt->fetchAll();
    $patientsStmt = $pdo->query("SELECT id, name, patient_id FROM patients ORDER BY name ASC");
    $allPatients = $patientsStmt->fetchAll();
} catch (PDOException $e) {
    $appointments = []; $allPatients = [];
    $error_msg = "Error fetching appointments.";
}
?>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto">

    <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Schedule</p>
            <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Appointments</h1>
            <p class="text-[#7c7c7c] text-sm mt-1">Manage scheduling and patient queues</p>
        </div>
        <button data-modal-target="addAppointmentModal"
                onclick="document.getElementById('addAppointmentModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-[#004591]/20 hover:shadow-[#ea741b]/20 transition-all duration-300 self-start sm:self-auto">
            <i class="fas fa-calendar-plus text-xs"></i>
            Book Appointment
        </button>
    </div>

    <?php if ($success_msg): ?>
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-3.5 rounded-xl text-sm font-medium" id="successAlert">
        <i class="fas fa-check-circle text-green-500"></i> <?= $success_msg ?>
        <button onclick="document.getElementById('successAlert').remove()" class="ml-auto text-green-400 hover:text-green-600"><i class="fas fa-times text-xs"></i></button>
    </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
    <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-5 py-3.5 rounded-xl text-sm font-medium" id="errorAlert">
        <i class="fas fa-exclamation-circle"></i> <?= $error_msg ?>
        <button onclick="document.getElementById('errorAlert').remove()" class="ml-auto text-red-400 hover:text-red-600"><i class="fas fa-times text-xs"></i></button>
    </div>
    <?php endif; ?>

    <div class="admin-card bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)] overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-100 flex gap-2">
            <button class="px-5 py-2 bg-[#004591] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-sm">Upcoming</button>
            <button class="px-5 py-2 text-[#7c7c7c] text-[11px] font-bold uppercase tracking-widest rounded-xl hover:bg-[#F4F7FC] transition-colors">History</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Date & Time</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Patient</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Contact</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Status</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Notes</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (count($appointments) > 0): ?>
                        <?php foreach($appointments as $a):
                            $sc = 'text-gray-600 bg-gray-100';
                            if ($a['status'] === 'Waiting') $sc = 'text-amber-700 bg-amber-50';
                            elseif ($a['status'] === 'In Treatment') $sc = 'text-blue-700 bg-blue-50';
                            elseif ($a['status'] === 'Completed') $sc = 'text-green-700 bg-green-50';
                            elseif ($a['status'] === 'Follow-Up') $sc = 'text-purple-700 bg-purple-50';
                            elseif ($a['status'] === 'Cancelled') $sc = 'text-red-700 bg-red-50';
                        ?>
                        <tr class="hover:bg-[#F8FAFC] transition-colors">
                            <td class="whitespace-nowrap px-6 py-4 whitespace-nowrap">
                                <p class="font-semibold text-[#004591]"><?= date('M d, Y', strtotime($a['appointment_date'])) ?></p>
                                <p class="text-xs text-[#7c7c7c] mt-0.5"><i class="far fa-clock mr-1 text-[#ea741b]"></i><?= date('h:i A', strtotime($a['appointment_time'])) ?></p>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#e8f0fa] flex items-center justify-center text-[#004591] text-sm font-bold flex-shrink-0">
                                        <?= strtoupper(substr($a['patient_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-[#004591]"><?= htmlspecialchars($a['patient_name']) ?></p>
                                        <p class="text-[10px] text-[#7c7c7c]"><?= htmlspecialchars($a['p_id']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-[#7c7c7c]"><?= htmlspecialchars($a['phone']) ?></td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-[9px] font-bold px-2.5 py-1 rounded-full uppercase tracking-widest <?= $sc ?>">
                                    <?= htmlspecialchars($a['status']) ?>
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 max-w-[150px] truncate text-[#7c7c7c]" title="<?= htmlspecialchars($a['description']) ?>">
                                <?= htmlspecialchars($a['description']) ?: '—' ?>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button class="w-8 h-8 rounded-lg bg-[#F4F7FC] hover:bg-green-500 hover:text-white flex items-center justify-center text-[#7c7c7c] transition-all" title="Mark Completed">
                                        <i class="fas fa-check text-xs"></i>
                                    </button>
                                    <button class="w-8 h-8 rounded-lg bg-[#F4F7FC] hover:bg-[#ea741b] hover:text-white flex items-center justify-center text-[#7c7c7c] transition-all" title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 rounded-full bg-[#F4F7FC] flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-calendar-times text-[#7c7c7c] text-xl md:text-2xl"></i>
                                </div>
                                <p class="text-[#7c7c7c] font-semibold">No upcoming appointments.</p>
                                <p class="text-[#7c7c7c] text-xs mt-1">Book a new appointment to get started.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Add Appointment Modal -->
<div id="addAppointmentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm bg-[#004591]/20">
    <div class="relative w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">New Entry</p>
                    <h3 class="font-serif text-xl text-[#004591] font-bold">Book Appointment</h3>
                </div>
                <button onclick="document.getElementById('addAppointmentModal').classList.add('hidden')"
                        class="w-9 h-9 rounded-xl bg-[#F4F7FC] hover:bg-red-50 hover:text-red-500 flex items-center justify-center text-[#7c7c7c] transition-all">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <form action="api/add_appointment.php" method="POST" class="p-6 space-y-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Select Patient *</label>
                    <div class="mod-dropdown" data-name="patient_id" data-placeholder="-- Choose Patient --">
                        <input type="hidden" name="patient_id" value="" required>
                        <div class="mod-dropdown-trigger">
                            <span class="mod-dropdown-selected">-- Choose Patient --</span>
                            <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                        </div>
                        <div class="mod-dropdown-panel">
                            <div class="mod-dropdown-option" data-value=""><span class="opt-check"></span><span>-- Choose Patient --</span></div>
                            <?php foreach($allPatients as $p): ?>
                            <div class="mod-dropdown-option" data-value="<?= $p['id'] ?>"><span class="opt-check"></span><span><?= htmlspecialchars($p['name']) ?> (<?= $p['patient_id'] ?>)</span></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <p class="text-xs text-[#7c7c7c] mt-1.5">Not here? Add them in Patients section first.</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="mod-calendar" data-placeholder="Select date">
                            <input type="hidden" name="appointment_date" value="" required>
                            <div class="mod-calendar-trigger">
                                <span class="mod-calendar-label">Date *</span>
                                <div class="mod-calendar-value">
                                    <i class="fas fa-calendar-day mod-calendar-icon text-sm"></i>
                                    <span class="mod-calendar-text">Select date</span>
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
                    <div>
                        <div class="mod-time" data-placeholder="Select time">
                            <input type="hidden" name="appointment_time" value="" required>
                            <div class="mod-time-trigger">
                                <span class="mod-time-label">Time *</span>
                                <div class="mod-time-value">
                                    <i class="fas fa-clock mod-time-icon text-sm"></i>
                                    <span class="mod-time-text">Select time</span>
                                </div>
                            </div>
                            <div class="mod-time-panel">
                                <div class="tp-row">
                                    <div class="tp-col">
                                        <span class="tp-label">Hour</span>
                                        <div class="tp-scroll tp-hour-scroll">
                                            <?php for($i=1;$i<=12;$i++): ?>
                                            <button type="button" class="tp-btn<?= $i===9?' is-selected':'' ?>" data-v="<?= $i ?>"><?= str_pad($i,2,'0',STR_PAD_LEFT) ?></button>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <span class="tp-sep">:</span>
                                    <div class="tp-col">
                                        <span class="tp-label">Min</span>
                                        <div class="tp-scroll tp-min-scroll">
                                            <?php foreach([0,5,10,15,20,25,30,35,40,45,50,55] as $min): ?>
                                            <button type="button" class="tp-btn<?= $min===0?' is-selected':'' ?>" data-v="<?= $min ?>"><?= str_pad($min,2,'0',STR_PAD_LEFT) ?></button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="tp-ampm">
                                        <button type="button" class="tp-ampm-btn is-selected" data-v="AM">AM</button>
                                        <button type="button" class="tp-ampm-btn" data-v="PM">PM</button>
                                    </div>
                                </div>
                                <button type="button" class="tp-now">Now</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Purpose / Notes</label>
                    <input type="text" name="description" placeholder="e.g., Routine checkup, braces adjustment...">
                </div>
                <div class="pt-4 flex gap-3 border-t border-gray-100">
                    <button type="submit"
                            class="flex-1 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300">
                        <i class="fas fa-calendar-check mr-2"></i> Book Now
                    </button>
                    <button type="button" onclick="document.getElementById('addAppointmentModal').classList.add('hidden')"
                            class="px-5 py-3 bg-[#F4F7FC] text-[#7c7c7c] text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'components/footer.php'; ?>
