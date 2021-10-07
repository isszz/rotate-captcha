<?php
declare (strict_types = 1);

namespace isszz\captcha\rotate\support;

use \FilesystemIterator;

class File
{
	public array $files = [];
	protected ?object $items = null;
	public ?string $directory = null;
	public array $extension = ['webp', 'png', 'jpg', 'jpeg'];

	protected static ?object $instance = null;

	public function __construct(string $directory)
	{
		$this->directory = $directory;
	}
	
	public static function make(string $directory): self
	{
		if(is_null(static::$instance)) {
			return static::$instance = new static($directory);
		}

		return static::$instance;
	}

	/**
	 * Randomly extract files
	 * 
	 * @param int $limit
	 * @return string|array
	 */
	public function rand(int $limit = 1): string|array
	{
		if(empty($this->files) || !is_array($this->files) || !count($this->files)) {
			$this->files = $this->candir();
		}

		shuffle($this->files);

		if($limit > 1) {
			return array_intersect_key($this->files, array_flip((array) array_rand($this->files, $limit)));
		}

		$rand = mt_rand(0, count($this->files) - 1);

		return $this->files[$rand];
	}

	/**
	 * Increase the available suffix
	 * 
	 * @param array $extensions
	 * @return int
	 */
	public function extension(array $extensions): self
	{
		$this->extensions = array_merge($this->extensions, $extensions);

		return $this;
	}
	
	/**
	 * Remove all files in the directory
	 * 
	 * @return bool
	 */
	public function remove(): bool
	{
		$files = $this->candir();

		if(empty($files) || !is_array($files) || !count($files)) {
			return false;
		}

		foreach ($files as $item)
		{
			@unlink($item);
		}

		return true;
	}

	/**
	 * Recursively get the files in the directory
	 * 
	 * @param ?string $directory
	 * @return array
	 */
	protected function candir(?string $directory = null): array
	{
		$items = new FilesystemIterator($directory ?? $this->directory, FilesystemIterator::SKIP_DOTS);

		$files = [];
		foreach ($items as $item)
		{
			if ($item->isDir()) {
				$_files = $this->candir($item->getRealPath());
				$files = array_merge($files, $_files);
			} else {
				if(!in_array($item->getExtension(), $this->extension)) {
					continue;
				}
				$files[] = $item->getRealPath();
			}
		}

		return $files;
	}
}
