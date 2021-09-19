<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\store;

use isszz\captcha\rotate\Store;
use isszz\captcha\rotate\support\Str;

use think\facade\Cache;

class CacheStore extends Store
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