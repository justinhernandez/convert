--TEST--
0010 Html, allowed
--FILE--
<?php

// $Id: 0010-html-allowed.phpt 20376 2006-04-06 21:47:20Z marc $

define('ALLOWHTML', true);

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
$h2 = any_wiki_tohtml($w2);
$w3 = any_wiki_towiki($h2);
$h3 = any_wiki_tohtml($w3);

echo $h1;
echo "\n\n---------\n\n";
echo $w2;
echo "\n\n---------\n\n";
echo $h2;
echo "\n\n---------\n\n";
echo $w3;
echo "\n\n---------\n\n";
echo $h3;
echo "\n\n---------\n\n";
echo any_wiki_runtime($h3);

?>
--EXPECT--
<p>A</p>

<!--[html]--><h1>some html</h1>
<!--[/html]-->
<p>B</p>

---------

A

<html>
<h1>some html</h1>

</html>

B


---------

<p>A</p>


<!--[html]--><h1>some html</h1>

<!--[/html]-->
<p>B</p>

---------

A

<html>
<h1>some html</h1>

</html>

B


---------

<p>A</p>


<!--[html]--><h1>some html</h1>

<!--[/html]-->
<p>B</p>

---------

<p>A</p>


<h1>some html</h1>


<p>B</p>
