<?php
$page_title = 'Book Appointment';
require_once 'includes/header.php';
require_once 'config/db.php';
require_once 'includes/auth.php';

requirePatientLogin();

$patient_id = $_SESSION['patient_id'];
$error = '';
$success = '';

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$center_id = isset($_GET['center_id']) ? intval($_GET['center_id']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : '';
$slot_id = isset($_GET['slot_id']) ? intval($_GET['slot_id']) : 0;

if ($doctor_id) {
    $stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $doctor = $stmt->get_result()->fetch_assoc();
}

if ($center_id) {
    $stmt = $conn->prepare("SELECT * FROM channelling_centers WHERE center_id = ?");
    $stmt->bind_param("i", $center_id);
    $stmt->execute();
    $center = $stmt->get_result()->fetch_assoc();
}

if ($slot_id) {
    $stmt = $conn->prepare("SELECT * FROM time_slots WHERE slot_id = ? AND status = 'available'");
    $stmt->bind_param("i", $slot_id);
    $stmt->execute();
    $slot = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slot_id = intval($_POST['slot_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    if (!$slot_id) {
        $error = 'Please select a time slot';
    } else {
        $stmt = $conn->prepare("SELECT * FROM time_slots WHERE slot_id = ? AND status = 'available'");
        $stmt->bind_param("i", $slot_id);
        $stmt->execute();
        $slot_check = $stmt->get_result()->fetch_assoc();
        
        if (!$slot_check) {
            $error = 'This slot is no longer available. Please select another.';
        } else {
            $stmt = $conn->prepare("SELECT * FROM appointments WHERE slot_id = ? AND status != 'cancelled'");
            $stmt->bind_param("i", $slot_id);
            $stmt->execute();
            $existing = $stmt->get_result();
            
            if ($existing->num_rows > 0) {
                $error = 'This slot has already been booked. Please select another.';
            } else {
                $conn->begin_transaction();
                
                try {
                    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, slot_id, notes, status) VALUES (?, ?, ?, 'pending')");
                    $stmt->bind_param("iis", $patient_id, $slot_id, $notes);
                    $stmt->execute();
                    $appointment_id = $conn->insert_id;
                    
                    $stmt = $conn->prepare("UPDATE time_slots SET status = 'booked' WHERE slot_id = ?");
                    $stmt->bind_param("i", $slot_id);
                    $stmt->execute();
                    
                    $stmt = $conn->prepare("INSERT INTO payments (appointment_id, amount, status) VALUES (?, 1500.00, 'unpaid')");
                    $stmt->bind_param("i", $appointment_id);
                    $stmt->execute();
                    
                    $conn->commit();
                    
                    header("Location: payment.php?appointment_id=$appointment_id");
                    exit;
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = 'Booking failed. Please try again.';
                }
            }
        }
    }
}

$doctors = [];
$result = $conn->query("SELECT * FROM doctors ORDER BY full_name");
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

$centers = [];
$result = $conn->query("SELECT * FROM channelling_centers ORDER BY center_name");
while ($row = $result->fetch_assoc()) {
    $centers[] = $row;
}

$flash = getFlashMessage();
?>

<section class="main-content">
    <div class="container">
        <div class="page-title">
            <h1>Book an Appointment</h1>
            <p>Fill in the details below to book your appointment</p>
        </div>
        
        <?php if ($error): ?>
        <div class="flash-message error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($flash): ?>
        <div class="flash-message <?php echo $flash['type']; ?>">
            <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($flash['message']); ?></span>
        </div>
        <?php endif; ?>
        
        <form action="book-appointment.php" method="POST" class="validate-form">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 30px;">
                <div>
                    <div class="form-group">
                        <label for="doctor_id">Select Doctor *</label>
                        <select id="doctor_id" name="doctor_id" required onchange="updateCenters()">
                            <option value="">Choose a Doctor</option>
                            <?php foreach ($doctors as $doc): ?>
                            <option value="<?php echo $doc['doctor_id']; ?>" <?php echo $doctor_id == $doc['doctor_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($doc['full_name'] . ' - ' . $doc['specialization']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="center_id">Select Center *</label>
                        <select id="center_id" name="center_id" required>
                            <option value="">Choose a Center</option>
                            <?php foreach ($centers as $cen): ?>
                            <option value="<?php echo $cen['center_id']; ?>" <?php echo $center_id == $cen['center_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cen['center_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date">Select Date *</label>
                        <input type="date" id="date" name="date" class="future-date" required value="<?php echo htmlspecialchars($date); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                </div>
                
                <div class="time-slots-section" id="slots-section" style="display: none;">
                    <h4>Available Time Slots</h4>
                    <div class="slots-grid" id="slots-container">
                        <p>Select doctor, center and date to see available slots</p>
                    </div>
                    <input type="hidden" name="slot_id" id="selected_slot" value="<?php echo $slot_id; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Notes for Doctor (Optional)</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Describe your symptoms or reason for visit..."><?php echo htmlspecialchars($notes ?? ''); ?></textarea>
            </div>
            
            <button type="submit" class="btn-primary" style="margin-top: 20px;">
                <i class="fas fa-calendar-check"></i> Confirm Booking
            </button>
        </form>
    </div>
</section>

<script>
function updateCenters() {
    const doctorId = document.getElementById('doctor_id').value;
    const centerId = document.getElementById('center_id').value;
    const date = document.getElementById('date').value;
    const slotsSection = document.getElementById('slots-section');
    const slotsContainer = document.getElementById('slots-container');
    
    if (doctorId && centerId && date) {
        slotsSection.style.display = 'block';
        
        fetch('get-slots.php?doctor_id=' + doctorId + '&center_id=' + centerId + '&date=' + date)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    slotsContainer.innerHTML = data.html;
                } else {
                    slotsContainer.innerHTML = '<p class="error">No slots available</p>';
                }
            })
            .catch(error => {
                slotsContainer.innerHTML = '<p class="error">Error loading slots</p>';
            });
    } else {
        slotsSection.style.display = 'none';
    }
}

function selectSlot(slotId, button) {
    document.querySelectorAll('.slot-btn').forEach(btn => btn.classList.remove('selected'));
    button.classList.add('selected');
    document.getElementById('selected_slot').value = slotId;
}

document.getElementById('center_id').addEventListener('change', updateCenters);
document.getElementById('date').addEventListener('change', updateCenters);

if (document.getElementById('doctor_id').value && document.getElementById('center_id').value && document.getElementById('date').value) {
    updateCenters();
}
</script>

<?php require_once 'includes/footer.php'; ?>