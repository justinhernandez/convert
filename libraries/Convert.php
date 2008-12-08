<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Main Convert class.
 *
 * @package        Convert
 * @author         Justin Hernandez <justin@transphorm.com>
 * @license        http://www.opensource.org/licenses/isc-license.txt
 */
class Convert
{
	// easy access to vendor folder
	public static $vendor;
	
	// private variables
	private $action;
	private $name;
	private $ext;
	private $driver;
	private $options;

	/**
	 * Driver - plugin to use
	 * Data - data to be converted
	 * Action - download, print, save
	 * Name - file name to use when saving or downloading
	 * Ext - file extension
	 *
	 *
	 * @param  string  $driver
	 * @param  string  $data
	 * @param  string  $action
	 * @param  string  $name
	 * @param  string  $ext
	 * @param  array   $options
	 */
	public function __construct($driver, $data, $action = NULL, $name = NULL, $ext = NULL, $options = array())
	{
		// clean driver name replaces _ to space for ucwords then converts back
		$this->driver = str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', $driver))));
		// set action
		$this->action = ($action)
					  ? $action
					  : 'download';
		// set ext if null attempt to auto find
		if ($ext)
		{
			$this->ext = str_replace('.', '', $ext);
		}
		else
		{
			$ext = pathinfo($name);
			$this->ext = $ext['extension'];
		}
		// set name
		$this->name = ($name)
					? $name
					: basename(Router::$current_uri, $this->ext);
		// set options
		$this->options = $options;
		// set vendor folder
		self::$vendor = MODPATH.'convert/vendor/';

		$this->init($data);
	}

	/**
	 * Initialize conversion process. Load and find driver, then convert.
	 *
	 * @param  string  $data
	 */
	private function init($data)
	{
		// Set the driver class name
		$driver = 'Convert_'.$this->driver.'_Driver';

		if ( ! Kohana::auto_load($driver))
			throw new Kohana_Exception('convert.driver_not_found', $this->driver, get_class($this));

		// Load the driver
		$driver = new $driver();
		// Pass options to driver
		$driver->options = $this->options;

		// check if driver implements Convert Driver properly
		if ( ! ($driver instanceof Convert_Driver))
			throw new Kohana_Exception('convert.driver_implements', $this->driver, get_class($this), 'Convert_Driver');

		// check if vendor library exists
		if ( ! ($this->exists($driver->lib())))
			throw new Kohana_Exception('convert.vendor_not_found', $this->driver);

		// check if passed file extension is allowed to be used for this driver
		$this->check_allowable($driver->allow());

		// render output through driver and pass it to the handler()
		$this->handler($driver->convert($data), $driver->default_ext());
	}

	/**
	 * Handle output
	 */
	private function handler($data, $default_ext)
	{
		// get mime type
		$mime = Kohana::config("mimes.$default_ext");

		if ( ! ($mime))
			throw new Kohana_Exception('convert.mime_not_found', $default_ext);

		switch($this->action)
		{
			case 'download':
				// just in case of developer error
				$this->name = basename($this->name).".$default_ext";
				header("Content-disposition: attachment; filename=$this->name");
				header("Content-type: $mime");
				print($data);
				break;
			case 'print':
				print($data);
				break;
			case 'save':
				// saves data to file, raises error if can't save
				if ( ! (@file_put_contents($this->name, $data)))
					throw new Kohana_Exception('convert.file_not_saved', $this->name, $this->driver);
				break;
		}

		// to prevent View from outputting data
		die();
	}

	/**
	 * Check which extensions to allow. If 'all' function will exit. If image
	 *  check will be made to ensure that extension type is in the img array.
	 * If text then extension must not be in array. Driver may pass their own
	 * array.
	 *
	 * @param  mixed  $allow
	 */
	private function check_allowable($allow)
	{
		if (is_array($allow))
		{
			$good = in_array($this->ext, $allow);
		}
		else
		{
			$img = array('jpg', 'jpeg', 'gif', 'bmp', 'tiff', 'tif', 'png', 'xcf',
				'xcm', 'ico', 'jpe', 'rgb', 'pbm', 'svg', 'xbm', 'xpm', 'xwd');

			switch ($allow)
			{
				case 'all':
					$good = TRUE;
					break;
				case 'text':
					// if not in array then return true
					$good = (!in_array($this->ext, $img));
					break;
				case 'image':
					// if in array then return TRUE
					$good = (in_array($this->ext, $img));
					break;
			}
		}

		// check if filetype is allowed
		if ( ! $good )
			throw new Kohana_Exception('convert.type_not_allowed', $this->ext, $this->driver);
	}

	/**
	 * Checks if vendor library exists
	 *
	 * @return  boolean
	 */
	public function exists($dir)
	{
		return is_dir($dir);
	}

}