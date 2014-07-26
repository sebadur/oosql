<?php # Urheber dieser Datei ist Sebastian Badur. Die beiliegende Lizenz muss gewahrt bleiben.

/**
 * Die Möglichkeit, Felder in die Datenbank zu speichern.
 * Bei Verwendung ist die Tabelle `dbarray` in der Datenbank unangetastet zu lassen!
 */
class dbarray extends ArrayObject {
	use dbtrait;

	private $index;

	public function __construct($oosql, $feld, $index = NULL, $indikator = array()) {
		dbtrait::__construct($oosql, 'dbarray', $feld, $indikator);
		parent::__construct($feld);
		$this->index = $index;
	}

	public function offsetSet($id, $wert) {
		if (isset($this[$wert])) {
			$this->oosql->query('UPDATE `dbarray` SET `wert`=\'\''.$this->where($id));
		} else {
			if ($this->index !== NULL) {
				$this->oosql->query('INSERT INTO `dbarray` (`index`, `id`, `wert`) VALUES ('.intval($this->index).', \''.addslashes($id)
					.'\', \''.addslashes($wert).'\')');
			} else {
				$this->oosql->query('INSERT INTO `dbarray` (`id`, `wert`) VALUES ('.addslashes($id).'\', \''.addslashes($wert).'\')');
				$this->index = $this->oosql->query('SELECT LAST_INSERT_ID()');
			}
		}
		parent::offsetSet($id, $wert);
	}

	public function offsetGet($id) {
		$this->evalObj($this->oosql, $this[$id], $this->indikator[$id]);
		return $this[$id] ?: NULL;
	}

	public function offsetUnset($id) {
		if (isset($this[$id])) {
			$this->oosql->query('DELETE FROM `dbarray`'.where($id));
		}
		parent::offsetUnset($id);
	}

	private final function where($id) {
		return ' WHERE `index`='.intval($this->index).' AND `id`=\''.addslashes($id).'\'';
	}
}