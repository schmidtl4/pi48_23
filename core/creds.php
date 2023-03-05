<?php

$creds_file = 	DB_ROOT.'creds';

if (file_exists($creds_file)) {
    $secret = $_ENV['server_secret'] ?? "Jt2&)2P^PVwjhvvG";
    $creds_input = file_get_contents($creds_file);

    list($crypted_token, $enc_iv) = explode("::", $creds_input);
    $cipher_method = 'AES-128-CTR';
    $enc_key = openssl_digest($secret, 'SHA256', TRUE);
    $token = openssl_decrypt($crypted_token, $cipher_method, $enc_key, 0, hex2bin($enc_iv));
    unset($crypted_token, $cipher_method, $enc_key, $enc_iv, $secret);

    $creds = explode("\n", $token);
    unset($token);

    foreach ($creds as $line) {
        $n = strpos($line, '=');
        if ($n !== false) {
            $key = trim(substr($line, 0, $n));
            $value = trim(substr($line, $n+1));

            define($key, $value);
            unset($key, $value);
        }
        unset($line);
    }
    unset($creds);
}
