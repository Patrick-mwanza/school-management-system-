<?php
// login.php
session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'dashboard.php' : 'teacher/dashboard.php'));
    exit;
}

require_once __DIR__ . '/config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT id, username, password, role, teacher_id, full_name FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login success - set session
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['teacher_id'] = $user['teacher_id'];

            // Get display name — use full_name column if available, else look up teacher
            if (!empty($user['full_name'])) {
                $_SESSION['full_name'] = $user['full_name'];
            } elseif ($user['role'] === 'teacher' && $user['teacher_id']) {
                $ts = $pdo->prepare("SELECT full_name FROM teachers WHERE id = ?");
                $ts->execute([$user['teacher_id']]);
                $t = $ts->fetch();
                $_SESSION['full_name'] = $t['full_name'] ?? $user['username'];
            } else {
                $_SESSION['full_name'] = $user['username'];
            }

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: dashboard.php');
            } else {
                header('Location: teacher/dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid username or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="School Management System Login">
    <title>Login | SchoolMS</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">

        <div class="login-logo">
            <div class="school-icon">🏫</div>
            <h1>SchoolMS</h1>
            <p>School Management System</p>
        </div>

        <h2>Welcome Back</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger" id="flashMsg">⚠️ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" data-validate id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                    <button type="button" class="eye-toggle" onclick="togglePassword('password', this)" title="Show/hide password">
                        👁️
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" id="loginBtn">
                🔐 Sign In
            </button>
        </form>



    </div>
</div>
<script src="js/main.js"></script>
<script>
function togglePassword(fieldId, btn) {
    var input = document.getElementById(fieldId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
        btn.title = 'Hide password';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
        btn.title = 'Show password';
    }
}
</script>
</body>
</html>
