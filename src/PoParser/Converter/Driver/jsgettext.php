<?php
/**
 * php po parser converter class to convert po file content to jsgettext json format
 *
 * @see http://jsgettext.berlios.de/
 *
 * User: Sebastian Lagemann <github@degola.de>
 * Date: 26.04.14
 * Time: 21:46 MESZ
 */

namespace PoParser;

/**
 * Class Driver_jsgettext
 * @package PoParser_Converter
 * @uses Driver_Cache
 * @uses PoParser\Parser
 */
class Converter_Driver_jsgettext {

	/**
	 * @var $poFile path to po file
	 */
	private $poFile;

	/**
	 * @var string|null content of converted po file, just in case that getContent() will be called more than once
	 */
	private $content = null;

	/**
	 * @var string prefixed array to use it directly with jsgettext
	 */
	private $domain = null;

	public function __construct($poFile, $domain = 'messages') {
		$this->poFile = $poFile;
		$this->domain = $domain;
	}

	/**
	 * converts po file to jsgettext ready json data
	 *
	 * @throws \Exception
	 */
	public function convert() {
		$content = array();
		$parser = new Parser();
		$result = $parser->read($this->poFile);
		foreach($result AS $msgid => $msgValue) {
			if($msgid === '') {
				// po header
				$content[''] = array();
				foreach($msgValue['msgstr'] AS $entity) {
					if($entity) {
						$idpos = strpos($entity, ':');
						$content[''][substr($entity, 0, $idpos)] = trim(substr($entity, $idpos + 1));
					}
				}
			} else {
				$content[$msgid] = $msgValue['msgstr'];
				// if not a plural entry add null at the beginning of msgstr array
				if(empty($msgValue['msgid_plural']))
					array_unshift($content[$msgid], null);
				else
					array_unshift($content[$msgid], $msgValue['msgid_plural']);

			}
		}
		$this->content = json_encode(array(
			$this->domain => $content
		));
	}

	/**
	 * returns po file content, calls convert()-method the first time
	 *
	 * @return string
	 */
	public function getContent() {
		if(is_null($this->content))
			$this->convert();
		return $this->content;
	}
}

?>