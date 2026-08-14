<?php
/**
 * Simple rate limiter using database
 */
class RateLimiter {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTable();
    }
    
    private function ensureTable() {
        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS rate_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                action_key VARCHAR(100) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                attempts INT NOT NULL DEFAULT 1,
                first_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                locked_until TIMESTAMP NULL DEFAULT NULL,
                UNIQUE KEY unique_action_ip (action_key, ip_address),
                KEY idx_action_ip (action_key, ip_address)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {}
    }
    
    /**
     * Check if an action is rate limited
     * @param string $action - e.g. 'login', 'search_patient', 'add_guest_review'
     * @param int $maxAttempts - max attempts allowed
     * @param int $windowSeconds - time window in seconds
     * @param int $lockoutSeconds - lockout duration after exceeding limit
     * @return array ['allowed' => bool, 'remaining' => int, 'retry_after' => int]
     */
    public function check($action, $maxAttempts = 60, $windowSeconds = 60, $lockoutSeconds = 0) {
        $ip = $this->getClientIP();
        $key = "{$action}:{$ip}";
        
        try {
            // Check if currently locked
            $stmt = $this->pdo->prepare("SELECT locked_until FROM rate_limits WHERE action_key = ? AND ip_address = ? LIMIT 1");
            $stmt->execute([$action, $ip]);
            $record = $stmt->fetch();
            
            if ($record && $record['locked_until'] && strtotime($record['locked_until']) > time()) {
                $retryAfter = strtotime($record['locked_until']) - time();
                return ['allowed' => false, 'remaining' => 0, 'retry_after' => $retryAfter];
            }
            
            // Clean old records outside the window
            $this->pdo->prepare("DELETE FROM rate_limits WHERE action_key = ? AND ip_address = ? AND last_attempt_at < DATE_SUB(NOW(), INTERVAL ? SECOND)")
                ->execute([$action, $ip, $windowSeconds]);
            
            // Get current count in window
            $stmt = $this->pdo->prepare("SELECT attempts FROM rate_limits WHERE action_key = ? AND ip_address = ? LIMIT 1");
            $stmt->execute([$action, $ip]);
            $current = $stmt->fetch();
            
            $attempts = $current ? $current['attempts'] : 0;
            
            if ($attempts >= $maxAttempts) {
                // Lock them out
                if ($lockoutSeconds > 0) {
                    $lockUntil = date('Y-m-d H:i:s', time() + $lockoutSeconds);
                    $this->pdo->prepare("UPDATE rate_limits SET locked_until = ? WHERE action_key = ? AND ip_address = ?")
                        ->execute([$lockUntil, $action, $ip]);
                }
                return ['allowed' => false, 'remaining' => 0, 'retry_after' => $lockoutSeconds];
            }
            
            return ['allowed' => true, 'remaining' => $maxAttempts - $attempts - 1, 'retry_after' => 0];
            
        } catch (Exception $e) {
            // If rate limit table fails, allow the request
            return ['allowed' => true, 'remaining' => $maxAttempts, 'retry_after' => 0];
        }
    }
    
    /**
     * Record an attempt
     */
    public function record($action) {
        $ip = $this->getClientIP();
        
        try {
            $stmt = $this->pdo->prepare("INSERT INTO rate_limits (action_key, ip_address, attempts) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt_at = CURRENT_TIMESTAMP");
            $stmt->execute([$action, $ip]);
        } catch (Exception $e) {}
    }
    
    /**
     * Reset attempts for an action (e.g., on successful login)
     */
    public function reset($action) {
        $ip = $this->getClientIP();
        
        try {
            $this->pdo->prepare("DELETE FROM rate_limits WHERE action_key = ? AND ip_address = ?")
                ->execute([$action, $ip]);
        } catch (Exception $e) {}
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        // Handle multiple IPs in X-Forwarded-For
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }
}
