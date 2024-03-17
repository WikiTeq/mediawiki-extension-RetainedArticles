#!/bin/bash

dir=$(dirname "$0")
echo "$dir"
for db in mysql postgres sqlite
do
	schema=RetainedArticles
	echo $db : $schema
	mkdir -p "$dir/$db"
	php "$dir/../../../maintenance/generateSchemaSql.php" --json "$dir/$schema.json" --sql "$dir/$db/$schema.sql" --type="$db"
done
