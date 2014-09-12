#WindServer2#

#Installer WindServer2#
Lancer la commande:
'git clone git://github.com/lapoiz/WindServer2.git'

Ce qui va vous créer un repertoire avec le code source dedans

Aller dans le repertoire WindServer2
Et installer Composer:
'curl -s http://getcomposer.org/installer | php'

Lancer composer:
'php composer.phar update'

Check si tout est ok:
'php app/check.php'

Et sur le navigateur:
<http://localhost/WindServer2/web/config.php>

Créer la Base de Donnée, créer les table, et la remplir d'un jeu de test
'php app/console doctrine:database:create'
'php app/console doctrine:schema:update --force'
'php app/console doctrine:fixtures:load --fixtures=src/LaPoiz/WindBundle/DataFixture/'

Mettre les liens pour la mise à jour
'php app/console assets:install web/ --symlink'

Tester
<http://localhost/WindServer2/web/app_dev.php/index>