CREATE TABLE dewdrop_test_animals (
    dewdrop_test_animal_id SERIAL PRIMARY KEY,
    name VARCHAR(64) NOT NULL,
    is_fierce BOOLEAN NOT NULL,
    is_cute BOOLEAN NOT NULL
);
