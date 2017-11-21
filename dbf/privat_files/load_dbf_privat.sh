#!/bin/bash
##############################################################
##############################################################
###							   ###
###  Загрузка файлов DNK*.dbf и DNP*.dbf в базу PostgreSQL ###
###  2 параметра: код юзера и перенаправление ошибок       ###
###   							   ###
##############################################################
##############################################################

function dbf2pg()
{
#Для всех DNK*.dbf и DNP*.dbf файлов с текущего каталога...
IFS=$'\n' array=( $(ls | grep -i "^$1"'.*.dbf$') )

for rec in "${array[@]}"; do

let "c = ${#rec} - 4";
tab_name=`echo $rec | cut -c1-$c`;
path="logs";

if [ $3 -gt 0 ];then
#создаю лог ошибок к выбранным файлам
file1_name=$tab_name"_warnings.log";
file2_name=$tab_name"_errors.log";
date > $path"/"$file1_name;
date > $path"/"$file2_name;
fi

#iconv -f cp866 -t utf-8 $rec > $rec.tmp; 
#mv -f $rec.tmp $rec;

#Создаю постгресовский дамп из dbf-файла
pgdbf "$rec" >> $tab_name".dmp"

#меняю кодировку дампа с dos(866) на utf-8
iconv -f cp866 -t utf-8 $tab_name".dmp" -o $tab_name".dmp"

#Формирую текст запроса
zapros="select "$1"_privat_fun('"$tab_name"', "$2")"

#Загружаю дамп в базу, вставляю запрос в таблицу и удаляю файл дампа
if [ $3 -gt 0 ];then
"$5" "$4" < $tab_name".dmp" >> $path"/"$file1_name  2>> $path"/"$file2_name;
"$5" -d"$4" --single-transaction -c"$zapros" >> $path"/"$file1_name  2>> $path"/"$file2_name;
else
"$5" "$4" < $tab_name".dmp"; 
"$5" -d"$4" --single-transaction -c"$zapros";
fi
rm -f $tab_name".dmp"

zapros="";
done;
}

function test_vars()
{ 
#проверка переменных на существование и на число
if [ -z "$1" ]; then
 return 0;
else
test_v=`echo $1 | sed -e 's/[0-9]//g'`
  
  if [ $1 == $test_v ]; then 
    return 0;
  else
    return 1;
  fi  

fi
}

#Название базы
base_name="abn_en_mcn";
temp_file="_temp.tmp";

#Код пользователя (0 - загрузка выполняется автоматически с крона)
test_vars $1 1>$temp_file 2>$temp_file  
user_id=$?
#Перенаправление вывода ошибок в файлы (только, если $err_id > 0)
test_vars $2 1>$temp_file 2>$temp_file
err_id=$?
rm $temp_file;

ps_dir=$(find / -name 'psql' 2>>$temp_file | head -1 );
rm $temp_file;

if [ $user_id -gt 0 ]; then
dbf2pg "dnk" $1 $err_id $base_name $ps_dir
dbf2pg "dnp" $1 $err_id $base_name $ps_dir
else
dbf2pg "dnk" $user_id $err_id $base_name $ps_dir 
dbf2pg "dnp" $user_id $err_id $base_name $ps_dir
fi