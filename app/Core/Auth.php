<?php
/**
 * Authentication Class
 */

require_once __DIR__ . '/Session.php';

class Auth {
    
    /**
     * Check if user is logged in
     */
    public static function check() {
        return Session::has('user_id');
    }

    /**
     * Get current user ID
     */
    public static function id() {
        return Session::get('user_id');
    }

    /**
     * Get current user data
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }
        
        return [
            'id' => Session::get('user_id'),
            'username' => Session::get('username'),
            'email' => Session::get('email'),
            'full_name' => Session::get('full_name'),
            'role' => Session::get('role'),
            'balance' => Session::get('balance')
        ];
    }

    /**
     * Login user
     */
    public static function login($user) {
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('email', $user['email']);
        Session::set('full_name', $user['full_name']);
        Session::set('role', $user['role']);
        Session::set('balance', $user['balance']);
    }

    /**
     * Logout user
     */
    public static function logout() {
        Session::destroy();
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return self::check() && Session::get('role') === 'admin';
    }

    /**
     * Require authentication
     */
    public static function requireAuth() {
        if (!self::check()) {
            Session::flash('error', 'Vui lòng đăng nhập để tiếp tục');
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
    }

    /**
     * Require admin role
     */
    public static function requireAdmin() {
        self::requireAuth();
        if (!self::isAdmin()) {
            Session::flash('error', 'Bạn không có quyền truy cập trang này');
            header('Location: ' . BASE_URL);
            exit;
        }
    }

    /**
     * Update user balance in session
     */
    public static function updateBalance($newBalance) {
        Session::set('balance', $newBalance);
    }
}
