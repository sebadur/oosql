<?php # Urheber dieser Datei ist Sebastian Badur. Die beiliegende Lizenz muss gewahrt bleiben.
include 'dbclass.php';

class oosql extends mysqli {
	private $instanzen = array();

/*	public final function query($query) { # Debug
		echo $query.'<br>';
		return parent::query($query);
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
	 * @param type $klasse Der Name der Klasse des Objektes.
	 * @param type $bedingung Auswahlkriterien in SQL-Sytax, optional.
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

		$erg = FALSE;
		# Ausgewählte Objekte holen
		$alleObjekte = $this->query('SELECT * FROM `'.$klasse.'` '.$bedingung);
		if ($alleObjekte !== FALSE) {

			# Auf höheres Objekt prüfen
			$indikator = array();
			$alleBeschr = $this->query('DESCRIBE `'.$klasse.'`');
			if ($alleBeschr !== FALSE) {
				while ($eineBeschr = $alleBeschr->fetch_assoc()) {
					$indikator[$eineBeschr['Field']] = $eineBeschr['Key']==='MUL';
				}
			}

			# Alle Attribute belegen
			$erg = array();
			while ($einObjekt = $alleObjekte->fetch_assoc()) {
				if ($einObjekt === NULL) {
					continue; # Sicherheitsabfrage, da ansonsten gleich ein neues Objekt in der Datenbank erzeugt würde
				}
				$dbklasse = class_exists($klasse) ? $klasse : 'dbclass';
				$obj = new $dbklasse($this, $klasse, $einObjekt, $indikator);
				
				# Instanzenfeld initialisieren
				if (!isset($this->instanzen[$klasse])) {
					$this->instanzen[$klasse] = array();
				}

				# Objekt für Rückgabe abspeichern
				if (isset($this->instanzen[$klasse][$obj->index])) { # Dieses Objekt gibt es schon
					array_push($erg, &$this->instanzen[$klasse][$obj->index]);
					unset($obj);
				} else { # Dieses Objekt ist neu
					array_push($erg, $obj);
					$this->instanzen[$klasse][$obj->index] = &$obj;
				}
			}

		}

		return $erg;
	}
}