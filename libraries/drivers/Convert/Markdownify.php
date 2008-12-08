<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Converts Html to Markdown using Markdownify.
 * Edit as necessary.
 *
 * @package        Convert
 * @author         Justin Hernandez <justin@transphorm.com>
 * @license        http://www.opensource.org/licenses/isc-license.txt
 */
class Convert_Markdownify_Driver implements Convert_Driver
{
	// array of options passed from Controller
	public $options;

	/**
	 * Load required files
	 */
	public function __construct()
	{
		require_once($this->lib().'markdownify.php');
	}

	/**
	 * Convert data
	 *
	 * @param   string  $data
	 * @return  string
	 */
	public function convert($data)
	{
		// markdownify doesn't like attrs
		$md = new Markdownify;
		return $md->parseString($data);
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
		return Convert::$vendor.'markdownify/';
	}

}