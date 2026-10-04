<?php
include __DIR__.'/_guard.php';
include __DIR__ . '/../../config/db.php';

$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? 'PAID';
$tracking = trim($_POST['tracking_no'] ?? '');

$stmt = $conn->prepare("UPDATE orders SET status=?, tracking_no=? WHERE id=?");
$stmt->bind_param("ssi", $status, $tracking, $id);
$stmt->execute();

header("Location: orders.php");
