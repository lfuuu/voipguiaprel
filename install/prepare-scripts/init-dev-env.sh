set -e
cd /workspace

if ! [ -f /workspace/.configprepare ]; then
echo "0. Готовим конфиги"

cp /root/.pgpass_b /root/.pgpass && chmod 600 /root/.pgpass && touch /workspace/.configprepare
fi

if ! [ -f /workspace/.gitclone ]; then
echo "1. Разворачиваем репозиторий"
git clone git@github.com:welltime/voip_gui.git && touch /workspace/.gitclone
fi

if ! [ -f /workspace/.srctune ]; then
echo "2. Тюнинг кода"
cd /workspace/voip_gui/config
cp db-local.tpl.php db-local.php
cp log-local.tpl.php log-local.php
cp params-local.tpl.php params-local.php
chown -R www-data:www-data /opt/voip_gui/web
chmod 777 /workspace/voip_gui/runtime
chmod 777 /workspace/voip_gui/web/assets
sed -i "s/host=localhost;dbname=nispd/host=$POSTGRES_HOST;dbname=$POSTGRES_DB/" db-local.php
sed -i "s/'username' => ''/'username' => '$POSTGRES_USER'/" db-local.php
sed -i "s/'password' => ''/'password' => '$POSTGRES_PASSWORD'/" db-local.php
touch /workspace/.srctune
fi

if ! [ -f /workspace/.composer ]; then
echo "3. Устанавливаем композер и зависимости."
#сurl -sS https://getcomposer.org/installer | php
#mv composer.phar /usr/local/bin/composer 
#composer global require "fxp/composer-asset-plugin"
cd /workspace/voip_gui
composer install
touch /workspace/.composer
fi

echo "### Для разворота база используйте скрипт /workspace/voip_gui/install/db/init_test_db.sh"

rm -rf /opt/voip_gui
ln -s /workspace/voip_gui /opt/voip_gui

