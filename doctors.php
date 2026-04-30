<?php
$page_title = 'Find Doctors';
require_once 'includes/header.php';
require_once 'config/db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$specialization = isset($_GET['specialization']) ? $_GET['specialization'] : '';

$query = "SELECT * FROM doctors WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $query .= " AND (full_name LIKE ? OR specialization LIKE ?)";
    $searchParam = "%$search%";
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $types .= "ss";
}

if ($specialization) {
    $query .= " AND specialization = ?";
    $params[] = &$specialization;
    $types .= "s";
}

$query .= " ORDER BY full_name ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$doctors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$specializations = [];
$specResult = $conn->query("SELECT DISTINCT specialization FROM doctors ORDER BY specialization");
while ($row = $specResult->fetch_assoc()) {
    $specializations[] = $row['specialization'];
}
?>

<section class="main-content">
    <div class="container">
        <div class="page-title">
            <h1>Find a Doctor</h1>
            <p>Browse our network of qualified healthcare professionals</p>
        </div>
        
        <div class="filters-section" style="background: #f9fafb; padding: 20px; margin-bottom: 30px;">
            <form action="doctors.php" method="GET" class="filters-form" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
                <input type="text" name="search" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; min-width: 200px;">
                <select name="specialization" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; min-width: 180px;">
                    <option value="">All Specializations</option>
                    <?php foreach ($specializations as $spec): ?>
                    <option value="<?php echo htmlspecialchars($spec); ?>" <?php echo $specialization === $spec ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($spec); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <?php if ($search || $specialization): ?>
                <a href="doctors.php" class="btn-outline">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if (empty($doctors)): ?>
        <div class="empty-state">
            <i class="fas fa-user-md"></i>
            <h3>No Doctors Found</h3>
            <p>Try adjusting your search criteria</p>
            <a href="doctors.php" class="btn-primary">View All Doctors</a>
        </div>
        <?php else: ?>
        <div class="doctors-grid">
            <?php foreach ($doctors as $doctor): ?>
            <div class="doctor-card">
                <div class="doctor-image">
                    <i class="fas fa-user-md placeholder"></i>
                </div>
                <div class="doctor-info">
                    <h3><?php echo htmlspecialchars($doctor['full_name']); ?></h3>
                    <p class="doctor-specialization"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                    <p class="doctor-qualification"><?php echo htmlspecialchars($doctor['qualification']); ?></p>
                    <div class="doctor-actions">
                        <a href="doctor-detail.php?id=<?php echo $doctor['doctor_id']; ?>" class="btn-view">View Profile</a>
                        <a href="book-appointment.php?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="btn-book">Book Now</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
