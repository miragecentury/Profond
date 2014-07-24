sudo apt-get install apache2 php openssl php5-xdebug libssh2-php
sudo nano /etc/php5/apache2/php.ini
sudo service apache2 restart
sudo cp ./profond.local.conf /etc/apache2/site-enabled/
sudo nano /etc/apache2/site-enabled/profond.local.conf
sudo nano /etc/hosts