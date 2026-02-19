<?php
require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Core/Validator.php';
require_once __DIR__ . '/../app/Models/User.php';

// Redirect if already logged in
if (Auth::check()) {
    header('Location: ' . BASE_URL);
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => Validator::sanitize($_POST['username'] ?? ''),
        'email' => Validator::sanitize($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'full_name' => Validator::sanitize($_POST['full_name'] ?? '')
    ];

    // Validate input
    $validator = new Validator($data);
    $validator->required('username', 'Tên đăng nhập là bắt buộc')
              ->min('username', 3, 'Tên đăng nhập phải có ít nhất 3 ký tự')
              ->required('email', 'Email là bắt buộc')
              ->email('email', 'Email không hợp lệ')
              ->required('password', 'Mật khẩu là bắt buộc')
              ->min('password', 6, 'Mật khẩu phải có ít nhất 6 ký tự')
              ->required('confirm_password', 'Xác nhận mật khẩu là bắt buộc')
              ->match('confirm_password', 'password', 'Mật khẩu xác nhận không khớp')
              ->required('full_name', 'Họ tên là bắt buộc');

    if ($validator->fails()) {
        $errors = $validator->errors();
    } else {
        // Check if username or email already exists
        $userModel = new User();
        
        if ($userModel->usernameExists($data['username'])) {
            $errors['username'] = 'Tên đăng nhập đã tồn tại';
        }
        
        if ($userModel->emailExists($data['email'])) {
            $errors['email'] = 'Email đã được sử dụng';
        }

        if (empty($errors)) {
            // Create user
            $userData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'full_name' => $data['full_name'],
                'role' => 'user'
            ];

            if ($userModel->create($userData)) {
                $success = true;
                Session::flash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
                header('Location: ' . BASE_URL . 'login.php');
                exit;
            } else {
                $errors['general'] = 'Có lỗi xảy ra. Vui lòng thử lại.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .auth-title {
            text-align: center;
            color: #007bff;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #007bff;
        }
        .error {
            color: #dc3545;
            font-size: 13px;
            margin-top: 5px;
        }
        .success {
            color: #28a745;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn-submit:hover {
            background: #0056b3;
        }
        .auth-links {
            text-align: center;
            margin-top: 20px;
        }
        .auth-links a {
            color: #007bff;
            text-decoration: none;
        }
        .auth-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2 class="auth-title"><i class="fa-solid fa-user-plus"></i> Đăng ký tài khoản</h2>
        
        <?php if (isset($errors['general'])): ?>
            <div class="error" style="text-align: center; margin-bottom: 15px;">
                <?= $errors['general'] ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username"><i class="fa-solid fa-user"></i> Tên đăng nhập</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                <?php if (isset($errors['username'])): ?>
                    <div class="error"><?= $errors['username'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="error"><?= $errors['email'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="full_name"><i class="fa-solid fa-id-card"></i> Họ và tên</label>
                <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                <?php if (isset($errors['full_name'])): ?>
                    <div class="error"><?= $errors['full_name'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Mật khẩu</label>
                <input type="password" id="password" name="password">
                <?php if (isset($errors['password'])): ?>
                    <div class="error"><?= $errors['password'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirm_password"><i class="fa-solid fa-lock"></i> Xác nhận mật khẩu</label>
                <input type="password" id="confirm_password" name="confirm_password">
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="error"><?= $errors['confirm_password'] ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">Đăng ký</button>
        </form>

        <div class="auth-links">
            Đã có tài khoản? <a href="<?= BASE_URL ?>login.php">Đăng nhập ngay</a>
        </div>
    </div>
</body>
</html>
