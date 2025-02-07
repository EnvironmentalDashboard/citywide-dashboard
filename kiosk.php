<?php require '../includes/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CWD</title>
<link rel="stylesheet" href="./style.css">
</head>
<body>
<?php
$youtube = false;
if (isset($_GET['loc_id'])) {
  $stmt = $db->prepare('SELECT youtube_id FROM youtube_screens WHERE screen_id = ? AND probability > 0 ORDER BY probability * rand() * rand() * rand() * rand() * rand() DESC LIMIT 1');
  $stmt->execute(array($_GET['loc_id']));
  $youtube = $stmt->fetchColumn();
}
if ($youtube !== false) {
?>
<div id="player"></div>
<object id="dashboard" type="image/svg+xml" data=""></object>
<script>
  function getParameterByName(name, url) {
    if (!url) {
      url = window.location.href;
    }
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
  }
  
  // 2. This code loads the IFrame Player API code asynchronously.
  console.log('playing video');
  var tag = document.createElement('script');
  tag.src = "https://www.youtube.com/iframe_api";
  var firstScriptTag = document.getElementsByTagName('script')[0];
  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

  // 3. This function creates an <iframe> (and YouTube player)
  //    after the API code downloads.
  var player;
  function onYouTubeIframeAPIReady() {
    var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
    player = new YT.Player('player', {
      height: h,
      width: w,
      playerVars: { autoplay: 1, controls: 0, mute: 1 },
      videoId: <?php echo json_encode($db->query('SELECT video_id FROM youtube WHERE id = '.intval($youtube))->fetchColumn()); ?>,
      events: {
        'onReady': onPlayerReady,
        'onStateChange': onPlayerStateChange
      }
    });
  }

  // 4. The API will call this function when the video player is ready.
  function onPlayerReady(event) {
    event.target.playVideo();
  }

  // 5. The API calls this function when the player's state changes.
  //    The function indicates that when playing a video (state=1),
  //    the player should play for six seconds and then stop.
  var done = false;
  function onPlayerStateChange(event) {
    //When the video has ended
    if (event.data == YT.PlayerState.ENDED) {
        console.log('player ended');
        document.getElementById('dashboard').setAttribute('data', '//<?php echo $_SERVER['HTTP_HOST'] . '/' . explode('/', $_SERVER['REQUEST_URI'])[1]; ?>/dashboard.php?ver=kiosk');
        //Get rid of the player
        event.target.destroy();
    }
  }
  setTimeout(function(){ window.location.reload(false); }, <?php echo (isset($_GET['timer'])) ? $_GET['timer'] * 1000 : 80000; ?>);
</script>
<?php } else {
  /* interval is time to show the water electricity air & river dashboard default is 30 seconds  */
  $interval = (isset($_GET['interval'])) ? (int)$_GET['interval'] * 1 : '';
  $current_state = (isset($_GET['current_state'])) ? $_GET['current_state'] : '';
  $baseURL  = "cwd-files";
  $dashboardURL = $_SERVER['HTTP_HOST'] .  "/$baseURL/dashboard.php?ver=kiosk&interval=$interval&current_state=$current_state";
  ?>
<object id="dashboard" type="image/svg+xml" data="//<?php echo $dashboardURL?>"></object>
<?php } ?>
</body>
</html>