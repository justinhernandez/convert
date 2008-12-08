--TEST--
0004 Mailto translation, with a description
--FILE--
<?php

// $Id: 0004-mailto-bracket.phpt 20335 2006-04-05 12:12:30Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');

$s = '
A mailto link:

[mailto:test@example.com Mister Test]

';

$wiki = any_wiki_tohtml($s);

echo $wiki;
echo "\n\n---------\n\n";
echo any_wiki_towiki($wiki);
echo "\n";

?>
--EXPECT--
<p>A mailto link:</p>

<p><!--[mailto]-->test@example.com Mister Test<!--[/mailto]--></p>

---------

A mailto link:

[mailto:test@example.com Mister Test]

