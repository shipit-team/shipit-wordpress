# SHIPIT-WORDPRESS
## Desription

The following project is a plugin for WordPress that interacts with the WooCommerce plugin to generate pricing, import sales, and update order statuses. To test in local, we must install a WordPress instance.

## Requirements


| Technology | Version |
| ------ | ------ |
| Apache | Last |
| Mysql/Mariadb | Last |
| PHP| Last |
| Wordpress| Last |
| WooCommerce | Last |

## Install Lampp

### Install Apache 
```sh
#sudo apt update
#sudo apt install apache2
```

### Install Mysql
```sh
#sudo apt install mysql-server
#sudo mysql_secure_installation
```

### Install PHP
```sh
#sudo apt install php libapache2-mod-php php-mysql
```
## Setting Environment

### create database
```sh
#mysql -u root -p
mysql>CREATE DATABASE wordpress DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
mysql>CREATE USER 'wordpressuser'@'%' IDENTIFIED WITH mysql_native_password BY 'password';
mysql>FLUSH PRIVILEGES;
mysql>exit;
```

### Install PHP extensions
```sh
#sudo apt update
#sudo apt install php-curl php-gd php-mbstring php-xml php-xmlrpc php-soap php-intl php-zip
#sudo systemctl restart apache2
```
### Setting Apache
```sh
#sudo nano /etc/apache2/sites-available/wordpress.conf
<Directory /var/www/htnl/wordpress/>
    AllowOverride All
</Directory>
```

### Enable ModRewrite
```sh
#sudo a2enmod rewrite
#sudo systemctl restart apache2
```

##Download and Install Wordpress
```sh
#cd /tmp
#curl -O https://wordpress.org/latest.tar.gz
#tar xzvf latest.tar.gz
#touch /tmp/wordpress/.htaccess
#cp /tmp/wordpress/wp-config-sample.php /tmp/wordpress/wp-config.php
#mkdir /tmp/wordpress/wp-content/upgrade
#sudo cp -a /tmp/wordpress/. /var/www/html/wordpress

###update folder owner
#sudo chown -R www-data:www-data /var/www/html/wordpress
#sudo find /var/www/wordpress/ -type d -exec chmod 750 {} \;
#sudo find /var/www/wordpress/ -type f -exec chmod 640 {} \;
```

### Get security values from Wordpress key generator
```sh
#curl -s https://api.wordpress.org/secret-key/1.1/salt/
```

### expected output
```sh
define('AUTH_KEY',         '1jl/vqfs<XhdXoAPz9 DO NOT COPY THESE VALUES c_j{iwqD^<+c9.k<J@4H');
define('SECURE_AUTH_KEY',  'E2N-h2]Dcvp+aS/p7X DO NOT COPY THESE VALUES {Ka(f;rv?Pxf})CgLi-3');
define('LOGGED_IN_KEY',    'W(50,{W^,OPB%PB<JF DO NOT COPY THESE VALUES 2;y&,2m%3]R6DUth[;88');
define('NONCE_KEY',        'll,4UC)7ua+8<!4VM+ DO NOT COPY THESE VALUES #`DXF+[$atzM7 o^-C7g');
define('AUTH_SALT',        'koMrurzOA+|L_lG}kf DO NOT COPY THESE VALUES  07VC*Lj*lD&?3w!BT#-');
define('SECURE_AUTH_SALT', 'p32*p,]z%LZ+pAu:VY DO NOT COPY THESE VALUES C-?y+K0DK_+F|0h{!_xY');
define('LOGGED_IN_SALT',   'i^/G2W7!-1H2OQ+t$3 DO NOT COPY THESE VALUES t6**bRVFSD[Hi])-qS`|');
define('NONCE_SALT',       'Q6]U:K?j4L%Z]}h^q7 DO NOT COPY THESE VALUES 1% ^qUswWgn+6&xqHN&%');
```
### replace in
```sh
#sudo nano /var/www/wordpress/wp-config.php
```

### complete installation in
https://server_domain_or_IP

## Install Woocommerce
- Go to: Plugins > Add New.
- Search for “WooCommerce”.
- Click Install Now.
- Click Activate Now and you’re ready for the WooCommerce Wizard.

## Install Shipit in Wordpress
[How install shipit](https://shipitcl.zendesk.com/hc/es-419/articles/360016135074--C%C3%B3mo-integrar-mi-tienda-de-WooCommerce-con-Shipit-)

## Others necessary products

| Product | port |
| ------ | ------ |
| core | 3000 |
| orders | 8000 |
| opit| 6000 |