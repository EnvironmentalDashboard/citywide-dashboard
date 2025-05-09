<?php
error_reporting(-1);
ini_set('display_errors', 'On');
/**
 * This page uses the BuildingOS API to display live readings in a dynamically generated SVG
 * Its dependancies are jQuery and GSAP, which are both included at the bottom of this file
 *
 * @author Tim Robert-Fitzgerald June 2016
 */
//$log = array(); // For debugging purposes. Remove when code is in production
require '../includes/db.php'; // The connection to the MySQL data is stored in here, which is a dynamically generated file written by install.php
require '../includes/class.Meter.php'; // Some animations depend on the reading of a meter
// require 'includes/analytics.php';

header('Content-Type: image/svg+xml');

function dd($data)
{
  echo "<pre>";
  print_r($data);
}
/* fixing the array of gauges as of now leter on we'll fetch it from api */
$gauges = array();

// Array containing the URLs of the guages to be displayed on the right sidebar for each button
$num_btns = 0; // Used to calculate x position of the buttons

// Builds a gauge URL
$gaugesList = [
  'electricity' => [
    'gauge1' => 1021,
    'gauge2' => 1020,
    'gauge3' => 1019,
    'gauge4' => 1018
  ],
  'water' => [
    'gauge1' => 1025,
    'gauge2' => 1024,
    'gauge3' => 1023,
    'gauge4' => 1022
  ],
  'stream' => [
    'gauge1' => 1029,
    'gauge2' => 1028,
    'gauge3' => 1027,
    'gauge4' => 1026
  ],
  'weather' => [
    'gauge1' => 1033,
    'gauge2' => 1032,
    'gauge3' => 1031,
    'gauge4' => 1030
  ],
  // 'gas' => [
  //   'gauge1' => null,
  //   'gauge2' => null,
  //   'gauge3' => null,
  //   'gauge4' => null
  // ],
  'landing' => [
    'gauge1' => null,
    'gauge2' => null,
    'gauge3' => null,
    'gauge4' => null
  ]
];
foreach ($gaugesList as $resource => $resourceList) {
  if (!isset($gauges[$resource])) {
    $gauges[$resource] = [];
  }
  foreach ($resourceList as $key => $gaugeID) {
    $gauges[$resource][$key] = dataHubGaugeURL($gaugeID);
  }
}

/**
 * set the guage count, otherwsie it was setting in a for loop while fetching data from cwd_states - 
 * later on we'll completely remove dependancy from environment dashboard
 */
$num_btns = count($gauges);
// print_r($gauges);
// exit;
function gaugeURL($rv_id, $meter_id, $color, $bg, $height, $width, $font_family, $title, $title2, $border_radius, $rounding, $ver, $units, $title_font_size = 24)
{
  $q = http_build_query([
    'rv_id' => $rv_id,
    'meter_id' => $meter_id,
    'color' => $color,
    'bg' => $bg,
    'height' => $height,
    'width' => $width,
    'font_family' => $font_family,
    'title' => $title,
    'title2' => $title2,
    'border_radius' => $border_radius,
    'rounding' => $rounding,
    'ver' => $ver,
    'units' => $units,
    'title_font_size' => $title_font_size
  ]);
  return "https://environmentaldashboard.org/gauges/gauge.php?" . $q;
}
function dataHubGaugeURL($gaugeID)
{
  return "https://oberlin.communityhub.cloud/api/data-hub-v2/visualizations/gauges/$gaugeID";
}

function relativeValueOfGauge($db, $gauge_id, $min = 0, $max = 100)
{
  // 'SELECT relative_value FROM relative_values WHERE meter_uuid IN (SELECT bos_uuid FROM meters WHERE meters.id = ?) LIMIT 1'
  $stmt = $db->prepare('SELECT meter_id FROM gauges WHERE id = ?');
  $stmt->execute(array($gauge_id));
  $meter_id = $stmt->fetchColumn();
  $stmt = $db->prepare('SELECT relative_value FROM relative_values WHERE meter_uuid IN (SELECT bos_uuid FROM meters WHERE meters.id = ?) LIMIT 1');
  $stmt->execute(array($meter_id));
  return ($stmt->fetchColumn() / 100) * ($max - $min) + $min;
}

// ------------------------
// SELECT the links to the gauges and messages to be shown with each state
// foreach ($db->query("SELECT * FROM cwd_states WHERE user_id = {$user_id} AND `on` = 1") as $row) {
//   $gauge1_data = $db->query('SELECT * FROM gauges WHERE id = \'' . $row['gauge1'] . '\'')->fetch();
//   // var_dump($gauge1_data);die;

//   $gauge1 = dataHubGaugeURL($gauge1_data['rv_id'], $gauge1_data['meter_id'], $gauge1_data['color'], $gauge1_data['bg'], $gauge1_data['height'], $gauge1_data['width'], $gauge1_data['font_family'], $gauge1_data['title'], $gauge1_data['title2'], $gauge1_data['border_radius'], $gauge1_data['rounding'], $gauge1_data['ver'], $gauge1_data['units'], $gauge1_data['title_font_size']);


//   $gauge2_data = $db->query('SELECT * FROM gauges WHERE id = \'' . $row['gauge2'] . '\'')->fetch();
//   $gauge2 = dataHubGaugeURL($gauge2_data['rv_id'], $gauge2_data['meter_id'], $gauge2_data['color'], $gauge2_data['bg'], $gauge2_data['height'], $gauge2_data['width'], $gauge2_data['font_family'], $gauge2_data['title'], $gauge2_data['title2'], $gauge2_data['border_radius'], $gauge2_data['rounding'], $gauge2_data['ver'], $gauge2_data['units'], $gauge2_data['title_font_size']);


//   $gauge3_data = $db->query('SELECT * FROM gauges WHERE id = \'' . $row['gauge3'] . '\'')->fetch();
//   $gauge3 = dataHubGaugeURL($gauge3_data['rv_id'], $gauge3_data['meter_id'], $gauge3_data['color'], $gauge3_data['bg'], $gauge3_data['height'], $gauge3_data['width'], $gauge3_data['font_family'], $gauge3_data['title'], $gauge3_data['title2'], $gauge3_data['border_radius'], $gauge3_data['rounding'], $gauge3_data['ver'], $gauge3_data['units'], $gauge3_data['title_font_size']);


//   $gauge4_data = $db->query('SELECT * FROM gauges WHERE id = \'' . $row['gauge4'] . '\'')->fetch();
//   $gauge4 = dataHubGaugeURL($gauge4_data['rv_id'], $gauge4_data['meter_id'], $gauge4_data['color'], $gauge4_data['bg'], $gauge4_data['height'], $gauge4_data['width'], $gauge4_data['font_family'], $gauge4_data['title'], $gauge4_data['title2'], $gauge4_data['border_radius'], $gauge4_data['rounding'], $gauge4_data['ver'], $gauge4_data['units'], $gauge4_data['title_font_size']);

//   // Save these so they can be hardcoded in as an initial state that doesnt repeat
//   if ($row['resource'] === 'landing') {
//     $landing1 = $gauge1;
//     $landing2 = $gauge2;
//     $landing3 = $gauge3;
//     $landing4 = $gauge4;
//     continue;
//   }
//   // Fill the array
//   $gauges[$row['resource']]['gauge1'] = $gauge1;
//   $gauges[$row['resource']]['gauge2'] = $gauge2;
//   $gauges[$row['resource']]['gauge3'] = $gauge3;
//   $gauges[$row['resource']]['gauge4'] = $gauge4;
//   $num_btns++;
// }
// ------------------------

// See $num_btns
switch ($num_btns) {
  // index 0 of $x is the initial position of the button
  // All other indexes are the amount the text needs to be shifted to fit in the buttons for each button in the order that's in the source
  case 5:
    $x = array(220, 75, 100, 90, 80, 92);
    break;
  case 4:
    $x = array(380, 75, 100, 90, 80, 92);
    break;
  case 3:
    $x = array(340, 75, 100, 90, 80, 92);
    break;
  case 2:
    $x = array(480, 75, 100, 90, 80, 92);
    break;
  default:
    $x = array(600, 75, 100, 90, 80, 92);
    break;
}

// ------------------------

// Get timing preferences
$timing = $db->query("SELECT * FROM timing WHERE user_id = {$user_id} LIMIT 1")->fetch();

// ------------------------

// Determine speed of animations
$cwd_bos = $db->query("SELECT * FROM cwd_bos WHERE user_id = {$user_id} LIMIT 1")->fetch(); // Get the meter IDs from the settings table

$water_speed = relativeValueOfGauge($db, $cwd_bos['water_speed'], -1, 1);
// array_push($log, 'Initial water speed ' . $water_speed);
$water_speed = (-$water_speed) + 2; // Default speed is 2, range 1-3
// Note that since the number we're generating above is the number of seconds it takes for the animation to complete, 1 is the fastest speed and 3 is the slowest
// array_push($log, 'Scaled water speed ' . $water_speed);

$electricity_speed = relativeValueOfGauge($db, $cwd_bos['electricity_speed'], -1, 1);
// array_push($log, 'Initial electricity speed ' . $electricity_speed);
$electricity_speed = (-$electricity_speed) + 2;
// array_push($log, 'Scaled electricity speed ' . $electricity_speed);

// Determine mood of the squirrel and fish
$squirrel_moods =  ['happy', 'neutral', 'angry'];
$fish_moods =  ['happy', 'neutral', 'sad'];
if ((isset($_GET['ver']) && $_GET['ver'] === 'kiosk')) {
  $squirrel_moods = ['happy-kiosk', 'neutral-kiosk', 'angry-kiosk'];
  $fish_moods = ['happy-kiosk', 'neutral-kiosk', 'sad-kiosk'];
}

/* default is 20 seconds */
$cwd_dashboard_interval = !empty($_GET['interval']) ? $_GET['interval'] : 30;
$cwd_dashboard_default_state = !empty($_GET['current_state']) ? $_GET['current_state'] : 'electricity';
$play_single_cwd_state = !empty($_GET['current_state']);

// Used later in JS below; filled up when checking if each button exists so resources that dont have buttons dont exist
$resources = [$cwd_dashboard_default_state];

$squirrel_mood = $squirrel_moods[round(relativeValueOfGauge($db, $cwd_bos['squirrel'], 0, 2))];
$fish_mood = $fish_moods[round(relativeValueOfGauge($db, $cwd_bos['fish'], 0, 2))];

$landing_messages_pct = relativeValueOfGauge($db, $cwd_bos['landing_messages']);
// array_push($log, 'Landing messages %: ' . $landing_messages_pct);
$landing_messages_bin = pickProb($landing_messages_pct);

$electricity_bool = false;
if (array_key_exists('electricity', $gauges)) {
  $electricity_bool = true;
  $electricity_messages_pct = relativeValueOfGauge($db, $cwd_bos['electricity_messages']);
  // array_push($log, 'electricity messages %: ' . $electricity_messages_pct);
  $electricity_messages_bin = pickProb($electricity_messages_pct);
}

$gas_bool = false;
if (array_key_exists('gas', $gauges)) {
  $gas_bool = true;
  $gas_messages_pct = relativeValueOfGauge($db, $cwd_bos['gas_messages']);
  // array_push($log, 'gas messages %: ' . $gas_messages_pct);
  $gas_messages_bin = pickProb($gas_messages_pct);
}

$stream_bool = false;
if (array_key_exists('stream', $gauges)) {
  $stream_bool = true;
  $stream_messages_pct = relativeValueOfGauge($db, $cwd_bos['stream_messages']);
  // array_push($log, 'stream messages %: ' . $stream_messages_pct);
  $stream_messages_bin = pickProb($stream_messages_pct);
}

$water_bool = false;
if (array_key_exists('water', $gauges)) {
  $water_bool = true;
  $water_messages_pct = relativeValueOfGauge($db, $cwd_bos['water_messages']);
  // array_push($log, 'water messages %: ' . $water_messages_pct);
  $water_messages_bin = pickProb($water_messages_pct);
}

$weather_bool = false;
if (array_key_exists('weather', $gauges)) {
  $weather_bool = true;
  $weather_messages_pct = relativeValueOfGauge($db, $cwd_bos['weather_messages']);
  // array_push($log, 'weather messages %: ' . $weather_messages_pct);
  $weather_messages_bin = pickProb($weather_messages_pct);
}

function pickProb($messages_pct)
{
  if ($messages_pct > 80) {
    return 'prob5';
  }
  if ($messages_pct > 60) {
    return 'prob4';
  }
  if ($messages_pct > 40) {
    return 'prob3';
  }
  if ($messages_pct > 20) {
    return 'prob2';
  } else {
    return 'prob1';
  }
}

// ------------------------

// Landing messages
// Multiply bin by rand() to get weighted random sorting. The reason you multiply multiple rand() terms is to reduce the influence of the bin term i.e. to get more randomness.
$landing_messages = $db->query("SELECT message FROM cwd_messages
  WHERE user_id = {$user_id} AND resource = 'landing' AND {$landing_messages_bin} > 0
  ORDER BY {$landing_messages_bin} * rand() * rand() * rand() * rand() * rand() DESC")->fetchAll();

// Electricity
if ($electricity_bool) {
  $electricity_messages = $db->query("SELECT message FROM cwd_messages
  WHERE user_id = {$user_id} AND resource = 'electricity' AND {$electricity_messages_bin} > 0
  ORDER BY {$electricity_messages_bin} * rand() * rand() * rand() * rand() * rand() DESC")->fetchAll();
}

// Gas
if ($gas_bool) {
  $gas_messages = $db->query("SELECT message FROM cwd_messages
  WHERE user_id = {$user_id} AND resource = 'gas' AND {$gas_messages_bin} > 0
  ORDER BY {$gas_messages_bin} * rand() * rand() * rand() * rand() * rand() DESC")->fetchAll();
}

// Stream
if ($stream_bool) {
  $stream_messages = $db->query("SELECT message FROM cwd_messages
  WHERE user_id = {$user_id} AND resource = 'stream' AND {$stream_messages_bin} > 0
  ORDER BY {$stream_messages_bin} * rand() * rand() * rand() * rand() * rand() DESC")->fetchAll();
}

// Water
if ($water_bool) {
  $water_messages = $db->query("SELECT message FROM cwd_messages
  WHERE user_id = {$user_id} AND resource = 'water' AND {$water_messages_bin} > 0
  ORDER BY {$water_messages_bin} * rand() * rand() * rand() * rand() * rand() DESC")->fetchAll();
}

// Weather
if ($weather_bool) {
  $weather_messages = $db->query("SELECT message FROM cwd_messages
  WHERE user_id = {$user_id} AND resource = 'weather' AND {$weather_messages_bin} > 0
  ORDER BY {$weather_messages_bin} * rand() * rand() * rand() * rand() * rand() DESC")->fetchAll();
}

// If it's raining irl, it's raining in cwd
$its_raining = ($db->query('SELECT COUNT(*) FROM meter_data WHERE meter_id = 166 AND value > 0 AND recorded > ' . strtotime('-20 minutes'))->fetchColumn() === '0') ? false : true;

// Whether edit tools are are available
$admin = false;
if (isset($_COOKIE['token'])) {
  $stmt = $db->prepare('SELECT token FROM users WHERE id = ?');
  $stmt->execute(array($user_id));
  if ($_COOKIE['token'] === $stmt->fetchColumn()) {
    $admin = true;
  }
}
?>
<?php
$onLoadEventForAdmin = "";
if ($admin) {
  $onLoadEventForAdmin = 'onload="makeDraggable(evt)"';
}
?>
<svg version="1.1" id="drawing" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="1584px" height="893px" viewBox="0 0 1584 893" enable-background="new 0 0 1584 893" xml:space="preserve" <?php echo $onLoadEventForAdmin ?>>
  <defs>
    <clipPath id="waterline_clip1">
      <circle r="35" cx="760" cy="320" />
      <circle r="35" cx="760" cy="320" />
      <circle r="35" cx="760" cy="320" />
    </clipPath>
    <clipPath id="waterline_clip2">
      <circle r="35" cx="1090" cy="210" />
    </clipPath>
    <clipPath id="waterline_clip3">
      <circle r="35" cx="1020" cy="520" />
      <circle r="35" cx="680" cy="750" />
    </clipPath>
    <clipPath id="waterline_clip4">
      <circle r="35" cx="400" cy="500" />
    </clipPath>
    <clipPath id="flow_marks_clip">
      <rect width="300" height="100" x="0" y="893" />
    </clipPath>
    <filter id="landscape_components_filter">
      <feComponentTransfer in="SourceGraphic">
        <feFuncR id="SvgjsFeFuncR1656" type="linear" slope="1" intercept="0.2"></feFuncR>
        <feFuncG type="linear" slope="1" intercept="0.2"></feFuncG>
        <feFuncB type="linear" slope="1" intercept="0.2"></feFuncB>
        <feFuncA type="identity"></feFuncA>
      </feComponentTransfer>
    </filter>
    <linearGradient id="rain-color" x1="50%" y1="100%" x2="50%" y2="0%">
      <stop offset="0%" style="stop-color:#2980b9;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#2980b9;stop-opacity:0.1" />
    </linearGradient>
  </defs>
  <rect id="background_white_bit" fill="#FFFFFF" width="1258.039" height="58.615" />
  <!-- <image overflow="visible" enable-background="new" width="1260" height="893" id="background" xlink:href="img/city-bg.png"></image> -->
  <image overflow="visible" enable-background="new" width="1584" height="893" id="background" xlink:href="img/<?php echo ($user_id === 2) ? 'cwdbgcle.svg' : 'background.png'; ?>"></image>
  
  <image overflow="visible" enable-background="new" width="326" height="835" id="sidebar" xlink:href="img/infopane_bg.png" transform="matrix(1 0 0 1.0707 1259 9.765625e-04)"/>
  <!-- <path id="sky" d="M0 50 L800 50 L800 200 Z"></path> -->
  <!-- charachters used to be here -->
  <?php if ($user_id !== 2) { ?>
    <g id="river">
      <g id="plum_x5F_creek" <?php echo ($user_id === 3) ? 'transform="translate(1275), scale(-1, 1)"' : ''; ?>>
        <path id="stream_flow" fill="#00AAED" d="M678.05,204.6l-1.896,0.3c-11.145,2.9-20.367,5.5-27.7,7.8
		c-7.367,2.333-13.483,4.117-18.354,5.35c-4.855,1.2-10.1,2.633-15.688,4.3c-5.645,1.667-10.645,3.066-15,4.2
		c-4.4,1.133-8.604,2.317-12.604,3.55c-4.133,1.367-7.267,2.45-9.396,3.25c-3.667,1.367-6.9,2.816-9.7,4.35
		c-3.067,1.566-5.917,3.4-8.55,5.5c-3.4,2.7-6.917,6.833-10.562,12.4c-4.6,7-7.767,11.483-9.5,13.45
		c-4.633,5.167-10.467,9.767-17.5,13.8c-4.522,2.533-10.688,5.35-18.5,8.45c-9.396,3.633-16.434,6.4-21.1,8.3
		c-8.1,3.233-14.517,5.283-19.25,6.15c-4.767,0.867-9.25,2.1-13.45,3.7c-4.2,1.6-9.283,3.229-15.25,4.896
		c-6.033,1.639-11.933,4.389-17.7,8.25c-5.767,3.833-9.105,6.117-10.05,6.854c-0.933,0.729-22.1,15.167-63.5,43.3
		c-13.5,6.933-26.033,13.683-37.6,20.25c-26.367,14.9-44.75,22.933-55.15,24.1c-10.4,1.104-22.167,4.521-35.3,10.25
		c-3.733,1.438-7.033,2.645-9.9,3.604c-2.867,0.933-7.567,2.833-14.1,5.7c-6.567,2.855-6.767,7.05-0.6,12.55
		c6.133,5.466,9.083,11.116,8.85,16.95c-0.267,5.833-3.933,9.766-11,11.8c-7.1,2.033-13.35,4.216-18.75,6.55
		s-15.917,5.9-31.55,10.7c-15.7,4.8-23.117,9.583-22.25,14.35c0.833,4.733,5.8,11.366,14.9,19.9c9.1,8.5,2.75,18.033-19.05,28.6
		c-21.8,10.534-34.483,20.784-38.05,30.75c-3.567,9.938-0.667,17.784,8.7,23.55c8.333,5.104,11.133,16.584,8.4,34.45
		c-9.934,20.134-4.967,45.05,14.9,74.75c2,4.667,6.05,10.15,12.15,16.45c9,9.3,20.35,16.217,34.05,20.75
		c13.667,4.5,25.633,13.283,35.9,26.35c10.233,13.033,29.35,21.918,57.35,26.65c27.966,4.733,46.85,18.967,56.65,42.7
		c9.8,23.7,47.316,36.384,112.55,38.05H251.3l-0.1-0.05l0.05,0.05H91.6C68.867,880.967,58,867.7,59,852.7
		c0.667-16.4-12.667-31.366-40-44.9c-10-6.134-16-15.384-18-27.75V533.2c1.9-3.833,4.517-6.733,7.85-8.7
		c4.3-2.6,11.233-4.533,20.8-5.8c9.567-1.271,18.783-3.95,27.65-8.05c8.833-4.146,12.95-10.617,12.35-19.45
		c-0.633-8.866,6.067-15.083,20.1-18.65c14.033-3.562,26.483-6.35,37.35-8.35c10.8-2.034,18.95-5.25,24.45-9.65
		c-3.667-4.033-5.767-6.6-6.3-7.7c-1.933-3.729-1.483-7.3,1.35-10.688c6.967-8.167,17-14.604,30.1-19.312
		c1.667-0.633,4.133-1.467,7.4-2.5c1.9-0.6,6.4-1.855,13.5-3.8c5.533-1.533,10.05-2.896,13.55-4.1c6.467-2.2,12.133-4.284,17-6.25
		c4.867-1.967,13.184-3.917,24.95-5.854c11.733-1.967,30.667-9.5,56.8-22.6c25.3-12.7,41.25-22.352,47.85-28.95
		c11.933-7.533,18.983-12.383,21.15-14.55c2.167-2.2,6.083-5.233,11.75-9.102c3.194-2.167,7.967-4.833,14.3-8
		c10.667-5.5,23.7-10.631,39.1-15.398c8.604-2.767,21.521-6.75,38.75-11.95l10.5-3.65c6.938-2.566,12.784-4.767,17.55-6.6
		c7.167-2.8,12.066-4.983,14.7-6.55c6.771-4,9.617-8.033,8.55-12.1c-0.833-3.066-0.667-5.95,0.5-8.65c1.2-2.7,3.45-5.333,6.75-7.9
		c3.104-2.434,6.645-4.633,10.604-6.6c2.966-1.467,6.533-2.967,10.7-4.5c7.633-2.8,16.062-5.317,25.3-7.55
		c6.033-1.5,14.283-3.25,24.75-5.25c3.366-0.633,8.146-1.6,14.35-2.9c6.567-1.4,11.333-2.383,14.3-2.95
		c8.144-1.533,15.478-3,22-4.4c3.9-0.833,7.228-1.567,9.95-2.2l4.25,2.25L678.05,204.6z M175.35,780.75c0,0.1,0.017,0.2,0.05,0.3
		l0.6,0.9C175.767,781.55,175.55,781.15,175.35,780.75z" />
        <path id="riparian_zone_2_" fill="#51CEF4" d="M548.6,255.6c-0.833,1.266-1.624,2.45-2.375,3.55
		c0.333-0.522,0.686-1.056,1.025-1.6c1.05-1.648,2.092-3.173,3.125-4.575C549.788,253.812,549.196,254.687,548.6,255.6z
		 M134.275,538.425c-0.667-1.165-1.609-2.306-2.825-3.425c-9.167-8.434-14.184-15-15.05-19.7c-0.152-0.771-0.085-1.561,0.2-2.35
		c2.075,4.35,6.658,9.85,13.75,16.5C133.424,532.321,134.732,535.312,134.275,538.425z M127.1,464.2
		c9.144-1.722,16.386-4.288,21.725-7.7c0.886,1.003,1.944,2.17,3.175,3.5c-5.467,4.434-13.583,7.717-24.35,9.85
		c-10.867,2.104-23.3,4.984-37.3,8.65c-14,3.7-20.65,9.983-19.95,18.85c0.7,8.834-3.35,15.334-12.15,19.5
		c-8.833,4.167-18.033,6.938-27.6,8.312c-9.533,1.333-16.45,3.333-20.75,6c-2.62,1.561-5.586,5.311-8.9,11.25v-9.2
		c1.9-3.833,4.517-6.733,7.85-8.7c4.3-2.6,11.233-4.533,20.8-5.8c9.567-1.271,18.783-3.95,27.65-8.05
		c8.833-4.146,12.95-10.617,12.35-19.45c-0.633-8.866,6.067-15.083,20.1-18.65C103.783,468.995,116.233,466.2,127.1,464.2z
		 M190.15,449.2c6.133,5.466,9.083,11.116,8.85,16.95c-0.004,0.098-0.013,0.188-0.025,0.3c-1.126-4.169-3.935-8.218-8.425-12.15
		c-4.103-3.617-5.419-6.676-3.95-9.175C187.345,446.395,188.528,447.754,190.15,449.2z M175.35,780.75c0,0.1,0.017,0.2,0.05,0.3
		l0.6,0.9C175.767,781.55,175.55,781.15,175.35,780.75z M110.35,744.95c-6.167-6.271-10.267-11.717-12.3-16.354
		c-8.455-12.413-12.263-28.413-11.425-48c2.487,12.455,8.695,26.105,18.625,40.95c2,4.667,6.05,10.15,12.15,16.45
		c9,9.3,20.35,16.217,34.05,20.75c13.667,4.5,25.633,13.283,35.9,26.35c10.233,13.033,29.35,21.917,57.35,26.65
		c27.966,4.732,46.85,18.967,56.65,42.7c9.8,23.7,47.316,36.384,112.55,38.05h-58.775c-33.006-5.58-52.948-16.496-59.825-32.75
		c-10-23.633-29-37.7-57-42.2c-28.066-4.466-47.267-13.183-57.6-26.146c-10.367-12.979-22.417-21.646-36.15-26
		C130.817,760.967,119.417,754.15,110.35,744.95z M90.575,625.975c-1.596-3.314-3.854-5.855-6.775-7.625
		c-6.356-3.84-9.773-8.646-10.25-14.425c1.642,3.188,4.441,5.988,8.4,8.425C86.397,615.071,89.272,619.613,90.575,625.975z
		 M168.7,787.45c-0.034-0.104-0.05-0.2-0.05-0.3c0.2,0.396,0.417,0.8,0.65,1.188L168.7,787.45z M524.3,269.15
		c-2.633,1.567-7.517,3.783-14.646,6.65c-4.733,1.9-10.566,4.15-17.5,6.75l-10.45,3.75c-17.2,5.367-30.083,9.467-38.65,12.3
		c-15.367,4.9-28.35,10.15-38.95,15.75c-6.3,3.233-11.048,5.95-14.25,8.15c-5.633,3.9-9.517,6.967-11.646,9.2
		c-2.167,2.167-9.188,7.062-21.05,14.7c-6.533,6.667-22.383,16.467-47.556,29.396c-26,13.333-44.867,21.034-56.6,23.104
		c-11.733,2.021-20.033,4.05-24.9,6.05c-4.833,2-10.483,4.133-16.95,6.396c-3.467,1.233-7.967,2.645-13.5,4.2
		c-7.066,2-11.55,3.317-13.45,3.95c-3.267,1.066-5.733,1.917-7.4,2.55c-13.033,4.833-23,11.354-29.9,19.55
		c-1.087,1.333-1.812,2.692-2.175,4.075c-1.283-3.31-0.658-6.479,1.875-9.521c6.967-8.167,17-14.604,30.1-19.312
		c1.667-0.633,4.133-1.467,7.4-2.5c1.9-0.6,6.4-1.855,13.5-3.8c5.533-1.533,10.05-2.896,13.55-4.1c6.467-2.2,12.133-4.284,17-6.25
		c4.867-1.975,13.184-3.917,24.95-5.854c11.733-1.967,30.667-9.5,56.8-22.6c25.3-12.7,41.25-22.353,47.85-28.95
		c11.938-7.533,18.983-12.383,21.15-14.55c2.167-2.2,6.083-5.233,11.75-9.103c3.196-2.168,7.967-4.834,14.3-8
		c10.667-5.5,23.7-10.633,39.1-15.397c8.604-2.767,21.521-6.75,38.75-11.95l10.5-3.65c6.938-2.566,12.784-4.767,17.55-6.6
		c7.175-2.8,12.066-4.983,14.7-6.55c3.062-1.805,5.312-3.613,6.775-5.425C531.161,264.097,528.486,266.622,524.3,269.15z
		 M671.8,202.35V203l-0.3-0.15c-2.733,0.667-6.05,1.433-9.95,2.3c-6.533,1.434-13.85,2.95-21.95,4.55
		c-2.967,0.6-7.729,1.633-14.3,3.1c-6.167,1.367-10.933,2.367-14.3,3c-10.467,2.1-18.7,3.934-24.7,5.5
		c-9.229,2.3-17.646,4.883-25.25,7.75c-4.167,1.567-7.716,3.1-10.646,4.6c-3.978,2-7.483,4.233-10.562,6.7
		c-1.756,1.398-3.215,2.806-4.375,4.225c1.306-2.113,3.265-4.188,5.875-6.225c3.104-2.434,6.645-4.633,10.604-6.6
		c2.966-1.467,6.533-2.967,10.7-4.5c7.633-2.8,16.062-5.317,25.3-7.55c6.033-1.5,14.283-3.25,24.75-5.25
		c3.366-0.633,8.146-1.6,14.35-2.9c6.567-1.4,11.333-2.383,14.309-2.95c8.135-1.533,15.469-3,22-4.4
		C666.562,203.514,669.379,202.897,671.8,202.35z" />
        <g id="objects">
          <g>
            <path fill="#4ACBF5" d="M214.1,812.951c-4.491,2.59-10.928,2.933-19.31,1.021l-1.311-0.893
				c-0.451-1.146,0.096-2.092,1.638-2.836c-0.327,0.968-0.123,1.66,0.614,2.076c5.406,2.566,10.676,1.229,15.809-4.021
				C215.8,809.35,216.652,810.896,214.1,812.951z" />
          </g>
          <g>
            <path fill="#B7AEA1" d="M207.156,803.693c1.185,0.312,2.36,1.354,3.525,3.143c1.165,1.761,1.829,2.562,1.993,2.406
				c1.472,0.803,1.952,1.738,1.44,2.809c-6.561,2.874-12.785,3.176-18.671,0.902c-0.674-1.805-0.766-3.266-0.276-4.379
				c0.491-1.114,2.116-1.716,4.875-1.812l2.606-2.729C204.489,803.471,205.991,803.359,207.156,803.693z" />
            <path fill="#AD9D89" d="M207.493,808.039c-0.49,0.135-1.093,0.201-1.809,0.201c-0.715,0-1.318-0.066-1.809-0.201
				c-0.491-0.134-0.736-0.289-0.736-0.468c0-0.188,0.245-0.335,0.736-0.479c0.49-0.134,1.093-0.189,1.809-0.189
				c0.716,0,1.319,0.062,1.809,0.189c0.511,0.145,0.767,0.289,0.767,0.479C208.26,807.75,208.004,807.905,207.493,808.039z" />
            <path fill="#AD9D89" d="M198.387,811.115c1.308-0.09,2.524-0.188,3.648-0.271c1.104-0.09,1.472-0.188,1.104-0.301
				c-0.552-0.268-0.348-0.257,0.613,0.033c0.94,0.289,1.983,0.345,3.127,0.166c1.165-0.178,1.87-0.229,2.116-0.133
				c0.225,0.062-0.327,0.379-1.656,0.936c-1.329,0.561-2.678,0.836-4.047,0.836c-1.39,0-2.565-0.104-3.526-0.334
				c-0.961-0.201-1.441-0.469-1.441-0.803C198.326,811.203,198.347,811.158,198.387,811.115z" />
            <path fill="#AD9D89" d="M203.14,810.547c0.368,0.111,0,0.211-1.104,0.301c-1.124,0.101-2.34,0.188-3.648,0.271
				c-0.062-0.023-0.112-0.045-0.153-0.068c-0.428-0.135-1.113-0.312-2.054-0.551c-0.94-0.229-1.012-0.393-0.214-0.484
				c0.792-0.093,1.728-0.132,2.805-0.105c1.079,0.013,1.789,0.062,2.131,0.146C201.247,810.134,201.993,810.301,203.14,810.547z" />
          </g>
          <g>
            <path fill="#4ACBF5" d="M221.756,825.319c-2.679,2.438-6.518,2.766-11.518,0.969l-0.781-0.842
				c-0.269-1.081,0.057-1.979,0.977-2.675c-0.196,0.9-0.074,1.562,0.366,1.959c3.224,2.428,6.367,1.153,9.429-3.791
				C222.77,821.922,223.278,823.382,221.756,825.319z" />
          </g>
          <g>
            <path fill="#BCB5AC" d="M220.873,821.811c0.877,0.74,1.164,1.604,0.859,2.602c-3.913,2.646-7.625,2.929-11.136,0.832
				c-0.402-1.664-0.458-3.021-0.165-4.039c0.292-1.027,0.927-1.787,1.901-2.281l2.012-1.146c1.092,0.688,2.053,0.896,2.88,0.604
				c0.827-0.292,1.616,0.242,2.368,1.604C220.348,821.341,220.774,821.952,220.873,821.811z M215.625,820.701
				c0.292,0.123,0.652,0.185,1.079,0.185c0.427,0,0.787-0.062,1.079-0.185c0.305-0.124,0.458-0.271,0.458-0.438
				s-0.152-0.309-0.458-0.432c-0.292-0.124-0.652-0.188-1.079-0.188c-0.427,0-0.787,0.062-1.079,0.188
				c-0.293,0.123-0.439,0.268-0.439,0.432C215.186,820.433,215.332,820.577,215.625,820.701z M215.186,823.014
				c-0.683-0.227-1.127-0.381-1.334-0.463s-0.628-0.123-1.262-0.123c-0.646-0.021-1.207,0.01-1.682,0.092
				c-0.476,0.082-0.433,0.236,0.128,0.475c0.561,0.193,0.969,0.357,1.225,0.481c0.024,0.021,0.055,0.04,0.092,0.062
				c-0.024,0.041-0.037,0.082-0.037,0.123c0,0.309,0.287,0.555,0.859,0.74c0.573,0.193,1.274,0.309,2.103,0.309
				c0.816,0,1.621-0.258,2.414-0.771c0.792-0.521,1.121-0.812,0.987-0.863c-0.146-0.082-0.567-0.041-1.262,0.123
				c-0.683,0.165-1.305,0.104-1.865-0.154C214.979,822.776,214.856,822.766,215.186,823.014z" />
            <path fill="#ADA08D" d="M212.352,823.537c0.78-0.082,1.505-0.164,2.176-0.246c0.658-0.083,0.878-0.176,0.658-0.277
				c-0.329-0.248-0.207-0.229,0.366,0.029c0.561,0.268,1.183,0.319,1.865,0.154c0.694-0.164,1.115-0.205,1.262-0.123
				c0.134,0.062-0.195,0.35-0.987,0.863c-0.793,0.514-1.598,0.771-2.414,0.771c-0.829,0-1.53-0.104-2.103-0.309
				c-0.573-0.188-0.859-0.438-0.859-0.74C212.315,823.619,212.327,823.578,212.352,823.537z" />
            <path fill="#ADA08D" d="M215.625,820.701c-0.293-0.124-0.439-0.271-0.439-0.438s0.146-0.309,0.439-0.432
				c0.292-0.124,0.652-0.188,1.079-0.188c0.427,0,0.787,0.062,1.079,0.188c0.305,0.123,0.458,0.268,0.458,0.432
				s-0.152,0.312-0.458,0.438c-0.292,0.123-0.652,0.185-1.079,0.185C216.277,820.886,215.917,820.824,215.625,820.701z" />
            <path fill="#ADA08D" d="M215.186,823.014c0.22,0.104,0,0.194-0.658,0.277c-0.67,0.082-1.396,0.164-2.176,0.246
				c-0.037-0.021-0.067-0.041-0.092-0.062c-0.256-0.124-0.664-0.288-1.225-0.492c-0.561-0.228-0.604-0.382-0.128-0.464
				c0.475-0.082,1.036-0.104,1.682-0.092c0.634,0,1.054,0.041,1.262,0.123S214.503,822.787,215.186,823.014z" />
          </g>
        </g>
        <g id="flow_marks_1_">
          <path fill="#36B8F2" d="M184.55,461.45c-2.167,4.134-5.9,6.833-11.2,8.1c13.433-5.2,13.316-12.2-0.35-21
			C182.867,452.983,186.717,457.283,184.55,461.45z M144.55,524.7l0.2,0.2c-0.3,0.021-0.717,0.016-1.25-0.062
			C143.867,524.816,144.217,524.767,144.55,524.7z M161.975,483.1h0.175c-2.12,1.021-4.454,2.271-7,3.75l-0.3-0.146
			c1.667-0.833,3.35-1.688,5.05-2.55C160.611,483.801,161.302,483.45,161.975,483.1z" />
          <g>
            <path fill="#36B8F2" d="M59.2,767.85v-0.3c1.466,1.104,2.966,2.233,4.5,3.4c24.167,18.466,50.25,30.062,78.25,34.8
				c27.961,4.73,49.028,19.572,63.2,44.525c14.175,24.956,45.875,37.015,95.1,36.175c-8.724,4.353-24.674,4.369-47.85,0.05
				c-33.233-8.467-54.3-22.883-63.2-43.25c-8.9-20.4-27.083-31.9-54.55-34.5c-27.467-2.6-46.367-10.383-56.7-23.35
				C72.217,778.233,65.967,772.383,59.2,767.85z M50.5,747.85c1.966,1.4,6.25,3.742,12.85,7.025s20.8,11.842,42.6,25.675v0.3
				c-5.467-3.666-11.267-6.479-17.4-8.438c-13.733-4.438-25.133-11.25-34.2-20.45C52.95,750.55,51.667,749.184,50.5,747.85z" />
          </g>
          <g>
            <path fill="#36B8F2" d="M56.6,537.075c0.304,1.384-2.996,4.06-9.9,8.021c-6.901,3.979-14.118,7.666-21.65,11.104
				c-7.6,3.396-12.483,9.683-14.65,18.85c-1.784,7.409-4.918,13.333-9.4,17.775V586.6c3.563-3.881,6.03-8.514,7.4-13.896
				c1.934-7.604,6.733-13.604,14.4-18c7.633-4.366,14.767-7.967,21.4-10.8C49.137,541.802,53.27,539.527,56.6,537.075z" />
          </g>
          <g>
            <path fill="#36B8F2" d="M16,719.35c-4.133-2.766-8.183-9.8-12.15-21.1c-3.4-12.3-3.767-22.517-1.1-30.65
				C-0.15,680.233,4.267,697.483,16,719.35z" />
          </g>
          <g>
            <path fill="#36B8F2" d="M20.826,546.508c6.485-2.021,13.046-5.637,19.684-10.83c10.006-7.803,19.278-12.932,27.815-15.39
				c-3.344,1.875-6.954,4.229-10.83,7.067C43.259,537.742,31.035,544.127,20.826,546.508z" />
          </g>
          <g>
            <path fill="#36B8F2" d="M125.95,483.1c10.733-3.562,19.966-4.688,27.7-3.396c-11.8-1.104-26.917,3.616-45.35,14.146
				C110.3,490.584,116.183,487,125.95,483.1z" />
          </g>
        </g>
      </g>
      <?php if ($user_id !== 3) { ?>
        <g id="black_x5F_river">
          <path id="river_flow" fill="#00AAED" d="M1258.301,243.3v36.1c-34.667-14.1-63.768-23-87.301-26.7
    c-23.566-3.733-42.633-6.6-57.199-8.6c-14.601-2-22.018-3-22.25-3c-3.934-0.466-7.934-0.883-12-1.25
    c-11.533-1.133-23.768-2.1-36.701-2.9c-14.562-0.933-31.146-1.717-49.75-2.35c-44.6-1-82.312-1.667-113.146-2
    c-30.833-0.367-57.021-1.867-78.55-4.5c-21.567-2.633-37.938-4.683-49.104-6.15c-14.667-1.9-27.85-4.1-39.55-6.6
    c-25.761-5.447-43.995-13.805-54.7-25.075c-0.644-0.679-2.811-2.071-6.5-4.175c4.021-1.21,8.4-1.627,13.15-1.25l-1.104,0.35
    c-1.168,0.067-1.051,0.8,0.354,2.2c1.367,1.433,5.267,4.267,11.7,8.5c6.433,4.2,19.562,8.633,39.396,13.3
    c19.833,4.667,44.854,8.383,75.051,11.15c30.166,2.767,57.332,4.233,81.5,4.4c24.145,0.133,43.399-0.184,57.812-0.95
    c14.359-0.767,34.188-0.7,59.5,0.2c25.267,0.9,45.133,1.4,59.6,1.5c20.438,0.1,38.783,0.433,55.053,1
    c16.23,0.533,30.73,2.1,43.5,4.7c12.771,2.6,30.725,4.133,53.854,4.6C1224.033,236.233,1243.167,238.733,1258.301,243.3z" />
          <path id="riparian_zone" fill="#51CEF4" d="M1258.25,273.325l0.051,6.075c-0.045-0.018-0.087-0.035-0.125-0.05L1258.25,273.325z
     M1258.301,243.3v3.85c-15.134-4.633-34.268-7.217-57.396-7.75c-23.138-0.6-41.066-2.217-53.812-4.85
    c-12.766-2.667-27.266-4.3-43.5-4.9c-16.266-0.633-34.604-1.05-55.049-1.25c-14.467-0.167-34.334-0.75-59.601-1.75
    c-25.271-1.033-45.104-1.2-59.5-0.5c-14.399,0.733-33.667,0.967-57.803,0.7c-24.166-0.267-51.313-1.85-81.447-4.75
    c-30.2-2.9-55.2-6.733-75-11.5c-19.833-4.733-32.95-9.216-39.354-13.45c-6.433-4.267-10.312-7.117-11.646-8.55
    c-1.4-1.4-2.15-2.133-2.25-2.2l0.175-0.1h0.725c0.312,0.304,0.682,0.67,1.104,1.1c1.367,1.433,5.267,4.267,11.7,8.5
    c6.433,4.2,19.562,8.633,39.396,13.3c19.833,4.667,44.854,8.383,75.05,11.15c30.168,2.767,57.334,4.233,81.5,4.4
    c24.146,0.133,43.4-0.184,57.812-0.95c14.356-0.767,34.188-0.7,59.5,0.2c25.267,0.9,45.133,1.4,59.6,1.5
    c20.438,0.1,38.783,0.433,55.052,1c16.231,0.533,30.731,2.1,43.5,4.7c12.771,2.6,30.724,4.133,53.854,4.6
    C1224.033,236.233,1243.167,238.733,1258.301,243.3z" />
        </g>
      <?php } ?>
    </g>
  <?php } // end if ($user_id !== 2) 
  ?>
  <g id="sunset" display="none">
    <rect id="background_sunset_bit" display="inline" fill="#FEFACC" width="1258.039" height="58.615" />

    <image display="inline" overflow="visible" enable-background="new" width="1256" height="137" id="sunset_graphic" xlink:href="img/sunset.png" transform="matrix(1 0 0 1 1.0205 58.6152)">
    </image>
  </g>
  <g id="flow_marks" clip-path="url(#flow_marks_clip)" style="opacity:0">
    <path display="inline" fill="#92E3FC" d="M183.558,462.634c-2.167,4.133-5.9,6.833-11.2,8.104c13.433-5.2,13.316-12.2-0.35-21
	C181.875,454.167,185.725,458.467,183.558,462.634z M142.508,526.033c0.367-0.033,0.717-0.083,1.05-0.149l0.2,0.2
	C143.458,526.117,143.042,526.101,142.508,526.033z M158.908,485.334c0.711-0.35,1.402-0.7,2.075-1.051h0.175
	c-2.12,1.012-4.454,2.262-7,3.75l-0.3-0.149C155.525,487.05,157.208,486.2,158.908,485.334z" />
    <path display="inline" fill="#92E3FC" d="M76.958,786.584c-5.733-7.167-11.983-13.017-18.75-17.551v-0.3
	c1.466,1.101,2.966,2.229,4.5,3.396c24.167,18.467,50.25,30.066,78.25,34.8c27.961,4.73,49.028,19.572,63.2,44.525
	c14.175,24.956,45.875,37.015,95.1,36.175c-8.724,4.354-24.674,4.369-47.85,0.05c-33.233-8.467-54.3-22.884-63.2-43.25
	c-8.9-20.396-27.083-31.896-54.55-34.5C106.191,807.333,87.292,799.55,76.958,786.584z M53.358,753.134
	c-1.4-1.396-2.684-2.767-3.85-4.101c1.966,1.396,6.25,3.741,12.85,7.021c6.6,3.284,20.8,11.854,42.6,25.688v0.301
	c-5.467-3.666-11.267-6.483-17.4-8.449C73.825,769.15,62.425,762.334,53.358,753.134z" />
    <path display="inline" fill="#92E3FC" d="M43.208,545.084c4.937-2.099,9.07-4.373,12.4-6.825c0.304,1.384-2.996,4.062-9.9,8.024
	c-6.901,3.967-14.118,7.667-21.65,11.101c-7.6,3.399-12.483,9.688-14.65,18.854c-1.784,7.396-4.918,13.334-9.4,17.771v-6.226
	c3.563-3.881,6.03-8.521,7.4-13.899c1.934-7.601,6.733-13.601,14.4-18C29.441,551.517,36.575,547.917,43.208,545.084z" />
    <path display="inline" fill="#92E3FC" d="M15.008,720.533c-4.133-2.771-8.183-9.8-12.15-21.104c-3.4-12.3-3.767-22.517-1.1-30.646
	C-1.142,681.417,3.275,698.667,15.008,720.533z" />
    <path display="inline" fill="#92E3FC" d="M67.358,521.483c-3.367,1.854-6.983,4.217-10.85,7.05
	c-14.233,10.396-26.45,16.783-36.65,19.146c6.467-2.021,13.017-5.633,19.65-10.8C49.508,529.051,58.792,523.917,67.358,521.483z" />
    <path display="inline" fill="#92E3FC" d="M124.958,484.283c10.733-3.566,19.966-4.699,27.7-3.399
	c-11.8-1.101-26.917,3.616-45.35,14.149C109.308,491.767,115.191,488.184,124.958,484.283z" />
  </g>
  <path id="flowpath" fill="none" d="M391.111,984.562c-45.272-18.396-88.772-40.354-130.083-66.479
c-17.963-11.354-36.367-23.123-51.044-38.676c-13.199-13.987-22.659-30.925-34.254-46.17
c-13.204-17.359-31.177-37.627-50.981-47.695c-15.071-7.661-32.549-6.793-47.613-14.838c-27.367-14.61-46.411-45.222-60.279-71.82
C3.62,673.499-12.494,638.516-4.115,609.11c6.541-22.957,28.802-36.943,45.936-51.621c27.643-23.688,49.917-52.188,80.851-71.95
c26.352-16.842,45.643-40.576,71.953-57.613c19.09-12.354,39.654-22.311,60.302-31.765c63.169-28.921,129.253-51.415,194.688-74.565
" />
  <?php if ($user_id !== 3) {
    echo '<image overflow="visible" enable-background="new" width="460" height="483" id="vegetation" xlink:href="img/vegetation.png" transform="matrix(1 0 0 1 0 410)">
  </image>';
  }
  if ($user_id !== 2) {
  ?>
    <g id="waterlines" <?php echo ($user_id === 3) ? 'transform="translate(1350), scale(-1, 1)"' : ''; ?>>
      <g opacity="0.2">
        <path d="M1070.073,240.2l-68.354,13.95v0.05c-7.396,1.9-13.47,2.57-18.188,2c-2.604-0.27-4.688-0.48-6.25-0.65l-4.9,4.85
		c4,0.37,7.28,0.68,9.854,0.95c5.521,0.5,12.271-0.28,20.25-2.35v-0.05l80.354-17.4L1070.073,240.2z M896.523,246.2
		c-5.771-0.5-12.812,0.28-21.104,2.35l0.604,4.8c8.93-2.57,15.22-3.58,18.85-3.05c3.631,0.5,5.9,1.07,6.801,1.7l5.149-4.85
		C902.653,246.78,899.224,246.47,896.523,246.2z M852.224,255.7c-66.5,17.03-94.62,36.15-84.35,57.35
		c2.03-21.33,32.5-39.58,91.4-54.75L852.224,255.7z M983.974,683.93c0.33,0.88,0.646,1.75,0.938,2.62h0.72L983.974,683.93z" />
        <path d="M872.424,763.1c-31.5,0.23-66.301,0.354-84.396,0.354l0.188-9.854l84.354,0.062c82.521-4.438,116.58-26.91,102.181-67.45
		l-76.229-120.7h0.062l-68.354-108.7c0.063,0.07,0.146,0.146,0.194,0.2l-21.5-33.45c-4.17-5.271-11.33-9.271-21.5-12.05h0.056
		c-21.354-7.5-31.926-18.3-31.646-32.396V352.56c-3.9,3.771-9.03,7.104-15.4,10l-56.646,26.7c-9.73,5.13-18.28,7.82-25.65,8.05
		c-3.562-0.021-186.779-0.021-189.646,0c0.229-3.47,0.633-6.75,1.188-9.85c2.07-0.03,184.883-0.05,188.45-0.05
		c6.57,0.062,14.521-2.382,23.854-7.354l54.604-25.646c12.47-5.53,18.83-13.562,19.104-24.062v-6.85h8.396l-0.55-0.3h3.688
		l-0.438,0.3h0.396v8c0.646,9.93,6.979,17.57,19,22.9l75.312,35.396v-0.05c0.17,0.1,0.33,0.18,0.5,0.25l0.053,0.05l0.447,0.2v0.05
		c9,4.671,16.683,6.971,23.053,6.9h105.6v9.896H892.5c-2.13-0.021-4.448-0.021-6.948,0c-7.271-0.27-15.646-2.879-25.146-7.85
		l-78.062-36.7c-5.931-2.7-10.801-5.78-14.603-9.25v25.8c0.132,10.669,8.396,18.771,24.802,24.301c0.028,0,0.08,0.021,0.146,0.062
		c12.354,4.329,20.314,9.43,23.896,15.3v-0.05l89.604,142.05v-0.05l19.479,30.85l58.317,92.38c0.327,0.88,0.643,1.75,0.933,2.62
		C1000.323,732.09,962.823,757.61,872.424,763.1z" />
      </g>
      <g opacity="0.2">
        <path d="M948.973,451.8l12,0.604l95.451,137.396v-0.05l98.25,141.5c2.133,3.133,4.033,5.949,5.699,8.449
		c1.667,2.5,5.1,8.229,10.301,17.2c0.799,1.934,2,4.75,3.604,8.438c1.396,4.733,1.479,9.479,0.25,14.2
		c-6.867,40.8-62.783,66.716-167.75,77.75c-13.033,1.866-28.084,3-45.15,3.396c-1.3,0-2.6,0.021-3.896,0.062H565.825
		c-0.033,0.021-0.066,0.05-0.104,0.05h-8.25c-0.396-0.034-1.083-0.083-2.05-0.146c-1-0.062-2-0.104-3-0.104
		c-1.033,0-7.617-0.812-19.75-2.45c-11.783-1.604-22.549-5.938-32.3-13v0.354c-0.833-0.732-1.667-1.479-2.5-2.199
		c-26.333-23.438-55.05-55.188-86.15-95.25c-0.1-0.033-0.183-0.066-0.25-0.104l0.15-0.104c-0.067-0.062-0.867-1.2-2.4-3.396
		l10.45-6.896l4.8,2.1c8.534,8.867,30.438,13.812,65.7,14.812l166.5,0.3v9.396c-16.167,0-42.562,0.021-59.2,0.062
		c-24.433,0.062-49.933,0.134-76.5,0.188c-41,0.104-69.267-1.688-84.8-5.396c16.438,24.101,37,47.467,61.7,70.101
		c0.833,0.771,1.667,1.533,2.5,2.312v0.146c6.412,6.104,11.496,9.988,15.25,11.691c3.966,1.771,9.064,3.417,15.3,4.95
		c6.2,1.467,17.667,2.688,34.4,3.688h391.851c1.5-0.021,2.982-0.062,4.449-0.104c17.301-0.604,32.283-1.729,44.95-3.396V848.3
		c101.936-12.332,154.469-37.999,157.601-77h-0.396c-2-9.562-6.482-19.8-13.449-30.694c-0.966-1.473-1.95-2.938-2.95-4.438h-0.05
		l-98.25-141.5h0.05L948.973,451.8z M466.874,413.5l12.2-0.4L368.574,563c-0.066,0.066-0.117,0.133-0.15,0.199
		c-8,10.562-5.933,18.634,6.2,24.2v-0.062l76.2,37.7V625c49.3,22.366,53.062,45.516,11.3,69.449h-0.05l-28.7,16.45l-0.05-2.562
		h-0.05l-0.062-8.396l-1.3-1.604l17.25-9.438c34.771-17.199,35.383-33.949,1.854-50.25l-0.06-2.562v2.5l-87.35-43.1
		c-0.034,0-0.067-0.021-0.104-0.062c-15.729-7.473-18.033-18.271-6.896-32.396h0.05L466.874,413.5z" />
      </g>
      <g>
        <g>
          <path fill="#B4F0FE" d="M972.873,656.4l-5.6-8.854h0.05l-57.854-91.3v0.05l-89.601-141.45v0.062
			c-3.569-5.871-11.529-10.95-23.899-15.25c-0.062-0.03-0.119-0.062-0.146-0.062c-16.4-5.521-24.67-13.6-24.8-24.188v-25.7
			c3.8,3.43,8.67,6.5,14.6,9.2l78.051,36.55c9.5,4.97,17.88,7.562,25.149,7.8c2.5-0.029,4.812-0.029,6.95,0h98.688v-9.85H888.82
			c-6.37,0.062-14.062-2.229-23.062-6.854v-0.05l-0.438-0.2l-0.062-0.05c-0.172-0.07-0.312-0.15-0.5-0.25v0.05l-75.307-35.25
			c-12.021-5.33-18.369-12.95-19-22.85V320h-0.396l0.195-0.15h-0.35v0.25h-5.5V320h-5.45v6.85
			c-0.271,10.438-6.63,18.42-19.104,23.95l-54.604,25.55c-9.33,4.938-17.278,7.37-23.854,7.312c-3.562,0-186.382,0.02-188.438,0.05
			c-0.562,3.104-0.979,6.37-1.197,9.8c2.867-0.021,186.078-0.021,189.646,0c7.359-0.229,15.923-2.896,25.646-8l56.646-26.6
			c6.363-2.9,11.5-6.229,15.396-9.95v26.45c-0.271,14.021,10.271,24.771,31.646,32.25h-0.061c10.172,2.77,17.328,6.77,21.5,12
			l37.31,59.8c-1.104-2.07-2.139-4.021-3.104-5.854l55.442,87.438h-0.062l28.646,45.15h-0.062l34.854,55h0.055
			c0.896,1.5,1.774,2.979,2.646,4.45c28.938,50.1-2.25,77.604-93.562,82.55l-83.188-0.05l-0.396,9.8
			c17.868,0,52.354-0.12,83.445-0.354c99.104-6.062,133.771-36.72,104-91.938C976.644,662.62,974.844,659.53,972.873,656.4z
			 M843.924,468.25c0.029,0.27,0.08,0.55,0.149,0.85C843.604,468.2,843.554,467.92,843.924,468.25z" />
          <g>

            <image display="none" overflow="visible" enable-background="new" width="267" height="475" id="freshwater_highlighted" xlink:href="img/freshwater_highlighted.png" transform="matrix(1 0 0 1 739.5 301)">
            </image>
            <g>
              <path fill="#B4F0FE" d="M977.704,665.652c-1.605-3.032-3.405-6.122-5.376-9.252l-5.6-8.854h0.05l-57.854-91.3v0.05
					l-89.601-141.45v0.062c-3.569-5.871-11.529-10.95-23.899-15.25c-0.062-0.03-0.119-0.062-0.146-0.062
					c-16.4-5.521-24.67-13.6-24.8-24.188v-25.7l-0.375-19.949c-0.081-0.593-0.146-1.195-0.188-1.805V320h-0.396l0.188-0.15h-0.354
					v0.25h-5.5V320h-5.45l0.144,28.955v26.45c-0.271,14.021,10.277,24.771,31.646,32.25h-0.062c10.174,2.77,17.327,6.77,21.5,12
					l37.312,59.8c-1.104-2.07-2.144-4.021-3.104-5.854l55.446,87.45h-0.051l28.646,45.15h-0.052l34.854,55h0.062
					c0.896,1.5,1.771,2.979,2.646,4.45c28.938,50.1-2.25,77.604-93.552,82.55l-83.198-0.05l-0.396,9.8
					c17.87,0,52.354-0.12,83.438-0.354C972.807,751.533,1007.475,720.877,977.704,665.652z" />
            </g>
          </g>
        </g>
        <path fill="#B4F0FE" d="M1079.523,240.75l-71.45,15.75v0.05c-7.729,2.07-14.28,2.85-19.649,2.35c-2.49-0.27-29.53-3.3-81.101-9.1
		c-3.95-0.52-6.771-0.91-8.399-1.15c-3.938-0.63-17.15,2.1-39.62,8.18l0.021,0.02c-56.8,14.23-86.2,31.47-88.2,51.7
		c-10.021-20.08,17.39-38.18,82.25-54.3l-0.55,0.05c25.229-6.77,40.021-10.02,44.351-9.75c3.239,0.17,6.42,0.51,9.55,1l2.649,0.3
		c51.03,5,77.801,7.63,80.301,7.9c4.604,0.57,10.479-0.08,17.649-1.95v-0.05l55.601-12.3L1079.523,240.75z" />
      </g>
      <g>
        <path fill="#E2D1C7" d="M412.062,741.9l-0.479,0.31c-0.05-0.07-0.11-0.14-0.16-0.21L412.062,741.9z" />
        <path fill="#E2D1C7" d="M1174.523,774.02c-6.86,40.812-62.771,66.73-167.75,77.78c-13.03,1.87-28.08,3-45.15,3.4
		c-1.3,0-2.6,0.02-3.899,0.05H565.824c-0.04,0.021-0.08,0.03-0.13,0.05h-8.229c-0.396-0.03-1.09-0.08-2.08-0.146
		c-0.979-0.08-1.979-0.12-3-0.131c-1,0.011-7.577-0.812-19.729-2.449c-12.17-1.646-23.24-6.2-33.23-13.67l-0.05-4.65V839
		c-26.72-23.56-55.94-55.77-87.66-96.63c-0.04-0.05-0.08-0.104-0.13-0.16l0.479-0.31l12.408-7.9c5.9,6.14,18.2,10.39,36.9,12.75
		c8.33,1.06,17.93,1.74,28.8,2.05l166.5,0.3v9.45c-12.77,0-35.6,0.021-48.5,0.05h-24.899c-20.062,0.062-40.851,0.104-62.312,0.15
		c-24.354,0.06-44.212-0.56-59.57-1.85c-10.5-0.881-18.892-2.07-25.188-3.57c16.769,24.59,37.812,48.42,63.188,71.52v0.2
		c6.87,6.63,12.29,10.84,16.25,12.62c3.979,1.8,9.07,3.45,15.271,4.95c6.228,1.5,17.688,2.74,34.438,3.729H957.2
		c1.5-0.021,2.979-0.062,4.438-0.1c17.312-0.6,32.28-1.729,44.95-3.4v-0.05c102.486-12.41,155.04-38.3,157.646-77.646
		c-1.87-7.312-4.775-14.4-8.729-21.312c-0.021-0.051-0.052-0.101-0.062-0.146c-1.521-2.854-3.229-5.75-5.104-8.7
		c-0.972-1.47-1.947-2.95-2.947-4.45h-0.053l-98.25-141.5h0.053l-95.553-136.85h10.604l92.25,132.1v-0.05l98.25,141.5
		c2.136,3.13,4.021,5.95,5.688,8.45c1.674,2.5,5.104,8.229,10.312,17.2c0.808,1.938,2,4.76,3.604,8.438
		C1175.684,764.57,1175.773,769.29,1174.523,774.02z" />
      </g>
      <g>
        <path fill="#E2D1C7" d="M462.124,688.85l-0.06-0.02l-28.69,16.47l-0.05-2.55h-0.05l-0.062-3c-1.329-1.561-3.26-3.26-5.8-5.1
		l21.36-11.15c0.13-0.07,0.26-0.13,0.39-0.2c34.76-17.188,35.37-33.938,1.854-50.25l-0.059-3.41V633l-87.35-43.1l-0.104-0.062
		c-15.74-7.47-18.05-18.26-6.896-32.396h0.01l108.24-146.854h11.896L368.553,557.4l-0.146,0.188
		c-7.99,10.58-5.92,18.646,6.18,24.17l0.021-0.02l76.195,37.688v-0.057C500.133,641.77,503.894,664.92,462.124,688.85z" />
      </g>
      <g>

        <image display="none" overflow="visible" enable-background="new" width="273" height="62" id="wastewater_highlighted" xlink:href="img/wastewater_highlighted.png" transform="matrix(1 0 0 1 392.5 716)">
        </image>
        <g>
          <g>
            <path fill="#E2D1C7" d="M656.648,749.545v9.45c-12.771,0-35.604,0.017-48.5,0.05c-8.233,0-16.533,0-24.9,0
				c-20.076,0.051-40.852,0.102-62.324,0.15c-24.354,0.057-44.214-0.562-59.575-1.854c-12.878-1.075-22.595-2.626-29.146-4.646
				l1.725,1.271c-7.979-5.667-15.479-9.409-22.5-11.225l13.025-8.3c5.896,6.138,18.202,10.388,36.896,12.75
				c8.33,1.061,17.938,1.729,28.812,2.05L656.648,749.545z" />
          </g>
        </g>
      </g>
    </g>
    <g id="waterlines_clip" style="opacity: 0">
      <g display="inline">
        <path clip-path="url(#waterline_clip1)" fill="#1CCBD3" d="M972.873,656.4l-5.6-8.854h0.05l-57.854-91.3v0.05l-89.601-141.45v0.062
		c-3.569-5.871-11.529-10.95-23.899-15.25c-0.062-0.03-0.119-0.062-0.146-0.062c-16.4-5.521-24.67-13.6-24.8-24.188v-25.7
		c3.8,3.43,8.67,6.5,14.6,9.2l78.051,36.55c9.5,4.97,17.88,7.562,25.149,7.8c2.5-0.029,4.812-0.029,6.95,0h98.688v-9.85H888.82
		c-6.37,0.062-14.062-2.229-23.062-6.854v-0.05l-0.438-0.2l-0.062-0.05c-0.172-0.07-0.312-0.15-0.5-0.25v0.05l-75.307-35.25
		c-12.021-5.33-18.369-12.95-19-22.85V320h-0.396l0.195-0.15h-0.35v0.25h-5.5V320h-5.45v6.85
		c-0.271,10.438-6.63,18.42-19.104,23.95l-54.604,25.55c-9.33,4.938-17.278,7.37-23.854,7.312c-3.562,0-186.382,0.02-188.438,0.05
		c-0.562,3.104-0.979,6.37-1.197,9.8c2.867-0.021,186.078-0.021,189.646,0c7.359-0.229,15.923-2.896,25.646-8l56.646-26.6
		c6.363-2.9,11.5-6.229,15.396-9.95v26.45c-0.271,14.021,10.271,24.771,31.646,32.25h-0.061c10.172,2.77,17.328,6.77,21.5,12
		l37.31,59.8c-1.104-2.07-2.139-4.021-3.104-5.854l55.442,87.438h-0.062l28.646,45.15h-0.062l34.854,55h0.055
		c0.896,1.5,1.774,2.979,2.646,4.45c28.938,50.1-2.25,77.604-93.562,82.55l-83.188-0.05l-0.396,9.8
		c17.868,0,52.354-0.12,83.445-0.354c99.104-6.062,133.771-36.72,104-91.938C976.644,662.62,974.844,659.53,972.873,656.4z
		 M843.924,468.25c0.029,0.27,0.08,0.55,0.149,0.85C843.604,468.2,843.554,467.92,843.924,468.25z" />
        <path fill="#1CCBD3" clip-path="url(#waterline_clip2)" d="M1079.523,240.75l-71.45,15.75v0.05c-7.729,2.07-14.28,2.85-19.649,2.35c-2.49-0.27-29.53-3.3-81.101-9.1
		c-3.95-0.52-6.771-0.91-8.399-1.15c-3.938-0.63-17.15,2.1-39.62,8.18l0.021,0.02c-56.8,14.23-86.2,31.47-88.2,51.7
		c-10.021-20.08,17.39-38.18,82.25-54.3l-0.55,0.05c25.229-6.77,40.021-10.02,44.351-9.75c3.239,0.17,6.42,0.51,9.55,1l2.649,0.3
		c51.03,5,77.801,7.63,80.301,7.9c4.604,0.57,10.479-0.08,17.649-1.95v-0.05l55.601-12.3L1079.523,240.75z" />
      </g>
      <g display="inline">
        <g>
          <path fill="#E28A64" d="M412.062,741.9l-0.479,0.31c-0.05-0.07-0.11-0.14-0.16-0.21L412.062,741.9z" />
          <path fill="#E28A64" clip-path="url(#waterline_clip3)" d="M1174.523,774.02c-6.86,40.812-62.771,66.73-167.75,77.78c-13.03,1.87-28.08,3-45.15,3.4
			c-1.3,0-2.6,0.02-3.899,0.05H565.824c-0.04,0.021-0.08,0.03-0.13,0.05h-8.229c-0.396-0.03-1.09-0.08-2.08-0.146
			c-0.979-0.08-1.979-0.12-3-0.131c-1,0.011-7.577-0.812-19.729-2.449c-12.17-1.646-23.24-6.2-33.23-13.67l-0.05-4.65V839
			c-26.72-23.56-55.94-55.77-87.66-96.63c-0.04-0.05-0.08-0.104-0.13-0.16l0.479-0.31l12.408-7.9c5.9,6.14,18.2,10.39,36.9,12.75
			c8.33,1.06,17.93,1.74,28.8,2.05l166.5,0.3v9.45c-12.77,0-35.6,0.021-48.5,0.05h-24.899c-20.062,0.062-40.851,0.104-62.312,0.15
			c-24.354,0.06-44.212-0.56-59.57-1.85c-10.5-0.881-18.892-2.07-25.188-3.57c16.769,24.59,37.812,48.42,63.188,71.52v0.2
			c6.87,6.63,12.29,10.84,16.25,12.62c3.979,1.8,9.07,3.45,15.271,4.95c6.228,1.5,17.688,2.74,34.438,3.729H957.2
			c1.5-0.021,2.979-0.062,4.438-0.1c17.312-0.6,32.28-1.729,44.95-3.4v-0.05c102.486-12.41,155.04-38.3,157.646-77.646
			c-1.87-7.312-4.775-14.4-8.729-21.312c-0.021-0.051-0.052-0.101-0.062-0.146c-1.521-2.854-3.229-5.75-5.104-8.7
			c-0.972-1.47-1.947-2.95-2.947-4.45h-0.053l-98.25-141.5h0.053l-95.553-136.85h10.604l92.25,132.1v-0.05l98.25,141.5
			c2.136,3.13,4.021,5.95,5.688,8.45c1.674,2.5,5.104,8.229,10.312,17.2c0.808,1.938,2,4.76,3.604,8.438
			C1175.684,764.57,1175.773,769.29,1174.523,774.02z" />
        </g>
        <g>
          <path fill="#E28A64" clip-path="url(#waterline_clip4)" d="M462.124,688.85l-0.06-0.02l-28.69,16.47l-0.05-2.55h-0.05l-0.062-3c-1.329-1.561-3.26-3.26-5.8-5.1
			l21.36-11.15c0.13-0.07,0.26-0.13,0.39-0.2c34.76-17.188,35.37-33.938,1.854-50.25l-0.059-3.41V633l-87.35-43.1l-0.104-0.062
			c-15.74-7.47-18.05-18.26-6.896-32.396h0.01l108.24-146.854h11.896L368.553,557.4l-0.146,0.188
			c-7.99,10.58-5.92,18.646,6.18,24.17l0.021-0.02l76.195,37.688v-0.057C500.133,641.77,503.894,664.92,462.124,688.85z" />
        </g>
      </g>
    </g>
  <?php } // end if ($user_id !== 2) 
  ?>
  <g id="dropletpaths">
    <path fill="none" d="M1087.5,231c-62.031,31.808-150.163,20.399-215.592,21.004c-30.583,0.283-68.828,8.238-88.581,33.96
	c-9.396,12.234-24.736,13.063-37.828,22.586c-21.508,15.646-50.178-16.044-56-30.211" />
    <path fill="none" d="M717.791,308.55c47.642-12.377,39.261,15.981,19.03,28.956c-12.438,6.918-26.276,29.86-39.375,35.447
	c-15.422,6.577-25.951,10.326-36.992,12.298c-10.151,1.813-170.735,2.125-185.954,1.749" />
    <path fill="none" d="M707.459,267.669C776.5,244.5,749.5,312.787,825.923,371.18c25.043,11.487,43.619,26.354,72.172,27.792
	c19.415,0.984,79-0.271,98.405-0.973" />
    <path fill="none" d="M695.322,277c85.513-19.494,69.422,62.339,70.396,73.385c1.062,11.919-1.146,32.896,7.146,42.604
	c8.438,9.883,28.847,10.604,39.87,18.979c9.965,7.588,13.542,21.283,20.646,31.289c9.562,13.456,18.792,27.154,27.813,40.979
	c18.229,27.938,35.54,56.479,52.762,85.062c17.92,29.744,34.709,60.709,54.438,89.312c14.146,20.5,32.024,46.562,2.896,65.393
	c-17.128,11.07-34.734,22.479-55.318,24.969c-21.664,2.625-79.604,2.622-87.312,3.646c-11.362,1.479-58.812,1.021-70.236,0.46" />
    <path fill="none" d="M477.5,406c-7.146,11.299-99.504,130.429-107.037,141.472c-3.979,5.836-12.479,14.877-12.132,22.562
	c0.753,16.614,38.087,25.542,49.968,31.062c21.312,9.906,42.842,21.107,62.336,34.312c16.312,11.042,19.174,21.555,2.521,34.478
	c-19.021,14.74-52.836,33.229-73.65,45.112" />
    <path fill="none" d="M681.5,754c-49.649-0.564-119.368,1.492-169.018,2.059c-21.733,0.248-42.021-0.645-62.688-7.979
	c-18.162-6.452-43.829-21.186-60.299-31.07" />
    <path fill="none" d="M960.503,454.998c24.503,36.554,115.142,157.993,138.565,195.181c9.896,15.738,19.845,31.417,30.021,46.979
	c10.044,15.349,23.26,29.414,32.445,45.161c21.039,36.062-12.049,67.133-42.188,79.896c-15.92,6.75-31.562,13.127-48.521,16.938
	c-36.825,8.259-78.417,9.874-116.146,9.875c-49.438,0.001-98.666,3.021-148.117,2.979c-26.459-0.021-52.021-4.042-78.188-4.021
	c-36.037,0.021-72.064,0.386-108.099,0.67c-39.244,0.309-94.48,15.181-126.188-16.405c-19.104-19.038-35.896-40.269-52.146-61.753
	c-12.985-17.17-31.162-37.396-48.447-50.506" />
  </g>
  <image overflow="visible" enable-background="new" width="67" height="84" id="windmill_base" xlink:href="img/wind_turbine_base.png" transform="matrix(1 0 0 1 8.25 251.0059)">
  </image>
  <image overflow="visible" enable-background="new" width="43" height="39" id="blades" xlink:href="img/wind_turbine_blades.png" transform="matrix(0.9999 0 0 0.9999 22.252 260.834)">
  </image>

  <?php if (!isset($_GET['ver']) || $_GET['ver'] !== 'kiosk') { ?>
    <image overflow="visible" enable-background="new" width="1584" height="77" id="top_menu" xlink:href="img/menu_top.png">
    </image>
  <?php } ?>
  <image xlink:href="img/squirrel/<?php echo $squirrel_mood; ?>.gif" x="0" y="1" height="206px" width="182px" id="squirrel" />
  <image xlink:href="img/fish/<?php echo $fish_mood; ?>.gif" x="0" y="1" height="206px" width="182px" id="fish" style="opacity:0" />

  <!-- Original ship heightxWidth was 22x44 -->
  <image overflow="visible" enable-background="new" width="55" height="27.5" id="ship" xlink:href="img/ship.png" transform="matrix(0.9999 0 0 0.9999 0 180)">
  </image>
  <?php if ($user_id !== 2) {
    echo ($user_id === 3) ? '<g id="powerlines_back" transform="translate(-110,0)">' : '<g id="powerlines_back">';  ?>
    <g id="powerlines_lit_back" display="none">
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
		M806.402,317c32.895,48.497,94.96,150.995,193.678,127.589" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
		M316.473,210.2c95.25,24.828,188.79,21.278,280.604-10.65" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
		M361.473,213.95c87.483,13.403,174.973,8.82,262.45-13.75" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
		M605.823,215.85c-10,68.402-41.323,124.15-126.322,168.814" />
    </g>
    <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M316.473,210.2
	c95.25,24.828,188.79,21.278,280.604-10.65" />
    <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M361.473,213.95
	c87.483,13.403,174.973,8.82,262.45-13.75" />
    <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M320.073,231.55
	c104.294,25.97,196.227,17.57,275.8-25.2" />
    <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M357.523,231.05
	c87.052,16.438,174.935,7.822,263.646-25.85" />
    <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M802.58,299.089
	c12.529,30.823,87.085,173.328,197.5,145.5" />
    <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M605.823,215.85
	c-10,68.402-41.323,124.15-126.322,168.814" />
    <g id="sparkpaths_back" display="none">
      <!-- <path fill="none" d="M605.823,215.872C602.41,233.617,593,337.506,477.08,384.089" /> -->
      <circle cx="605" cy="215" r="7" fill="#FFF915" />
      <!-- <path fill="none" d="M802.58,299.089c12.529,30.823,87.085,173.328,197.5,145.5" /> -->
      <circle cx="820" cy="320" r="7" fill="#FFF915" />
    </g>
    </g>
  <?php } // end if $user_id !== 2 
  ?>

  <g id="clickables">
    <?php
    $text_pos = array();
    foreach ($db->query("SELECT component, pos, widthxheight, img, attr FROM cwd_landscape_components WHERE hidden = 0 AND user_id = {$user_id} AND component != 'river_click' ORDER BY `order` ASC") as $row) {
      $p = explode(',', str_replace(' ', '', $row['pos']));
      $wh = explode('x', str_replace(' ', '', strtolower($row['widthxheight'])));
      $text_pos[$row['component']] = array($p[0] + $wh[0], $p[1] + ((1 / 3) * $wh[1]));
      echo "<image {$row['attr']} style='cursor:pointer' class='draggable' overflow='visible' enable-background='new    '
        width='{$wh[0]}' height='{$wh[1]}' id='{$row['component']}' xlink:href='{$row['img']}' x='{$p[0]}' y='{$p[1]}'></image>";
      // figure out pos of inside of house based on translating from old pos
      if ($row['component'] === 'houses') {
        $houses_x = $p[0] - 519;
        $houses_y = $p[1] - 584;
      }
    }
    ?>
    <path id="river_click" fill="#2B9CBF" fill-opacity="0" d="M1199.9,235.8c-23.131-0.47-41.08-2-53.854-4.6
  c-12.76-2.6-27.26-4.17-43.5-4.7c-16.26-0.57-34.604-0.9-55.05-1c-14.46-0.1-34.33-0.6-59.604-1.5
  c-25.303-0.9-45.133-0.97-59.5-0.2c-14.396,0.77-33.651,1.08-57.803,0.95c-21.47-0.15-45.3-1.32-71.51-3.52v6.71
  c0.43,0.05,0.87,0.11,1.312,0.16c21.526,2.63,47.728,4.13,78.558,4.5c30.83,0.33,68.55,1,113.146,2
  c18.604,0.63,35.188,1.42,49.75,2.35c12.938,0.8,25.17,1.77,36.7,2.9c4.069,0.37,8.069,0.78,12,1.25c0.229,0,7.649,1,22.25,3
  c14.569,2,33.63,4.87,57.2,8.6c23.521,3.7,52.63,12.6,87.3,26.7v-36.1C1242.17,238.73,1223.03,236.23,1199.9,235.8z M413.9,892.5
  c-65.23-1.67-102.75-14.35-112.55-38.05c-9.8-23.73-28.68-37.97-56.65-42.7c-28-4.73-47.12-13.62-57.35-26.65
  c-10.27-13.062-22.23-21.85-35.9-26.35c-13.7-4.53-25.05-11.45-34.05-20.75c-6.1-6.3-10.15-11.78-12.15-16.45
  c-19.87-29.7-24.83-54.62-14.9-74.75c2.73-17.87-0.07-29.35-8.4-34.45c-9.37-5.77-12.27-13.619-8.7-23.55
  c3.57-9.97,16.25-20.22,38.05-30.75c21.8-10.569,28.15-20.1,19.05-28.6c-9.1-8.53-14.07-15.17-14.9-19.9
  c-0.48-2.646,1.59-5.31,6.23-7.96H67.65c-1.99,3.74-5.44,6.76-10.35,9.062c-8.87,4.1-18.08,6.771-27.65,8.05
  c-9.57,1.27-16.5,3.2-20.8,5.8c-3.33,1.97-5.95,4.87-7.85,8.7v246.85c2,12.37,8,21.62,18,27.75c27.33,13.53,40.67,28.5,40,44.9
  c-1,15,9.87,28.27,32.6,39.8h159.65l-0.05-0.05l0.1,0.05L413.9,892.5L413.9,892.5z M175.4,781.05c-0.03-0.1-0.05-0.2-0.05-0.3
  c0.2,0.4,0.42,0.8,0.65,1.2L175.4,781.05z" />
  </g>

  <?php if ($user_id !== 2) {
    echo ($user_id === 3) ? '<g id="powerlines" transform="translate(-110,0)">' : '<g id="powerlines">';  ?>
    <g id="powerlines_lit" display="none">
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M624.023,200.222c64.021,63.933,133.396,96.833,208.1,98.7" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M836.373,298.321c52.52,149.147,127.502,259.949,224.949,332.401" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M806.402,317" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M479.501,384.664" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M605.823,215.872" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M1022.223,667.371c-27.633,14.104-53.517,24.57-77.646,31.4c-45.363,12.836-84.562,12.836-117.574,0
    c-2.703-1.06-5.361-2.188-7.975-3.4" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M800.773,298.321c43.713,149.958,109.104,260.333,196.188,331.151" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M595.823,200.222c64.017,63.947,133.384,96.848,208.1,98.7" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-miterlimit="10" d="M288.545,893
    c52.598-48.266,105.798-140.229,112.07-173.854" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-miterlimit="10" d="M832.123,298.921
    c14.333-16.166,27.854-31.59,34.854-59.256" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M152.173,302.05c64.789-15.182,134.572-44.548,209.354-88.1" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M140.923,301.4c58.317-18.8,116.633-49.2,174.95-91.2" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M597.077,199.55" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M316.473,210.2" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M623.923,200.2" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M361.473,213.95" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M479.501,384.664" />
      <path display="inline" fill="none" stroke="#FFF915" stroke-width="7" stroke-linejoin="round" stroke-miterlimit="3" d="
    M605.823,215.85" />
    </g>
    <g>
      <g id="shadow_1_" opacity="0.2">
        <g>
          <path d="M358.773,272.5c0.021,0.062-0.021,0.145-0.125,0.25c-0.271,0.055-0.45,0.072-0.556,0.05
        c-0.733-0.575-2.15-0.898-4.25-0.975c-0.32-0.063-2.126-0.038-5.396,0.075c-2.133-0.013-3.674-0.037-4.625-0.075
        c-1.683-0.101-2.961-0.309-3.854-0.625c-0.146-0.042-0.125-0.102,0.075-0.175c0.172-0.106,0.347-0.141,0.525-0.102
        c1.029,0.379,2.654,0.579,4.875,0.602c1.249,0.024,3.174,0.017,5.771-0.025c1.92-0.041,3.351,0.001,4.275,0.125
        C356.972,271.748,358.064,272.039,358.773,272.5z" />
          <path d="M355.148,259.4c-0.017-0.121,0.104-0.214,0.35-0.275c0.3-0.111,1.775-0.561,4.425-1.35
        c1.784-0.61,3.229-1.127,4.325-1.552c0.124-0.089,0.249-0.112,0.375-0.073c0.549,0.393,1.041,0.899,1.475,1.523
        c0.229,0.368,0.562,0.942,1,1.727c0.91-1.537,1.938-3.045,3.075-4.525c-0.474-0.268-0.771-0.668-0.896-1.2
        c-0.042-0.042-0.083-0.083-0.125-0.125h0.646c0.133,0.412,0.354,0.712,0.65,0.9c0.188-0.313,0.438-0.621,0.75-0.925l0.5-0.025
        c-2.049,2.766-4.049,5.766-6,9c-3.146,5.344-5.957,10.86-8.45,16.55l0.325,0.2h-0.45v0.35l-0.1-0.073l-1.9,4.475
        c-0.021,0.123-0.154,0.206-0.4,0.25c-0.104,0-0.188,0-0.25,0c-0.1-0.003-0.104-0.045-0.021-0.125l2.125-4.85
        c-1.34-0.87-2.123-1.361-2.354-1.477c-0.805-0.579-1.388-1.128-1.75-1.648c-1.008-1.156-1.591-1.823-1.75-2
        c-0.373-0.375-0.639-0.65-0.8-0.825c-0.415-0.266-0.824-0.423-1.225-0.477c-0.469-0.073-1.161,0.043-2.075,0.352
        c-0.356,0.097-1.098,0.364-2.225,0.8l-5.025,2c-1.306,0.496-3.164,1.129-5.575,1.9c-0.955,0.279-1.812,0.539-2.575,0.773
        c-1.321,0.43-2.354,0.795-3.1,1.102c-0.291,0.065-0.458,0.065-0.5,0c-0.043-0.115,0.057-0.217,0.3-0.302
        c0.862-0.323,1.961-0.683,3.3-1.073c0.066-0.016,0.133-0.031,0.2-0.052l3.575-1.1c1.464-0.448,3.239-1.107,5.325-1.975
        c2.664-1.103,4.438-1.777,5.325-2.025c1.552-0.592,2.613-0.851,3.194-0.775c0.632,0.004,1.281,0.312,1.95,0.927
        c0.363,0.313,1.022,0.998,1.979,2.05c0.084,0.169,0.268,0.452,0.55,0.85c0.228,0.32,0.48,0.579,0.771,0.775l2.65,1.625
        c2.546-5.622,5.429-11.13,8.647-16.525l1.5-2.425c-0.222,0.062-0.354,0.062-0.397,0c-0.428-0.854-0.729-1.461-0.9-1.825
        c-0.34-0.389-0.651-0.747-0.944-1.075l-0.275-0.273c-0.184-0.048-0.451,0.02-0.8,0.2c-0.943,0.311-2.235,0.744-3.875,1.3
        c-2.11,0.599-3.485,1.048-4.125,1.35C355.348,259.445,355.207,259.438,355.148,259.4z" />
          <path d="M349.948,262.174c1.521-1.306,3.468-2.864,5.825-4.675c2.005-1.621,3.616-2.88,4.85-3.775h0.5
        c-5.262,4.101-8.829,6.926-10.7,8.477c-2.62,2.136-4.603,3.752-5.95,4.85c-2.332,1.835-4.54,3.511-6.625,5.025
        c-1.541,1.065-3.749,2.558-6.625,4.475c-0.397,0.266-0.814,0.54-1.25,0.825c-3.785,2.433-6.521,4.258-8.225,5.475
        c-0.18,0.075-0.363,0.125-0.55,0.15c-0.148-0.068-0.148-0.127,0-0.175c-0.03-0.049-0.02-0.073,0.05-0.075
        c1.692-1.231,4.425-3.064,8.2-5.5c0.62-0.402,1.211-0.785,1.771-1.15c2.623-1.715,4.648-3.063,6.075-4.05
        C340.607,269.66,344.823,266.367,349.948,262.174z" />
          <path d="M355.323,259.75c-0.084-0.102-0.025-0.186,0.175-0.25c0.155-0.077,0.28-0.094,0.375-0.05
        c0.987,0.816,1.654,2.034,2,3.648c3.102-1.414,5.693-2.497,7.775-3.25c0.188-0.084,0.342-0.084,0.444,0
        c-0.002,0.062-0.086,0.121-0.25,0.177c-1.938,0.763-4.558,1.871-7.85,3.323c0.087,0.748,0.254,1.939,0.5,3.575
        c0.283,1.403,0.883,2.545,1.8,3.425c0.026,0.054-0.082,0.137-0.325,0.25c-0.222,0.06-0.338,0.075-0.35,0.052
        c-0.845-0.863-1.429-2.004-1.75-3.427c-0.229-1.639-0.4-2.839-0.525-3.6c-2.927,1.295-4.969,2.178-6.125,2.65
        c-2.391,0.977-4.549,1.817-6.475,2.523c-0.254,0.063-0.388,0.056-0.4-0.023c-0.047-0.109,0.059-0.193,0.306-0.25
        c1.948-0.683,4.114-1.516,6.5-2.5c0.966-0.437,3.021-1.345,6.175-2.727C357.002,261.685,356.335,260.502,355.323,259.75z" />
        </g>
      </g>
      <path opacity="0.2" enable-background="new" d="M322.923,278.299l-0.35,1.5c-0.999,0.899-0.466,1.666,1.6,2.3
    c2.064,0.638,5.281,1.229,9.65,1.775c4.369,0.547,8.886,0.572,13.55,0.075c4.666-0.499,6.982-1.233,6.95-2.2
    c-0.033-1,0.034-1.767,0.2-2.3c0.433-0.102,0.717-0.268,0.85-0.5c0.1-0.102,0.1-0.233,0-0.4c0.033-0.167,0.033-0.3,0-0.398
    l0.05-0.102c0,0.167,0.083,0.25,0.25,0.25c0.133,0.034,0.217-0.033,0.25-0.2l0.15-0.3v-0.05c0.167-0.133,0.3-0.434,0.396-0.9
    c0-0.032,0.033-0.115,0.104-0.25v5.852c-0.271,0.667-0.533,1.398-0.8,2.2l-0.056,0.148c-0.133,0-0.229,0.067-0.3,0.2
    c-0.033,0.133-0.033,0.283,0,0.45v0.05c-0.033-0.1-0.083-0.184-0.146-0.25c-0.104-0.033-0.188-0.017-0.25,0.05
    c-0.033-0.065-0.104-0.1-0.2-0.1c-0.167,0.065-0.25,0.216-0.25,0.45l0.05,0.35h-0.1c-2,0.566-4.533,0.75-7.604,0.55
    c-3.396-0.366-5.933-0.583-7.6-0.648c-4.137-0.066-7.233-0.167-9.303-0.302c-3.564-0.166-6.647-0.55-9.25-1.148h-0.147l-1.3-0.15
    l-0.25-1.648C320.506,280.049,321.79,278.599,322.923,278.299z" />
      <g id="tower_1_">
        <g>

          <path fill="none" stroke="#373435" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="
        M355.973,278.5c-6.104-14.078-10.188-33.262-12.25-57.55" />
          <path fill="#231F20" d="M338.709,164.445c0.051-0.178,0.164-0.228,0.34-0.152c0.151,0.076,0.202,0.203,0.151,0.379
        c-0.378,1.209-0.812,2.961-1.285,5.255c-0.025,0.177-0.139,0.252-0.341,0.229c-0.176-0.051-0.251-0.163-0.229-0.34
        C337.953,167.141,338.406,165.352,338.709,164.445z" />
          <path fill="#231F20" d="M338.406,199.869c-0.104,0.149-0.214,0.163-0.34,0.037c-0.151-0.104-0.182-0.239-0.075-0.416
        c0.302-0.479,0.756-1.046,1.355-1.701c-1.008-0.857-1.765-1.475-2.269-1.854c-0.151-0.101-0.164-0.227-0.038-0.378
        c0.126-0.177,0.252-0.2,0.378-0.075l2.344,1.891c0.435-0.453,1.122-1.084,2.079-1.891c0.126-0.125,0.252-0.113,0.378,0.039
        c0.104,0.176,0.076,0.313-0.07,0.414c-0.812,0.655-1.468,1.262-1.972,1.814l1.438,1.361c0.126,0.126,0.126,0.252,0,0.378
        c-0.126,0.149-0.252,0.164-0.378,0.037l-1.479-1.36C339.288,198.696,338.835,199.263,338.406,199.869z" />
          <path fill="#231F20" d="M342.792,206.938c0.101,0.15,0.076,0.29-0.075,0.415c-0.146,0.103-0.271,0.076-0.378-0.075
        c-0.58-0.855-1.396-1.713-2.458-2.57l-2.798,2.76c-0.126,0.127-0.265,0.127-0.416,0c-0.126-0.125-0.126-0.252,0-0.378
        l2.76-2.722c-0.58-0.428-1.475-1.021-2.685-1.777c-0.151-0.1-0.189-0.227-0.113-0.377c0.076-0.177,0.202-0.215,0.378-0.114
        c1.512,0.983,2.458,1.602,2.835,1.854c0.958-1.008,1.664-1.789,2.117-2.344c0.101-0.15,0.239-0.164,0.416-0.037
        c0.151,0.125,0.164,0.251,0.038,0.377c-0.605,0.73-1.312,1.524-2.117,2.382C341.38,205.185,342.212,206.055,342.792,206.938z" />
          <path fill="#231F20" d="M341.771,208.261c0.101-0.151,0.227-0.164,0.378-0.039c0.146,0.103,0.176,0.229,0.07,0.379
        c-0.428,0.605-1.122,1.322-2.08,2.154c1.109,0.883,1.884,1.777,2.312,2.686c0.076,0.177,0.021,0.29-0.151,0.341
        c-0.177,0.075-0.303,0.037-0.378-0.113c-0.428-0.908-1.159-1.752-2.188-2.533c-1.033,0.933-1.729,1.612-2.08,2.041
        c-0.125,0.126-0.252,0.14-0.378,0.037c-0.126-0.125-0.144-0.264-0.038-0.415c0.454-0.58,1.134-1.248,2.042-2.004
        c-0.63-0.454-1.109-0.806-1.438-1.059c-0.555-0.403-0.996-0.77-1.32-1.098c-0.127-0.125-0.142-0.264-0.035-0.416
        c0.125-0.125,0.251-0.125,0.378,0c0.176,0.152,1.121,0.884,2.835,2.193C340.649,209.583,341.342,208.866,341.771,208.261z" />
          <path fill="#231F20" d="M329.22,198.28c0.151,0.101,0.189,0.228,0.113,0.378c-0.076,0.177-0.202,0.227-0.378,0.151
        c-0.479-0.253-1.034-0.594-1.664-1.021c-0.706-0.479-1.248-0.844-1.625-1.097c-0.151-0.102-0.164-0.228-0.038-0.378
        c0.125-0.15,0.265-0.177,0.416-0.076C327.531,197.272,328.589,197.953,329.22,198.28z" />
          <path fill="#231F20" d="M333.643,198.318c0.126,0.126,0.126,0.265,0,0.416c-0.126,0.149-0.252,0.164-0.378,0.037
        c-1.084-1.083-1.985-2.055-2.722-2.91c-0.101-0.127-0.076-0.252,0.076-0.379c0.176-0.102,0.312-0.089,0.416,0.039
        C331.639,196.302,332.509,197.234,333.643,198.318z" />
          <path fill="#231F20" d="M347.442,198.469c0.176,0.075,0.227,0.201,0.146,0.378c-0.101,0.151-0.239,0.201-0.416,0.151
        c-1.104-0.403-2.143-1.046-3.1-1.929c-0.126-0.125-0.126-0.265,0-0.416c0.126-0.15,0.252-0.163,0.378-0.037
        C345.363,197.5,346.358,198.117,347.442,198.469z" />
          <path fill="#231F20" d="M351.222,198.658c0.182,0.102,0.229,0.227,0.151,0.379c-0.076,0.176-0.202,0.227-0.378,0.149
        c-0.554-0.276-0.958-0.54-1.209-0.793c-0.126-0.127-0.126-0.266,0-0.416c0.126-0.151,0.252-0.163,0.378-0.038
        C350.365,198.167,350.718,198.406,351.222,198.658z" />
          <path fill="#231F20" d="M332.698,174.576c0.146,0.102,0.164,0.228,0.038,0.379c-0.126,0.15-0.271,0.176-0.416,0.075
        l-1.896-1.323c-0.146-0.101-0.164-0.227-0.038-0.378c0.104-0.151,0.229-0.177,0.378-0.075L332.698,174.576z" />
          <path fill="#231F20" d="M346.156,174.387c0.146,0.104,0.164,0.239,0.038,0.416c-0.126,0.151-0.251,0.163-0.378,0.038
        c-0.812-0.58-1.562-0.87-2.271-0.87c-0.176-0.023-0.277-0.126-0.302-0.302c0-0.202,0.088-0.29,0.265-0.266
        C344.367,173.404,345.249,173.732,346.156,174.387z" />
          <path fill="#231F20" d="M349.786,175.371c0.151,0.101,0.164,0.214,0.038,0.34c-0.126,0.151-0.271,0.176-0.417,0.076
        c-0.781-0.504-1.297-0.959-1.55-1.361c-0.104-0.15-0.076-0.276,0.07-0.379c0.151-0.125,0.277-0.113,0.378,0.038
        C348.588,174.462,349.08,174.892,349.786,175.371z" />
          <path fill="#231F20" d="M329.976,218.619c0.183,0.051,0.22,0.164,0.114,0.341c-0.076,0.177-0.202,0.239-0.378,0.188
        c-0.68-0.277-1.222-0.693-1.625-1.247c-0.101-0.151-0.076-0.276,0.076-0.378c0.151-0.126,0.277-0.113,0.378,0.036
        C328.892,218.064,329.371,218.417,329.976,218.619z" />
          <path fill="#231F20" d="M332.433,218.808c0.182,0.052,0.229,0.164,0.151,0.341c-0.075,0.178-0.202,0.239-0.378,0.188
        c-0.605-0.227-1.134-0.565-1.587-1.021c-0.126-0.126-0.139-0.253-0.038-0.378c0.126-0.152,0.252-0.164,0.378-0.038
        C331.438,218.33,331.929,218.632,332.433,218.808z" />
          <path fill="#231F20" d="M320.273,231l-0.104,1.25C320.673,231.516,320.706,231.099,320.273,231z M343.473,220.75
        c-3.194,0-5.934-0.05-8.194-0.15c-0.5,5.167-1,9.052-1.5,11.65c-1.5,8.367-2.473,14-2.9,16.9c-0.533,3.731-0.967,6.55-1.3,8.448
        c-0.5,3.066-1.083,5.834-1.75,8.302c-0.5,1.967-1.367,4.75-2.604,8.35c-0.479,1.381-0.9,2.631-1.271,3.75
        c0.203-0.148,0.481-0.324,0.85-0.525c0.881-0.482,1.811-1.059,2.775-1.725l2.45-1.7c1-0.7,2.1-1.716,3.3-3.05
        c1.6-1.733,2.684-2.834,3.25-3.3c1.034-0.9,1.833-1.317,2.396-1.25c0.667,0.065,1.556,0.55,2.65,1.45
        c0.7,0.532,1.917,1.6,3.65,3.198c0.267,0.269,0.683,0.7,1.25,1.302c0.5,0.5,0.944,0.883,1.35,1.148l3.75,2.4
        c-2.2-8.634-4.067-17.467-5.6-26.5l-0.65-4.35c-0.167,0.102-0.3,0.084-0.4-0.05c-1.167-1.5-2.05-2.616-2.646-3.35
        c-0.633-0.7-1.283-1.366-1.95-2l-0.55-0.5c-0.167-0.102-0.367-0.018-0.604,0.25c-0.633,0.667-1.562,1.466-2.8,2.398
        c-1.533,1.135-2.533,1.917-3,2.352c-0.133,0.1-0.267,0.083-0.396-0.05c-0.104-0.2-0.067-0.352,0.1-0.45
        c0.233-0.2,1.316-1.018,3.25-2.45c1.3-1.033,2.283-1.967,2.95-2.8c0.1-0.102,0.229-0.116,0.396-0.05
        c0.938,0.731,1.917,1.684,2.95,2.85c0.604,0.7,1.467,1.783,2.604,3.25c-0.438-2.8-0.771-5.584-1-8.35
        c-0.7-0.5-1.4-1.283-2.104-2.352c-0.967-1.366-1.6-2.216-1.896-2.55c-0.306-0.367-0.533-0.5-0.7-0.4
        c-0.133,0.066-0.367,0.269-0.7,0.602l-1.45,1.3c-1.367,1.2-2.433,2.217-3.2,3.05c-0.1,0.102-0.229,0.102-0.396,0
        c-0.133-0.134-0.15-0.267-0.05-0.399c0.694-0.734,1.646-1.635,2.85-2.7c1.467-1.3,2.436-2.185,2.9-2.65
        c0.1-0.1,0.229-0.1,0.396,0c0.567,0.5,1.283,1.384,2.15,2.65c0.833,1.267,1.534,2.148,2.1,2.648
        C343.706,230.65,343.473,225.783,343.473,220.75z M335.173,215.549l-0.6,0.2c-0.5,0.166-1.233,0.283-2.2,0.35
        c-1.133,0.102-1.9,0.185-2.3,0.25c-1.271,0.269-2.816,0.65-4.65,1.15c-1,0.334-2.517,0.8-4.55,1.4
        c5.233,0.532,9.85,0.898,13.85,1.1L335.173,215.549z M342.023,215.4l0.146-0.052c-0.896-0.166-1.7-0.266-2.396-0.3
        c-1.5-0.033-2.833,0.083-4,0.352l-0.45,4.6c2.267,0.133,4.979,0.2,8.146,0.2c0-1.3,0.033-2.616,0.104-3.95h-0.05
        c-0.104,0.033-0.2,0-0.306-0.1c-0.1-0.066-0.111-0.167-0.05-0.302l0.104-0.198l-0.95-0.25c0.1,0.065,0.146,0.148,0.146,0.25
        c0,0.1-0.05,0.166-0.146,0.198c-0.733,0.468-1.4,0.917-2,1.352c0.8,0.6,1.617,1.35,2.45,2.25c0.133,0.167,0.133,0.3,0,0.398
        c-0.167,0.102-0.317,0.084-0.45-0.05c-0.733-0.866-1.566-1.616-2.5-2.25l-1.854,1.352c-0.133,0.1-0.25,0.083-0.35-0.052
        c-0.133-0.166-0.117-0.3,0.05-0.398l1.7-1.2c-0.771-0.467-1.55-0.783-2.353-0.95c-0.197,0-0.28-0.116-0.25-0.35
        c0.064-0.167,0.186-0.233,0.353-0.2c0.897,0.166,1.8,0.55,2.7,1.15C340.456,216.433,341.19,215.933,342.023,215.4z
         M344.173,215.849c-0.1,1.435-0.146,2.867-0.146,4.302c3.694-0.033,7.133-0.2,10.3-0.5c-0.938-0.302-2.617-0.95-5.05-1.95
        C347.106,216.833,345.406,216.216,344.173,215.849z M342.873,200.799l-6.5-0.1v0.85c-0.033,1.435-0.233,3.65-0.6,6.65
        c-0.306,2.898-0.473,5.116-0.5,6.648c0.027-0.166,0.111-0.25,0.25-0.25c0.133,0,0.217,0.084,0.25,0.25
        c1.167-0.266,2.361-0.383,3.6-0.35c1.3,0.033,2.7,0.233,4.2,0.6l0.05-0.148c0.066-0.134,0.2-0.185,0.4-0.15
        c-0.067-1.866-0.233-4.2-0.5-7C343.09,203.166,342.873,200.833,342.873,200.799z M343.073,194.75c0,1.633,0.1,3.45,0.3,5.45
        l9.05-0.05c0.604,0,1.567-0.066,2.9-0.2c1.267-0.167,2.25-0.25,2.95-0.25c-1.473-0.435-4-1.268-7.604-2.5
        C347.473,196.099,344.94,195.283,343.073,194.75z M338.573,190.549c-0.533-0.634-1.083-1.1-1.65-1.398l-0.05,3.1
        L338.573,190.549z M341.223,194.25h-0.146v-0.05c-0.066,0-0.104-0.034-0.104-0.102l-0.05-0.198l-4.5,0.35l-0.05,5.9l6.4,0.05
        c-0.167-2-0.271-3.817-0.306-5.45v-0.15L341.223,194.25z M340.623,193.349c-0.467-0.833-1.017-1.633-1.65-2.398l-2.146,2.148
        l-0.05,0.551L340.623,193.349z M337.173,183.599l-0.146,2.45c0.194-0.5,0.583-1.065,1.146-1.7L337.173,183.599z M337.173,187.4
        c-0.033,0.1-0.1,0.166-0.2,0.198v0.901c0.7,0.4,1.367,0.934,2,1.6c0.7-0.731,1.306-1.5,1.806-2.3
        c0.1-0.134,0.25-0.167,0.444-0.1c0.167,0.065,0.2,0.183,0.104,0.35c-0.466,0.768-1.117,1.602-1.95,2.5
        c0.867,1,1.55,2.067,2.05,3.2l1,0.25c-0.167-2.033-0.3-4.283-0.396-6.75c-0.033,0.033-0.104,0.017-0.2-0.05
        c-0.8-0.7-1.867-1.534-3.2-2.5C337.756,185.632,337.273,186.533,337.173,187.4z M337.973,178.4l-0.444,2.25l1.694-1.552
        C338.79,178.799,338.373,178.566,337.973,178.4z M341.973,181.549c-0.062,0.034-0.133-0.017-0.194-0.148
        c-0.5-0.734-1.188-1.4-2.056-2l-2.396,2.148l-0.15,1.4h0.05l1.354,1c0.633-0.602,1.467-1.25,2.5-1.95
        c0.167-0.1,0.3-0.084,0.396,0.05c0.104,0.167,0.066,0.3-0.1,0.4c-0.934,0.634-1.7,1.25-2.3,1.85
        c1.133,0.866,2.117,1.65,2.95,2.352L341.973,181.549z M341.473,177.5c0.104,0.133,0.066,0.267-0.1,0.4l-1.25,1.1
        c0.767,0.533,1.383,1.133,1.85,1.8l0.15-3.898c0-0.135,0.083-0.234,0.25-0.302l-3.9-0.05l-0.05,0.102
        c-0.2,0.698-0.3,1.1-0.3,1.198c0.566,0.269,1.083,0.533,1.55,0.802l1.4-1.25C341.206,177.266,341.34,177.299,341.473,177.5z
         M338.373,170.7c-0.567,1.232-0.917,2.933-1.05,5.1l4.8,0.15c-0.133-2.233-0.2-3.9-0.2-5c-0.267-0.067-0.517-0.101-0.75-0.101
        C341.14,170.849,340.206,170.799,338.373,170.7z M337.773,170.7h-0.604c-1.562,0.533-3.583,1.134-6.05,1.8
        c-3.733,0.967-5.769,1.5-6.103,1.6c0.334,0.801,4.25,1.367,11.75,1.7l0.103-0.898h-0.15c-1.028-0.533-1.812-1.15-2.35-1.852
        c-0.133-0.1-0.117-0.216,0.05-0.35c0.167-0.102,0.3-0.083,0.4,0.05c0.434,0.566,1.115,1.084,2.05,1.55
        C337.006,172.867,337.306,171.666,337.773,170.7z M354.973,176.049c-0.062,0.167-0.194,0.217-0.396,0.15
        c-0.938-0.366-2.383-1.018-4.354-1.95c-1.934-0.834-3.417-1.467-4.444-1.9c-0.7-0.3-1.233-0.532-1.604-0.698
        c-0.667-0.269-1.229-0.468-1.7-0.602c0,1.102,0.066,2.733,0.2,4.9l-0.05,0.05c2.333,0.033,5.983,0.05,10.95,0.05
        c0.167,0,0.267,0.102,0.3,0.3c0,0.167-0.083,0.25-0.25,0.25h-11.15c0.139,0.066,0.2,0.167,0.2,0.302
        c-0.229,6.633-0.133,12.383,0.3,17.25c1.9,0.532,5.066,1.55,9.5,3.05c4.033,1.398,7.2,2.384,9.5,2.95
        c0.2,0.065,0.283,0.184,0.25,0.35c-0.062,0.2-0.184,0.283-0.35,0.25l-0.5-0.15c-0.1,0.135-0.2,0.167-0.3,0.102
        c-0.8-0.268-1.733-0.4-2.8-0.4c-0.533,0-1.483,0.067-2.854,0.2c-1.333,0.166-2.979,0.25-4.95,0.25l-7.05,0.05
        c0.133,1.534,0.354,3.866,0.65,7c0.267,2.833,0.434,5.2,0.5,7.102c0,0.231-0.135,0.315-0.4,0.25v0.1
        c1.233,0.367,3.317,1.133,6.25,2.3c2.633,1.102,4.717,1.817,6.25,2.15c0.2,0.065,0.271,0.183,0.2,0.35
        c0,0.2-0.102,0.284-0.3,0.25l-0.95-0.25l-0.15,0.05c-3.396,0.367-7.217,0.584-11.444,0.65c0,8.8,0.883,18.533,2.646,29.2
        c1.533,8.933,3.417,17.75,5.65,26.45l0.198,0.5c0.04-0.006,0.084-0.016,0.125-0.025c-0.022,0.182-0.02,0.424,0.021,0.725
        l-0.146-0.398l1.696,6.35c0.066,0.167,0,0.284-0.196,0.352c-0.166,0.065-0.281,0-0.354-0.2l-1.85-6.95
        c-1.867-1.232-2.95-1.95-3.25-2.149c-1.2-0.833-2.2-1.649-3-2.449c-1.867-1.7-2.983-2.717-3.354-3.051
        c-0.6-0.533-1.083-0.935-1.448-1.199c-0.602-0.435-1.135-0.717-1.602-0.852c-0.5-0.1-1.064,0.102-1.7,0.602
        c-0.229,0.166-0.699,0.6-1.396,1.3l-3,3.149c-0.771,0.801-1.983,1.782-3.65,2.949c-1.764,1.233-3.172,2.151-4.225,2.75
        c-0.686,0.387-1.146,0.61-1.4,0.676c-0.562,1.68-0.987,3.038-1.272,4.074c-0.03,0.167-0.147,0.25-0.353,0.25
        c-0.167-0.065-0.229-0.183-0.2-0.35c0.5-1.8,1.383-4.583,2.65-8.352c1.267-3.6,2.146-6.365,2.646-8.3
        c1-3.898,2.021-9.466,3.055-16.7c0.333-2.332,0.8-5.165,1.396-8.5c0.667-3.698,1.167-6.5,1.5-8.398
        c0.5-2.866,1-6.733,1.5-11.602c-4.333-0.203-8.979-0.604-13.95-1.203c0.067,0.1,0.067,0.217,0,0.35
        c0.033,0.066,0.033,0.133,0,0.2c-0.467,1.366-0.683,3.083-0.646,5.148c0,1.167,0.05,2.935,0.146,5.302
        c0.306,0.032,0.567,0.083,0.806,0.148c0.1,0.102,0.146,0.217,0.146,0.352c-0.133,0.633-0.25,1.083-0.35,1.35
        c-0.2,0.467-0.5,0.834-0.9,1.1c-0.1,0.102-0.217,0.102-0.35,0c-0.104-0.065-0.135-0.166-0.104-0.3
        c0.104-0.866,0.15-1.667,0.15-2.398v-0.102c-0.066-2.333-0.103-4.1-0.103-5.3c-0.031-2.066,0.168-3.8,0.603-5.2h-0.05
        c-0.104-0.167-0.104-0.3,0-0.398l0.3-0.25l-0.5-0.052c-0.167,0-0.25-0.083-0.25-0.25c-0.103-0.032-0.15-0.1-0.15-0.198
        c-0.029-0.167,0.021-0.269,0.15-0.302c0.7-0.166,2.35-0.633,4.95-1.398c1.967-0.634,3.6-1.083,4.896-1.352
        c1.167-0.231,2.9-0.5,5.2-0.8c0-1.533,0.167-3.767,0.5-6.7c0.367-3.032,0.55-5.3,0.55-6.8v-0.85
        c-7.333-0.233-13.529-0.935-18.6-2.102c-0.033,1-0.15,2.602-0.354,4.802c-0.198,2.065-0.333,3.666-0.396,4.8
        c0.167-0.102,0.281-0.083,0.35,0.05c0.333,0.6,0.483,1.066,0.45,1.4c-0.034,0.398-0.333,0.766-0.896,1.1
        c-0.064,0.066-0.148,0.066-0.25,0c-0.104-0.033-0.167-0.1-0.198-0.2c-0.033-1.565,0.05-3.583,0.25-6.05
        c0.333-3.5,0.517-5.5,0.55-6v-0.05l-0.75-0.15c-0.2-0.065-0.269-0.2-0.2-0.398c0.033-0.167,0.133-0.234,0.3-0.2
        c5.438,1.398,12.021,2.215,19.75,2.449l0.054-5.852c-5,0.5-11.47,1.634-19.398,3.4c-0.2,0.065-0.3,0-0.3-0.2
        c-0.033-0.2,0.03-0.316,0.198-0.35c7.969-1.769,14.479-2.917,19.552-3.45c0.062-0.134,0.183-0.185,0.35-0.15
        c0.066-0.966,0.133-2.866,0.2-5.7c0.065-2.434,0.167-4.35,0.3-5.75c-0.1,0.033-0.2,0-0.3-0.1c-0.066-0.133-0.05-0.25,0.05-0.35
        l0.4-0.352c0.1-1.033,0.28-2.065,0.55-3.1l-0.25-0.05c-0.167-0.066-0.217-0.2-0.15-0.4c0-0.166,0.104-0.233,0.305-0.2l0.25,0.15
        c0.062-0.435,0.185-0.833,0.351-1.2h-0.601v0.4c0,0.166-0.083,0.266-0.25,0.3c-0.199,0-0.305-0.102-0.305-0.3v-0.45
        c-8.362-0.366-12.479-1.233-12.35-2.602v-0.05l0.1-0.148h0.055l0.051-0.052c0.699-0.198,2.767-0.75,6.199-1.648
        c2.529-0.634,4.583-1.217,6.146-1.75c0.066-0.034,0.117-0.034,0.15,0l0.1-0.05c1.8,0,3.15,0.032,4.05,0.1
        c0.138,0.033,0.333,0.084,0.604,0.15c0-2.2-0.167-4.302-0.5-6.302c-0.271,0.069-0.688,0.104-1.25,0.104
        c-0.604,0-1.051,0.017-1.354,0.05c-0.167,0.066-0.267,0-0.3-0.2c0-0.2,0.083-0.312,0.25-0.35c0.633-0.067,1.583-0.134,2.854-0.2
        c0.195,0,0.301,0.084,0.301,0.25c0.366,1.9,0.562,4.117,0.6,6.65v0.1c0.604,0.133,1.617,0.517,3.05,1.15
        c2.104,0.833,5.223,2.184,9.354,4.05C355.04,175.766,355.073,175.882,354.973,176.049z" />
          <path fill="#231F20" d="M344.909,218.355c-0.151-0.076-0.189-0.202-0.113-0.38c0.075-0.176,0.202-0.214,0.378-0.112
        c0.277,0.151,0.605,0.364,0.983,0.643l0.939,0.719c0.151,0.103,0.182,0.228,0.076,0.379c-0.126,0.15-0.265,0.177-0.416,0.074
        C345.98,219.048,345.363,218.607,344.909,218.355z" />
          <path fill="#231F20" d="M349.899,218.998c0.176,0.075,0.214,0.201,0.107,0.377c-0.076,0.178-0.188,0.229-0.341,0.152
        c-0.554-0.304-0.907-0.593-1.058-0.869c-0.076-0.178-0.057-0.316,0.071-0.416c0.15-0.103,0.276-0.076,0.381,0.075
        C349.218,218.594,349.496,218.821,349.899,218.998z" />
          <path fill="#231F20" d="M339.238,225.462c0.756-0.58,1.317-1.033,1.701-1.361c0.63-0.555,1.083-1.07,1.355-1.55
        c0.104-0.15,0.239-0.188,0.416-0.113c0.176,0.075,0.214,0.188,0.113,0.341c-0.277,0.479-0.731,1.009-1.361,1.588
        c-0.378,0.302-0.919,0.743-1.625,1.323l-0.146,0.15l0.038,0.038c1.386,1.185,2.527,2.344,3.438,3.479
        c0.126,0.126,0.114,0.252-0.038,0.378c-0.176,0.102-0.312,0.075-0.416-0.076c-0.882-1.059-2.042-2.205-3.478-3.439
        c-1.462,1.234-2.766,2.219-3.896,2.949c-0.177,0.1-0.312,0.075-0.416-0.076c-0.101-0.15-0.076-0.276,0.076-0.378
        c0.982-0.604,2.243-1.562,3.78-2.873c-1.264-1.108-2.319-1.827-3.179-2.154c-0.179-0.052-0.229-0.164-0.148-0.341
        c0.076-0.177,0.202-0.239,0.378-0.188C336.794,223.509,337.927,224.277,339.238,225.462z" />
          <path fill="#231F20" d="M344.273,245.25c0.133-0.1,0.25-0.1,0.35,0c0.167,0.133,0.183,0.267,0.05,0.4
        c-1.3,1.3-2.967,3.184-5,5.648c0.867,1.366,2.133,3.435,3.8,6.2c1.533,2.367,3.066,4.233,4.604,5.6
        c0.133,0.102,0.133,0.234,0,0.4c-0.133,0.133-0.25,0.15-0.354,0.05c-1.528-1.366-3.062-3.216-4.6-5.55
        c-1.667-2.8-2.934-4.867-3.8-6.2c-1.733,2.2-2.966,3.7-3.7,4.5c-1.43,1.663-2.789,3.098-4.075,4.3
        c-1.292,1.21-2.343,2.085-3.146,2.625c-0.812,0.541-0.222-0.134,1.771-2.023c1.997-1.897,3.714-3.682,5.15-5.352
        c0.562-0.698,1.8-2.217,3.7-4.55c-1.833-2.898-3.688-5.083-5.556-6.55c-0.133-0.133-0.146-0.267-0.05-0.4
        c0.133-0.133,0.271-0.148,0.4-0.05c1.863,1.5,3.717,3.685,5.55,6.55C341.24,248.483,342.873,246.617,344.273,245.25z" />
          <path fill="#231F20" d="M348.462,266.519c0.151,0.125,0.164,0.253,0.038,0.378c-0.126,0.151-0.265,0.163-0.417,0.037
        c-1.184-0.855-2.797-1.349-4.839-1.474c-0.327-0.025-2.054,0-5.179,0.075c-2.021,0.05-3.528,0.038-4.537-0.038
        c-1.688-0.126-3.15-0.453-4.391-0.982c-0.177-0.075-0.214-0.201-0.107-0.379c0.076-0.147,0.202-0.201,0.378-0.147
        c1.461,0.63,3.271,0.97,5.438,1.021c1.266,0.024,3.125-0.013,5.601-0.113c1.812-0.024,3.201,0.038,4.159,0.188
        C346.144,265.308,347.429,265.788,348.462,266.519z" />
          <path fill="#231F20" d="M323.095,183.612l0.038,1.89c0.454-0.402,0.454-1.021,0-1.852L323.095,183.612z M323.738,174.425
        c0.126-0.127,0.265-0.127,0.416,0c0.146,0.125,0.164,0.252,0.038,0.378c-0.561,0.682-0.908,1.639-1.062,2.873
        c-0.075,0.556-0.113,1.575-0.113,3.062l0.076,2.686c0.076-0.076,0.164-0.127,0.271-0.151c0.126,0,0.227,0.062,0.302,0.188
        c0.328,0.605,0.429,1.146,0.302,1.625c-0.101,0.529-0.454,0.935-1.058,1.211c-0.252,0.075-0.378-0.038-0.378-0.341
        c0.051-0.731,0.051-1.827,0-3.289c-0.056-1.487-0.063-2.597-0.038-3.327c0.05-1.159,0.126-2.016,0.227-2.569
        C322.919,175.811,323.259,175.03,323.738,174.425z" />
          <path fill="#231F20" d="M354.625,176.958c-0.429,1.361-0.706,3.024-0.832,4.991c-0.101,1.436-0.088,2.52,0.038,3.251
        c0.051,0.527,0.164,0.919,0.34,1.172c0.303,0.403,0.277,0.276-0.076-0.378c-0.101-0.152-0.076-0.277,0.076-0.379
        c0.177-0.125,0.315-0.113,0.416,0.038c0.277,0.429,0.429,0.654,0.454,0.681c0.076,0.201,0.051,0.453-0.076,0.756
        c-0.05,0.103-0.126,0.151-0.227,0.151c-1.312,0-1.878-1.524-1.701-4.574c0.151-2.117,0.491-4.058,1.021-5.822
        c0.056-0.177,0.182-0.238,0.378-0.188C354.612,176.681,354.675,176.782,354.625,176.958z" />
          <path fill="#231F20" d="M361.165,212.646l0.188,1.284C361.556,213.478,361.493,213.049,361.165,212.646z M361.656,201.153
        c0.177,0.074,0.227,0.202,0.146,0.378c-1.008,2.496-1.229,6.088-0.681,10.774c0.051-0.102,0.126-0.164,0.227-0.188
        c0.104,0,0.189,0.051,0.271,0.15c0.328,0.555,0.479,1.021,0.454,1.399c-0.025,0.428-0.271,0.844-0.723,1.247
        c-0.102,0.075-0.188,0.088-0.266,0.038c-0.101-0.051-0.164-0.127-0.188-0.229c-0.403-2.822-0.618-5.079-0.646-6.767
        c-0.021-2.672,0.328-4.902,1.062-6.691C361.367,201.09,361.48,201.052,361.656,201.153z" />
          <path fill="#231F20" d="M355.721,225.726c0.057-0.452,0.252-1.989,0.605-4.61c0.05-0.177,0.164-0.252,0.34-0.229
        c0.202,0.025,0.277,0.126,0.227,0.304c-0.378,2.798-0.574,4.359-0.604,4.688c-0.126,1.94,0.088,3.49,0.643,4.649
        c0.454,0.15,0.693,0.566,0.725,1.247c0.021,0.127-0.026,0.215-0.151,0.266c-0.126,0.05-0.24,0.024-0.341-0.076
        c-0.201-0.252-0.416-0.566-0.646-0.945l-0.523-0.074c-0.182-0.025-0.24-0.14-0.189-0.342c0.025-0.176,0.126-0.252,0.306-0.227
        h0.148C355.772,229.167,355.595,227.617,355.721,225.726z" />
          <g>

            <path fill="none" stroke="#373435" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M329.423,276.25c2.539-13.859,4.406-32.109,5.604-54.75" />
          </g>
        </g>
      </g>
    </g>
    <g>
      <g id="Layer_2_3_">
        <g>
          <g id="Layer_1_8_">
            <polygon fill="#FFFFFF" points="1016.248,688.581 1016.248,664.024 1034.167,664.024 1034.167,688.581           " />
            <g>
              <path d="M1034.167,688.858c-0.184,0-0.332-0.124-0.332-0.277v-24.278h-17.255v24.278c0,0.153-0.148,0.277-0.332,0.277
            c-0.183,0-0.332-0.124-0.332-0.277v-24.557c0-0.153,0.149-0.278,0.332-0.278h17.919c0.183,0,0.331,0.125,0.331,0.278v24.557
            C1034.498,688.734,1034.35,688.858,1034.167,688.858z" />
            </g>
            <g>
              <path fill="#FFFFFF" d="M1018.794,662.396c1.771-0.396,3.862-0.604,6.283-0.604c2.417,0,4.483,0.199,6.192,0.604
            c1.771,0.395,2.646,0.854,2.646,1.396c0,0.545-0.886,1.021-2.646,1.408c-1.709,0.396-3.775,0.594-6.192,0.594
            c-2.421,0-4.513-0.188-6.283-0.594c-1.71-0.396-2.565-0.863-2.565-1.408C1016.229,663.261,1017.084,662.79,1018.794,662.396z" />
              <g>
                <path d="M1025.077,666.083c-2.446,0-4.59-0.203-6.369-0.603c-1.918-0.443-2.811-0.979-2.811-1.688s0.893-1.229,2.809-1.677
              c1.798-0.4,3.94-0.604,6.371-0.604c2.433,0,4.546,0.202,6.281,0.604c1.978,0.438,2.896,0.974,2.896,1.677
              c0,0.702-0.922,1.235-2.896,1.688C1029.639,665.88,1027.525,666.083,1025.077,666.083z M1025.077,662.08
              c-2.373,0-4.458,0.196-6.197,0.584c-2.093,0.482-2.319,0.958-2.319,1.14c0,0.188,0.229,0.656,2.321,1.146
              c1.722,0.388,3.812,0.584,6.195,0.584s4.442-0.196,6.104-0.584c2.188-0.485,2.412-0.959,2.412-1.146
              c0-0.182-0.229-0.646-2.407-1.14C1029.504,662.276,1027.45,662.08,1025.077,662.08z" />
              </g>
            </g>
            <g>
              <path fill="#FFFFFF" d="M1016.358,687.47h17.477c0.072,0.119,0.11,0.244,0.11,0.371c0,0.496-0.896,0.959-2.655,1.39
            c-1.693,0.371-3.76,0.562-6.193,0.562c-2.433,0-4.534-0.188-6.305-0.562c-1.697-0.433-2.544-0.896-2.544-1.39
            C1016.248,687.714,1016.286,687.589,1016.358,687.47z" />
              <g>
                <path d="M1025.097,690.064c-2.438,0-4.6-0.19-6.385-0.564c-1.912-0.486-2.796-1.013-2.796-1.659
              c0-0.168,0.051-0.339,0.146-0.497c0.07-0.137,0.271-0.188,0.438-0.122c0.163,0.069,0.229,0.236,0.146,0.374
              c-0.061,0.08-0.074,0.165-0.074,0.245c0,0.125,0.166,0.578,2.309,1.124c1.729,0.359,3.812,0.543,6.208,0.543
              c2.398,0,4.454-0.184,6.109-0.546c2.229-0.544,2.407-0.996,2.407-1.121c0-0.082-0.025-0.164-0.074-0.245
              c-0.083-0.138-0.021-0.305,0.146-0.374c0.163-0.062,0.362-0.015,0.445,0.122c0.098,0.16,0.146,0.327,0.146,0.497
              c0,0.649-0.923,1.177-2.896,1.656C1029.664,689.874,1027.552,690.064,1025.097,690.064z" />
              </g>
            </g>
          </g>
        </g>
      </g>
      <g id="Layer_1_5_">
        <g opacity="0.2">
          <path d="M1150.1,742.4v0.188l-1.396,1.562c0.3-0.033,0.449,0.062,0.449,0.3c-0.104-0.034-0.116,0-0.062,0.1
        c-0.229,0.104-0.518,0.167-0.851,0.2l-13.399-0.05l-6.551,3l15.648,0.1c0.525,0,0.668,0.167,0.396,0.5l-1.5,1.8
        c0.438-0.021,0.582,0.066,0.449,0.312c-0.199,0.166-0.5,0.25-0.899,0.25l-20.8-0.104c-10.396,3.938-18.084,6.983-23.051,9.15
        l-62.306,67.5c-0.229,0.134-0.526,0.283-0.896,0.45l-0.75-0.2h-0.25c-0.066,0.134-0.25,0.267-0.551,0.396
        c-1.771,0.234-3.332,0.479-4.699,0.7c-1.438-0.134-2.688-0.366-3.75-0.7c-0.834,0.104-1.15,0.033-0.95-0.188h-0.3
        c-0.334-0.2-0.334-0.395,0-0.562c0.229-0.3,0.6-0.416,1.1-0.35l63.312-67.55c-1.167-2.167-3.217-5.104-6.149-8.803l-26.75-0.197
        c-0.367-0.104-0.518-0.188-0.449-0.25c-0.534-0.2-0.534-0.5,0-0.9c0.271-0.366,0.806-0.729,1.604-1.1
        c-0.308-0.144-0.366-0.25-0.2-0.354c-0.032-0.062,0.021-0.083,0.146-0.05c0.104-0.2,0.396-0.267,0.896-0.2l22.5,0.104
        l-2.198-3.104l-15.689-0.1c-0.367-0.033-0.533-0.117-0.5-0.25c-0.7-0.334-0.167-1,1.6-2c-0.332-0.104-0.367-0.217-0.1-0.354
        c0.1-0.229,0.396-0.312,0.896-0.25l40.899,0.354l1.75-1.854c0.101-0.229,0.436-0.334,1-0.307c0.034-0.133,0.267-0.25,0.7-0.35
        c1.268-0.2,3.355-0.283,6.3-0.25c0.396-0.066,0.583,0.017,0.55,0.25c-0.229,0.267-0.35,0.7-0.35,1.3l0.44-0.6
        c0.143-0.271,0.48-0.366,1.06-0.312c0.361-0.062,0.518,0.021,0.44,0.25l-1.854,1.854l34.7,0.2
        C1150.217,741.983,1150.332,742.133,1150.1,742.4z M1109.6,747.65l16.899,0.146c3.034-1.433,5.284-2.45,6.75-3.05l-20.649-0.25
        L1109.6,747.65z M1111.149,744.6l-7.312-0.146l-2.896,3.2l7.35,0.05L1111.149,744.6z M1102.399,744.4l-22.604-0.15l2.15,3.25
        l17.447,0.1L1102.399,744.4z M1099.699,758.4c4.367-1.867,11.062-4.484,20.1-7.854l-12.75-0.1L1099.699,758.4z M1096.799,750.45
        l-12.646-0.104c2.521,3.167,4.35,5.75,5.438,7.75L1096.799,750.45z" />
        </g>
        <g>
          <g>
            <g>
              <polygon fill="#FFFFFF" points="1073.949,644.95 1053.149,644.95 1055.799,640.75 1073.949,640.75             " />
              <polygon fill="#FFFFFF" points="1035.749,640.75 1053.749,640.75 1051.149,644.95 1035.749,644.95             " />
              <polygon fill="#FFFFFF" points="1034.199,640.75 1034.199,644.95 1025.899,644.95 1025.899,640.75             " />
              <polygon fill="#FFFFFF" points="1024.249,644.95 1008.85,644.95 1005.749,640.75 1024.249,640.75            " />
              <path fill="#FFFFFF" d="M978.6,640.75h25.104l3.146,4.2H978.95C978.082,644.85,977.967,643.45,978.6,640.75z" />
              <polygon fill="#FFFFFF" points="1074.649,629.2 1035.749,629.2 1035.749,625.15 1074.649,625.15             " />
              <polygon fill="#FFFFFF" points="1025.899,629.2 1025.899,625.15 1034.199,625.15 1034.199,629.2             " />
              <path fill="#FFFFFF" d="M1024.249,629.2h-26.8l-1.45-2.15c-0.3-0.396-0.649-0.433-1.05-0.1c-0.5,0.3-0.583,0.646-0.25,1.05
            l0.7,1.2h-15.7c-0.967-0.067-1.083-1.417-0.354-4.05h44.899L1024.249,629.2L1024.249,629.2z" />
              <path fill="#231F20" d="M1076.225,629.207c0.562,0.08,0.84,0.359,0.84,0.84c0.08,0.562-0.146,0.842-0.719,0.842h-15.25
            l-4.322,8.283h17.771c0.719,0,1,0.361,0.84,1.082l0.119,0.479v4.189c0.561,0,0.842,0.279,0.842,0.854
            c0.078,0.561-0.162,0.842-0.724,0.842h-23.53c-7.521,10.405-12.967,18.33-16.328,23.771v157.047
            c0,0.4-0.201,0.688-0.602,0.854c-0.4,0.078-0.688,0-0.84-0.24l-0.121-0.479V673.277l-0.119,0.106
            c-0.24,0.402-0.604,0.521-1.082,0.361c-0.559-0.24-0.68-0.604-0.359-1.08l1.562-2.644V646.61h-8.285v22.562l1.441,2.521
            c0.312,0.479,0.229,0.881-0.24,1.199c-0.399,0.312-0.76,0.229-1.08-0.238l-0.121-0.24v155.01
            c2.645,1.361,4.964,1.441,6.966,0.24c0.239-0.16,0.479-0.199,0.719-0.119c0.242,0.078,0.402,0.24,0.481,0.479
            c0.16,0.396,0.078,0.721-0.239,0.961c-2.562,1.602-5.363,1.602-8.406,0c-0.639,0.312-1.038,0.158-1.188-0.48l-0.361-0.229
            c-0.316-0.24-0.438-0.562-0.355-0.959c0.08-0.482,0.316-0.688,0.729-0.604V669.67c-3.521-5.764-8.933-13.447-16.209-23.053
            H978.14c-0.312,0-0.561-0.16-0.721-0.48c-1.121-0.721-1.281-2.521-0.479-5.396c-0.396-0.159-0.562-0.479-0.479-0.96
            c0-0.4,0.239-0.604,0.721-0.604h25.334l-5.883-8.283h-17.78c-0.312,0-0.56-0.148-0.719-0.479
            c-1.041-0.729-1.201-2.521-0.479-5.404c-0.4-0.16-0.562-0.479-0.48-0.959c0-0.396,0.24-0.602,0.722-0.602h46.354v-4.923
            c0-0.562,0.271-0.853,0.84-0.853c0-0.312,0.16-0.602,0.479-0.842c1.273-0.56,3.562-0.839,6.849-0.839
            c0.562,0,0.881,0.24,0.961,0.719c0.08,0.812,0.354,1.922,0.84,3.363v-1.562c0-0.562,0.279-0.848,0.84-0.848
            c0.479-0.081,0.729,0.159,0.729,0.729v5.044h39.5c0.724,0,1.002,0.354,0.845,1.079l0.237,0.479v4.084L1076.225,629.207z
             M1074.664,625.125h-38.9v4.082h38.9V625.125z M1073.943,640.732h-18.131l-2.646,4.189h20.771L1073.943,640.732
            L1073.943,640.732z M1059.295,630.889h-23.531v8.283h19.09C1056.934,635.49,1058.414,632.729,1059.295,630.889z
             M1053.773,640.732h-18.021v4.189h15.367L1053.773,640.732z M1032.521,620.201l-0.729-2.521c-2.562,0-4.438,0.238-5.642,0.729
            l-0.242,0.119v4.924h8.285v-3.312c-0.021,0.521-0.297,0.779-0.84,0.779C1032.881,621.004,1032.6,620.762,1032.521,620.201z
             M1025.916,629.207h8.285v-4.082h-8.285V629.207z M1025.916,644.936h8.285v-4.189h-8.285V644.936z M1034.201,630.889h-8.285
            v8.283h8.285V630.889z M1035.764,667.268c3.041-4.723,7.812-11.604,14.287-20.646h-14.287V667.268z M1024.236,625.125h-44.904
            c-0.723,2.641-0.602,4.002,0.359,4.082h15.729l-0.721-1.201c-0.32-0.396-0.24-0.76,0.24-1.08
            c0.396-0.312,0.76-0.281,1.067,0.121l1.441,2.16h26.771L1024.236,625.125L1024.236,625.125z M1003.705,640.732h-25.096
            c-0.646,2.723-0.521,4.123,0.354,4.189h27.854L1003.705,640.732z M1008.867,644.936h15.369v-4.189h-18.49L1008.867,644.936z
             M1024.236,630.889h-25.574l5.883,8.283h19.691V630.889z M1024.236,666.547v-19.93h-14.168
            C1016.312,654.861,1021.035,661.506,1024.236,666.547z" />
              <path fill="#FFFFFF" d="M1034.199,646.6v23.45l-1.551,2.604c-0.332,0.5-0.229,0.854,0.351,1.1
            c0.468,0.166,0.833,0.05,1.101-0.35l0.104-0.15v154.3l0.1,0.5h-0.199c-0.101-0.229-0.268-0.396-0.5-0.5
            c-0.229-0.062-0.467-0.017-0.688,0.15c-2,1.2-4.332,1.116-7-0.25v-155l0.146,0.2c0.308,0.5,0.65,0.583,1.058,0.25
            c0.5-0.312,0.582-0.7,0.25-1.2l-1.444-2.5V646.6H1034.199z" />
              <path fill="#FFFFFF" d="M1034.199,623.45h-8.301v-4.95l0.25-0.1c1.188-0.479,3.084-0.7,5.646-0.7l0.7,2.5
            c0.101,0.562,0.384,0.8,0.851,0.7c0.562,0,0.841-0.229,0.854-0.688V623.45L1034.199,623.45z" />
            </g>
          </g>
        </g>
      </g>
    </g>
    <g>
      <g id="Layer_2_2_">
        <g>
          <g id="Layer_1_7_">
            <polygon fill="#FFFFFF" points="605.001,224.858 605.001,214.655 612.442,214.655 612.442,224.858           " />
            <g>
              <path d="M612.442,224.973c-0.076,0-0.146-0.051-0.146-0.115V214.77h-7.166v10.088c0,0.064-0.062,0.115-0.138,0.115
            s-0.139-0.051-0.139-0.115v-10.203c0-0.064,0.062-0.116,0.139-0.116h7.438c0.073,0,0.146,0.052,0.146,0.116v10.203
            C612.58,224.922,612.519,224.973,612.442,224.973z" />
            </g>
            <g>
              <path fill="#FFFFFF" d="M606.058,213.978c0.735-0.164,1.604-0.247,2.609-0.247c1.004,0,1.862,0.083,2.572,0.247
            c0.729,0.164,1.103,0.359,1.103,0.585s-0.368,0.421-1.103,0.585c-0.71,0.166-1.568,0.247-2.572,0.247
            c-1.005,0-1.874-0.081-2.609-0.247c-0.71-0.164-1.062-0.359-1.062-0.585S605.348,214.142,606.058,213.978z" />
              <g>
                <path d="M608.667,215.51c-1.016,0-1.906-0.084-2.646-0.251c-0.809-0.184-1.166-0.405-1.166-0.696s0.358-0.513,1.166-0.697
              c0.735-0.167,1.638-0.25,2.646-0.25c1.01,0,1.888,0.084,2.609,0.25c0.812,0.184,1.202,0.405,1.202,0.697
              s-0.394,0.513-1.203,0.697C610.562,215.426,609.684,215.51,608.667,215.51z M608.667,213.847
              c-0.985,0-1.852,0.082-2.574,0.243c-0.869,0.201-0.963,0.398-0.963,0.474c0,0.075,0.094,0.272,0.964,0.474
              c0.715,0.161,1.581,0.243,2.573,0.243s1.845-0.082,2.535-0.243c0.903-0.202,1.002-0.398,1.002-0.474s-0.099-0.272-1-0.474
              C610.506,213.928,609.653,213.847,608.667,213.847z" />
              </g>
            </g>
            <g>
              <path fill="#FFFFFF" d="M605.046,224.396h7.258c0.03,0.05,0.046,0.102,0.046,0.154c0,0.206-0.355,0.398-1.103,0.578
            c-0.703,0.154-1.562,0.231-2.572,0.231c-1.011,0-1.883-0.077-2.619-0.231c-0.705-0.18-1.056-0.373-1.056-0.578
            C605.001,224.498,605.017,224.446,605.046,224.396z" />
              <g>
                <path d="M608.676,225.474c-1.018,0-1.911-0.079-2.646-0.234c-0.794-0.202-1.161-0.421-1.161-0.689
              c0-0.069,0.021-0.141,0.061-0.207c0.034-0.057,0.117-0.08,0.188-0.051c0.062,0.029,0.095,0.099,0.061,0.155
              c-0.02,0.034-0.03,0.069-0.03,0.102c0,0.052,0.068,0.24,0.959,0.467c0.722,0.149,1.581,0.226,2.577,0.226
              s1.854-0.076,2.537-0.227c0.935-0.226,1-0.414,1-0.466c0-0.034-0.01-0.068-0.03-0.102c-0.023-0.057-0.008-0.126,0.062-0.155
              c0.062-0.029,0.146-0.006,0.185,0.051c0.041,0.066,0.062,0.136,0.062,0.207c0,0.27-0.383,0.489-1.202,0.688
              C610.572,225.396,609.695,225.474,608.676,225.474z" />
              </g>
            </g>
          </g>
        </g>
      </g>
      <g id="Layer_1_1_">
        <g opacity="0.2">
          <path d="M660.589,247.22v0.083l-0.582,0.646c0.125-0.014,0.188,0.027,0.188,0.125c-0.042-0.014-0.062,0-0.021,0.042
        c-0.097,0.042-0.215,0.069-0.353,0.083l-5.565-0.021l-2.72,1.247l6.5,0.041c0.221,0,0.276,0.07,0.166,0.208l-0.623,0.748
        c0.18-0.013,0.242,0.028,0.187,0.125c-0.083,0.069-0.208,0.104-0.374,0.104l-8.639-0.042c-4.317,1.635-7.51,2.901-9.572,3.802
        l-25.874,28.046c-0.097,0.056-0.221,0.118-0.374,0.187l-0.312-0.083h-0.104c-0.021,0.056-0.104,0.111-0.228,0.166
        c-0.734,0.098-1.384,0.195-1.952,0.291c-0.595-0.056-1.114-0.152-1.557-0.291c-0.354,0.042-0.479,0.014-0.396-0.082h-0.125
        c-0.138-0.083-0.138-0.16,0-0.229c0.097-0.125,0.25-0.173,0.457-0.145l26.289-28.067c-0.485-0.9-1.335-2.12-2.554-3.656
        l-11.109-0.083c-0.152-0.042-0.215-0.077-0.187-0.104c-0.229-0.083-0.229-0.208,0-0.374c0.104-0.152,0.331-0.304,0.663-0.457
        c-0.125-0.056-0.151-0.104-0.082-0.146c-0.021-0.027,0.007-0.034,0.062-0.021c0.042-0.083,0.166-0.111,0.374-0.083l9.344,0.042
        l-0.914-1.289l-6.52-0.042c-0.152-0.014-0.223-0.049-0.208-0.104c-0.291-0.139-0.069-0.416,0.664-0.831
        c-0.146-0.041-0.152-0.09-0.042-0.145c0.042-0.097,0.166-0.131,0.374-0.104l16.979,0.146l0.729-0.769
        c0.042-0.098,0.18-0.139,0.415-0.125c0.015-0.055,0.104-0.104,0.291-0.145c0.521-0.083,1.396-0.118,2.616-0.104
        c0.167-0.028,0.229,0.007,0.229,0.104c-0.097,0.11-0.146,0.291-0.146,0.54l0.187-0.25c0.055-0.111,0.201-0.152,0.438-0.125
        c0.146-0.027,0.216,0.007,0.188,0.104l-0.768,0.769l14.399,0.083C660.638,247.046,660.686,247.108,660.589,247.22z
         M643.77,249.401l7.021,0.062c1.26-0.595,2.194-1.018,2.803-1.268l-8.576-0.104L643.77,249.401z M644.414,248.133l-3.032-0.062
        l-1.204,1.33l3.062,0.021L644.414,248.133z M640.779,248.051l-9.396-0.062l0.896,1.35l7.247,0.042L640.779,248.051z
         M639.658,253.868c1.812-0.776,4.596-1.863,8.348-3.262l-5.295-0.042L639.658,253.868z M638.454,250.564l-5.253-0.042
        c1.052,1.316,1.807,2.389,2.263,3.22L638.454,250.564z" />
        </g>
        <g>
          <g>
            <g>
              <polygon fill="#FFFFFF" points="628.964,206.729 620.326,206.729 621.426,204.984 628.964,204.984             " />
              <polygon fill="#FFFFFF" points="613.1,204.984 620.575,204.984 619.496,206.729 613.1,206.729             " />
              <polygon fill="#FFFFFF" points="612.456,204.984 612.456,206.729 609.009,206.729 609.009,204.984             " />
              <polygon fill="#FFFFFF" points="608.324,206.729 601.928,206.729 600.641,204.984 608.324,204.984             " />
              <path fill="#FFFFFF" d="M589.365,204.984h10.424l1.308,1.745H589.51C589.15,206.688,589.104,206.106,589.365,204.984z" />
              <polygon fill="#FFFFFF" points="629.255,200.185 613.1,200.185 613.1,198.502 629.255,198.502             " />
              <polygon fill="#FFFFFF" points="609.009,200.185 609.009,198.502 612.456,198.502 612.456,200.185             " />
              <path fill="#FFFFFF" d="M608.324,200.185h-11.13l-0.604-0.894c-0.125-0.166-0.27-0.18-0.436-0.042
            c-0.208,0.125-0.242,0.27-0.104,0.437l0.291,0.499h-6.52c-0.401-0.028-0.45-0.589-0.146-1.683h18.647L608.324,200.185
            L608.324,200.185z" />
              <path fill="#231F20" d="M629.909,200.188c0.229,0.033,0.349,0.149,0.349,0.349c0.033,0.233-0.062,0.35-0.299,0.35h-6.333
            l-1.795,3.441h7.38c0.298,0,0.415,0.15,0.349,0.45l0.062,0.199v1.746c0.229,0,0.35,0.116,0.35,0.349
            c0.033,0.233-0.062,0.35-0.3,0.35H619.9c-3.125,4.324-5.39,7.616-6.772,9.877v65.253c0,0.166-0.083,0.283-0.25,0.349
            c-0.166,0.033-0.283,0-0.354-0.1l-0.053-0.199v-64.106l-0.05,0.049c-0.104,0.167-0.25,0.217-0.449,0.15
            c-0.229-0.1-0.276-0.25-0.146-0.449l0.646-1.098v-9.728h-3.438v9.379l0.601,1.047c0.134,0.2,0.104,0.366-0.101,0.499
            c-0.166,0.133-0.314,0.099-0.447-0.1l-0.05-0.1v64.405c1.098,0.565,2.061,0.599,2.893,0.1
            c0.104-0.066,0.199-0.083,0.301-0.049c0.104,0.032,0.167,0.1,0.197,0.199c0.065,0.166,0.032,0.299-0.1,0.399
            c-1.064,0.665-2.229,0.665-3.491,0c-0.271,0.133-0.438,0.066-0.498-0.2l-0.146-0.1c-0.139-0.1-0.188-0.233-0.146-0.398
            c0.03-0.2,0.133-0.284,0.3-0.25V217c-1.463-2.395-3.708-5.587-6.729-9.578H589.19c-0.139,0-0.229-0.066-0.305-0.2
            c-0.466-0.299-0.525-1.047-0.191-2.245c-0.168-0.066-0.229-0.199-0.197-0.399c0-0.167,0.102-0.25,0.301-0.25h10.521
            l-2.438-3.441h-7.382c-0.133,0-0.229-0.066-0.301-0.199c-0.438-0.3-0.498-1.048-0.196-2.246
            c-0.166-0.066-0.232-0.199-0.198-0.398c0-0.167,0.104-0.25,0.3-0.25h19.247v-2.045c0-0.233,0.116-0.35,0.354-0.35
            c0-0.132,0.062-0.25,0.195-0.35c0.526-0.232,1.479-0.349,2.845-0.349c0.229,0,0.364,0.1,0.396,0.299
            c0.032,0.334,0.146,0.798,0.354,1.397v-0.648c0-0.233,0.114-0.35,0.353-0.35c0.197-0.033,0.306,0.066,0.306,0.299v2.095
            h16.398c0.304,0,0.416,0.15,0.354,0.449l0.104,0.199v1.697L629.909,200.188z M629.261,198.492h-16.155v1.696h16.155V198.492z
             M628.962,204.977h-7.53l-1.097,1.746h8.626L628.962,204.977L628.962,204.977z M622.878,200.887h-9.771v3.441h7.928
            C621.896,202.798,622.512,201.651,622.878,200.887z M620.585,204.977h-7.479v1.746h6.382L620.585,204.977z M611.759,196.446
            l-0.3-1.047c-1.062,0-1.844,0.099-2.343,0.299l-0.104,0.05v2.045h3.441v-1.372c-0.008,0.216-0.124,0.324-0.354,0.324
            C611.909,196.779,611.792,196.679,611.759,196.446z M609.016,200.188h3.441v-1.696h-3.441V200.188z M609.016,206.723h3.441
            v-1.746h-3.441V206.723z M612.457,200.887h-3.441v3.441h3.441V200.887z M613.105,216.002c1.263-1.962,3.229-4.822,5.934-8.58
            h-5.934V216.002z M608.318,198.492h-18.649c-0.3,1.097-0.25,1.663,0.149,1.696h6.521l-0.299-0.499
            c-0.133-0.166-0.1-0.316,0.104-0.449c0.166-0.133,0.312-0.117,0.439,0.05l0.604,0.897h11.115L608.318,198.492L608.318,198.492
            z M599.792,204.977H589.37c-0.271,1.131-0.216,1.713,0.146,1.746h11.568L599.792,204.977z M601.936,206.723h6.396v-1.746
            h-7.688L601.936,206.723z M608.318,200.887h-10.621l2.438,3.441h8.188L608.318,200.887L608.318,200.887z M608.318,215.703
            v-8.281h-5.896C605.027,210.847,606.989,213.608,608.318,215.703z" />
              <path fill="#FFFFFF" d="M612.456,207.415v9.744l-0.644,1.081c-0.146,0.208-0.103,0.359,0.145,0.457
            c0.194,0.069,0.346,0.021,0.457-0.146l0.042-0.062V282.6l0.042,0.208h-0.083c-0.042-0.097-0.111-0.166-0.208-0.208
            c-0.097-0.027-0.194-0.007-0.291,0.062c-0.831,0.499-1.799,0.464-2.907-0.104v-64.402l0.062,0.083
            c0.125,0.208,0.27,0.242,0.436,0.104c0.208-0.125,0.242-0.291,0.104-0.499l-0.604-1.039v-9.391L612.456,207.415
            L612.456,207.415z" />
              <path fill="#FFFFFF" d="M612.456,197.796h-3.447v-2.057l0.104-0.042c0.499-0.194,1.271-0.291,2.347-0.291l0.291,1.039
            c0.042,0.235,0.159,0.333,0.353,0.291c0.232,0,0.354-0.094,0.354-0.281L612.456,197.796L612.456,197.796z" />
            </g>
          </g>
        </g>
      </g>
    </g>
    <g>
      <g id="Layer_2_4_">
        <g>
          <g id="Layer_1_2_">
            <polygon fill="#FFFFFF" points="813.461,331.793 813.461,318.38 823.248,318.38 823.248,331.793           " />
            <g>
              <path d="M823.248,331.945c-0.1,0-0.182-0.068-0.182-0.152v-13.261h-9.437v13.261c0,0.083-0.081,0.152-0.182,0.152
            c-0.104,0-0.188-0.068-0.188-0.152V318.38c0-0.084,0.078-0.152,0.188-0.152h9.787c0.101,0,0.182,0.068,0.182,0.152v13.413
            C823.43,331.877,823.349,331.945,823.248,331.945z" />
            </g>
            <g>
              <path fill="#FFFFFF" d="M814.852,317.491c0.979-0.215,2.109-0.324,3.438-0.324c1.312,0,2.449,0.109,3.383,0.324
            c0.967,0.215,1.45,0.473,1.45,0.769c0,0.298-0.483,0.561-1.45,0.771c-0.934,0.218-2.062,0.318-3.383,0.318
            c-1.322,0-2.465-0.104-3.438-0.318c-0.934-0.215-1.396-0.478-1.396-0.771C813.45,317.963,813.918,317.706,814.852,317.491z" />
              <g>
                <path d="M818.283,319.505c-1.336,0-2.507-0.111-3.479-0.329c-1.048-0.242-1.534-0.533-1.534-0.916
              c0-0.383,0.486-0.674,1.533-0.916c0.982-0.219,2.152-0.33,3.479-0.33c1.329,0,2.479,0.111,3.432,0.33
              c1.079,0.241,1.583,0.532,1.583,0.916c0,0.384-0.504,0.675-1.585,0.916C820.775,319.394,819.621,319.505,818.283,319.505z
               M818.283,317.318c-1.296,0-2.438,0.104-3.385,0.312c-1.146,0.271-1.271,0.523-1.271,0.622c0,0.104,0.123,0.359,1.271,0.623
              c0.938,0.211,2.067,0.318,3.384,0.318c1.305,0,2.427-0.107,3.335-0.318c1.188-0.265,1.312-0.521,1.312-0.623
              c0-0.099-0.128-0.354-1.312-0.622C820.701,317.426,819.58,317.318,818.283,317.318z" />
              </g>
            </g>
            <g>
              <path fill="#FFFFFF" d="M813.521,331.188h9.546c0.039,0.064,0.06,0.139,0.06,0.202c0,0.271-0.482,0.524-1.45,0.766
            c-0.925,0.196-2.054,0.304-3.383,0.304s-2.477-0.104-3.443-0.304c-0.927-0.236-1.39-0.489-1.39-0.766
            C813.461,331.32,813.481,331.251,813.521,331.188z" />
              <g>
                <path d="M818.294,332.604c-1.339,0-2.512-0.104-3.487-0.308c-1.045-0.266-1.521-0.553-1.521-0.906
              c0-0.092,0.021-0.186,0.079-0.271c0.046-0.075,0.146-0.105,0.231-0.067c0.102,0.038,0.125,0.129,0.079,0.204
              c-0.021,0.044-0.04,0.09-0.04,0.134c0,0.068,0.104,0.315,1.271,0.614c0.938,0.196,2.081,0.297,3.391,0.297
              c1.312,0,2.438-0.101,3.337-0.298c1.228-0.297,1.315-0.544,1.315-0.612c0-0.044-0.021-0.09-0.041-0.134
              c-0.045-0.075-0.021-0.166,0.08-0.204c0.089-0.038,0.188-0.008,0.23,0.067c0.062,0.087,0.08,0.179,0.08,0.271
              c0,0.355-0.503,0.646-1.58,0.905C820.789,332.5,819.635,332.604,818.294,332.604z" />
              </g>
            </g>
          </g>
        </g>
      </g>
      <g id="Layer_1_6_">
        <g opacity="0.2">
          <path d="M886.57,361.189v0.104l-0.771,0.848c0.164-0.018,0.246,0.036,0.246,0.164c-0.055-0.019-0.062,0-0.021,0.062
        c-0.138,0.055-0.282,0.091-0.477,0.104l-7.312-0.021l-3.578,1.639l8.548,0.054c0.292,0,0.364,0.098,0.219,0.273l-0.812,0.979
        c0.229-0.018,0.312,0.036,0.244,0.165c-0.107,0.098-0.271,0.144-0.489,0.144l-11.361-0.062
        c-5.681,2.149-9.877,3.814-12.59,4.998l-34.028,36.869c-0.128,0.073-0.291,0.154-0.486,0.246l-0.402-0.104h-0.146
        c-0.029,0.067-0.137,0.146-0.3,0.225c-0.966,0.128-1.814,0.255-2.562,0.382c-0.781-0.073-1.477-0.2-2.059-0.382
        c-0.454,0.056-0.629,0.018-0.52-0.108h-0.164c-0.188-0.104-0.188-0.21,0-0.301c0.127-0.164,0.328-0.229,0.602-0.188
        l34.574-36.896c-0.638-1.188-1.757-2.787-3.354-4.812l-14.609-0.104c-0.2-0.062-0.282-0.104-0.245-0.144
        c-0.292-0.104-0.292-0.271,0-0.485c0.146-0.2,0.438-0.4,0.874-0.604c-0.164-0.072-0.2-0.136-0.104-0.188
        c-0.021-0.036,0.01-0.045,0.08-0.027c0.06-0.108,0.221-0.146,0.483-0.108l12.289,0.062l-1.199-1.694l-8.566-0.054
        c-0.199-0.021-0.291-0.063-0.271-0.139c-0.382-0.187-0.091-0.55,0.874-1.097c-0.184-0.054-0.195-0.116-0.057-0.188
        c0.057-0.128,0.221-0.175,0.491-0.144l22.34,0.191l0.955-1.01c0.058-0.128,0.229-0.188,0.548-0.165
        c0.021-0.073,0.146-0.144,0.384-0.191c0.689-0.104,1.839-0.152,3.438-0.137c0.221-0.036,0.312,0.009,0.303,0.137
        c-0.128,0.146-0.188,0.382-0.188,0.71l0.245-0.328c0.07-0.146,0.271-0.195,0.573-0.164c0.198-0.031,0.28,0.015,0.244,0.143
        l-1.019,1.01l18.95,0.107C886.635,360.962,886.697,361.043,886.57,361.189z M864.449,364.057l9.229,0.081
        c1.657-0.776,2.896-1.338,3.688-1.666l-11.279-0.136L864.449,364.057z M865.296,362.391l-3.987-0.081l-1.584,1.748l4.021,0.027
        L865.296,362.391z M860.517,362.282l-12.344-0.082l1.174,1.771l9.531,0.061L860.517,362.282z M859.042,369.929
        c2.386-1.02,6.045-2.442,10.979-4.289l-6.977-0.054L859.042,369.929z M857.458,365.586l-6.909-0.056
        c1.383,1.729,2.376,3.141,2.979,4.229L857.458,365.586z" />
        </g>
        <g>
          <g>
            <g>
              <polygon fill="#FFFFFF" points="844.979,307.962 833.616,307.962 835.062,305.668 844.979,305.668             " />
              <polygon fill="#FFFFFF" points="824.112,305.668 833.944,305.668 832.524,307.962 824.112,307.962             " />
              <polygon fill="#FFFFFF" points="823.266,305.668 823.266,307.962 818.732,307.962 818.732,305.668             " />
              <polygon fill="#FFFFFF" points="817.831,307.962 809.42,307.962 807.727,305.668 817.831,305.668            " />
              <path fill="#FFFFFF" d="M792.896,305.668h13.709l1.722,2.294h-15.239C792.614,307.907,792.552,307.143,792.896,305.668z" />
              <polygon fill="#FFFFFF" points="845.359,299.36 824.112,299.36 824.112,297.147 845.359,297.147             " />
              <polygon fill="#FFFFFF" points="818.732,299.36 818.732,297.147 823.266,297.147 823.266,299.36             " />
              <path fill="#FFFFFF" d="M817.831,299.36h-14.638l-0.792-1.175c-0.164-0.218-0.355-0.236-0.574-0.054
            c-0.272,0.164-0.318,0.354-0.136,0.573l0.382,0.656h-8.575c-0.528-0.037-0.592-0.774-0.191-2.212h24.524V299.36
            L817.831,299.36z" />
              <path fill="#231F20" d="M846.22,299.363c0.312,0.044,0.459,0.196,0.459,0.459c0.044,0.307-0.086,0.459-0.393,0.459h-8.33
            l-2.36,4.524h9.707c0.396,0,0.546,0.197,0.458,0.591l0.065,0.262v2.295c0.307,0,0.46,0.153,0.46,0.459
            c0.043,0.306-0.089,0.46-0.396,0.46h-12.852c-4.11,5.684-7.083,10.012-8.918,12.984v85.772c0,0.229-0.11,0.373-0.329,0.459
            c-0.219,0.043-0.373,0-0.459-0.131l-0.066-0.262v-84.272l-0.062,0.065c-0.131,0.22-0.329,0.285-0.591,0.197
            c-0.312-0.138-0.372-0.329-0.196-0.597l0.854-1.438V308.87h-4.524v12.329l0.787,1.377c0.175,0.262,0.131,0.479-0.131,0.656
            c-0.229,0.175-0.415,0.13-0.591-0.137l-0.065-0.132v84.664c1.442,0.744,2.711,0.787,3.805,0.131
            c0.131-0.087,0.271-0.104,0.396-0.062c0.132,0.043,0.22,0.131,0.263,0.262c0.088,0.218,0.043,0.396-0.131,0.525
            c-1.399,0.875-2.932,0.875-4.592,0c-0.354,0.175-0.566,0.087-0.654-0.271l-0.197-0.13c-0.175-0.131-0.229-0.307-0.188-0.521
            c0.045-0.271,0.176-0.368,0.396-0.324V321.46c-1.925-3.146-4.876-7.345-8.854-12.591h-16.33c-0.188,0-0.312-0.088-0.396-0.263
            c-0.604-0.396-0.689-1.376-0.262-2.953c-0.219-0.087-0.308-0.262-0.264-0.524c0-0.219,0.132-0.329,0.395-0.329h13.837
            l-3.214-4.524h-9.707c-0.173,0-0.305-0.087-0.394-0.262c-0.567-0.394-0.655-1.377-0.271-2.952
            c-0.219-0.088-0.307-0.262-0.262-0.524c0-0.22,0.131-0.329,0.394-0.329h25.313v-2.688c0-0.307,0.15-0.459,0.459-0.459
            c0-0.175,0.088-0.329,0.263-0.46c0.699-0.306,1.944-0.458,3.737-0.458c0.307,0,0.479,0.131,0.521,0.393
            c0.048,0.439,0.193,1.049,0.456,1.837v-0.853c0-0.307,0.152-0.459,0.459-0.459c0.271-0.044,0.396,0.087,0.396,0.393v2.754
            H845.7c0.394,0,0.547,0.197,0.459,0.59l0.132,0.262v2.231L846.22,299.363z M845.368,297.134h-21.247v2.229h21.247V297.134z
             M844.974,305.659h-9.896l-1.441,2.295h11.353L844.974,305.659L844.974,305.659z M836.974,300.282H824.12v4.524h10.427
            C835.683,302.795,836.492,301.287,836.974,300.282z M833.957,305.659h-9.837v2.295h8.394L833.957,305.659z M822.35,294.444
            l-0.396-1.376c-1.398,0-2.425,0.13-3.081,0.393l-0.132,0.066v2.688h4.521v-1.804c-0.01,0.285-0.162,0.426-0.459,0.426
            C822.546,294.883,822.392,294.75,822.35,294.444z M818.741,299.363h4.521v-2.229h-4.521V299.363z M818.741,307.954h4.521
            v-2.295h-4.521V307.954z M823.267,300.282h-4.521v4.524h4.521V300.282z M824.12,320.152c1.661-2.579,4.262-6.338,7.804-11.278
            h-7.804V320.152z M817.824,297.134h-24.526c-0.396,1.442-0.329,2.186,0.196,2.229h8.591l-0.394-0.656
            c-0.188-0.217-0.145-0.415,0.131-0.59c0.219-0.174,0.415-0.153,0.59,0.066l0.787,1.18h14.624L817.824,297.134L817.824,297.134
            z M806.61,305.659h-13.708c-0.354,1.487-0.283,2.252,0.188,2.295h15.225L806.61,305.659z M809.43,307.954h8.396v-2.295
            h-10.104L809.43,307.954z M817.824,300.282h-13.969l3.214,4.524h10.755V300.282z M817.824,319.758v-10.885h-7.738
            C813.496,313.376,816.075,317.005,817.824,319.758z" />
              <path fill="#FFFFFF" d="M823.266,308.863v12.809l-0.847,1.42c-0.182,0.273-0.118,0.479,0.191,0.604
            c0.255,0.09,0.455,0.021,0.601-0.191l0.055-0.083V407.7l0.062,0.271h-0.109c-0.055-0.127-0.146-0.218-0.271-0.271
            c-0.128-0.036-0.268-0.009-0.396,0.082c-1.092,0.646-2.354,0.604-3.812-0.146v-84.654l0.082,0.104
            c0.164,0.273,0.354,0.318,0.562,0.136c0.273-0.165,0.318-0.382,0.146-0.648l-0.792-1.366v-12.344L823.266,308.863
            L823.266,308.863z" />
              <path fill="#FFFFFF" d="M823.266,296.219h-4.521v-2.704l0.137-0.054c0.655-0.256,1.688-0.383,3.086-0.383l0.382,1.366
            c0.062,0.309,0.211,0.437,0.471,0.382c0.312,0,0.459-0.124,0.469-0.37L823.266,296.219L823.266,296.219z" />
            </g>
          </g>
        </g>
      </g>
    </g>
    <g>
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M137.823,300.8
    c69.458-29.053,131.108-67.586,184.95-115.6" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M148.423,301.4
    c57.64-17.138,126.173-55.254,205.604-114.35" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M152.173,302.05
    c64.789-15.182,134.572-44.548,209.354-88.1" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M140.923,301.4
    c58.317-18.8,116.633-49.2,174.95-91.2" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M144.673,301.4
    c12.967-1.816,25.834-4.033,38.6-6.65c43.176-8.847,85.209-22.264,126.1-40.25c15.889-6.995,31.605-14.678,47.15-23.05" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M138.423,301.4
    c64.432-14.284,124.832-37.401,181.2-69.35" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M597.077,199.55" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M316.473,210.2" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M623.923,200.2" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M361.473,213.95" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M323.473,185.05
    c66.325,24.623,157.325,29.656,273,15.1" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M354.173,186.7
    c64.712,20.709,154.429,25.426,269.15,14.15" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M595.873,206.35" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M320.073,231.55" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M621.169,205.2" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M357.523,231.05" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M624.023,200.2
    c64.021,63.933,133.396,96.833,208.1,98.7" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M836.373,298.3
    c52.52,149.147,127.502,259.948,224.949,332.401" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M802.58,299.089" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M479.501,384.664" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M605.823,215.85" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M1022.223,667.35
    c-27.633,14.104-53.517,24.57-77.646,31.4c-45.363,12.836-84.562,12.836-117.574,0c-2.703-1.047-5.361-2.18-7.975-3.4" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M800.773,298.3
    c43.713,149.95,109.104,260.333,196.188,331.151" />
      <path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="M595.823,200.2
    c64.017,63.947,133.384,96.848,208.1,98.7" />
      <path fill="none" stroke="#000000" stroke-miterlimit="10" d="M288.545,892.979c52.598-48.266,105.798-140.228,112.07-173.854" />
      <path fill="none" stroke="#000000" stroke-miterlimit="10" d="M832.123,298.9c14.333-16.166,27.854-31.59,34.854-59.256" />
    </g>
    <g id="sparkpaths" display="none">
      <!-- <path fill="none" d="M824.58,310.589c14.333-16.166,33-36.834,40-64.5" /> -->
      <circle cx="833" cy="300" r="7" fill="#FFF915" />
      <!-- <path fill="none" d="M1018.08,668.09c-38.579,23.264-134.246,59.245-196.5,27.912" /> -->
      <circle cx="1020" cy="670" r="7" fill="#FFF915" />
      <!-- <path fill="none" d="M288.545,892.979c52.598-48.266,105.798-140.228,112.07-173.854" /> -->
      <circle cx="290" cy="890" r="7" fill="#FFF915" />
    </g>
    </g>
  <?php } // end if $user_id !== 2 
  ?>

  <g id="smoke">

    <?php if ($user_id !== 3) { // not toledo 
    ?>
      <image overflow="visible" enable-background="new" width="25" height="21" xlink:href="img/smoke.png" x="108" y="230">
      </image>

      <image overflow="visible" enable-background="new" width="25" height="21" xlink:href="img/smoke.png" x="141" y="230">
      </image>

      <image overflow="visible" enable-background="new" width="25" height="21" xlink:href="img/smoke.png" x="175" y="230">
      </image>
    <?php } else { ?>
      <image overflow="visible" enable-background="new" width="50" height="42" xlink:href="img/smoke.png" x="141" y="180"></image>
    <?php } ?>
  </g>

  <!-- g#pipes used to be here -->

  <g id="gauges">
    <!-- <foreignObject width="440" height="285" id="gauge1" x="-1000" y="-1000" style="overflow:hidden">
      <!- pointer-events:none; hack to prevent scrolling by disabling mouse interaction: http://stackoverflow.com/a/27481052/2624391 ->
      <iframe style="display:none;overflow:hidden;pointer-events:none;" scrolling="no" xmlns="http://www.w3.org/1999/xhtml" width="100%" height="100%" frameborder="0" id="iframe1"></iframe>
    </foreignObject>
    <foreignObject width="440" height="285" id="gauge2" x="-1000" y="-1000" style="overflow:hidden">
      <iframe style="display:none;overflow:hidden;pointer-events:none;" scrolling="no" xmlns="http://www.w3.org/1999/xhtml" width="100%" height="100%" frameborder="0" id="iframe2"></iframe>
    </foreignObject>
    <foreignObject width="440" height="285" id="gauge3" x="-1000" y="-1000" style="overflow:hidden">
      <iframe style="display:none;overflow:hidden;pointer-events:none;" scrolling="no" xmlns="http://www.w3.org/1999/xhtml" width="100%" height="100%" frameborder="0" id="iframe3"></iframe>
    </foreignObject>
    <foreignObject width="440" height="285" id="gauge4" x="-1000" y="-1000" style="overflow:hidden">
      <iframe style="display:none;overflow:hidden;pointer-events:none;" scrolling="no" xmlns="http://www.w3.org/1999/xhtml" width="100%" height="100%" frameborder="0" id="iframe4"></iframe>
    </foreignObject> -->
    <?php
    $defaultStateContent = isset($gauges[$cwd_dashboard_default_state]) ? $gauges[$cwd_dashboard_default_state] :  ['', '', '', ''];
    $initialYPosition = 80;
    $id_number = 1;
    foreach ($defaultStateContent as $key => $gaugeLink) {
      $gaugeID = "gauge" . $id_number;
      $svgContent = file_get_contents($gaugeLink);
      echo "<foreignObject x='1280' y='$initialYPosition' width='290' height='190'>
      <iframe 
        style='overflow:hidden;pointer-events:none;'
        scrolling='no'
        xmlns='http://www.w3.org/1999/xhtml'
        width='100%'
        height='100%'
        frameborder='0'
        id='$gaugeID'
        src='$gaugeLink'
      ></iframe>
      </foreignObject>\n";
      // echo "<image id='$gaugeID' x='1280' y='$initialYPosition' width='290' height='190' xlink:href='$gaugeLink'/>";
      $initialYPosition += 200;
      $id_number++;
    }
    ?>
    <!-- <use id="gauge2" x="1280" y="280" width="290" height="190" xlink:href="" />
    <use id="gauge3" x="1280" y="480" width="290" height="190" xlink:href="" />
    <use id="gauge4" x="1280" y="680" width="290" height="190" xlink:href="" /> -->
  </g>
  <!-- <rect rx="3" ry="3" width="296" height="199" fill="#379adc" x="1275" y="65" id="loader1" />
  <image x="1349" y="110" width="148" height="100" xlink:href="img/tail-spin.svg" />
  <rect rx="3" ry="3" width="296" height="199" fill="#379adc" x="1275" y="273" id="loader2" />
  <image x="1349" y="320" width="148" height="100" xlink:href="img/tail-spin.svg" />
  <rect rx="3" ry="3" width="296" height="199" fill="#379adc" x="1275" y="480" id="loader3" />
  <image x="1349" y="530" width="148" height="100" xlink:href="img/tail-spin.svg" />
  <rect rx="3" ry="3" width="296" height="199" fill="#379adc" x="1275" y="685" id="loader4" />
  <image x="1349" y="730" width="148" height="100" xlink:href="img/tail-spin.svg" /> -->
  <?php if (isset($houses_x)) { ?>
    <g transform="translate(<?php echo $houses_x . ',' . $houses_y ?>)">
      <g id="house_inside" display="none">
        <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="1013.999" y1="-212.8965" x2="1013.999" y2="-149.653" gradientTransform="matrix(1 0 0 1 -294 858)">
          <stop offset="0" style="stop-color:#D5D5D5" />
          <stop offset="1" style="stop-color:#FFFFFF" />
        </linearGradient>
        <polygon display="inline" fill="url(#SVGID_1_)" points="679.114,793.571 659.495,794.006 641.505,702.748 714.008,630.702 
    757.898,668.094 798.493,699.006 786.325,794.006 703.565,793.047   " />
        <polygon display="inline" fill="#E7E7E7" stroke="#BFBFBF" stroke-miterlimit="10" points="790.5,794.006 659.495,794.006 
    678.05,772.59 792.167,772.59  " />
        <line display="inline" fill="none" stroke="#BFBFBF" stroke-miterlimit="10" x1="678.05" y1="772.59" x2="678.05" y2="663" />
      </g>

      <g id="stick_figures">
        <g id="stick_electricity" display="none">
          <path display="inline" fill="#231F20" d="M785.751,774.007c0.905,1.828,0.634,3.909-0.09,5.847
      c-0.09,0.261-0.235,0.388-0.398,0.478c0.108,0.182,0.217,0.354,0.312,0.507c0.521,0.869-0.29,1.812-1.214,1.448
      c-0.959-0.351-1.922-0.814-2.189-1.883c-0.126-0.438-0.09-0.854-0.09-1.271c-1.918-2.66-4.688-5.897-6.009-4.435
      c1.354,1.141,2.625,2.371,3.801,3.675c1.303,1.447,2.95,2.856,2.95,4.904c0,0.571-0.352,0.938-0.895,1.05
      c-3.231,0.706-5.104-0.651-7.384-2.316c-2.438,1.629-5.604,2.104-7.8,4.108c-0.489,0.452-1.249,0.054-1.484-0.453
      c-0.506-1.158-0.607-2.334-0.76-3.529c-0.833,0.091-1.81,0.058-2.118-0.796c-0.507-1.285,1.412-2.208,2.27-2.696
      c0.861-0.507,1.756,0.438,1.502,1.158c0,0.021,0.018,0.058,0.036,0.09c0.064,0,0.155-0.018,0.229-0.028
      c0.108-0.146,0.217-0.271,0.398-0.352c0.344-0.181,0.706-0.344,1.062-0.507c0.021,0,0.072-0.018,0.093-0.036
      c0.613-0.344,1.308-0.48,2.024-0.507c0.344,0,0.615,0.127,0.778,0.308c0.29-0.064,0.611-0.145,0.923-0.229
      c0.145-0.036,0.29-0.058,0.396-0.036c0.941,0.109,1.347,1.557,0.29,1.864c-1.177,0.318-2.353,0.646-3.547,0.938
      c-0.037,0.072-0.069,0.163-0.104,0.235c-0.095,0.416-0.199,0.812-0.312,1.213c1.521-0.706,3.167-1.249,4.604-2.118
      c-0.146-0.669,0.669-1.375,1.43-1.029c0.706-0.6,1.521,0.197,1.43,0.979c1.521,1.122,2.812,2.045,4.778,1.954
      c-0.525-1.411-2.118-2.771-3.062-3.746c-1.468-1.466-3.006-2.859-4.67-4.072c-0.562,0.271-1.377,0.688-1.377,0.706
      c0.188,0.235,0.418,0.397,0.642,0.599c0.181,0.188,0.344,0.36,0.471,0.544c0.09-0.021,0.181-0.057,0.29-0.057
      c1.249,0,1.43,1.954,0.145,1.954c-0.181,0-0.354-0.072-0.507-0.163c-0.145,0.073-0.308,0.109-0.485,0.109
      c-0.448,0-0.927-0.271-1.027-0.833c-0.021,0-0.923-0.742-1.122-1.031c-0.271-0.489-0.398-0.905-0.312-1.484
      c0.198-1.277,2.896-2.979,4.021-2.241c0.308,0.222,0.597,0.449,0.887,0.675c2.268-2.914,5.595,0,8.072,3.095
      c0.436-0.091,0.905,0.054,1.122,0.562c0.163,0.451,0.29,0.893,0.326,1.355c0.323-1.018,0.471-2.136,0.163-3.112
      c-0.256-0.146-0.438-0.381-0.438-0.74c0-0.031,0-0.067,0-0.128c-0.054-0.066-0.104-0.162-0.181-0.254
      c-1.146-1.304-2.986-1.646-4.635-1.664c-1.247-0.035-1.446-1.975-0.161-1.954c1.024,0.021,2.136,0.146,3.146,0.438
      c-0.344-0.729,0.346-1.792,1.249-1.321c0.905,0.474,1.812,1.087,2.208,2.063C785.697,772.521,785.715,773.265,785.751,774.007z" />
          <path display="inline" fill="#FFFFFF" d="M780.666,782.531c-1.973,0.091-3.258-0.832-4.778-1.954
      c0.091-0.778-0.724-1.575-1.43-0.979c-0.76-0.344-1.575,0.362-1.43,1.031c-1.438,0.869-3.077,1.412-4.604,2.118
      c0.108-0.398,0.225-0.797,0.312-1.213c0.036-0.072,0.064-0.163,0.104-0.235c1.194-0.29,2.371-0.615,3.547-0.938
      c1.057-0.312,0.651-1.758-0.283-1.864v-0.09c0.182,0,0.345-0.036,0.482-0.109c0.146,0.091,0.326,0.163,0.508,0.163
      c1.284,0,1.104-1.954-0.146-1.954c-0.109,0-0.199,0.036-0.29,0.054c-0.127-0.181-0.29-0.354-0.473-0.543
      c-0.222-0.191-0.451-0.354-0.639-0.597c0-0.019,0.813-0.435,1.376-0.706c1.664,1.213,3.202,2.606,4.67,4.072
      C778.548,779.763,780.141,781.12,780.666,782.531z" />
          <path display="inline" fill="#231F20" d="M757.861,688.002c0.104,0.489-0.021,1.104-0.579,1.268
      c-3.674,1.057-7.746,0.198-11.275-0.938c-0.561-0.182-0.812-0.543-0.778-1.141c0.135-2.497,1.438-3.729,3.149-5.347
      c0.688-0.646,1.755-0.064,1.773,0.642c0.318,0,0.634,0.018,0.923,0.033c-0.021-3.969-0.217-7.933-0.398-11.896
      c0.525,0.271,1.05,0.525,1.562,0.797c0.158,0.06,0.308,0.091,0.435,0.146c0.181,3.619,0.344,7.222,0.362,10.815
      c0,0.146-0.036,0.271-0.092,0.388C755.418,683.333,757.138,684.835,757.861,688.002z M747.256,686.681
      c2.679,0.814,5.719,1.448,8.47,0.941c-0.897-2.516-2.643-3.188-5.539-3.188c-0.521,0-0.833-0.344-0.959-0.724
      C748.288,684.6,747.545,685.396,747.256,686.681z" />
          <path display="inline" fill="#FFFFFF" d="M755.726,687.622c-2.751,0.507-5.792-0.127-8.47-0.941
      c0.29-1.277,1.032-2.081,1.979-2.968c0.126,0.38,0.435,0.725,0.959,0.725C753.083,684.438,754.821,685.106,755.726,687.622z" />
          <path display="inline" fill="#231F20" d="M728.922,778.459c1.249,0,1.412,1.955,0.163,1.955c-0.09,0-0.163,0-0.235,0
      c-0.104,0.127-0.191,0.253-0.308,0.38c0.869,0.344,1.792,0.543,2.878,0.579c0.615-2.1,2.229-4.054,3.674-5.502
      c0.895-0.887,2.396,0.362,1.484,1.267c-0.833,0.854-1.811,1.938-2.371,2.878c-0.253,0.398-0.471,0.833-0.669,1.271
      c-0.036,0.104-0.073,0.218-0.109,0.326c-0.036,0.019-0.064,0.163-0.064,0.191c-0.062,0.146-0.092,0.312-0.126,0.452
      c-0.062,0.188-0.073,0.362-0.098,0.562c0,0.018,0,0.104,0,0.163c0,0.054,0,0.104,0,0.145c0,0.163,0.021,0.325,0.035,0.488
      c0,0.019,0,0.019,0,0.036c0,0.036,0.036,0.091,0.062,0.146c0.036,0.126,0.064,0.253,0.145,0.38c0.021,0,0.021,0,0.021,0.021
      c0.035,0.036,0.054,0.091,0.09,0.127c0.037,0.055,0.163,0.217,0.163,0.231c0.071,0.059,0.145,0.104,0.198,0.159
      c0.019,0.036,0.146,0.126,0.218,0.146c0.054,0.036,0.09,0.07,0.145,0.091c0.095,0.054,0.188,0.09,0.271,0.126
      c0.018,0,0.036,0,0.036,0c0.018,0,0.035,0.021,0.064,0.021c0.188,0.058,0.398,0.067,0.604,0.094c0.054,0,0.126,0,0.145,0
      c0.127,0,0.221-0.021,0.348-0.021c0.059-0.018,0.221-0.055,0.271-0.055c0.073-0.036,0.146-0.054,0.199-0.072
      c0.091-0.036,0.217-0.07,0.325-0.104c0.019-0.021,0.104-0.072,0.192-0.108c0.199-0.104,0.381-0.231,0.579-0.396
      c0.06-0.035,0.091-0.071,0.146-0.107c0,0,0,0,0-0.02c0.09-0.097,0.181-0.188,0.271-0.271c0.108-0.126,0.199-0.253,0.29-0.38
      c0-0.021,0.056-0.072,0.072-0.108c0.018-0.018,0.036-0.055,0.054-0.091c0.146-0.253,0.271-0.521,0.38-0.796
      c0.126-0.326,0.163-0.396,0.253-0.869c0.072-0.354,0.126-0.724,0.146-1.086c0.036-0.217,0.036-0.229,0.036-0.543
      c0-0.217-0.036-0.434-0.036-0.65c-0.021-0.445-0.055-0.924-0.126-1.375c-0.188-1.104-0.489-2.188-0.869-3.233
      c-0.416-1.194,1.43-1.883,1.846-0.688c0.109,0.35,0.235,0.706,0.351,1.053c1.792-0.9,3.36-1.596,5.502-1.687
      c0.521-0.018,0.978,0.344,1.05,0.888c0.072,0.488,0.127,0.995,0.163,1.521c0.035-0.036,0.054-0.072,0.067-0.108
      c1.218-1.521,1.409-2.979,3.604-3.271c0.453-0.061,0.796,0.253,0.996,0.633c0.229,0.487,0.435,1.014,0.597,1.557
      c0.362,0.29,0.489,0.778,0.362,1.158c1.479,6.027-0.163,13.954-7.909,12.289c-0.073-0.019-0.107-0.036-0.146-0.054
      c-1.282,1.104-3.11,1.592-5.734,1.025c-1.229-0.271-0.891-2.188,0.359-1.919c6.935,1.482,6.438-6.783,5.823-11.675
      c-1.568,0.219-2.817,0.832-4.253,1.574c0.398,2.009,0.479,4.072-0.217,5.99c-0.579,1.731-1.937,3.33-3.746,3.777
      c-1.882,0.477-4.018-0.146-4.923-1.896c-0.163,0.146-0.344,0.232-0.634,0.232h-5.847c-0.312,0-0.614-0.105-0.813-0.323
      c-0.326-0.221-0.507-0.524-0.361-1.067c0.061-0.218,0.162-0.396,0.271-0.562h-2.027c-0.706,0-1.339-0.672-0.977-1.396
      c0.344-0.67,0.646-1.882,1.303-2.335c0.362-0.253,0.778-0.452,1.194-0.597c-0.225-0.38-0.398-0.778-0.579-1.194
      c-0.235-0.562,0.217-1.319,0.833-1.319h6.968c1.249,0,1.412,1.953,0.163,1.953h-2.228v-0.009h-0.009H728.922L728.922,778.459z
       M728.831,778.459h-3.095c0.036,0.036,0.064,0.072,0.091,0.108C726.858,778.459,727.89,778.459,728.831,778.459z M745.663,786.44
      c6.135,0.646,6.009-7.112,4.326-11.619c-0.729,0.521-1.159,1.7-1.647,2.312c-0.435,0.543-1.086,0.381-1.447-0.018
      C747.111,780.306,747.002,783.943,745.663,786.44z M725.211,780.885c-0.036-0.055-0.072-0.091-0.107-0.146
      c-0.572,0.104-1.141,0.218-1.115,0.146c-0.099,0.188-0.163,0.344-0.26,0.524h1.356
      C725.067,781.229,725.121,781.048,725.211,780.885z" />
          <path display="inline" fill="#FFFFFF" d="M749.989,774.821c1.683,4.507,1.81,12.271-4.326,11.619
      c1.339-2.497,1.448-6.143,1.229-9.32c0.362,0.396,1.021,0.561,1.448,0.018C748.83,776.521,749.265,775.347,749.989,774.821z" />
          <path display="inline" fill="#231F20" d="M741.537,709.395c0.326,0.388,0.217,1.122-0.235,1.384
      c-1.267,0.698-2.689,0.282-3.909,0.977c-0.271,0.146-0.543,0.163-0.771,0.091c0.09,0.635,0.181,1.271,0.217,1.955
      c0.021,0.651-0.507,1.158-1.159,1.05c-1.086-0.161-2.135-0.417-3.146-0.688c0.308,0.254,0.481,0.646,0.29,1.122
      c-0.895,2.245-3.729,2.816-3.819,5.376c-0.036,0.48-0.308,1.05-0.887,1.05c-1.884-0.021-4.271-0.183-6.244-1.014
      c-4.036,11.764-16.091,21.022-27.999,23.521c-0.562,0.108-0.996-0.416-1.086-0.9c-0.127-0.812-0.219-1.698-0.271-2.593
      c-0.229,0.163-0.471,0.325-0.688,0.488c0.018,7.782-0.108,16.234,4.271,23.021c1.068,1.646,2.208,3.222,3.479,4.688
      c1.12,1.285,3.329,2.733,3.927,4.312c0.742,2.012,0.344,5.407,0.562,7.604c0.232,2.443,0.418,4.868,0.476,7.33
      c0.02,0.67-0.525,1.031-1.053,1.031c-0.451,0-0.892-0.271-0.892-0.854c-0.104-4.741,0.452-12.705-2.819-16.288
      c-2.44-2.66-4.832-5.479-6.43-8.724c-0.308-0.604-0.561-1.213-0.812-1.812c-0.188,0.188-0.381,0.312-0.651,0.312
      c-0.344,0-0.688-0.021-1.03,0.019c0,0.021,0,0.021,0,0.021c0.545,0.055,1.03,0.452,0.959,1.123
      c-0.146,0.938-0.219,1.312-0.544,2.226c-0.797,2.172-0.034,2.407-0.435,4.615c-0.235,1.339-2.604,2.981-3.403,4.09
      c-3.638,5.015-5.81,10.498-7.364,16.472c-0.125,0.523-0.562,0.766-0.991,0.766c-0.562,0-1.104-0.416-0.927-1.121
      c1.343-5.158,3.041-10.334,6.013-14.81c1.396-2.103,4.31-4.521,4.484-7.134c0.107-1.557,0.65-2.896,1.019-4.344
      c-0.397-0.091-0.764-0.416-0.764-0.852c0-0.777-0.127-1.854,0.485-2.442c0.163-0.183,0.347-0.29,0.562-0.38
      c-0.146-0.543,0.092-1.178,0.812-1.178c0.634,0,0.996,0.481,1.032,0.988c0.054,0,0.091,0.021,0.146,0.021
      c-1.861-5.538-2.048-11.42-2.082-17.284c-4.562,3.402-8.705,7.275-11.966,11.999c-0.704,1.05-2.226-0.217-1.502-1.249
      c4.187-6.045,9.812-10.769,15.836-14.854c0.072-0.034,0.128-0.061,0.22-0.07c0.035-2.68,0.522-5.376,1.861-7.548
      c-1.953,0.325-3.963,0.036-5.972-1.122c-7.33-4.234-9.991-13.537-6.045-21.085c3.397-6.497,9.465-8.729,15.146-5.47
      c0.289-0.094,0.603-0.068,0.892,0.13c6.438,4.416,9.122,12.598,5.479,19.775c-1.316,2.686-3.547,5.146-6.171,6.59
      c-0.037,0.222-0.146,0.438-0.359,0.613c-3.108,2.646-3.188,7.803-2.682,11.71c10.877-2.643,22.153-11.511,25.56-22.334
      c-0.475-0.38-0.902-0.832-1.214-1.375c-1.86-3.06-0.347-6.335,1.481-8.995c0.727-1.05,2.208,0.224,1.502,1.248
      c-0.09,0.146-0.199,0.312-0.309,0.453c0.218,0.108,0.381,0.271,0.472,0.543c0.036,0.092,0.09,0.199,0.126,0.312
      c0.054-0.741,0.905-1.376,1.557-0.729l1.701,1.727c0.163,0,0.326,0.02,0.508,0.02c0.234,0,0.416,0.071,0.562,0.162
      c0.356-0.218,0.796-0.235,1.14,0.055c0.22,0.188,0.326,0.488,0.347,0.778c0.485,0.019,0.926,0.325,0.979,0.869
      c0.054,0.506-0.362,1.062-0.889,1.062c-0.51,0-1.157,0.061-1.688-0.066c-0.29,0.163-0.67,0.163-0.996-0.163l-0.899-0.904
      c-0.452-0.091-0.892-0.253-1.319-0.507c0.071,0.091,0.104,0.182,0.163,0.254c0.104,0.198,0.23,0.396,0.36,0.578
      c0.575-0.289,1.375-0.091,1.354,0.725c-0.019,0.688-0.146,1.342-0.852,1.63c-0.095,0.061-0.198,0.061-0.271,0.071
      c0.813,0.488,1.665,0.979,2.521,1.412c0.356,0.198,0.521,0.543,0.521,0.869c0.058,0,0.091,0,0.146,0
      c0.127-0.925,0.45-1.757,1.155-2.498c0.992-1.016,2.188-1.376,2.73-2.787c0.146-0.345,0.38-0.487,0.65-0.543
      c-4.854-1.483-8.928-4.182-12.289-8.271c-0.478-0.545-0.021-1.521,0.706-1.521c0.852-0.021,1.663-0.037,2.4-0.29
      c-0.161-0.146-0.308-0.345-0.355-0.614c-0.253-1.479-1.688-2.335-1.954-3.905c-0.072-0.396,0.229-0.903,0.633-1.019
      c7.258-2.062,12.506,1.899,16.995,6.81c0-0.059-0.021-0.129,0-0.186c0.507-5.268-7.964-7.781-11.945-8.646
      c-1.213-0.271-0.869-2.188,0.362-1.938c3.511,0.777,8.688,2.406,11.527,5.447c-0.513-4.765-5.598-5.411-9.664-5.466
      c-1.271,0-1.437-1.955-0.163-1.938c5.283,0.036,11.639,1.284,11.812,7.729c0,0.634-0.47,0.991-0.959,1.026
      c0.729,1.193,1.086,2.558,0.941,4.126c-0.059,0.638-0.602,0.854-1.104,0.766C739.636,707.114,740.595,708.271,741.537,709.395z
       M736.27,710.137c0.905-0.507,1.9-0.435,2.859-0.607c-2.643-3.239-5.484-6.896-9.267-8.705c-1.122-0.514-2.27-1.032-3.479-1.159
      c-0.145,0.019-4.507-0.217-3.602,0.761c0.561,0.615,0.887,1.104,1.104,1.737c0.037,0,0.099-0.021,0.127-0.021
      c5.854,0.146,10.592,2.938,12.225,8.036C736.234,710.173,736.234,710.155,736.27,710.137z M734.768,712.671
      c-0.688-5.158-4.506-8.104-9.693-8.521c0,0.271-0.109,0.543-0.398,0.733c-0.775,0.562-1.574,0.854-2.425,1.021
      c1.141,1.177,2.335,2.227,3.638,3.105c0.021,0,0.061-0.019,0.092-0.019c0.513,0,0.812,0.309,0.959,0.67
      C729.284,711.096,731.908,712.092,734.768,712.671z M721.99,716.271c0.145,0.091,0.308,0.182,0.452,0.271
      c-0.854-1.014-1.448-2.28-1.918-3.529c-0.29,0.706-0.515,1.438-0.544,2.144c0.472,0.229,0.924,0.48,1.395,0.76
      c0.217,0.036,0.417,0.127,0.579,0.344L721.99,716.271z M702.643,720.924c1.763-5.213,0.543-10.188-2.979-13.882
      c-0.217,0.018-0.438-0.055-0.633-0.235c-5.9-5.538-13.122-3.203-15.656,4.352c-2.153,6.369-0.021,15.891,7.602,17.592
      C696.362,729.955,700.959,725.811,702.643,720.924z" />
          <path display="inline" fill="#231F20" d="M737.193,718.951c0.396-1.195,2.172-0.344,1.755,0.832
      c-0.796,2.39-1.688,4.561-0.941,7.077c0.362,1.188-1.466,1.882-1.828,0.688C735.238,724.453,736.197,721.883,737.193,718.951z" />
          <path display="inline" fill="#FFFFFF" d="M739.129,709.521c-0.959,0.181-1.955,0.104-2.859,0.607
      c-0.028,0.021-0.028,0.036-0.028,0.036c-1.629-5.104-6.371-7.891-12.225-8.036c-0.028,0-0.09,0.021-0.127,0.021
      c-0.217-0.639-0.543-1.122-1.104-1.737c-0.905-0.979,3.457-0.742,3.602-0.761c1.212,0.127,2.354,0.646,3.479,1.156
      C733.646,702.626,736.487,706.282,739.129,709.521z" />
          <path display="inline" fill="#FFFFFF" d="M725.067,704.146c5.188,0.416,9.013,3.358,9.693,8.521
      c-2.854-0.579-5.479-1.575-7.836-3.005c-0.145-0.361-0.452-0.67-0.959-0.67c-0.028,0-0.064,0.019-0.09,0.019
      c-1.303-0.887-2.498-1.937-3.638-3.105c0.851-0.162,1.646-0.452,2.425-1.021C724.958,704.689,725.067,704.418,725.067,704.146z" />
          <path display="inline" fill="#231F20" d="M727.401,726.787c0.521-1.158,2.299-0.325,1.771,0.833
      c-0.905,1.973-0.887,4.217-0.887,6.335c0,1.267-1.955,1.447-1.955,0.181C726.333,731.656,726.352,729.086,727.401,726.787z" />
          <path display="inline" fill="#FFFFFF" d="M725.736,778.459h3.095c-0.941,0-1.973,0-3.004,0.108
      C725.809,778.531,725.771,778.495,725.736,778.459z" />
          <path display="inline" fill="#FFFFFF" d="M725.104,780.739c0.035,0.055,0.071,0.091,0.107,0.146
      c-0.09,0.163-0.145,0.344-0.126,0.524h-1.356c0.097-0.188,0.162-0.344,0.26-0.524
      C723.963,780.957,724.524,780.849,725.104,780.739z" />
          <path display="inline" fill="#FFFFFF" d="M722.442,716.544c-0.146-0.091-0.312-0.182-0.452-0.271l-0.037-0.021
      c-0.163-0.217-0.362-0.308-0.579-0.344c-0.47-0.271-0.923-0.524-1.394-0.76c0.028-0.706,0.253-1.438,0.543-2.144
      C720.995,714.264,721.592,715.53,722.442,716.544z" />
          <path display="inline" fill="#FFFFFF" d="M699.656,707.042c3.521,3.692,4.742,8.669,2.979,13.882
      c-1.683,4.887-6.272,9.031-11.674,7.818c-7.62-1.701-9.755-11.221-7.602-17.592c2.529-7.547,9.755-9.89,15.65-4.352
      C699.222,706.987,699.439,707.06,699.656,707.042z M701.177,719.566c1.067-0.688-0.055-2.28-1.123-1.601
      c-1.267,0.832-6.146,3.818-6.272,0.729c-0.062-1.271-2.027-1.104-1.955,0.163C692.055,724.271,698.643,721.231,701.177,719.566z
       M696.073,711.856c0.453-0.235,0.771-0.729,0.579-1.249c-0.188-0.479-0.778-0.814-1.271-0.579
      c-0.271,0.127-0.543,0.253-0.812,0.354c-0.253-0.345-0.768-0.562-1.146-0.354c-0.487,0.253-1.086,0.507-1.339,1.023
      c-0.253,0.544-0.018,1.271,0.562,1.484C693.72,712.942,695.131,712.327,696.073,711.856z" />
          <path display="inline" fill="#231F20" d="M700.054,717.974c1.068-0.688,2.19,0.905,1.123,1.601
      c-2.534,1.665-9.122,4.705-9.354-0.706c-0.072-1.271,1.896-1.438,1.955-0.163C693.901,721.792,698.788,718.806,700.054,717.974z" />
          <path display="inline" fill="#231F20" d="M696.652,710.607c0.191,0.521-0.126,1.014-0.579,1.249
      c-0.941,0.471-2.354,1.086-3.421,0.688c-0.579-0.217-0.814-0.94-0.562-1.484c0.253-0.521,0.854-0.771,1.339-1.023
      c0.38-0.199,0.895,0.018,1.146,0.354c0.271-0.104,0.543-0.229,0.812-0.354C695.874,709.793,696.471,710.137,696.652,710.607z" />
        </g>
        <g id="stick_water" display="none">
          <g display="inline">
            <path fill="#231F20" d="M782.246,748.191c-0.341-3.352-5.159-5.127-7.775-5.944c-3.97-1.239-8.134-1.903-12.307-2.035
        c-4.831-0.438-9.692-0.562-14.543-0.434c0.015-0.021,0.021-0.034,0.042-0.05c0.137-0.152,0.286-0.304,0.431-0.44
        c0.147-0.147,0.197-0.354,0.18-0.553c0.438,0.008,0.868-0.277,0.812-0.831c-1.092-9.438,0.097-20.938-7.49-27.991
        c-1.186-1.104-3.46-2.416-5.062-1.336c-2.415,1.626-2.179,5.283-1.915,7.951c-1.086,0.249-2.151,0.661-3.192,1.083
        c-0.034-4.367,0.685-9.438,4.172-12.229c4.271-3.396,10.354,3.572,12.359,6.838c4.559,7.407,4.162,18.169,2.744,26.405
        c-0.183,1.021,1.396,1.311,1.567,0.289c1.74-10.104,1.854-23.638-5.98-31.373c-3.312-3.261-7.934-6.407-12.209-2.989
        c-4.146,3.312-4.438,10.312-4.19,15.084c0.058,1.032,1.647,0.896,1.597-0.138c-0.006-0.062-0.008-0.127-0.01-0.188
        c1.314-0.539,2.692-1.146,4.08-1.354c0.436-0.06,0.772-0.413,0.729-0.864c-0.165-1.688-0.979-8.271,2.495-7.123
        c1.988,0.663,3.443,2.854,4.538,4.52c4.23,6.479,3.312,16.058,4.17,23.497c-0.229,0.007-0.44,0.1-0.61,0.271
        c-0.304,0.312-0.604,0.592-0.812,0.979c-0.104,0.185-0.146,0.389-0.159,0.597c-3.238,0.134-6.479,0.385-9.688,0.751
        c-10.983,1.245-22.354,4.054-31.598,10.344c-0.303,0.206-0.404,0.604-0.288,0.938c0.243,0.688,0.55,1.33,0.884,1.938
        c0.247,2.105,0.759,4.034,1.485,5.812c-0.58-0.006-1.062,0.61-0.673,1.229c1.589,2.547,3.584,4.588,5.846,6.249
        c0.778,0.739,1.616,1.425,2.491,2.068c2.443,2.095,5.146,3.847,7.977,5.546c0.6,0.354,1.217-0.3,1.12-0.88
        c6.335,2.212,13.396,3.028,19.628,2.866c1.486-0.039,3.033-0.146,4.615-0.312h1.453c0.259,0,0.438-0.103,0.56-0.255
        c13.989-1.97,29.836-9.152,31.276-24.168C781.871,750.876,782.392,749.628,782.246,748.191z M706.38,751.747
        c0.209-0.441,0.738-0.735,1.385-1.048c3.498,6.298,11.564,7.366,15.058,0.884c2.817,3.465,8.922,5.914,11.309,1.465
        c4.19,4.209,10.423,3.897,12.903-1.757c3.504,2.588,9.826,4.298,11.335-0.347c3.306,3.337,7.437,3.336,11.229,0.313
        c0.312,0.62,0.739,1.188,1.372,1.623c1.348,0.938,3.026,1.021,4.657,0.785c-2.862,1.606-6.225,2.854-7.834,3.376
        c-11.836,3.854-25.084,5.229-37.478,4.896c-6.189-0.166-12.729-0.971-18.298-3.813c-2.146-1.095-3.949-2.688-5.238-4.717
        c-0.04-0.366-0.084-0.73-0.107-1.105C706.655,752.047,706.541,751.861,706.38,751.747z M747.212,774.78h-3.001
        c-0.947-0.021-1.888-0.069-2.827-0.146c-1.729-0.415-3.452-0.771-5.188-1.021c-0.021-0.005-0.027-0.018-0.042-0.021
        c-0.104-0.197-0.271-0.354-0.529-0.396c-0.403-0.064-0.809-0.128-1.216-0.192c-1.164-0.381-2.333-0.743-3.504-1.104
        c-1.777-0.677-3.542-1.373-5.277-2.149c0.326-0.356,0.325-1.017-0.208-1.288c-4.954-2.55-9.596-5.604-14.229-8.688
        c-0.722-0.479-1.743,0.291-1.17,1.072c0.521,0.712,1.063,1.402,1.63,2.071c-0.786-0.562-1.575-1.117-2.364-1.673
        c-0.792-1.391-1.438-2.89-1.881-4.521c4.594,4.611,12.382,5.981,18.562,6.562c11.354,1.062,23.063-0.308,34.185-2.626
        c4.385-0.918,8.63-2.131,12.781-3.812c1.73-0.691,4.258-1.741,6.237-3.218C776.685,767.486,760.245,773.368,747.212,774.78z
         M780,750.099c-0.25,0.113-0.443,0.349-0.49,0.659c-0.312,0.364-0.688,0.729-1.117,1.074c-0.188-0.186-0.461-0.296-0.778-0.207
        c-1.771,0.483-3.833,0.918-5.561,0.061c-1.681-0.835-1.577-3.22-1.381-4.765c0.128-1.021-1.438-1.312-1.576-0.292
        c-0.123,0.974-0.149,2.004-0.009,2.979c-3.604,3.073-7.5,3.184-10.55-0.896c-0.38-0.504-1.387-0.413-1.441,0.319
        c-0.438,5.937-7.394,2.627-9.896,0.331c-0.49-0.447-1.136-0.065-1.303,0.468c-1.844,5.949-7.936,5.625-11.361,1.282
        c-0.456-0.577-1.244-0.354-1.44,0.312c-1.625,5.524-8.185,0.873-9.76-1.918c-0.291-0.512-1.17-0.41-1.377,0.12
        c-2.536,6.496-9.688,6.038-12.771,0.336c1.979-1.118,4.084-2.021,6.188-2.866c12.33-4.917,26.499-6.475,40.146-5.812
        c0.05,0.385,0.333,0.729,0.831,0.688c1.848-0.17,3.759-0.243,5.686-0.202c2.606,0.264,5.182,0.586,7.702,0.993
        c0.178,0.03,0.325-0.01,0.455-0.071c2.548,0.562,5.015,1.372,7.285,2.52C780.618,746.779,781.009,748.482,780,750.099z" />
            <path fill="#231F20" d="M735.193,722.959c-1.062-1.003-2.224-0.461-2.607,0.818c-0.301,0.978-0.305,2.048-0.305,3.062
        c0,1.033,1.594,0.886,1.594-0.138c0-0.646,0.026-1.271,0.118-1.901c0.034-0.229,0.084-0.435,0.153-0.635
        c0.01,0.008,0.019,0.018,0.019,0.017C734.914,724.896,735.938,723.667,735.193,722.959z" />
            <path fill="#231F20" d="M733.732,738.312c-0.688-0.747-0.656-1.855-0.657-2.812c-0.001-1.033-1.595-0.885-1.594,0.141
        c0.001,1.229,0.068,2.646,0.938,3.601C733.111,739.988,734.428,739.075,733.732,738.312z" />
            <path fill="#231F20" d="M735.021,747.651c-1.229-0.673-1.146-2.123-1.146-3.354c-0.001-1.026-1.596-0.885-1.594,0.146
        c0.002,1.896,0.243,3.663,2.055,4.647C735.245,749.597,735.92,748.142,735.021,747.651z" />
            <path fill="#231F20" d="M734.392,727.76c-0.354-0.299-0.812-0.208-1.126,0.104c-0.268,0.266-0.526,0.523-0.8,0.799
        c-0.309,0.308-0.214,0.866,0.104,1.127c0.354,0.298,0.812,0.208,1.126-0.099c0.267-0.271,0.526-0.531,0.801-0.808
        C734.801,728.577,734.705,728.021,734.392,727.76z" />
          </g>
          <path display="inline" fill="#231F20" d="M728.049,740.41c-11.408-3.113-23.85-6.501-35.197-1.954
      c-0.172-2.699-0.319-5.361,0.203-8.108c0.032-0.166,0.026-0.312-0.006-0.438c5.868-0.253,9.389-6.933,10.514-11.993
      c1.692-7.624-1.611-15.446-9.023-18.35c-1.229-0.479-2.105,1.361-0.876,1.852c7.468,2.924,10.304,13.539,6.81,20.555
      c-2.086-1.055-4.021-2.191-5.714-3.845c-0.948-0.922-2.256,0.652-1.312,1.568c1.759,1.711,3.802,2.925,5.966,4.021
      c-1.222,1.663-2.884,3.02-5.036,3.825c-6.78,2.563-10.511-5.702-10.741-10.942c-0.269-6.162,3.365-15.854,11.058-12.109
      c1.188,0.572,2.062-1.276,0.875-1.854c-14.678-7.129-20.338,24.529-4.432,27.142c-0.025,0.065-0.066,0.125-0.086,0.209
      c-0.604,3.136-0.428,6.258-0.229,9.396c-0.054,0.024-0.103,0.041-0.146,0.065c-1.01,0.505-0.62,2.018,0.271,2.021
      c0.039,0.697,0.067,1.396,0.092,2.091c0.001,6.514,0.021,12.998,1.021,19.444c0.122,0.786,0.923,1.028,1.487,0.806
      c4.041,4.232,7.035,9.229,10.411,13.979c1.312,1.85,1.958,5.13,3.744,6.545c0.501,0.396,1.312,0.271,1.583-0.352
      c0.021-0.059,0.046-0.104,0.062-0.157c0.283-0.656-0.131-1.201-0.646-1.389c-0.659-0.812-1.161-2.354-1.307-3.231
      c-0.233-1.519-2.424-3.509-3.3-4.775c-2.939-4.269-5.729-8.541-9.343-12.293c-0.254-0.264-0.536-0.329-0.793-0.271
      c-0.804-5.729-0.881-11.484-0.89-17.264c7.394,0.698,14.994,0.43,20.834,5.793c0.974,0.896,2.279-0.678,1.312-1.562
      c-3.892-3.581-8.382-4.949-13.556-5.562c-1.107-0.13-8.604-0.052-8.629-1.284c-0.02-0.446-0.031-0.896-0.06-1.344
      c11.175-4.729,23.438-1.281,34.698,1.787C728.955,742.773,729.311,740.755,728.049,740.41z" />
          <path display="inline" fill="#231F20" d="M697.821,709.935c-0.863-0.771-1.95-0.569-2.675,0.267
      c-0.85,0.97-1.026,2.456-1.056,3.684c-0.009,0.562,0.604,0.979,1.108,0.938c0.601-0.06,0.92-0.559,0.931-1.107
      c0.017-0.574,0.11-1.164,0.318-1.7c0.058-0.146,0.13-0.274,0.212-0.399C697.626,712.193,698.752,710.766,697.821,709.935z" />
          <path display="inline" fill="#231F20" d="M690.099,763.492c-4.344,7.741-7.984,15.974-9.292,24.812
      c-0.188,1.312,1.823,1.684,2.021,0.371c1.271-8.675,4.866-16.714,9.128-24.309C692.597,763.214,690.741,762.349,690.099,763.492z" />
        </g>
        <g id="stick_stream" display="none">
          <g display="inline">
            <path fill="#231F20" d="M758.391,716.75c-0.04-0.014-0.08-0.024-0.11-0.037c-0.062-0.025-0.107-0.039-0.164-0.051
        c-3.645-1.169-7.651-0.021-11.231,0.886c-1.753,0.441-3.502,0.952-5.256,1.459c-0.005-0.056,0.005-0.104-0.011-0.164
        c-3.101-11.347-20.61-12.729-23.688-0.682c-0.262,1.021,1.354,1.312,1.612,0.295c2.712-10.629,17.912-8.672,20.537,0.958
        c0.003,0.013,0.013,0.02,0.017,0.032c-3.808,1.08-7.646,2.042-11.604,2.142c-0.149,0.004-0.271,0.046-0.37,0.104
        c-0.073-0.039-0.146-0.078-0.23-0.101c-9.651-1.659-19.962-3.854-28.46-8.938c-0.904-0.543-1.598,0.938-0.692,1.479
        c0.188,0.116,0.393,0.229,0.588,0.336c-0.285,0.371-0.306,0.998,0.229,1.311c2.889,1.646,3.188,6.011,3.417,8.938
        c0.334,4.172-0.008,8.398-0.456,12.562c-0.1-0.238-0.222-0.473-0.428-0.649c-0.675-0.6-1.572-0.58-2.427-0.43
        c-0.1-0.54-0.047-1.073,0.034-1.615c0.021,0.011,0.041,0.021,0.062,0.03c0.312,0.146,0.61,0.313,0.909,0.494
        c0.104,0.062,0.206,0.122,0.308,0.188c0.021,0.008,0.024,0.014,0.035,0.021c0.002,0,0.002,0,0.005,0.004c0,0,0.001,0,0.002,0
        c0.066,0.041,0.044,0.021,0.015,0.005c0.867,0.557,1.921-0.685,1.041-1.259c-0.688-0.452-1.421-0.871-2.188-1.2
        c-0.062-0.625-0.771-0.735-1.229-0.415c-0.179-0.044-0.354-0.104-0.526-0.132c0.021-0.013,0.029-0.021,0.062-0.022
        c0.294-0.099,0.671-0.062,0.979-0.062c0.521,0,0.75-0.403,0.718-0.812c0.034-0.004,0.065-0.012,0.104-0.013
        c1.061-0.048,0.909-1.679-0.146-1.632c-0.423,0.018-0.813,0.112-1.194,0.26c0.663-0.849,2.271-0.706,3.188-1.133
        c0.947-0.442,0.391-1.983-0.57-1.536c-0.801,0.372-1.674,0.403-2.507,0.662c-0.946,0.295-1.573,1.079-1.952,1.962
        c-0.18,0.42-0.216,0.917-0.076,1.332c-0.437,0.552-0.315,1.396-0.04,2.011c0.166,0.368,0.444,0.521,0.84,0.531
        c0.229,0.007,0.448,0.05,0.673,0.104c-0.152,1.188-0.425,2.203,0.128,3.458c0.193,0.45,0.599,0.587,1.055,0.482
        c0.371-0.086,1.464-0.452,1.688,0.062c0.197,0.446,0.301,0.903,0.635,1.281c0.161,0.188,0.356,0.271,0.562,0.287
        c-0.852,7-2.197,13.972-3.881,20.814c-0.4,0.188-0.812,0.39-1.21,0.588c-0.938,0.462-0.377,2.004,0.565,1.534
        c3.812-1.888,7.664-3.646,11.562-5.322c0.028-0.013,0.059-0.022,0.085-0.035c-0.886,1.104-1.181,2.146-1.057,3.109
        c-0.541-0.096-1.127-0.101-1.771,0.021c-1.033,0.18-0.896,1.812,0.145,1.631c1.831-0.314,3.193,0.74,4.294,2.438
        c-2.18,1.188-4.628,2.881-6.896,3.304c-1.026,0.193-0.896,1.826,0.146,1.633c2.119-0.396,4.275-1.947,6.188-2.892
        c0.438-0.224,0.896-0.444,1.344-0.669c1.752,3.538,2.801,8.534,4.281,10.734c-0.146,0.229-0.238,0.402-0.337,0.66
        c-0.155,0.401,0.007,0.748,0.271,0.961c-0.006,0.199,0,0.396-0.016,0.603c-0.021,0.489-0.062,0.979-0.104,1.474
        c-0.337-0.086-0.646-0.161-0.902-0.208c-1.066-0.194-1.276,1.312-0.298,1.61c0.329,0.102,0.662,0.18,0.992,0.269
        c0.019,0.055,0.024,0.107,0.062,0.158c-0.035,0.432-0.063,0.862-0.083,1.296c-0.017,0.371,0.345,0.657,0.667,0.734
        c3.149,0.762,7.327,1.47,10.812,0.819c1.203-0.077,2.397-0.354,3.547-0.857c0.354-0.16,0.525-0.45,0.525-0.839v-0.072
        c1.229-1.027,2.115-2.521,2.447-4.622c0.063-0.423-0.161-0.713-0.462-0.854c0.992-1.592,1.794-3.294,2.475-4.672
        c1.064-2.149,2.036-4.438,3.119-6.663c2.084,0.919,4.104,1.997,6.261,2.764c-0.798,5.637-2.035,11.188-2.975,16.806
        c-0.562,3.354,3.292,4.521,6.188,4.747c1.044,0.082,1.349-1.528,0.295-1.61c-1.202-0.094-5.197-0.681-4.872-2.841
        c0.285-1.873,0.652-3.729,1.016-5.595c0.759-3.958,1.521-7.906,2.062-11.903c0.062-0.479-0.218-0.812-0.655-0.954
        c-2.271-0.691-4.356-1.811-6.508-2.787c0.256-0.479,0.521-0.956,0.809-1.426c-0.046-0.006-0.09-0.014-0.138-0.02
        c1.067-1.858,2.311-3.604,3.854-5.082c0.497-0.479,0.134-1.083-0.391-1.249c0.234-0.729,0.429-1.494,0.553-2.312
        c0.675-0.051,1.352-0.102,2.021-0.135c0.771-0.038,1.538-0.05,2.309-0.062c0.229-0.157,0.453-0.314,0.688-0.478
        c-0.104-0.39-0.204-0.78-0.288-1.171c-0.277,0.083-0.56,0.176-0.834,0.271c-0.128-0.472-0.23-0.938-0.327-1.409
        c0.245-0.9,0.454-1.812,0.633-2.729c-0.117-2.008,0.016-3.999,0.354-5.954c-0.062-1.063-0.052-2.131,0.026-3.188
        c-0.068-0.076-0.146-0.146-0.241-0.188c0.091-0.02,0.184-0.047,0.257-0.094c1.057-0.604,3.845-1.714,4.001-3.244
        c0.106-1.007-0.607-1.943-1.036-2.886c1.875-1.604,1.432-3.04-0.303-4.832c0.292-0.521,0.611-1.021,0.966-1.491
        c0.997-1.336,1.932-2.646,2.902-3.887c1.086-1.852,2.193-3.619,3.277-5.271c0.052-0.071,0.063-0.146,0.095-0.227
        C758.959,717.414,758.904,716.933,758.391,716.75z M700.267,759.436c2.031-8.479,3.558-17.146,4.224-25.848
        c0.355-4.729,1.264-13.188-2.236-17.555c7.604,3.709,16.298,5.597,24.525,7.031c-1.81,9.089-5.818,17.781-4.854,27.188
        c-1.396,0.13-3.229,1.262-4.534,1.784c-0.658,0.266-1.312,0.544-1.977,0.812c0.101-0.734-0.878-1.399-1.399-0.671
        c-0.396,0.566-0.717,1.113-0.968,1.646c-0.959,0.396-1.924,0.784-2.882,1.186C706.827,756.408,703.529,757.884,700.267,759.436z
         M711.791,762.641c-0.45-0.638-0.963-1.188-1.559-1.609c-0.621-1.194-0.271-2.604,1.771-4.2c0.325-0.256,0.374-0.617,0.267-0.923
        c0.053-0.021,0.098-0.039,0.144-0.062c-0.111,0.752-0.07,1.46,0.098,2.125c0.502-0.204,1.005-0.4,1.519-0.601
        c-0.113-0.704-0.053-1.466,0.255-2.293c0.266-0.108,0.523-0.228,0.796-0.328c2.229-0.916,5.551-2.951,8.08-2.619
        c-0.389-0.035-0.812,0.142-0.957,0.604c-0.771,2.442-0.824,4.98-0.828,7.521c-0.027,0.001-0.047-0.008-0.075-0.001
        c-0.083,0.016-0.165,0.039-0.248,0.056c-0.116-0.014-0.229-0.023-0.352-0.045c-0.328-0.04-0.571,0.104-0.734,0.311
        c-2.423,0.646-4.735,1.691-7.005,2.768C712.528,763.117,712.143,762.885,711.791,762.641z M734.741,764.197
        c-3.651,1.561-8.021,2.013-11.958,1.771c-1.609-0.096-5.009-0.695-7.906-1.792c2.018-0.932,4.087-1.757,6.209-2.217
        C725.664,762.546,730.195,763.42,734.741,764.197z M719.446,780.928c-0.006-0.005-0.02-0.01-0.021-0.014
        c-0.23-0.167-0.48-0.183-0.711-0.119c-0.418-0.074-0.839-0.146-1.256-0.236c0.011-0.144,0.023-0.288,0.035-0.432
        c0.042-0.555,0.122-1.161,0.146-1.765c1.044,1.064,2.278,2.062,3.613,2.811C720.652,781.106,720.048,781.023,719.446,780.928z
         M730.307,781.546h-0.012c-0.252,0-0.505-0.004-0.757-0.004c0.723-0.458,1.382-1.048,1.979-1.726
        C731.214,780.513,730.796,781.077,730.307,781.546z M735.5,769.367c-1.825,3.911-4.208,10.878-9.175,11.595
        c-2.972,0.428-5.802-2.3-7.743-4.152c-2.356-2.25-3.229-5.349-4.236-8.352c-0.352-1.047-0.746-2.231-1.229-3.374
        c2.485,1.226,5.424,2.031,7.444,2.312c5.105,0.698,11.059,0.188,15.737-2.135c0.328,0.107,0.654,0.225,0.979,0.347
        C736.663,766.864,736.078,768.128,735.5,769.367z M737.139,762.932c-4.72-0.766-9.41-1.688-14.146-2.357
        c0.002-2.559,0.04-5.082,0.809-7.539c0.152-0.488-0.146-0.807-0.534-0.894c0.029,0.013,0.062,0.013,0.086,0.018
        c1.319,0.247,2.64,0.508,3.944,0.769c-0.013,0.01-0.021,0.021-0.035,0.029c0.643-0.047,1.274-0.071,1.92-0.071h0.364
        c0.421,0,0.841,0.019,1.259,0.033c0.146,0.008,0.286,0.021,0.436,0.028c0.271,0.017,0.548,0.036,0.812,0.062
        c0.167,0.021,0.333,0.034,0.497,0.06c0.253,0.022,0.505,0.062,0.756,0.1c0.164,0.021,0.328,0.046,0.491,0.072
        c0.281,0.047,0.562,0.1,0.839,0.146c0.123,0.023,0.246,0.047,0.368,0.071c0.404,0.085,0.805,0.18,1.203,0.282
        c0.052,0.013,0.103,0.022,0.155,0.044c0.033,0.008,0.062,0.02,0.099,0.021c-0.598,0.354-0.364,1.526,0.527,1.526h5.498
        C741.732,758.791,739.752,761.25,737.139,762.932z M750.072,733.51c0.251,0.896,0.697,1.612,1.104,2.438
        c0.183,0.34-1.038,1.104-2.054,1.679c-0.021-0.193-0.051-0.385-0.09-0.573c0.088-0.277,0.169-0.562,0.263-0.845
        c0.144-0.833,0.289-1.679,0.447-2.552c0.181-0.987,0.457-1.979,0.815-2.923c0.18,0.06,0.371,0.032,0.554-0.025
        c0.356,0.588,0.27,1.121-0.753,1.854C750.05,732.784,749.971,733.15,750.072,733.51z M750.854,728.276
        c-0.146,0.003-0.297,0.062-0.422,0.151c-0.317-0.209-0.799-0.112-1.039,0.17c-0.305,0.36-0.214,0.844,0.104,1.15l0.461,0.462
        c-0.71,1.574-1.37,3.173-1.935,4.796c-0.684-0.443-1.705,0.391-1.133,1.146c0.229,0.289,0.364,0.595,0.472,0.913
        c-1.438,4.795-2.042,9.71-1.229,14.479c-0.203,0.696-0.422,1.396-0.669,2.072c-3.91,0.049-7.762-0.376-11.643-1.053
        c-3.065-0.533-6.943-2.078-10.259-2.317c-1.011-9.354,3.088-18.029,4.854-27.07c0.068,0.021,0.131,0.038,0.21,0.037
        c4.604-0.114,9.082-1.435,13.484-2.668c4.259-1.188,10.046-3.663,14.646-2.591C754.722,721.12,752.646,724.606,750.854,728.276z" />
            <path fill="#231F20" d="M726.804,717.072c0.2-0.324,0.104-0.793-0.177-1.028c-0.347-0.292-0.783-0.213-1.099,0.062
        c-0.021,0.006-0.035,0.002-0.06,0.01c-0.595,0.259-0.732,1.051-0.229,1.438c0.003,0.025,0.008,0.059,0.012,0.077
        c0.029,0.246,0.032,0.495,0.033,0.743c0.003,0.445,0.469,0.78,0.887,0.743c0.479-0.041,0.747-0.44,0.744-0.886
        C726.915,717.848,726.896,717.449,726.804,717.072z" />
            <path fill="#231F20" d="M732.583,716.674c-0.475,0.042-0.745,0.442-0.745,0.889v0.818c0,0.445,0.471,0.78,0.887,0.743
        c0.479-0.041,0.744-0.44,0.744-0.886v-0.819C733.469,716.972,732.997,716.638,732.583,716.674z" />
            <path fill="#231F20" d="M716.625,727.396c-2.71-0.895-5.479-1.573-8.189-2.459c-0.985-0.322-1.693,1.146-0.692,1.479
        c2.71,0.884,5.479,1.57,8.188,2.456C716.917,729.201,717.626,727.722,716.625,727.396z" />
            <path fill="#231F20" d="M715.604,732.244c-2.212-0.689-4.469-0.827-6.771-0.829c-1.057-0.001-0.905,1.63,0.142,1.632
        c2.162,0.002,4.267,0.162,6.333,0.812C716.316,734.17,716.598,732.555,715.604,732.244z" />
            <path fill="#231F20" d="M743.448,734.702c-4.19-0.777-8.546-0.913-12.718,0.037c-1.026,0.229-0.445,1.767,0.563,1.535
        c3.873-0.889,7.979-0.688,11.854,0.029C744.192,736.504,744.485,734.894,743.448,734.702z" />
            <path fill="#231F20" d="M738.534,741.253c-3.319-0.828-6.613-1.585-10.046-1.646c-1.062-0.021-0.902,1.604,0.142,1.63
        c3.276,0.062,6.445,0.839,9.608,1.629C739.266,743.12,739.552,741.507,738.534,741.253z" />
            <path fill="#231F20" d="M735.039,746.975c-2.143-0.046-4.209-0.635-6.13-1.562c-0.953-0.46-1.65,1.021-0.701,1.479
        c2.195,1.062,4.533,1.66,6.973,1.721C736.237,748.631,736.083,746.997,735.039,746.975z" />
            <path fill="#231F20" d="M715.453,741.987c-0.024-0.479-0.455-0.744-0.887-0.744h-4.914c-0.711,0-0.864,0.729-0.576,1.216
        c-0.104,0.132-0.169,0.289-0.169,0.489c0.001,1.468-0.084,3.271,0.938,4.458c-0.339,0.702,0.465,1.646,1.17,1.054
        c0.604-0.512,1.497-0.617,2.244-0.781c0.854-0.188,1.688-0.394,2.482-0.759c0.344-0.157,0.55-0.451,0.53-0.839
        C716.201,744.688,715.521,743.381,715.453,741.987z M711.196,746.479c-0.812-0.931-0.663-2.438-0.658-3.604h3.377
        c0.168,0.922,0.506,1.817,0.655,2.739C713.495,745.962,712.251,746.069,711.196,746.479z" />
            <path fill="#231F20" d="M735.878,697.55c-0.438-0.953-1.977-0.392-1.535,0.571c0.772,1.681,0.771,3.699,0.771,5.519
        c0,1.062,1.632,0.904,1.631-0.144C736.744,701.534,736.71,699.362,735.878,697.55z" />
            <path fill="#231F20" d="M743.833,702.801c-1.694,0.863-2.865,1.993-3.729,3.693c-0.479,0.943,1.009,1.639,1.48,0.7
        c0.658-1.293,1.523-2.197,2.821-2.857C745.332,703.862,744.774,702.32,743.833,702.801z" />
          </g>
          <path display="inline" fill="#231F20" d="M704.686,768.631c-0.546-0.546-1.092-1.093-1.638-1.638
      c-0.074-0.074-0.146-0.148-0.224-0.229c-0.315-0.312-0.885-0.22-1.153,0.104c-0.209,0.25-0.223,0.55-0.114,0.812
      c-0.354,0.096-0.67,0.363-0.67,0.823v16.324c-0.092-0.003-0.186,0.001-0.288,0.035l-7.371,2.456
      c-1.002,0.335-0.431,1.864,0.571,1.532l7.37-2.459c0.044-0.016,0.069-0.041,0.107-0.062c0.479,0.227,1.234-0.062,1.234-0.776
      V768.77c0.371,0.371,0.742,0.742,1.115,1.113c0.315,0.317,0.885,0.22,1.153-0.101
      C705.092,769.419,705.001,768.945,704.686,768.631z" />
        </g>
        <g id="stick_weather" display="none">
          <path display="inline" fill="#231F20" d="M722.09,695.3c-0.135-1.161-1.945-1.006-1.809,0.157
      c1.062,9.082,3.103,18.286,2.202,27.467c-0.38,3.869-1.786,8.408-5.126,10.713c-1.127,0.778-2.088,0.88-2.908,0.553
      c0.229-0.173,0.404-0.433,0.378-0.762c-0.046-0.604-0.188-1.226-0.374-1.846c-0.281-1.611-0.464-3.243-0.722-4.858
      c0.249-0.416,0.126-0.97-0.228-1.245c-0.089-0.46-0.188-0.914-0.309-1.361c0.631-0.253,1.269-0.492,1.938-0.646
      c1.064-0.244,0.558-1.756-0.396-1.757c-0.354,0-1.062,0.067-1.81,0.106c0.098-0.551-0.229-1.146-0.919-1.021
      c-0.896,0.169-1.807,0.261-2.603,0.721c-0.559,0.322-0.423,1.06-0.062,1.438c0.174,0.182,0.406,0.312,0.688,0.413
      c-0.246,0.062-0.496,0.116-0.754,0.146c-0.49,0.068-0.87,0.479-0.825,0.983c0.116,1.341,0.281,2.755,0.587,4.131
      c-1.054,1.371-1.975,2.877-2.84,4.058c-2.917,3.972-5.808,8.312-9.548,11.604c0.032-5.074,0.217-10.118,0.891-15.161
      c0.006-0.042-0.009-0.075-0.007-0.114c2.63-0.875,4.979-3.059,6.443-5.159c2.938-4.224,3.987-9.997,2.622-14.938
      c-0.771-2.792-3.229-4.882-6.023-6.024c-1.605-1.441-3.523-2.433-5.796-2.135c-3.409,0.445-5.449,4.168-6.564,7.014
      c-2.47,6.305-3.089,17.858,4.415,21.113c1.021,0.441,2.045,0.579,3.061,0.505c-0.539,4.271-0.746,8.532-0.815,12.817
      c-4.331,5.129-9.038,9.846-14.178,14.165c-0.894,0.747,0.146,2.23,1.05,1.479c4.708-3.955,9.06-8.249,13.102-12.865
      c-0.001,0.393-0.004,0.771-0.004,1.155c-0.472,3.633-0.689,7.271-0.814,10.926c-0.058,1.584-0.77,5.926-0.396,8.329
      c-0.199,0.062-0.389,0.188-0.521,0.404c-3.821,6.068-6.248,14.17-6.438,21.347c-0.028,1.17,1.779,0.999,1.812-0.15
      c0.179-6.686,2.468-14.112,5.936-19.854c4.527,5.92,6.987,13.069,10.576,19.554c0.565,1.021,2.044-0.033,1.479-1.053
      c-3.021-5.461-5.278-11.299-8.516-16.647c-2.211-3.658-2.229-6.278-2.165-10.509c0.063-3.944,0.312-7.877,0.812-11.788
      c3.394-2.66,6.035-6.183,8.606-9.604c1.523-2.025,3-4.089,4.526-6.111c0.72,1.847,1.834,3.471,3.633,4.531
      c2.741,1.618,5.751-0.606,7.415-2.662c3.275-4.044,3.695-9.932,3.701-14.896C724.52,710.352,722.969,702.815,722.09,695.3z
       M690.459,724.79c-3.271-4.551-2.246-11.984-0.341-16.842c0.172-0.438,0.372-0.878,0.591-1.313
      c-0.127,1.484,0.126,3.104,0.575,4.492c0.354,1.115,2.062,0.48,1.702-0.633c-1.733-5.377,1.423-7.949,6.479-6.092
      c0.104,0.036,0.188,0.078,0.281,0.117c1.043,1.054,1.918,2.291,2.538,3.186c0.427,0.616,1.185,0.403,1.5-0.079
      c1.342,1.951,1.782,4.447,1.555,7.182c-0.127,1.539-0.729,3.471-1.67,5.354c-0.208-0.271-0.551-0.404-0.961-0.215
      c-3.412,1.584-6.782,1.706-10.413,0.866c-1.144-0.271-1.463,1.521-0.328,1.777c3.692,0.854,7.25,0.766,10.729-0.684
      C699.664,726.837,694.594,730.546,690.459,724.79z M711.177,729.048c0.095-0.122,0.195-0.24,0.294-0.354
      c0.146,0.816,0.354,1.633,0.693,2.402c0.087,0.198,0.224,0.321,0.374,0.41c0.074,0.222,0.144,0.442,0.207,0.663
      c0.04,0.229,0.081,0.452,0.125,0.679C712.134,731.839,711.58,730.447,711.177,729.048z" />
          <path display="inline" fill="#231F20" d="M695.442,713.747c-0.473-0.447-1.074-0.818-1.729-0.807
      c-0.195-0.205-0.477-0.334-0.749-0.312c-0.526,0.046-0.826,0.488-0.826,0.981v0.104c-0.334,0.36-0.597,0.809-0.816,1.237
      c-0.445,0.854,0.587,1.516,1.267,1.146c0.168,0.103,0.356,0.157,0.542,0.146c0.525-0.045,0.825-0.488,0.825-0.982v-0.398
      c0.115,0.078,0.225,0.174,0.329,0.271C695.12,715.952,696.282,714.555,695.442,713.747z" />
          <path display="inline" fill="#231F20" d="M700.896,709.96c-0.773,0.39-1.524,0.84-2.354,1.126
      c-0.104,0.04-0.167,0.055-0.229,0.065c-0.016-0.037-0.021-0.075-0.021-0.104c0.021-0.331,0.103-0.659,0.172-0.979
      c0.271-1.146-1.519-1.462-1.787-0.328c-0.271,1.165-0.384,3.123,1.282,3.228c1.277,0.082,2.468-0.754,3.562-1.305
      C702.562,711.141,701.944,709.433,700.896,709.96z" />
          <path display="inline" fill="#231F20" d="M757.441,692.339c-3.086-9.944-5.816-21.001-13.249-28.739
      c-8.784-9.145-22.25-11.08-34.278-8.566c-10.776,2.235-19.733,9.358-24.721,19.098c-2.698,5.261-3.785,11.189-3.979,17.053
      c-0.059,1.87-0.351,3.396,1.854,3.906c0.176,0.041,0.314,0.061,0.463,0.071c-0.417,0.471-0.812,0.959-1.171,1.457
      c-0.688,0.938,0.7,2.109,1.391,1.167c3.079-4.266,9.122-8.699,13.089-2.782c0.403,0.602,1.24,0.433,1.562-0.136
      c3.405-6.036,9.269-5.354,11.938,0.836c0.253,0.585,1.12,0.851,1.546,0.267c3.583-4.896,9.526-7.386,13.997-1.875
      c0.352,0.425,1.074,0.562,1.438,0.062c3.102-4.309,7.6-6.374,10.255-0.271c0.225,0.521,0.89,0.804,1.376,0.425
      c4.961-3.846,12.045-5.354,15.979,0.691c0.645,0.979,2.118-0.068,1.479-1.05c-0.138-0.212-0.29-0.396-0.435-0.594
      C756.568,693.87,757.734,693.282,757.441,692.339z M738.77,692.202c-3.098-5.776-8.271-4.854-12.188-0.043
      c-4.706-4.826-10.987-3.328-15.2,1.54c-3.405-6.04-9.822-6.541-13.854-0.812c-3.479-4.006-8.229-2.75-11.931,0.273
      c0.521-0.955-0.799-2.059-1.417-1.098c-0.312,0.49-0.439,0.686-0.63,1.23c-0.175-0.354-0.349-0.71-0.521-1.062
      c-0.021-1.136,0.021-2.271,0.103-3.397c0.146-2.326,0.57-4.625,1.107-6.89c1.226-5.179,3.805-9.94,7.176-14.021
      c6.479-7.84,16.376-11.589,26.316-12.04c-0.083,0.146-0.146,0.29-0.147,0.451h-2.829c-0.597,0-0.842,0.448-0.792,0.897
      c-0.039,0.003-0.077,0.009-0.116,0.011c-0.776,0.033-1.114,1.123-0.504,1.6c0.25,0.194,0.506,0.325,0.774,0.425
      c-9.189,3.271-14.469,11.98-15.609,21.414c-0.14,1.156,1.646,1.486,1.787,0.325c1.083-8.963,5.979-17.224,14.856-20.188
      c0.646-0.221,0.688-0.885,0.396-1.33c0.354-0.008,0.752-0.039,1.146-0.033c0.802,6.229,0.944,12.519,1.771,18.747
      c0.153,1.156,1.963,1.003,1.812-0.157c-0.796-6.012-0.976-12.076-1.7-18.093c0.415-0.062,0.736-0.44,0.764-0.861
      c9.15,2.997,15.216,10.242,15.469,20.021c0.03,1.172,1.845,1.007,1.812-0.158c-0.256-9.926-6.155-17.336-14.979-20.92
      c0.022-0.021,0.054-0.029,0.069-0.047c0.646-0.515,0.354-1.836-0.604-1.646c-0.163,0.032-0.328,0.066-0.49,0.104
      c-0.122-0.062-0.259-0.104-0.417-0.104h-0.229c-0.177-0.185-0.423-0.333-0.646-0.489c10.773-0.094,20.463,3.986,26.728,12.991
      c4.978,7.146,7.147,15.925,9.691,24.123c0.021,0.07,0.062,0.115,0.094,0.171C751.507,687.544,744.222,688.435,738.77,692.202z" />
        </g>
      </g>
    </g>
  <?php } ?>
  <?php if (!isset($_GET['ver']) || $_GET['ver'] !== 'kiosk') { ?>
    <image overflow="visible" enable-background="new" width="326" height="77" id="top_menu_side" xlink:href="img/menu_top_side.png" transform="matrix(0.9999 0 0 0.9999 1258.041 0)">
    </image>
  <?php } ?>

  <g id="buttons" style="cursor:pointer">
    <?php if ($electricity_bool) {
      if (isset($_GET['ver']) && $_GET['ver'] === 'kiosk') {
        $x[0] = 1325;
      }
    ?>
      <image style="opacity: 0" overflow="visible" enable-background="new" width="223" height="111" id="electricity_hover" xlink:href="img/electricity_button_hover_1.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
      </image>
      <image style="opacity: 0" overflow="visible" enable-background="new" width="223" height="111" id="electricity_highlight" xlink:href="img/electricity_button_highlighted_1.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
      </image>
      <g id="electricity">
        <image id="electricity_btn" overflow="visible" enable-background="new" width="223" height="111" xlink:href="img/electricity_button_1.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
        </image>
        <text id="electricity_label" transform="matrix(1 0 0 1 <?php echo $x[0] + $x[1]; $x[0] += 223; ?> 34.4961)" font-family="'Futura-Medium'" font-size="22"> Electricity </text>
      </g>

    <?php array_push($resources, 'electricity');
    }
    if ($water_bool) {
      if (isset($_GET['ver']) && $_GET['ver'] === 'kiosk') {
        $x[0] = 1325;
      }
    ?>
      <image style="opacity: 0" overflow="visible" enable-background="new" width="223" height="111" id="water_highlight" xlink:href="img/water_pressed_2.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
      </image>
      <image style="opacity: 0" overflow="visible" enable-background="new" width="223" height="111" id="water_hover" xlink:href="img/water_button_hover_2.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
      </image>
      <g id="water">
        <image id="water_btn" overflow="visible" enable-background="new" width="223" height="111" xlink:href="img/water_button_2.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
        </image>
        <text id="water_label" transform="matrix(1 0 0 1 <?php echo $x[0] + $x[2]; $x[0] += 223; ?> 34.4961)" font-family="'Futura-Medium'" font-size="22">Water</text>
      </g>

    <?php array_push($resources, 'water');
    }
    if ($stream_bool) {
      if (isset($_GET['ver']) && $_GET['ver'] === 'kiosk') {
        $x[0] = 1325;
      }
    ?>
      <image style="opacity: 0" overflow="visible" enable-background="new" width="223" height="111" id="stream_highlight"
        xlink:href="img/stream_button_highlighted.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
      </image>
      <image style="opacity: 0" overflow="visible" enable-background="new" width="223" height="111" id="stream_hover"
        xlink:href="img/stream_button_hover_1.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
      </image>

      <g id="stream">
        <image id="stream_btn" overflow="visible" enable-background="new" width="223" height="111"
          xlink:href="img/stream_button_1.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
        </image>
        <!-- Stream -->
        <text id="stream_label" transform="matrix(1 0 0 1 <?php echo $x[0] + $x[3];
                                                          $x[0] += 223; ?> 34.4961)" font-family="'Futura-Medium'" font-size="22">Lake Erie</text>
      </g>

    <?php array_push($resources, 'stream');
    }
    if ($weather_bool) {
      if (isset($_GET['ver']) && $_GET['ver'] === 'kiosk') {
        $x[0] = 1325;
      }
    ?>

      <image style="opacity: 0" overflow="visible" enable-background="new" width="223" height="111" id="weather_highlight" xlink:href="img/weather_highlighted_1.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
      </image>
      <image style="opacity: 0" overflow="visible" enable-background="new" width="223" height="111" id="weather_hover" xlink:href="img/weather_button_hover_2.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
      </image>

      <g id="weather">
        <image id="weather_btn" overflow="visible" enable-background="new" width="223" height="111" xlink:href="img/weather_button_2.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
        </image>
        <text id="weather_label" transform="matrix(1 0 0 1 <?php echo $x[0] + $x[4];
                                                            $x[0] += 223; ?> 35.4961)" font-family="'Futura-Medium'" font-size="22">Air Quality</text> <!-- Weather -->
      </g>

    <?php array_push($resources, 'weather');
    }
    if ($gas_bool) {
      if (isset($_GET['ver']) && $_GET['ver'] === 'kiosk') {
        $x[0] = 1325;
      }
    ?>

      <image style="opacity: 0" overflow="visible" enable-background="new" width="223" height="111" id="gas_hover" xlink:href="img/gas_hover_1.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
      </image>
      <image style="opacity: 0" overflow="visible" enable-background="new" width="223" height="111" id="gas_highlight" xlink:href="img/gas_highlighted_leggo.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
      </image>

      <g id="gas">
        <image id="gas_btn" overflow="visible" enable-background="new" width="223" height="111" xlink:href="img/gas_button_1.png" transform="matrix(1 0 0 1 <?php echo $x[0]; ?> -20)">
        </image>
        <text id="gas_label" transform="matrix(1 0 0 1 <?php echo $x[0] + $x[5];
                                                        $x[0] += 223; ?> 37)" font-family="'Futura-Medium'" font-size="22"> Gas </text>
      </g>

    <?php array_push($resources, 'gas');
    } ?>
  </g>

  <!-- g#clickables was temporarily here -->

  <g id="rain">
    <?php
    if ($its_raining) {
      for ($i = 0; $i < 100; $i++) {
        $rand = mt_rand(0, 1200);
        echo "<rect x='{$rand}' y='-90' fill='url(#rain-color)' id='raindrop-{$i}'
          width='2' height='90'
          rx='20' ry='20'/>";
      }
    }
    ?>
  </g>

  <g id="pipes">
    <?php if ($user_id !== 3) { // but not for toledo 
    ?>
      <image overflow="visible" enable-background="new" width="164" height="156" xlink:href="img/smokestack/smokestack1.png" transform="matrix(1 0 0 1 107 161)">
      </image>

      <image overflow="visible" enable-background="new" width="164" height="156" xlink:href="img/smokestack/smokestack1.png" transform="matrix(1 0 0 1 140 161)">
      </image>

      <image overflow="visible" enable-background="new" width="164" height="156" xlink:href="img/smokestack/smokestack1.png" transform="matrix(1 0 0 1 174 161)">
      </image>
    <?php } ?>
  </g>

  <g id="messages">
    <?php if (isset($_GET['ver']) && $_GET['ver'] === 'kiosk') { ?>
      <foreignObject x="205" y="10" width="800" height="15%">
        <p style="font: 25px Futura, sans-serif;color: #777" id="message" xmlns="http://www.w3.org/1999/xhtml"></p>
      </foreignObject>
    <?php } else { ?>
      <foreignObject x="205" y="55" width="800" height="15%">
        <p style="font: 20px Futura, sans-serif;color: #777" id="message" xmlns="http://www.w3.org/1999/xhtml"></p>
      </foreignObject>
    <?php } ?>
  </g>

  <?php if (!isset($_GET['ver']) || $_GET['ver'] !== 'kiosk') { ?>
    <g id="play" style="cursor:pointer">
      <linearGradient id="SVGID_2_" gradientUnits="userSpaceOnUse" x1="3069.9248" y1="2399.4941" x2="3099.5271" y2="2399.4941" gradientTransform="matrix(3.378378e-05 1 -1 -8.333332e-04 2649.3901 -2907.7246)">
        <stop offset="0" style="stop-color:#FFCB00" />
        <stop offset="1" style="stop-color:#FF6700" />
      </linearGradient>
      <path fill="url(#SVGID_2_)" d="M204.4,185.6c-2.933-2.933-4.4-6.467-4.4-10.6s1.467-7.667,4.4-10.6c2.934-2.933,6.467-4.4,10.6-4.4
  h70c4.133,0,7.667,1.467,10.6,4.4c2.933,2.934,4.4,6.467,4.4,10.6s-1.467,7.667-4.4,10.6c-2.934,2.933-6.467,4.4-10.6,4.4h-70
  C210.867,190,207.333,188.533,204.4,185.6z" />

      <linearGradient id="darkplay_1_" gradientUnits="userSpaceOnUse" x1="544" y1="-415" x2="544" y2="-386.1805" gradientTransform="matrix(1 0 0 1 -294 576)">
        <stop offset="0" style="stop-color:#D4A900" />
        <stop offset="1" style="stop-color:#AB4500" />
      </linearGradient>
      <path id="darkplay" fill="url(#darkplay_1_)" d="M204.4,185.6c-2.933-2.933-4.4-6.467-4.4-10.6s1.467-7.667,4.4-10.6
  c2.934-2.933,6.467-4.4,10.6-4.4h70c4.133,0,7.667,1.467,10.6,4.4c2.933,2.934,4.4,6.467,4.4,10.6s-1.467,7.667-4.4,10.6
  c-2.934,2.933-6.467,4.4-10.6,4.4h-70C210.867,190,207.333,188.533,204.4,185.6z" />
      <path fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="3" d="
  M204.4,185.6c2.934,2.933,6.467,4.4,10.6,4.4h70c4.133,0,7.667-1.467,10.6-4.4s4.4-6.467,4.4-10.6s-1.467-7.667-4.4-10.6
  c-2.934-2.933-6.467-4.4-10.6-4.4h-70c-4.133,0-7.667,1.467-10.6,4.4c-2.933,2.934-4.4,6.467-4.4,10.6S201.467,182.667,204.4,185.6
  z" />
      <g id="playtext">
        <g opacity="0.2">
          <path d="M239.55,164.754c0.925-0.2,2.174-0.325,3.424-0.325c1.949,0,3.499,0.275,4.574,1.3c1,0.875,1.45,2.274,1.45,3.699
      c0,1.824-0.55,3.124-1.425,4.049c-1.05,1.125-2.724,1.625-4.099,1.625c-0.225,0-0.425,0-0.65-0.025v6.273h-3.274V164.754z
       M242.824,172.427c0.175,0.025,0.35,0.025,0.55,0.025c1.649,0,2.374-1.175,2.374-2.749c0-1.475-0.6-2.625-2.125-2.625
      c-0.3,0-0.6,0.05-0.8,0.125L242.824,172.427L242.824,172.427z" />
          <path d="M250.975,164.504h3.274v14.071h4.299v2.774h-7.573V164.504z" />
          <path d="M263.349,177.501l-0.75,3.849h-3.174l3.649-16.846h3.924l3.274,16.846h-3.174l-0.725-3.849H263.349z M266.073,174.952
      l-0.55-3.524c-0.175-1.025-0.4-2.725-0.55-3.824h-0.075c-0.175,1.125-0.425,2.849-0.6,3.824l-0.625,3.524H266.073z" />
          <path d="M273.324,181.35v-6.448l-3.699-10.397h3.499l1.125,4.024c0.325,1.075,0.65,2.324,0.9,3.574h0.05
      c0.2-1.225,0.475-2.45,0.775-3.649l1-3.949h3.399l-3.774,10.272v6.573H273.324z" />
        </g>
        <g>
          <path fill="#FFFFFF" d="M240.65,166.854c0.925-0.2,2.174-0.325,3.424-0.325c1.949,0,3.499,0.275,4.574,1.3
      c1,0.875,1.45,2.274,1.45,3.699c0,1.824-0.55,3.124-1.425,4.049c-1.05,1.125-2.724,1.625-4.099,1.625
      c-0.225,0-0.425,0-0.65-0.025v6.273h-3.274V166.854z M243.924,174.527c0.175,0.025,0.35,0.025,0.55,0.025
      c1.649,0,2.374-1.175,2.374-2.749c0-1.475-0.6-2.625-2.125-2.625c-0.3,0-0.6,0.05-0.8,0.125L243.924,174.527L243.924,174.527z" />
          <path fill="#FFFFFF" d="M252.075,166.604h3.274v14.071h4.299v2.774h-7.573V166.604L252.075,166.604z" />
          <path fill="#FFFFFF" d="M264.448,179.601l-0.75,3.849h-3.174l3.649-16.846h3.924l3.274,16.846h-3.174l-0.725-3.849H264.448
      L264.448,179.601z M267.172,177.052l-0.55-3.524c-0.175-1.025-0.4-2.725-0.55-3.824h-0.075c-0.175,1.125-0.425,2.849-0.6,3.824
      l-0.625,3.524H267.172z" />
          <path fill="#FFFFFF" d="M274.423,183.45v-6.448l-3.699-10.397h3.499l1.125,4.024c0.325,1.075,0.65,2.324,0.9,3.574h0.05
      c0.2-1.225,0.475-2.45,0.775-3.649l1-3.949h3.399l-3.774,10.272v6.573H274.423z" />
        </g>
        <g opacity="0.2">
          <polygon points="228.5,172.85 214.6,180.85 214.6,164.85       " />
        </g>
        <g>
          <polygon fill="#FFFFFF" points="215.5,167.5 229.4,175.5 215.5,183.5       " />
        </g>
      </g>
      <g id="pausetext" display="none">
        <g display="inline" opacity="0.2">
          <path d="M234.268,165.157c0.925-0.2,2.174-0.325,3.424-0.325c1.949,0,3.499,0.275,4.574,1.3c1,0.875,1.45,2.274,1.45,3.699
      c0,1.824-0.55,3.124-1.425,4.049c-1.05,1.125-2.724,1.625-4.099,1.625c-0.225,0-0.425,0-0.65-0.025v6.273h-3.274V165.157z
       M237.542,172.83c0.175,0.025,0.35,0.025,0.55,0.025c1.649,0,2.374-1.175,2.374-2.749c0-1.475-0.6-2.625-2.125-2.625
      c-0.3,0-0.6,0.05-0.8,0.125L237.542,172.83L237.542,172.83z" />
          <path d="M247.317,177.903l-0.75,3.849h-3.174l3.649-16.846h3.924l3.274,16.846h-3.174l-0.725-3.849H247.317L247.317,177.903z
       M250.042,175.354l-0.55-3.524c-0.175-1.025-0.4-2.725-0.55-3.824h-0.075c-0.175,1.125-0.425,2.849-0.6,3.824l-0.625,3.524
      H250.042z" />
          <path d="M258.867,164.907v10.947c0,2.474,0.725,3.224,1.6,3.224c0.95,0,1.625-0.675,1.625-3.224v-10.947h3.274v10.372
      c0,4.324-1.649,6.674-4.874,6.674c-3.524,0-4.899-2.425-4.899-6.648v-10.397L258.867,164.907L258.867,164.907z" />
          <path d="M267.818,178.403c0.675,0.375,1.85,0.65,2.774,0.65c1.524,0,2.299-0.8,2.299-1.899c0-1.225-0.75-1.825-2.174-2.75
      c-2.299-1.4-3.174-3.174-3.174-4.699c0-2.7,1.8-4.949,5.299-4.949c1.125,0,2.174,0.3,2.649,0.6l-0.525,2.824
      c-0.475-0.3-1.2-0.575-2.125-0.575c-1.4,0-2.075,0.85-2.075,1.75c0,1,0.5,1.524,2.299,2.625c2.25,1.35,3.049,3.049,3.049,4.824
      c0,3.074-2.274,5.099-5.548,5.099c-1.35,0-2.649-0.35-3.225-0.675L267.818,178.403z" />
          <path d="M285.141,174.354h-3.824v4.549h4.324v2.849h-7.598v-16.846h7.323v2.849h-4.049v3.874h3.824V174.354z" />
        </g>
        <g display="inline">
          <path fill="#FFFFFF" d="M235.368,167.256c0.925-0.2,2.174-0.325,3.424-0.325c1.949,0,3.499,0.275,4.574,1.3
      c1,0.875,1.45,2.274,1.45,3.699c0,1.824-0.55,3.124-1.425,4.049c-1.05,1.125-2.724,1.625-4.099,1.625
      c-0.225,0-0.425,0-0.65-0.025v6.273h-3.274V167.256z M238.642,174.929c0.175,0.025,0.35,0.025,0.55,0.025
      c1.649,0,2.374-1.175,2.374-2.749c0-1.475-0.6-2.625-2.125-2.625c-0.3,0-0.6,0.05-0.8,0.125L238.642,174.929L238.642,174.929z" />
          <path fill="#FFFFFF" d="M248.417,180.003l-0.75,3.849h-3.174l3.649-16.846h3.924l3.274,16.846h-3.174l-0.725-3.849H248.417
      L248.417,180.003z M251.141,177.454l-0.55-3.524c-0.175-1.025-0.4-2.725-0.55-3.824h-0.075c-0.175,1.125-0.425,2.849-0.6,3.824
      l-0.625,3.524H251.141z" />
          <path fill="#FFFFFF" d="M259.967,167.006v10.947c0,2.474,0.725,3.224,1.6,3.224c0.95,0,1.625-0.675,1.625-3.224v-10.947h3.274
      v10.372c0,4.324-1.649,6.674-4.874,6.674c-3.524,0-4.899-2.425-4.899-6.648v-10.397L259.967,167.006L259.967,167.006z" />
          <path fill="#FFFFFF" d="M268.917,180.503c0.675,0.375,1.85,0.65,2.774,0.65c1.524,0,2.299-0.8,2.299-1.899
      c0-1.225-0.75-1.825-2.174-2.75c-2.299-1.4-3.174-3.174-3.174-4.699c0-2.7,1.8-4.949,5.299-4.949c1.125,0,2.174,0.3,2.649,0.6
      l-0.525,2.824c-0.475-0.3-1.2-0.575-2.125-0.575c-1.4,0-2.075,0.85-2.075,1.75c0,1,0.5,1.524,2.299,2.625
      c2.25,1.35,3.049,3.049,3.049,4.824c0,3.074-2.274,5.099-5.548,5.099c-1.35,0-2.649-0.35-3.225-0.675L268.917,180.503z" />
          <path fill="#FFFFFF" d="M286.241,176.454h-3.824v4.549h4.324v2.849h-7.598v-16.846h7.323v2.849h-4.049v3.874h3.824V176.454z" />
        </g>
        <g display="inline" opacity="0.2">
          <path d="M220.306,166.302h3.95v15.55h-3.95V166.302z M213.256,166.302h3.95v15.55h-3.95V166.302z" />
        </g>
        <g display="inline">
          <path fill="#FFFFFF" d="M225.355,184.002h-3.95v-15.55h3.95V184.002z M218.306,184.002h-3.95v-15.55h3.95V184.002z" />
        </g>
      </g>
    </g>
  <?php } ?>
  <g id="landscape_messages"><!-- See #clickables -->
    <?php
    $components = array();
    $cwdLandscapeComponents = $db->query("SELECT component, title, link, `text` FROM cwd_landscape_components WHERE hidden = 0 AND user_id = {$user_id} AND component != 'river_click'");

    foreach ($cwdLandscapeComponents as $row) {
      array_push($components, $row['component']);
      $y = $text_pos[$row['component']][1] + 50;
    ?>
      <g id='<?php echo $row['component']; ?>_message' display="none">
        <rect width="320" height="<?php echo (strlen($row['text']) * 0.7) + 80 ?>"
          x="<?php echo $text_pos[$row['component']][0]; ?>"
          y="<?php echo $text_pos[$row['component']][1]; ?>"
          style="fill:#fff;stroke-width:1;stroke:#333;" />
        <text x="<?php echo $text_pos[$row['component']][0] + 10; ?>" y="<?php echo $text_pos[$row['component']][1] + 25; ?>" fill="#333" font-size="22" font-family="Futura, sans-serif"><?php echo $row['title']; ?></text>
        <text x="<?php echo $text_pos[$row['component']][0] + 10; ?>" y="<?php echo $text_pos[$row['component']][1] + 60; ?>" fill="#333" font-family="Futura, sans-serif">
          <tspan y="<?php echo $y; ?>">
            <?php
            // Split into words
            $text = explode(' ', $row['text']);
            $num_chars = 0;
            for ($i = 0; $i < count($text); $i++) {
              echo $text[$i] . ' ';
              $num_chars += strlen($text[$i]);
              if ($num_chars > 25) { // Assumes every charachter is the same size but this usually gets close
                $y += 22;
                echo "</tspan><tspan y='{$y}' x='" . ($text_pos[$row['component']][0] + 10) . "'>";
                $num_chars = 0;
              }
            }
            ?>
          </tspan>
          <tspan style="fill:#3498db;text-decoration:underline;font-size:18px" x="<?php echo $text_pos[$row['component']][0] + 10; ?>" y="<?php echo $y + 30; ?>"><?php echo "<a style='fill:#3498db;' x='" . ($text_pos[$row['component']][0] + 10) . "' xlink:href='{$row['link']}'>Read more</a>" ?></tspan>
        </text>
      </g>
      <image display="none"
        x="<?php echo $text_pos[$row['component']][0] + 295; ?>"
        y="<?php echo $text_pos[$row['component']][1] + 5; ?>"
        width="20" height="20"
        id="<?php echo $row['component']; ?>_close"
        xlink:href="img/close.svg" style="cursor:pointer"></image>
    <?php } ?>
  </g>

  <!-- <circle r="35" cx="760" cy="320" fill="red" id="test" /> -->
  <!-- <rect id="test" width="300" height="100" x="0" y="893" style="fill:rgb(0,0,255);" /> -->
  <image xlink:href="img/bird/1.svg" height="146px" x="1500" y="-40" width="136px" id="bird1" />
  <image xlink:href="img/bird/2.svg" height="146px" x="1500" y="-40" width="136px" id="bird2" style="opacity: 0" />
  <image xlink:href="img/bird/3.svg" height="146px" x="1500" y="-40" width="136px" id="bird3" style="opacity: 0" />
  <image xlink:href="img/bird/4.svg" height="146px" x="1500" y="-40" width="136px" id="bird4" style="opacity: 0" />


  <script type="text/javascript" xlink:href="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js" />
  <script type="text/javascript" xlink:href="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.19.1/TweenMax.min.js" />
  <?php //if ($admin) {
  // echo '<script type="text/javascript" xlink:href="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"/>';
  //} 
  ?>

  <script type="text/javascript">
    // <![CDATA[
    //console.log('<?php //echo json_encode($log) 
                    ?>'); // Delete this when in production
    <?php if ($admin) { // drag icons and save their position on double-click; from http://www.petercollingridge.co.uk/tutorials/svg/interactive/dragging/ 
    ?>

      function makeDraggable(evt) {
        var svg = evt.target;

        svg.addEventListener('mousedown', startDrag);
        svg.addEventListener('mousemove', drag);
        svg.addEventListener('mouseup', endDrag);
        svg.addEventListener('mouseleave', endDrag);
        svg.addEventListener('touchstart', startDrag);
        svg.addEventListener('touchmove', drag);
        svg.addEventListener('touchend', endDrag);
        svg.addEventListener('touchleave', endDrag);
        svg.addEventListener('touchcancel', endDrag);

        function getMousePosition(evt) {
          var CTM = svg.getScreenCTM();
          if (evt.touches) {
            evt = evt.touches[0];
          }
          return {
            x: (evt.clientX - CTM.e) / CTM.a,
            y: (evt.clientY - CTM.f) / CTM.d
          };
        }

        var selectedElement, offset, transform;

        function initialiseDragging(evt) {
          offset = getMousePosition(evt);

          // Make sure the first transform on the element is a translate transform
          var transforms = selectedElement.transform.baseVal;

          if (transforms.length === 0 || transforms.getItem(0).type !== SVGTransform.SVG_TRANSFORM_TRANSLATE) {
            // Create an transform that translates by (0, 0)
            var translate = svg.createSVGTransform();
            translate.setTranslate(0, 0);
            selectedElement.transform.baseVal.insertItemBefore(translate, 0);
          }

          // Get initial translation
          transform = transforms.getItem(0);
          offset.x -= transform.matrix.e;
          offset.y -= transform.matrix.f;
        }

        function startDrag(evt) {
          evt.target.setAttribute('style', 'outline:3px dashed red');
          if (evt.target.classList.contains('draggable')) {
            selectedElement = evt.target;
            initialiseDragging(evt);
          } else if (evt.target.parentNode.classList.contains('draggable-group')) {
            selectedElement = evt.target.parentNode;
            initialiseDragging(evt);
          }
        }

        function drag(evt) {
          if (selectedElement) {
            evt.preventDefault();
            var coord = getMousePosition(evt);
            transform.setTranslate(coord.x - offset.x, coord.y - offset.y);
          }
        }

        function endDrag(evt) {
          evt.target.setAttribute('style', 'outline:3px dashed black');
          selectedElement = false;
        }
      }
      <?php
      if (count($components) > 0) { ?>
        $('#<?php echo implode(', #', $components); ?>').on('dblclick', function() {
          var pos = this.getBoundingClientRect();
          $.post('includes/update-landscape-comp.php', {
            x: pos.x,
            y: pos.y,
            comp: $(this).attr('id'),
            id: <?php echo $user_id ?>
          });
          $(this).attr('style', 'outline:none');
        });
    <?php }
    } ?>
    // FUNCTIONS //

    // Timer function based on setInterval()
    // See http://stackoverflow.com/a/8126515/2624391
    function Timer(fn, t) {
      var timerObj = setInterval(fn, t);
      this.stop = function() {
        if (timerObj) {
          clearInterval(timerObj);
          timerObj = null;
        }
        return this;
      }
      // start timer using current settings (if it's not already running)
      this.start = function() {
        if (!timerObj) {
          this.stop();
          timerObj = setInterval(fn, t);
        }
        return this;
      }
      // start with new interval, stop current interval
      this.reset = function(newT) {
        t = newT;
        return this.stop().start();
      }
    }

    var landing_messages = <?php echo json_encode($landing_messages); ?>;
    <?php if ($electricity_bool) {
      echo 'var electricity_messages = ' . json_encode($electricity_messages) . ';';
    } ?>
    <?php if ($gas_bool) {
      echo 'var gas_messages = ' . json_encode($gas_messages) . ';';
    } ?>
    <?php if ($stream_bool) {
      echo 'var stream_messages = ' . json_encode($stream_messages) . ';';
    } ?>
    <?php if ($water_bool) {
      echo 'var water_messages = ' . json_encode($water_messages) . ';';
    } ?>
    <?php if ($weather_bool) {
      echo 'var weather_messages = ' . json_encode($weather_messages) . ';';
    } ?>

    // Set landing gauges
    $('#gauge1').attr('src', '<?php echo $gauges[$cwd_dashboard_default_state]['gauge1']; ?>');
    $('#gauge2').attr('src', '<?php echo $gauges[$cwd_dashboard_default_state]['gauge2']; ?>');
    $('#gauge3').attr('src', '<?php echo $gauges[$cwd_dashboard_default_state]['gauge3']; ?>');
    $('#gauge4').attr('src', '<?php echo $gauges[$cwd_dashboard_default_state]['gauge4']; ?>');

    var i = 0;
    var current_state = '<?php echo $cwd_dashboard_default_state; ?>';
    // trigger current state immediately
    current_state && nextState(current_state);

    $('#message').text(landing_messages[i++]['message']);
    var msgTimer = new Timer(function() {
      if (i === landing_messages.length || current_state !== 'landing') {
        msgTimer.stop();
      } else {
        $('#message').text(landing_messages[i++]['message']);
      }
    }, <?php echo $timing['message_section'] * 1000; ?>);
    var time = 0;
    setInterval(function() {
      time++;
    }, 1000);

    // Functions for each state //
    // 
    <?php if (in_array('electricity', $resources)) { ?>

      function electricity() {
        // ga('send', 'event', 'Electricity button', 'Click', '', time);
        time = 0;
        console.log('called electricity()');
        // Set gauge URLs
        $('#gauge1').attr('src', '<?php echo $gauges['electricity']['gauge1']; ?>');
        $('#gauge2').attr('src', '<?php echo $gauges['electricity']['gauge2']; ?>');
        $('#gauge3').attr('src', '<?php echo $gauges['electricity']['gauge3']; ?>');
        $('#gauge4').attr('src', '<?php echo $gauges['electricity']['gauge4']; ?>');
        // Set powerline highlight
        $('#powerlines_lit, #powerlines_lit_back').attr('display', 'visible');
        // Set button to active state
        $('#electricity_highlight').css('opacity', '1');
        <?php echo (isset($_GET['ver']) && $_GET['ver'] === 'kiosk') ? "\$('#electricity_highlight').attr('visibility', '');" : ''; ?>
        $('#electricity_hover, #electricity_btn').css('opacity', '0');
        // House
        $('#stick_electricity').attr('display', 'visible');
        $('#house_inside').attr('display', 'visible');
        // Electricity nodes
        $('#sparkpaths, #sparkpaths_back').attr('display', 'visible');
        // Select charachter
        $('#squirrel').css('opacity', '1');
        var i1 = 0;
        $('#message').text(electricity_messages[i1++]['message']);
        var msgTimer1 = new Timer(function() {
          if (i1 === electricity_messages.length || current_state !== 'electricity') {
            msgTimer1.stop();
          } else {
            $('#message').text(electricity_messages[i1++]['message']);
          }
        }, <?php echo $timing['message_section'] * 1000; ?>);
        i = 0;
      }

      function undo_electricity() {
        console.log('called undo_electricity()');
        // Remove power line highlight
        $('#powerlines_lit, #powerlines_lit_back').attr('display', 'none');
        // Reset button state
        $('#electricity_highlight').css('opacity', '0');
        $('#electricity_btn').css('opacity', '1');
        // House
        $('#stick_electricity').attr('display', 'none');
        // Electricity nodes
        $('#sparkpaths, #sparkpaths_back').attr('display', 'none');
        $('#house_inside').attr('display', 'none');
        $('#squirrel').css('opacity', '0');
        i1 = 0;
      }
    <?php }
    if (in_array('water', $resources)) { ?>

      function water() {
        console.log('called water()');
        // Set gauge URLs
        $('#gauge1').attr('src', '<?php echo $gauges['water']['gauge1']; ?>');
        $('#gauge2').attr('src', '<?php echo $gauges['water']['gauge2']; ?>');
        $('#gauge3').attr('src', '<?php echo $gauges['water']['gauge3']; ?>');
        $('#gauge4').attr('src', '<?php echo $gauges['water']['gauge4']; ?>');
        // Set button to active state
        $('#water_highlight').css('opacity', '1');
        $('#water_btn, #water_hover').css('opacity', '0');
        <?php echo (isset($_GET['ver']) && $_GET['ver'] === 'kiosk') ? "\$('#water_highlight').attr('visibility', '');" : ''; ?>
        // House
        $('#stick_water').attr('display', 'visible');
        $('#house_inside').attr('display', 'visible');
        // Print messages
        // Animation stuff
        $('#waterlines_clip').css('opacity', '1');
        $('#fish').css('opacity', '1');
        var i2 = 0;
        $('#message').text(water_messages[i2++]['message']);
        var msgTimer2 = new Timer(function() {
          if (i2 === water_messages.length || current_state !== 'water') {
            msgTimer2.stop();
          } else {
            $('#message').text(water_messages[i2++]['message']);
          }
        }, <?php echo $timing['message_section'] * 1000; ?>);
      }

      function undo_water() {
        console.log('called undo_water()');
        // Reset button state
        $('#water_highlight').css('opacity', '0');
        $('#water_btn, #water_hover').css('opacity', '1');
        $('#waterlines_clip').css('opacity', '0');
        // House
        $('#stick_water').attr('display', 'none');
        $('#house_inside').attr('display', 'none');
        $('#fish').css('opacity', '0');
        i2 = 0;
      }
    <?php }
    if (in_array('stream', $resources)) { ?>

      function stream() {
        console.log('called stream()');
        // Set gauge URLs, set button states, print message to top of SVG
        $('#gauge1').attr('src', '<?php echo $gauges['stream']['gauge1']; ?>');
        $('#gauge2').attr('src', '<?php echo $gauges['stream']['gauge2']; ?>');
        $('#gauge3').attr('src', '<?php echo $gauges['stream']['gauge3']; ?>');
        $('#gauge4').attr('src', '<?php echo $gauges['stream']['gauge4']; ?>');
        $('#stream_highlight').css('opacity', '1');
        $('#stream_btn, #stream_hover').css('opacity', '0');
        <?php echo (isset($_GET['ver']) && $_GET['ver'] === 'kiosk') ? "\$('#stream_highlight').attr('visibility', '');" : ''; ?>
        // House
        $('#stick_stream').attr('display', 'visible');
        $('#house_inside').attr('display', 'visible');
        // Animation
        $('#flow_marks').css('opacity', '1');
        $('#fish').css('opacity', '1');
        var i3 = 0;
        $('#message').text(stream_messages[i3++]['message']);
        var msgTimer3 = new Timer(function() {
          if (i3 === stream_messages.length || current_state !== 'stream') {
            msgTimer3.stop();
          } else {
            $('#message').text(stream_messages[i3++]['message']);
          }
        }, <?php echo $timing['message_section'] * 1000; ?>);
      }

      function undo_stream() {
        console.log('called undo_stream()');
        // Reset button state
        $('#stream_highlight').css('opacity', '0');
        $('#stream_btn, #stream_hover').css('opacity', '1');
        // House
        $('#stick_stream').attr('display', 'none');
        $('#house_inside').attr('display', 'none');
        // Animation
        $('#flow_marks').css('opacity', '0');
        $('#fish').css('opacity', '0');
        i3 = 0;
      }
    <?php }
    if (in_array('weather', $resources)) { ?>

      function weather() {
        console.log('called weather()');
        // Set gauge URLs, set button states, print message to top of SVG
        $('#gauge1').attr('src', '<?php echo $gauges['weather']['gauge1']; ?>');
        $('#gauge2').attr('src', '<?php echo $gauges['weather']['gauge2']; ?>');
        $('#gauge3').attr('src', '<?php echo $gauges['weather']['gauge3']; ?>');
        $('#gauge4').attr('src', '<?php echo $gauges['weather']['gauge4']; ?>');
        $('#weather_highlight').css('opacity', '1');
        $('#weather_btn, #weather_hover').css('opacity', '0');
        <?php echo (isset($_GET['ver']) && $_GET['ver'] === 'kiosk') ? "\$('#weather_highlight').attr('visibility', '');" : ''; ?>
        // House
        $('#stick_weather').attr('display', 'visible');
        $('#house_inside').attr('display', 'visible');
        $('#squirrel').css('opacity', '1');
        var i4 = 0;
        $('#message').text(weather_messages[i4++]['message']);
        var msgTimer4 = new Timer(function() {
          if (i4 === weather_messages.length || current_state !== 'weather') {
            msgTimer4.stop();
          } else {
            $('#message').text(weather_messages[i4++]['message']);
          }
        }, <?php echo $timing['message_section'] * 1000; ?>);
      }

      function undo_weather() {
        console.log('called undo_weather()');
        // Reset button state
        $('#weather_highlight').css('opacity', '0');
        $('#weather_btn, #weather_hover').css('opacity', '1');
        <?php echo (isset($_GET['ver']) && $_GET['ver'] === 'kiosk') ? "\$('#weather_highlight').attr('visibility', '');" : ''; ?>
        // House
        $('#stick_weather').attr('display', 'none');
        $('#house_inside').attr('display', 'none');
        $('#squirrel').css('opacity', '0');
        i4 = 0;
      }
    <?php }
    if (in_array('gas', $resources)) { ?>

      function gas() {
        // Set gauge URLs, set button states, print message to top of SVG
        $('#gauge1').attr('src', '<?php echo $gauges['gas']['gauge1']; ?>');
        $('#gauge2').attr('src', '<?php echo $gauges['gas']['gauge2']; ?>');
        $('#gauge3').attr('src', '<?php echo $gauges['gas']['gauge3']; ?>');
        $('#gauge4').attr('src', '<?php echo $gauges['gas']['gauge4']; ?>');
        $('#gas_highlight').css('opacity', '1');
        $('#gas_btn, #gas_hover').css('opacity', '0');
        <?php echo (isset($_GET['ver']) && $_GET['ver'] === 'kiosk') ? "\$('#gas_highlight').attr('visibility', '');" : ''; ?>
        $('#squirrel').css('opacity', '1');
        var i5 = 0;
        $('#message').text(gas_messages[i5++]['message']);
        var msgTimer5 = new Timer(function() {
          if (i5 === gas_messages.length || current_state !== 'gas') {
            msgTimer5.stop();
          } else {
            $('#message').text(gas_messages[i5++]['message']);
          }
        }, <?php echo $timing['message_section'] * 1000; ?>);
      }

      function undo_gas() {
        // Reset button state
        $('#gas_highlight').css('opacity', '0');
        $('#gas_btn, #gas_hover').css('opacity', '1');
        $('#squirrel').css('opacity', '0');
        i5 = 0;
      }
    <?php } ?>

    // ANIMATIONS //

    // These animaions happen continuously
    // Bird animation
    function bird_animation() {
      var arr = [];
      var x = -100; // initially move left
      var y = 100; // initially move down
      for (var i = 0; i < 25; i++) {
        arr[i] = {
          x: x,
          y: y
        };
        var rand = Math.random();
        x -= (100 * rand * 2); // some random #
        if (rand > .9) {
          y -= 25; // move up a bit
        } else if (rand > .8) {
          // nothing
        } else if (rand > .7) {
          y += 25; // move down
        } else if (rand > .3) {
          y += 50;
        } else if (rand > .1) {
          y += 75;
        }
      }
      return arr;
    }
    TweenMax.to($('#bird1, #bird2, #bird3, #bird4'), 10, {
      bezier: {
        type: 'cubic',
        values: bird_animation(),
        autoRotate: false
      },
      scaleX: 1.3,
      scaleY: 1.3,
      ease: Power1.easeIn,
      repeat: -1,
      repeatDelay: 10
    }); //, x:"-1800px", y:(Math.random()*500)+"px", ease: Power1.easeIn, repeat: -1, repeatDelay: 10});
    // var c = 1;
    // var n = 2;
    var direction = 0;
    var frame = 1;
    setInterval(function() {
      // $('#bird' + c).css('opacity', '0');
      // $('#bird' + n).css('opacity', '1');
      // if (c == 3) {
      //   n = 0;
      // }
      // if (c == 4) {
      //   c = 0;
      //   n = 1;
      // }
      // c++;
      // n++;
      $('#bird' + (frame + 1)).css('opacity', '0');
      if (frame >= 3 || frame <= 0) {
        direction = !direction;
      }
      if (direction) {
        frame--;
      } else {
        frame++;
      }
      $('#bird' + (frame + 1)).css('opacity', '1');

    }, 100);
    // Rain animation
    <?php if ($its_raining) {
      for ($i = 0; $i < 100; $i++) { ?>
        TweenMax.to($('#raindrop-<?php echo $i; ?>'), 1, {
          y: 1000,
          repeat: -1,
          delay: <?php echo mt_rand(0, 100) / 10; ?>
        });
    <?php }
    } ?>
    // Ship animation
    TweenMax.to($('#ship'), 60, {
      scaleX: 0.7,
      scaleY: 0.7,
      x: "1260px",
      y: "140px",
      ease: Power1.easeInOut,
      repeat: -1,
      repeatDelay: 1
    });
    // Wind turbine animation
    TweenMax.to($('#blades'), 2.5, {
      rotation: 360,
      transformOrigin: "50% 60%",
      repeat: -1,
      ease: Power0.easeNone
    });
    // Smokestack image swapping
    <?php if ($user_id !== 3) { // but not for toledo 
    ?>
      var counter = 0;
      var smokestack = setInterval(function() {
        if (counter++ % 2 == 0) {
          $('#pipes').children().attr('xlink:href', 'img/smokestack/smokestack2.png');
        } else {
          $('#pipes').children().attr('xlink:href', 'img/smokestack/smokestack1.png');
        }
      }, 1000);
    <?php } ?>
    // Waterlines animations (very messy but there doesnt seem to be any other way of doing this but by animating clip-path)
    var tl1 = new TimelineMax({
      repeat: -1
    });
    var waterlines_clip1 = $('#waterline_clip1').children()[0];
    tl1.to(waterlines_clip1, <?php echo $water_speed * 0.25; ?>, {
        y: 80,
        ease: Power0.easeNone
      })
      .to(waterlines_clip1, <?php echo $water_speed * 0.25; ?>, {
        x: 50,
        ease: Power0.easeNone
      })
      .to(waterlines_clip1, <?php echo $water_speed * 1.5 ?>, {
        y: 385,
        x: 240,
        ease: Power0.easeNone
      })
      .to(waterlines_clip1, <?php echo $water_speed * 0.25; ?>, {
        y: 410,
        x: 210,
        ease: Power0.easeNone
      })
      .to(waterlines_clip1, <?php echo $water_speed; ?>, {
        y: 450,
        x: -100,
        ease: Power0.easeNone
      });
    var tl2 = new TimelineMax({
      repeat: -1,
      repeatDelay: 2
    });
    var waterlines_clip2 = $('#waterline_clip1').children()[1];
    tl2.to(waterlines_clip2, <?php echo $water_speed; ?>, {
      x: 230,
      y: 140,
      ease: Power0.easeNone
    });
    var tl3 = new TimelineMax({
      repeat: -1,
      repeatDelay: 2
    });
    var waterlines_clip3 = $('#waterline_clip1').children()[2];
    tl3.to(waterlines_clip3, <?php echo $water_speed; ?>, {
      x: -230,
      y: 160,
      ease: Power0.easeNone
    });
    var tl4 = new TimelineMax({
      repeat: -1
    });
    var waterlines_clip4 = $('#waterline_clip2').children()[0];
    tl4.to(waterlines_clip4, <?php echo $water_speed; ?>, {
      x: -330,
      y: 75,
      ease: Power0.easeNone
    });
    var tl5 = new TimelineMax({
      repeat: -1
    });
    var waterlines_clip5 = $('#waterline_clip3').children()[0];
    tl5.to(waterlines_clip5, <?php echo $water_speed; ?>, {
        x: 170,
        y: 270,
        ease: Power0.easeNone
      })
      .to(waterlines_clip5, <?php echo $water_speed * 0.375 ?>, {
        x: 50,
        y: 310,
        ease: Power0.easeNone
      })
      .to(waterlines_clip5, <?php echo $water_speed * 1.5 ?>, {
        x: -500,
        y: 340,
        ease: Power0.easeNone
      })
      .to(waterlines_clip5, <?php echo $water_speed * 0.5 ?>, {
        x: -620,
        y: 230,
        ease: Power0.easeNone
      })
    var tl6 = new TimelineMax({
      repeat: -1,
      repeatDelay: 2
    });
    var waterlines_clip6 = $('#waterline_clip3').children()[1];
    tl6.to(waterlines_clip6, <?php echo $water_speed * 0.5 ?>, {
      x: -300,
      ease: Power0.easeNone
    });
    var tl6 = new TimelineMax({
      repeat: -1,
      repeatDelay: 0.5
    });
    var waterlines_clip6 = $('#waterline_clip4').children()[0];
    tl6.to(waterlines_clip6, <?php echo $water_speed * 0.5 ?>, {
        x: -50,
        y: 75,
        ease: Power0.easeNone
      })
      .to(waterlines_clip6, <?php echo $water_speed * 0.75 ?>, {
        x: 100,
        y: 150,
        ease: Power0.easeNone
      })
      .to(waterlines_clip6, <?php echo $water_speed * 0.5 ?>, {
        x: 0,
        y: 200,
        ease: Power0.easeNone
      });
    // Flow marks animation
    TweenMax.to($('#flow_marks_clip').children()[0], 3, {
      y: "-600px",
      ease: Power0.easeNone,
      repeat: -1
    });
    // Smoke animation
    TweenMax.to($('#smoke > image'), 1, {
      y: "-60px",
      x: "20px",
      scaleX: 2,
      scaleY: 1.5,
      opacity: 0,
      ease: Power0.easeNone,
      repeat: -1,
      repeatDelay: 1.1
    });
    // Spark animations
    TweenMax.to($("#sparkpaths").children()[0], <?php echo $electricity_speed * 0.5; ?>, {
      bezier: [{
        x: 15,
        y: -25
      }, {
        x: 30,
        y: -50
      }],
      ease: Power0.easeNone,
      repeat: -1
    });
    TweenMax.to($("#sparkpaths").children()[1], <?php echo $electricity_speed * 0.5; ?>, {
      bezier: [{
        x: -150,
        y: 40
      }, {
        x: -200,
        y: 20
      }],
      ease: Power0.easeNone,
      repeat: -1
    });
    TweenMax.to($("#sparkpaths").children()[2], <?php echo $electricity_speed * 0.5; ?>, {
      bezier: [{
        x: 60,
        y: -70
      }, {
        x: 100,
        y: -140
      }],
      ease: Power0.easeNone,
      repeat: -1
    });
    TweenMax.to($("#sparkpaths_back").children()[0], <?php echo $electricity_speed * 0.5; ?>, {
      bezier: [{
        x: -10,
        y: 50
      }, {
        x: -20,
        y: 70
      }],
      ease: Power0.easeNone,
      repeat: -1
    });
    TweenMax.to($("#sparkpaths_back").children()[1], <?php echo $electricity_speed * 0.5; ?>, {
      bezier: [{
        x: 25,
        y: 50
      }, {
        x: 75,
        y: 100
      }],
      ease: Power0.easeNone,
      repeat: -1
    });

    // LANDSCAPE COMPONENTS //

    <?php
    $componentsCSSIds = [];
    foreach ($components as $key => $value) {
      $componentsCSSIds[] = "#$value";
    }
    $componentsCSSIds = implode(', ', $componentsCSSIds)
    ?>

    // Display landscape component messages on click
    $('<?php echo $componentsCSSIds ?>').click(function() {
      var id = '#' + $(this).attr('id');
      var box = id + '_message';
      var close = id + '_close';
      $(box).attr('display', 'visible');
      $(close).attr('display', 'visible');
    });
    $('#<?php echo implode('_close, #', $components); ?>_close').click(function() {
      // alert(this);
      var close = '#' + $(this).attr('id');
      var box = close.slice(0, -6) + '_message';
      $(box).attr('display', 'none');
      $(close).attr('display', 'none');
    });
    // Hover on landscape components (most of which are in g#clickables)
    $('#pipes, #house_inside, <?php echo $componentsCSSIds ?>').hover(
      function() {
        $(this).attr('filter', 'url(#landscape_components_filter)');
        var id = $(this).attr('id');
        if (id == 'pipes') {
          $("#industry").attr('filter', 'url(#landscape_components_filter)');
        } else if (id == 'industry') {
          $("#pipes").attr('filter', 'url(#landscape_components_filter)');
        }
      },
      function() {
        $(this).attr('filter', '');
        var id = $(this).attr('id');
        if (id == 'pipes') {
          $("#industry").attr('filter', '');
        } else if (id == 'industry') {
          $("#pipes").attr('filter', '');
        }
      }
    );

    // BUTTONS //

    // Hover on #buttons swaps out image
    $('#<?php echo implode(', #', array_keys($gauges)); ?>').hover(
      function() {
        var id = $(this).attr('id');
        if ($('#' + id + '_highlight').css('opacity') == 0) {
          $('#' + id + '_btn').css('opacity', '0');
          $('#' + id + '_hover').css('opacity', '1');
        }
      },
      function() {
        var id = $(this).attr('id');
        if ($('#' + id + '_highlight').css('opacity') == 0) {
          $('#' + $(this).attr('id') + '_btn').css('opacity', '1');
          $('#' + $(this).attr('id') + '_hover').css('opacity', '0');
        }
      }
    );

    <?php if (isset($_GET['ver']) && $_GET['ver'] === 'kiosk') { ?>
      $('#<?php echo implode(', #', $resources); ?>').attr('visibility', 'hidden');
      $('#<?php echo implode('_btn, #', $resources); ?>_btn').css('opacity', '1');
      $('#<?php echo implode('_highlight, #', $resources); ?>_highlight').attr('visibility', 'hidden');
      $('#<?php echo implode('_hover, #', $resources); ?>_hover').attr('visibility', 'hidden');
    <?php } ?>

    var last_state = 'landing';

    function nextState(nextStateValue = null) {
      // first undo everything & then apply the current state
      undo_weather();
      undo_electricity();
      undo_water();
      undo_stream();

      // store current state into last state
      last_state = current_state;

      // define specific state value
      if (nextStateValue) {
        current_state = nextStateValue;
      } else {
        // use cycle of states as per last_state
        switch (last_state) {
          case 'landing':
            current_state = 'electricity';
            break;
          case 'electricity':
            current_state = 'water';
            break;
          case 'water':
            current_state = 'stream';
            break;
          case 'stream':
            current_state = 'weather';
            break;
          case 'weather':
            current_state = 'electricity';
            break;
        }
      }

      // trigger specific callback function as per the current state
      switch (current_state) {
        case 'electricity':
          electricity();
          break;
        case 'water':
          water();
          break;
        case 'stream':
          stream();
          break;
        case 'weather':
          weather();
          break;
      }

      <?php if (isset($_GET['ver']) && $_GET['ver'] === 'kiosk'): ?>
        $('#' + last_state).attr('visibility', 'hidden');
        $('#' + current_state).attr('visibility', 'visible');
      <?php endif ?>
    }

    // Click on #buttons sets the index and calls the function of the new state
    $('#<?php echo implode(', #', array_keys($gauges)); ?>').click(function() {
      if (current_state !== 'landing') {
        window['undo_' + current_state]();
      }
      current_state = $(this).attr('id');
      window[current_state](); // Call clicked state
    });
    // Automatically play
    var playTimer = new Timer(function() {
      nextState();
    }, <?php echo $cwd_dashboard_interval * 1000; ?>);

    const playText = $('#playtext');
    const pauseText = $('#pausetext');
    // Play button
    $('#play').click(function() {
      if (pauseText.attr('display') === 'none') { // Currently paused
        nextState(); // Call once because setInterval doesnt fire immediatly
        togglePlayPause()
        playTimer.start();
        console.log('play');
      } else { // Currently playing
        togglePlayPause(false)
        playTimer.stop();
        console.log('pause');
      }
    });

    // if playState is true then the pause button will be visible which means states are playing else the play button will be visibiel 
    function togglePlayPause(playState = true) {
      if(playState){
        playText.attr('display', 'none');
        pauseText.attr('display', 'visible');
      } else {
        playText.attr('display', 'visible');
        pauseText.attr('display', 'none');
      }
    }

    // if current state is passed then start the timer else freeze the timer
    current_state ? playTimer.start() && togglePlayPause() : playTimer.stop();

    <?php 
    
    if (isset($_GET['ver']) && $_GET['ver'] === 'kiosk' && !$play_single_cwd_state): ?>
      // Start the play button after x seconds
      setTimeout(function() {
        // If the play button has not been pressed yet
        if (current_state === 'landing') {
          current_state = <?php echo "'$resources[1]';\n"; ?>
          <?php echo $resources[1] . "();\n"; ?>
          playTimer.start();
          togglePlayPause()
        }
      }, <?php echo $timing['delay'] * 1000; ?>);
    <?php endif ?>

    // refresh every 5 mins to get new data
    setTimeout(function() {
      // Don't reload if CWD is paused
      if (pauseText.attr('display') !== 'none') {
        window.location.reload();
      }
    }, 5 * 1000 * 60);
    // ]]>
  </script>
</svg>