## General Installation
apt update && apt upgrade
apt install wget nginx mysql-server php7.4-fpm php-mysql php-common
clear

### MySQL Setup
PASSWD=$(sed "s/[^a-zA-Z0-9]//g" <<< $(cat /dev/urandom | tr -dc 'a-zA-Z0-9!@#$%*()-+' | fold -w 15 | head -n 1))"!"
mysql_secure_installation
wget -O mysqlsetup https://raw.githubusercontent.com/bhopkins0/lemp-webadmin/main/mysqlsetup
clear
mysql --execute "CREATE DATABASE nginxmanager;"
mysql --execute "CREATE USER 'nginxmanager'@'localhost' IDENTIFIED BY '$PASSWD';"
mysql --execute "GRANT ALL PRIVILEGES ON nginxmanager.* TO 'nginxmanager'@'localhost';"
mysql --execute "FLUSH PRIVILEGES;";
mysql --execute "USE nginxmanager; source mysqlsetup;";
echo -e "Your MySQL username is: nginxmanager \nYour MySQL password is: $PASSWD";
