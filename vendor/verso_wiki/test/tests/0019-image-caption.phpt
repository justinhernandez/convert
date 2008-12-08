--TEST--
0019 image with a caption attribute
--FILE--
<?php

// $Id: 0019-image-caption.phpt 25304 2006-12-20 09:15:05Z marc $

error_reporting(E_ALL);

require_once(getcwd() . '/../any_wiki_glue.php');
require_once(getcwd() . '/../any_wiki_tohtml.php');
require_once(getcwd() . '/../any_wiki_towiki.php');
require_once(getcwd() . '/../any_wiki_runtime.php');

$s = '

a normal image [[image fig01]] inline

some text [[image fig02 caption="after"]] more text

[[image fig03 caption="before"]]

last paragraph.
';

$h1 = any_wiki_tohtml($s);
$w2 = any_wiki_towiki($h1);

echo $h1;
echo "\n\n---------\n\n";
echo $w2;

?>
--EXPECT--
<p>a normal image <!--[image fig01]--> inline</p>

<p>some text <!--[image fig02 caption="after"]--> more text</p>

<p><!--[image fig03 caption="before"]--></p>

<p>last paragraph.</p>

---------

a normal image [[image fig01]] inline

some text [[image fig02 caption="after"]] more text

[[image fig03 caption="before"]]

last paragraph.

