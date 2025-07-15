<?php
require_once dirname(__DIR__) . '/config.php';

class AuthManager {
    private static $sessionKey = 'clipboard_authenticated';
    private static $sessionTimeout = 'clipboard_auth_time';
    
    /**
     * 验证密码
     */
    public static function verifyPassword($password) {
        return $password === APP_PASSWORD;
    }
    
    /**
     * 设置认证状态
     */
    public static function setAuthenticated($authenticated = true) {
        $_SESSION[self::$sessionKey] = $authenticated;
        $_SESSION[self::$sessionTimeout] = time();
        
        // 生成新的CSRF token
        generateCSRFToken();
    }
    
    /**
     * 检查是否已认证
     */
    public static function isAuthenticated() {
        if (!isset($_SESSION[self::$sessionKey]) || !$_SESSION[self::$sessionKey]) {
            return false;
        }
        
        // 检查会话是否超时
        if (isset($_SESSION[self::$sessionTimeout])) {
            $elapsed = time() - $_SESSION[self::$sessionTimeout];
            if ($elapsed > SESSION_TIMEOUT) {
                self::logout();
                return false;
            }
            
            // 更新最后访问时间
            $_SESSION[self::$sessionTimeout] = time();
        }
        
        return true;
    }
    
    /**
     * 处理登录请求
     */
    public static function handleLogin($password) {
        if (self::verifyPassword($password)) {
            self::setAuthenticated(true);
            return ['success' => true, 'message' => '认证成功'];
        }
        return ['success' => false, 'message' => '密码错误'];
    }
    
    /**
     * 登出
     */
    public static function logout() {
        unset($_SESSION[self::$sessionKey]);
        unset($_SESSION[self::$sessionTimeout]);
        unset($_SESSION[CSRF_TOKEN_NAME]);
    }
    
    /**
     * 验证CSRF token
     */
    public static function validateCSRF($token) {
        if (!self::isAuthenticated()) {
            return false;
        }
        return validateCSRFToken($token);
    }
    
    /**
     * 获取CSRF token
     */
    public static function getCSRFToken() {
        if (self::isAuthenticated()) {
            return generateCSRFToken();
        }
        return null;
    }
    
    /**
     * 检查是否需要重新认证
     */
    public static function requiresReauth() {
        return !self::isAuthenticated();
    }
    
    /**
     * 获取剩余会话时间（秒）
     */
    public static function getRemainingTime() {
        if (!self::isAuthenticated()) {
            return 0;
        }
        
        if (isset($_SESSION[self::$sessionTimeout])) {
            $elapsed = time() - $_SESSION[self::$sessionTimeout];
            return max(0, SESSION_TIMEOUT - $elapsed);
        }
        
        return SESSION_TIMEOUT;
    }
}
?>