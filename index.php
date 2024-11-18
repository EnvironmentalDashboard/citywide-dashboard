<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>CWD</title>
  <link rel="stylesheet" href="./style.css">
</head>

<body>
  <?php
  $baseURL  = "cwd-files";
  $queryParams = http_build_query($_GET);
  $cwdURL = $_SERVER['HTTP_HOST'] . "/$baseURL/dashboard.php?$queryParams"
  ?>
  <object id="dashboard" type="image/svg+xml" data="//<?php echo $cwdURL ?>"></object>
</body>

</html>