ALTER TABLE dewdrop_test_animals ADD COLUMN favorite_fruit_id INTEGER;

ALTER TABLE dewdrop_test_animals
    ADD CONSTRAINT animal_to_fruit_fk
    FOREIGN KEY (favorite_fruit_id)
    REFERENCES dewdrop_test_fruits (dewdrop_test_fruit_id);
