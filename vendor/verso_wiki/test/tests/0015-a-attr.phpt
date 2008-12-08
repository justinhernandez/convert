--TEST--
0015 Links with extra attributes
--FILE--
<?php

// $Id: 0015-a-attr.phpt 22148 2006-07-13 10:26:07Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '[http://www.annefrank.org/ Anne Frankhuis class="c_link" target="new"]
[http://www.westerbork.nl/ Herinneringscentrum Kamp Westerbork class="c_link" target="new"]';

$h1 = any_wiki_tohtml($s);
$w2 = any_wiki_towiki($h1);

echo $h1;
echo "\n\n---------\n\n";
echo $w2;

?>
--EXPECT--
<p><a href="http://www.annefrank.org/" class="c_link" target="new">Anne Frankhuis</a><br/>
<a href="http://www.westerbork.nl/" class="c_link" target="new">Herinneringscentrum Kamp Westerbork</a></p>

---------

[http://www.annefrank.org/ Anne Frankhuis class="c_link" target="new"]
[http://www.westerbork.nl/ Herinneringscentrum Kamp Westerbork class="c_link" target="new"]

