<?php # Urheber dieser Datei ist Sebastian Badur. Die beiliegende Lizenz muss gewahrt bleiben.

/**
 * Die Möglichkeit, Felder in die Datenbank zu speichern.
 * Bei Verwendung ist die Tabelle `dbarray` in der Datenbank unangetastet zu lassen!
 */
class dbarray extends ArrayObject {
	use dbtrait;

	public $index;

	public function __construct(oosql $oosql, array $feld, $index = NULL, $indikator = array()) {
		parent::__construct($feld);
		$this->construct($oosql, 'dbarray', $indikator);
		if (!isset($index)) {

			# Jetzt müssen alle Einträge des neuen Feldes zunächst einmal angelegt werden
			$sql = NULL;
			$ids = array_keys($feld);
			foreach ($ids as $id) {
				$ist_dbklasse = $this->ist_dbklasse($feld[$id]);
				if (!isset($this->index)) {
					$this->oosql->query('INSERT INTO `dbarray` (`id`, `wert`, `ref`) VALUES (\''.addslashes($id).'\', \''.
						$this->nachSQL($feld[$id], $ist_dbklasse)."', $ist_dbklasse)");
					$this->index = $this->oosql->insert_id;
				} else {
					# Nachdem der Index bestimmt wurde, sind die restlichen Spalten mit nur einer Anfrage speicherbar
					$sql .= "INSERT INTO `dbarray` (`index`, `id`, `wert`, `ref`) VALUES ($this->index, '".addslashes($id).'\', \''.
						$this->nachSQL($feld[$id], $ist_dbklasse)."', $ist_dbklasse); ";
				}
			}
			if (isset($sql)) {
				$this->oosql->query($sql);
			}

		} else {
			$this->index = $index;
		}
	}

	public function offsetSet($id, $wert) {
		$ist_dbklasse = $this->ist_dbklasse($wert);

		if (parent::offsetGet($id) !== NULL) {
			$this->oosql->query('UPDATE `dbarray` SET `wert`=\''.$this->nachSQL($wert, $ist_dbklasse).'\''.(
					($this->indikator[$id]?TRUE:FALSE) ^ $ist_dbklasse ? ", `ref`=$ist_dbklasse" : ''
				).$this->where($id));
		} else {
			$this->oosql->query('INSERT INTO `dbarray` (`index`, `id`, `wert`, `ref`) VALUES ('.intval($this->index).', \''.addslashes($id).
				'\', \''.$this->nachSQL($wert, $ist_dbklasse)."', $ist_dbklasse)");
		}

		$this->indikator[$id] = $ist_dbklasse ? 2 : FALSE;
		parent::offsetSet($id, $wert);
	}

	public function offsetGet($id) {
		$obj = parent::offsetGet($id);
		$this->evalObj($this->oosql, $obj, $this->indikator[$id]);
		return $obj ?: NULL;
	}

	public function offsetUnset($id) {
		if (parent::offsetGet($id) !== NULL) {
			$this->oosql->query('DELETE FROM `dbarray`'.where($id));
		}
		parent::offsetUnset($id);
	}

	private final function where($id) {
		return ' WHERE `index`='.intval($this->index).' AND `id`=\''.addslashes($id).'\'';
	}
}