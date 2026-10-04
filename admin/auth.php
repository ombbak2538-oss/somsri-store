<?php
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

include __DIR__ . "/../../config/db.php";

if (!isset($_SESSION['admin']) && !empty($_COOKIE['remember_admin'])) {
  $_SESSION['admin'] = $_COOKIE['remember_admin'];
  session_regenerate_id(true);
}

if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit;
}

$timeout = 60 * 20;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
  session_unset();
  session_destroy();
  header("Location: login.php?timeout=1");
  exit;
}
$_SESSION['last_activity'] = time();

$now = date("Y-m-d H:i:s");
$stmt = $conn->prepare("REPLACE INTO admin_online (username, last_seen) VALUES (?, ?)");
$stmt->bind_param("ss", $_SESSION['admin'], $now);
$stmt->execute();

if (!isset($_SESSION['login_time_ts'])) {
  $_SESSION['login_time_ts'] = time();
}
