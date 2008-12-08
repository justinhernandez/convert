<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Custom MY_View to add the convert method.
 *
 * @package        Convert
 * @author         Justin Hernandez <justin@transphorm.com>
 * @license        http://www.opensource.org/licenses/isc-license.txt
 */
class View extends View_Core
{

	public function convert($driver, $action = 'download', $name = NULL, $options = array())
	{
		new Convert($driver, $this, $action, $name, $this->kohana_filetype, $options);
	}
	
}
