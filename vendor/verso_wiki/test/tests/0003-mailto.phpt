--TEST--
0003 Mailto translation, back and forth
--FILE--
<?php

// $Id: 0003-mailto.phpt 20334 2006-04-05 11:37:12Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');

$s = '
A mailto link:

mailto:test@example.com

';

$wiki = any_wiki_tohtml($s);

echo $wiki;
echo "\n\n---------\n\n";
echo any_wiki_towiki($wiki);
echo "\n";

?>
--EXPECT--
<p>A mailto link:</p>

<p><!--[mailto]-->test@example.com<!--[/mailto]--></p>

---------

A mailto link:

mailto:test@example.com
