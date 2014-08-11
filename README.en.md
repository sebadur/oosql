*[deutsche Version](/README.md)*

Extension OOSQL
===============

An object orientated interface for MySQL written in PHP. With this it is possible to save objects of own classes in your database and load them
as instances of these. **Real instances of the classes. Without helping constructs.**

**Testing phase has not finished yet.** Please report erroneous behaviour, so it can be corrected.

Utilization
-----------

Basically you need to import the class [OOSQL](oosql.php) and use it instead of `mysqli`. All objects extending the class [DBClass](dbclass.php)
are synchronized to the database then, so that every change to an attribute will be saved instantly to the database.

The attributes of the classes will be defined via the database. Just create the desired attribute and its name there, as every table in the
database stands for a class in PHP and owns all its instances. Though every class **must** have an attribute `id` as its primary key and an
attribute `ref` as integer (occupying one bit for every other attribute). Just as in PHP variables can be of any type, particulary being
instances of **other classes**, therefore append the appropriate attribute as a `varchar` with a normal key. Apart from that don't assign further
keys. Attributes given to the class via PHP can be used during runtime, but will not be integrated into the database.

Methodes and functions of the classes will be defined via PHP instead. If a class shall not have any further functionality, it don't has to be
declared.

Arrays and Cardinalities
------------------------

Variables of the type `array` can be easily assigned to attributes. After assignment the attribute-array will be of the type `DBArray`. The
particular attribute has to be designed as a reference in the database, thus `varchar`. Alternatively objects of the class `DBArray` can be
created manually, but that is only necessary, if they have to be deposited to the database immediately.

Import the table [dbarray.sql](dbarray.sql) to your database, if you want to use arrays.

Sample application
------------------

The enclosed script [beispiel.php](beispiel.php) is a demonstrative application of OOSQL. It will be usable after a few steps:
 1. Prepare a database
 2. Import the table [beispiel.sql](beispiel.sql)
 3. Create a file *geheim.php*, and therein the variables
  - $host = IP-adress of the databse (`localhost` or so)
  - $nutzer = name of a database user
  - $pw = his passwors
  - $dbname = name of the database (`beispiel` or so)
 4. Request the sample script

Then you should read:

> Hallo, mein Name ist Peter - und Werner und Dieter sind meine Kunden.<br>
> Hallo, mein Name ist Peter - und Werner und Dieter und Otto sind meine Kunden.

Try to reproduce the working flow of the sample script and also take note of the attached commentaries in it.

Class reference
---------------

The exactly documentations are enclosed to the sources. This is just an overview of all additional functionalities.

### OOSQL

```php
 var $sync = TRUE;
 function select(string $class [, string $condition]);
```

### DBClass

```php
 function __construct(OOSQL $oosql);
 function remove();
 function save();
```

### DBArray

```php
 function __construct(OOSQL $oosql, array $feld);
 function remove();
 function save();
 # Furthermore all native functions with the prefix array_* are implemented as functions without prefix (e.g. $dbarray->push())
```

Plans for Development
---------------------

- The programmer should decide when changes to the objects are getting inherit to the databse in future, to reduce the network traffic
- Analysis and evaluation of running time parameters, comprehensive test of functionality
- Identify possible problems of deficient process synchronization (due from the user)
- Strengthen the documentation

License
-------

For conditions of usage and redistribution read the [license](lizenz). The license is written in German and also applies for German law, similar
to the BSD-2 license.

**BEWARE: IF YOU WANT TO USE OR SHARE THIS SOFTWARE YOU HAVE TO ACCEPT AND FULLFILL THAT LICENSE EVEN IF YOU DO NOT UNDERSTAND GERMAN!
IGNORANCE IS NO EXCUSE by German law.**
