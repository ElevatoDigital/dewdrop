CREATE TABLE "security_levels" (
    "security_level_id" SERIAL PRIMARY KEY,
    "name" varchar(32) NOT NULL,
    "deleted" bool NOT NULL DEFAULT false,
    "sort_index" INTEGER NOT NULL DEFAULT 99999
);

INSERT INTO "security_levels" (name) VALUES ('Admin');
