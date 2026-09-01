<?php
session_start();
include 'db.php';

$message = "";

if(isset($_POST['register'])){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if(strlen($password) < 6){

        $message = "Password must be at least 6 characters!";

    }else{

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){

            $message = "Email already exists!";

        }else{

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashedPassword);

            if($stmt->execute()){

                header("Location: login.php");
                exit();

            }else{

                $message = "Registration Failed!";

            }

            $stmt->close();

        }

        $check->close();

    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Register</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

*{
    box-sizing:border-box;
}

body{
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(-45deg,#1E2761,#3a4a9f,#141B4D,#2a3480);
    background-size:400% 400%;
    animation:gradientShift 12s ease infinite;
    font-family:Arial,sans-serif;
    overflow:hidden;
}

@keyframes gradientShift{
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

.blob{
    position:absolute;
    border-radius:50%;
    background:rgba(212,175,55,.08);
}

.register-box{
    position:relative;
    width:450px;
    background:rgba(255,255,255,.97);
    padding:45px 40px;
    border-radius:20px;
    box-shadow:0 25px 60px rgba(0,0,0,.4);
    animation:riseIn .6s cubic-bezier(.16,1,.3,1);
    z-index:2;
}

@keyframes riseIn{
    from{opacity:0; transform:translateY(24px) scale(.97);}
    to{opacity:1; transform:translateY(0) scale(1);}
}

.vault-icon{
    width:72px;
    height:72px;
    background:linear-gradient(135deg,#D4AF37,#F1D67A);
    color:#1E2761;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:32px;
    margin:0 auto 18px auto;
    box-shadow:0 8px 20px rgba(212,175,55,.4);
}

.register-box h2{
    color:#1E2761;
    font-weight:700;
}

.form-control{
    margin-bottom:15px;
    border-radius:10px;
    padding:13px 14px;
    border:1.5px solid #e2e6f0;
    transition:.2s;
}

.form-control:focus{
    border-color:#D4AF37;
    box-shadow:0 0 0 .2rem rgba(212,175,55,.25);
}

.btn-primary{
    background:#1E2761;
    border:none;
    border-radius:10px;
    padding:12px;
    font-weight:600;
    letter-spacing:.3px;
    transition:.2s;
}

.btn-primary:hover{
    background:#141B4D;
    transform:translateY(-2px);
    box-shadow:0 10px 20px rgba(20,27,77,.35);
}

.register-box a{
    color:#1E2761;
    font-weight:600;
    text-decoration:none;
}

.register-box a:hover{
    color:#D4AF37;
}

.alert{
    border-radius:10px;
    animation:shake .4s;
}

@keyframes shake{
    0%,100%{transform:translateX(0);}
    25%{transform:translateX(-6px);}
    75%{transform:translateX(6px);}
}

.form-hint{
    font-size:12px;
    color:#9aa1b5;
    margin-top:-10px;
    margin-bottom:15px;
}

</style>

</head>

<body>

<div class="blob" style="width:280px;height:280px;top:-80px;right:-80px;"></div>
<div class="blob" style="width:220px;height:220px;bottom:-60px;left:-60px;"></div>

<div class="register-box">

<div class="vault-icon">
<i class="bi bi-shield-lock-fill"></i>
</div>

<h2 class="text-center">Create Account</h2>

<p class="text-center text-muted">
Digital Document Vault
</p>

<?php
if($message!=""){
echo "<div class='alert alert-danger'>".htmlspecialchars($message)."</div>";
}
?>

<form method="POST">

<input
type="text"
name="name"
class="form-control"
placeholder="Full Name"
required>

<input
type="email"
name="email"
class="form-control"
placeholder="Email Address"
required>

<input
type="password"
name="password"
class="form-control"
placeholder="Password"
minlength="6"
required>

<div class="form-hint">Minimum 6 characters</div>

<div class="d-grid">

<button
type="submit"
name="register"
class="btn btn-primary text-white">

Create Account

</button>

</div>

</form>

<div class="text-center mt-3">

Already have an account?

<a href="login.php">

Login

</a>

</div>

</div>

</body>
</html>
