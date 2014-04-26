<?php
/**
 * php po parser converter cache class to store conversions temporarily until the source po files are changed
 *
 * @see http://jsgettext.berlios.de/
 *
 * User: Sebastian Lagemann <github@degola.de>
 * Date: 26.04.14
 * Time: 21:46 MESZ
 */


namespace PoParser;

class Converter_Cache {
	protected $sourceFile;
	protected $cacheFilePrefix;
	protected $cacheDirectory;
	protected $cacheFile;

	/**
	 * @param $sourceFile
	 * @param string $cacheFilePrefix
	 * @param string $cacheDirectory
	 * @throws \Exception
	 */
	public function __construct($sourceFile, $cacheFilePrefix = '', $cacheDirectory = '/tmp') {
		$this->sourceFile = $sourceFile;
		$this->cacheFilePrefix = $cacheFilePrefix;
		$this->cacheDirectory = $cacheDirectory;
		$this->setCacheFile($this->sourceFile);

		if(!$this->validateConfiguration())
			throw new \Exception('invalid configuration, source file doesn\'t exist, cache directory doesn\'t exist or is not writable.');
	}

	/**
	 * updates cache file
	 *
	 * @param $data
	 */
	public function updateContent($data) {
		$dir = dirname($this->getCacheFile());
		if(!file_exists($dir))
			mkdir($dir, 0755, true);

		file_put_contents($this->getCacheFile(), sha1(file_get_contents($this->getSourceFile())).' '.$data);
		unset($data);
	}

	/**
	 * returns the cache file content
	 *
	 * @param $data
	 * @return string
	 */
	public function getContent() {
		if(!file_exists($this->getCacheFile()) || !is_readable($this->getCacheFile()))
			throw new \Exception('cache file '.$this->getCacheFile().' doesn\'t exist or is not readable.');
		return substr(file_get_contents($this->getCacheFile()), 41);
	}

	public function getSourceFile() {
		return $this->sourceFile;
	}

	/**
	 * returns the cache file
	 *
	 * @return mixed
	 */
	public function getCacheFile() {
		return $this->cacheFile;
	}

	protected function validateConfiguration() {
		if(!file_exists($this->getSourceFile()) || !is_readable($this->getSourceFile()))
			return false;
		if(!is_dir($this->cacheDirectory) || !is_writable($this->cacheDirectory))
			return false;
		return true;
	}
	/**
	 * sets the cache file
	 *
	 * @param $file
	 * @return string
	 */
	protected function setCacheFile($file) {
		$path = $this->cacheDirectory;
		if(substr($path, -1) !== '/') $path .= '/';
		$path .= str_replace(array('\\', '/'), array('_', '_'), $this->cacheFilePrefix).'_'.sha1($file);
		$this->cacheFile = $path;
		unset($path);
	}

	/**
	 * returns last modified timestamp of cached file or null if cache file does not exists
	 *
	 * @return int|null
	 */
	public function getCacheTime() {
		if(file_exists($this->cacheFile))
			return filemtime($this->cacheFile);
		return null;
	}

	/**
	 * returns the time of change for the source file
	 *
	 * @return int
	 */
	public function getChangeTime() {
		return filemtime($this->sourceFile);
	}

	/**
	 * returns if the source file compared to the cache file was changed
	 * if $doHashCheck is set to true it will open the cached file and reads the first 40 bytes for the sha1 check of the
	 * source file data
	 *
	 * @param bool $doHashCheck
	 * @return bool
	 */
	public function isChanged($doHashCheck = false) {
		if($this->getChangeTime() > $this->getCacheTime())
			return true;

		if($doHashCheck === true) {
			$fp = fopen($this->getSourceFile(), 'r');
			$cacheHash = fgets($fp, 40);
			fclose($fp);

			$sourceHash = sha1(file_get_contents($this->getSourceFile()));
			if($sourceHash != $cacheHash)
				return true;
		}
		return false;
	}

	/**
	 * sets http headers
	 * returns false if headers were already sent
	 *
	 * @return bool
	 */
	public function setCachingHeaders() {
		if(!headers_sent()) {
			if($this->isRequestCached()) {
				// Client's cache IS current, so we just respond '304 Not Modified'.
				header("HTTP/1.1 304 Not Modified");
			}
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->getChangeTime()) . ' GMT');
			unset($time);
			return true;
		}
		return false;
	}

	/**
	 * returns if the current request was already cached
	 *
	 * @return bool
	 */
	public function isRequestCached() {
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $this->getChangeTime()))
			return true;
		return false;
	}
}

?>