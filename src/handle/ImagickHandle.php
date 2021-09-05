<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\handle;

use \Imagick;
use \ImagickDraw;
use \ImagickPixel;

use isszz\captcha\rotate\Handle;
use isszz\captcha\rotate\CaptchaException;

class ImagickHandle extends Handle
{
	public function __construct($image, $config = [])
	{
		if(!extension_loaded('imagick')) {
			throw new CaptchaException('Need to support Imagick extension.');
		}

		$this->image = $image;
		$this->config = $config;

		return $this;
	}

	/**
	 * Get image info
	 *
	 * @param string $filePath
	 * @return array
	 */
	public function getInfo(string $filePath = null): array
	{
		if(is_null($filePath)) {

			if(is_null($this->front)) {
				$this->createFront();
			}

			$info = $this->front->getImageGeometry();
			$info['mime'] = $this->front->getimagemimetype();
			$info['type'] = $this->getFileExt();
		} else {
			if (!is_file($filePath)) {
				throw new CaptchaException('Image does not exist.');
			}

			$_image = new Imagick($filePath);

			$info = $_image->getImageGeometry();
			$info['mime'] = $_image->getimagemimetype();
			$info['type'] = $this->getFileExt($filePath);

			$_image->clear();
			$_image->destroy();
		}

		if (!in_array($info['mime'], ['image/jpeg', 'image/png'])) {
			throw new CaptchaException('Please use jpeg or png images.');
		}

		return $this->info = $info;
	}

	/**
	 * Save image
	 *
	 * @param int $size
	 * @return bool
	 */
	public function save($size = 350): bool
	{
        if (!$this->build($size) || !$this->back) {
            return false;
        }

		// $filepath = $this->cacheFilePath . '1-1-1.jpg';

		// header('Content-type: image/png');
		// exit($this->front->getImageBlob());

		$this->back->writeImage($this->cacheFilePath);

		$this->back->clear();
		$this->back->destroy();

		return true;
	}

	/**
	 * Build rotate image
	 *
	 * @param int $size
	 * @return bool
	 */
	public function build($size = 350): bool
	{
		/*
		if(!$this->info || is_null($this->front)) {
			return false;
		}
		*/
		if(($sizes = $this->calcSize($size)) && $sizes === false) {
			return false;
		}

		[$src_w, $src_h, $dst_w, $dst_h, $dst_scale, $src_scale, $w, $h, $x, $y] = $sizes;

		// Cut image
		$this->front->thumbnailImage($src_w, $src_h, true);
		$this->front->cropImage($w, $h, $x, $y);

		// Set mask
		$mask = new Imagick();
		$mask->newImage($w, $h, new ImagickPixel('transparent'), 'png');
		// Create the rounded rectangle
		$shape = new ImagickDraw();
		$shape->setFillColor(new ImagickPixel('white'));
		$shape->roundRectangle(0, 0, $w, $h, $w, $w);
		// Draw the rectangle
		$mask->drawImage($shape);
		// Apply mask
		$this->front->setImageMatte(1);
		$this->front->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);
		// Rotate image
		$this->front->rotateImage(new ImagickPixel('none'), $this->degrees);

		// Cut image
		$info = $this->front->getImageGeometry();

		$x = intval(($info['width'] - $w) / 2);
		$y = intval(($info['height'] - $h) / 2);

		$this->front->thumbnailImage($info['width'], $info['height'], true);
		$this->front->cropImage($w, $h, $x, $y);

		// Zoom
		$scale = $dst_w / $w;

		$final_w = intval($w * $scale);
		$final_h = intval($h * $scale);

		$this->front->thumbnailImage($final_w, $final_h, true);
		$this->front->cropimage($w, $h, 0, 0);

		// Processing compression
		if(empty($this->config['compress'])) {
			if(empty($this->config['bgcolor'])) {
				$this->config['bgcolor'] = 'white';
			}

			// Convert to jpg
			$this->back = new Imagick();
			$this->back->newImage($final_w, $final_h, new ImagickPixel($this->config['bgcolor']));

			$this->back->compositeImage($this->front, Imagick::COMPOSITE_OVER, 0, 0);

			// Compressed size, average 30kb, shortcomings cannot be transparent
			$this->back->setImageFormat('jpg');
			$this->back->setImageCompression(Imagick::COMPRESSION_JPEG);
			$this->back->setImageCompressionQuality($this->config['quality'] ?: 80);

		} else {
			// Delete picture information
			$this->front->stripImage();

			if($this->config['compress'] == 1) {
				// PNG can be compressed by more than 2 times, with an average of about 90kb, the disadvantage is that it loses too many pixels
				$this->front->setImageType(Imagick::IMGTYPE_PALETTEMATTE);
			} else {
				// Losslessly compress png, only a few K- -...
				// $this->front->setImageFormat('png');
				$this->front->setImageAlphaChannel(Imagick::COLOR_BLACK);
				$this->front->setImageCompression(Imagick::COMPRESSION_ZIP);
				// $this->front->setImageCompressionQuality(9);
				$this->front->setOption('png:compression-level', 9);

			}

			$this->back = $this->front;
		}

		$this->front->clear();
		$this->front->destroy();

		return true;
	}

	/**
	 * Create image
	 *
	 * @return bool
	 */
	public function createFront(): bool
	{
		$this->front = new Imagick($this->image);

		return true;
	}
}
