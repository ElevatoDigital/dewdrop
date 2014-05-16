CREATE TABLE dewdrop_test_fruits_eaten_by_animals (
    animal_id INTEGER NOT NULL,
    fruit_id INTEGER NOT NULL,
    INDEX (fruit_id),
    FOREIGN KEY (animal_id) REFERENCES dewdrop_test_animals (dewdrop_test_animal_id),
    FOREIGN KEY (fruit_id) REFERENCES dewdrop_test_fruits (dewdrop_test_fruit_id),
    PRIMARY KEY(animal_id, fruit_id)
) ENGINE=InnoDB CHARSET=utf8;
