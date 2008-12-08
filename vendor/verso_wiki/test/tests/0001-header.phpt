--TEST--
0001 Test the header blocks
--FILE--
<?php

// $Id: 0001-header.phpt 19475 2006-02-28 18:02:13Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');

$s = '
+ Header level 1
++ Header level 2
+++ Header level 3
++++ Header level 4
+++++ Header level 5
++++++ Header level 6

+ Back to level 1
';

echo any_wiki_tohtml($s);

?>
--EXPECT--
<h1>Header level 1</h1>

<h2>Header level 2</h2>

<h3>Header level 3</h3>

<h4>Header level 4</h4>

<h5>Header level 5</h5>

<h6>Header level 6</h6>


<h1>Back to level 1</h1>
