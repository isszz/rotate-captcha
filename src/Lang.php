<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate;

class Lang 
{
	private static $instance = null;
	/**
	 * 多语言信息
	 * @var array
	 */
	private $lang = [];

	/**
	 * 当前语言
	 * @var string
	 */
	private $range = 'en';

	/**
	 * Create a new Lang instance.
	 *
	 * @param  string  $file
	 * @return self
	 */
	protected function __construct(string $file, string $range = 'en')
	{
		$this->range = $range;

		if($range == 'en') {
			return $this;
		}

		if( empty($this->lang)) {
			$this->load($file);
		}

		if(empty($this->lang)) {
			throw new CaptchaException('Language file exists.');
		}

		return $this;
	}

	/**
	 * Create a new Lang instance.
	 *
	 * @param  string  $file
	 * @return self
	 */
	public static function line(string $file, string $range = 'en'): self
	{
		if(is_null(static::$instance)) {
			return static::$instance = new static($file, $range);
		}

		return static::$instance;
	}

	/**
	 * 判断是否存在
	 * 
	 * @param string|null $name  语言变量
	 * @return bool
	 */
	public function has(string $name): bool
	{
		return $this->get($name, []) ? true : false;
	}

	/**
	 * 获取语言
	 * 
	 * @param string|null $name  语言变量
	 * @param array $vars  变量替换
	 * @return string
	 */
	public function get(string $name = null, array $vars = []): string
	{
		// 空参数返回所有
		if (is_null($name)) {
			return $this->lang ?? [];
		}

		if($this->range == 'en') {
			$value = $name;
		} else {
			$value = isset($this->lang[$name]) ? $this->lang[$name] : $name;
		}

		if (empty($value)) {
			return '';
		}
		
		if (strpos($value, ':')) {
			foreach ($vars as $key => $v) {
				$value = str_replace(':' . $key, (string) $v, $value);
			}
		}

		return $value;
	}

	/**
	 * 加载语言
	 * 
	 * @param string $file  语言文件
	 * @return array
	 */
	public function load($file): array
	{
		if (!empty($this->lang)) {
			return $this->lang;
		}

		$lang = [];

		if (is_file($file)) {
			$result = include $file;
			$lang = isset($result) && is_array($result) ? $result : [];
		}

		if (!empty($lang)) {
			$lang = isset($lang[$this->range]) ? $lang[$this->range] : $lang;
		}

		return $this->lang = $lang;
	}
}
