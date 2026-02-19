<?php
/**
 * Session Management Class
 */

class Session {
    
    /**
     * Start session
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Set session variable
     */
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get session variable
     */
    public static function get($key, $default = null) {
        self::start();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * Check if session variable exists
     */
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Delete session variable
     */
    public static function delete($key) {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy all session data
     */
    public static function destroy() {
        self::start();
        session_unset();
        session_destroy();
    }

    /**
     * Set flash message
     */
    public static function flash($key, $value = null) {
        self::start();
        if ($value === null) {
            // Get and delete flash message
            $message = self::get('flash_' . $key);
            self::delete('flash_' . $key);
            return $message;
        } else {
            // Set flash message
            self::set('flash_' . $key, $value);
        }
    }

    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        self::start();
        $token = bin2hex(random_bytes(32));
        self::set(CSRF_TOKEN_NAME, $token);
        self::set(CSRF_TOKEN_NAME . '_time', time());
        return $token;
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        self::start();
        $sessionToken = self::get(CSRF_TOKEN_NAME);
        $tokenTime = self::get(CSRF_TOKEN_NAME . '_time');
        
        if (!$sessionToken || !$tokenTime) {
            return false;
        }
        
        // Check if token expired
        if (time() - $tokenTime > CSRF_TOKEN_TIME) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }
}
