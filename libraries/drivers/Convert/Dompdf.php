<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Converts Html to Pdf using Dompdf.
 * Edit as necessary.
 *
 * @package        Convert
 * @author         Justin Hernandez <justin@transphorm.com>
 * @license        http://www.opensource.org/licenses/isc-license.txt
 */
class Convert_Dompdf_Driver implements Convert_Driver
{
	// array of options passed from Controller
	public $options;

	/**
	 * Load required files
	 */
	public function __construct()
	{
		require_once($this->lib()."dompdf_config.inc.php");
	}

	/**
	 * Convert data
	 *
	 * @param   string  $data
	 * @return  string
	 */
	public function convert($data)
	{
		$dompdf = new DOMPDF();
		$dompdf->load_html($data);
		$dompdf->render();
		return $dompdf->output();
	}

	/**
	 * Return default extension
	 *
	 * @return  string
	 */
	public function default_ext()
	{
		return 'pdf';
	}

	/**
	 * Allow what kind of file types. all, images, text
	 *
	 * @return  string
	 */
	public function allow()
	{
		return 'all';
	}

	/**
	 * Return vendor library path
	 *
	 * @return  string
	 */
	public function lib()
	{
		return Convert::$vendor.'dompdf/';
	}
	
}