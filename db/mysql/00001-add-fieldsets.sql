CREATE TABLE dewdrop_fieldsets (
    fieldset_name VARCHAR(32) PRIMARY KEY
) ENGINE=InnoDB CHARSET=utf8;

CREATE TABLE dewdrop_fieldset_groups (
    dewdrop_fieldset_group_id INTEGER PRIMARY KEY AUTO_INCREMENT,
    fieldset_name VARCHAR(32) NOT NULL,
    title VARCHAR(128) NOT NULL,
    caption TEXT,
    sort_index INTEGER NOT NULL DEFAULT 999999,
    FOREIGN KEY (fieldset_name) REFERENCES dewdrop_fieldsets (fieldset_name)
) ENGINE=InnoDB CHARSET=utf8;

CREATE TABLE dewdrop_fieldset_fields (
    dewdrop_fieldset_field_id INTEGER PRIMARY KEY AUTO_INCREMENT,
    dewdrop_fieldset_group_id INTEGER,
    fieldset_name VARCHAR(32) NOT NULL,
    field_name VARCHAR(255) NOT NULL,
    sort_index INTEGER NOT NULL DEFAULT 999999,
    INDEX (dewdrop_fieldset_group_id),
    FOREIGN KEY (fieldset_name) REFERENCES dewdrop_fieldsets (fieldset_name),
    FOREIGN KEY (dewdrop_fieldset_group_id) REFERENCES dewdrop_fieldset_groups (dewdrop_fieldset_group_id)
) ENGINE=InnoDB CHARSET=utf8;

