<?php

use isszz\captcha\rotate\facade\Captcha;

if (!function_exists('rotate_captcha')) {
    /**
     * @param string $image
     * @param string $uploadPath
     */
    function rotate_captcha($image = '', $uploadPath = null, $size = 350)
    {
        return Captcha::create($image, $uploadPath)->get($size);
    }
}

if (!function_exists('rotate_captcha_img')) {
    /**
     * @param string $value
     * @param string $uploadPath
     * @return bool
     */
    function rotate_captcha_check($value, $uploadPath = null)
    {
        return Captcha::img($value, $uploadPath);
    }
}

if (!function_exists('rotate_captcha_check')) {
    /**
     * @param string $value
     * @return bool
     */
    function rotate_captcha_check($value)
    {
        return Captcha::check($value);
    }
}
