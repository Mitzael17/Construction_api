<?php

namespace core\controllers\base;

trait BaseMethods
{

    private $cryptMethod = 'AES-256-CBC';
    private $hashAlgoritm = 'sha256';
    private $hashLength = 32;

    protected function writeLog($file, $message) {

        file_put_contents('logs/' . $file, $message, FILE_APPEND);

    }

    protected function createLinkForImage($image) {

        return $this->protocol . '://' . $_SERVER['SERVER_NAME'] . PATH . UPLOAD_DIR . $image;

    }

    protected function encrypt($str) {

        $ivlen = openssl_cipher_iv_length($this->cryptMethod);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($str, $this->cryptMethod, CRYPT_KEY, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac($this->hashAlgoritm, $ciphertext_raw, CRYPT_KEY, true);
        $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );

        return $ciphertext;

    }

    protected function decrypt($str) {

        $str = base64_decode($str);
        $ivlen = openssl_cipher_iv_length($this->cryptMethod);
        $iv = substr($str, 0, $ivlen);
        $hmac = substr($str, $ivlen, $this->hashLength);
        $ciphertext_raw = substr($str, $ivlen+$this->hashLength);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $this->cryptMethod, CRYPT_KEY, OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac($this->hashAlgoritm, $ciphertext_raw, CRYPT_KEY, true);
        if (hash_equals($hmac, $calcmac)) {
            return $original_plaintext;
        } else {
            return false;
        }

    }

    protected function stringFieldsToInt(array $arr, array $except = ['password']): array {


        foreach ($arr as $key => $value) {

            if(is_array($value)) {
                $arr[$key] = $this->stringFieldsToInt($value);
                continue;
            }

            if(gettype($value) === 'string' && array_search($key, $except) === false && preg_match('/^\d+(\.\d+)?$/', $value)) {

                $arr[$key] = (int) $value;

            }

        }

        return $arr;

    }

}