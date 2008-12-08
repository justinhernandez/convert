--TEST--
0017 Table, alignment of text in table cells
--FILE--
<?php

// $Id: 0017-table-aligment.phpt 22556 2006-08-09 11:39:13Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '
||< left||= center||> right ||
|| The quick brown|| fox jumps over|| the lazy dog. ||
';

$h1 = any_wiki_tohtml($s);
$w2 = any_wiki_towiki($h1);

echo $h1;
echo "\n\n---------\n\n";
echo $w2;

?>
--EXPECT--
<table>
<tr>
  <td style="text-align: left;">left</td>
  <td style="text-align: center;">center</td>
  <td style="text-align: right;">right</td>
</tr>
<tr>
  <td>The quick brown</td>
  <td>fox jumps over</td>
  <td>the lazy dog.</td>
</tr>
</table>

---------

||< left||= center||> right ||
|| The quick brown|| fox jumps over|| the lazy dog. ||
