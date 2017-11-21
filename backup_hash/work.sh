echo "Working..."
tar -cf ./abn_php.tar ../php
/usr/local/pgsql/bin/pg_dump -U local abn_en_mcn | gzip > ./abn_db.gz
echo "Done."
