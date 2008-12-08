--TEST--
0023 Comment within a text block
--FILE--
<?php

// $Id: $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '
A block of text with an embedded<!-- comment in the line -->comment before here.

<!-- Comment at the start of a block -->And a new paragraph

<!-- A comment on its own -->

<!-- A comment with some < > characters -->

And the last line.
';

$wiki = any_wiki_tohtml($s);

echo $wiki;
echo "\n\n---------\n\n";
echo any_wiki_towiki($wiki);
echo "\n\n---------\n\n";
echo any_wiki_runtime($wiki);
echo "\n";

?>
--EXPECT--
<p>A block of text with an embedded<!-- comment in the line -->comment before here.</p>

<p><!-- Comment at the start of a block -->And a new paragraph</p>

<p><!-- A comment on its own --></p>

<p><!-- A comment with some &lt; &gt; characters --></p>

<p>And the last line.</p>

---------

A block of text with an embedded<!-- comment in the line -->comment before here.

<!-- Comment at the start of a block -->And a new paragraph

<!-- A comment on its own -->

<!-- A comment with some < > characters -->

And the last line.


---------

<p>A block of text with an embedded<!-- comment in the line -->comment before here.</p>

<p><!-- Comment at the start of a block -->And a new paragraph</p>

<p><!-- A comment on its own --></p>

<p><!-- A comment with some &lt; &gt; characters --></p>

<p>And the last line.</p>
