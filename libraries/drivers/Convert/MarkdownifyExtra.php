<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Converts Html to Markdown Extra using Markdownify Extra.
 * Edit as necessary.
 *
 * @package        Convert
 * @author         Justin Hernandez <justin@transphorm.com>
 * @license        http://www.opensource.org/licenses/isc-license.txt
 */
class Convert_MarkdownifyExtra_Driver implements Convert_Driver
{
	// array of options passed from Controller
	public $options;

	/**
	 * Load required files
	 */
	public function __construct()
	{
		require_once($this->lib().'markdownify_extra.php');
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
		$data = $this->strip_attrs($data);
		$md = new Markdownify_Extra;
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

	/**
	 * Strip attribute strings cuz markdownify can't handle them. Huh?
	 *
	 * @param   string  $data
	 * @return  string
	 */
	public function strip_attrs($data)
	{
		return preg_replace('/<(.+?) (.+?)>/', '<$1>', $data);
	}

}