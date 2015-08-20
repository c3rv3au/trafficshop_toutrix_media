<?
require "classes/toutrix_php/api_toutrix.php";
require "trafficshop.php";

// Change the value of the fields below
define('trafficshop_username','CHANGE ME');
define('trafficshop_password','CHANGE ME');

// You can find your domain_sel in the source page here:
// https://www.trafficshop.com/publishers/selling_traffic/skimmed/?type=7
// Find for name='domain_sel[]' and check for value
define('trafficshop_domain_sel',12345); // Change this too

// Change URL for your own url. Check at: https://www.trafficshop.com/publishers/settings/
define('trafficshop_url',"http://go.trafficshop.com/51334643"); 

define("toutrix_username",'CHANGE ME');
define("toutrix_password",'CHANGE ME');

// Change this to true
define("ENABLED",false);

if (!ENABLED)
  die("Read the script file first.\n");

global $adserver;
$adserver = new api_toutrix_adserver();

$connected = $adserver->login("egrenier","celibat123");
if ($connected) {
  echo "Connected to the API\n";
} else {
  die("Not connected to the API\n");
}

// Only do once
if (!fileexist("trafficshop.dat"))
  setup($adserver);

get_cpm($adserver);

