<?
require "trafficshop.php";

// Change the value of the fields below
define('trafficshop_username','CHANGE ME');
define('trafficshop_password','CHANGE ME');
define('trafficshop_domain_sel',12345); // Change this too
define('trafficshop_url',"http://go.trafficshop.com/51334643"); // Change URL for your own url

define("ENABLED",false);

if (!ENABLED)
  die("Read the script file first.\n");

// Only do once
if (!fileexist("trafficshop.dat"))
  setup();

get_cpm();

