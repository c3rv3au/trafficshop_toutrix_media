<?
// Fetch datas on TrafficShop and send the datas to AdServer API, only for desktop

// Change the value of the fields below

define('trafficshop_username','CHANGE ME');
define('trafficshop_password','CHANGE ME');
define('trafficshop_domain_sel',12345); // Change this too
define('trafficshop_url',"http://go.trafficshop.com/51334643"); // Change URL for your own url

define("ENABLED",false);

// -- STOP EDITING FROM HERE

if (!ENABLED)
  die("Read the script file first.\n");

require('common.php');
require('countries.php');

set_time_limit(0);
ignore_user_abort(true);

define(minimum_profit_per,0.95);

function get_cpm() {
  global $_countries;
  global $adserver;

  require("trafficshop.dat");

  global $ch;
  $ch = curl_init();
  $arr = array();

  array_push($arr, 'Accept-Language: en-us,en;q=0.5');
  array_push($arr, 'Accept-Encoding=gzip,deflate');
  array_push($arr, 'Accept-Charset=ISO-8859-1,utf-8;q=0.7,*;q=0.7');
  array_push($arr, 'Connection: keep-alive');
  curl_setopt($ch, CURLOPT_HTTPHEADER, $arr);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  curl_setopt($ch, CURLOPT_TIMEOUT_MS, 10000);

  $action = "https://www.trafficshop.com/home/";
  echo $action . "\n";
  $source = get_content($action);

  if (strpos($source,'403 Forbidden')!==false) {
    echo "403 forbidden error<br/>";
    return;
  }

  $ts_countries = file_get_contents('trafficshop_countries.txt');

  $search = "name=\"r\" value=\"";
  $pos_to = strpos($source,$search);
  $source = substr($source,$pos_to+strlen($search),strlen($source)-$pos_to-strlen($search));

  $search = "\"";
  $pos_to = strpos($source,$search);
  $data_r = substr($source,0,$pos_to);

  $action = "https://www.trafficshop.com/";
  $datas = "LOG_IN=1&r=" . $data_r . "&login=" . trafficshop_username . "&password=" . trafficshop_password . "&secure=on";
  $source = get_content($action,$datas);

  $action = "https://www.trafficshop.com/publishers/selling_traffic/skimmed/?type=7";
  $source = get_content($action);

  $donnee = array();
  $donnee['action'] = "https://www.trafficshop.com/publishers/selling_traffic/skimmed/";
  $donnee['datas'] = "period=1&SD=" . date('d',time()+21600) . "&SM=" . date('m',time()+21600) .  "&SY=" . date('Y',time()+21600) . 
"&ED=" . date('d',time()+21600) . "&EM=" . date('m',time()+21600) .  "&EY=" . date('Y',time()+21600) . 
"&sel_stat_type=2&x=57&y=11&submit=submit&domain_sel[]=" . trafficshop_domain_sel;

  foreach ($_countries as $country_code => $country) {
    echo "Find " . $country_code . "\n";

    foreach(preg_split("/((\r?\n)|(\r\n?))/", $ts_countries) as $line){
      if (strpos($line,'>'.$country_code)>0) {
        $good_line = $line;
        break;
      }
    }
    $pos = strpos($line,'"');
    $good_line = substr($line,$pos+1,strlen($line)-$pos-1);
    $pos = strpos($good_line,'"');
    $good_line = substr($good_line,0,$pos);

    if (strlen($good_line)>0) {
      echo $good_line . "\n";

      $action = $donnee['action'];
      $datas = $donnee['datas'] . "&country=" . $good_line;
      echo $datas . "\n";

      $source = get_content($action,$datas);

      $search = "<tr class=\"s\">";
      $pos_to = strpos($source,$search);
      $source = substr($source,$pos_to,strlen($source)-$pos_to);

      $search = "</table>";
      $pos_to = strpos($source,$search);
      $source = substr($source,0,$pos_to);

      $i = 0;
      while (strlen($source)>0) {
        $search = "</tr>";
        $pos_to = strpos($source,$search);
        $phrase = substr($source,0,$pos_to);
        $source = substr($source,$pos_to + 4, strlen($source)-$pos_to-4);

        preg_match_all('#<td(.*?)</td>#', $phrase, $tds);
        if (count($tds)>0 and count($tds[1])>2){
          $quantity = $tds[1][1];
          $quantity = substr($quantity,18,strlen($quantity)-18);

	  echo "Quantity: " . $quantity . "\n";

          $cpm = $tds[1][4];
          $cpm = substr($cpm,19,strlen($cpm)-19);       $force = false;

          echo "CPM: " . $cpm . "\n";

          $perc = $tds[1][3];
          $perc = substr($perc,18,strlen($perc)-19);
          echo "Per sold: " . $perc . "\n";

          if ($quantity >= 3 and $perc < 60)
            echo "Very low sold.\n";

          if ($quantity >= 10 or $cpm > 1.25) {
            if (strlen($country_code)>1) {
              if ($country_code == 'Ot') {
              }
              if ($cpm > 6)
                $cpm = 6;
              $perc = $perc/100;

              $cpm = $cpm * $perc;

              // TODO - Update flight at the AdServer
              echo "Update Adserver\n";
              $flight = new stdClass();
              $flight->id = $traffichop_datas[$country_code];
              $flight->Price = $cpm;

              $flight = $adserver->flight_update($flight);
              var_dump($flight);
            }
          } else { // Si bonne quantite
            echo "Not enough hit to update\n";
          }  
        }
        $source = '';

        if ($i>500)
         break; // I think there is a real bug
      } // While Source
    } else {
      // TODO - Send an alert to the admin, this country is not found. We should hack this code.
    } // If country found

    //die("Do one for the moment");
  } // For Each Countries
}

function setup() {
  global $adserver;
  global $_countries;

  $datas = array();

  $campaign = new stdClass();
  $campaign->name = "TrafficShop";
  $campaign->isDeleted = false;
  $campaign->isActive = true;

  $campaign = $adserver->campaign_create($campaign);
  var_dump($campaign);
  $campaignId = $campaign->id;

  //$campaignId = 18;
  echo "Campaign ID: " . $campaignId . "\n";

  // Creative
  $creative = new stdClass();
  $creative->adtypeId = 1; // Popunder
  $creative->title = "TrafficShop";
  $creative->url = trafficshop_url;
  $creative->IsDeleted = false;
  $creative->IsActive = true;

  $creative = $adserver->creative_create($creative);
  var_dump($creative);
  $creativeId = $creative->id;

  //$creativeId = 3;
  echo "Creative ID: " . $creativeId . "\n";

  // Now add a target to this flight to the good channel. All flight herit from this target
  $target = new stdClass();
  $target->campaignId = $campaignId;
  $target->isExcept = false;
  $target->target_type = "channelId";
  $target->target_value = "[" . channel_adult . "]";
  $target = $adserver->target_create($target);
  //var_dump($target);

  foreach ($_countries as $country_code => $country) {
    echo $country_code . "=" . $country . "\n";
    $flight = new stdClass();
    $flight->campaignId = $campaignId;
    $flight->NoEndDate = true;
    $flight->Price = 0.1;
    $flight->IsUnlimited = true;
    $flight->Name = "TS " . $country_code;
    $flight->MaxPerIp = 1;
    $flight->IsActive = true;
    $flight = $adserver->flight_create($flight);
    var_dump($flight);
    echo "New flight ID: " . $flight->id . "\n";

    // Now associate the creative to this flight
    $cf = new stdClass();
    $cf->campaignId = $campaignId;
    $cf->flightId = $flight->id;
    $cf->creativeId = $creativeId;
    $cf->IsActive = true;
    $cf = $adserver->creative_flight_create($cf);

    // Now add a target to this flight to the good country
    $target = new stdClass();
    $target->flightId = $flight->id;
    $target->isExcept = false;
    $target->target_type = "country";
    $target->target_value = "[\"" . $country_code . "\"]";
    $target = $adserver->target_create($target);
    echo "New target\n";
    var_dump($target);

    $datas[$country_code] = $flight->id;    

    //die("Do only one for the moment");
  }
  create_config($datas);
}

function create_config($datas) {
  $fp = fopen('trafficshop.dat', 'w');
  fwrite($fp, '<' . '? $traffichop_datas = array(');
  foreach ($datas as $country_code => $flight_id) {
    fwrite($fp, "'" . $country_code . "'=>" . $flight_id . ',');
  }
  fwrite($fp, ');');
  fclose($fp); 
}

function get_content($URL,$post_datas = null, $referer=null){
  global $ch;

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  $arr = array();
  array_push($arr, 'Accept-Language: en-us,en;q=0.5');
  array_push($arr, 'Accept-Encoding=gzip,deflate');
  array_push($arr, 'Accept-Charset=ISO-8859-1,utf-8;q=0.7,*;q=0.7');
  array_push($arr, 'Connection: keep-alive');


  curl_setopt($ch, CURLOPT_HTTPHEADER, $arr);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13'); 

  curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/my_cookie.txt'); 
  curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/my_cookie.txt'); 
  curl_setopt($ch, CURLOPT_URL, $URL);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  if ($referer<> null) {
    curl_setopt($ch, CURLOPT_REFERER, $referer);
  }
  if ($post_datas <> null) {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_datas);
  }
  $data = curl_exec($ch);
  return $data;
}
