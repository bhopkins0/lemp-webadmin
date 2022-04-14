## General Installation
apt update && apt upgrade
apt install wget nginx mysql-server php7.4-fpm php-mysql php-common
clear

## MySQL Setup
PASSWD=$(sed "s/[^a-zA-Z0-9]//g" <<< $(cat /dev/urandom | tr -dc 'a-zA-Z0-9!@#$%*()-+' | fold -w 15 | head -n 1))"!"
mysql_secure_installation
wget -O mysqlsetup https://raw.githubusercontent.com/bhopkins0/lemp-webadmin/main/mysqlsetup
clear
mysql --execute "CREATE DATABASE nginxmanager;"
mysql --execute "CREATE USER 'nginxmanager'@'localhost' IDENTIFIED BY '$PASSWD';"
mysql --execute "GRANT ALL PRIVILEGES ON nginxmanager.* TO 'nginxmanager'@'localhost';"
mysql --execute "FLUSH PRIVILEGES;";
mysql --execute "USE nginxmanager; source mysqlsetup;";

## nginx Setup
sed -i "s/# server_tokens.*/server_tokens off;/" /etc/nginx/nginx.conf
wget -O /etc/nginx/sites-enabled/default https://raw.githubusercontent.com/bhopkins0/lemp-webadmin/main/default
rm /var/www/html/index.nginx-debian.html
clear
echo "What domain will the manager be hosted on? (ex: manager.example.com)"
read domain
wget -O /etc/nginx/sites-enabled/$domain https://raw.githubusercontent.com/bhopkins0/lemp-webadmin/main/example_nginxconf
mkdir /var/www/$domain
sed -i "s#root.*#root /var/www/$domain;#" /etc/nginx/sites-enabled/$domain
sed -i "s/server_name.*/server_name $domain;/" /etc/nginx/sites-enabled/$domain
service nginx restart

## Config File Setup
phpversion=$(php -v | grep "(cli)")
mysqlversion=$(mysql --version)
nginxversion=$(nginx -v 2>&1)
managerversion="beta"

echo "<?php
\$dbservername = 'localhost';
\$dbusername = 'nginxmanager';
\$dbpassword = '$PASSWD';
\$dbname = 'nginxmanager';
\$phpversion = '$phpversion';
\$mysqlversion = '$mysqlversion';
\$nginxversion = '$nginxversion';
\$managerversion = 'beta';

if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
  $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}" > /var/www/config.php

## More stuff here to prepare the webadmin install



## Install the webadmin



## End of installation
echo -e "Your MySQL username is: nginxmanager \nYour MySQL password is: $PASSWD\nThis will be stored in the configuration file (/var/www/config.php)";
