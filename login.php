<?php
session_start();
include 'db.php';

$message = "";

if(isset($_POST['login'])){

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $user = $result->fetch_assoc();

        if(password_verify($password, $user['password'])){

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['is_admin'] = (int) $user['is_admin'];

            if($_SESSION['is_admin'] === 1){
                header("Location: admin.php");
            }else{
                header("Location: dashboard.php");
            }
            exit();

        }else{
            $message = "Incorrect Password!";
        }

    }else{
        $message = "User Not Found!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Digital Document Vault - Login</title>

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
    filter:blur(2px);
}

.login-box{
    position:relative;
    width:420px;
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

.login-box h2{
    text-align:center;
    margin-bottom:6px;
    color:#1E2761;
    font-weight:700;
}

.login-box p{
    text-align:center;
    color:gray;
    margin-bottom:25px;
}

.form-floating{
    margin-bottom:16px;
}

.form-control{
    border-radius:10px;
    padding:14px;
    border:1.5px solid #e2e6f0;
    transition:.2s;
}

.form-control:focus{
    border-color:#D4AF37;
    box-shadow:0 0 0 .2rem rgba(212,175,55,.25);
}

.btn-login{
    width:100%;
    background:#1E2761;
    border:none;
    border-radius:10px;
    padding:12px;
    font-weight:600;
    letter-spacing:.3px;
    transition:.2s;
}

.btn-login:hover{
    background:#141B4D;
    transform:translateY(-2px);
    box-shadow:0 10px 20px rgba(20,27,77,.35);
}

.login-box a{
    color:#1E2761;
    font-weight:600;
    text-decoration:none;
}

.login-box a:hover{
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

</style>

</head>

<body>

<div class="blob" style="width:280px;height:280px;top:-80px;left:-80px;"></div>
<div class="blob" style="width:220px;height:220px;bottom:-60px;right:-60px;"></div>

<div class="login-box">

<div class="vault-icon">
<i class="bi bi-shield-lock-fill"></i>
</div>

<h2>Digital Document Vault</h2>

<p>Login to your account</p>

<?php
if($message!=""){
echo "<div class='alert alert-danger'>".htmlspecialchars($message)."</div>";
}
?>

<form method="POST">

<div class="form-floating">
<input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
<label for="email">Email Address</label>
</div>

<div class="form-floating">
<input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
<label for="password">Password</label>
</div>

<div class="d-grid">
<button type="submit" name="login" class="btn btn-login text-white">
Login
</button>
</div>

</form>

<div class="text-center mt-3">
Don't have an account?
<a href="register.php">Register</a>
</div>

</div>

</body>
</html>
