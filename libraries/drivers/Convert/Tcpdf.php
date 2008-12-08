<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Converts HTML to pdf. Edit as necessary.
 * Use the tcpdf vendor library.
 *
 * @package        Convert
 * @author         Justin Hernandez <justin@transphorm.com>
 * @license        http://www.opensource.org/licenses/isc-license.txt
 */
class Convert_Tcpdf_Driver implements Convert_Driver
{
	// array of options passed from Controller
	public $options;

	/**
	 * Load required files
	 */
	public function __construct()
	{
		require_once($this->lib().'config/lang/eng.php');
		require_once($this->lib().'tcpdf.php');
	}

	/**
	 * Convert data. Taken from http://www.tecnick.com/pagefiles/tcpdf/example_006.phps
	 *
	 * @param   string  $data
	 * @return  string
	 */
	public function convert($data)
	{
		// create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		// SET THESE YOURSELF
		// $pdf->SetAuthor("Nicola Asuni");
		// $pdf->SetTitle("TCPDF Example 006");
		// $pdf->SetSubject("TCPDF Tutorial");
		// $pdf->SetKeywords("TCPDF, PDF, example, test, guide");

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//set some language-dependent strings
		// $pdf->setLanguageArray($l);

		//initialize document
		$pdf->AliasNbPages();

		// add a page
		$pdf->AddPage();

		// ---------------------------------------------------------

		// set font
		$pdf->SetFont("dejavusans", "", 10);

		// output the HTML content
		$pdf->writeHTML($data, true, 0, true, 0);

		// return converted html as a string
		return $pdf->Output('', 'S');
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
		return Convert::$vendor.'tcpdf/';
	}

}