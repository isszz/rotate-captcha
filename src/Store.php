<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate;

use isszz\captcha\rotate\interface\StoreInterface;
use isszz\captcha\rotate\support\request\Request;
use isszz\captcha\rotate\support\encrypter\Encrypter;

abstract class Store implements StoreInterface
{
    /**
     * @var Captcha
     */
    protected $captcha;

    /**
     * @var string
     */
    protected $secureKey;
    
    /**
     * @var int
     */
    protected $ttl;

    /**
     * @var string|null
     */
    protected $message = null;

    public function __construct(Captcha $captcha, Encrypter $encrypter, int $ttl)
    {
        $this->captcha = $captcha;
        $this->encrypter = $encrypter;
        $this->ttl = $ttl;
    }

    public function buildPayload(?int $degrees): ?string
    {
        $ua = Request::header('User-Agent');

        $payload = json_encode([
            'ds' => $degrees,
            'ip' => Request::ip(),
            'ua' => crc32($ua),
            'ttl' => time() + $this->ttl,
        ], JSON_UNESCAPED_UNICODE);

        return $this->encrypter->encrypt($payload);
    }

    abstract public function get(string $token): array;
    abstract public function put(?int $degrees): string;
}
