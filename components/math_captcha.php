<?php
/**
 * Simple Math CAPTCHA — no external services needed
 */
class MathCaptcha {
    
    /**
     * Generate a CAPTCHA challenge and store answer in session
     * @return array ['question' => string, 'key' => string]
     */
    public static function generate() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $a = rand(1, 20);
        $b = rand(1, 20);
        $operators = ['+', '-'];
        $op = $operators[array_rand($operators)];
        
        // Ensure positive result for subtraction
        if ($op === '-' && $a < $b) {
            [$a, $b] = [$b, $a];
        }
        
        $answer = $op === '+' ? $a + $b : $a - $b;
        $key = bin2hex(random_bytes(16));
        
        $_SESSION['captcha_' . $key] = [
            'answer' => $answer,
            'expires' => time() + 300, // 5 minutes
        ];
        
        return [
            'question' => "What is {$a} {$op} {$b}?",
            'key' => $key,
        ];
    }
    
    /**
     * Verify a CAPTCHA answer
     * @param string $key - the CAPTCHA key
     * @param int $answer - the user's answer
     * @return bool
     */
    public static function verify($key, $answer) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $sessionKey = 'captcha_' . $key;
        
        if (!isset($_SESSION[$sessionKey])) {
            return false;
        }
        
        $captcha = $_SESSION[$sessionKey];
        
        // Check expiry
        if (time() > $captcha['expires']) {
            unset($_SESSION[$sessionKey]);
            return false;
        }
        
        // Check answer
        $valid = (int)$answer === (int)$captcha['answer'];
        
        // Remove after use (one-time)
        unset($_SESSION[$sessionKey]);
        
        return $valid;
    }
}
