--TEST--
0013 A symbolic name in a wiki link
--FILE--
<?php

// $Id: 0013-wikilink-symbolic.phpt 20589 2006-04-19 10:19:33Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '

A link ((symbolicname)) after link.

A link with text ((symbolicname|text)) after link.

';

$h1 = any_wiki_tohtml($s);
$w2 = any_wiki_towiki($h1);

echo $h1;
echo "\n\n---------\n\n";
echo $w2;

?>
--EXPECT--
<p>A link <!--[link symbolicname]--><a href="id.php/symbolicname">symbolicname</a><!--[/link]--> after link.</p>

<p>A link with text <a href="id.php/symbolicname">text</a> after link.</p>

---------

A link ((symbolicname)) after link.

A link with text ((symbolicname|text)) after link.

