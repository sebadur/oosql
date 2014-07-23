<?php # Urheber dieser Datei ist Sebastian Badur. Die beiliegende Lizenz muss gewahrt bleiben.
include_once 'oosql.php';

/**
 * Eine Klasse, importiert aus der Datenbank.
 * Diese Klasse enthält automatisch alle Attribute aus der Datenbank und es werden keine weiteren Attribute per PHP in der Datenbank erzeugt.
 * In der Datenbank MUSS der Primärschlüssel `index` (als Zahl) vergeben werden. Dieser sollte nicht verändert werden.
 */
class person extends dbclass {
	/**
	 * Demonstration des Attributzugriffes.
	 * Problemlos können referenzierte Objekte implizit abgefragt und deren Eigenschaften somit ermittelt werden.
	 * Der Programmiervorgang ist somit für Objektorientierung trivial.
	 */
	public function vorstellen() {
		echo 'Hallo, mein Name ist '.$this->name;
		if (isset($this->arbeitgeber)) {
			# Es können alle Attribute in beliebiger Tiefe erreicht werden. Referenzen werden wenn nötig implizit durch DB-Anfragen aufgelöst
			echo ' und ich bin arbeite f&uuml;r '.$this->arbeitgeber->name;
		}
		echo '.';
	}
}

# Verbinden mit der Datenbank wie von [mysqli] gewohnt, nur über die Erweiterung [oosql]
include_once 'geheim.php';
$datenbank = new oosql($host, $nuter, $pw, $dbname);

# Die Datenbank wird aus guten Gründen weiterhin relational abstrahiert, deshalb muss zunächst ein Ankerobjekt angefordert werden
$peter = $datenbank->select('person', 'WHERE `name`=\'Peter\'')[0];

# Peter liegt schon jetzt als Objekt der Klasse [person] vor
$peter->vorstellen();