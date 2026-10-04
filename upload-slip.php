<?php
session_start();
include __DIR__ . "/../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

$order_id = (int)($_POST['order_id'] ?? 0);
$method   = $_POST['payment_method'] ?? '';

if (!$order_id || !in_array($method, ['promptpay','bank'], true)) {
  http_response_code(400);
  exit('Bad Request');
}

if (!isset($_FILES['slip']) || $_FILES['slip']['error'] !== UPLOAD_ERR_OK) {
  http_response_code(400);
  exit('No file uploaded');
}

$allowed = ['image/jpeg','image/png','image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $_FILES['slip']['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed, true)) {
  http_response_code(400);
  exit('Invalid file type');
}

$ext = match($mime){
  'image/jpeg' => 'jpg',
  'image/png'  => 'png',
  'image/webp' => 'webp',
};

$dir = __DIR__ . "/slips";
if (!is_dir($dir)) mkdir($dir, 0775, true);

$filename = "slip_{$order_id}_" . time() . ".$ext";
$path = $dir . "/" . $filename;

if (!move_uploaded_file($_FILES['slip']['tmp_name'], $path)) {
  http_response_code(500);
  exit('Upload failed');
}

$relPath = "slips/" . $filename;

$stmt = $conn->prepare("UPDATE orders SET slip_path=?, status='pending_verify' WHERE id=?");
$stmt->bind_param('si', $relPath, $order_id);
$stmt->execute();

echo json_encode(['ok'=>true, 'path'=>$relPath]);