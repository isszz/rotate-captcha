<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\support\encrypter;

class Encrypter extends EncrypterBase
{
    /**
     * The algorithm used for encryption.
     *
     * @var string
     */
    protected $cipher;

    /**
     * Create a new encrypter instance.
     *
     * @param  string  $key
     * @param  string  $cipher
     * @return void
     *
     * @throws \Exception
     */
    public function __construct($key, $cipher = 'AES-128-CBC')
    {
        $key =  md5((string) $key);
        if ($this->key = self::supported($key, $cipher)) {
            $this->cipher = $cipher;
        } else {
            throw new \Exception('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');
        }
    }

    /**
     * Determine if the given key and cipher combination is valid.
     *
     * @param  string  $key
     * @param  string  $cipher
     * @return bool
     */
    public static function supported($key, $cipher)
    {
        $length = mb_strlen($key, '8bit');
        if ($cipher === 'AES-128-CBC') {

            if ($length === 16) {
                return $key;
            }

            if ($length > 16) {
                return substr($key, 0, 16);
            }

        }

        if ($cipher === 'AES-256-CBC' && $length === 32)
        {
            return $key;
        }

        return false;
    }

    /**
     * Encrypt the given value.
     *
     * @param  string  $value
     * @return string
     *
     * @throws \Exception
     */
    public function encrypt($value)
    {
        $iv = openssl_random_pseudo_bytes($this->getIvSize());

        $value = openssl_encrypt(serialize($value), $this->cipher, $this->key, 0, $iv);

        if ($value === false) {
            throw new \Exception('Could not encrypt the data.');
        }

        // Once we have the encrypted value we will go ahead base64_encode the input
        // vector and create the MAC for the encrypted value so we can verify its
        // authenticity. Then, we'll JSON encode the data in a "payload" array.
        $mac = $this->hash($iv = base64_encode($iv), $value);

        $json = json_encode(compact('iv', 'value', 'mac'));

        if (! is_string($json))
        {
            throw new \Exception('Could not encrypt the data.');
        }

        return base64_encode($json);
    }

    /**
     * Decrypt the given value.
     *
     * @param  string  $payload
     * @return string
     *
     * @throws \Exception
     */
    public function decrypt($payload)
    {
        $payload = $this->getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);

        $decrypted = openssl_decrypt($payload['value'], $this->cipher, $this->key, 0, $iv);

        if ($decrypted === false) {
            throw new \Exception('Could not decrypt the data.');
        }

        return unserialize($decrypted);
    }

    /**
     * Get the IV size for the cipher.
     *
     * @return int
     */
    protected function getIvSize()
    {
        return 16;
    }
}