CREATE TABLE people (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(15) NOT NULL,
	password VARCHAR(32) NOT NULL,
	type INT UNSIGNED NOT NULL
);

CREATE TABLE equipments (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	title varchar(255),
	year varchar(255),
	size varchar(255),
        stamp varchar(255),
        comp varchar(255),
        price varchar(255),
	owner_id INT UNSIGNED NOT NULL REFERENCES people(id)
);

CREATE TABLE contacts (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	first_name varchar(255),
	last_name varchar(255),
	middle_name varchar(255),
	interest varchar(255),
	telephone varchar(255),
	email varchar(255),
	skype varchar(255),
	other varchar(255),
	equip_id INT NOT NULL,
  owner_id INT UNSIGNED NOT NULL,

  FOREIGN KEY (equip_id) REFERENCES equipments(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (owner_id) REFERENCES people(id)
);

CREATE TABLE interviews (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  date_time DATETIME,
# 	date DATE,
# 	time TIME,
	goal varchar(255),
	result varchar(255),
	contact_id INT NOT NULL,
	owner_id INT UNSIGNED NOT NULL,

  FOREIGN KEY (contact_id) REFERENCES contacts(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  FOREIGN KEY (owner_id) REFERENCES people(id)
);
