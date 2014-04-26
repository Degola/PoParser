<?php
/**
 * example script for online conversion of po files to json files with caching
 *
 *
 * User: Sebastian Lagemann <github@degola.de>
 * Date: 26.04.14
 * Time: 23:24
 */

define('DOMAIN', 'messages');
define('PO_FILE', '../app/locales/en_US.utf-8/'.DOMAIN.'.po');
define('PO_PARSER_CACHE_DIR', '/tmp/po-parser-converter-cache/');

require '../src/PoParser/Entry.php';
require '../src/PoParser/Parser.php';
require '../src/PoParser/Converter/Cache.php';
require '../src/PoParser/Converter/Driver/jsgettext.php';

header('Content-Type: application/json');

// first load caching class
$ppc = new \PoParser\Converter_Cache(
	// path to po file
	PO_FILE,
	// prefix for caching file, if you use more than one converter driver you have to differentiate here
	'jsgettext-'.DOMAIN,
	// caching path where the caching files are placed
	PO_PARSER_CACHE_DIR
);

// send http header last-modified and if client already cached the file a 304 not modified response
$ppc->setCachingHeaders();
// if client didn't cache the file
if($ppc->isRequestCached() === false) {
	// if the web server didn't cache already the converted po file
	if($ppc->isChanged()) {
		// load, convert and output json file as expected by jsgettext
		$pp = new \PoParser\Converter_Driver_jsgettext(PO_FILE, DOMAIN);
		$localisationData = $pp->getContent();
		$ppc->updateContent($localisationData);

		echo $localisationData;
		unset($localisationData);
		unset($pp);
	} else {
		echo $ppc->getContent();
	}
}
unset($ppc);


?>