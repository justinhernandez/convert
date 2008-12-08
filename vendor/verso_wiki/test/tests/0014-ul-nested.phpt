--TEST--
0014 Nested ul, wiki to html to wiki translation
--FILE--
<?php

// $Id: 0014-ul-nested.phpt 21726 2006-06-27 09:56:41Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '
* a0
* b0
* c0
 * d1
 * e1
  * f2
 * g1
 * h1
* i0
 * j1
';

$h1 = any_wiki_tohtml($s);
$w2 = any_wiki_towiki($h1);

echo $h1;
echo "\n\n---------\n\n";
echo $w2;

?>
--EXPECT--

<ul>
<li>a0</li>
<li>b0</li>
<li>c0<ul>
<li>d1</li>
<li>e1<ul>
<li>f2</li>
</ul>

</li>
<li>g1</li>
<li>h1</li>
</ul>

</li>
<li>i0<ul>
<li>j1</li>
</ul>

</li>
</ul>

---------

* a0
* b0
* c0
 * d1
 * e1
  * f2
 * g1
 * h1
* i0
 * j1
 
