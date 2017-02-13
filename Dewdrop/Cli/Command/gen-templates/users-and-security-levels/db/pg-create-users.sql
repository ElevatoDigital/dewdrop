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
	"last_name" varchar(128)
);