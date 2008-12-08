<?

// File:		$Id: any_wiki_towiki.php 29975 2007-08-10 11:41:52Z marc $
// Author:		Marc Worrell
// Copyright:	(c) 2005-2007 Mediamatic
// Description:	Translate html back to wiki
//

// This file is part of Verso Wiki.
// 
// Verso Wiki is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
// 
// Verso Wiki is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with anyMeta Wiki; if not, write to the Free Software
// Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

require_once('XML_Wiki_Parser.php');
require_once('any_wiki_tohtml.php');


// Function:	any_wiki_towiki
// Access:		EXTERNAL
// Parameters:	$html		html text to unparse
//				$options	(optional) wikification options
// Returns:		wiki text version of html
// Description:	tries to translate the given html to wiki. 
//
function any_wiki_towiki ( $html, $options = false )
{
	global $_wiki_xhtml_parser;
	
	$html = wiki_normalize_newlines($html);
	$html = "\n$html\n\n\n";

	// html block (escapes all that is within)
	$html = preg_replace_callback('|<!--\[html\]-->(.*?)<!--\[/html]-->|s', '_towiki_html', $html);

	// Simple markup
	$html = preg_replace('!</?(i|em)>!', 		'//', 	$html);
	$html = preg_replace('!</?(b|strong)>!',	'**', 	$html);
	$html = preg_replace('|<sup><a|', 			'<a', 	$html);
	$html = preg_replace('|</a></sup>|', 		'</a>',	$html);
	$html = preg_replace('|</?sup>|',	 		'^^', 	$html);
	$html = preg_replace('|<tt>|',				'{{',	$html);
	$html = preg_replace('|</tt>|',				'}}',	$html);

	// coloured texts
	$html = preg_replace('|<span style="color: #?([^"]*);">(.*?)</span>|s', '##\1|\2##', $html);

	// mailto links
	$html = preg_replace('|<!--\[mailto\]-->([^ ]*?)<!--\[/mailto\]-->|', 'mailto:\1', $html);
	$html = preg_replace('|<!--\[mailto\]-->(.*?)<!--\[/mailto\]-->|',    '[mailto:\1]', $html);

	// images
	$html = preg_replace('/<!--(\[image .*?\])-->/', '[\1]', $html);

	// literal texts
	$html = preg_replace('|<!--\[lit\]-->(.*?)<!--\[/lit]-->|s', '``\1``', $html);

	// comments
	$html = preg_replace('|<!-- (.*?) -->|s', '<x-comment>\1</x-comment>', $html);

	// toc
	if (strpos($html, '<!--[toc]-->') !== false)
	{
		// The [[toc]] tag
		$html = preg_replace('|<!--\[toc\]-->.*?<!--\[/toc]-->|s', "\n[[toc]]\n", $html);

		// The toc anchors in the headers		
		$html = preg_replace('|(<h[2-6]>)<a name=\'[0-9]+-[0-9]+\'></a>|', '\1', $html);
	}

	//
	// Make sure that the XML parser gets legal utf8 text
	$html = any_text_utf8($html);

	// Parse the remaining text 
	if (!isset($_wiki_xhtml_parser))
	{
		$_wiki_xhtml_parser = new XML_Wiki_Parser();
	}
	$_wiki_xhtml_parser->reset();

	$ret = $_wiki_xhtml_parser->parseString("<wiki>\n" . $html . "\n</wiki>", true);
	if ($_wiki_xhtml_parser->isError($ret))
	{
		// Error in XML - just return the text... sorry...
		$html = $ret->getMessage() . "\n\n\n" . strip_tags($html);
	}
	else
	{
		$html = $_wiki_xhtml_parser->wiki;
	}
	$_wiki_xhtml_parser->reset();
//	$_wiki_xhtml_parser->free();
	
	// Correct <del/><ins/> pairs
	$html = preg_replace('|(@@---.*?)@@@@(\+\+\+.*?@@)|', '\1\2', $html);

	// Correct multiple newlines
	$html = trim(preg_replace("/\n\n\n*/", "\n\n", $html)) . "\n";
	
	// Remove too many spaces at linestarts in blockquotes
	$html = preg_replace("/\n(>+)[ \t]+/", "\n\\1 ", $html);
	
	// When a line is requested, remove superfluous <p/>'s
	if (!empty($options['target']) && $options['target'] == 'line')
	{
		$html = str_replace(array("\n", "<p>", "</p>"), array(' ', '', ''), $html);
	}
	return $html;
}


function _towiki_html ( $matches )
{
	return "\n<html>\n" . htmlspecialchars($matches[1]) . "\n</html>\n";
}


?>