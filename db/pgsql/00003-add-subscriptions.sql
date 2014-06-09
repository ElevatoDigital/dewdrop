CREATE TABLE dewdrop_mail_log (
    dewdrop_mail_log_id SERIAL PRIMARY KEY,
    to_address VARCHAR(128) NOT NULL,
    from_address VARCHAR(128) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_html TEXT,
    body_plaintext TEXT,
    date_sent TIMESTAMP DEFAULT NOW() NOT NULL,
    sent_successfully BOOLEAN NOT NULL
);

CREATE TABLE dewdrop_notification_frequencies (
    dewdrop_notification_frequency_id SERIAL PRIMARY KEY,
    name VARCHAR(128) NOT NULL
);

INSERT INTO dewdrop_notification_frequencies (name) VALUES
    ('Immediately'),
    ('Daily'),
    ('Weekly');

CREATE TABLE dewdrop_notification_subscriptions (
    dewdrop_notification_subscription_id SERIAL PRIMARY KEY,
    component VARCHAR(128) NOT NULL,
    dewdrop_notification_frequency_id INTEGER REFERENCES dewdrop_notification_frequencies NOT NULL,
    when_added BOOLEAN DEFAULT true NOT NULL,
    when_edited BOOLEAN DEFAULT true NOT NULL,
    preferred_time_of_day TIME,
    preferred_day_of_week INTEGER,
    date_created TIMESTAMP DEFAULT NOW() NOT NULL,
    date_updated TIMESTAMP DEFAULT NOW() NOT NULL
);

CREATE TABLE dewdrop_notification_subscription_recipients (
    dewdrop_notification_subscription_recipient_id SERIAL PRIMARY KEY,
    dewdrop_notification_subscription_id INTEGER REFERENCES dewdrop_notification_subscriptions NOT NULL,
    email_address VARCHAR(255) NOT NULL
);

CREATE TABLE dewdrop_notification_subscription_fields (
    dewdrop_notification_subscription_id INTEGER REFERENCES dewdrop_notification_subscriptions NOT NULL,
    field_id VARCHAR(128) NOT NULL,
    PRIMARY KEY (dewdrop_notification_subscription_id, field_id)
);

CREATE TABLE dewdrop_notification_subscription_log (
    dewdrop_notification_subscription_id INTEGER REFERENCES dewdrop_notification_subscriptions NOT NULL,
    dewdrop_mail_log_id INTEGER REFERENCES dewdrop_mail_log NOT NULL,
    PRIMARY KEY (dewdrop_notification_subscription_id, dewdrop_mail_log_id)
);
