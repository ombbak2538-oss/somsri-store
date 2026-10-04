<?php
session_start();
session_unset();
session_destroy();
header("Location: /somsri-store/public/admin/login.php");
exit;
