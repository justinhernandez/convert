<?

// File:		$Id: any_wiki_glue.php 29975 2007-08-10 11:41:52Z marc $
// Date:		2005/02/25
// Author:		Marc Worrell
// Copyright:	(c) 2006-2007  Marc Worrell
// Description:	Glueing code for anyMeta specific callbacks
//
// This file give you the prototypes and some implementations for functions
// used by the Wiki code.  These functions normally plug into the anyMeta core,
// and you can use them to link it to your own database.
//
// In anyMeta the 'thing' is the basic object.  This could be a person, an article, an image
// or anything else that is supported.  In a Wiki system it could be comparable with a page.
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


// Function:	any_thing_uri_abs
// Access:		EXTERNAL
// Parameters:	$thg_id		Id of thing (may be an array)
//				$idn_id		(optional) Identity reading the uris
//				$lang		(optional) Preferred language array
//				$commit		(optional) Commit or checkout version
// Returns:		array(id => uri)	when input is an array
//				uri					when input is a single id
// Description:	Translates a thing id to an absolute uri.
//				Depending on the sharing status and the refering identity
//				the uri will direct to either the own or the refered
//				identity.
//
function any_thing_uri_abs ( $thg_id, $idn_id = null, $lang = null, $commit = null )
{
	return any_thing_uri($thg_id, $idn_id, $lang, $commit, true);
}

	
// Function:	any_thing_uri
// Access:		EXTERNAL
// Parameters:	$thg_id		Id of thing (may be an array)
//				$idn_id		(optional) Identity reading the uris
//				$lang		(optional) The target _must_ have this language
//				$commit		(optional) not used (for now)
//				$abs		(optional,internal) Force absolute uri
// Returns:		array(id => uri)	when input is an array
//				uri					when input is a single id
// Description:	Translates a thing id to an uri.
//				Depending on the sharing status and the refering identity
//				the uri will direct to either the own or the refered
//				identity.
//
function any_thing_uri ( $thg_id, $idn_id = null, $lang = null, $commit = null, $abs = false )
{
	if ($abs)
	{
		return 'http://' . $_SERVER['HTTP_HOST'] . "/id/" . urlencode($thg_id);
	}
	else
	{
		return 'id/' . urlencode($thg_id);
	}
}


// Function:	any_uri_abs
// Access:		EXTERNAL
// Parameters:	$uri
// Returns:		uri with hostname etc.
// Description:	Prepends (when needed) the given uri with a full domain name.
//
function any_uri_abs ( $uri )
{
	if (strpos($uri, '://') === false)
	{
		$uri = 'http://' . $_SERVER['HTTP_HOST'] . '/' . ltrim($uri, '/');
	}
	return $uri;
}

// Function:	any_symbolic2id
// Access:		EXTERNAL
// Parameters:	$symbolic	Symbolic name to look for
//				$options	Options for search
// Returns:		id 		of thing with given symbolic id
//				false 	when no id found
// Description:	return id of thing with the given symbolic name
//				a thing of the internal source prevails above things
//				of external sources.
//				Optionally the selection is reduced to only the
//				own things and/or things of a certain kind.
//				When the symbolic id matches the pattern [0-9]+ then
//				the symbolic id is assumed to be a real id and the id
//				is returned as is.
//
function any_symbolic2id ( $symbolic, $options = array() )
{
	return false;
}



// Function:	any_attach_label2pred
// Access:		EXTERNAL
// Parameters:	label	label or nr of attachment		
// Returns:		list(predicate, ord_nr)
// Description:	translates a label or nr to the correct predicate/ order nr.
//
//				A label can have the format of FIG01, FIG02, ICON or DOC01
//				Or just be a number, in that case we assume that the user
//				is refering to a figure.
//				The first figure is nr 1.  Internally we use nr 0 for the 
//				first figure.
//
function any_attach_label2pred ( $label )
{
	if (strcasecmp($label, 'ICON') == 0)
	{
		$pred	= 'ICON';
		$nr		= 1;
	}
	else if (strncasecmp($label, 'FIG', 3) == 0)
	{
		$pred	= 'FIGURE';
		$nr		= @intval(substr($label, 3));
	}
	else if (strncasecmp($label, 'DOC', 3) == 0)
	{
		$pred	= 'DOCUMENT';
		$nr		= @intval(substr($label, 3));
	}
	else
	{
		// Assume numeric
		$pred	= 'FIGURE';
		$nr		= @intval($label);
	}

	// We need an offset from 0
	if ($nr > 0)
	{
		$nr--;
	}
	else
	{
		$nr = 0;
	}

	return array($pred, $nr);
}


// Function:	any_attach_caption_label
// Access:		EXTERNAL
// Parameters:	thg_id		thing id
//				pred		predicate (figure, icon, document)
//				nr			order nr of figure etc. (0..n)
// Returns:		list(att_id, alt, caption)
//				false when not found
// Description:	tryes to find the named attachment.
//				returns the found attachment id, the alt and the caption text
//				does not consider _any_ access rights!
//
//				In anyMeta a 'thing' can have many images attached to it.
//				This images are 'things' of the kind 'attachment'.
//				We identify a particular image by a predicate on the edge
//				to the image and by the order nr.
//
//				The alt text is typically a short title describing the image.
//				The caption text could be longer and is typically shown underneath
//				the image.
//
function any_attach_caption_pred ( $thg_id, $label )
{
	return false;
}


// Function:	any_attach_caption
// Access:		EXTERNAL
// Parameters:	thg_id		thing id
// Returns:		list(alt, caption)
//				false when not found
// Description:	returns the alt and the caption text of the attachment
//				does not consider _any_ access rights!
//
//				an attachment is an image, mainly used in the context of 
//				articles etc.  see the function any_attach_caption_label()
//				above for a short discussion about attachments.
//
function any_attach_caption ( $thg_id )
{
	return false;
}


// Function:	any_thing_title_short
// Access:		EXTERNAL
// Parameters:	$thg_id		Id of the thing (may be an array, must be real id)
//				$usr_id		(optional) User reading the titles
//				$lang		(optional) Preferred language array
// Returns:		array(id=>title)
//				error		string with error message
// Description:	Reads the short titles of the given thing(s).
//
//				in anyMeta every thing must have a title.  it might also have a
//				short title.  this function returns the short title, and when
//				missing it returns the (long) title.
//
function any_thing_title_short ( $thg_id, $usr_id = null, $lang = null )
{
	$ts = array();
	if (!is_array($thg_id))
	{
		foreach ($thg_id as $id)
		{
			$ts[$id] = 'title of ' . htmlspecialchars($id);
		}
	}
	else
	{
		$ts[$thg_id] = 'title of ' . htmlspecialchars($thg_id);
	}
	return $ts;
}



// Function:	any_text_utf8
// Access:		EXTERNAL
// Parameters:	$html	text to check
// Returns:		utf8 version of text
// Description:	This checks the input string to be really utf8, replaces non utf8 characters
//				with a question mark.  This validity check is needed before you want to parse
//				the string with any XML parser.
//
function any_text_utf8 ( $html )
{
	if (function_exists('iconv'))
	{
		do
		{
			$ok   = true;
			$text = @iconv('UTF-8', 'UTF-8//TRANSLIT', $html);
			if (strlen($text) != strlen($html))
			{
				// Remove the offending character...
				$html = $text . '?' . substr($html, strlen($text) + 1);
				$ok   = false;
			}
		}
		while (!$ok);
	}
	return $html;
}




// Function:	any_encode_mailto
// Access:		EXTERNAL
// Parameters:	$href 	mailto link
//				$text	(optional) description for link
// Returns:		<a /> for mailto
// Description:	This generates the anchor-tag for an mailto url.
//				The email address is encoded so that most web-bots won't recognise
//				it as an email address.
//
function any_encode_mailto ( $href, $text = '', $encode = 'javascript' )
{
	if (substr($href, 0, 7) == 'mailto:')
	{
		$href = substr($href, 7);
	}

	if (empty($text))
	{
		$text = $href;
	}
	
	$html	=	'<a href="mailto:'
			.	htmlspecialchars($href, ENT_QUOTES).'">'
			.	htmlspecialchars(str_replace('@',' [at] ',$text), ENT_QUOTES)
			.	'</a>';
	
    if ($encode == 'javascript' ) 
	{
		// Double encode the text using javascript
		//
		
		$js = '';
		for ($x=0; $x < strlen($html); $x++) 
		{
			if (rand(0,5) == 1)
			{
				$js .= '\'+\'';
			}
			if (strchr('><\'@', $html[$x]) !== false || rand(0,2) == 1)
			{
				$js .= '%' . bin2hex($html[$x]);
			}
			else
			{
				$js .= $html[$x];
			}
		}

		$html = '<script type="text/javascript">document.write(unescape(\''.$js.'\'))</script>';
		$js   = '';
		for ($x=0; $x < strlen($html); $x++) 
		{
			if (strchr('><\'', $html[$x]) !== false || rand(0,2) == 1)
			{
				$js .= '%' . bin2hex($html[$x]);
			}
			else
			{
				$js .= $html[$x];
			}
		}
		$html = '<script type="text/javascript">document.write(unescape(\''.$js.'\'))</script>';
	}
    else
    {
    	// Simple non-javascript version
    	//
		$text_encode = '';
		for ($x=0; $x < strlen($href); $x++) 
		{
			$text_encode .= '&#' . ord($href[$x]) . ';';
		}
		$href = $text_encode;
	
		$text_encode = '';
		$text        = str_replace('@', ' [at] ', $text);
		for ($x=0; $x < strlen($text); $x++) 
		{
			$text_encode .= '&#' . ord($text[$x]) . ';';
		}
		$text 	= $text_encode;
		$html	=	"<a href=\"mailto:$href\">$text</a>";
	}
    return $html;
}






// Class:		Anymeta_ACL
// Access:		EXTERNAL
// Provides:	Access control for the anymeta system
//
class Anymeta_ACL 
{
	function Anymeta_ACL ()
	{
	}
	
	// Function:	Anymeta_ACL::allowHtml
	// Access:		PUBLIC
	// Parameters:	-
	// Returns:		false	when user is not allowed to edit html text
	//				true	when user is allowed to edit html text
	// Description:	Checks if the current user is allowed to edit html.
	//				This is a very special right, and should be given 
	//				with caution!
	//
	// 				This should be a right of an editor, letting normal users
	// 				enter HTML really defies the idea of an wiki, and of the
	// 				security of letting people enter markup text.
	//
	function allowHtml ()
	{
		return defined('ALLOWHTML');
	}


	// Function:	Anymeta_ACL::filterUri
	// Access:		PUBLIC
	// Parameters:	uri		uri to be filtered
	// Returns:		''		when uri was not allowed
	//				uri		when uri was allowed
	// Description:	Checks the given uri with the access permissions of the user.
	//				The user needs text/html permissions for entering javascrpt uris.
	//
	function filterUri ( $uri )
	{
		$allow	= false;
		$uri	= trim($uri);
		$u		= urldecode($uri);
		
		if (	strpos($u, '&#') === false
			&&	strpos($u, '"') === false
			&&	strncasecmp($u, 'javascript:', 11) != 0)
		{
			$allow = true;
		}
		
		if (!$allow)
		{
			// user needs to have the right to edit HTML
			$allow = $this->allowHtml();
		}
		return $allow ? $uri : '';
	}

}

$any_acl = new Anymeta_ACL();



?>