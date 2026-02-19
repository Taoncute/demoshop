<?php
require_once __DIR__ . '/../app/Config/Config.php';
require_once __DIR__ . '/../app/Core/Session.php';
require_once __DIR__ . '/../app/Core/Auth.php';

Auth::logout();
Session::flash('success', 'Đăng xuất thành công');
header('Location: ' . BASE_URL . 'login.php');
exit;
