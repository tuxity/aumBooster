aumBooster
==========

Script permettant de booster la popularité d'un compte sur le site de rencontre "Adopte un Mec".

Le fonctionnement est simple : le script va simuler la visite en série de tous les profils correspondant aux critères de recherche indiqués dans le fichier de configuration avec le compte utilisateur y figurant aussi.

Pour les utilisateurs Windows :
-------------------------------

Installer php comme suit : Télécharger le Zip de la dernière version stable de PHP sur http://windows.php.net/download/

Décompresser le contenu de ce fichier zip dans le répertoire C:\php de façon à ce que le fichier php.exe soit accessible par le chemin c:\php\php.exe

Cliquer droit sur le "Poste de travail", puis sélectionner Propriétés.

Choisir l'onglet "Avancé", puis cliquer sur le bouton "Variables d'environnement".

Dans le deuxième tableau, celui du bas, sélectionner "Path", puis cliquer sur "Modifier".

Sans rien modifier de la chaine de caractères "Valeur de la variable", ajouter à la fin ";c:\php" puis cliquer sur "OK".

Cliquer sur "Démarrer" puis "Exécuter", saisir cmd puis appuyer sur entrée.

Une fenêtre de commande (noire) s'ouvre, saisir "c:\php" puis entrée.

Saisir "copy php.ini-development php.ini" puis entrée

Ouvrir le fichier ainsi créé qui se trouve sur c:\php\php.ini (avec l'explorateur de fichiers).

Trouver la ligne :
;extension=php_curl.dll
et la remplacer par :
extension=ext/php_curl.dll

Sauvegarder et fermer le fichier php.ini.

Vous voici prêt à exécuter l'aumBooster sur votre machine sous windows.

Télécharger l'archive de l'aumBooster en cliquant sur le bouton Zip en haut de cette page.

Décompresser le contenu de cette archive dans le répertoire c:\aumBooster de façon à ce que le fichier aumBooster.php soit accessible par le chemin c:\aumBooster\aumBooster.php

Dans la fenêtre de commande ouverte précédemment, saisir "c:\aumBooster.php".

Changer les informations contenues dans le fichier aumBooster.yml en lisant les insctructions en commentaire.

Puis dans la fenêtre de commande saisir "php aumBooster.php" puis entrée.

Pour les utilisateurs Linux (je résume parce qu'ils doivent savoir se débrouiller ;) ) :
----------------------------------------------------------------------------------------

Change les informations contenues dans le fichier aumBooster.yml en lisant les insctructions en commentaire

Lance en CLI :

    php aumBooster.php

Ou alors, afin de ne pas avoir besoin de conserver le terminal ouvert, lance un petit coup de :

    nohup php aumBooster.php &

avec, afin de suivre la sortie, mais ce n'est pas obligatoire, un :

    tail -f nohup.out

derière et le script tourne en tâche de fond.

un :

    ps aux | grep aumBooster.php

Pour identifier le process et son id puis lui lancer un

    kill [pid]

si besoin.

Conclusion :
------------

Voilà c'était mon explication pour les (très) nuls.

Enjoy !

