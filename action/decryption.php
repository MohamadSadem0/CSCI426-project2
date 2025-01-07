<?php
class Decryption {
    private $key;
    private $cipher = "AES-256-CBC";
    private $options = OPENSSL_RAW_DATA;

    public function __construct() {
        // Use the same key as encryption
        $this->key = "your-32-character-secret-key-here";
    }

    public function decrypt($encryptedData) {
        try {
            // Decode the base64 encoded data
            $combined = base64_decode($encryptedData);
            
            // Get the IV size
            $ivlen = openssl_cipher_iv_length($this->cipher);
            
            // Extract IV and encrypted data
            $iv = substr($combined, 0, $ivlen);
            $encrypted = substr($combined, $ivlen);

            // Decrypt the data
            $decrypted = openssl_decrypt(
                $encrypted, 
                $this->cipher, 
                $this->key, 
                $this->options, 
                $iv
            );

            if ($decrypted === false) {
                throw new Exception("Decryption failed");
            }

            return $decrypted;
        } catch (Exception $e) {
            error_log("Decryption error: " . $e->getMessage());
            return false;
        }
    }

    public function decryptId($encryptedId) {
        try {
            $decrypted = $this->decrypt($encryptedId);
            if ($decrypted === false) {
                return false;
            }

            // Split the decrypted data
            $parts = explode('|', $decrypted);
            if (count($parts) !== 3) {
                return false;
            }

            return $parts[0]; // Return only the ID
        } catch (Exception $e) {
            error_log("ID Decryption error: " . $e->getMessage());
            return false;
        }
    }
}
?>