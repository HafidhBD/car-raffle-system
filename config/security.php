<?php
/**
 * Security Configuration and Utilities
 * Car Raffle System
 */

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

class Security {
    
    /**
     * Sanitize input data
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    /**
     * Validate phone number (Saudi format)
     */
    public static function validatePhone($phone) {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        // Check if it's a valid Saudi phone number
        if (preg_match('/^(05|5|9665|00966)[0-9]{8}$/', $phone)) {
            return true;
        }
        return false;
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Hash password securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate secure random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Rate limiting check
     */
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
        $key = 'rate_limit_' . md5($identifier);
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'time' => time()];
        }
        
        if (time() - $_SESSION[$key]['time'] > $timeWindow) {
            $_SESSION[$key] = ['count' => 1, 'time' => time()];
            return true;
        }
        
        $_SESSION[$key]['count']++;
        
        if ($_SESSION[$key]['count'] > $maxAttempts) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Reset rate limit
     */
    public static function resetRateLimit($identifier) {
        $key = 'rate_limit_' . md5($identifier);
        unset($_SESSION[$key]);
    }
    
    /**
     * Log security events
     */
    public static function logEvent($type, $message, $userId = null) {
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = date('Y-m-d H:i:s') . " | " . 
                    $type . " | " . 
                    ($userId ?? 'guest') . " | " . 
                    $_SERVER['REMOTE_ADDR'] . " | " . 
                    $message . PHP_EOL;
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
