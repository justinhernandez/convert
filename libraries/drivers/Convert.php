<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Convert library interface.
 *
 * @package        Convert
 * @author         Justin Hernandez <justin@transphorm.com>
 * @license        http://www.opensource.org/licenses/isc-license.txt
 */
interface Convert_Driver
{

	/**
	 * Load vendor library files here
	 */
	public function __construct();

	/**
	 * Renders html into specified format
	 *
	 * @param   string  $output
	 * @return  string
	 */
	public function convert($output);

	/**
	 * Returns default ext type, i.e. 'pdf'
	 *
	 * @return  string
	 */
	public function default_ext();

	/**
	 * Declare vendor library path
	 *
	 * @return  string
	 */
	public function lib();
	
}