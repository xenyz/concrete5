<?
/**
 * @package Helpers
 * @category Concrete
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */

/**
 * Functions useful for working with files and directories.
 * @package Helpers
 * @category Concrete
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */

defined('C5_EXECUTE') or die(_("Access Denied."));
class FileHelper {

	/**
	 * @access private
	 */
	protected $ignoreFiles = array('__MACOSX', DIRNAME_CONTROLLERS);
	
	/** 
	 * Returns the contents of a directory in an array.
	 * @param string $directory
	 * @return array
	 */
	public function getDirectoryContents($dir, $ignoreFilesArray = array()) {
		$this->ignoreFiles = array_merge($this->ignoreFiles, $ignoreFilesArray);
		$aDir = array();
		if (is_dir($dir)) {
			$handle = opendir($dir);
			while(($file = readdir($handle)) !== false) {
				if (substr($file, 0, 1) != '.' && (!in_array($file, $this->ignoreFiles))) {
					$aDir[] = $file;
				}
			}
		}
		return $aDir;
	}
	
	/** 
	 * Removes the extension of a filename, uncamelcases it.
	 * @param string $filename
	 * @return string
	 */	
	public function unfilename($filename) {
		// removes the extension and makes it look nice
		$txt = Loader::helper('text');
		return substr($txt->unhandle($filename), 0, strrpos($filename, '.'));
	}
	
	/** 
	 * Returns the full path to the temporary directory
	 */
	public function getTemporaryDirectory() {
		if (function_exists('sys_get_temp_dir')) {
			return sys_get_temp_dir();
		} else {
			if (!empty($_ENV['TMP'])) { return realpath($_ENV['TMP']); }
			if (!empty($_ENV['TMPDIR'])) { return realpath( $_ENV['TMPDIR']); }
			if (!empty($_ENV['TEMP'])) { return realpath( $_ENV['TEMP']); }
			
			$tempfile=tempnam(uniqid(rand(),TRUE),'');
			if (file_exists($tempfile)) {
				unlink($tempfile);
				return realpath(dirname($tempfile));
			}
		}
	}


	
	/**
	 * Adds content to a new line in a file. If a file is not there it will be created
	 * @param string $filename
	 * @param string $content
	 */
	public function append($filename, $content) {
		file_put_contents($filename, $content, FILE_APPEND);
	}
	
	
	/**
	 * Just a consistency wrapper for file_get_contents
	 * Should use curl if it exists and fopen isn't allowed (thanks Remo)
	 * @param $filename
	 */
	public function getContents($file, $timeout = 5) {
		$url = @parse_url($file);
		if (isset($url['scheme']) && isset($url['host'])) {
			if (ini_get('allow_url_fopen')) {
				$ctx = stream_context_create(array( 
					'http' => array( 'timeout' => $timeout ) 
				)); 
				if ($contents = @file_get_contents($file, 0, $ctx)) {
					return $contents;
				}
			}
			
			if (function_exists('curl_init')) {
				$curl_handle = curl_init();
				curl_setopt($curl_handle, CURLOPT_URL, $file);
				curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
				$contents = curl_exec($curl_handle);
				return $contents;
			}
		} else {
			if ($contents = @file_get_contents($file)) {
				return $contents;
			}
		}
		
		return false;
	}
	
	/** 
	 * Removes contents of the file
	 * @param $filename
	 */
	public function clear($file) {
		file_put_contents($file, '');
	}
	
	
	/** 
	 * Cleans up a filename and returns the cleaned up version
	 */
	public function sanitize($file) {
		//return preg_replace(array("/[^0-9A-Za-z-.]/","/[\s]/"),"", $file);
		$file = preg_replace("/[^0-9A-Z_a-z-.\s]/","", $file);
		return trim($file);
	}
	
	/** 
	* Returns the extension for a file name
	* @param $filename
	*/
	public function getExtension($filename) {
		$extension = end(explode(".",$filename));
		return $extension;
	}
	
	/** 
	 * Takes a path and replaces the files extension in that path with the specified extension
	 */
	public function replaceExtension($filename, $extension) {
		$newFileName = substr($filename, 0, strrpos($filename, '.')) . '.' . $extension;
		return $newFileName;
	}
		
}
?>