-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: sql/RetainedArticles.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE retained_articles (
  ra_deleted_page_namespace INT DEFAULT 0 NOT NULL,
  ra_deleted_page_title TEXT DEFAULT '' NOT NULL,
  ra_retained_page_id INT DEFAULT NULL,
  ra_retained_page_namespace INT DEFAULT 0 NOT NULL,
  ra_retained_page_title TEXT DEFAULT '' NOT NULL,
  ra_actor_id INT NOT NULL,
  ra_timestamp TIMESTAMPTZ NOT NULL,
  PRIMARY KEY(
    ra_deleted_page_namespace, ra_deleted_page_title
  )
);

CREATE INDEX ra_retained_page ON retained_articles (
  ra_retained_page_namespace, ra_retained_page_title
);