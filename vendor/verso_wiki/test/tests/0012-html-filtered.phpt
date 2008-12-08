--TEST--
0012 Html, filtered
--FILE--
<?php

// $Id: 0012-html-filtered.phpt 20376 2006-04-06 21:47:20Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '

A
<html>
<h1>some html</h1>
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
some html<br />
</p><p>B</p>

---------

A
some html

B
