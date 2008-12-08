--TEST--
0007 Code to wiki
--FILE--
<?php

// $Id: 0007-code-towiki.phpt 20375 2006-04-06 21:09:11Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '

<code>
text
</code>

Link http://pecl.php.net/package/filter after.

';

$wiki = any_wiki_tohtml($s);

echo $wiki;
echo "\n\n---------\n\n";
echo any_wiki_towiki($wiki);

?>
--EXPECT--
<pre>
text
</pre>
<p>Link <a href="http://pecl.php.net/package/filter">pecl.php.net/package/filter</a> after.</p>

---------

<code>
text
</code>

Link http://pecl.php.net/package/filter after.
