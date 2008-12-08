--TEST--
0020 define an anchor
--FILE--
<?php

// $Id: 0020-anchor-definition.phpt 25304 2006-12-20 09:15:05Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '
Some text with an [[# anchor]] anchore in it.
';

$h1 = any_wiki_tohtml($s);
$w2 = any_wiki_towiki($h1);

echo $h1;
echo "\n\n---------\n\n";
echo $w2;

?>
--EXPECT--
<p>Some text with an <a name="anchor"></a> anchore in it.</p>

---------

Some text with an [[# anchor]] anchore in it.
