<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$id = (int) ($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){

    $row = $result->fetch_assoc();

    $file = "uploads/" . $row['filename'];

    if(file_exists($file)){
        unlink($file);
    }

    $del = $conn->prepare("DELETE FROM documents WHERE id = ? AND user_id = ?");
    $del->bind_param("ii", $id, $user_id);
    $del->execute();
    $del->close();

    header("Location: dashboard.php?deleted=1");
    exit();

}else{
    header("Location: dashboard.php?error=denied");
    exit();
}
?>
