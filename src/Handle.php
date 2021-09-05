<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate;

use isszz\captcha\rotate\interface\HandleInterface;

abstract class Handle implements HandleInterface
{
    public $info = [];
    public $image = '';

    public $size = 350;
    public $back = null;
    public $front = null;

	public function calcSize($size = 350)
	{
		if(!$this->info || is_null($this->front)) {
			return false;
		}

		$this->size = $size ?? 350;

		// Minimum size of original image
		$src_min = min($this->info['width'], $this->info['height']);

		if($src_min < 160) {
			return false;
		}

		if($src_min < $this->size) {
			$this->size = $src_min;
		}

		$src_w = $this->info['width'];
		$src_h = $this->info['height'];

		$dst_w = $dst_h = $this->size;

		$dst_scale = $dst_h / $dst_w; // Target image ratio
		$src_scale = $src_h / $src_w; // Original image aspect ratio

		if ($src_scale >= $dst_scale) { // Too high
			$w = intval($src_w);
			$h = $w;
			$x = 0;
			$y = (int) round(($src_h - $h) / 2);
		} else {
			$h = intval($src_h);
			$w = $h;
			$x = (int) round(($src_w - $w) / 2);
			$y = 0;
		}

		return [$src_w, $src_h, $dst_w, $dst_h, $dst_scale, $src_scale, $w, $h, $x, $y];
	}

    /**
     * Set the processed image path and rotation angle to the handle class
     * 
     * @param string $cacheFilePath
     * @param int $degrees
     * @return bool
     */
	public function setCachePathAndDegrees($cacheFilePath = null, $degrees = 0)
	{
		if (empty($cacheFilePath)) {
			throw new CaptchaException('The path of the cached image cannot be empty.');
		}

		if (empty($degrees) || $degrees < 30) {
			throw new CaptchaException('The degrees of rotation cannot be less than 30.');
		}

		$this->degrees = (float) $degrees;
		$this->cacheFilePath = $cacheFilePath;

		return true;
	}

	/**
	 * Get file extension
	 * 
     * @param string $filePath
     * @param bool $isIgnoreAfter
	 * @return string
	 */
	public function getFileExt($filePath = null, $isIgnoreAfter = true)
	{
		$ext = strtolower(strrchr((is_null($filePath) ? $this->image : $filePath), '.'));

		if($isIgnoreAfter) {
			return $ext;
		}

		if(empty($this->config['compress']) && ($ext != '.jpg' || $ext != '.jepg')) {
			$ext = '.jpg';
		}

		return $ext;
	}

	/**
	 * Hexadecimal color to RGB
	 * 
	 * @param string $color
     * @param bool $isReturnString
	 * @return string|array
	 */
	public function hex2rgb($color, $isReturnString = true)
	{
		$hexColor = str_replace('#', '', $color);
		$lens = strlen($hexColor);

		if ($lens != 3 && $lens != 6) {
			return false;
		}

		$newColor = '';

		if ($lens == 3) {
			for ($i = 0; $i < $lens; $i++) {
				$newColor .= $hexColor[$i] . $hexColor[$i];
			}
		} else {
			$newColor = $hexColor;
		}

		$rgb = [];
		$hex = str_split($newColor, 2);

		foreach ($hex as $key => $vls) {
			$rgb[] = hexdec($vls);
		}
        
        if($isReturnString) {
            return implode(', ', $rgb);
        }

		return $rgb;
	}
}