CREATE TABLE dewdrop_test_fruits_eaten_by_animals (
    animal_id INTEGER REFERENCES dewdrop_test_animals NOT NULL,
    fruit_id INTEGER REFERENCES dewdrop_test_fruits NOT NULL,
    PRIMARY KEY(animal_id, fruit_id)
);

CREATE INDEX dtfeba_fruit_id_idx ON dewdrop_test_fruits_eaten_by_animals (fruit_id);
