CREATE TABLE IF NOT EXISTS dbarray (
	id int(10) unsigned NOT NULL AUTO_INCREMENT,
	ndx varchar(20) NOT NULL,
	wert varchar(20) NOT NULL,
	ref tinyint(1) NOT NULL,
	UNIQUE KEY (id,ndx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;