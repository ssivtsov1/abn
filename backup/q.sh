rm -f backup/done.log
rm -f backup/abn.dmp.gz
/usr/local/pgsql/bin/pg_dump -U local abn_en_mcn | gzip > ./backup/abn.dmp.gz && echo "done" > ./backup/done.log