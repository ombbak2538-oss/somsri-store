<?php
session_start();
header('Content-Type: application/json');

$act = $_GET['act'] ?? '';
$id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (($act === 'add' || $act === 'add_ajax') && $id > 0) {
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
}

elseif ($act === 'remove' && $id > 0) {
    unset($_SESSION['cart'][$id]);
}

elseif ($act === 'remove_multi' && !empty($_GET['ids'])) {
    $ids = explode(',', $_GET['ids']);
    foreach ($ids as $rid) {
        $rid = (int)$rid;
        unset($_SESSION['cart'][$rid]);
    }
}

elseif ($act === 'set_qty' && $id > 0 && isset($_GET['qty'])) {
    $qty = max(1, (int)$_GET['qty']);
    $_SESSION['cart'][$id] = $qty;
}

$totalCount = array_sum($_SESSION['cart']);

if ($act === 'add_ajax' || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
    echo json_encode([
        'status' => 'success',
        'cartCount' => $totalCount,
        'message' => 'Updated successfully'
    ]);
    exit;
}

$target = (strpos($act, 'add') !== false) ? ($_SERVER['HTTP_REFERER'] ?? 'index.php') : 'cart.php';
header("Location: $target");
exit;