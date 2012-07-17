aumBooster
==========

Script permettant de booster la popularité d'un compte sur le site de rencontre "Adopte un Mec".

Le fonctionnement est simple : le script va simuler la visite en série de tous les profils correspondant aux critères de recherche indiqués dans le fichier de configuration avec le compte utilisateur y figurant aussi.

Change les informations contenues dans le fichier aumBooster.yml

Lance en CLI :

    php aumBooster.php

Ou alors, lance un petit coup de :

    nohup php aumBooster.php &

avec un :

    tail -f nohup.out

derière et le script tourne en tâche de fond.

un :

    ps aux | grep aumBooster.php

Pour identifier le process et son id puis lui lancer un

    kill [pid]

si besoin.

Voilà c'était mon explication pour les (très) nuls.

Enjoy !
