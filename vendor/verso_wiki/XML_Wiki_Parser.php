<?

// File:		$Id: XML_Wiki_Parser.php 29975 2007-08-10 11:41:52Z marc $
// Author:		Marc Worrell
// Copyright:	(c) 2005-2007 Marc Worrell
// Description:	Parses the xhtml and produces some wiki tags
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

// Include the PEAR XML/Parser
require_once('XML/Parser.php');


//// The wiki unparser

class XML_Wiki_Parser extends XML_Parser
{
	public $wiki			= '';
	
	public $blockquote_level= 0;
	public $list_level		= 0;
	public $list_nesting	= array('*');

	public $in_block		= 0;
	public $in_elt			= 0;
	public $in_html			= false;
	public $in_code			= false;
	
	public $href			= false;
	public $collect			= false;
	public $collect_s		= '';
	public $attribs			= false;
	
	function reset ()
	{
		$this->wiki			= '';
	
		$this->blockquote_level= 0;
		$this->list_level	= 0;
		$this->list_nesting	= array('*');

		$this->in_block		= 0;
		$this->in_elt		= 0;
		$this->in_html		= false;
		$this->in_code		= false;
	
		$this->href			= false;
		$this->collect		= false;
		$this->collect_s	= '';
		$this->attribs		= false;
		return parent::reset();
	}

	function startHandler ( $xp, $elem, &$attribs )
	{
		switch ($elem)
		{
		case 'PRE':
			$this->in_code = true;
			$this->wiki   .= "\n<code>";
			break;
		case 'HTML':
			$this->in_html = true;
			$this->wiki   .= "\n<html>";
			break;
		case 'X-COMMENT':
			$this->in_html = true;
			$this->wiki   .= '<!-- ';
			break;
		case 'H1':
		case 'H2':
		case 'H3':
		case 'H4':
		case 'H5':
		case 'H6':
			$this->wiki .= "\n" . str_repeat('+', $elem{1}) . ' ';
			break;
		case 'HR':
			$this->wiki .= "\n----\n";
			break;
			
		case 'UL':
			$this->list_level++;
			$this->in_block++;
			array_unshift($this->list_nesting, '*');
			break;
		case 'OL':
			$this->list_level++;
			$this->in_block++;
			array_unshift($this->list_nesting, '#');
			break;
		case 'LI':
			$this->in_elt++;
			$this->wiki .= "\n" . str_repeat(' ', $this->list_level-1) . $this->list_nesting[0] . ' ';
			break;
		case 'BLOCKQUOTE':
			$this->blockquote_level++;
			$this->wiki .= "\n" . str_repeat('>', $this->blockquote_level) . ' ';
			break;
		case 'BR':
			if ($this->blockquote_level > 0)
			{
				$this->wiki .= "\n" . str_repeat('>', $this->blockquote_level) . ' ';
			}
			else
			{
				// When we are at the start of a line then use a " _\n" token as the newline
				// Otherwise we are in a paragraph, so we can use a simple newline.
				// This is less confusing for editors - they don't understand the underscores
				// appearing in their texts.
				if (empty($this->wiki))
				{
					$this->wiki .= " _\n";
				}
				else
				{
					$last = substr($this->wiki, -1);
					if ($last == '>' || $last == "\n")
					{
						$this->wiki .= " _\n";
					}
					else
					{
						$this->wiki .= "\n";
					}
				}
			}
			break;
		case 'P':
			if ($this->blockquote_level == 0)
			{
				$this->wiki .= "\n";
			}
			break;
		case 'DIV':
			if (!empty($attribs['STYLE']) && strpos($attribs['STYLE'], 'center') !== false)
			{
				$this->wiki .= "\n= ";
			}
			break;
			
		case 'TABLE':
			$this->wiki .= "\n";
			$this->in_block++;
			break;
		case 'TR':
			break;
		case 'TH':
		case 'TD':
			$this->in_elt++;
			if (!empty($attribs['ALIGN']))
			{
				$align = $attribs['ALIGN'];
			}
			else if (!empty($attribs['STYLE']))
			{
				$align = $attribs['STYLE'];
			}
			else
			{
				$align = '';
			}
			
			if (!empty($attribs['COLSPAN']))
			{
				$colspan = @intval($attribs['COLSPAN']);
			}
			else
			{
				$colspan = 1;
			}
			$this->wiki .= str_repeat('||', max(1,$colspan));
			
			if ($elem == 'TH')
			{
				$this->wiki .= "~ ";
			}

			if (strpos($align, 'left') !== false)
			{
				$this->wiki .= "< ";
			}
			else if (strpos($align, 'right') !== false)
			{
				$this->wiki .= "> ";
			}
			else if (strpos($align, 'center') !== false)
			{
				$this->wiki .= "= ";
			}
			else
			{
				$this->wiki .= " ";
			}
			break;
		case 'DL':
			$this->wiki .= "\n";
			$this->in_block++;
			break;
		case 'DT':
			$this->in_elt++;
			$this->wiki .= "\n: ";
			break;
		case 'DD':
			$this->in_elt++;
			$this->wiki .= " : ";
			break;
		case 'A':
			if (!empty($attribs['NAME']))
			{
				$this->wiki 	.= "[[# ".$attribs['NAME']."]]";
				$this->href	 	= false;
				$this->attribs	= false;
			}
			else if (!empty($attribs['HREF']))
			{
				$this->href      = $attribs['HREF'];
				$this->collect   = true;
				$this->collect_s = '';
				$this->attribs	 = array();
				
				foreach ($attribs as $a => $v)
				{
					if ($a != 'HREF')
					{
						$this->attribs[mb_strtolower($a)] = $v;
					}
				}
			}
			break;
		case 'IMG':
			$this->wiki .= '[[image ' .$attribs['SRC'];
			if (!empty($this->href))
			{
				$this->wiki .= ' link="'. addslashes($this->href) . '"';
			}
			foreach ($attribs as $name => $val)
			{
				if ($name != 'SRC')
				{
					$this->wiki .= ' '.strtolower($name).'="'.addslashes($val).'"';
				}
			}
			$this->wiki .= ']]';
			$this->href  = '';
			break;
		case 'INS':
			$this->wiki .= '@@+++';
			break;
		case 'DEL':
			$this->wiki .= '@@---';
			break;
		}
	}
	
	function endHandler ( $xp, $elem )
	{
		switch ($elem)
		{
		case 'PRE':
			$this->in_code = false;
			$this->wiki   .= "</code>\n\n";
			break;
		case 'HTML':
			$this->in_html = false;
			$this->wiki   .= "</html>\n\n";
			break;
		case 'X-COMMENT':
			$this->in_html = false;
			$this->wiki   .= ' -->';
			break;
		case 'H1':
		case 'H2':
		case 'H3':
		case 'H4':
		case 'H5':
		case 'H6':
			$this->wiki .= "\n";
			break;
		case 'UL':
		case 'OL':
			$this->list_level--;
			array_shift($this->list_nesting);
			
			if ($this->list_level == 0)
			{
				$this->wiki .= "\n";
			}
			$this->in_block--;
			break;
		case 'LI':
			$this->in_elt--;
			break;
		case 'BLOCKQUOTE':
			$this->blockquote_level--;
			$this->wiki .= "\n";
			break;
		case 'P':
			if ($this->blockquote_level == 0)
			{
				$this->wiki .= "\n";
			}
			break;
		case 'DIV':
			$this->wiki .= "\n";
			break;
		case 'TABLE':
			$this->wiki .= "\n";
			$this->in_block--;
			break;
		case 'TR':
			$this->wiki .= " ||\n";
			break;
		case 'TD':
		case 'TH':
			$this->in_elt--;
			break;
		case 'DL':
			$this->wiki .= "\n";
			$this->in_block--;
			break;
		case 'DT':
			$this->in_elt--;
			break;
		case 'DD':
			$this->in_elt--;
			break;
		case 'A':
			if (!empty($this->href))
			{
				// Split the anchor from the href
				@list($href,$anchor) = split('#', $this->href);
				
				if (!empty($anchor))
				{
					$anchor = '#' . $anchor;
				}
				else
				{
					$anchor = '';
				}
				$href = preg_replace('/^id(\.php)?\//', '', $href);

				// Attributes				
				$as = '';
				if (!empty($this->attribs))
				{
					foreach ($this->attribs as $a => $v)
					{
						$as .= ' ' . $a . '="' . str_replace('"', '\'', $v) . '"';
					}
				}
				
				
				// Could be [uri descr]  or  ((uri|descr))
				if (	strpos($href, '://') !== false 
					&&	strpos($href, ']') === false
					&&	strpos($this->collect_s, ']') === false)
				{
					if (empty($as) && $href.$anchor == 'http://'.$this->collect_s)
					{
						$this->wiki .= 'http://' . $this->collect_s;
					}
					else
					{
						$this->wiki .= '[' . $href . $anchor;
						if ($this->collect_s != '*')
						{
							$this->wiki .=	' ' . $this->collect_s;
						}
						$this->wiki .= $as . ']';
					}
				}
				else if ($href == $this->collect_s && empty($as) && empty($anchor))
				{
					if (preg_match('/[A-Z]+[a-z0-9_]+[A-Z][a-zA-Z0-9_]+/', $href))
					{
						$this->wiki .= $href;
					}
					else
					{
						$this->wiki .= '((' . $href . '))';
					}
				}
				else if ($href == $this->collect_s)
				{
					$this->wiki .= '((' . $href . '|' . trim($as) . $anchor . '))';
				}
				else
				{
					$this->wiki .= '((' . $href . '|' . $this->collect_s . $as . $anchor . '))';
				}
			}
			$this->collect	 = false;
			$this->href		 = false;
			$this->collect_s = '';
			break;
		case 'INS':
		case 'DEL':
			$this->wiki .= '@@';
			break;
		}
	}
	
	function cDataHandler ( $xp, $data )
	{
		// Prevent the collection of newlines from the xml, we insert them ourselves with the tags
		if ($this->in_html || $this->in_code)
		{
			$this->wiki .= $data;
		}
		else if ($this->collect)
		{
			$this->collect_s .= str_replace("\n", '', $data);
		}
		else if ($this->in_block == $this->in_elt)
		{
			$this->wiki .= str_replace("\n", '', $data);
		}
	}
}



?>