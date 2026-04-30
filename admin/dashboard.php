<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/../config/db.php';
require_once 'includes/admin-header.php';

$totalDoctors = 0;
$result = $conn->query("SELECT COUNT(*) as cnt FROM doctors");
if ($row = $result->fetch_assoc()) $totalDoctors = $row['cnt'];

$totalPatients = 0;
$result = $conn->query("SELECT COUNT(*) as cnt FROM patients");
if ($row = $result->fetch_assoc()) $totalPatients = $row['cnt'];

$totalCenters = 0;
$result = $conn->query("SELECT COUNT(*) as cnt FROM channelling_centers");
if ($row = $result->fetch_assoc()) $totalCenters = $row['cnt'];

$todayAppointments = 0;
$result = $conn->query("SELECT COUNT(*) as cnt FROM appointments a JOIN time_slots ts ON a.slot_id = ts.slot_id WHERE ts.slot_date = CURDATE() AND a.status != 'cancelled'");
if ($row = $result->fetch_assoc()) $todayAppointments = $row['cnt'];

$pendingAppointments = 0;
$result = $conn->query("SELECT COUNT(*) as cnt FROM appointments WHERE status = 'pending'");
if ($row = $result->fetch_assoc()) $pendingAppointments = $row['cnt'];

$totalRevenue = 0;
$result = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'paid'");
if ($row = $result->fetch_assoc()) $totalRevenue = $row['total'];

$recentAppointments = [];
$stmt = $conn->prepare("
    SELECT a.*, ts.slot_date, ts.start_time, d.full_name as doctor_name, p.full_name as patient_name
    FROM appointments a
    JOIN time_slots ts ON a.slot_id = ts.slot_id
    JOIN doctors d ON ts.doctor_id = d.doctor_id
    JOIN patients p ON a.patient_id = p.patient_id
    ORDER BY a.booked_at DESC
    LIMIT 10
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recentAppointments[] = $row;
}
?>

<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-icon primary">
            <i class="fas fa-user-md"></i>
        </div>
        <div class="admin-stat-info">
            <h3><?php echo $totalDoctors; ?></h3>
            <p>Total Doctors</p>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-icon success">
            <i class="fas fa-users"></i>
        </div>
        <div class="admin-stat-info">
            <h3><?php echo $totalPatients; ?></h3>
            <p>Total Patients</p>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-icon warning">
            <i class="fas fa-hospital"></i>
        </div>
        <div class="admin-stat-info">
            <h3><?php echo $totalCenters; ?></h3>
            <p>Channelling Centers</p>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-icon danger">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="admin-stat-info">
            <h3><?php echo $todayAppointments; ?></h3>
            <p>Today's Appointments</p>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-icon primary">
            <i class="fas fa-clock"></i>
        </div>
        <div class="admin-stat-info">
            <h3><?php echo $pendingAppointments; ?></h3>
            <p>Pending Appointments</p>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-icon success">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="admin-stat-info">
            <h3>LKR <?php echo number_format($totalRevenue, 0); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
</div>

<div class="admin-table-container">
    <div class="admin-table-header">
        <h3>Recent Appointments</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentAppointments as $apt): ?>
            <tr>
                <td>#<?php echo $apt['appointment_id']; ?></td>
                <td><?php echo htmlspecialchars($apt['patient_name']); ?></td>
                <td><?php echo htmlspecialchars($apt['doctor_name']); ?></td>
                <td><?php echo date('d M Y', strtotime($apt['slot_date'])); ?></td>
                <td><?php echo date('h:i A', strtotime($apt['start_time'])); ?></td>
                <td>
                    <span class="status-badge <?php echo $apt['status']; ?>">
                        <?php echo ucfirst($apt['status']); ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($recentAppointments)): ?>
            <tr>
                <td colspan="6" style="text-align: center;">No appointments found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/admin-footer.php'; ?>