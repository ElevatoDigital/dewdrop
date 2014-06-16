CREATE TABLE dewdrop_field_groups (
    dewdrop_field_group_id SERIAL PRIMARY KEY,
    title VARCHAR(128) NOT NULL,
    sort_index INTEGER NOT NULL
);

CREATE TABLE dewdrop_sorted_fields (
    component VARCHAR(128) NOT NULL,
    field_id VARCHAR(128) NOT NULL,
    dewdrop_field_group_id INTEGER REFERENCES dewdrop_field_groups ON DELETE SET NULL,
    sort_index INTEGER NOT NULL,
    PRIMARY KEY(component, field_id)
);

CREATE INDEX dewdrop_sorted_fields_component_idx ON dewdrop_sorted_fields (component);
CREATE INDEX dewdrop_sorted_fields_group_idx ON dewdrop_sorted_fields (dewdrop_field_group_id);
CREATE INDEX dewdrop_sorted_fields_sort_index_idx ON dewdrop_sorted_fields (sort_index);

CREATE INDEX dewdrop_field_groups_sort_index_idx ON dewdrop_field_groups (sort_index);

DROP TABLE dewdrop_fieldset_fields;
DROP TABLE dewdrop_fieldset_groups;
