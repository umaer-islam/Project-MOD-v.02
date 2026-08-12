<?php
/**
 * Patient ID Generator — Mamun's Ortho Dental
 * Format-Preserving Encryption (Feistel Cipher)
 * Generates unpredictable MOD-XXXX patient IDs from sequential counters.
 *
 * Developer: Umaer Islam (https://umaerislam.com)
 */

// Secret key for the cipher — keep this safe and never expose it
define('PATIENT_ID_SECRET', 0xA3F5B9C7);
define('PATIENT_ID_PREFIX', 'MOD');
define('PATIENT_ID_DIGITS', 4);

/**
 * Generate a unique, unpredictable patient ID from a sequential counter.
 * Uses a Feistel cipher to encrypt the counter into a seemingly random 4-digit code.
 *
 * @param int $counter The sequential patient counter (1, 2, 3, ...)
 * @return string Patient ID like "MOD-7384", "MOD-1503", etc.
 */
function generate_patient_id(int $counter): string {
    $halfBits = (PATIENT_ID_DIGITS * 4) / 2;
    $mask = (1 << $halfBits) - 1;

    $left = ($counter >> $halfBits) & $mask;
    $right = $counter & $mask;

    for ($round = 0; $round < 4; $round++) {
        $roundKey = feistel_round_key($round);
        $fOut = feistel_f($right, $roundKey, $mask);
        $newRight = $left ^ $fOut;
        $left = $right;
        $right = $newRight;
    }

    $encrypted = ($right << $halfBits) | $left;
    $encrypted = $encrypted % 10000;

    return PATIENT_ID_PREFIX . '-' . str_pad((string)$encrypted, PATIENT_ID_DIGITS, '0', STR_PAD_LEFT);
}

/**
 * Decrypt a patient MOD-XXXX code back to the original counter.
 *
 * @param string $patientId The patient ID like "MOD-7384"
 * @return int|null The original counter, or null if invalid
 */
function decrypt_patient_id(string $patientId): ?int {
    $prefix = PATIENT_ID_PREFIX . '-';
    if (substr($patientId, 0, strlen($prefix)) !== $prefix) {
        return null;
    }

    $code = (int) substr($patientId, strlen($prefix));
    if ($code < 0 || $code > 9999) {
        return null;
    }

    $halfBits = (PATIENT_ID_DIGITS * 4) / 2;
    $mask = (1 << $halfBits) - 1;

    $left = ($code >> $halfBits) & $mask;
    $right = $code & $mask;

    for ($round = 3; $round >= 0; $round--) {
        $roundKey = feistel_round_key($round);
        $fOut = feistel_f($left, $roundKey, $mask);
        $newLeft = $right ^ $fOut;
        $right = $left;
        $left = $newLeft;
    }

    $decrypted = ($left << $halfBits) | $right;
    return $decrypted;
}

function feistel_round_key(int $round): int {
    return (PATIENT_ID_SECRET >> ($round * 4)) & 0xFF;
}

function feistel_f(int $halfBlock, int $roundKey, int $mask): int {
    $x = ($halfBlock ^ $roundKey) & $mask;
    $x = ($x * 0x37 + 0x5A) & $mask;
    $x = (($x >> 4) | ($x << 4)) & $mask;
    return $x;
}

/**
 * Get the next available patient ID.
 * Queries the database for the highest counter, then encrypts counter+1.
 *
 * @param PDO $pdo Database connection
 * @return string The new patient ID
 */
function get_next_patient_id(PDO $pdo): string {
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(patient_id, 5) AS UNSIGNED)) FROM patients WHERE patient_id LIKE 'MOD-%'");
    $maxCounter = (int) $stmt->fetchColumn();

    $nextCounter = $maxCounter + 1;

    // Generate and verify uniqueness (collision check)
    $patientId = generate_patient_id($nextCounter);
    $attempts = 0;
    while (patient_id_exists($pdo, $patientId) && $attempts < 100) {
        $nextCounter++;
        $patientId = generate_patient_id($nextCounter);
        $attempts++;
    }

    return $patientId;
}

/**
 * Check if a patient ID already exists in the database.
 */
function patient_id_exists(PDO $pdo, string $patientId): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE patient_id = ?");
    $stmt->execute([$patientId]);
    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Generate a secure access token for patient portal.
 *
 * @return string Random hex token (32 characters)
 */
function generate_access_token(): string {
    return bin2hex(random_bytes(16));
}
?>
