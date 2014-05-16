CREATE TABLE {{table_name}} (
    change_number INTEGER,
    delta_set VARCHAR(32) NOT NULL,
    start_dt TIMESTAMP NOT NULL,
    complete_dt TIMESTAMP,
    applied_by VARCHAR(32) NOT NULL,
    description TEXT NOT NULL,
    PRIMARY KEY (change_number, delta_set)
) ENGINE=INNODB CHARSET=utf8;
