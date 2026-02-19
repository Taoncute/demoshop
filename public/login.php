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
$successMessage = Session::flash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => Validator::sanitize($_POST['username'] ?? ''),
        'password' => $_POST['password'] ?? ''
    ];

    // Validate input
    $validator = new Validator($data);
    $validator->required('username', 'Tên đăng nhập hoặc email là bắt buộc')
              ->required('password', 'Mật khẩu là bắt buộc');

    if ($validator->fails()) {
        $errors = $validator->errors();
    } else {
        $userModel = new User();
        $user = $userModel->findByUsernameOrEmail($data['username']);

        if ($user && password_verify($data['password'], $user['password'])) {
            // Login successful
            Auth::login($user);
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: ' . BASE_URL . 'admin/');
            } else {
                header('Location: ' . BASE_URL);
            }
            exit;
        } else {
            $errors['general'] = 'Tên đăng nhập hoặc mật khẩu không đúng';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/styles.css">
    <style>
        .auth-container {
            max-width: 450px;
            margin: 80px auto;
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
            padding: 10px;
            background: #d4edda;
            border-radius: 6px;
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
        <h2 class="auth-title"><i class="fa-solid fa-right-to-bracket"></i> Đăng nhập</h2>
        
        <?php if ($successMessage): ?>
            <div class="success"><?= $successMessage ?></div>
        <?php endif; ?>

        <?php if (isset($errors['general'])): ?>
            <div class="error" style="text-align: center; margin-bottom: 15px;">
                <?= $errors['general'] ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username"><i class="fa-solid fa-user"></i> Tên đăng nhập hoặc Email</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                <?php if (isset($errors['username'])): ?>
                    <div class="error"><?= $errors['username'] ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Mật khẩu</label>
                <input type="password" id="password" name="password">
                <?php if (isset($errors['password'])): ?>
                    <div class="error"><?= $errors['password'] ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">Đăng nhập</button>
        </form>

        <div class="auth-links">
            Chưa có tài khoản? <a href="<?= BASE_URL ?>register.php">Đăng ký ngay</a>
        </div>
    </div>
</body>
</html>
