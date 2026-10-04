<?php
// members_save.php
include __DIR__ . "/../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ดึงค่าตาม name ที่ตั้งไว้ในฟอร์ม
    $emp_code   = $_POST['emp_code'];
    $username   = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role       = $_POST['role'];

    // จัดการรูปภาพ
    $avatar_name = "default.png";
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $path = "../../public/avatars/";
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatar_name = "user_" . time() . "." . $ext;
        move_uploaded_file($_FILES['avatar']['tmp_name'], $path . $avatar_name);
    }

    // INSERT ให้ตรงคอลัมน์ในรูปที่ 8
    $sql = "INSERT INTO users (emp_code, username, first_name, last_name, password, role, avatar) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $emp_code, $username, $first_name, $last_name, $password, $role, $avatar_name);

    if ($stmt->execute()) {
        echo "<script>alert('บันทึกสำเร็จ'); window.location.href='members.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}