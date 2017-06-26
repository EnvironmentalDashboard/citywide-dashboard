<?php
require '../../includes/db.php';
if (isset($_POST['h']) && isset($_POST['w'])) {
  $stmt = $db->prepare('UPDATE cwd_landscape_components SET pos = ?, widthxheight = ? WHERE component = ? AND user_id = ?');
  $stmt->execute(array($_POST['x'].','.$_POST['y'], $_POST['w'].'x'.$_POST['h'], $_POST['comp'], $_POST['id']));
} else {
  $stmt = $db->prepare('UPDATE cwd_landscape_components SET pos = ? WHERE component = ? AND user_id = ?');
  $stmt->execute(array($_POST['x'].','.$_POST['y'], $_POST['comp'], $_POST['id']));
}
?>