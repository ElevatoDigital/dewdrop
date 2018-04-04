CREATE TABLE "users" (
	"user_id" SERIAL PRIMARY KEY,
	"deleted" bool NOT NULL DEFAULT false,
	"security_level_id" INTEGER NOT NULL,
	"username" varchar(128) NOT NULL,
	"email_address" varchar(255) NOT NULL,
	"password_hash" varchar(60) NOT NULL,
	"date_created" timestamp(6) NOT NULL DEFAULT now(),
	"date_updated" timestamp(6) NOT NULL DEFAULT now(),
	"last_successful_login_date" timestamp(6) NULL,
	"first_name" varchar(128),
	"last_name" varchar(128),
	CONSTRAINT fk_users_security_level_id FOREIGN KEY (security_level_id)
		REFERENCES security_levels (security_level_id)
);

INSERT INTO users(security_level_id, username, email_address, password_hash) VALUES (
    '1',
    '{{adminUsername}}',
    '{{adminEmail}}',
    '{{adminPassword}}'
);
