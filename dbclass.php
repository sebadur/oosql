<?php # Urheber dieser Datei ist Sebastian Badur. Die beiliegende Lizenz muss gewahrt bleiben.

/**
 * Instanzen, welche von dieser Klasse erben können über die oosql-Erweiterung direkt in die Datenbank gespeichert und von dort auch wieder
 * geladen werden, ohne sie manuell konvertieren zu müssen.
 * Muss das Attribut `index` als Primärschlüssel in der DB verwenden.
 */
class dbclass {
	use dbtrait;

	/**
	 * Konstruiert ein neues Datenbankobjekt.
	 * @param oosql $oosql Der Datenbankadapter.
	 * @param string $klasse Der Klassenname dieses neuen Objektes.
	 * Und weitere interne Parameter, die nicht übergeben werden sollten.
	 */
	public function __construct(oosql $oosql, $klasse, $att = NULL, $indikator = array()) {
		$this->construct($oosql, $klasse, $indikator);

		if ($att !== NULL) {
			$this->att = $att;
		} else {
			$bezeichner = array_keys($this->att);
			$namen = ''; $werte = '';
			foreach ($bezeichner as $name) {
				$namen .= '`,`'.addslashes($name);
				$werte .= '\',\''.$this->nachSQL($this->att[$name]);
			}
			$this->oosql->query('INSERT INTO `'.addslashes($this->klasse).'` ('.substr($namen, 2).') VALUES ('.substr($werte, 2).')');
		}
	}

	public function __set($name, $wert) {
		$ist_dbklasse = $this->ist_dbklasse($wert);
		if ($this->oosql->sync) {
			$this->oosql->query('UPDATE `'.addslashes($this->klasse).'` SET `'.addslashes($name).'`=\''.$this->nachSQL($wert).'\''.(
					($this->indikator[$name]?TRUE:FALSE) ^ $ist_dbklasse ? ', `ref`=\''.$this->ref($this->indikator).'\'' : ''
				).' WHERE `index`='.intval($this->index));
		}
		
		$this->indikator[$name] = $ist_dbklasse ? 2 : FALSE;
		$this->att[$name] = $wert;
	}

	public function __get($name) {
		$this->evalObj($this->oosql, $this->att[$name], $this->indikator[$name]);
		return $this->att[$name];
	}

	public function __isset($name) {
		return isset($this->att[$name]);
	}

	public function __unset($name) {
		unset($this->att[$name]);
		$this->oosql->query('UPDATE `'.addslashes($this->klasse).'` SET '.addslashes($name).'=DEFAULT WHERE `index`='.intval($this->index));
	}
}