<?php
class Encryption {
    private $key;
    private $cipher = "AES-256-CBC";
    private $options = OPENSSL_RAW_DATA;

    public function __construct() {
        $this->key = "your-32-character-secret-key-here";
    }

    public function encrypt($data) {
        try {
            $ivlen = openssl_cipher_iv_length($this->cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);

            $encrypted = openssl_encrypt(
                $data, 
                $this->cipher, 
                $this->key, 
                $this->options, 
                $iv
            );

            if ($encrypted === false) {
                throw new Exception("Encryption failed");
            }

            $combined = $iv . $encrypted;

            return base64_encode($combined);
        } catch (Exception $e) {
            error_log("Encryption error: " . $e->getMessage());
            return false;
        }
    }

    public function encryptId($id) {
        try {
            $timestamp = time();
            $random = bin2hex(random_bytes(16));
            $data = $id . '|' . $timestamp . '|' . $random;
            return $this->encrypt($data);
        } catch (Exception $e) {
            error_log("ID Encryption error: " . $e->getMessage());
            return false;
        }
    }
}
?>