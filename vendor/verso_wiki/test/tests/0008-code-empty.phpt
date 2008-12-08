--TEST--
0008 Code, empty code block
--FILE--
<?php

// $Id: 0008-code-empty.phpt 20376 2006-04-06 21:47:20Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '

<code>
</code>
X

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

?>
--EXPECT--
<pre>
</pre>
<p>X</p>

---------

<code>
</code>

X


---------

<pre>
</pre>
<p>X</p>

---------

<code>
</code>

X


---------

<pre>
</pre>
<p>X</p>
