<?php

// use isszz\captcha\rotate\facade\Captcha;

if (!function_exists('rotate_captcha')) {
    /**
     * @param string $image
     * @param string $uploadPath
     * @param int $size
     * @return array
     */
    function rotate_captcha(string $image = '', string $uploadPath = null, int $size = 350): array
    {
        return \isszz\captcha\rotate\facade\Captcha::create($image, $uploadPath)->get($size);
    }
}

if (!function_exists('rotate_captcha_check')) {
    /**
     * @param string $value
     * @param string $value
     * @return bool
     */
    function rotate_captcha_check(string $token, string $value): bool
    {
        return \isszz\captcha\rotate\facade\Captcha::check($token, $value);
    }
}

if (!function_exists('rotate_captcha_output')) {
    /**
     * @param string $value
     * @param string $uploadPath
     * @return array
     */
    function rotate_captcha_output(string $value, string $uploadPath = null): array
    {
        return \isszz\captcha\rotate\facade\Captcha::output($value, $uploadPath);
    }
}

if (! function_exists('array_get')) {
	function array_get($array, $key, $default = null)
	{
		if (is_null($key)) return $array;

		// To retrieve the array item using dot syntax, we'll iterate through
		// each segment in the key and look for that value. If it exists, we
		// will return it, otherwise we will set the depth of the array and
		// look for the next segment.
		foreach (explode('.', $key) as $segment) {
			if ( ! is_array($array) or ! array_key_exists($segment, $array)) {
				return value($default);
			}

			$array = $array[$segment];
		}

		return $array;
	}
}
