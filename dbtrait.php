<?php # Urheber dieser Datei ist Sebastian Badur. Die beiliegende Lizenz muss gewahrt bleiben.

/**
 * Interner Trait für alle DB-Klassen.
 */
trait DBTrait {
	public $klasse;
	private $oosql, $att = array(), $indikator;

	private function construct($oosql, $klasse, $indikator = array()) {
		$this->oosql = $oosql;
		$this->klasse = $klasse;
		$this->indikator = $indikator;
	}


	/**
	 * Löscht dieses spezielle Objekt aus der Datenbank.
	 * Referenzierte Objekte bleiben von dieser Funktion unangetastet, werden also nicht implizit gelöscht.
	 * @return boolean Wahr genau dann, wenn der Vorgang erfolgreich verlaufen ist.
	 */
	public final function remove() {
		return $this->oosql->query('REMOVE FROM '.addslashes($this->klasse).' WHERE id='.intval($this->id));
	}

	/**
	 * Speichert dieses Objekt in die Datenbank.
	 * Die in der Datenbank vorhandene Kopie wird dabei verworfen. Funktioniert auch, wenn $sync wahr ist, obwohl redundant.
	 * @return boolean Wahr genau dann, wenn der Vorgang erfolgreich verlaufen ist.
	 */
	public final function save() {
		$bezeichner = array_keys($this->att);
		foreach ($bezeichner as $name) {
			$werte .= addslashes($name).'=\''.addslashes($this->att[$name]).'\',';
		}
		# Hier ist nicht mehr bekannt, welche Attribute verändert wurden, deshalb wird der Indikator einfach neu bestimmt und überschrieben
		$werte .= 'ref='.$this->ref($this->indikator);
		$this->oosql->query('UPDATE '.addslashes($this->klasse)." SET $werte WHERE id=".intval($this->id));
	}


	private final function ist_dbklasse($objekt, $erlaube_string = FALSE) {
		return is_a($objekt, 'DBClass', $erlaube_string) || is_a($objekt, 'DBArray', $erlaube_string);
	}

	private final function nachSQL($wert, $ist_dbklasse = NULL) { # Zerlegt ein Objekt (kein [array]) in seine Referenz
		$ist_dbklasse = $ist_dbklasse ?: $this->ist_dbklasse($wert);
		if ($ist_dbklasse) {
			return addslashes($wert->klasse).' '.intval($wert->id);
		} else if (is_scalar($wert)) {
			return addslashes((string) $wert);
		} else {
			return 'DEFAULT';
		}
	}

	private final function evalObj($oosql, &$wert, &$indikator) { # Wenn der Wert eine Referenz ist, dann wird diese aufgelöst
		if ($indikator === TRUE) {
			$id = explode(' ', $wert);
			$wert = array_shift($oosql->select(addslashes($id[0]), 'WHERE id='.intval($id[1])));
			$indikator = 2;
		}
	}

	private final function evalFeld(&$wert) { # Konvertiert bei Bedarf das [array] in ein [DBArray]
		if (is_array($wert)) {
			$wert = new DBArray($this->oosql, $wert);
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