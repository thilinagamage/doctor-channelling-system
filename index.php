<?php
$page_title = 'Home';
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

$query .= " LIMIT 6";

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

$centers = [];
$centerResult = $conn->query("SELECT * FROM channelling_centers LIMIT 3");
while ($row = $centerResult->fetch_assoc()) {
    $centers[] = $row;
}
?>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Find Your Doctor & Book an Appointment</h1>
            <p>Connect with the best healthcare professionals in Sri Lanka. Book your appointment online in just a few clicks.</p>
            
            <form action="doctors.php" method="GET" class="search-box" style="background: white; padding: 20px; border-radius: 12px; display: flex; gap: 15px; flex-wrap: wrap; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <input type="text" name="search" placeholder="Search by doctor name or specialization..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; min-width: 200px; padding: 14px 20px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem;">
                <select name="specialization" style="padding: 14px 20px; border: 2px solid #e5e7eb; border-radius: 8px; min-width: 180px; font-size: 1rem;">
                    <option value="">All Specializations</option>
                    <?php foreach ($specializations as $spec): ?>
                    <option value="<?php echo htmlspecialchars($spec); ?>" <?php echo $specialization === $spec ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($spec); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" style="padding: 14px 28px; background: #1a73e8; color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer;"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>
    </div>
</section>

<section class="main-content">
    <div class="container">
        <h2 class="section-title">Featured <span>Doctors</span></h2>
        
        <?php if (empty($doctors)): ?>
        <div class="empty-state">
            <i class="fas fa-user-md"></i>
            <h3>No Doctors Found</h3>
            <p>Try adjusting your search criteria</p>
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
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="doctors.php" class="btn-outline">View All Doctors <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="how-it-works">
    <div class="container">
        <h2 class="section-title">How It <span>Works</span></h2>
        
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Find a Doctor</h3>
                <p>Search for doctors by name, specialization, or browse our featured healthcare professionals.</p>
            </div>
            
            <div class="step-card">
                <div class="step-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>Book Appointment</h3>
                <p>Select your preferred date and time slot that works best for your schedule.</p>
            </div>
            
            <div class="step-card">
                <div class="step-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h3>Make Payment</h3>
                <p>Securely pay for your appointment online or choose to pay at the center.</p>
            </div>
            
            <div class="step-card">
                <div class="step-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>Get Care</h3>
                <p>Visit the doctor at the scheduled time and receive quality healthcare.</p>
            </div>
        </div>
    </div>
</section>

<section class="main-content">
    <div class="container">
        <h2 class="section-title">Our <span>Centers</span></h2>
        
        <div class="centers-grid">
            <?php foreach ($centers as $center): ?>
            <div class="center-card-main">
                <h3><?php echo htmlspecialchars($center['center_name']); ?></h3>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($center['address']); ?></p>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($center['phone']); ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($center['email']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
