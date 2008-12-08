<?

// File:		$Id: any_wiki_tokenizer.php 29975 2007-08-10 11:41:52Z marc $
// Author:		Marc Worrell
// Copyright:	(c) 2005-2007 Marc Worrell
// Description:	Tokenizers for Wiki texts
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


// Function:	wiki_tokenizer
// Access:		EXTERNAL
// Parameters:	$s			input string
//				$options	tokenize options
// Returns:		token stream
// Description:	tokenizes the given wiki stream
//
//				options:
//				- allow_wikiword		default false
//				
function wiki_tokenizer ( $s, $options = array() )
{
	$s  		= wiki_normalize_newlines($s) . "\n\n";
	$i  		= 0;			// the offset of the scanner
	$line_offs	= 0;			// the token offset in the current line
	$len 		= strlen($s);	// the length of the input stream

	$tk			= array();		// the token list returned
	$tk_s		= array();		// token strings

	// Get the settings
	$allow_wikiword	= !empty($options['allow_wikiword']);
	
	// Translate the character stream into tokens, use the ending "\n" as a buffer.
	while ($i < $len-2)
	{
		$c		= $s{$i};
		$n_c	= $s{$i+1};
		$nn_c	= $s{$i+2};
		
		$line_offs++;

		switch ($c)
		{
		case "\n":
			if ($n_c == "\n")
			{
				while ($i < $len - 1 && $s{$i+1} == "\n")
				{
					$i++;
				}
				$tk[]   = "p";
				$tk_s[] = "\n\n";
			}
			else
			{
				$tk[]   = "newline";
				$tk_s[] = "\n";
			}
			$line_offs = 0;
			break;
		
		case ' ':
			if ($n_c == '_'	&& $nn_c == "\n")
			{
				$tk[]   = 'br';
				$tk_s[] = " _\n";
				$i   += 2;
			}
			else
			{
				$tok = ' ';
				while ($s{$i+1} == ' ')
				{
					$tok .= ' ';
					$i++;
				}
				
				if (	$s{$i+1} == '_'
					&&	$s{$i+2} == "\n")
				{
					$tk[]   = 'br';
					$tk_s[] = " _\n";
					$i   += 2;
				}
				else
				{
					$tk[]   = ' ';
					$tk_s[] = $tok;
				}
			}
			break;
		
		case '`':
			if ($n_c == '`')
			{
				$j   = $i+2;
				$tok = '';
				while (		$j < $len - 2
						&&	($s{$j} != '`' || $s{$j+1} != '`'))
				{
					$tok .= $s{$j};
					$j++;
				}
				if ($s{$j} == '`' && $s{$j+1} == '`')
				{
					$tk[]   = 'literal';
					$tk_s[] = str_replace("\n", " ", $tok);
					$i      = $j+1;
				}
				else
				{
					$tk[]	= '`';
					$tk_s[] = '`';
				}
			}
			else
			{
				$tk[]	= '`';
				$tk_s[] = '`';
			}
			break;

		case '<':
			// Check for <html> on one line
			if (	$line_offs == 1
				&&	substr($s, $i, 7) == "<html>\n"
				&&	($end = strpos($s, "\n</html>\n", $i+5)) !== false)
			{
				$tk[]	= 'html';
				$tk_s[] = substr($s, $i+7, $end - ($i+6));
				$i		= $end + 8;
			}
			// Check for <code> on one line
			else if (	$line_offs == 1
					&&	substr($s, $i, 7) == "<code>\n"
					&&	($end = strpos($s, "\n</code>\n", $i+5)) !== false)
			{
				$tk[]	= 'code';
				$tk_s[] = substr($s, $i+7, $end - ($i+6));
				$i		= $end + 8;
			}
			// Check for a <!-- ... --> block
			else if (	substr($s, $i, 4) == '<!--'
					&&	($end = strpos($s, '-->', $i+4)) !== false)
			{
				$tk[]	= 'comment';
				$tk_s[] = trim(substr($s, $i+4, $end - ($i+4)));
				$i		= $end + 2;
			}
			else
			{
				$tk[]	= '<';
				$tk_s[]	= '<';
			}
			break;
			
		case '/':
			if ($n_c == '/')
			{
				$tk[]	= "em";
				$tk_s[]	= "//";
				$i+=1;
			}
			else 
			{
				$tk[]   = $c;
				$tk_s[] = $c;
			}
			break;
			
		case '*':
			if ($n_c == '*')
			{
				$tk[]	= "strong";
				$tk_s[]	= "**";
				$i+=1;
			}
			else 
			{
				$tk[]   = $c;
				$tk_s[] = $c;
			}
			break;
			
		case '^':
			if ($n_c == '^')
			{
				$tk[]	= "sup";
				$tk_s[]	= "^^";
				$i++;
			}
			else 
			{
				$tk[]   = $c;
				$tk_s[] = $c;
			}
			break;
			
		case '@':
		case '#':
		case '(':
		case ')':
		case '|':
		case '[':
		case ']':
		case '{':
		case '}':
			if ($c == '[' & $n_c == '[')
			{
				// check for block-level [[toc]]
				if (	$line_offs == 1
					&&	substr($s, $i, 8) == "[[toc]]\n")
				{
					$tk[]   = 'toc';
					$tk_s[] = '[[toc]]';
					$i     += 6;
				}
				else
				{
					$tk[]   = $c.$c;
					$tk_s[] = $c.$c;
					$i++;
				}
			}
			else if ($n_c == $c)
			{
				$tk[]   = $c.$c;
				$tk_s[] = $c.$c;
				$i++;
			}
			else
			{
				$tk[] 	= $c;
				$tk_s[]	= $c;
			}
			break;
		
		case '>':
			$tok = '>';
			while ($s{$i+1} == '>')
			{
				$tok .= '>';
				$i++;
			}
			$tk[]	= ">";
			$tk_s[]	= $tok;
			break;
			
		case '\'':
			if ($n_c == '\'' && $nn_c == '\'')
			{
				$tk[]	= "strong";
				$tk_s[]	= "'''";
				$i+=2;
			}
			else if ($n_c == '\'')
			{
				$tk[]	= "em";
				$tk_s[]	= "''";
				$i+=1;
			}
			else 
			{
				$tk[]   = $c;
				$tk_s[] = $c;
			}
			break;
			
		case ':':
			if ($n_c == '/' && $nn_c == '/')
			{
				$tk[]   = '://';
				$tk_s[] = '://';
				$i += 2;
			}
			else
			{
				$tk[]   = ':';
				$tk_s[] = ':';
			}
			break;
			
		default:
			$class	= _charclass($c);
			$tok	= $c;
			$j		= $i;
			while ($class == _charclass($s{$j+1}) && $j < $len - 2)
			{
				$j++;
				$tok .= $s{$j};
			}
			
			if ($class == 'word')
			{
				if (	(($tok == 'http' || $tok == 'https') && substr($s, $j+1, 3) == '://')
					||	($tok == 'mailto' && $s[$j+1] == ':'))
				{
					// http://  or   mailto: -- fetch till whitespace or one of "])|>"
					if ($tok == 'mailto')
					{
						$class = 'mailto';
					}
					else
					{
						$class = 'url';
					}
					
					while (strpos("\n\t |[](){}<>\"'", $s{$j+1}) === false)
					{
						$j++;
						$tok .= $s{$j};
					}
				}
				else if (	$allow_wikiword
						&&	$c >= 'A' 
						&&	$c <= 'Z'
						&&	preg_match('/^[A-Z][a-z0-9_]+[A-Z][a-zA-Z0-9_]*$/', $tok))
				{
					$class = "wiki-word";
				}
			}
			$tk[]	= $class;
			$tk_s[]	= $tok;
			
			$i = $j;
			break;
		}
		$i++;
	}
	
	$tk[]   = 'end';
	$tk_s[] = '';
	
	return array($tk, $tk_s);
}


// Function:	_charclass
// Access:		INTERNAL
// Parameters:	$c		character
// Returns:		the class of the character
// Description:	classifies a character as to belong to a wiki token group
//
function _charclass ( $c )
{
	switch ($c)
	{
	case '[':	return $c;
	case ']':	return $c;
	case '*':	return $c;
	case ':':	return $c;
	case '#':	return $c;
	case '\'':	return $c;
	case '/':	return $c;
	case '|':	return $c;
	case '+':	return $c;
	case '-':	return $c;
	case '@':	return $c;
	case ':':	return $c;
	case '^':	return $c;
	case '>':	return $c;
	case '<':	return $c;
	case '=':	return $c;
	case '"':	return $c;
	case '{':	return $c;
	case '}':	return $c;
	case '(':	return $c;
	case ')':	return $c;
	case '~':	return $c;
	case ' ':	
	case "\n":
				return 'ws';
	default:
		return 'word';
	}
}


// Function:	wiki_normalize_newlines
// Access:		EXTERNAL
// Parameters:	$s		input string
// Returns:		string with normalized newlines
// Description:	translates the newlines in the string to unix style
//				concatenates lines ending with a '\'
//
function wiki_normalize_newlines ( $s )
{
	$s = str_replace("\r\n", 	"\n", 	$s);
	$s = str_replace("\r", 		"\n", 	$s);
	$s = str_replace("\\\n", 	" ", 	$s);
	$s = str_replace("\n\n\n", 	"\n\n", $s);
	$s = str_replace("\t",		"    ", $s);
	
	$s = trim($s) . "\n\n";

	return $s;
}


?>