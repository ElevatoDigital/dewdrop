CREATE TABLE dewdrop_activity_log_user_information (
    dewdrop_activity_log_user_information_id SERIAL PRIMARY KEY,
    ip_address VARCHAR(32) NOT NULL,
    sapi_name VARCHAR(32) NOT NULL,
    user_agent VARCHAR(255) NOT NULL,
    geo_city VARCHAR(32),
    geo_region VARCHAR(32),
    geo_country VARCHAR(32),
    geo_country_code VARCHAR(6),
    geo_latitude VARCHAR(32),
    geo_longitude VARCHAR(32)
);

ALTER TABLE dewdrop_activity_log ADD dewdrop_activity_log_user_information_id INTEGER
    REFERENCES dewdrop_activity_log_user_information;
