<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate;

class Crypt
{
	/**
	 * Encryption
	 *
	 * @param string $data String to be encrypted
	 * @param string $key Encryption key
	 * @param int $expire Expiration time in seconds
	 * @return string
	 */
	public static function encode(string|int $data, string $key = '', int $expire = 0): string
	{
	    $key = md5(empty($key) ? 'cfyun1028~(^.^)' : $key);

	    $data = base64_encode($data);
	    $x = 0;
	    $len = strlen($data);
	    $l = strlen($key);
	    $char = '';

	    for ($i = 0; $i < $len; $i++) {
	        if ($x == $l)
	            $x = 0;
	        $char .= substr($key, $x, 1);
	        $x++;
	    }

	    $str = sprintf('%010d', $expire ? $expire + time() : 0);

	    for ($i = 0; $i < $len; $i++) {
	        $str .= chr(ord(substr($data, $i, 1)) + ( ord(substr($char, $i, 1)) ) % 256);
	    }

	    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($str));
	}

	/**
	 * Decrypt
	 *
	 * @param string $data The string to be decrypted (must be a string encrypted by the encode method)
	 * @param string $key Encryption key
	 * @return string
	 */
	public static function decode(string|int $data, string $key = ''): string
	{
	    $key = md5(empty($key) ? 'cfyun1028~(^.^)' : $key);
	    $data = str_replace(['-', '_'], ['+', '/'], $data);
	    $mod4 = strlen($data) % 4;

	    if ($mod4) {
	        $data .= substr('====', $mod4);
	    }

	    $data = base64_decode($data);
	    $expire = substr($data, 0, 10);
	    $data = substr($data, 10);

	    if ($expire > 0 && $expire < time()) {
	        return '';
	    }

	    $x = 0;
	    $len = strlen($data);
	    $l = strlen($key);
	    $char = $str = '';

	    for ($i = 0; $i < $len; $i++) {
	        if ($x == $l)
	            $x = 0;
	        $char .= substr($key, $x, 1);
	        $x++;
	    }

	    for ($i = 0; $i < $len; $i++) {
	        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
	            $str .= chr(( ord(substr($data, $i, 1)) + 256 ) - ord(substr($char, $i, 1)));
	        } else {
	            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
	        }
	    }

	    return base64_decode($str);
	}

}
