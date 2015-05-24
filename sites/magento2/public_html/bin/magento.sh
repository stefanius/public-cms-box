#!/usr/bin/env bash

sudo apt-get install mysql-server-5.6
sudo apt-get install php5-gd
sudo apt-get install php5-mcrypt
sudo apt-get install php5-intl
cd /home/vagrant/sites/magento2/public_html
composer install
export PATH=$PATH:/home/vagrant/sites/magento2/bin
echo "SHOW VARIABLES LIKE \"%version%\";" | mysql -uroot -ppassword
echo "create database magento2" | mysql -uroot -ppassword
cd /home/vagrant/sites/magento2/public_html/bin
php magento setup:install --db_password password --db_user root --admin_user admin --admin_password admin --admin_email admin@admin.nl --admin_firstname henkie --admin_lastname spenkie