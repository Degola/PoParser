# PoParser
Gettext *.po files parser for PHP.

This package is compliant with [PSR-0](http://www.php-fig.org/psr/0/), [PSR-1](http://www.php-fig.org/psr/1/), and [PSR-2](http://www.php-fig.org/psr/2/).
If you notice compliance oversights, please send a patch via pull request.

## Usage
### Read file content
```php
$parser = new PoParser\Parser();
$parser->read('my-pofile.po');
$entries = $parser->getEntriesAsArrays();
// Now $entries contains every string information in your pofile

echo '<ul>';
foreach ($entries as $entry) {
   echo '<li>'.
   '<b>msgid:</b> '.$entry['msgid'].'<br>'.         // Message ID
   '<b>msgstr:</b> '.$entry['msgstr'].'<br>'.       // Translation
   '<b>reference:</b> '.$entry['reference'].'<br>'. // Reference
   '<b>msgctxt:</b> ' . $entry['msgctxt'].'<br>'.   // Message Context
   '<b>tcomment:</b> ' . $entry['tcomment'].'<br>'. // Translator comment
   '<b>ccomment:</b> ' . $entry['ccomment'].'<br>'. // Code Comment
   '<b>obsolete?:</b> '.(string)$entry['obsolete'].'<br>'. // Is obsolete?
	'<b>fuzzy?:</b> ' .(string)$entry['fuzzy'].     // Is fuzzy?
	'</li>';
}
echo '</ul>';
```

### Modify content
```php
$parser = new PoParser\Parser();
$parser->read('my-pofile.po');
// Entries are stored in array, so you can modify them.

// Use updateEntry method to change messages you want.
$parser->updateEntry('Write your email', 'Escribe tu email');
$parser->write('my-pofile.po');
```

### Convert po file to jsgettext content

For usage with the jsgettext library from http://jsgettext.berlios.de/.

#### Shell script converting

po2json.php like the perl script of the jsgettext library but without further dependency.

```bash
bin$ po2json.php messages.po messages >messages.json
```


#### Online converting with caching

It's also possible to use the library for live conversion as soon as the po file changes on the server. Gives you way
more flexibility while developing. To avoid the overhead of compiling the po files again and again there is also an
simple caching functionality included which uses just last-modified headers and filemtime checks.

```php
<?php

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
```

## Todo
* Improve interface to edit entries.
* Discover what's the meaning of "#@ " line.

## License
This library is released under [MIT](http://www.tldrlegal.com/license/mit-license) license.