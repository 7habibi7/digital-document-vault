<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(empty($_SESSION['is_admin'])){
    header("Location: dashboard.php");
    exit();
}

include 'db.php';

$totalUsers = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$totalDocs  = $conn->query("SELECT COUNT(*) as total FROM documents")->fetch_assoc()['total'];

$docsQuery = "
    SELECT documents.id, documents.title, documents.filename, users.name AS owner, users.email AS owner_email
    FROM documents
    JOIN users ON users.id = documents.user_id
    ORDER BY documents.id DESC
";
$docs = $conn->query($docsQuery);

$usersQuery = "SELECT id, name, email, is_admin FROM users ORDER BY id ASC";
$users = $conn->query($usersQuery);
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel - Digital Document Vault</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

body{
    background:#f4f6fb;
    animation:fadeInBody .5s ease;
}

@keyframes fadeInBody{
    from{opacity:0;}
    to{opacity:1;}
}

.navbar{
    background:#141B4D !important;
    box-shadow:0 2px 10px rgba(0,0,0,.25);
}

.navbar .btn-sm{
    border-radius:8px;
}

.badge-admin{
    background:#D4AF37;
    color:#1E2761;
    font-weight:700;
    letter-spacing:.5px;
}

.card{
    border:none;
    border-radius:16px;
}

.stat-card{
    animation:riseUp .5s ease backwards;
}
.stat-card:nth-child(1){animation-delay:.05s;}
.stat-card:nth-child(2){animation-delay:.15s;}
.stat-card:nth-child(3){animation-delay:.25s;}

@keyframes riseUp{
    from{opacity:0; transform:translateY(16px);}
    to{opacity:1; transform:translateY(0);}
}

.stat-card:hover{
    transform:translateY(-6px);
    box-shadow:0 16px 30px rgba(0,0,0,.12) !important;
}

.stat-icon{
    width:56px;
    height:56px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 10px auto;
    font-size:24px;
    color:white;
}

.icon-navy{ background:#1E2761; }
.icon-gold{ background:#D4AF37; color:#1E2761 !important; }

.table thead{
    background:#141B4D;
}
.table thead th{
    color:white;
    border:none;
}
.table tbody tr:hover{
    background:#FFF7DF;
}

.section-title{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:10px;
}

.btn-danger{
    border-radius:8px;
}

footer{
    margin-top:40px;
    padding:20px;
    color:#777;
}

</style>

</head>

<body>

<nav class="navbar navbar-dark">
<div class="container">

<span class="navbar-brand">
<i class="bi bi-speedometer2" style="color:#D4AF37"></i>
Admin Panel
<span class="badge badge-admin ms-2">ADMIN</span>
</span>

<div class="text-white d-flex align-items-center">
<span class="me-2">Logged in as <b><?php echo htmlspecialchars($_SESSION['user_name']); ?></b></span>
<a href="dashboard.php" class="btn btn-sm text-white" style="background:#1E2761;">
<i class="bi bi-arrow-left"></i> My Dashboard
</a>
<a href="logout.php" class="btn btn-danger btn-sm ms-2">Logout</a>
</div>

</div>
</nav>

<div class="container mt-4">

<div class="row g-3 mb-4">

<div class="col-md-6">
<div class="card stat-card shadow-sm p-3 text-center">
<div class="stat-icon icon-navy"><i class="bi bi-people-fill"></i></div>
<h5>Total Users</h5>
<h2><?php echo (int) $totalUsers; ?></h2>
</div>
</div>

<div class="col-md-6">
<div class="card stat-card shadow-sm p-3 text-center">
<div class="stat-icon icon-gold"><i class="bi bi-folder-fill"></i></div>
<h5>Total Documents (all users)</h5>
<h2><?php echo (int) $totalDocs; ?></h2>
</div>
</div>

</div>

<div class="card shadow-sm p-4 mb-4">

<div class="section-title">
<i class="bi bi-people" style="color:#1E2761;font-size:22px;"></i>
<h4 class="mb-0">Registered Users</h4>
</div>

<table class="table table-hover align-middle">
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
</tr>
</thead>
<tbody>
<?php while($u = $users->fetch_assoc()){ ?>
<tr>
<td>#<?php echo (int) $u['id']; ?></td>
<td><?php echo htmlspecialchars($u['name']); ?></td>
<td><?php echo htmlspecialchars($u['email']); ?></td>
<td>
<?php if($u['is_admin']){ ?>
<span class="badge badge-admin">Admin</span>
<?php }else{ ?>
<span class="badge bg-secondary">User</span>
<?php } ?>
</td>
</tr>
<?php } ?>
</tbody>
</table>

</div>

<div class="card shadow-sm p-4">

<div class="section-title">
<i class="bi bi-folder2-open" style="color:#1E2761;font-size:22px;"></i>
<h4 class="mb-0">All Documents (every user)</h4>
</div>

<input type="text" id="search" class="form-control my-3" placeholder="Search by title, file, or owner...">

<?php if($totalDocs == 0){ ?>

<div class="text-center text-muted py-4">
<i class="bi bi-inbox" style="font-size:36px;"></i>
<div>No documents uploaded yet.</div>
</div>

<?php }else{ ?>

<table class="table table-hover align-middle">
<thead>
<tr>
<th>Title</th>
<th>File</th>
<th>Owner</th>
<th>Download</th>
<th>Delete</th>
</tr>
</thead>
<tbody id="myTable">
<?php while($row = $docs->fetch_assoc()){ ?>
<tr>
<td><?php echo htmlspecialchars($row['title']); ?></td>
<td><?php echo htmlspecialchars($row['filename']); ?></td>
<td>
<?php echo htmlspecialchars($row['owner']); ?>
<br><small class="text-muted"><?php echo htmlspecialchars($row['owner_email']); ?></small>
</td>
<td>
<a href="admin_download.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-success btn-sm">
<i class="bi bi-download"></i> Download
</a>
</td>
<td>
<a href="#" data-id="<?php echo (int) $row['id']; ?>" class="btn btn-danger btn-sm admin-delete-btn">
<i class="bi bi-trash"></i> Delete
</a>
</td>
</tr>
<?php } ?>
</tbody>
</table>

<?php } ?>

</div>

<footer class="text-center">
<hr>
<p>
Digital Document Vault — Admin Panel <br>
Software Development II Project <br>
Developed by Md. Habibur Rahman & Partner Name © 2026
</p>
</footer>

</div>

<script>

document.getElementById("search").addEventListener("keyup", function(){
    var value = this.value.toLowerCase();
    var rows = document.querySelectorAll("#myTable tr");
    rows.forEach(function(row){
        row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
    });
});

document.querySelectorAll(".admin-delete-btn").forEach(function(btn){
    btn.addEventListener("click", function(e){
        e.preventDefault();
        const id = this.dataset.id;
        Swal.fire({
            title: "Delete this document?",
            text: "This removes it for the user permanently.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete it"
        }).then((result) => {
            if(result.isConfirmed){
                window.location.href = "admin_delete.php?id=" + id;
            }
        });
    });
});

const params = new URLSearchParams(window.location.search);
const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});
if(params.get("deleted") == "1"){
    Toast.fire({ icon: "success", title: "Document deleted." });
}
if(params.get("error") == "notfound"){
    Toast.fire({ icon: "error", title: "Document not found." });
}

</script>

</body>
</html>
