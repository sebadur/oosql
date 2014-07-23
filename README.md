*[english version](README.en.md)*

Erweiterung oosql
=================

Eine objektorientierte Schnittstelle für MySQL in PHP. Damit ist es möglich, Objekte eigener Klassen in der Datenbank zu speichern und als solche
wieder zu laden. **Echte Instanzen der Klassen. Ohne Hilfskonstrukte.**

**Die Entwicklung ist noch nicht abgeschlossen.** Bitte verwenden Sie diese Erweiterung zunächst nur experimentell.

Verwendung
----------

Grundsätzlich muss die Klasse [oosql](oosql.php) eingebunden und anstatt *mysqli* verwendet werden. Alle Objekte, die von der Klasse [dbclass]
(dbclass.php) erben sind dann mit der jeweiligen Datenbank synchronisiert, sodass jede Änderung an deren Attributen sofort in die Datenbank
gespeichert wird.

Die Attribute der Klassen werden über die Datenbank definiert. Einfach dort die gewünschten Attribute und deren Namen anlegen, wobei jede
Tabelle in der Datenbank für eine Klasse in PHP steht und alle deren Instanzen besitzt. Dabei **muss** jede Klasse das Attribut *index* als
Primärschlüssel besitzen. Genauso wie in PHP können Variablen auch beliebigen Typs, insbesondere Instanzen **anderer Klassen** sein, dafür das
entsprechende Attribut als *varchar* mit einem Schlüssel versehen. Ansonsten keine weiteren Schlüssel vergeben. In PHP an die Klasse vergebene
Attribute können zwar zur Laufzeit genutzt, aber nicht in die Datenbank aufgenommen werden.

Die Methoden und Funktionen der Klassen werden statt dessen in PHP definiert. Soll eine Klasse über keine zusätzliche Funktionalität verfügen,
muss sie nicht deklariert werden.

Beispielanwendung
-----------------

Mit dem Skript [beispiel.php](beispiel.php) liegt eine demonstrative Anwendung von oosql bei. Es lässt sich nach wenigen Schritten ausführen:
 1. Eine Datenbank einrichten
 2. Die Tabelle [beispiel.sql](beispiel.sql) importieren
 3. Eine Datei *geheim.php* erstellen, und darin die Variablen
  - $host = IP-Adresse der Datenbank (etwa *localhost*)
  - $nutzer = Name eines Benutzers der Datenbank
  - $pw = dessen Passwort
  - $dbname = Name der Datenbank (etwa *beispiel*)
 4. Das Beispielskript aufrufen

Die Ausgabe sollte dann lauten:

    Hallo, mein Name ist Peter und ich arbeite für Dieter.

Versuchen Sie die Arbeitsweise des Beispielskriptes nachzuvollziehen und nehmen Sie auch die darin vermerkten Kommentare zur Kenntnis.

Pläne für die Entwicklung
-------------------------

- Der Programmierer soll zukünftig darüber entscheiden können, wann Änderungen an den Objekten in die Datenbank übernommen werden, um die
Netzwerkbelastung zu reduzieren
- Analyse und Auswertung von Laufzeitparametern, umfassender Test der Funktionalität
- Mögliche Probleme mangelnder Prozesssynchronisation (seitens des Anwenders) erkennen
- Dokumentation ausbauen

Lizenz
------

Für Bedingungen zur Nutzung und Weiterverbreitung ist die [Lizenz](lizenz) zu lesen.