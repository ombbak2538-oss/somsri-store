<?php
if (session_status()===PHP_SESSION_NONE) session_start();
include "_guard.php";
include __DIR__ . "/../../config/db.php";

$id=(int)($_GET['id']??0);
$conn->query("DELETE FROM users WHERE id=$id");
header("Location: members.php");
exit;