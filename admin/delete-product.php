<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

$id = (int)$_GET['id'];

$p = $conn->query("SELECT image FROM products WHERE id=$id")->fetch_assoc();

$conn->query("DELETE FROM products WHERE id=$id");

if (!empty($p['image'])) {
  @unlink(__DIR__ . "/../uploads/" . $p['image']);
}

header("Location: products.php");
exit;
