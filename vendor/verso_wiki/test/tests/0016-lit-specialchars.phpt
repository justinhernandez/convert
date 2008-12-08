--TEST--
0016 Literal, special chars in literal text
--FILE--
<?php

// $Id: 0016-lit-specialchars.phpt 22556 2006-08-09 11:39:13Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '
``||< left||= center||> right ||``
``|| The quick brown|| fox jumps over|| the lazy dog. ||``

';

$h1 = any_wiki_tohtml($s);
$w2 = any_wiki_towiki($h1);

echo $h1;
echo "\n\n---------\n\n";
echo $w2;

?>
--EXPECT--
<p><!--[lit]-->||&lt; left||= center||&gt; right ||<!--[/lit]--><br/>
<!--[lit]-->|| The quick brown|| fox jumps over|| the lazy dog. ||<!--[/lit]--></p>

---------

``||< left||= center||> right ||``
``|| The quick brown|| fox jumps over|| the lazy dog. ||``

