CREATE TABLE dewdrop_activity_log (
    dewdrop_activity_log_id SERIAL PRIMARY KEY,
    message TEXT,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE dewdrop_activity_log_entities (
    dewdrop_activity_log_entity_id SERIAL PRIMARY KEY,
    dewdrop_activity_log_id INTEGER REFERENCES dewdrop_activity_log NOT NULL,
    handler VARCHAR(128) NOT NULL,
    primary_key_value INTEGER NOT NULL,
    title_text VARCHAR(512) NOT NULL
);
