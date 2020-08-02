#!/bin/sh

THIS=`readlink -f "${BASH_SOURCE[0]}"`
DIR=`dirname "${THIS}"`

# Если указать адрес PgHostname - разворачиваться будет по нем, если адреса нет - то коннект будет с локальным Постгресом через SOCKS
PgHostname=db
PgPort=5432

NAME_DB_TEST_MAIN=nispd_test
NAME_DB_TEST_REGIONAL='nispd$NUM_test'

HOST_DB_MAIN=eridanus.mcn.ru
NAME_DB_MAIN=nispd
PORT_DB_MAIN=5432

#HOST_DB_MAIN=127.0.0.1
#NAME_DB_MAIN=nispd
#PORT_DB_MAIN=15432

# Пользователь в тестовом окружении
psqluser=postgres
# Пользователь на бою
DB_USER=pgsqltest

PGBIN=psql

DB_INIT_SQL=$DIR/SQL/init-db.sql
DB_DUMP_SCHEMA_MAIN=$DIR/SQL/NISPD_CENTRAL_DB.schema.sql
DB_DUMP_DATA_MAIN_WO_CALLS=$DIR/SQL/NISPD_CENTRAL_DB.data_wo_calls_data.sql
DB_DUMP_SCHEMA_MAIN_CALLS=$DIR/SQL/NISPD_CENTRAL_DB.calls_schema.sql  

testConnect() {
    if [[ ! -z $PgHostname ]]; then
        if [[ -z $psqluser ]]; then
            echo "Не указано имя пользователя для доступа к БД ${DbName} на ${PgHostname}"
            exit 1
        fi
        PSQLOPTS="-h $PgHostname -U $psqluser"
        PgDSN="pgsql:dbname=${DbName};user=${PgUsername};host=${PgHostname};port={$PgPort};password=${PGPASSWORD}"
    else
        PSQLOPTS="-U $psqluser"
        PgDSN="pgsql:dbname=${DbName};user=postgres;"
    fi
    echo "test connect"

    PSQL="psql $PSQLOPTS -d $DbName"
    echo "Проверяем имеем ли доступ..."

    echo "PSQLOPTS:$PSQLOPTS"

    psql $PSQLOPTS -d postgres -c "select 1"  1> /dev/null
    res=$?
    if [[ $res -gt 0 ]]; then
        echo "Ошибка! Нет доступа у юзера $psqluser"
        exit 127
    else
        echo "Доступ есть."
    fi
}

makeSQLDumpFromProd() {
    echo "#### makeSQLDumpFromProd: Снимаем минимальный дамп с центральной и региональной боевой базы"

    echo "]]] 2. Дампим данные ЦЕНТРАЛЬНОЙ БД без схем  calls_raw,calls_aggr,calls_cdr,calls_raw_cache,nnp_ported. сервер [$HOST_DB_MAIN],база [$NAME_DB_MAIN]"
    pg_dump -a -n auth -n billing -n billing_api -n billing_uu -n event -n geo -n mtt_billing -n nnp -n public -n tests --disable-triggers -O -h $HOST_DB_MAIN -U $DB_USER -p $PORT_DB_MAIN $NAME_DB_MAIN > $DIR/SQL/NISPD_CENTRAL_DB.data_wo_calls_data.sql
    pg_dump -a -t vpbx.billing_did_location -t voip.pricelist --disable-triggers -O -h $HOST_DB_MAIN -U $DB_USER -p $PORT_DB_MAIN $NAME_DB_MAIN >> $DIR/SQL/NISPD_CENTRAL_DB.data_wo_calls_data.sql

    # Центральная база
    echo "]]] 3. Дампим cхему центральной БД. сервер [$HOST_DB_MAIN],база [$NAME_DB_MAIN]"
    pg_dump -c -s -N sorm_itgrad --if-exists -h $HOST_DB_MAIN -p $PORT_DB_MAIN -U $DB_USER $NAME_DB_MAIN > $DIR/SQL/NISPD_CENTRAL_DB.schema.sql

    # Схему с calls'ами докатываем.
    echo "]]] 4. Дампим схемы calls_raw,calls_cdr и calls_aggr центральной БД без данных. сервер [$HOST_DB_MAIN],база [$NAME_DB_MAIN]"
    pg_dump -s -n calls_raw -n calls_aggr -n calls_cdr -n nnp_ported -p $PORT_DB_MAIN -h $HOST_DB_MAIN -U $DB_USER $NAME_DB_MAIN > $DIR/SQL/NISPD_CENTRAL_DB.calls_schema.sql
}

createCentralNISPD() {
    echo "#### createCentralNISPD: Формируем тестовую центральную базу."
    echo "]]] 1. Удаляем центральную БД"
    dropdb $PSQLOPTS --if-exists $NAME_DB_TEST_MAIN || ( echo "ОШИБКА: Не получилось удалить базу $NAME_DB_TEST_MAIN на тестовом сервере. Выходим" && exit 1 )

    echo "]]] 2. Создаём пустую центральную БД"
    createdb $PSQLOPTS $NAME_DB_TEST_MAIN || ( echo "ОШИБКА: Не получилось создать пустую базу $NAME_DB_TEST_MAIN на тестовом сервере. Выходим"; exit 1 )

    echo "]]] 3. Вручную удаляем тип  dblink_pkey_results"
    psql $PSQLOPTS -d $NAME_DB_TEST_MAIN -c "drop type if exists dblink_pkey_results"

    echo "]]] 4. Создаем схему в центральной базе из [$DB_DUMP_SCHEMA_MAIN]"
    psql $PSQLOPTS -d $NAME_DB_TEST_MAIN < $DB_DUMP_SCHEMA_MAIN || exit 1

    echo "]]] 5. Загружаем тестовые данные в базу из [$DB_DUMP_DATA_MAIN_WO_CALLS]"
    psql $PSQLOPTS -d $NAME_DB_TEST_MAIN < $DB_DUMP_DATA_MAIN_WO_CALLS 

    echo "]]] 6. Загружаем схему calls из [$DB_DUMP_SCHEMA_MAIN_CALLS]"
    psql $PSQLOPTS -d $NAME_DB_TEST_MAIN < $DB_DUMP_SCHEMA_MAIN_CALLS
}

testConnect

initDB() {
    psql $PSQLOPTS -d postgres < $DB_INIT_SQL || exit 1
}

case "$1" in
        initDB)						initDB;;
        makeSQLDumpFromProd)		makeSQLDumpFromProd;;
        createCentralNISPD)		    createCentralNISPD;;
        *)		echo "usage { makeSQLDumpFromProd | createCentralNISPD | createRegionalNISPD {RegNum} | transferTableFromRegion {RegNum} {TableName}| DropALLDB }";;
esac

