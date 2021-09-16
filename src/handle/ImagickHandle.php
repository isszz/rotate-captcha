<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\handle;

use \Imagick;
use \ImagickDraw;
use \ImagickPixel;

use isszz\captcha\rotate\Handle;
use isszz\captcha\rotate\Captcha;
use isszz\captcha\rotate\CaptchaException;

class ImagickHandle extends Handle
{
	public function __construct(Captcha $captcha, string $image, array $config = [])
	{
		if(!extension_loaded('imagick')) {
			throw new CaptchaException($captcha->lang()->get('Need to support Imagick extension.'));
		}

		$this->captcha = $captcha;
		$this->image = $image;
		$this->config = $config;
        $this->outputMime = $this->captcha->getMime();
        $this->outputType = $this->getExt();

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
				throw new CaptchaException($this->captcha->lang()->get('Image does not exist.'));
			}

			$_image = new Imagick($filePath);

			$info = $_image->getImageGeometry();
			$info['mime'] = $_image->getimagemimetype();
			$info['type'] = $this->getFileExt($filePath);

			$_image->clear();
			$_image->destroy();
		}

		if (!in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'])) {
			throw new CaptchaException($this->captcha->lang()->get('Please use jpeg and png or webp images.'));
		}

		return $this->info = $info;
	}

	/**
	 * Save image
	 *
	 * @param int $size
	 * @return bool
	 */
	public function save(int $size = 350): bool
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
	public function build(int $size = 350): bool
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

		// Delete picture information
		$this->front->stripImage();

		// Jpg default white background
		if($this->outputMime == 'image/jpeg') {
			$this->config['bgcolor'] = $this->config['bgcolor'] ?: 'white';
		}

		if(empty($this->config['bgcolor'])) {
			$this->back = $this->front;
		} else {
			// Have a background
			$this->back = new Imagick();
			$this->back->newImage($final_w, $final_h, new ImagickPixel($this->config['bgcolor']));
			$this->back->compositeImage($this->front, Imagick::COMPOSITE_OVER, 0, 0);
		}
		
		// Conversion format
		$this->back->setImageFormat($this->outputType);

		if($this->outputMime == 'image/webp' || $this->outputMime == 'image/jpeg') {
			$this->back->setImageCompression(Imagick::COMPRESSION_JPEG);
			// $this->back->setImageCompression(Imagick::COMPRESSION_WEBP);
			$this->back->setImageCompressionQuality($this->config['quality'] ?: 80);
		} else {
			// PNG can be compressed by more than 2 times, with an average of about 90kb, the disadvantage is that it loses too many pixels
			// $this->back->setImageType(Imagick::IMGTYPE_PALETTEMATTE);

			// Losslessly compress png, only a few K- -...
			// $this->back->setImageFormat('png');
			$this->back->setImageAlphaChannel(Imagick::COLOR_BLACK);
			$this->back->setImageCompression(Imagick::COMPRESSION_ZIP);
			// $this->back->setImageCompressionQuality(9);
			$this->back->setOption('png:compression-level', 9);
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
