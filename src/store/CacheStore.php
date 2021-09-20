<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\store;

use isszz\captcha\rotate\Store;
use think\facade\Cache;

class CacheStore extends Store
{
	/**
	 * Get token
	 * 
	 * @param string $token
	 * @return string
	 */
	public function get(string $token): array
	{
		if(!Cache::has(self::TOKEN_PRE . $token)) {
			return [];
		}

		$payload = Cache::get(self::TOKEN_PRE . $token);

		if(empty($payload)) {
			return [];
		}

		$payload = $this->encrypter->decrypt($payload);

		if(empty($payload)) {
			return [];
		}

		Cache::delete(self::TOKEN_PRE . $token);

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

		Cache::set(self::TOKEN_PRE . $token, $payload, $this->ttl);

		return $token;
	}
}
