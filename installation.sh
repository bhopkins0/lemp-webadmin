## General Installation
apt update && apt upgrade
apt install wget nginx mysql-server php8.1-fpm php-mysql php-common
clear

## MySQL Setup
PASSWD=$(sed "s/[^a-zA-Z0-9]//g" <<< $(cat /dev/urandom | tr -dc 'a-zA-Z0-9!@#$%*()-+' | fold -w 15 | head -n 1))"!"
ROOTPASSWD=$(sed "s/[^a-zA-Z0-9]//g" <<< $(cat /dev/urandom | tr -dc 'a-zA-Z0-9!@#$%*()-+' | fold -w 15 | head -n 1))"?"
mysql --execute "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password by '$ROOTPASSWD';"
export MYSQL_PWD="$ROOTPASSWD" 
mysql_secure_installation --password=$ROOTPASSWD --use-default
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
mkdir /var/www/nginxmanager
sed -i "s#root.*#root /var/www/nginxmanager;#" /etc/nginx/sites-enabled/$domain
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
" > /var/www/config.php

## More stuff here to prepare the webadmin install
ctime=$(date +%s)
mysql --execute "USE nginxmanager; INSERT INTO websites VALUES ('$domain', 'nginxmanager', '$ctime');";

## Clean up
rm mysqlsetup

## Install the webadmin
mkdir /var/www/nginxmanager/resources/
wget -O /var/www/nginxmanager/resources/functions.php https://raw.githubusercontent.com/bhopkins0/lemp-webadmin/main/resources/functions.php
wget -O /var/www/nginxmanager/resources/bootstrap.min.css https://raw.githubusercontent.com/bhopkins0/lemp-webadmin/main/resources/bootstrap.min.css
wget -O /var/www/nginxmanager/resources/bootstrap.bundle.min.js https://raw.githubusercontent.com/bhopkins0/lemp-webadmin/main/webadmin/home.php
wget -O /var/www/nginxmanager/installation.php https://raw.githubusercontent.com/bhopkins0/lemp-webadmin/main/webadmin/installation.php
wget -O /var/www/nginxmanager/index.php https://raw.githubusercontent.com/bhopkins0/lemp-webadmin/main/webadmin/index.php
wget -O /var/www/nginxmanager/home.php https://raw.githubusercontent.com/bhopkins0/lemp-webadmin/main/webadmin/home.php
wget -O /var/www/nginxmanager/sysinfo.php https://raw.githubusercontent.com/bhopkins0/lemp-webadmin/main/webadmin/sysinfo.php
chown -R www-data:www-data /var/www/nginxmanager

## End of installation
clear
echo -e "Your MySQL username is: nginxmanager \nYour MySQL password is: $PASSWD\nThis will be stored in the configuration file (/var/www/config.php)\nYou will need to go to http://$domain/installation.php";
