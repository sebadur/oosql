<?php # Urheber dieser Datei ist Sebastian Badur. Die beiliegende Lizenz muss gewahrt bleiben.
include_once 'oosql.php';

/**
 * Eine Klasse, importiert aus der Datenbank.
 * Diese Klasse enthält automatisch alle Attribute aus der Datenbank und es werden keine weiteren Attribute per PHP in der Datenbank erzeugt.
 * In der Datenbank MUSS der Primärschlüssel id (als Zahl) vergeben werden. Dieser sollte nicht verändert werden.
 */
class Person extends DBClass {
	/**
	 * Demonstration des Attributzugriffes.
	 * Problemlos können referenzierte Objekte implizit abgefragt und deren Eigenschaften somit ermittelt werden.
	 * Der Programmiervorgang ist somit für Objektorientierung trivial.
	 */
	public function vorstellen() {
		echo 'Hallo, mein Name ist '.$this->name;
		# Die Überprüfung auf Existenz eines Attributes findet ohne Datenbankabfrage statt, auch wenn die Referenz noch nicht aufgelöst wurde
		if (isset($this->kunden)) {
			echo ' - ';
			# Es können alle Attribute in beliebiger Tiefe erreicht werden. Referenzen werden wenn nötig implizit durch DB-Anfragen aufgelöst
			foreach ($this->kunden as $kunde) {
				echo ' und '.$kunde->name;
			}
			echo ' sind meine Kunden';
		}
		echo '.<br>';
	}
}

# Verbinden mit der Datenbank wie von [mysqli] gewohnt, nur über die Erweiterung [oosql]
include_once 'geheim.php';
$datenbank = new OOSQL($host, $nutzer, $pw, $dbname);

# Die Datenbank wird aus guten Gründen weiterhin relational abstrahiert, deshalb muss zunächst ein Ankerobjekt angefordert werden
$peter = $datenbank->select('person', 'WHERE name=\'Peter\'') [0]?:NULL;
if (isset($peter)) {

	# Peter liegt schon jetzt als Objekt der Klasse [person] vor
	$peter->vorstellen();

	# Ein neuer Kunde kann ganz einfach hinzugefügt werden
	$otto = $datenbank->select('person', 'WHERE name=\'Otto\'') [0]?:NULL;
	$peter->kunden['neu'] = $otto;

	$peter->vorstellen();

}