<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CWD</title>
  <style>
  	html, body {width: 100%; height: 100%;}
  	body {margin:0px;padding:0px;background:#000;}
  	#dashboard {width:100%;}
  </style>
</head>
<body>
<object id="dashboard" type="image/svg+xml" data="//<?php echo $_SERVER['HTTP_HOST'] . '/' . explode('/', $_SERVER['REQUEST_URI'])[1]; ?>/cwd/dashboard.php?<?php echo http_build_query($_GET); ?>"></object>
</body>
</html>