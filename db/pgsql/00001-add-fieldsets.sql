CREATE TABLE dewdrop_fieldsets (
    fieldset_name VARCHAR(32) PRIMARY KEY
);

CREATE TABLE dewdrop_fieldset_groups (
    dewdrop_fieldset_group_id SERIAL PRIMARY KEY,
    fieldset_name VARCHAR(32) REFERENCES dewdrop_fieldsets NOT NULL,
    title VARCHAR(128) NOT NULL,
    caption TEXT,
    sort_index INTEGER NOT NULL DEFAULT 999999
);

CREATE TABLE dewdrop_fieldset_fields (
    dewdrop_fieldset_field_id SERIAL PRIMARY KEY,
    dewdrop_fieldset_group_id INTEGER REFERENCES dewdrop_fieldset_groups,
    fieldset_name VARCHAR(32) REFERENCES dewdrop_fieldsets NOT NULL,
    field_name VARCHAR(255) NOT NULL,
    sort_index INTEGER NOT NULL DEFAULT 999999
);

CREATE INDEX dewdrop_fieldset_fields_group_id_idx ON dewdrop_fieldset_fields (dewdrop_fieldset_group_id);
