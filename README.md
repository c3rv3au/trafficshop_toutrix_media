This script connect to your TrafficShop account, get all the CPMs you are earning and update your TouTrix Media account with those CPMs.

TrafficShop is paying us few times per week since years. You don't have an account yet?
http://www.trafficshop.com/home/?aff=ADPornMedia

If you don't have a TouTrix account yet, get one here:
http://www.toutrix.com/media/

SETUP

change directory to your workspace

mkdir trafficshop && cd trafficshop

git clone https://github.com/c3rv3au/trafficshop_toutrix_media.git

mkdir classes && cd classes

git clone https://github.com/c3rv3au/toutrix_php


This script is using the Toutrix PHP API connector :

https://github.com/c3rv3au/toutrix_php

cd ..

edit run.php and update the variable at the beginning of the file.

Launch run.php, it will create one campaign at TouTrix Media and one flight per country. It only done one time. After, it will update your CPM each time your launch run.php.
