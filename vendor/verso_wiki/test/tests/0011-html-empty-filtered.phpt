--TEST--
0011 Html, empty block, html filtered
--FILE--
<?php

// $Id: 0011-html-empty-filtered.phpt 20376 2006-04-06 21:47:20Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '

A
<html>
</html>
B

';

$h1 = any_wiki_tohtml($s);
$w2 = any_wiki_towiki($h1);

echo $h1;
echo "\n\n---------\n\n";
echo $w2;

?>
--EXPECT--
<p>A<br/>
</p><p>B</p>

---------

A

B
