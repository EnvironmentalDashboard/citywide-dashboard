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
<!-- <iframe id="dashboard" src="dashboard.php" frameborder="0"></iframe> -->
<!-- <img id="dashboard" src="dashboard.php" alt=""> -->
<object id="dashboard" type="image/svg+xml" data="http://<?php echo $_SERVER['HTTP_HOST'] ?>/oberlin/cwd/dashboard.php"></object>
</body>
</html>