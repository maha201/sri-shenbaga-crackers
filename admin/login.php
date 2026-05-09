<?php
require_once dirname(__DIR__) . '/includes/config.php';
$db = getDB();
$B = BASE_URL;

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . $B . '/admin/dashboard.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user['username'];
        header('Location: ' . $B . '/admin/dashboard.php'); exit;
    } else {
        $error = 'Invalid username or password!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Sri Shenbaga Crackers</title>
<link rel="stylesheet" href="<?php echo $B; ?>/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{background:linear-gradient(135deg,#c0392b 0%,#2c3e50 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;margin:0;padding:20px;}
.login-wrap{width:100%;max-width:420px;}
.login-card{background:#fff;border-radius:16px;padding:45px 40px;box-shadow:0 25px 60px rgba(0,0,0,0.3);}
.login-logo{text-align:center;margin-bottom:30px;}
.login-logo .icon{font-size:3.5rem;display:block;}
.login-logo h2{color:#c0392b;font-size:1.6rem;margin:10px 0 4px;}
.login-logo p{color:#7f8c8d;font-size:13px;margin:0;}
.login-btn{width:100%;padding:14px;background:#c0392b;color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;transition:background .3s;margin-top:5px;}
.login-btn:hover{background:#96281b;}
.login-footer{text-align:center;margin-top:20px;font-size:13px;color:#7f8c8d;}
.login-footer a{color:#c0392b;text-decoration:none;}
.form-group{margin-bottom:18px;}
.form-group label{display:block;font-size:14px;font-weight:600;color:#2c3e50;margin-bottom:6px;}
.form-group input{width:100%;padding:12px 15px;border:2px solid #e0e0e0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;transition:border-color .3s;}
.form-group input:focus{border-color:#c0392b;}
.alert-error{background:#fadbd8;color:#922b21;border:1px solid #f1948a;padding:12px 15px;border-radius:8px;margin-bottom:18px;font-size:14px;display:flex;align-items:center;gap:8px;}
.hint-box{background:#fef9f0;border:1px solid #fdecc8;border-radius:8px;padding:12px 15px;margin-bottom:18px;font-size:13px;color:#7d6608;}
</style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="login-logo">
            <span class="icon">🎆</span>
            <h2>Admin Panel</h2>
            <p>Sri Shenbaga Crackers Management</p>
        </div>

        <div class="hint-box">
            <strong>Default credentials:</strong><br>
            Username: <code>admin</code> &nbsp; Password: <code>admin123</code>
        </div>

        <?php if ($error): ?>
        <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username" required
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required autocomplete="current-password">
            </div>
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> &nbsp; Login to Admin Panel
            </button>
        </form>

        <div class="login-footer">
            <a href="<?php echo $B; ?>/index.php"><i class="fas fa-arrow-left"></i> Back to Website</a>
        </div>
    </div>
</div>
</body>
</html>
