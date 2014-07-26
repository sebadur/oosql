<?php # Urheber dieser Datei ist Sebastian Badur. Die beiliegende Lizenz muss gewahrt bleiben.

/**
 * Instanzen, welche von dieser Klasse erben können über die oosql-Erweiterung direkt in die Datenbank gespeichert und von dort auch wieder
 * geladen werden, ohne sie manuell konvertieren zu müssen.
 * Muss das Attribut `index` als Primärschlüssel in der DB verwenden.
 */
class dbclass {
	use dbtrait;

	public function __set($name, $wert) {
		$neuerTyp = FALSE;
		if (is_subclass_of($wert, 'dbclass', FALSE)) {
			if (!$this->indikator) {
				$neuerTyp = TRUE;
			}
			$this->indikator[$name] = 2;
		} else if ($this->indikator) {
			$neuerTyp = TRUE;
			$this->indikator[$name] = FALSE;
		}

		if ($this->oosql->sync) {
			$this->oosql->query('UPDATE `'.addslashes($this->klasse).'` SET `'.addslashes($name).'`=\''.$this->nachSQL($wert).'\''.(
					$neuerTyp ? ', `ref`=\''.$this->ref($this->indikator).'\'' : ''
				).' WHERE `index`='.intval($this->index));
		}
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

trait dbtrait {
	public $klasse;
	private $oosql, $att = array(), $indikator = NULL;

	/**
	 * Konstruiert ein neues Datenbankobjekt.
	 * @param oosql $oosql Der Datenbankadapter.
	 * @param string $klasse Der Klassenname dieses neuen Objektes.
	 * Und weitere interne Parameter, die nicht übergeben werden sollten.
	 */
	public function __construct($oosql, $klasse, $att = NULL, $indikator = array()) {
		$this->oosql = $oosql;
		$this->klasse = $klasse;
		if ($att !== NULL) {
			$this->att = $att;
			$this->indikator = $indikator;
		} else {
			$bezeichner = array_keys($this->att);
			foreach ($bezeichner as $name) {
				$namen .= '`,`'.addslashes($name);
				$werte .= '\',\''.$this->nachSQL($this->att[$name]);
			}
			$this->oosql->query('INSERT INTO `'.addslashes($this->klasse).'` ('.substr($namen, 2).') VALUES ('.substr($werte, 2).')');
		}
	}


	/**
	 * Löscht dieses spezielle Objekt aus der Datenbank.
	 * Referenzierte Objekte bleiben von dieser Funktion unangetastet, werden also nicht implizit gelöscht.
	 * @return boolean Wahr genau dann, wenn der Vorgang erfolgreich verlaufen ist.
	 */
	public final function remove() {
		return $this->oosql->query('REMOVE FROM `'.addslashes($this->klasse).'` WHERE `index`='.intval($this->index));
	}

	/**
	 * Speichert dieses Objekt in die Datenbank.
	 * Die in der Datenbank vorhandene Kopie wird dabei verworfen. Funktioniert auch, wenn $sync wahr ist, obwohl redundant.
	 * @return boolean Wahr genau dann, wenn der Vorgang erfolgreich verlaufen ist.
	 */
	public final function save() {
		$bezeichner = array_keys($this->att);
		foreach ($bezeichner as $name) {
			$werte .= addslashes($name).'`=\''.addslashes($this->att[$name]).'\',';
		}
		# Hier ist nicht mehr bekannt, welche Attribute verändert wurden, deshalb wird der Indikator einfach neu bestimmt und überschrieben
		$werte .= '`ref`='.$this->ref($this->indikator);
		$this->oosql->query('UPDATE `'.addslashes($this->klasse)."` SET $werte WHERE `index`=".intval($this->index));
	}

	private final function nachSQL($wert) { # Zerlegt ein Objekt in seine Referenz
		if (is_subclass_of($wert, 'dbclass', FALSE)) {
			return addslashes($wert->klasse).' '.intval($wert->index);
		} else {
			return addslashes((string) $wert);
		}
	}

	private final function evalObj($oosql, &$wert, $indikator) { # Wenn der Wert eine Referenz ist, dann wird diese aufgelöst
		if ($indikator === TRUE) {
			$id = explode(' ', $wert);
			$wert = $oosql->select(addslashes($id[0]), 'WHERE `index`='.intval($id[1]))[0];
		}
	}

	private final function ref($indikator) {
		$ref = 0;
		foreach ($indikator as $bool) {
			if ($bool) {
				$ref++;
			}
			$ref <<= 1;
		}
		return $ref;
	}
}