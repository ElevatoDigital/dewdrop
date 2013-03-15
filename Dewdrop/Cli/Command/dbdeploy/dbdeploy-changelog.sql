CREATE TABLE dbdeploy_changelog (
    change_number INTEGER PRIMARY KEY,
    delta_set VARCHAR(10) NOT NULL,
    start_dt TIMESTAMP NOT NULL,
    complete_dt TIMESTAMP,
    applied_by VARCHAR(32) NOT NULL,
    description TEXT NOT NULL
) ENGINE=INNODB CHARSET=utf8;
