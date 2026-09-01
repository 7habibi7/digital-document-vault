<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$allowed_ext = ['pdf','doc','docx','jpg','jpeg','png','txt','xlsx','pptx'];
$max_size = 10 * 1024 * 1024; // 10 MB

if(isset($_POST['title'])){

    $title = trim($_POST['title']);
    $user_id = $_SESSION['user_id'];

    if(!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK){
        header("Location: dashboard.php?error=upload");
        exit();
    }

    $originalName = $_FILES['document']['name'];
    $tempname = $_FILES['document']['tmp_name'];
    $size = $_FILES['document']['size'];

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if(!in_array($ext, $allowed_ext)){
        header("Location: dashboard.php?error=type");
        exit();
    }

    if($size > $max_size){
        header("Location: dashboard.php?error=size");
        exit();
    }

    // Strip unsafe characters from the original name and keep it short
    $safeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
    $safeBase = substr($safeBase, 0, 60);
    $newname = time() . "_" . bin2hex(random_bytes(4)) . "_" . $safeBase . "." . $ext;

    if(move_uploaded_file($tempname, "uploads/" . $newname)){

        $stmt = $conn->prepare("INSERT INTO documents (user_id, title, filename) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $title, $newname);

        if($stmt->execute()){
            header("Location: dashboard.php?success=1");
            exit();
        }else{
            unlink("uploads/" . $newname);
            header("Location: dashboard.php?error=db");
            exit();
        }

        $stmt->close();

    }else{
        header("Location: dashboard.php?error=upload");
        exit();
    }

}
