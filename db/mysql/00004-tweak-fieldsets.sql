CREATE TABLE dewdrop_field_groups (
    dewdrop_field_group_id INTEGER AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(128) NOT NULL,
    sort_index INTEGER NOT NULL,
    INDEX (sort_index)
);

CREATE TABLE dewdrop_sorted_fields (
    component VARCHAR(128) NOT NULL,
    field_id VARCHAR(128) NOT NULL,
    dewdrop_field_group_id INTEGER,
    sort_index INTEGER NOT NULL,
    PRIMARY KEY(component, field_id),
    FOREIGN KEY (dewdrop_field_group_id) REFERENCES dewdrop_field_groups (dewdrop_field_group_id) ON DELETE SET NULL,
    INDEX (component),
    INDEX (dewdrop_field_group_id),
    INDEX (sort_index)
);

DROP TABLE dewdrop_fieldset_fields;
DROP TABLE dewdrop_fieldset_groups;
