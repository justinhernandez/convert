<?

// File:		$Id: any_wiki_tohtml.php 29975 2007-08-10 11:41:52Z marc $
// Author:		Marc Worrell
// Copyright:	(c) 2005-2007 Marc Worrell
// Description:	Parser for Wiki texts, translates to HTML
//
// http://wiki.ciaweb.net/yawiki/index.php?area=Text_Wiki&page=WikiRules

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

require_once('any_wiki_tokenizer.php');


// Function:	any_wiki_tohtml
// Access:		EXTERNAL
// Parameters:	$s			Wiki text to translate
//				$options	(optional) translation options
// Returns:		html version of parameter
// Description:	parse the wiki text and generate a html version
//
//	this parser is a hand coded shift-reduce like parser.
//	due to the nature of the wiki texts a recursive descent parser is not feasible.
//
//  the only option is 'target', set to 'line' to suppress a <p/> around the generated
//	line.  this is useful for making html of headlines and titles.  as it is not too
//  handy to have <p/> inside your <h1/>
//
function any_wiki_tohtml ( $s, $options = array() )
{
	list($tk, $tk_s) = wiki_tokenizer($s, $options);

	$block		= array();		// lines for current block
	$line		= array();		// stacked tokens for current line
	$line_s		= array();		// stacked texts for current line

	$html		= '';			// generated html
	$i			= 0;
	$toc		= false;		// is there a toc or not?	
	
	do
	{
		$tok = $tk[$i];

		switch ($tok)
		{
		case 'br':
			$line[]   = 'html';
			$line_s[] = "<br/>\n";
			break;

		case 'html':
			list($_html, $block) = _wiki_reduce_line($block, $line, $line_s);
			$html  .= $_html . _wiki_reduce_block($block);
			
			if (wiki_allow_html())
			{
				$html  .= "\n<!--[html]-->" . $tk_s[$i] . "<!--[/html]-->\n";
			}
			else
			{
				$html .= '<p>' . nl2br(strip_tags($tk_s[$i])) . '</p>';
			}
			
			$line   = array();
			$line_s = array();
			$block  = array();
			break;
			
		case 'code':
			list($_html, $block) = _wiki_reduce_line($block, $line, $line_s);
			$html  .= $_html . _wiki_reduce_block($block);
			
			$html  .= "\n<pre>\n" . htmlspecialchars($tk_s[$i]) . "</pre>\n";
			
			$line   = array();
			$line_s = array();
			$block  = array();
			break;

		case 'p':
		case 'end':
			list($_html, $block) = _wiki_reduce_line($block, $line, $line_s);
			$html  .= $_html . "\n" . _wiki_reduce_block($block);
			$line   = array();
			$line_s = array();
			$block  = array();
			break;
		
		case 'newline':
			list($_html, $block) = _wiki_reduce_line($block, $line, $line_s);
			$html  .= $_html;
			$line   = array();
			$line_s = array();
			break;
		
		case 'toc':
			$html  .= '<!--[[toc]]-->';
			$line   = array();
			$line_s = array();
			$toc	= true;
			break;
			
		case 'comment':
			if ($i == 0)
			{
				// Comment at the start of a line or in a block
				$html .= '<!-- '.htmlspecialchars(trim($tk_s[$i])).' -->';
			}
			else
			{
				// Comment in a line
				list($line, $line_s) = _wiki_shift_reduce($line, $line_s, $tok, $tk_s[$i]);
			}
			break;

		case 'word':
		case ' ':
		default:
			list($line, $line_s) = _wiki_shift_reduce($line, $line_s, $tok, $tk_s[$i]);
			break;
		}
		$i++;
	}
	while ($tok != 'end');
	
	// Merge <p/>'s over more than one line
	$html = preg_replace("|</p>\n<p>|", "<br/>\n", $html);
	
	if (!empty($options['target']) && $options['target'] == 'line')
	{
		// Strip the <p> tags... the user wants a single line.
		$html = trim(preg_replace('|</?p>|', ' ', $html));
	}
	else if ($toc)
	{
		$html = _wiki_toc($html);
	}
	
	return trim($html);
}



// Function:	wiki_allow_html
// Access:		EXTERNAL
// Parameters:	-
// Returns:		false	no html allowed
//				true 	html allowed
// Description:	Check if the ACL allows html entry
//
function wiki_allow_html ( )
{
	$allow = false;
	if (isset($GLOBALS['any_acl']))
	{
		$allow = $GLOBALS['any_acl']->allowHtml();
	}
	return $allow;
}


// Function:	wiki_filter_uri
// Access:		EXTERNAL
// Parameters:	$uri		uri to be checked
// Returns:		false	when uri not allowed
//				uri		when allowed
// Description:	Check if the ACL allows the given uri
//
function wiki_filter_uri ( $uri )
{
	if (isset($GLOBALS['any_acl']))
	{
		$uri = $GLOBALS['any_acl']->filterUri($uri);
	}
	return $uri;
}


// Function:	wiki_filter_attrs
// Access:		EXTERNAL
// Parameters:	$uri		uri to be checked
// Returns:		false	when uri not allowed
//				uri		when allowed
// Description:	Check if the ACL allows the given attrs.
//				This function has a short whitelist of allowed attributes.
//
function wiki_filter_attrs ( $attr )
{
	$as = array();
	foreach ($attr as $a => $v)
	{
		switch ($a)
		{
		case 'id':
		case 'name':
		case 'align':
		case 'valign':
		case 'title':
		case 'width':
		case 'height':
		case 'rel':
		case 'alt':
		case 'class':
		case 'link':
		case 'caption':
			$as[$a] = $v;
			break;
		default:
			if (	isset($GLOBALS['any_acl'])
				&&	$GLOBALS['any_acl']->allowHtml())
			{
				$as[$a] = $v;
			}
			break;
		}
	}
	return $as;
}



// Function:	_wiki_reduce_block
// Access:		INTERNAL
// Parameters:	$block		the tokens in the block
// Returns:		html fragment
// Description:	Force the complete reduction of a block to html
//
function _wiki_reduce_block ( $block )
{
	if (count($block) > 0)
	{
		list($html, $block) = _wiki_shift_reduce_block($block, array('end'), array(''));
	}
	else
	{
		$html = '';
	}
	return $html;
}


// Function:	_wiki_shift_reduce_block
// Access:		INTERNAL
// Parameters:	$block		the tokens in the block
//				$line		line tokens
//				$line_s		line strings
// Returns:		array(html-fragment, block)
// Description:	(Partially) reduces the block after encountering the given line
//
//				Checks for:
//				- enumerated lists
//				- tables
//				- blockquote
//
//
// Each block entry is as follows:
//
//			( class, depth, class-parms, line_tokens, line_strings )
//
// Where class is one of:
//
//			table, ul, ol, blockquote, dl
//
// Depth is valid for:
//
//			ul, ol, blockqoute
//
function _wiki_shift_reduce_block ( $block, $line, $line_s )
{
	if (!empty($line))
	{
		if ($line[0] == '=' && @$line[1] == ' ')
		{
			$html  				 = _wiki_reduce_block($block);
			list($line, $line_s) = _wiki_merge($line, $line_s, 2, false, true);
			$html .= "\n<p><div style=\"text-align: center;\">" . $line_s[2] . "</div></p>\n";
			
			return array($html, array());
		}
	}

	$block_line = _wiki_block_line($line, $line_s);

	if ($block_line[0] == 'p' || $block_line[0] == 'end')
	{
		$html = _wiki_reduce_block_lines($block);

		if ($block_line[0] == 'p')
		{
			list($line, $line_s) = _wiki_merge($line, $line_s, 0, false, true);
			if (!empty($line_s[0]))
			{
				$html .= "<p>" . $line_s[0] . "</p>\n";
			}
		}
		
		$block = array();
	}
	else
	{
		$block[] 	= $block_line;	
		$html    	= '';
	}
	
	return array($html, $block);
}



// Function:	_wiki_reduce_block_lines
// Access:		INTERNAL
// Parameters:	$block		a complete block
// Returns:		html
// Description:	recursively reduces a block to html
//				all line level reductions have been done
//				what we get is a block of lines, each preparsed.
//
function _wiki_reduce_block_lines ( &$block )
{
	if (empty($block))
	{
		return '';
	}

	$len	= count($block);
	$class	= $block[0][0];
	$depth	= $block[0][1];

	// Collect all lines with the same class and depth
	
	$sub_block   = array();
	$sub_block[] = array_shift($block);

	if ($class == 'ol')
	{
		$alt_class = 'ul';
	}
	else if ($class == 'ul')
	{
		$alt_class = 'ol';
	}
	else
	{
		$alt_class = false;
	}
	
	while (		!empty($block)
			&&	$block[0][1] >= $depth
			&&	(	$block[0][0] == $class
				 || $block[0][0] == $alt_class))
	{
		if ($block[0][1] > $depth || $block[0][0] != $class)
		{
			// this is a nested block of the same kind
			// reduce this one separately and remember the html in the previous block line
			$html = _wiki_reduce_block_lines($block);
			
			if (!empty($html))
			{
				$sub_block[count($sub_block)-1][5] = $html;
			}
		}
		else
		{
			$sub_block[] = array_shift($block);
		}
	}

	// special handling for a table
	$td = 0;
	if ($class == 'table')
	{
		foreach ($sub_block as $sub)
		{
			$td = max($td, $sub[2]);
		}
	}
	
	// generate the html for the captured block
	$html = "<$class>\n";
	$nr   = 0;
	foreach ($sub_block as $sub)
	{
		$pars	= $sub[2];
		$line	= $sub[3];
		$line_s	= $sub[4];
		$nested	= isset($sub[5]) ? $sub[5] : '';
		$nr++;
		
		switch ($class)
		{
		case 'ol':
		case 'ul':
			list($line, $line_s) = _wiki_merge($line, $line_s, 2, false, true);
			$html .= '<li>' . trim($line_s[2]) . $nested . "</li>\n";
			break;

		case 'table':
			// Generate a row
			$html .= _wiki_table_row($td, $line, $line_s, $pars);
			break;
		
		case 'blockquote':
			if ($nr == 1)
			{
				$html .= '<p>';
			}
			list($line, $line_s) = _wiki_merge($line, $line_s, 2, false, true);
			$html .= $line_s[2] . $nested;
			if ($nr != count($sub_block))
			{
				$html .= '<br/>';
			}
			else
			{
				$html .= "</p>\n";
			}
			break;
		
		case 'dl':
			// $pars is the offset of the first ' ' of the ' : ' separating the dt from the dd
			list($line, $line_s) = _wiki_merge($line, $line_s, $pars+3, false, true);

			// the reduced html of the dd
			$dd = array_pop($line_s);
			array_pop($line);
			
			// op the ' ' ':' ' ';
			array_pop($line_s);
			array_pop($line);
			array_pop($line_s);
			array_pop($line);
			array_pop($line_s);
			array_pop($line);

			// Reduce the dt part
			list($line, $line_s) = _wiki_merge($line, $line_s, 2, false, true);
			$dt = array_pop($line_s);
			
			$html .= "  <dt>$dt</dt>\n  <dd>$dd</dd>\n";
			break;
		}
	}
	$html .= "</$class>\n\n";

	return $html;
}



// Function:	_wiki_table_row
// Access:		INTERNAL
// Parameters:	$table_cols	nr of tds
// 				$line		tokens in line
//				$line_s		text of tokens
// Returns:		html for row
// Description:	generates the html for a row
//
function _wiki_table_row ( $table_cols, $line, $line_s )
{
	$html	= "<tr>";
	$len	= count($line);
	$td		= array();

	$start	= 1;
	$colspan= 1;

	// Split the line in tds 
	for ($i=1;$i<$len;$i++)
	{
		if ($line[$i] == '||')
		{
			if ($line[$i-1] == '||' && $i+1 < $len)
			{
				$colspan++;
				$start++;
			}
			else
			{
				// A td from $start to $i-1
				if ($i - $start > 0)
				{
					$td[]	= array(	array_slice($line,   $start, $i - $start),
										array_slice($line_s, $start, $i - $start),
										$colspan);
				}
				else
				{
					$td[]	= array(false, false, $colspan);
				}
				$start   = $i+1;
				$colspan = 1;
			}
		}
	}
	
	// Generate the html per td
	foreach ($td as $t)
	{
		$line    = $t[0];
		$line_s  = $t[1];
		
		if ($t[2] > 1)
		{
			$colspan = ' colspan="' . $t[2] . '" ';
		}
		else
		{
			$colspan = '';
		}
		
		if (!empty($line))
		{
			$end = "</td>";
			switch ($line[0])
			{
			case '>':
				$html .= "\n  <td style=\"text-align: right;\"$colspan>";
				$start = 1;
				break;
			case '<':
				$html .= "\n  <td style=\"text-align: left;\"$colspan>";
				$start = 1;
				break;
			case '=':
				$html .= "\n  <td style=\"text-align: center;\"$colspan>";
				$start = 1;
				break;
			case '~':
				$html .= "\n  <th$colspan>";
				$end   = "</th>";
				$start = 1;
				break;
			default:
				$html .= "\n  <td$colspan>";
				$start = 0;
				break;
			}

			list($line, $line_s) = _wiki_merge($line, $line_s, $start, false, true);
			
			$html .= trim($line_s[$start]) . $end;
		}
		else
		{
			$html .= "\n  <td$colspan></td>";
		}
	}
	
	$html .= "\n</tr>\n";
	return $html;
}


// Function:	_wiki_block_line
// Access:		INTERNAL
// Parameters:	$line		line tokens
//				$line_s		line strings
// Returns:		a block line entry
// Description:	checks the line to see what kind of block line the line is
//
function _wiki_block_line ( $line, $line_s )
{
	$len = count($line);
	
	if ($len >= 2)
	{
		// : term : definition
		if (	$line[0] == ':' 
			&&	$line[1] == ' ')
		{
			// Try to find (' ', ':' , ' ');
			$i    = 2;
			$offs = false;
			while ($i < $len - 2 && $offs === false)
			{
				if ($line[$i] == ':' && $line[$i-1] == ' ' && $line[$i+1] == ' ')
				{
					$offs = $i-1;
				}
				$i++;
			}
			
			if ($offs !== false)
			{
				return array('dl', 0, $offs, $line, $line_s);
			}
		}
		
		// || td || .. ||
		if ($line[0] == '||' && $line[$len-1] == '||')
		{
			// count the number of cols
			$cols = 0;
			for ($i = 0; $i<$len; $i++)
			{
				if ($line[$i] == '||')
				{
					$cols++;
				}
			}
			return array('table', 0, $cols-1, $line, $line_s);
		}
		
		// > block quoted text
		if ($line[0] == '>' && $line[1] == ' ')
		{
			return array('blockquote', strlen($line_s[0]), 0, $line, $line_s);
		}

		// * unordered list
		if ($line[0] == '*' && $line[1] == ' ')
		{
			return array('ul', 0, 0, $line, $line_s);
		}
		if ($line[0] == ' ' && $line[1] == '*' && $line[2] == ' ')
		{
			return array('ul', strlen($line_s[0]), 0, $line, $line_s);
		}

		// # ordered list
		if ($line[0] == '#' && $line[1] == ' ')
		{
			return array('ol', 0, 0, $line, $line_s);
		}
		if ($line[0] == ' ' && $line[1] == '#' && $len > 2 && $line[2] == ' ')
		{
			return array('ol', strlen($line_s[0]), 0, $line, $line_s);
		}
	}
	
	// Just another part of a paragraph
	if ($len > 0 && $line[0] == 'end')
	{
		return array('end', 0, 0, $line, $line_s);
	}
	else
	{
		return array('p', 0, 0, $line, $line_s);
	}
}


// Function:	_wiki_reduce_line
// Access:		INTERNAL
// Parameters:	$block		the tokens in the block
// 				$line		the line stack
//				$line_s		line texts
// Returns:		html fragment
//				modified block
// Description:	Reduce the current line and append it to the current block.
//				The reduction of a single line checks for:
//				- non reduced :// or mailto: urls
//				- non reduced wiki words
//				- headers
//				- blockquote levels
//				- enumerated lists
//				- table rows
//
function _wiki_reduce_line ( $block, $line, $line_s )
{
	// wiki words
	list($line, $line_s) = _wiki_replace_wikiwords($line, $line_s);
	
	if (count($line) == 1 && $line[0] == '-' && (strlen($line_s[0]) == 4 || strlen($line_s[0]) == 3))
	{
		// horiz	\n----\n
		$html = _wiki_reduce_block($block);
		return array($html . "\n<hr />\n", array());
	}
	
	if (count($line) > 2 && $line[0] == '+' && $line[1] == ' ' && strlen($line_s[0]) <= 6)
	{
		//  \n+++++ headline 1..6
		list($line, $line_s) = _wiki_merge($line, $line_s, 2, false, true);
		$html   = _wiki_reduce_block($block);
		$level  = strlen($line_s[0]);
		$html  .= "\n<h$level>".trim($line_s[2])."</h$level>\n";
		
		return array($html, array());
	}
	
	return _wiki_shift_reduce_block($block, $line, $line_s);
}


// Function:	_wiki_shift_reduce
// Access:		INTERNAL
// Parameters:	$line
//				$line_s
//				$tok
//				$tok_s
// Returns:		the new line state
// Description:	Shifts the given token on the stack and reduces the stack
//				returning a new line state.
//
function _wiki_shift_reduce ( $line, $line_s, $tok, $tok_s )
{
	switch ($tok)
	{
	case "em":
	case "strong":
	case "sup":
	case '}}':
		//  "//"  or "**" or "^^" or {{ }}
		$offs = _wiki_offset($line, _wiki_inline_start($tok));
		if ($offs !== false)
		{
			list($line, $line_s) = _wiki_merge($line, $line_s, $offs+1, false, true);
			array_pop($line);
			$text	  = array_pop($line_s);
			array_pop($line);
			array_pop($line_s);
			$line[]   = 'html';
			$line_s[] =  _wiki_inline_html($tok, $text);
		}
		else
		{
			$line[]   = $tok;
			$line_s[] = $tok_s; 
		}
		break;
		
	case '@@':
		// @@---minus+++revision@@
		$offs = _wiki_offset($line, '@@');
		if ($offs !== false)
		{
			list($line, $line_s) = _wiki_reduce_revise($line, $line_s, $offs);
		}
		else
		{
			$line[]   = $tok;
			$line_s[] = $tok_s; 
		}
		break;

	case '##':
		// ##color|text##
		$offs = _wiki_offset($line, '##');
		if ($offs !== false)
		{
			list($line, $line_s) = _wiki_reduce_colortext($line, $line_s, $offs);
		}
		else
		{
			$line[]   = $tok;
			$line_s[] = $tok_s; 
		}
		break;

	case ']':
		// [uri descr]
		$offs = _wiki_offset($line, '[');
		if ($offs !== false)
		{
			list($line, $line_s) = _wiki_reduce_link($line, $line_s, $offs);
		}
		else
		{
			$line[]   = $tok;
			$line_s[] = $tok_s; 
		}
		break;

	case ']]':
		// [[# anchor-name]]
		// [[image iamge-pars]]
		$offs = _wiki_offset($line, '[[');
		if ($offs !== false && $line[$offs+1] == '#')
		{
			list($line, $line_s) = _wiki_reduce_anchor($line, $line_s, $offs);
		}
		else if ($offs !== false && $line[$offs+1] == 'word' && $line_s[$offs+1] == 'image')
		{
			list($line, $line_s) = _wiki_reduce_image($line, $line_s, $offs);
		}
		else
		{
			$line[]   = $tok;
			$line_s[] = $tok_s; 
		}
		break;
		
	case '))':
		// ((name|descr))
		$offs = _wiki_offset($line, '((');
		if ($offs !== false)
		{
			list($line, $line_s) = _wiki_reduce_freelink($line, $line_s, $offs);
		}
		else
		{
			$line[]   = $tok;
			$line_s[] = $tok_s; 
		}
		break;

	case 'comment':
		$line[]   = 'html';
		$line_s[] = '<!-- '. htmlspecialchars(trim($tok_s)) . ' -->';
		break;

	default:
		$line[]	  = $tok;
		$line_s[] = $tok_s;
		break;
	}
	
	return array($line, $line_s);
}



// helper for @@--- +++ @@ revision patterns
function _wiki_reduce_revise ( $line, $line_s, $offs )
{
	// @@---minus+++revision@@
	$len  = count($line_s);
	$offs = _wiki_offset($line, '@@');
	if (	$offs !== false 
		&&	$offs < $len-1 
		&&	($line_s[$offs+1] == '---' || $line_s[$offs+1] == '+++'))
	{
		if ($line_s[$offs+1] === '---')
		{
			$offs_del = $offs+1;
			
			$offs_ins = $offs+2;
			
			// Try to find the '+++'
			while ($offs_ins < $len && $line_s[$offs_ins] != '+++')
			{
				$offs_ins++;
			}
		}
		else
		{
			$offs_del = false;
			$offs_ins = $offs+1;
		}
		
		if ($offs_ins < $len)
		{
			list($line, $line_s) = _wiki_merge($line, $line_s, $offs_ins+1, false, true);
			array_pop($line);
			$ins = array_pop($line_s);

			// Remove the '+++'
			array_pop($line);
			array_pop($line_s);
		}
		else
		{
			$ins = false;
		}
		
		if ($offs_del !== false)
		{
			list($line, $line_s) = _wiki_merge($line, $line_s, $offs_del+1, false, true);
			array_pop($line);
			$del = array_pop($line_s);

			// Remove the '---'
			array_pop($line);
			array_pop($line_s);
		}
		else
		{
			$del = false;
		}

		// Remove the '@@';
		array_pop($line);
		array_pop($line_s);
		
		if (!empty($del))
		{
			$line[]   = 'html';
			$line_s[] =  _wiki_inline_html('del', $del);
		}
		if (!empty($ins))
		{
			$line[]   = 'html';
			$line_s[] =  _wiki_inline_html('ins', $ins);
		}
	}
	return array($line, $line_s);
}


// helper for [[# anchor-name]]
function _wiki_reduce_anchor ( $line, $line_s, $offs )
{
	// fetch the anchor name
	list($line, $line_s) = _wiki_merge($line, $line_s, $offs+2, -1, false, false);

	// pop the name
	array_pop($line);
	$name = array_pop($line_s);
	
	// pop the #
	array_pop($line);
	array_pop($line_s);

	$line[$offs]   = 'html';
	$line_s[$offs] = '<a name="' . htmlspecialchars(trim($name)) . '"></a>';
	
	return array($line, $line_s);
}


// helper for [[image path/to/image image-pars]]
function _wiki_reduce_image ( $line, $line_s, $offs )
{
	// fetch the complete text
	list($line, $line_s) = _wiki_merge($line, $line_s, $offs+2, -1, false, false);

	// pop the image path and parameters
	array_pop($line);
	$text = trim(array_pop($line_s));

	// pop 'image'
	array_pop($line);
	array_pop($line_s);
	
	// Extract the interesting parts from the image description
	$pos = strpos($text, ' ');
	
	if ($pos === false) 
	{
		$src  = $text;
		$attr = array();
	}
	else 
	{
		$src  = substr($text, 0, $pos);
		$attr = _wiki_get_attrs(substr($text, $pos+1));
	}

	// Remove double quotes around the uri, some people do type them...
	if (strlen($src) >= 2 && $src{0} == '"' && $src{strlen($src)-1} == '"')
	{
		$src = substr($src, 1, -1);
	}

	// We have to postpone the image generation till 'showtime' because an image
	// typically refers to data that is dynamic.  So we just pack the image data
	// in a special tag and do an expand in smarty.

	if (	(	strpos($src, '://') !== false 
			||	strpos($src, '/') !== false
			||	preg_match('/^[a-zA-Z0-9_]+\.[a-z]{3}$/', $src))
		&&	(	empty($attr['link'])
			||	(	strpos($attr['link'], '://') !== false
				&&	strncasecmp($attr['link'], 'popup:', 6) != 0)))
	{
		if (!empty($attr['link']))
		{
			// Remove double quotes around the uri, some people do type them...
			$link = $attr['link'];
			if (strlen($link) >= 2 && $link{0} == '"' && $link{strlen($link)-1} == '"')
			{
				$link = substr($link, 1, -1);
			}
	
			$pre  = '<a href="' . htmlspecialchars(wiki_filter_uri($link)) . '">';
			$post = '</a>';
			unset($attr['link']);
		}
		else
		{
			$pre  = '';
			$post = '';
		}
		
		$html = $pre . '<img src="'. htmlspecialchars($src) . '" ';

		if (!isset($attr['alt']))
		{
			$attr['alt'] = '';
		}

		$attr = wiki_filter_attrs($attr);

		foreach ($attr as $label=>$value)
		{
			$html .= htmlspecialchars($label) . '="' . htmlspecialchars($value) .'" ';
		}
		$html .= '/>' . $post;
	}
	else
	{
		// Pack the attributes so that we can easily expand them again.
		$html  = '<!--[image ';
		$html .= htmlspecialchars($src);

		if (!empty($attr['link']))
		{
			$attr['link'] = wiki_filter_uri($attr['link']);
		}
		$attr  = wiki_filter_attrs($attr);

		foreach ($attr as $label=>$value)
		{
			$html .= ' ' . htmlspecialchars($label) . '="' . htmlspecialchars($value) .'"';
		}
		$html .= ']-->';
	}
	
	$line[$offs] 	= 'html';
	$line_s[$offs] 	= $html;
	
	return array($line, $line_s);
}



// helper for ##color| ## colored text
function _wiki_reduce_colortext ( $line, $line_s, $offs )
{
	// Check for the optional description
	$space = _wiki_after($line, '|', $offs);
	if ($space != false)
	{
		// Fetch description of link
		list($line, $line_s) = _wiki_merge($line, $line_s, $space+1, -1, true);
		array_pop($line);
		$text = trim(array_pop($line_s));

		array_pop($line);
		array_pop($line_s);
	}
	else
	{
		$text = false;
	}

	// Merge all tokens for the color
	list($line, $line_s) = _wiki_merge($line, $line_s, $offs+1, -1, false);
	array_pop($line);
	$color = trim(array_pop($line_s));

	if (	(strlen($color) == 3 || strlen($color) === 6)
		&&	preg_match('/^[0-9a-fA-F]+$/', $color))
	{
		$color = '#' . $color;
	}
	
	// pop the opening '##'
	array_pop($line);
	array_pop($line_s);
	
	// Create the span
	if (!empty($text))
	{
		$line[]   = 'html';
		$line_s[] = "<span style=\"color: $color;\">$text</span>";
	}	
	return array($line, $line_s);
}


// helper for [uri descr]
function _wiki_reduce_link ( $line, $line_s, $offs )
{
	// Keep a copy of line/line_s in case we don't find an uri
	$line0   = $line;
	$line_s0 = $line_s;

	// Check for the optional description
	$space = _wiki_after($line, ' ', $offs);
	if ($space != false)
	{
		// Fetch description of link
		list($line, $line_s) = _wiki_merge($line, $line_s, $space, -1, false);
		array_pop($line);
		$descr = trim(array_pop($line_s));
		
		// Try to fetch any optional attributes
		list($descr, $attrs) = _wiki_split_descr_attrs($descr);
	}
	else
	{
		$descr = false;
		$attrs = false;
	}

	// Merge all tokens for the uri
	list($line, $line_s) = _wiki_merge($line, $line_s, $offs+1, -1, false, false);
	array_pop($line);
	$uri   = array_pop($line_s);

	// only accept this construct when the uri looks like an uri
	$colon = strpos($uri, ':');
	$dot   = strpos($uri, '.');
	$last  = strlen($uri) - 1;

	if (	strpos($uri, '/') !== false
		||	strpos($uri, '#') !== false
		||	($dot !== false && $dot < $last)
		||	($colon > 0 && $colon < $last))
	{
		// pop the opening '['
		array_pop($line);
		array_pop($line_s);
	
		// Create the link
		if (empty($descr))
		{
			// Footnote
			$html = '<sup>' . _wiki_make_link($uri, '*', '', $attrs) .'</sup>';
		}
		else
		{
			// Described link
			$html = _wiki_make_link($uri, $descr, '', $attrs);
		}
		$line[]   = 'html';
		$line_s[] = $html;
	}
	else
	{
		// No uri found, do not reduce the found [uri descr] construct
		$line     = $line0;
		$line_s   = $line_s0;
		
		$line[]   = ']';
		$line_s[] = ']';
	}
	return array($line, $line_s);
}


// helper for ((uri|descr))
function _wiki_reduce_freelink ( $line, $line_s, $offs )
{
	// Check for the optional description
	$anchor = false;
	$pipe   = _wiki_after($line, '|', $offs);
	if ($pipe != false)
	{
		$hash = _wiki_after($line, '#', $pipe, true);
		if ($hash !== false)
		{
			list($line, $line_s) = _wiki_merge($line, $line_s, $hash+1, -1, false, false);
			array_pop($line);
			$anchor = '#' . trim(array_pop($line_s));

			array_pop($line);
			array_pop($line_s);
		}
		
		// Fetch description of link
		list($line, $line_s) = _wiki_merge($line, $line_s, $pipe+1, -1, false);
		array_pop($line);
		$descr = trim(array_pop($line_s));

		list($descr, $attrs) = _wiki_split_descr_attrs($descr);
		
		array_pop($line);
		array_pop($line_s);
	}
	else
	{
		$descr = false;
		$attrs = false;
	}

	// Merge all tokens for the uri (we will need unescaped text for this one)
	list($line, $line_s) = _wiki_merge($line, $line_s, $offs+1, -1, false, false);
	array_pop($line);
	$uri = array_pop($line_s);

	// pop the opening '['
	array_pop($line);
	array_pop($line_s);
	
	// Create the link
	$line[]   = 'html';
	$line_s[] = _wiki_make_link($uri, $descr, $anchor, $attrs);
	
	return array($line, $line_s);
}


// Function:	_wiki_offset
// Access:		INTERNAL
// Parameters:	$stack		stack with tokens
//				$tok		try to find this token
//				$start		(optional) look below this offset
// Returns:		offset in stack
//				false when not found
// Description:	try to locate the token the stack in the stack,
//				starting to search on top
//
function _wiki_offset ( $stack, $tok, $start = false )
{
	if ($start === false)
	{
		$start = count($stack) - 1;
	}
	else
	{
		$start--;
	}
	
	// Don't scan through tds...
	while (		$start >= 0 
			&&	$stack[$start] != $tok 
			&&	($tok == '||' || $stack[$start] != '||'))
	{
		$start--;
	}
	
	if ($start < 0 || $stack[$start] != $tok)
	{
		$start = false;
	}
	return $start;
}


// Function:	_wiki_after
// Access:		INTERNAL
// Parameters:	$line		list of tokens
// 				$tok		token to find
// 				$offs		offset to start above
//				$space		(optional) set to false to disallow whitespace
// Returns:		false when not found
//				offset otherwise
// Description:	find the given token _after_ the given offset
//
function _wiki_after ( $line, $tok, $offset, $space = true )
{
	$ct = count($line);
	while (		$offset < $ct && $line[$offset] != $tok
			&&	($space	|| $line[$offset] != ' '))
	{
		$offset ++;
	}
	
	if ($offset == $ct || $line[$offset] != $tok)
	{
		return false;
	}
	else
	{
		return $offset;
	}
}


// Function:	_wiki_merge
// Access:		INTERNAL
// Parameters:	$stack		the token stack
//				$stack_s	the texts of the stack
//				$depth		the offset to start the merge
//				$count		number of tokens to merge (-1 for all)
//				$replace	do some wikiword on uri replacements
//				$escape		(optional) set to false to not escape html specialchars
// Returns:		modified token stack
// Description:	merges the given entries into one textual entry
//				literal and word entries will be escaped with htmlspecialchars.
//
function _wiki_merge ( $stack, $stack_s, $offset, $count, $replace, $escape = true )
{
	if ($count <= 0)
	{
		$len  = count($stack);
	}
	else
	{
		$len = min(count($stack), $offset+$count);
	}
	
	$text = '';
	for ($i=$offset; $i<$len; $i++)
	{
		if ($replace && $stack[$i] == 'wiki-word')
		{
			$text .= _wiki_make_link($stack_s[$i],'');
		}
		else if ($stack[$i] == 'html')
		{
			$text .= $stack_s[$i];
		}
		else if ($stack[$i] == 'literal')
		{
			$text .= '<!--[lit]-->' . htmlspecialchars($stack_s[$i]) . '<!--[/lit]-->';
		}
		else if ($replace && $stack[$i] == 'url')
		{
			@list($protocol, $address) = explode('://', $stack_s[$i]);
			$text .= '<a href="'.htmlspecialchars($stack_s[$i]).'">' . htmlspecialchars($address) . "</a>"; 
		}
		else if ($replace && $stack[$i] == 'mailto')
		{
			// Add a marker to the mailto so that we can rebuild the wiki text
			$text .= '<!--[mailto]-->' 
					.  substr(htmlspecialchars($stack_s[$i]), 7)
					. '<!--[/mailto]-->';
		}
		else if ($escape)
		{
			$text .= htmlspecialchars($stack_s[$i]);
		}
		else
		{
			$text .= $stack_s[$i];
		}
	}
	
	if ($len == count($stack))
	{
		array_splice($stack,   $offset);
		array_splice($stack_s, $offset);
	}
	else
	{
		array_splice($stack,   $offset, $count);
		array_splice($stack_s, $offset, $count);
	}
	
	if ($escape)
	{
		$stack[] = 'html';
	}	
	else
	{
		$stack[] = 'text';
	}
	$stack_s[] = $text;
	return array($stack, $stack_s);
}



// Function:	_wiki_make_link
// Access:		INTERNAL
// Parameters:	$uri			url, not escaped
//				$descr			description, escaped
//				$anchor			optional anchor ('#anchor')
//				$attrs			attributes ( attr="value" )
// Returns:		complete <a/> anchor tag
// Description:	creates the anchor tag for the given uri and descr.
//				when descr is empty then the anchor tag is generated from the uri.
//
function _wiki_make_link ( $uri, $descr, $anchor = '', $attrs = array() )
{
	$uri = trim($uri);
	if (!empty($descr))
	{
		$descr = trim($descr);
	}

	// Remove double quotes around the uri, some people do type them...
	if (strlen($uri) >= 2 && $uri{0} == '"' && $uri{strlen($uri)-1} == '"')
	{
		$uri = substr($uri, 1, -1);
	}
	
	$pre  = '';
	$post = '';

	if (!empty($attrs))
	{
		$attrs = ' ' . implode(' ', $attrs);
	}
	else
	{
		$attrs = '';
	}
	
	// 1. Check if the uri is a complete one
	if (strncasecmp($uri, 'mailto:', 7) == 0)
	{
		// Add a marker to the mailto so that we can rebuild the wiki text
		$descr = trim($descr);
		if (!empty($descr))
		{
			$descr = ' '.$descr;
		}
		$text   = '<!--[mailto]-->' 
				.  htmlspecialchars(substr($uri, 7)) . htmlspecialchars($descr)
				. '<!--[/mailto]-->';
		
		// Bail out!
		return $text;
	}
	else if (	strpos($uri, '/') === false  
			&&	!preg_match('/^[a-zA-Z0-9_\-]+\.[a-zA-Z]{2,4}/', $uri)
			&&	strncasecmp($uri, 'javascript:', 11) != 0)
	{
		// assume symbolic name
		if (empty($descr))
		{
			// Bail Out: Make special runtime tag, we will need the title of the thing we are linking to...
			$pre  = '<!--[link ' . htmlspecialchars($uri) . htmlspecialchars($anchor) . $attrs . ']-->';
			$post = '<!--[/link]-->';
			
			$descr = htmlspecialchars($uri);
		}
		
		if (!empty($uri))
		{
			$uri = "id.php/" . str_replace(' ', '%20', $uri);
		}
		else if (empty($anchor))
		{
			$anchor = '#';
		}
	}
	else if (	!empty($uri)
			&&	strpos($uri, '://') === false
			&&	strncasecmp($uri, 'javascript:', 11) != 0
			&&	preg_match('/^[a-z]+(\.[a-z]+)(\.[a-z]+)+(\/.*)?$/', $uri))
	{
		// Make sure we have a protocol for our link, better for <a/> tags
		$uri = 'http://' . $uri;
	}
	
	// 2. Extract a description when we don't have one
	if (empty($descr) && strpos($uri, '://') !== false)
	{
		list($protocol, $col, $descr) = explode('://', $uri);
	}
	
	if (empty($descr))
	{
		$descr = $uri;
	}
	
	if (isset($GLOBALS['any_acl']))
	{
		$uri = $GLOBALS['any_acl']->filterUri($uri);
	}
	return $pre . '<a href="' . htmlspecialchars($uri) . htmlspecialchars($anchor) . '"' . $attrs . '>' . $descr . '</a>' . $post;
}


// Function:	_wiki_inline_start
// Access:		INTERNAL
// Parameters:	$descr
// Returns:		list($descr, $attrs)
// Description:	splits any  attr="value" attributes from the given description
//				returns the descr and the list of attributes
//
function _wiki_split_descr_attrs ( $descr )
{
	global $_attrs;

	$_attrs = array();
	$descr  = preg_replace_callback('/\s([a-zA-Z]+)=("|&quot;)(.*?)("|&quot;)/', '_wiki_collect_attr', ' ' . $descr);
	return array(trim($descr), $_attrs);
}


// Helper function to collect all attributes from the descr
function _wiki_collect_attr ( $match )
{
	global $_attrs;
	global $any_acl;
	
	if (	$match[1] == 'target' 
		||	$match[1] == 'class'
		||	$any_acl->allowHtml())
	{
		$_attrs[] = $match[1] . '="' . $match[3] . '"';

		return '';
	}
	else
	{
		return $match[0];
	}
}


// Function:	_wiki_inline_start
// Access:		INTERNAL
// Parameters:	$tok
// Returns:		start token for $tok
// Description:	returns the start token belonging to the inline token $tok
//
function _wiki_inline_start ( $tok )
{
	switch ($tok)
	{
	case '}}':
		return '{{';

	default:
		break;
	}
	return $tok;
}


// Function:	_wiki_inline_html
// Access:		INTERNAL
// Parameters:	$tok
//				$text
// Returns:		html for text
// Description:	surrounds text with the correct html tags for $tok
//
function _wiki_inline_html ( $tok, $text )
{
	switch ($tok)
	{
	case '}}':
		$tag = 'tt';
		break;
	default:
		$tag = $tok;
		break;
	}
	
	return "<$tag>$text</$tag>";
}



// Function:	_wiki_replace_wikiwords
// Access:		INTERNAL
// Parameters:	$line
//				$line_s
//				$offset		(optional) start scanning at offset
//				$end		(optional) stop at offset
// Returns:		(line, line_s)
// Description:	scans the line for WikiWords, when found then replaces them
//				with HTML fragments for freelinks.
//
function _wiki_replace_wikiwords( $line, $line_s, $offset = 0, $end = false )
{
	if ($end === false)
	{
		$end = count($line);
	}
	
	for ($i = $offset; $i< $end; $i++)
	{
		if ($line[$i] == 'wiki-word')
		{
			$line[$i]   = 'html';
			$line_s[$i] = _wiki_make_link($line_s[$i], '');
		}
	}
	
	return array($line, $line_s);
}


// Function:	_wiki_get_attrs
// Access:		INTERNAL
// Parameters:	$text	the text containing 'attr="value"' pairs
// Returns:		array with attr=>value pairs
// Description:	parses the attributes of a tag
//
function _wiki_get_attrs ( $text )
{
	$parts	= explode('="', trim($text));
	$last	= count($parts) - 1;
	$attrs	= array();
	$key	= false;
	
	foreach ($parts as $i => $val) 
	{
		if ($i == 0) 
		{
			$key = trim($val);
		}
		else
		{
			$pos 		 = strrpos($val, '"');
			$attrs[$key] = stripslashes(substr($val, 0, $pos));
			$key 		 = trim(substr($val, $pos+1));
		}
	}
	return $attrs;
}



// Function:	_wiki_toc
// Access:		INTERNAL
// Parameters:	$html	html with a toc marker
// Returns:		html with a table of contents
// Description:	Inserts a table of contents into the html
//
function _wiki_toc ( $html )
{
	global $toc_nr;
	global $toc_base;
	global $toc;
	
	$pos = strpos($html, '<!--[[toc]]-->');
	if ($pos !== false)
	{
		$toc_base = abs(crc32(microtime(true).'-'.rand(0,100))); 
		$toc_nr   = 0;
		
		// 1. Find all <h[2-6]> tags for insertion in the table of contents, no h1 tags are inserted
		$html = preg_replace_callback('|(<h[2-6]>)(.*)(</h[2-6]>)|U', '_wiki_toc_accum', $html);

		// 2. Create the table of contents at the place of the toc tag
		$s = "<!--[toc]-->\n<ul class='wikitoc'>\n";
		foreach ($toc as $entry)
		{
			list($anchor, $level, $title) = $entry;
			$s .= "<li class='wikitoc$level'><a href='#$anchor'>$title</a></li>\n";
		}
		$s   .= "</ul>\n<!--[/toc]-->\n";
		$html = str_replace('<!--[[toc]]-->', $s, $html);
	}
	return $html;
}


function _wiki_toc_accum ( $ms )
{
	global $toc_nr;
	global $toc_base;
	global $toc;
	
	$toc_nr++;
	$anchor = "$toc_base-$toc_nr";
	$toc[]  = array($anchor, $ms[1]{2}, $ms[2]);
	return $ms[1]."<a name='$anchor'></a>".$ms[2].$ms[3];
}

?>