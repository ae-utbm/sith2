<?
$topdir = '../';
require_once($topdir."planet/include/atomparser.inc.php");

$flux = 'http://twitter.com/statuses/user_timeline/14591898.atom';

$parser = new AtomParser();
$parser->parse($flux);
print_r($parser->getEntry(0));

?>
