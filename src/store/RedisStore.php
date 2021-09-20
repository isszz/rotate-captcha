<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\store;

use isszz\captcha\rotate\Store;
use isszz\captcha\rotate\support\Redis;

class RedisStore extends Store
{
	/**
	 * Get token
	 * 
	 * @param string $token
	 * @return string
	 */
	public function get(string $token): array
	{
        $redis = Redis::connection($this->captcha->config('redis'));

		if(!$redis->has(self::TOKEN_PRE . $token)) {
			return [];
		}

		$payload = $redis->get(self::TOKEN_PRE . $token);

		if(empty($payload)) {
			return [];
		}

		$payload = $this->encrypter->decrypt($payload);

		if(empty($payload)) {
			return [];
		}

		$redis->forget(self::TOKEN_PRE . $token);

		return json_decode($payload, true);
	}
	
	/**
	 * Storage token
	 * 
	 * @param int|float|string $degrees
	 * @return string
	 */
	public function put(?int $degrees): string
	{
		[$token, $payload] = $this->buildPayload($degrees);

        $redis = Redis::connection($this->captcha->config('redis'));
		$redis->put(self::TOKEN_PRE . $token, $payload, $this->ttl);

		return $token;
	}
}
