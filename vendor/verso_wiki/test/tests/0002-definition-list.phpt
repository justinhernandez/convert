--TEST--
0002 Test the definition lists
--FILE--
<?php

// $Id: 0002-definition-list.phpt 19475 2006-02-28 18:02:13Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');

$s = '
A definition list:

: def1, yes really! : descr1 and //some// extra tekst
: def2 : descr2
: def3 : descr3
: def4 : descr4
: def5 : descr5

and done!
';

echo any_wiki_tohtml($s);

?>
--EXPECT--
<p>A definition list:</p>


<dl>
  <dt>def1, yes really!</dt>
  <dd>descr1 and <em>some</em> extra tekst</dd>
  <dt>def2</dt>
  <dd>descr2</dd>
  <dt>def3</dt>
  <dd>descr3</dd>
  <dt>def4</dt>
  <dd>descr4</dd>
  <dt>def5</dt>
  <dd>descr5</dd>
</dl>

<p>and done!</p>
