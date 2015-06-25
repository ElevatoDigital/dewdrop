CREATE TABLE dewdrop_test_fruits (
    dewdrop_test_fruit_id SERIAL PRIMARY KEY,
    name VARCHAR(128) UNIQUE NOT NULL,
    is_delicious BOOLEAN DEFAULT true NOT NULL,
    level_of_deliciousness INTEGER DEFAULT 0 NOT NULL
);
