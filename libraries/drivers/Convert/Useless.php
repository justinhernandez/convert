<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Makes use of Derek Allard's useless fun CI helper to convert images to text.
 * Edit as necessary
 * http://www.derekallard.com/blog/post/most-useless-code-igniter-helper-ever/
 *
 * @package        Convert
 * @author         Justin Hernandez <justin@transphorm.com>
 * @license        http://www.opensource.org/licenses/isc-license.txt
 */
class Convert_Useless_Driver implements Convert_Driver
{
	// array of options passed from Controller
	public $options;

	/**
	 * Load required files
	 */
	public function __construct()
	{
		require_once($this->lib().'useless_fun.php');
	}

	/**
	 * Data is null and string data is grabbed from pic file.
	 *
	 * @param   string  $data
	 * @return  string
	 */
	public function convert($data)
	{
		$o = '';
		$o .= @$this->options['open'];
		$data = file_get_contents($this->options['image']);
		$o .= image_to_text($data, $this->options['text'], @$this->options['width'], @$this->options['width']);
		$o .= @$this->options['close'];
		return $o;
	}

	/**
	 * Return default extension
	 *
	 * @return  string
	 */
	public function default_ext()
	{
		return 'htm';
	}

	/**
	 * Allow what kind of file types. all, image, text
	 *
	 * @return  string
	 */
	public function allow()
	{
		return 'image';
	}

	/**
	 * Return vendor library path
	 *
	 * @return  string
	 */
	public function lib()
	{
		return Convert::$vendor.'useless_fun/';
	}

}