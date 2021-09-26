<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate;

use isszz\captcha\rotate\support\request\Request;
use isszz\captcha\rotate\support\encrypter\Encrypter;
// use isszz\captcha\rotate\support\FileSystem;

class Captcha
{
	/**
	 * The image path used to generate the rotate captcha image
	 */
	private string $image = '';

	/**
	 * Image information
	 */
	private array $info = [];

	/**
	 * Configuration
	 */
	private array $config = [];

	/**
	 * Config drive class
	 */
	private ?object $configDrive = null;

	/**
	 * The token is the generated verification information
	 */
	private string $token = '';

	/**
	 * The hash generated according to the angle
	 */
	private string $hash = '';

	/**
	 * Rotation angle
	 */
	private int $degrees = 30;

	/**
	 * Whether the same angle of the image has been generated
	 */
	private bool $existed = false;

	/**
	 * Image handle class
	 */
	private ?object $handle = null;

	/**
	 * Store drive
	 */
	private ?object $store = null;

	/**
	 * encrypter
	 */
	private ?object $encrypter = null;

	/**
	 * lang
	 */
	private ?object $lang = null;

	/**
	 * Store the path of the generated image
	 */
	private ?string $uploadPath = null;

	/**
	 * Image Cache path
	 */
	private ?string $cachePath = null;
	private ?string $cacheFilePath = null;

	public const OUTPUT_PNG  = 'png';
	public const OUTPUT_JPEG = 'jpg';
	public const OUTPUT_WEBP = 'webp';

	public function __construct()
	{
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
	 * @return self
	 */
	public function create($image = '', $uploadPath = null): self
	{
		if(!extension_loaded('gd') && !extension_loaded('imagick')) {
			throw new CaptchaException($this->lang()->get('Need to support GD or Imagick extension.'));
		}

		if (empty($image)) {
			throw new CaptchaException($this->lang()->get('Please pass in the material image.'));
		}

		if(is_null($uploadPath)) {
			throw new CaptchaException($this->lang()->get('Please set uploadPath parameter.'));
		}

		$this->image = $image;
		$this->uploadPath = $uploadPath;

		if (!is_file($this->image)) {
			throw new CaptchaException($this->lang()->get('Material image does not exist.'));
		}

		// Create image handle class
		$this->handle();
		// Initialize store
		$this->store();
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
				throw new CaptchaException($this->lang()->get('Failed to create image.'));
			}

			// Create a directory
			$this->createFolder(dirname($this->cacheFilePath));
		}

		$this->info['token'] = $this->token;
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
	 * Check if it is rotated to the correct angle
	 *
	 * @param int|float|string $angle
	 * @return array
	 */
	public function check(string $token, int|float|string $angle = null): bool
	{
		if(empty($token) || empty($angle)) {
			return false;
		}

		$payload = $this->store()->get($token);

		if(empty($payload)) {
			return false;
		}

		if(!isset($payload['ttl']) || time() > $payload['ttl']) {
			throw new CaptchaException($this->lang()->get('Verification timed out.'));
		}

		if(!isset($payload['ip']) || Request::ip() !== $payload['ip']) {
			throw new CaptchaException($this->lang()->get('Invalid verification.'));
		}

		$ua = Request::header('User-Agent');
		if(!isset($payload['ua']) || crc32($ua) !== $payload['ua']) {
			throw new CaptchaException($this->lang()->get('Invalid verification.'));
		}

		if(!isset($payload['ds'])) {
			throw new CaptchaException($this->lang()->get('Validation error.'));
		}

		$angle = (float) $angle;
		$payload['ds'] = (int) $payload['ds'];

		if($angle == $payload['ds']) {
			return true;
		}

		$earea = $this->config('earea') ?: 10;

		if($angle > $payload['ds'] && $angle - $payload['ds'] < $earea) {
			return true;
		}

		if($angle < $payload['ds'] && $payload['ds'] - $angle < $earea) {
			return true;
		}

		throw new CaptchaException($this->lang()->get('Invalid verification.'));

		return false;
	}

	/**
	 * According to the encrypted string, get the image content data
	 *
	 * @param string $str
	 * @param string $uploadPath
	 * @return array
	 */
	public function output(?string $str = '', string $uploadPath = null): array
	{
		if(empty($str)) {
			return [null, ''];
		}

		try {
			$str = $this->encrypter()->decrypt($str);
		} catch (\Exception $e) {
			return [null, ''];
		}

		if(is_null($uploadPath)) {
			throw new CaptchaException($this->lang()->get('Please set uploadPath parameter.'));
		}

		$filepath = $uploadPath . $str;

		if(!is_file($filepath)) {
			return [null, ''];
		}

		$mime = 'image/'. pathinfo($str, PATHINFO_EXTENSION);

		ob_start();
		@readfile($filepath);
		$image  = ob_get_contents();
		ob_end_clean();

		return [$mime, $image];
	}

	/**
	 * Get information about the generated image
	 *
	 * @return array
	 */
	public function info(): array
	{
		return [
			'token' => $this->info['token'],
			// 'path' => $this->info['path'],
			'str' => $this->encrypter()->encrypt($this->info['path']),
			'angle' => $this->info['angle'], // Please do not display it
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
	public function buildBase(): void
	{
		// Get random angle, generate angle hash
		$this->degrees = rand(30, 270);
		// $this->degrees = 70;

		// Set token
		$this->token = $this->store()->put($this->degrees);

		if(is_null($this->uploadPath)) {
			throw new CaptchaException($this->lang()->get('Please set uploadPath parameter.'));
		}

		$this->cachePath = date('ym', time()) . '/' . md5(md5((string) $this->degrees) . md5($this->image . 'cfyun') . 'cfyun.cc') . $this->handle->getFileExt($this->image, false);

		$this->cacheFilePath = $this->uploadPath . $this->cachePath;

		$this->handle->setCachePathAndDegrees($this->cacheFilePath, $this->degrees);
	}

	/**
	 * Set config drive
	 *
	 * @param string $config
	 * @return self
	 */
	public function configDrive(string $config): self
	{
		// dd($config, !is_string($config), !class_exists($config), !is_subclass_of($config, Config::class));
		if (!is_string($config) || !class_exists($config) || !is_subclass_of($config, Config::class)) {
			throw new CaptchaException($this->lang()->get('Config driver :driver does not exist.', ['driver' => $config]));
		}

		$this->configDrive = new $config;

		return $this;
	}

	/**
	 * Get configuration
	 *
	 * @param string $name
	 * @param string $defaultValue
	 * @return array|string|null
	 */
	public function config(string|null $name = null, string|null $defaultValue = null): mixed
	{
		if (!is_object($this->configDrive) && !is_subclass_of($this->configDrive, Config::class)) {

			if(!class_exists(\think\App::class) || strpos(\think\App::VERSION, '6.0') === false) {
				throw new CaptchaException($this->lang()->get('Config driver :driver does not exist.', [
					'driver' => is_object($this->configDrive) ? get_class($this->configDrive) : $this->configDrive
				]));
			}

			// Default config drive is thinkphp6.0.x
			$this->configDrive = new \isszz\captcha\rotate\config\Think();
		}

		$config = array_merge($this->config, $this->configDrive->get('rotateCaptcha'));


		if(is_null($name)) {
			return $config;
		}

		return array_get($config, $name, null);
	}

	/**
	 * Set language
	 *
	 * @param string|null $language
	 * @param string|null $file
	 * @return self
	 */
	public function setLang(string $language = 'en', ?string $file = null): self
	{
		$this->lang($language, $file);

		return $this;
	}

	/**
	 * Initialize language
	 *
	 * @param string|null $language
	 * @param string|null $file
	 * @return object
	 */
	public function lang(string $language = 'en', ?string $file = null): object
	{
		if(!is_null($this->lang)) {
			return $this->lang;
		}

		if(empty($file)) {
		   $file = __DIR__ . DIRECTORY_SEPARATOR .'lang'. DIRECTORY_SEPARATOR .'lang.php';
		}

		return $this->lang = Lang::line($file, $language);
	}

	/**
	 * Initialize image processing handle
	 *
	 * @return object
	 */
	private function handle(): object
	{
		if(!is_null($this->handle)) {
			return $this->handle;
		}

		if($this->handleName === 'imagick') {
			return $this->handle = new \isszz\captcha\rotate\handle\ImagickHandle($this, $this->image, $this->config('imagick'));
		}

		return $this->handle = new \isszz\captcha\rotate\handle\GdHandle($this, $this->image, $this->config('gd'));
	}

	/**
	 * Initialize storage drive
	 *
	 * @return object
	 */
	private function store(): object
	{
		if(!is_null($this->store)) {
			return $this->store;
		}

		$class = $this->config('store') ?? '';

		if (!is_string($class) || !class_exists($class) || !is_subclass_of($class, Store::class)) {
			throw new CaptchaException($this->lang()->get('Captcha storage drive class: :class invalid.', ['class' => $class]));
		}

		return $this->store = new $class($this, $this->encrypter(), $this->config('expire'));
	}

	/**
	 * Initialize encrypter
	 *
	 * @return object
	 */
	private function encrypter(): object
	{
		if(!is_null($this->encrypter)) {
			return $this->encrypter;
		}

		return $this->encrypter = new Encrypter($this->config('salt'));
	}

	/**
	 * Get output mime
	 *
	 * @return string
	 */
	public function getMime(): string
	{
		$outputType = $this->config('outputType');

		switch ($outputType) {
			case self::OUTPUT_PNG:
				return 'image/png';
			case self::OUTPUT_WEBP:
				return 'image/webp';
			case self::OUTPUT_JPEG:
				return 'image/jpeg';
			default:
				throw new CaptchaException($this->lang()->get('Unsupported type: :outputType', ['outputType' => $outputType]));
		}
	}

	/**
	 * Create a directory
	 *
	 * @param string $directory
	 * @return bool
	 */
	private function createFolder(string $directory): bool
	{
		if (!is_dir($directory)) {
			try {
				if (mkdir($directory, 0777, true) == false && !is_dir($directory)) {
					throw new \Exception($this->lang()->get('Unable to create the :directory directory.', ['directory' => $directory]));
				}
			} catch (\Exception $e) {
				throw new CaptchaException($this->lang()->get($e->getMessage()));
			}
		} else if (!is_writeable($directory)) {
			throw new CaptchaException($this->lang()->get('The directory does not have write permission.'));
		}

		// @chmod($directory, 0777);
		// @fclose(@fopen($directory . '/index.html', 'w'));
		// @chmod($directory . '/index.html', 0777);

		return true;
	}

	/**
	 * Get the captcha image file content|For testing
	 *
	 * @return string
	 */
	public function __toString(): string
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