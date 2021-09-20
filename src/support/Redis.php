<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\support;

class Redis
{
	/**
	 * Options
	 */
	protected array $options = [
		'host'       => '127.0.0.1',
		'port'       => 6379,
		'password'   => '',
		'select'     => 0,
		'timeout'    => 0,
		'expire'     => 0,
		'persistent' => false,
		'prefix'     => '',
	];

    protected ?object $handler = null;
    protected static ?object $connection = null;

    protected static array $config = [];
    
	/**
	 * Determine if an item exists in the redis.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	protected function __construct()
	{
		if (!empty(self::$config)) {
			$this->options = array_merge($this->options, self::$config);
		}

		if (class_exists('\Predis\Client')) {
			$params = [];
			foreach ($this->options as $key => $val) {
				if (in_array($key, ['aggregate', 'cluster', 'connections', 'exceptions', 'prefix', 'profile', 'replication', 'parameters'])) {
					$params[$key] = $val;
					unset($this->options[$key]);
				}
			}

			if ($this->options['password'] == '') {
				unset($this->options['password']);
			}

			$this->handler = new \Predis\Client($this->options, $params);

			$this->options['prefix'] = '';

		} elseif (extension_loaded('redis')) {
			$this->handler = new \Redis;

			if ($this->options['persistent']) {
                // 脚本结束之后连接不释放，连接保持在php-fpm进程中
				$this->handler->pconnect($this->options['host'], (int) $this->options['port'], (int) $this->options['timeout'], 'persistent_id_' . $this->options['select']);
			} else {
                // 脚本结束之后连接释放
				$this->handler->connect($this->options['host'], (int) $this->options['port'], (int) $this->options['timeout']);
			}

			if ($this->options['password'] != '') {
				$this->handler->auth($this->options['password']);
			}
		} else {
			throw new \BadFunctionCallException('Not support: redis');
		}

		if ($this->options['select'] != 0) {
			$this->handler->select((int) $this->options['select']);
		}
    }

    public static function connection(?array $config = []): self
    {
        if(is_null(self::$connection)) {
            self::$config = $config;
            return self::$connection = new static;
        }

        return self::$connection;
    }

	/**
	 * Determine if an item exists in the redis.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function has($key): bool
	{
		return $this->handler->exists($key) ? true : false;
	}

	/**
	 * Get an item from the redis.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function get(string $key): mixed
	{
		if (!empty($result = $this->handler->get($key)))
		{
			return unserialize($result);
		}
	}

	/**
	 * Write an item to the redis for a given number of minutes.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  int     $expire
	 * @return bool
	 */
	public function put(string $key, mixed $value, int $expire = null): bool
	{
		if (is_null($expire)) {
			$expire = $this->options['expire'];
		}

		if (is_null($expire)) {
			$this->handler->set($key, $value);
		} else {
			$this->handler->setex($key, $expire, serialize($value));
		}

		return true;
	}

	/**
	 * Write an item to the redis that lasts forever.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return bool
	 */
	public function forever($key, $value): bool
	{
		$this->handler->set($key, serialize($value));
		return true;
	}

	/**
	 * Delete an item from the redis.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function forget($key): bool
	{
		$result = $this->handler->del($key);
		return $result > 0;
	}

	/**
	 * Clear redis.
	 *
	 * @return bool
	 */
	public function clear(): bool
	{
		$this->handler->flushDB();
		return true;
	}
}
