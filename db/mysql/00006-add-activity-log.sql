CREATE TABLE dewdrop_activity_log (
    dewdrop_activity_log_id INTEGER PRIMARY KEY AUTO_INCREMENT,
    message TEXT,
    date_created TIMESTAMP DEFAULT NOW() NOT NULL
) ENGINE=InnoDB CHARSET=utf8;

CREATE TABLE dewdrop_activity_log_entities (
    dewdrop_activity_log_entity_id INTEGER PRIMARY KEY AUTO_INCREMENT,
    dewdrop_activity_log_id INTEGER NOT NULL,
    handler VARCHAR(128) NOT NULL,
    primary_key_value INTEGER NOT NULL,
    title_text VARCHAR(512) NOT NULL,
    FOREIGN KEY (dewdrop_activity_log_id) REFERENCES dewdrop_activity_log (dewdrop_activity_log_id)
) ENGINE=InnoDB CHARSET=utf8;
