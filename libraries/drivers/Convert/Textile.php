<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Converts HTML to textile. Edit as necessary.
 * Vendor lib: http://3v1n0.tuxfamily.org/scripts/detextile/HTML-to-Textile.php?source=show
 *
 * @package        Convert
 * @author         Justin Hernandez <justin@transphorm.com>
 * @license        http://www.opensource.org/licenses/isc-license.txt
 */
class Convert_Textile_Driver implements Convert_Driver
{
	// array of options passed from Controller
	public $options;

	/**
	 * Load required files
	 */
	public function __construct()
	{
		require_once($this->lib().'html2textile.php');
	}

	/**
	 * Convert data
	 *
	 * @param   string  $data
	 * @return  string
	 */
	public function convert($data)
	{
		$data = stripslashes($data);
		$html2textile = new html2textile;
		return $html2textile->detextile($data);
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
		return Convert::$vendor.'html2textile/';
	}

}