<html>

<!-- $Id: demo.php 19427 2006-02-26 13:25:39Z marc $ -->

<head>
	<title>Demo of the Mediamatic anyMeta Wiki</title>
</head>

<body>

<h1>Demo of the Mediamatic anyMeta Wiki</h1>

<?

// A little demo of the wiki translations
// Call this from your browser when you want to see the html :-)

require_once 'any_wiki_glue.php';
require_once 'any_wiki_tohtml.php';
require_once 'any_wiki_towiki.php';
require_once 'any_wiki_runtime.php';

$wiki = '
This is a text with some **strong** and //emphasized// texts.

There is also
* a 
* bullet
* list

And some
> quotes about something else

+++ And a h3 header before the table

||~ header||~ text ||
|| yes || positive exclamation||
|| no || in general not so positive||

And a reference to a website http://www.anymeta.net/

Of course we also have email addresses mailto:nobody@mediamatic.nl that are encoded as javascript so that bots can\'t read them.

There is an extra in this wiki, so that newlines
in texts translate to
extra <br/> tags in the paragraphs, this is a bit more what //normal// (ie. non technical) users expect.

';

echo '<h2>The Wiki source text</h2>';
echo '<hr/>';

echo '<pre>';
echo htmlspecialchars($wiki);
echo '</pre>';

$html = any_wiki_tohtml($wiki);

echo '<hr/>';
echo '<h2>HTML produced by any_wiki_tohtml()</h2>';
echo '<hr/>';

echo '<pre>';
echo htmlspecialchars($html);
echo '</pre>';

echo '<hr/>';
echo '<h2>Back to Wiki by using any_wiki_towiki() on the html above</h2>';
echo '<hr/>';

echo '<pre>';
echo htmlspecialchars(any_wiki_towiki($html));
echo '</pre>';

echo '<hr/>';
echo '<h2>And after any_wiki_runtime() -- check the mailto address</h2>';
echo '<hr/>';

echo '<pre>';
echo htmlspecialchars(any_wiki_runtime($html));
echo '</pre>';

echo '<hr/>';
echo '<h2>This is the runtime text when we echo it inline</h2>';
echo '<hr/>';

echo any_wiki_runtime($html);

?>
</body>
</html>
