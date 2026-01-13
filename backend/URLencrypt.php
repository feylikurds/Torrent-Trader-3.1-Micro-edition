<?php
   //openSSL torrent encryption

class OpensslAES
{
    const METHOD = 'aes-256-cbc';

public static function encrypt($message, $key) {
    $self = new self();
        list($encKey, $authKey) = $self->splitKeys($key);

    $ivsize = openssl_cipher_iv_length(self::METHOD);
    $iv = openssl_random_pseudo_bytes($ivsize);

    $ciphertext = openssl_encrypt (
        $message,
        self::METHOD,
        $encKey,
        OPENSSL_RAW_DATA,
        $iv
    );
        $mac = hash_hmac('sha256', $iv.$ciphertext, $authkey, true);

        return $mac.$iv.$ciphertext;
}

public static function decrypt($message, $key) {
		$self = new self();
        list($encKey, $authKey) = $self->splitKeys($key);

        $ivsize = openssl_cipher_iv_length(self::METHOD);
        $mac = mb_substr($message, 0, 32, '8bit');
        $iv = mb_substr($message, 32, $ivsize, '8bit');
        $ciphertext = mb_substr($message, 32 + $ivsize, null, '8bit');

        // Very important: Verify MAC before decrypting
        $calc = hash_hmac('sha256', $iv.$ciphertext, $authkey, true);
if (!hash_equals($mac, $calc)) {
            throw new Exception('MAC Validation failed');
}

        return openssl_decrypt(
        $ciphertext,
        self::METHOD,
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );
}

public function splitKeys($masterKey) {
        // You probably want RFC 5869 HKDF here instead
       return [
        hash_hmac('sha256', 'encryption', $masterKey, true),
        hash_hmac('sha256', 'authentication', $masterKey, true),
//		hash_hmac('sha512', 'encryption', $masterKey, true),
//		hash_hmac('sha512', 'authentication', $masterKey, true)
		   ];
}
}

    // OpenSSL URl key encryption for download
    $key = "\xd8\x75\x26\xd5\x59\x45\x47\x1b\x02\x13\x13\xa5\xa8\x4d\x61\xd8\x94\xb0\x87\x60\x40\x2f\x29\x63\x2f\x13\x9c\xc3\x42\x88\xf1\xe5";
    $message = $row["filename"];
   $OpensslAES = new OpensslAES();
    $safe_data = $OpensslAES->encrypt($message, $key);
//    $safe_data = OpensslAES::encrypt($message, $key);
    //$download_title = urlencode(base64_decode($safe_data));

    // URl Encryption Fix - Above was bad implementation
    // Nov, 27 2015 @ Update 2
    $download_title = rtrim(strtr(base64_encode($safe_data), '+/', '-_'), '=');
////end torrent encryption
?>