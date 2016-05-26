:city_sunset: imagely is a small gallery system written in pure php in association with javascript (jQuery) and MySQL
# Installation #
```
cd /var/www/html/
git clone git@github.com:LazuLitee/imagely.git
cd /imagely
find ./ -type d -exec chmod 755 {} +
find ./ -type f -exec chmod 644 {} +
chmod 777 ./data/media/
chmod 766 ./data/media/*
mysql -u root -p imagely < ./data/database/imagely.sql
```
# Login credentials #
## Admin ##
```
E-Mail: admin@buendig.net
Password: abcABC123!
```
## User ##
```
E-Mail: user@buendig.net
Password: abcABC123!
```