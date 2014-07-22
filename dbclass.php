<?php # Urheber dieser Datei ist Sebastian Badur. Die beiliegende Lizenz muss gewahrt bleiben.

/**
 * ...
 * Muss das Attribut `index` als Primärschlüssel in der DB verwenden.
 */
class dbclass {
	public $klasse;
	private $oosql, $att = array(), $indikator = NULL;

	public function __construct($oosql, $klasse, $att = NULL, $indikator = array()) {
		$this->oosql = $oosql;
		$this->klasse = $klasse;
		if ($att !== NULL) {
			$this->att = $att;
			$this->indikator = $indikator;
		} else {
			$bezeichner = array_keys($att);
			foreach($bezeichner as $name) {
				$namen .= '`,`'.addslashes($name);
				$werte .= '\',\''.addslashes($att[$name]);
			}
			$this->oosql->query('INSERT INTO `'.$this->klasse.'` ('.substr($namen, 2).') VALUES ('.substr($werte, 2).')');
		}
	}

	public function __set($name, $wert) {
		$this->att[$name] = $wert;
		if ($this->indikator[$name] == TRUE) {
			$this->indikator[$name] = 2;
		} else {
			assert('!is_subclass_of($wert, \'dbclass\')', "Die Datenbank lässt für $this->klasse->$name keine Referenz zu.");
		}
		$this->oosql->query('UPDATE `'.addslashes($this->klasse).'` SET `'.addslashes($name).'`=\''.(
			$this->indikator[$name] != 2
				? addslashes((string) $wert)
				: addslashes($wert->klasse).' '.intval($wert->index)
			).'\' WHERE `index`='.intval($this->index));
	}

	public function __get($name) {
		if ($this->indikator[$name] == TRUE) {
			$id = explode(' ', $this->att[$name]);
			$this->att[$name] = $this->oosql->select(addslashes($id[0]), 'WHERE `index`='.intval($id[1]))[0];
		}
		return $this->att[$name];
	}

	public function __isset($name) {
		return isset($this->att[$name]);
	}

	public function __unset($name) {
		unset($this->att[$name]);
	}


	/**
	 * Löscht dieses spezielle Objekt aus der Datenbank.
	 * Referenzierte Objekte bleiben von dieser Funktion unangetastet, werden also nicht implizit gelöscht.
	 * @return boolean Wahr genau dann, wenn der Vorgang erfolgreich verlaufen ist.
	 */
	public final function remove() {
		$this->oosql->query('REMOVE FROM `'.addslashes($this->klasse).'` WHERE `index`='.intval($this->index));
	}
}