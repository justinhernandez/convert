--TEST--
0006 Absolute url encoding, back and forth
--FILE--
<?php

// $Id: 0006-url-absolute.phpt 20375 2006-04-06 21:09:11Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '
A url to some page

In a paragraph we have [http://www.example.com/somepage.html some descriptive sentence] linking to a page somewhere.

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
<p>A url to some page</p>

<p>In a paragraph we have <a href="http://www.example.com/somepage.html">some descriptive sentence</a> linking to a page somewhere.</p>

---------

A url to some page

In a paragraph we have [http://www.example.com/somepage.html some descriptive sentence] linking to a page somewhere.


---------

<p>A url to some page</p>

<p>In a paragraph we have <a href="http://www.example.com/somepage.html">some descriptive sentence</a> linking to a page somewhere.</p>

