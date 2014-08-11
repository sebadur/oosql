<?php # Urheber dieser Datei ist Sebastian Badur. Die beiliegende Lizenz muss gewahrt bleiben.
include_once 'dbtrait.php';
include_once 'dbclass.php';
include_once 'dbarray.php';

class OOSQL extends mysqli {
	private $instanzen = array();

	/**
	 * Wenn wahr, dann wird jede Änderung an diesem Objekt unmittelbar in die Datenbank übernommen. Wenn dieses Verhalten nicht erwünscht ist
	 * (weil dadurch die Netzwerkbelastung steigt), dann kann das Objekt mit der Funktion save() manuell abgespeichert werden.
	 */
	public $sync = TRUE;

	/*public final function query($query) { # Debug
		echo $query.'<br>';
		return parent::query($query);
	}
	public final function multi_query($query) {
		echo $query.'<br>';
		return parent::multi_query($query);
	}*/

	public function __construct() {
		call_user_func_array('parent::__construct', func_get_args());
	}

	/**
	 * Holt bestimmte Objekte aus der Datenbank.
	 * Alle Objekte in der (über den Konstruktor definierten) Datenbank, die der angegebenen Klasse entsprechen werden den angegebenen Kriterien
	 * entsprechend selektiert. Die Bedingung ist dabei in SQL-Syntax (beginnend etwa mit WHERE oder ORDER BY, ...) anzugeben und auf
	 * zusätzliche Anfragen (per Semikolon) ist zu verzichten. Wenn nur ein oder kein Objekt die genannten Kriterien erfüllt, dann wird dennoch
	 * ein Feld mit diesem einem, beziehungsweise keinem Objekt zurückgegeben.
	 * Die zurückgegebenen Objekte sind ausnahmslos Instanzen der angegebenen Klasse und müssen von [dbclass] erben.
	 * @param string $klasse Der Name der Klasse des Objektes.
	 * @param string $bedingung Auswahlkriterien in SQL-Sytax, optional.
	 * @return boolean|array Genau dann FALSE, falls entweder die Parameter falsch angegeben wurden, oder falls sie für die Datenbank keinen Sinn
	 * ergeben haben. Ansonsten ein Feld mit den ausgewählten Objekten in richtiger Reihenfolge (so, wie man sie durch eine normale Anfrage mit
	 * den gleichen Auswahlkriterien bekommen würde). Von einem Objekt wird während der gesamten Laufzeit nur eine Instanz zurückgegeben; Kopien
	 * müssen manuell angefertigt werden.
	 */
	public final function select($klasse, $bedingung='') {
		# Fehleingaben abfangen
		if (!(is_string($klasse) && is_string($bedingung))) {
			return FALSE;
		}

		# Ausgewählte Objekte holen
		$alleObjekte = $this->query('SELECT * FROM '.$klasse.' '.$bedingung);
		if ($alleObjekte === FALSE) {
			return FALSE;
		}

		$erg = array();
		if (!is_a($klasse, 'DBArray', TRUE)) {

			# Alle Attribute belegen
			while ($einObjekt = $alleObjekte->fetch_assoc()) {
				if ($einObjekt === NULL) {
					continue; # Sicherheitsabfrage, da ansonsten gleich ein neues Objekt in der Datenbank erzeugt würde
				}

				# Indikator extrahieren und umwandeln
				$indikator = array();
				$ref = intval($einObjekt['ref']);
				unset($einObjekt['ref']);
				$namen = array_keys($einObjekt);
				foreach (array_reverse($namen) as $name) { # Gegenläufig zu dbtrait::ref()
					if ($name !== 'id') { # Der Index kann keine Referenz sein
						$indikator[$name] = (boolean) ($ref & 1);
						$ref >>= 1;
					}
				}

				$dbklasse = class_exists($klasse) ? $klasse : 'DBClass';
				$obj = new $dbklasse($this, $einObjekt, $indikator);
				
				# Instanzenfeld initialisieren
				if (!isset($this->instanzen[$klasse])) {
					$this->instanzen[$klasse] = array();
				}

				# Objekt für Rückgabe abspeichern
				if (isset($this->instanzen[$klasse][$obj->id])) { # Dieses Objekt gibt es schon
					array_push($erg, $this->instanzen[$klasse][$obj->id]);
					unset($obj);
				} else { # Dieses Objekt ist neu
					array_push($erg, $obj);
					$this->instanzen[$klasse][$obj->id] = &$obj;
				}
			}

		} else {
			$nIndex = -1;
			$indizes = array(); # Index des Objekts => Index für $feld und $indikator
			$feld = array();
			$indikator = array();

			while ($eintrag = $alleObjekte->fetch_assoc()) {
				$id = $eintrag['id'];
				if (!isset($indizes[$id])) {
					$nIndex++;
					$indizes[$id] = $nIndex;
					$feld[$nIndex] = array();
					$indikator[$nIndex] = array();
				}
				$feld[$indizes[$id]][$eintrag['ndx']] = $eintrag['wert'];
				# Der Indikator wird hier nur für das Attribut wert benötigt, muss also nicht aufgearbeitet werden
				$indikator[$indizes[$id]][$eintrag['ndx']] = boolval($eintrag['ref']);
			}

			foreach ($indizes as $id => $n) {
				$dbarray = new $klasse($this, $feld[$n], $id, $indikator[$n]);
				array_push($erg, $dbarray);
			}
		}

		return $erg;
	}
}