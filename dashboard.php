<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM documents WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$countRow = $stmt->get_result()->fetch_assoc();
$totalDocuments = $countRow['total'];
$stmt->close();

$stmt = $conn->prepare("SELECT * FROM documents WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$docs = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Digital Document Vault</title>

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
    background:#1E2761 !important;
    box-shadow:0 2px 10px rgba(0,0,0,.2);
}

.navbar .btn-danger{
    border-radius:8px;
    transition:.2s;
}
.navbar .btn-danger:hover{
    transform:translateY(-1px);
}

.card{
    border:none;
    border-radius:16px;
    transition:.25s;
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
.icon-teal{ background:#028090; }

.btn-primary{
    background:#1E2761;
    border:none;
    border-radius:10px;
    transition:.2s;
}
.btn-primary:hover{
    background:#141B4D;
    transform:translateY(-2px);
    box-shadow:0 10px 18px rgba(20,27,77,.3);
}

.dropzone{
    border:2px dashed #c7cee6;
    border-radius:14px;
    padding:28px;
    text-align:center;
    background:#fafbff;
    cursor:pointer;
    transition:.2s;
}
.dropzone.dragover{
    border-color:#D4AF37;
    background:#FFF9E8;
    transform:scale(1.01);
}
.dropzone i{
    font-size:34px;
    color:#1E2761;
}
.dropzone .file-name{
    margin-top:8px;
    font-weight:600;
    color:#1E2761;
}

.table thead{
    background:#1E2761;
}
.table thead th{
    color:white;
    border:none;
}
.table tbody tr{
    animation:rowIn .35s ease backwards;
    transition:background .2s;
}
.table tbody tr:hover{
    background:#FFF7DF;
}

@keyframes rowIn{
    from{opacity:0; transform:translateX(-6px);}
    to{opacity:1; transform:translateX(0);}
}

.btn-sm{
    border-radius:8px;
    transition:.15s;
}
.btn-sm:hover{
    transform:translateY(-1px);
}

#search{
    border-radius:10px;
}

.empty-state{
    text-align:center;
    padding:40px 20px;
    color:#9aa1b5;
}
.empty-state i{
    font-size:40px;
    color:#c7cee6;
    display:block;
    margin-bottom:10px;
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
<i class="bi bi-shield-lock-fill" style="color:#D4AF37"></i>
Digital Document Vault
</span>

<div class="text-white">

Welcome,
<b><?php echo htmlspecialchars($_SESSION['user_name']); ?></b>

<?php if(!empty($_SESSION['is_admin'])){ ?>
<a href="admin.php" class="btn btn-sm ms-2" style="background:#D4AF37;color:#1E2761;font-weight:600;">
<i class="bi bi-speedometer2"></i>
Admin Panel
</a>
<?php } ?>

<a href="logout.php" class="btn btn-danger btn-sm ms-2">
Logout
</a>

</div>

</div>

</nav>

<div class="container mt-4">

<div class="row g-3">

<div class="col-md-4">
<div class="card stat-card shadow-sm p-3 text-center">
<div class="stat-icon icon-navy"><i class="bi bi-folder-fill"></i></div>
<h5>Total Documents</h5>
<h2><?php echo (int) $totalDocuments; ?></h2>
</div>
</div>

<div class="col-md-4">
<div class="card stat-card shadow-sm p-3 text-center">
<div class="stat-icon icon-gold"><i class="bi bi-person-fill"></i></div>
<h5>User</h5>
<h4><?php echo htmlspecialchars($_SESSION['user_name']); ?></h4>
</div>
</div>

<div class="col-md-4">
<div class="card stat-card shadow-sm p-3 text-center">
<div class="stat-icon icon-teal"><i class="bi bi-cloud-check-fill"></i></div>
<h5>Status</h5>
<h4>Active</h4>
</div>
</div>

</div>

<div class="card shadow-sm p-4 mb-4 mt-4">

<h3>
<i class="bi bi-upload" style="color:#1E2761"></i>
Upload Document
</h3>

<form action="upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">

<div class="mb-3">
<input type="text" name="title" class="form-control" placeholder="Document Title" required>
</div>

<div class="dropzone mb-3" id="dropzone">
<i class="bi bi-cloud-arrow-up"></i>
<div>Drag &amp; drop a file here, or click to browse</div>
<small class="text-muted">PDF, DOC/DOCX, XLSX, PPTX, JPG, PNG, TXT &middot; max 10&nbsp;MB</small>
<div class="file-name" id="fileNameLabel"></div>
<input type="file" name="document" id="fileInput" class="d-none" required>
</div>

<button class="btn btn-primary w-100 text-white" id="uploadBtn">
<i class="bi bi-upload"></i>
Upload Document
</button>

</form>

</div>

<div class="card shadow-sm mt-4 p-4">

<h3>
<i class="bi bi-folder2-open" style="color:#1E2761"></i>
My Documents
</h3>

<input type="text" id="search" class="form-control my-3" placeholder="Search Document...">

<?php if($totalDocuments == 0){ ?>

<div class="empty-state">
<i class="bi bi-inbox"></i>
No documents yet — upload your first file above.
</div>

<?php } else { ?>

<table class="table table-hover align-middle">

<thead>
<tr>
<th>Title</th>
<th>File</th>
<th>Download</th>
<th>Delete</th>
</tr>
</thead>

<tbody id="myTable">
<?php
$i = 0;
while($row = $docs->fetch_assoc()){
    $ext = strtolower(pathinfo($row['filename'], PATHINFO_EXTENSION));

    if($ext=="pdf"){ $icon = "📕"; }
    elseif($ext=="doc" || $ext=="docx"){ $icon = "📘"; }
    elseif($ext=="xlsx"){ $icon = "📗"; }
    elseif($ext=="pptx"){ $icon = "📙"; }
    elseif($ext=="jpg" || $ext=="jpeg" || $ext=="png"){ $icon = "🖼️"; }
    else{ $icon = "📁"; }
    $i++;
?>

<tr style="animation-delay:<?php echo min($i * 0.05, 0.5); ?>s">

<td><?php echo htmlspecialchars($row['title']); ?></td>

<td><?php echo $icon . " " . htmlspecialchars($row['filename']); ?></td>

<td>
<a href="download.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-success btn-sm">
<i class="bi bi-download"></i>
Download
</a>
</td>

<td>
<a href="#"
   data-id="<?php echo (int) $row['id']; ?>"
   class="btn btn-danger btn-sm delete-btn">
<i class="bi bi-trash"></i>
Delete
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
Digital Document Vault <br>
Software Development II Project <br>
Developed by Md. Habibur Rahman & Rashed Hawlader © 2026
</p>
</footer>

</div>

<script>

// Live search
document.getElementById("search").addEventListener("keyup", function(){
    var value = this.value.toLowerCase();
    var rows = document.querySelectorAll("#myTable tr");
    rows.forEach(function(row){
        row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
    });
});

// Drag & drop upload zone
const dropzone = document.getElementById("dropzone");
const fileInput = document.getElementById("fileInput");
const fileNameLabel = document.getElementById("fileNameLabel");

dropzone.addEventListener("click", () => fileInput.click());

fileInput.addEventListener("change", () => {
    if(fileInput.files.length > 0){
        fileNameLabel.textContent = "Selected: " + fileInput.files[0].name;
    }
});

["dragenter","dragover"].forEach(evt => {
    dropzone.addEventListener(evt, (e) => {
        e.preventDefault();
        dropzone.classList.add("dragover");
    });
});

["dragleave","drop"].forEach(evt => {
    dropzone.addEventListener(evt, (e) => {
        e.preventDefault();
        dropzone.classList.remove("dragover");
    });
});

dropzone.addEventListener("drop", (e) => {
    if(e.dataTransfer.files.length > 0){
        fileInput.files = e.dataTransfer.files;
        fileNameLabel.textContent = "Selected: " + e.dataTransfer.files[0].name;
    }
});

document.getElementById("uploadForm").addEventListener("submit", function(){
    const btn = document.getElementById("uploadBtn");
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading...';
});

// Delete with SweetAlert2 confirm
document.querySelectorAll(".delete-btn").forEach(function(btn){
    btn.addEventListener("click", function(e){
        e.preventDefault();
        const id = this.dataset.id;
        Swal.fire({
            title: "Delete this document?",
            text: "This cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete it"
        }).then((result) => {
            if(result.isConfirmed){
                window.location.href = "delete.php?id=" + id;
            }
        });
    });
});

// Status toasts from redirect query params
const params = new URLSearchParams(window.location.search);
const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

if(params.get("success") == "1"){
    Toast.fire({ icon: "success", title: "Document uploaded successfully!" });
}
if(params.get("deleted") == "1"){
    Toast.fire({ icon: "success", title: "Document deleted." });
}
if(params.get("error") == "type"){
    Toast.fire({ icon: "error", title: "That file type isn't allowed." });
}
if(params.get("error") == "size"){
    Toast.fire({ icon: "error", title: "File is larger than 10 MB." });
}
if(params.get("error") == "upload"){
    Toast.fire({ icon: "error", title: "Upload failed. Try again." });
}
if(params.get("error") == "denied"){
    Toast.fire({ icon: "error", title: "You don't have access to that document." });
}

</script>

</body>

</html>
