<?php

use isszz\captcha\rotate\facade\Captcha;

if (!function_exists('rotate_captcha')) {
    /**
     * @param string $image
     * @param string $uploadPath
     * @param int $size
     */
    function rotate_captcha(string $image = '', string $uploadPath = null, int $size = 350): array
    {
        return Captcha::create($image, $uploadPath)->get($size);
    }
}

if (!function_exists('rotate_captcha_check')) {
    /**
     * @param string $value
     * @return bool
     */
    function rotate_captcha_check(string $value): bool
    {
        return Captcha::check($value);
    }
}

if (!function_exists('rotate_captcha_img')) {
    /**
     * @param string $value
     * @param string $uploadPath
     * @return string
     */
    function rotate_captcha_img(string $value, string $uploadPath = null): array
    {
        return Captcha::img($value, $uploadPath);
    }
}