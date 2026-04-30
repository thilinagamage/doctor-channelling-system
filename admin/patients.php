<?php
$page_title = 'Manage Patients';
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT * FROM patients WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $query .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchParam = "%$search%";
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $types .= "sss";
}

$query .= " ORDER BY registered_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$patients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (isset($_GET['delete'])) {
    $patient_id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM patients WHERE patient_id = ?");
    $stmt->bind_param("i", $patient_id);
    
    if ($stmt->execute()) {
        $success = 'Patient deleted successfully';
    } else {
        $error = 'Failed to delete patient';
    }
}
?>

<?php if ($error): ?>
<div class="flash-message error" style="padding: 16px; border-radius: 8px; margin-bottom: 20px; background: #fee2e2; color: #b91c1c;">
    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="flash-message success" style="padding: 16px; border-radius: 8px; margin-bottom: 20px; background: #d1fae5; color: #047857;">
    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
</div>
<?php endif; ?>

<div class="filters-section" style="margin-top: 0; background: transparent; padding: 0; margin-bottom: 20px;">
    <form action="patients.php" method="GET" class="filters-form" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
        <input type="text" name="search" placeholder="Search patients..." value="<?php echo htmlspecialchars($search); ?>" style="padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; min-width: 250px; font-size: 1rem;">
        <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Search</button>
        <?php if ($search): ?>
        <a href="patients.php" class="btn-outline">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-table-container">
    <div class="admin-table-header">
        <h3>Patients (<?php echo count($patients); ?>)</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Date of Birth</th>
                <th>Gender</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($patients as $patient): ?>
            <tr>
                <td>#<?php echo $patient['patient_id']; ?></td>
                <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                <td><?php echo htmlspecialchars($patient['email']); ?></td>
                <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                <td><?php echo $patient['date_of_birth'] ? date('d M Y', strtotime($patient['date_of_birth'])) : '-'; ?></td>
                <td><?php echo ucfirst($patient['gender']); ?></td>
                <td><?php echo date('d M Y', strtotime($patient['registered_at'])); ?></td>
                <td>
                    <div class="action-btns">
                        <button class="action-btn view" onclick="viewPatient(<?php echo $patient['patient_id']; ?>, '<?php echo htmlspecialchars($patient['full_name']); ?>', '<?php echo htmlspecialchars($patient['email']); ?>', '<?php echo htmlspecialchars($patient['phone']); ?>', '<?php echo $patient['date_of_birth']; ?>', '<?php echo $patient['gender']; ?>', '<?php echo htmlspecialchars($patient['address']); ?>')">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <a href="patients.php?delete=<?php echo $patient['patient_id']; ?>" class="action-btn cancel" data-confirm="Are you sure you want to delete this patient?" style="background: #fee2e2; color: #dc2626; border: none; padding: 6px 10px; border-radius: 4px; text-decoration: none;">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($patients)): ?>
            <tr>
                <td colspan="8" style="text-align: center;">No patients found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal" id="viewModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Patient Details</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Full Name</label>
                <p id="view_name" style="padding: 10px; background: #f3f4f6; border-radius: 6px;"></p>
            </div>
            <div class="form-group">
                <label>Email</label>
                <p id="view_email" style="padding: 10px; background: #f3f4f6; border-radius: 6px;"></p>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <p id="view_phone" style="padding: 10px; background: #f3f4f6; border-radius: 6px;"></p>
            </div>
            <div class="form-group">
                <label>Date of Birth</label>
                <p id="view_dob" style="padding: 10px; background: #f3f4f6; border-radius: 6px;"></p>
            </div>
            <div class="form-group">
                <label>Gender</label>
                <p id="view_gender" style="padding: 10px; background: #f3f4f6; border-radius: 6px;"></p>
            </div>
            <div class="form-group">
                <label>Address</label>
                <p id="view_address" style="padding: 10px; background: #f3f4f6; border-radius: 6px;"></p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<script>
function viewPatient(id, name, email, phone, dob, gender, address) {
    document.getElementById('view_name').textContent = name;
    document.getElementById('view_email').textContent = email;
    document.getElementById('view_phone').textContent = phone;
    document.getElementById('view_dob').textContent = dob ? dob : 'Not provided';
    document.getElementById('view_gender').textContent = gender ? gender.charAt(0).toUpperCase() + gender.slice(1) : 'Not provided';
    document.getElementById('view_address').textContent = address || 'Not provided';
    document.getElementById('viewModal').classList.add('active');
}

function closeModal() {
    document.getElementById('viewModal').classList.remove('active');
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>