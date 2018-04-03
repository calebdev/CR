# Instalation
Lancer la commande : composer install
# Dependence
- Intaller rabbitmq
- Configurer et Lancer le serveur rabbitmq
# Apres l'installation
- Lancer la commande : php rabbitmq/receiver.php
# Dossier public
/public/
# Config
- Configurer votre apache server :
<code>
<VirtualHost *:80>
    ServerName test-esokia.loc
    DocumentRoot "/home/esokia/work/www/test-esokia.loc/public"
    SetEnv APPLICATION_ENV "development"
    <Directory "/home/esokia/work/www/test-esokia.loc/public">
        Options +FollowSymLinks +Indexes
        AllowOverride All
	RewriteEngine On
	Require all granted
    </Directory>
</VirtualHost>
</code>
- Rendez vous a l'ulr : http://test-esokia.loc/

### Thanks!


