/usr/local/pgsql/bin/dropdb -U postgres abn_test_db
/usr/local/pgsql/bin/createdb -U postgres -E UTF8 abn_test_db
gunzip -c ./abn.dmp.gz | /usr/local/pgsql/bin/psql -U postgres abn_test_db
