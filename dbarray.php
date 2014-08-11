<?php # Urheber dieser Datei ist Sebastian Badur. Die beiliegende Lizenz muss gewahrt bleiben.

/**
 * Die Möglichkeit, Felder in die Datenbank zu speichern.
 * Bei Verwendung ist die Tabelle dbarray in der Datenbank unangetastet zu lassen!
 * Alle Felderfunktionen mit Präfix "array_" sind OHNE das Präfix als Funktionen dieser Klasse implementiert
 * Beispiel:  $dbarray->push()  anstatt  array_push($dbarray)
 */
class DBArray extends ArrayIterator {
	use DBTrait;

	public $id;

	/**
	 * Konstruiert ein neues Feld in der Datenbank.
	 * Dieses wird sofort angelegt und sollte daher in einem Attribut gespeichert werden, um nicht zu verwaisen.
	 * Nur in seltenen Fällen muss dieses Objekt aber manuell erstellt werden, da auch das normale [array] einem Attribut des DB-Objektes
	 * zugewiessen werden kann.
	 * @param OOSQL $oosql Der Datenbankadapter.
	 * @param array $feld Der Inhalt des Feldes.
	 * Und weitere interne Parameter, die nicht übergeben werden sollten.
	 */
	public function __construct(OOSQL $oosql, array $feld, $id = NULL, $indikator = NULL) {
		parent::__construct($feld);
		$this->construct($oosql, get_class($this), $indikator);
		if (!isset($id)) {

			$indikator = array();
			# Jetzt müssen alle Einträge des neuen Feldes zunächst einmal angelegt werden
			$sql = NULL;
			$ids = array_keys($feld);
			foreach ($ids as $ndx) {
				$this->evalFeld($feld[$ndx]);
				$ist_dbklasse = (int) $this->ist_dbklasse($feld[$ndx]);
				if (!isset($this->id)) {
					$this->oosql->query('INSERT INTO '.__CLASS__.' (ndx, wert, ref) VALUES (\''.addslashes($ndx).'\', \''.
						$this->nachSQL($feld[$ndx], $ist_dbklasse)."', $ist_dbklasse)");
					$this->id = $this->oosql->insert_id;
				} else {
					# Nachdem der Index bestimmt wurde, sind die restlichen Spalten mit nur einer Anfrage speicherbar
					$sql .= 'INSERT INTO '.__CLASS__." (id, ndx, wert, ref) VALUES ($this->id, '".addslashes($ndx).'\', \''.
						$this->nachSQL($feld[$ndx], $ist_dbklasse)."', $ist_dbklasse); ";
				}

				# Der Indikator muss hier für jedes Element angelegt werden
				array_push($indikator, $this->ist_dbklasse($feld[$ndx] ? 2 : FALSE ));
			}
			if (isset($sql)) {
				$this->oosql->multi_query($sql);
			}
			$this->indikator = $indikator;

		} else {
			$this->id = $id;
		}
	}

	public function offsetSet($ndx, $wert) {
		$this->evalFeld($wert);
		$ist_dbklasse = $this->ist_dbklasse($wert);

		if (parent::offsetExists($ndx)) {
			$this->oosql->query('UPDATE '.__CLASS__.' SET wert=\''.$this->nachSQL($wert, $ist_dbklasse).'\''.(
					($this->indikator[$ndx]?TRUE:FALSE) ^ $ist_dbklasse ? ", ref=$ist_dbklasse" : ''
				).$this->where($ndx));
		} else {
			$this->oosql->query('INSERT INTO '.__CLASS__.' (id, ndx, wert, ref) VALUES ('.intval($this->id).', \''.addslashes($ndx).
				'\', \''.$this->nachSQL($wert, $ist_dbklasse)."', $ist_dbklasse)");
		}

		$this->indikator[$ndx] = $ist_dbklasse ? 2 : FALSE;
		parent::offsetSet($ndx, $wert);
	}

	public function offsetGet($ndx) {
		$obj = parent::offsetGet($ndx);
		$this->evalObj($this->oosql, $obj, $this->indikator[$ndx]);
		# Die Methode parent::offsetGet gibt KEINE Referenz zurück, weshalb das Element nach der Evaluation überschrieben werden muss.
		parent::offsetSet($ndx, $obj);
		return $obj ?: NULL;
	}

	public function offsetUnset($ndx) {
		if (parent::offsetGet($ndx) !== NULL) {
			$this->oosql->query('DELETE FROM '.__CLASS__.where($ndx));
		}
		parent::offsetUnset($ndx);
	}

	private final function where($ndx) {
		return ' WHERE id='.intval($this->id).' AND ndx=\''.addslashes($ndx).'\'';
	}


	public function current() {
		return $this->offsetGet($this->key());
	}


	public function __call($fkt, $argumente) {
		$nativ = 'array_'.$fkt;
		if (is_callable($nativ)) {
			# Referenz des Inhaltes an die native Funktion weiterreichen
			return call_user_func_array($nativ, array_merge(array($this->getArrayCopy()), $argumente));
		} else {
			throw new BadMethodCallException(__CLASS__.'->'.$fkt);
		}
	}
}