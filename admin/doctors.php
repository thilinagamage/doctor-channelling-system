<?php
$page_title = 'Manage Doctors';
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_doctor'])) {
        $full_name = trim($_POST['full_name'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $qualification = trim($_POST['qualification'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        
        if (empty($full_name) || empty($specialization) || empty($email) || empty($phone)) {
            $error = 'Please fill in all required fields';
        } else {
            $stmt = $conn->prepare("INSERT INTO doctors (full_name, specialization, email, phone, qualification, bio) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $full_name, $specialization, $email, $phone, $qualification, $bio);
            
            if ($stmt->execute()) {
                $success = 'Doctor added successfully';
            } else {
                $error = 'Failed to add doctor';
            }
        }
    } elseif (isset($_POST['update_doctor'])) {
        $doctor_id = intval($_POST['doctor_id']);
        $full_name = trim($_POST['full_name'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $qualification = trim($_POST['qualification'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        
        $stmt = $conn->prepare("UPDATE doctors SET full_name = ?, specialization = ?, email = ?, phone = ?, qualification = ?, bio = ? WHERE doctor_id = ?");
        $stmt->bind_param("ssssssi", $full_name, $specialization, $email, $phone, $qualification, $bio, $doctor_id);
        
        if ($stmt->execute()) {
            $success = 'Doctor updated successfully';
        } else {
            $error = 'Failed to update doctor';
        }
    }
}

if (isset($_GET['delete'])) {
    $doctor_id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM doctors WHERE doctor_id = ?");
    $stmt->bind_param("i", $doctor_id);
    
    if ($stmt->execute()) {
        $success = 'Doctor deleted successfully';
    } else {
        $error = 'Failed to delete doctor';
    }
}

$doctors = [];
$result = $conn->query("SELECT * FROM doctors ORDER BY full_name");
while ($row = $result->fetch_assoc()) {
    $doctors[] = $row;
}

$specializations = [];
$result = $conn->query("SELECT DISTINCT specialization FROM doctors ORDER BY specialization");
while ($row = $result->fetch_assoc()) {
    $specializations[] = $row['specialization'];
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

<div class="admin-table-container">
    <div class="admin-table-header">
        <h3>Add New Doctor</h3>
    </div>
    <div style="padding: 20px;">
        <form action="doctors.php" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-group">
                <label>Specialization *</label>
                <input type="text" name="specialization" required list="specializations">
                <datalist id="specializations">
                    <?php foreach ($specializations as $spec): ?>
                    <option value="<?php echo htmlspecialchars($spec); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Phone *</label>
                <input type="tel" name="phone" required>
            </div>
            <div class="form-group">
                <label>Qualification</label>
                <input type="text" name="qualification">
            </div>
            <div class="form-group">
                <label>Bio</label>
                <input type="text" name="bio">
            </div>
            <div style="grid-column: 1 / -1;">
                <button type="submit" name="add_doctor" class="btn-primary">
                    <i class="fas fa-plus"></i> Add Doctor
                </button>
            </div>
        </form>
    </div>
</div>

<div class="admin-table-container" style="margin-top: 30px;">
    <div class="admin-table-header">
        <h3>All Doctors (<?php echo count($doctors); ?>)</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Specialization</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Qualification</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($doctors as $doctor): ?>
            <tr>
                <td>#<?php echo $doctor['doctor_id']; ?></td>
                <td><?php echo htmlspecialchars($doctor['full_name']); ?></td>
                <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                <td><?php echo htmlspecialchars($doctor['qualification']); ?></td>
                <td>
                    <div class="action-btns">
                        <button class="action-btn view" onclick="editDoctor(<?php echo $doctor['doctor_id']; ?>, '<?php echo htmlspecialchars($doctor['full_name']); ?>', '<?php echo htmlspecialchars($doctor['specialization']); ?>', '<?php echo htmlspecialchars($doctor['email']); ?>', '<?php echo htmlspecialchars($doctor['phone']); ?>', '<?php echo htmlspecialchars($doctor['qualification']); ?>', '<?php echo htmlspecialchars($doctor['bio']); ?>')">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <a href="doctors.php?delete=<?php echo $doctor['doctor_id']; ?>" class="action-btn cancel" data-confirm="Are you sure you want to delete this doctor?">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($doctors)): ?>
            <tr>
                <td colspan="7" style="text-align: center;">No doctors found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Doctor</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form action="doctors.php" method="POST">
            <div class="modal-body">
                <input type="hidden" name="doctor_id" id="edit_doctor_id">
                <input type="hidden" name="update_doctor" value="1">
                
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" id="edit_full_name" required>
                </div>
                <div class="form-group">
                    <label>Specialization *</label>
                    <input type="text" name="specialization" id="edit_specialization" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="tel" name="phone" id="edit_phone" required>
                </div>
                <div class="form-group">
                    <label>Qualification</label>
                    <input type="text" name="qualification" id="edit_qualification">
                </div>
                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" id="edit_bio" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editDoctor(id, name, spec, email, phone, qual, bio) {
    document.getElementById('edit_doctor_id').value = id;
    document.getElementById('edit_full_name').value = name;
    document.getElementById('edit_specialization').value = spec;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_qualification').value = qual;
    document.getElementById('edit_bio').value = bio;
    document.getElementById('editModal').classList.add('active');
}

function closeModal() {
    document.getElementById('editModal').classList.remove('active');
}
</script>

</div></main></div></body></html>