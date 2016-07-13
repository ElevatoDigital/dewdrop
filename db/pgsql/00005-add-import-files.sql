CREATE TABLE dewdrop_import_files (
    dewdrop_import_file_id SERIAL PRIMARY KEY,
    component VARCHAR(128) NOT NULL,
    full_path VARCHAR(1024) NOT NULL,
    first_row_is_headers BOOLEAN DEFAULT true NOT NULL,
    date_uploaded TIMESTAMP DEFAULT NOW() NOT NULL
);

CREATE TABLE dewdrop_import_file_records (
    dewdrop_import_file_id INTEGER REFERENCES dewdrop_import_files NOT NULL,
    record_primary_key_value INTEGER NOT NULL,
    PRIMARY KEY(dewdrop_import_file_id, record_primary_key_value)
);
