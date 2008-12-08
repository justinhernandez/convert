--TEST--
0018 Table of contents [[toc]]
--FILE--
<?php

// $Id: 0018-toc.phpt 25233 2006-12-14 15:18:52Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '

[[toc]]
+ header 1-1

some text 1-1

++ header 2-1

some text 2-1

++ header 2-2

some text 2-2

+ header 1-2

some last 1-2
';

$h1 = any_wiki_tohtml($s);
$w2 = any_wiki_towiki($h1);

echo preg_replace('/[0-9]{4,12}/', 'anchorid', $h1);
echo "\n\n---------\n\n";
echo $w2;

?>
--EXPECT--
<!--[toc]-->
<ul class='wikitoc'>
<li class='wikitoc2'><a href='#anchorid-1'>header 2-1</a></li>
<li class='wikitoc2'><a href='#anchorid-2'>header 2-2</a></li>
</ul>
<!--[/toc]-->

<h1>header 1-1</h1>

<p>some text 1-1</p>


<h2><a name='anchorid-1'></a>header 2-1</h2>

<p>some text 2-1</p>


<h2><a name='anchorid-2'></a>header 2-2</h2>

<p>some text 2-2</p>


<h1>header 1-2</h1>

<p>some last 1-2</p>

---------

[[toc]]
+ header 1-1

some text 1-1

++ header 2-1

some text 2-1

++ header 2-2

some text 2-2

+ header 1-2

some last 1-2
