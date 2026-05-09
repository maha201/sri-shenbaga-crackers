<?php
$admin_title = 'Change Password';
require_once __DIR__ . '/admin_header.php';
$db = getDB();
$msg = '';
$msg_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current  = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $user = $db->query("SELECT * FROM admin_users WHERE username='".$db->real_escape_string($_SESSION['admin_user'])."'")->fetch_assoc();

    if (!password_verify($current, $user['password'])) {
        $msg = 'Current password is incorrect.';
        $msg_type = 'error';
    } elseif (strlen($new_pass) < 6) {
        $msg = 'New password must be at least 6 characters.';
        $msg_type = 'error';
    } elseif ($new_pass !== $confirm) {
        $msg = 'New passwords do not match.';
        $msg_type = 'error';
    } else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE admin_users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $user['id']);
        $stmt->execute();
        $msg = 'Password changed successfully!';
    }
}
?>
<?php if ($msg): ?>
<div class="alert alert-<?php echo $msg_type === 'error' ? 'error' : 'success'; ?>">
    <i class="fas fa-<?php echo $msg_type === 'error' ? 'exclamation-circle' : 'check-circle'; ?>"></i>
    <?php echo htmlspecialchars($msg); ?>
</div>
<?php endif; ?>

<div class="card" style="max-width:500px;">
    <div class="card-header"><h2><i class="fas fa-lock"></i> Change Password</h2></div>
    <div class="card-body">
        <form method="POST" class="admin-form">
            <div class="form-group">
                <label>Current Password <span style="color:red">*</span></label>
                <input type="password" name="current_password" required placeholder="Enter current password">
            </div>
            <div class="form-group">
                <label>New Password <span style="color:red">*</span></label>
                <input type="password" name="new_password" required placeholder="Min 6 characters">
            </div>
            <div class="form-group">
                <label>Confirm New Password <span style="color:red">*</span></label>
                <input type="password" name="confirm_password" required placeholder="Repeat new password">
            </div>
            <button type="submit" class="btn btn-red"><i class="fas fa-save"></i> Update Password</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/admin_footer.php'; ?>
