<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate;

use think\Config;
use think\Session;

class Captcha
{
	/**
	 * The image path used to generate the rotate captcha image
	 */
    private $image = '';

	/**
	 * Image information
	 */
    private $info = [];

	/**
	 * Configuration
	 */
    private $config = [];

	/**
	 * The hash generated according to the angle
	 */
    private $hash = '';

	/**
	 * Rotation angle
	 */
    private $degrees = 30;

	/**
	 * Whether the same angle of the image has been generated
	 */
    private $existed = false;

	/**
	 * Image handle class
	 */
    private $handle = null;

	/**
	 * Store the path of the generated image
	 */
    private $uploadPath = null;

	/**
	 * Image Cache path
	 */
	private $cachePath = null;
	private $cacheFilePath = null;

    public function __construct(Config $config, Session $session)
    {
        if(!extension_loaded('gd') && !extension_loaded('imagick')) {
            throw new CaptchaException('Need to support GD or Imagick extension.');
        }

        $this->_config = $config;
        $this->_session = $session;

        $this->handleName = 'gd';

        if(extension_loaded('imagemagick')) {
            $this->handleName = 'imagick';
        }

        return $this;
    }

    /**
     * Create captcha rotate image
     *
     * @param string $image
     * @return object
     */
    public function create($image = '', $uploadPath = null): Captcha
    {
		if (empty($image)) {
            throw new CaptchaException('Please pass in the material image.');
        }

        if(is_null($uploadPath)) {
            throw new CaptchaException('Please configure the uploadPath parameter.');
        }

        $this->image = $image;
        $this->uploadPath = $uploadPath;

        $this->config = $this->getConfig();

		if (!is_file($this->image)) {
            throw new CaptchaException('Material image does not exist.');
        }

        // Create image handle class
        $this->handle();
        // Build the necessary parameters
        $this->buildBase();

        // Image from the same angle
        if(is_file($this->cacheFilePath)) {
            $this->existed = true;
            $this->info = $this->handle->getInfo($this->cacheFilePath);

        } else {
            // Get image information
            $this->info = $this->handle->getInfo();

            if(is_null($this->handle->front) && !$this->handle->createFront()) {
                throw new CaptchaException('Failed to create image.');
            }

            // Create a directory
            $this->createFolder(dirname($this->cacheFilePath));
        }

        $this->info['hash'] = $this->hash;
        $this->info['angle'] = $this->degrees;
        $this->info['path'] = $this->cachePath;
        $this->info['cache'] = $this->cacheFilePath;

        return $this;
    }

    /**
     * Generate verification pictures and obtain relevant information
     * 
     * @return array
     */
    public function get($size = 350): array
    {
        $this->size = $size ?? 350;

        if($this->size < 150) {
            $this->size = 150;
        }

        // Image, already has the same angle
        if($this->existed) {
            $this->size = min($this->info['width'], $this->info['height']);
            return $this->info();
        }
        // Save image
        $this->handle->save($this->size);
        // Set global size
        $this->size = $this->handle->size;

        return $this->info();
    }

    /**
     * Get information about the generated image
     * 
     * @return array
     */
    public function info()
    {
        return [
            'hash' => $this->info['hash'],
            // 'path' => $this->info['path'],
            'str' => Crypt::encode($this->info['path']),
            'angle' => $this->info['angle'],
            // 'cache' => $this->info['cache'],
            'type' => $this->info['type'],
            'size' => $this->size,
            // 'width'  => $this->info['width'],
            // 'height' => $this->info['height'],
        ];
    }

    /**
     * Build the necessary parameters
     */
    public function buildBase()
    {
        // Get random angle, generate angle hash
        $this->degrees = (string) rand(30, 270);
        // $this->degrees = 70;
        $this->hash = Crypt::encode($this->degrees, $this->config['salt'], 300);
        
        $this->_session->set('captcha_rotate', $this->hash);

        // dd(decrypt('eyJpdiI6ImdIbk9NK0VZUTB2TjZrdStnWnJsZHc9PSIsInZhbHVlIjoicHJOdGh0aFwvSXpOYW5IY3hvcFQ2M3c9PSIsIm1hYyI6IjU4MTE0YzlkMDQxYjNjMjQxZjYwMjA5YTI5NGQxODhhNWQ2NjA3NTQzOWViNGY5NzVkZDVkNGJjZjRhMzY3NGQifQ==', 'cfyun@isszz#rotateVerify'));

        if(is_null($this->uploadPath)) {
            throw new CaptchaException('Please use the setUploadPath method to configure uploadPath parameters.');
        }

        $this->cachePath = date('ym', time()) . '/' . md5(md5($this->degrees) . md5($this->image . 'cfyun') . 'cfyun.cc') . $this->handle->getFileExt($this->image, false);

        $this->cacheFilePath = $this->uploadPath . $this->cachePath;

        $this->handle->setCachePathAndDegrees($this->cacheFilePath, $this->degrees);
    }

    /**
     * Check if it is rotated to the correct angle
     *
     * @param string $angle
     * @return array
     */
    public function check($angle = null): bool
    {
        if(empty($angle)) {
            return false;
        }

        $hash = $this->_session->get('captcha_rotate');

        if(empty($hash)) {
            return false;
        }
        
        $this->config = $this->getConfig();

        $_angle = Crypt::decode($hash, $this->config['salt']);

        if(empty($_angle)) {
            return false;
        }

        $angle = (float) $angle;

        if($angle == $_angle) {
            return true;
        }

        if($angle > $_angle && $angle - $_angle < $this->config['sarea']) {
            return true;
        }

        if($angle < $_angle && $_angle - $angle < $this->config['sarea']) {
            return true;
        }

        return false;
    }

    /**
     * According to the encrypted string, get the image content data
     *
     * @param string $str
     * @param string $uploadPath
     * @return array
     */
    public function img(string $str = '', string $uploadPath = null): array
    {
        if(empty($str)) {
            return [null, ''];
        }

        try {
            $str = Crypt::decode($str);
        } catch (\Exception $e) {
            return [null, ''];
        }

        if(is_null($uploadPath)) {
            throw new CaptchaException('Please set uploadPath parameter.');
        }

        $filepath = $uploadPath . $str;

        if(!is_file($filepath)) {
            return [null, ''];
        }

        $format = strrchr($str, '.');

        ob_start();
        @readfile($filepath);
        $image  = ob_get_contents();
        ob_end_clean();

        return [$format, $image];
    }

    /**
     * Get configuration
     *
     * @return array
     */
    private function getConfig(): array
    {
        return array_merge($this->config, $this->_config->get('rotateCaptcha'));
    }

    /**
     * Get an example of image processing
     *
     * @return object
     */
    private function handle()
    {
        if(!is_null($this->handle)) {
            return $this->handle;
        }

        if($this->handleName === 'imagick') {
            return $this->handle = new \isszz\captcha\rotate\handle\ImagickHandle($this->image, $this->config['imagick']);
        }

        return $this->handle = new \isszz\captcha\rotate\handle\GdHandle($this->image, $this->config['gd']);
    }

	/**
	 * Create a directory
	 *
	 * @param string $dirname
	 * @return bool
	 */
	private function createFolder($dirname)
	{
		if (!file_exists($dirname) && !is_dir($dirname) && !mkdir($dirname, 0777, true)) {
			throw new CaptchaException('Directory creation failed.');
			return;
		} else if (!is_writeable($dirname)) {
			throw new CaptchaException('The directory does not have write permission.');
			return;
		}

		@chmod($dirname, 0777);
		@fclose(@fopen($dirname . '/index.html', 'w'));
		@chmod($dirname . '/index.html', 0777);

		return true;
	}

    /**
     * Get the captcha image file content|For testing
     * 
     * @return string
     */
    public function __toString()
    {
        // Image, already has the same angle
        if($this->existed) {
            $this->size = min($this->info['width'], $this->info['height']);
        } else {
            $this->handle->create();
            $this->size = $this->handle->size;
        }
        
        ob_start();
        @readfile($this->info['cache']);
        $content  = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}