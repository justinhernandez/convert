--TEST--
0005 Mailto translation, test runtime encoding
--FILE--
<?php

// $Id: 0005-mailto-encode.phpt 20335 2006-04-05 12:12:30Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '
A mailto link:

mailto:test@example.com

[mailto:test@example.com Mister Test]

';

$wiki = any_wiki_tohtml($s);

echo $wiki;
echo "\n\n---------\n\n";
echo any_wiki_runtime($wiki, array('nojavascript'=>true));
echo "\n";

?>
--EXPECT--
<p>A mailto link:</p>

<p><!--[mailto]-->test@example.com<!--[/mailto]--></p>

<p><!--[mailto]-->test@example.com Mister Test<!--[/mailto]--></p>

---------

<p>A mailto link:</p>

<p><a href="mailto:&#116;&#101;&#115;&#116;&#64;&#101;&#120;&#97;&#109;&#112;&#108;&#101;&#46;&#99;&#111;&#109;">&#116;&#101;&#115;&#116;&#32;&#91;&#97;&#116;&#93;&#32;&#101;&#120;&#97;&#109;&#112;&#108;&#101;&#46;&#99;&#111;&#109;</a></p>

<p><a href="mailto:&#116;&#101;&#115;&#116;&#64;&#101;&#120;&#97;&#109;&#112;&#108;&#101;&#46;&#99;&#111;&#109;">&#77;&#105;&#115;&#116;&#101;&#114;&#32;&#84;&#101;&#115;&#116;</a></p>

