<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Convert demo controller
 *
 * @package        Convert
 * @author         Justin Hernandez <justin@transphorm.com>
 * @license        http://www.opensource.org/licenses/isc-license.txt
 */
class Convert_Controller extends Template_Controller {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	//template
	public $template = 'lorem_ipsum';

	public function dompdf()
	{
		$this->template->content = 'howdy from dompdf!';
		$this->template->convert('dompdf');
	}

	public function tcpdf()
	{
		$options = array('a', 'b', 'c');
		$this->template->content = 'howdy from tcpdf!';
		$this->template->convert('tcpdf', NULL, NULL, $options);
	}

	public function markdownify()
	{
		$this->template->content = 'howdy from markdownify!';
		$this->template->convert('markdownify');
	}

	public function markdownify_extra()
	{
		$this->template->content = 'howdy from markdownify extra!';
		$this->template->convert('markdownify_extra');
	}

	public function textile()
	{
		$this->template->content = 'howdy from html2textile!';
		$this->template->convert('textile');
	}

	public function wiki()
	{
		$this->template->content = 'howdy from verso wiki!';
		$this->template->convert('wiki');
	}

	/**
	 * Implements the useless helper from Derek Allard:
	 * http://www.derekallard.com/blog/post/most-useless-code-igniter-helper-ever/
	 *
	 * Data for the image will be collected in the driver.
	 */
	public function useless()
	{
		// you could also use a local image
		$options['image'] = 'http://farm1.static.flickr.com/121/306682878_b82298c1d3_m_d.jpg';
		// find ext
		$path = pathinfo($options['image']);
		$ext = $path['extension'];
		// text to fill image with
		$options['text'] = 'laugh';
		// wrap output with open and close
		$options['open'] = '<div style="font-size: 0.5em;letter-spacing: 0em;line-height: 0.5em;">';
		$optins['close'] = '</div>';
		// optional resample image, for large images
		$options['width'] = 50;
		$options['heigh'] = 50;
		// use convert without using a View object
		// find ext so that type checking can be run to ensure that pdf or txt
		// files are not sent. Only images
		new Convert('useless', NULL, 'print', NULL, $ext, $options);
	}

} 