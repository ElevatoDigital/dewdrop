CREATE TABLE dewdrop_test_animals (
    dewdrop_test_animal_id INTEGER PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(64) NOT NULL,
    is_fierce BOOLEAN NOT NULL,
    is_cute BOOLEAN NOT NULL
) ENGINE=InnoDB CHARSET=utf8;
