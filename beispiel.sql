CREATE TABLE IF NOT EXISTS person (
	id int(10) unsigned NOT NULL AUTO_INCREMENT,
	name varchar(30) NOT NULL,
	kunden varchar(20) NOT NULL,
	ref tinyint(2) NOT NULL,
	PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO person (id, name, kunden, ref) VALUES
	(1, 'Dieter', '', 0),
	(2, 'Werner', '', 0),
	(3, 'Peter', 'dbarray 1', 1),
	(4, 'Otto', '', 0);
CREATE TABLE IF NOT EXISTS dbarray (
	id int(10) unsigned NOT NULL AUTO_INCREMENT,
	ndx varchar(20) NOT NULL,
	wert varchar(20) NOT NULL,
	ref tinyint(1) NOT NULL,
	UNIQUE KEY (id,ndx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO dbarray (id, ndx, wert, ref) VALUES
	(1, '3', 'person 2', 1),
	(1, 'A', 'person 1', 1);