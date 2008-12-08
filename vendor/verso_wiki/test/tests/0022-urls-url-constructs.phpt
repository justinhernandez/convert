--TEST--
0022 Urls in [url descr] constructs
--FILE--
<?php

// $Id: 0006-url-absolute.phpt 20375 2006-04-06 21:09:11Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '
a word [this is not a link] and [http://www.example.com example.com] was a link.
and an empty [] link and a link to a page [page.html] or page with descr [page.html some page].
and not a dot at the end of name [yes. that is not a link].
but a slash is a link [/some/dir/]

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
<p>a word [this is not a link] and <a href="http://www.example.com">example.com</a> was a link.<br/>
and an empty [] link and a link to a page <sup><a href="page.html">*</a></sup> or page with descr <a href="page.html">some page</a>.<br/>
and not a dot at the end of name [yes. that is not a link].<br/>
but a slash is a link <sup><a href="/some/dir/">*</a></sup></p>

---------

a word [this is not a link] and [http://www.example.com example.com] was a link.
and an empty [] link and a link to a page ((page.html|*)) or page with descr ((page.html|some page)).
and not a dot at the end of name [yes. that is not a link].
but a slash is a link ((/some/dir/|*))


---------

<p>a word [this is not a link] and <a href="http://www.example.com">example.com</a> was a link.<br/>
and an empty [] link and a link to a page <sup><a href="page.html">*</a></sup> or page with descr <a href="page.html">some page</a>.<br/>
and not a dot at the end of name [yes. that is not a link].<br/>
but a slash is a link <sup><a href="/some/dir/">*</a></sup></p>

