<?

// File:		$Id: any_wiki_runtime.php 29975 2007-08-10 11:41:52Z marc $
// Author:		Marc Worrell
// Copyright:	(c) 2005-2007 Mediamatic
// Description:	Runtime expansion for parsed wiki texts
//
// TODO: 	
//		- suppress javascript popups when the option 'nojavascript' is set
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



// Function:	any_wiki_runtime
// Access:		EXTERNAL
// Parameters:	$text		the text to be expanded
//				$options	(optional) translation options
// Returns:		text for displaying
// Description:	The wiki parser leaves some tags undecided.  The main example is the
//				image tag, which needs to be evaluated at runtime.  Because the images
//				of a thing could change dynamically, producing completely different 
//				image tags between the moment of wiki translation and the moment of
//				serving the page with the translated wiki.
//
//				options:
//
//				nojavascript		true/false		suppress the generation of javascript
//				abs_uri				true/false		make links absolute
//				width				<number>		base width in pixels for images
//				height				<number>		base height in pixels for images
//				thg_id				<id>			the id of the thing the texts belong to
//													(needed to find images)
//
function any_wiki_runtime ( $text, $options = array() )
{
	global $_wiki_options;
	
	$_wiki_options = $options;
	
	// 1. Filter remainings of and html tags
	$text	= preg_replace('/<!--\[\/?(html).*?\]-->/', '', $text);
	
	// 2. Grep all image tags and produce <img /> tags.
	$text	= preg_replace_callback('/<!--\[image ([^\s\]]+)((\s+[a-zA-Z0-9]+)="([^"]*?)")*\]-->/', '_wiki_runtime_image', $text);
	
	// 3. Handle the runtime replacement of links
	$text	= preg_replace_callback('/<!--\[link ([^\]]+?)((\s+[a-zA-Z0-9]+)="([^"]*?)")*\]-->.+?<!--\[\/link\]-->/', '_wiki_runtime_link', $text);

	// 4. Handle the runtime replacement of mailto hrefs
	$text	= preg_replace_callback('/<!--\[mailto\]-->([^ ]+?)( .+?)?<!--\[\/mailto\]-->/', '_wiki_runtime_mailto', $text);

	if (!empty($options['abs_uri']))
	{
		// 5. Make the id.php/xxxx uris absolute
		$text = preg_replace_callback('|<a\s+href="(id.php/[^"]*)">|', '_wiki_runtime_anchor_abs_uri', $text);
	}

	return $text;
}


// Function:	any_wiki_runtime_image_labels
// Access:		EXTERNAL
// Parameters:	$text		the text to be expanded
// Returns:		array with image indices used
// Description:	Fetch all image nrs used in the given text.
//				Does recognise the 'FIGxx' format for labels.
//				Does also handle numerical references correctly
//
function any_wiki_runtime_image_labels ( $text )
{
	$image = array();
	if (preg_match_all('/<!--\[image ([^\s\]]+)\s*[^\]]*\]-->/', $text, $ms, PREG_PATTERN_ORDER))
	{
		foreach ($ms[1] as $m)
		{
			if (strncasecmp($m, 'FIG', 3) == 0)
			{
				$image[] = @intval(substr($m,3)) - 1;
			}
			else if (is_numeric($m) && $m < 100)
			{
				$image[] = intval($m) - 1;
			}
		}
		sort($image);
	}
	return $image; 
}


// Function:	_wiki_runtime_link
// Access:		INTERNAL
// Parameters:	$matches	pattern match of the <!--[link .. ]--> tag
// Returns:		<a /> tag
// Description:	runtime produces the <a /> tag with the correct title.
//
function _wiki_runtime_link ( $matches )
{
	global $_wiki_options;

	$page 		= $matches[1];
	$abs_uri	= !empty($_wiki_options['abs_uri']);
	$attr		= '';
	$ct   		= count($matches);
	for ($i=2; $i<$ct; $i+=3)
	{
		$attr .= ' ' . $matches[$i];
	}
	
	@list($page, $anchor) = explode('#', $page);
	
	if (strncasecmp($page, 'uri:', 4) == 0)
	{
		$page = substr($page, 4);
	}
	else if (   strpos($page, '://') === false
			 && strpos($page, '/'  ) === false)
	{
		// try to translate the given name to an anymeta id
		$thg_id = any_symbolic2id($page);
	}  
	
	if (!empty($thg_id))
	{
		// It is an anymeta id, use the anymeta routines to generate the link

		$text = any_thing_title_short($thg_id);
		if (is_array($text))
		{
			$text = reset($text);
		}
		
		if (empty($text))
		{
			$text = $page;
		}
		
		// Fetch the uri, prefered from the pre-fetched uris in the template
		if ($abs_uri)
		{
			$href = any_thing_uri_abs($thg_id);
		}
		else
		{
			$href = any_thing_uri($thg_id);
		}

		// Add the anchor
		if (!empty($anchor))
		{
			$href .= "#$anchor";
		}
		$html = '<a href="' . htmlspecialchars($href) . '"' . $attr . '>' . htmlspecialchars($text) . '</a>';
	}
	else if (strpos($page, '://') !== false)
	{
		$n    = strpos($page, '://');
		$text = substr($page, $n+3);
		$html = "<a href=\"$page\"$attr>".htmlspecialchars($text)."</a>";
	}
	else
	{
		// the page does not exist, show the page name and
		// the "new page" text
		$page = htmlspecialchars($page);
		$url  = 'id.php/'.urlencode($page);
		if ($abs_uri)
		{
			$url = any_uri_abs($url);
		}
		$html = "<a href=\"$url\"$attr>$page</a>";
	}
	return $html;
}


// Function:	_wiki_runtime_anchor_abs_uri
// Access:		INTERNAL
// Parameters:	$matches	pattern of the <a href="(id.php/...)"> tag
// Returns:		modified tag
// Description:	makes the enclose uri absolute
//
function _wiki_runtime_anchor_abs_uri ( $matches )
{
	return '<a href="'.any_uri_abs($matches[1]).'">';
}


// Function:	_wiki_runtime_mailto
// Access:		INTERNAL
// Parameters:	$matches	pattern match of the <!--[mailto]--> tag
// Returns:		<a /> tag
// Description:	runtime produces the <a /> tag with the correct title.
//
function _wiki_runtime_mailto ( $matches )
{
	global $_wiki_options;
	
	if (empty($_wiki_options['nojavascript']))
	{
		$encode = 'javascript';
	}
	else
	{
		$encode = 'entities';
	}
	return any_encode_mailto($matches[1], @trim($matches[2]), $encode);
}




// Function:	_wiki_runtime_image
// Access:		INTERNAL
// Parameters:	$matches	pattern match of the <!--[image .. ]--> tag
// Returns:		<img /> tag
// Description:	runtime produce the <img /> tag for the given image description
//
function _wiki_runtime_image ( $matches )
{
	global $_wiki_options;

	$attr = array();
	$src  = $matches[1];
	$ct   = count($matches);
	for ($i=2; $i<$ct; $i+=3)
	{
		$attr[trim($matches[$i+1])] = $matches[$i+2];
	}

	$base_thg_id	= !empty($_wiki_options['thg_id'])  ? $_wiki_options['thg_id']  : false;
	$base_width		= !empty($_wiki_options['width'])   ? $_wiki_options['width']   : 400;
	$base_height	= !empty($_wiki_options['height'])  ? $_wiki_options['height']  : 400;
	$abs_uri		= !empty($_wiki_options['abs_uri']) ? $_wiki_options['abs_uri'] : false;

	// Fetch the requested width and height
	if (!empty($attr['width']))
	{
		$width = _wiki_runtime_img_size($attr['width'], $base_width);
	}
	else
	{
		$width = round($base_width);
	}

	if (!empty($attr['height']))
	{
		$height = _wiki_runtime_img_size($attr['height'], $base_height);
	}
	else
	{
		$height = round($base_height);
	}

	if (substr($src, 0, 1) == '"' && substr($src, -1) == '"')
	{
		$src = substr($src, 1, -1);
	}
	$src = trim($src);

	// See where we have to fetch the image from
	if (!empty($src))
	{
		if (strpos($src, 'uri:') === 0)
		{
			// direct uri
			$src = substr($src, 4);
			$id  = false;
		}
		else if (strpos($src, '://') === false)
		{
			if (strpos($src, ':') !== false)
			{
				list($a, $b) = explode(':', $src);
				if (empty($b))
				{
					$thg_id = $a;
					$lbl	= false;
				}
				else if (empty($a))
				{
					$thg_id = $base_thg_id;
					$lbl	= $b;
				}
				else
				{
					$thg_id	= $a;
					$lbl	= $b;
				}
			}
			else
			{
				$thg_id = $base_thg_id;
				$lbl    = $src;
			}
			
			// Try to translate to a real thg_id
			if (!is_numeric($thg_id))
			{
				if (empty($lbl))
				{
					$thg_id = any_symbolic2id($thg_id, array('kind'=>'ATTACHMENT'));
				}
				else
				{
					$thg_id = any_symbolic2id($thg_id);
				}
			}
			
			// Fetch the thing id of the attachment
			if (!empty($lbl) && !empty($thg_id))
			{
				list($pred, $nr)            = any_attach_label2pred($lbl);
				@list($aid, $alt, $caption) = any_attach_caption_pred($thg_id, $pred, $nr);
			}
			else
			{
				// Assume the given src is an attachment id
				if (!empty($attr['caption']))
				{
					@list($alt, $caption) = any_attach_caption($thg_id);
				}
				else
				{
					$alt	 = '';
					$caption = '';
				}
				$aid     = $thg_id;
				$lbl     = false;
			}

			if (empty($caption) && !empty($alt))
			{
				$caption = $alt;
			}
			
			$alt = strip_tags($alt);
		}
		else
		{
			$id  	 = false;
			$aid 	 = false;
			$lbl 	 = false;
			$alt 	 = '';
			$caption = '';
		}
	}
	else
	{
		$src = '#';	// Unknown source
		$id  = false;
		$aid = false;
		$lbl = false;
	}

	if (!empty($attr['caption']))
	{
		if (!empty($caption))
		{
			$alt     = trim($alt);
			$caption = trim(str_replace(array('<p>', '</p>'), array('','<br/>'), $caption));
			while (substr($caption, -5) == '<br/>')
			{
				$caption = trim(substr($caption, 0, -5));
			}

			if (!empty($alt) && !empty($intro))
			{
				$cap = '<span class="caption">';
				if (!empty($alt))
				{
					$cap .= '<span>' . any_wiki_runtime($alt, $_wiki_options) .'</span>';
				}
				if (!empty($caption))
				{
					$cap .= any_wiki_runtime($caption, $_wiki_options);
				}
				$cap .= '</span>';
			}
			else
			{
				$cap = '';
			}

			if (strcasecmp($attr['caption'], 'before') == 0)
			{
				$cap1 = $cap;
				$cap2 = '';
			}
			else
			{
				$cap1 = '';
				$cap2 = $cap;
			}
		}
		else
		{
			$cap1 = '';
			$cap2 = '';
		}
		
		unset($attr['caption']);
	}
	else
	{
		$cap1 = '';
		$cap2 = '';
	}


	//
	// Expand the anchor tag around the image
	//
	if (array_key_exists('link',$attr))
	{
		$link = trim($attr['link']);
	}
	else
	{
		$link = false;
	}
	$expand = false;

	if (empty($link) && !empty($aid))
	{
		// Link to the attachment ;-)
		$href = 'id/' . $aid;

		if ($abs_uri)
		{
			$href = any_uri_abs($href);
		}
	}
	else if ($link[0] == '#')
	{
		// Ref to local anchor
		$href = $link;
	}
	else if (	strpos($link, '://') > 0
			||	strpos($link, '.php') !== false
			||	strpos($link, '.html') !== false
			||	strpos($link, '.htm') !== false)
	{
		// Literal link
		$href = $link;
	}
	else if (strncmp($link, 'javascript:', 12) == 0)
	{
		$href = $link;
	}
	else if (strncmp($link, 'popup:', 6) == 0)
	{
		$popup = substr($link, 6);
		if (strlen($popup) == 0)
		{
			if (is_numeric($thg_id))
			{
				$label = addslashes($lbl);
				$href  = "javascript:popup('$thg_id','$label')";
			}
			else if (!empty($aid) && is_numeric($aid))
			{
				$href   = "javascript:popup('{$aid}')";
				$expand = true;
			}
		}
		else
		{
			// Undefined behaviour for now...
			$href = false;
		}
	}
	else
	{
		// Assume a thing id
		if (!empty($abs_uri))
		{
			$href = any_thing_uri_abs($link);
		}
		else
		{
			$href = any_thing_uri($link);
		}
	}

	// Perform any macro expansion (when needed)
	if (!empty($href) && $expand)
	{
		$href = str_replace(array('{$thg_id}', '{$label}', '{$att_id}'),
							array($thg_id, $lbl, $aid),
							$href);
	}
	$href = htmlspecialchars($href);

	// unset these so they don't show up as attributes
	unset($attr['link']);

	// Build the image tag
	if (!empty($aid) && is_numeric($aid))
	{
		if (empty($attr['alt']))
		{
			$attr['alt'] = $alt;
		}
		if (empty($attr['title']))
		{
			$attr['title'] = $alt;
		}

		unset($attr['height']);
		unset($attr['width']);
		
		$pars        = $attr;
		$pars['abs'] = $abs_uri;
		
		$img = any_attach_img_tag($aid, $pars, $width, $height);
	}
	else
	{
		if (!array_key_exists('alt', $attr))
		{
			$attr['alt'] = basename($src);
		}
		
		// A normal src, build the tag ourselves
		$attr_s = '';
		foreach ($attr as $key => $val) 
		{
			$attr_s .= " $key=\"$val\"";
		}

		$img = "<img src=\"$src\"$attr_s />";
	}

	if (!empty($href)) 
	{
		$html = "$cap1<a href=\"$href\">$img</a>$cap2";
	}
	else
	{
		$html = "$cap1$img$cap2";
	}
	return $html;
}


// Calculate a size, using a base size and a percentage
//
function _wiki_runtime_img_size ( $req, $base )
{
	if (substr($req, -2) == 'px')
	{
		// Absolute size
		
		$ret = substr($req, 0, -2);
	}
	else if (is_string($req) && is_numeric($base))
	{
		// Assume percentage of base size
		
		if (substr($req, -1) == '%')
		{
			$req = substr($req, 0, -1);
		}
		$ret = ceil(floatval($req) * floatval($base) / 100.0);
	}
	else
	{
		// No size
		
		$ret = null;
	}
	return $ret;
}


?>