<?php defined('SYSPATH') or die('No direct script access.');
/**
 * HTML to wiki converter driver. Implements verso wiki. Edit as necessary.
 * PEAR XML Parser is required for this driver.
 *
 * @package        Convert
 * @author         Justin Hernandez <justin@transphorm.com>
 * @license        http://www.opensource.org/licenses/isc-license.txt
 */
class Convert_Wiki_Driver implements Convert_Driver
{
	// array of options passed from Controller
	public $options;

	/**
	 * Load required files
	 */
	public function __construct()
	{
		require_once($this->lib().'any_wiki_towiki.php');
		require_once($this->lib().'any_wiki_glue.php');
	}

	/**
	 * Convert data
	 *
	 * @param   string  $data
	 * @return  string
	 */
	public function convert($data)
	{
		return strip_tags(any_wiki_towiki($data));
	}

	/**
	 * Return default extension
	 *
	 * @return  string
	 */
	public function default_ext()
	{
		return 'txt';
	}

	/**
	 * Allow what kind of file types. all, images, text
	 *
	 * @return  string
	 */
	public function allow()
	{
		return 'text';
	}

	/**
	 * Return vendor library path
	 *
	 * @return  string
	 */
	public function lib()
	{
		return Convert::$vendor.'verso_wiki/';
	}

}