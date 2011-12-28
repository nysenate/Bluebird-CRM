DROP DATABASE IF EXISTS signups;
CREATE DATABASE signups;

USE signups;

CREATE TABLE list (
    id int(10) unsigned AUTO_INCREMENT PRIMARY KEY,
    title varchar(255),
    UNIQUE INDEX(title)
) ENGINE=InnoDB;

CREATE TABLE senator (
    nid int(10) unsigned PRIMARY KEY,
    title varchar(255),
    district int(10) unsigned,
    list_id int(10) unsigned,
    INDEX(title),
    INDEX(district),
    FOREIGN KEY (list_id) REFERENCES list (id)
);

CREATE TABLE committee (
    nid int(10) unsigned PRIMARY KEY,
    title varchar(255),
    chair_nid int(10) unsigned,
    list_id int(10) unsigned,
    INDEX(title),
    FOREIGN KEY (list_id) REFERENCES list (id),
    FOREIGN KEY (chair_nid) REFERENCES senator (nid)
);

-- Not auto_increment because we get id from drupal
CREATE TABLE person (
    id int(10) unsigned PRIMARY KEY,
    first_name varchar(255),
    last_name varchar(255),
    address1 varchar(255),
    address2 varchar(255),
    city varchar(100),
    state varchar(50),
    zip varchar(10),
    phone varchar(20),
    district int(10) unsigned,
    email varchar(255),
    status varchar(255),
    created datetime,
    modified datetime,
    INDEX(first_name),
    INDEX(last_name),
    INDEX(address1),
    INDEX(address2),
    INDEX(district),
    INDEX(city),
    INDEX(state),
    INDEX(zip),
    INDEX(email),
    INDEX(status),
    INDEX(created),
    INDEX(modified)
) ENGINE=InnoDB;

CREATE TABLE signup (
    id int(10) unsigned AUTO_INCREMENT PRIMARY KEY,
    list_id int(10) unsigned,
    person_id int(10) unsigned,
    UNIQUE KEY `unqiue_signup` (list_id, person_id),
    FOREIGN KEY (list_id) REFERENCES list (id),
    FOREIGN KEY (person_id) REFERENCES person (id)
) ENGINE=InnoDB;

CREATE TABLE issue (
    id int(10) unsigned AUTO_INCREMENT PRIMARY KEY,
    person_id int(10) unsigned,
    issue varchar(255),
    INDEX(issue),
    UNIQUE KEY (person_id, issue),
    FOREIGN KEY (person_id) REFERENCES person (id)
) ENGINE=InnoDB;
