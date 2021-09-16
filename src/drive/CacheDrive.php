<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\drive;

use isszz\captcha\rotate\Drive;
use isszz\captcha\rotate\support\Str;

use think\facade\Cache;

class CacheDrive extends Drive
{
    public function get(string $token): array
    {
        if(!Cache::has($token)) {
            return [];
        }

        $payload = Cache::get($token);

        if(empty($payload)) {
            return [];
        }

        $payload = $this->encrypter->decrypt($payload);

        if(empty($payload)) {
            return [];
        }

        Cache::delete($token);
        return json_decode($payload, true);
    }
    
    public function put(?int $degrees): string
    {
        $token = Str::random(32, 'alnum');

        $payload = $this->buildPayload($degrees);

        Cache::set($token, $payload, $this->ttl);

        return $token;
    }
}
