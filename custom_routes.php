<?php require './includes/db.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CWD</title>
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <?php
    // echo "<pre>";
    // print_r($_GET);
    // exit;
    /* interval is time to show the water electricity air & river dashboard default is 30 seconds  */
    $interval = (isset($_GET['interval'])) ? $_GET['interval'] * 1 : '';
    $current_state = (isset($_GET['current_state'])) ? $_GET['current_state'] : '';
    $baseURL  = "cwd-files";
    $dashboardURL = $_SERVER['HTTP_HOST'] .  "/$baseURL/dashboard.php?interval=$interval&current_state=$current_state";
?>
    <object id="dashboard" type="image/svg+xml" data="//<?php echo $dashboardURL ?>"></object>
</body>

</html>