<?php
// Important $_SERVER variables: REQUEST_TIME_FLOAT, HTTP_REFERER
// var_dump(getOS($_SERVER['HTTP_USER_AGENT']));
$ip = get_ip_address();
if ($ip !== '132.162.90.8') {
  $os = getOS($_SERVER['HTTP_USER_AGENT']);
  $browser = get_browser_name($_SERVER['HTTP_USER_AGENT']);

  $loc_arr = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"), true);
  $stmt = $db->prepare('INSERT INTO analytics (ip, referer, loc, coords, browser, platform) VALUES (?, ?, ?, ?, ?, ?)');
  $stmt->execute(array(
                   $ip,
                   (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '',
                   $loc_arr['city'] . ', ' . $loc_arr['region'] . ', ' . $loc_arr['country'],
                   $loc_arr['loc'],
                   get_browser_name($_SERVER['HTTP_USER_AGENT']),
                   getOS($_SERVER['HTTP_USER_AGENT'])
                 ));
}
function getOS($user_agent) { 
  $os_platform = "Unknown OS Platform";
  $os_array = array(
              '/windows nt 6.2/i'     =>  'Windows 8',
              '/windows nt 6.1/i'     =>  'Windows 7',
              '/windows nt 6.0/i'     =>  'Windows Vista',
              '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
              '/windows nt 5.1/i'     =>  'Windows XP',
              '/windows xp/i'         =>  'Windows XP',
              '/windows nt 5.0/i'     =>  'Windows 2000',
              '/windows me/i'         =>  'Windows ME',
              '/win98/i'              =>  'Windows 98',
              '/win95/i'              =>  'Windows 95',
              '/win16/i'              =>  'Windows 3.11',
              '/macintosh|mac os x/i' =>  'Mac OS X',
              '/mac_powerpc/i'        =>  'Mac OS 9',
              '/linux/i'              =>  'Linux',
              '/ubuntu/i'             =>  'Ubuntu',
              '/iphone/i'             =>  'iPhone',
              '/ipod/i'               =>  'iPod',
              '/ipad/i'               =>  'iPad',
              '/android/i'            =>  'Android',
              '/blackberry/i'         =>  'BlackBerry',
              '/webos/i'              =>  'Mobile'
              );
  foreach ($os_array as $regex => $value) { 
    if (preg_match($regex, $user_agent)) {
      $os_platform = $value;
    }
  }   
  return $os_platform;
}


function get_browser_name($user_agent) {
    if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
    elseif (strpos($user_agent, 'Edge')) return 'Edge';
    elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
    elseif (strpos($user_agent, 'Safari')) return 'Safari';
    elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
    elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';
    
    return 'Other';
}


function get_ip_address() {
  $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
  foreach ($ip_keys as $key) {
    if (array_key_exists($key, $_SERVER) === true) {
      foreach (explode(',', $_SERVER[$key]) as $ip) {
        // trim for safety measures
        $ip = trim($ip);
        // attempt to validate IP
        if (validate_ip($ip)) {
          return $ip;
        }
      }
    }
  }
  return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
}

/**
 * Ensures an ip address is both a valid IP and does not fall within
 * a private network range.
 */
function validate_ip($ip) {
  if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
    return false;
  }
  return true;
}
?>