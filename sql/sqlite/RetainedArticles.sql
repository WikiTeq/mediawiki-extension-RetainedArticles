-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: sql/RetainedArticles.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE /*_*/retained_articles (
  ra_deleted_page_namespace INTEGER DEFAULT 0 NOT NULL,
  ra_deleted_page_title BLOB DEFAULT '' NOT NULL,
  ra_retained_page_id INTEGER UNSIGNED DEFAULT NULL,
  ra_retained_page_namespace INTEGER DEFAULT 0 NOT NULL,
  ra_retained_page_title BLOB DEFAULT '' NOT NULL,
  ra_actor_id INTEGER UNSIGNED NOT NULL,
  ra_timestamp BLOB NOT NULL,
  PRIMARY KEY(
    ra_deleted_page_namespace, ra_deleted_page_title
  )
);

CREATE INDEX ra_retained_page ON /*_*/retained_articles (
  ra_retained_page_namespace, ra_retained_page_title
);
