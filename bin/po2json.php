<?php
/**
 * shell script for creating json file from po file
 *
 * @usage php po2json.php <po file> >output.json
 *
 * User: Sebastian Lagemann <github@degola.de>
 * Date: 26.04.14
 * Time: 23:24
 */

if(!isset($argv[1]) || !file_exists($argv[1]) || !is_readable($argv[1]))
	die($argv[0]." <po file> [messages]\n");

if(isset($argv[2]))
	define('DOMAIN', $argv[2]);
else define('DOMAIN', 'messages');

require '../src/PoParser/Entry.php';
require '../src/PoParser/Parser.php';
require '../src/PoParser/Converter/Driver/jsgettext.php';

$converter = new \PoParser\Converter_Driver_jsgettext($argv[1], DOMAIN);
echo $converter->getContent();

?>