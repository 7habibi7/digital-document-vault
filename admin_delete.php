<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(empty($_SESSION['is_admin'])){
    header("Location: dashboard.php");
    exit();
}

$id = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){

    $row = $result->fetch_assoc();
    $file = "uploads/" . $row['filename'];

    if(file_exists($file)){
        unlink($file);
    }

    $del = $conn->prepare("DELETE FROM documents WHERE id = ?");
    $del->bind_param("i", $id);
    $del->execute();
    $del->close();

    header("Location: admin.php?deleted=1");
    exit();

}else{
    header("Location: admin.php?error=notfound");
    exit();
}
?>
