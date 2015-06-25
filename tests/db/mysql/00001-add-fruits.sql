CREATE TABLE dewdrop_test_fruits (
    dewdrop_test_fruit_id INTEGER PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(128) UNIQUE NOT NULL,
    is_delicious BOOLEAN DEFAULT true NOT NULL,
    level_of_deliciousness INTEGER DEFAULT 0 NOT NULL
) ENGINE=InnoDB CHARSET=utf8;
