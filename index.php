<?php
session_start();
include "koneksi.php";

if (isset($_POST['login'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];

    if ($user == "admin" && $pass == "12345") {
        $_SESSION['login'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin | Tabungan Santri</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body{
  font-family: 'Inter', sans-serif;
  background: linear-gradient(135deg, #0f766e, #16a34a);
}

.login-card{
  width:100%;
  max-width:380px;
  border-radius:16px;
  animation: fadeIn 0.8s ease;
}

.form-control{
  border-radius:10px;
  padding:12px;
}

.form-control:focus{
  box-shadow:none;
  border-color:#16a34a;
}

.btn-login{
  background:#16a34a;
  border:none;
  padding:12px;
  border-radius:10px;
  font-weight:600;
}

.btn-login:hover{
  background:#15803d;
}

.toggle-password{
  cursor:pointer;
  font-size:14px;
  color:#64748b;
}

@keyframes fadeIn{
  from{opacity:0; transform:translateY(20px)}
  to{opacity:1; transform:translateY(0)}
}
</style>
</head>

<body class="d-flex align-items-center justify-content-center vh-100">

<div class="card login-card shadow-lg p-4">
  <div class="text-center mb-3">
    <h4 class="fw-bold">Login Admin</h4>
    <small class="text-muted">Sistem Tabungan Santri</small>
  </div>

  <?php if (isset($error)) : ?>
    <div class="alert alert-danger text-center py-2">
      <?= $error ?>
    </div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" name="user" class="form-control" placeholder="Masukkan username" required>
    </div>

    <div class="mb-2">
      <label class="form-label">Password</label>
      <input type="password" name="pass" id="password" class="form-control" placeholder="Masukkan password" required>
    </div>

    <div class="mb-3 text-end">
      <span class="toggle-password" onclick="togglePassword()">Tampilkan Password</span>
    </div>

    <button type="submit" name="login" class="btn btn-login w-100">
      Masuk
    </button>
  </form>

  <div class="text-center mt-3">
    <small class="text-muted">© <?= date('Y') ?> Tabungan Santri</small>
  </div>
</div>

<script>
function togglePassword(){
  const input = document.getElementById('password');
  input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

</body>
</html>
