<?php
$page_title = 'Doctor Profile';
require_once 'includes/header.php';
require_once 'config/db.php';
require_once 'includes/auth.php';

$doctor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$doctor_id) {
    header('Location: doctors.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

if (!$doctor) {
    header('Location: doctors.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT dc.center_id, cc.center_name, cc.address 
    FROM doctor_center dc 
    JOIN channelling_centers cc ON dc.center_id = cc.center_id 
    WHERE dc.doctor_id = ?
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$centers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$selectedCenter = isset($_GET['center_id']) ? intval($_GET['center_id']) : ($centers[0]['center_id'] ?? 0);
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d', strtotime('+1 day'));

$slots = [];
if ($selectedCenter) {
    $stmt = $conn->prepare("
        SELECT ts.*, cc.center_name 
        FROM time_slots ts 
        JOIN channelling_centers cc ON ts.center_id = cc.center_id 
        WHERE ts.doctor_id = ? AND ts.center_id = ? AND ts.slot_date = ? AND ts.status = 'available'
        ORDER BY ts.start_time ASC
    ");
    $stmt->bind_param("iis", $doctor_id, $selectedCenter, $selectedDate);
    $stmt->execute();
    $slots = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$dates = [];
for ($i = 1; $i <= 14; $i++) {
    $dates[] = date('Y-m-d', strtotime("+{$i} day"));
}

$flash = getFlashMessage();
?>

<section class="main-content">
    <div class="container">
        <?php if ($flash): ?>
        <div class="flash-message <?php echo $flash['type']; ?>">
            <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($flash['message']); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="doctor-profile">
            <div class="profile-sidebar">
                <div class="profile-image">
                    <i class="fas fa-user-md"></i>
                </div>
                <h2><?php echo htmlspecialchars($doctor['full_name']); ?></h2>
                <p class="specialization"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                
                <div class="profile-details">
                    <div class="detail-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span><?php echo htmlspecialchars($doctor['qualification']); ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($doctor['email']); ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo htmlspecialchars($doctor['phone']); ?></span>
                    </div>
                </div>
                
                <a href="book-appointment.php?doctor_id=<?php echo $doctor_id; ?>&center_id=<?php echo $selectedCenter; ?>" class="btn-secondary" style="margin-top: 20px; width: 100%; justify-content: center;">
                    <i class="fas fa-calendar-plus"></i> Book Appointment
                </a>
            </div>
            
            <div class="profile-main">
                <h3>About Dr. <?php echo htmlspecialchars(explode(' ', $doctor['full_name'])[1] ?? $doctor['full_name']); ?></h3>
                <p class="bio-text"><?php echo nl2br(htmlspecialchars($doctor['bio'])); ?></p>
                
                <div class="time-slots-section">
                    <h3>Available Time Slots</h3>
                    
                    <div class="center-selection">
                        <h4>Select Center</h4>
                        <?php foreach ($centers as $center): ?>
                        <label class="center-card <?php echo $selectedCenter == $center['center_id'] ? 'selected' : ''; ?>" onclick="window.location.href='doctor-detail.php?id=<?php echo $doctor_id; ?>&center_id=<?php echo $center['center_id']; ?>'">
                            <input type="radio" name="center" value="<?php echo $center['center_id']; ?>" <?php echo $selectedCenter == $center['center_id'] ? 'checked' : ''; ?>>
                            <span class="radio-indicator"></span>
                            <div class="center-info">
                                <h4><?php echo htmlspecialchars($center['center_name']); ?></h4>
                                <p><?php echo htmlspecialchars($center['address']); ?></p>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($selectedCenter): ?>
                    <div class="date-selector">
                        <?php foreach ($dates as $date): $dayName = date('l', strtotime($date)); ?>
                        <button class="date-btn <?php echo $selectedDate === $date ? 'active' : ''; ?>" onclick="window.location.href='doctor-detail.php?id=<?php echo $doctor_id; ?>&center_id=<?php echo $selectedCenter; ?>&date=<?php echo $date; ?>'">
                            <span class="day"><?php echo substr($dayName, 0, 3); ?></span>
                            <span class="date"><?php echo date('d M', strtotime($date)); ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    
                    <h4>Select Time Slot for <?php echo date('l, F j, Y', strtotime($selectedDate)); ?></h4>
                    
                    <?php if (empty($slots)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No Available Slots</h3>
                        <p>Please select a different date or center</p>
                    </div>
                    <?php else: ?>
                    <div class="slots-grid">
                        <?php foreach ($slots as $slot): ?>
                        <button class="slot-btn" onclick="window.location.href='book-appointment.php?doctor_id=<?php echo $doctor_id; ?>&center_id=<?php echo $selectedCenter; ?>&date=<?php echo $selectedDate; ?>&slot_id=<?php echo $slot['slot_id']; ?>'">
                            <span class="time"><?php echo date('h:i A', strtotime($slot['start_time'])); ?> - <?php echo date('h:i A', strtotime($slot['end_time'])); ?></span>
                            <span class="status"><?php echo $slot['status']; ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>