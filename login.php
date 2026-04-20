<?php
require_once 'conf.php';

// Redirect jika sudah login
if ($is_logged_in) { header('Location: catalog.php'); exit; }
if ($is_admin)     { header('Location: admin.php');   exit; }

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $e = mysqli_real_escape_string($conn, $email);
        $p = mysqli_real_escape_string($conn, $password);

        // Cek admin dulu
        $admin_q = mysqli_query($conn, "SELECT id, nama FROM admin WHERE email='$e' AND password='$p' LIMIT 1");
        if ($admin_q && mysqli_num_rows($admin_q) === 1) {
            $admin = mysqli_fetch_assoc($admin_q);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_nama'] = $admin['nama'];
            header('Location: admin.php');
            exit;
        }

        // Cek user
        $user_q = mysqli_query($conn, "SELECT id, nama, email FROM users WHERE email='$e' AND password='$p' LIMIT 1");
        if ($user_q && mysqli_num_rows($user_q) === 1) {
            $user = mysqli_fetch_assoc($user_q);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_nama']  = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            $redirect = $_GET['redirect'] ?? 'catalog.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Email atau password salah. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk — ScholarHub Portal Beasiswa</title>
  <meta name="description" content="Masuk ke akun ScholarHub dan mulai perjalanan beasiswamu.">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-[#0A0A0F] min-h-screen flex flex-col relative overflow-hidden">
  <!-- BG decorations -->
  <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#FF312E]/10 rounded-full blur-[120px] pointer-events-none"></div>
  <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-purple-600/10 rounded-full blur-[100px] pointer-events-none"></div>
  <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),linear-gradient(90deg,rgba(255,255,255,.06) 1px,transparent 1px);background-size:40px 40px;"></div>

  <!-- Logo header -->
  <div class="relative z-10 container py-6">
    <a href="index.php" class="flex items-center gap-2 w-fit">
      <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#FF312E] to-[#FF6B35] flex items-center justify-center text-white font-black text-sm">🎓</div>
      <span class="font-black text-lg text-white">Scholar<span class="text-[#FF312E]">Hub</span></span>
    </a>
  </div>

  <main class="flex-1 flex items-center justify-center relative z-10 px-4 pb-12">
    <div class="w-full max-w-4xl fade-up">
      <div class="grid grid-cols-1 md:grid-cols-2 overflow-hidden rounded-3xl shadow-2xl">

        <!-- Left panel -->
        <div class="bg-gradient-to-br from-[#FF312E] to-[#CC1A18] p-10 flex flex-col justify-between">
          <div>
            <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-3xl mb-8">🎓</div>
            <h1 class="text-white text-3xl font-black mb-3 leading-tight">
              Selamat<br><span class="opacity-80">Datang</span><br>Kembali!
            </h1>
            <p class="text-white/70 text-sm leading-relaxed mb-8">
              Masuk ke akunmu dan lanjutkan perjalanan menuju beasiswa impianmu.
            </p>
            <div class="space-y-3">
              <?php
              $features = ['Akses semua program beasiswa', 'Pantau status pengajuanmu', 'Hapus pengajuan kapan saja', 'Kelola profil akademikmu'];
              foreach ($features as $f): ?>
              <div class="flex items-center gap-3 text-white/80 text-sm">
                <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-xs">✓</div>
                <?= $f ?>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="mt-8 pt-6 border-t border-white/20">
            <p class="text-white/50 text-xs">Belum punya akun?</p>
            <a href="register.php" class="text-white font-bold text-sm hover:underline">Daftar gratis sekarang →</a>
          </div>
        </div>

        <!-- Right: Form -->
        <div class="bg-white p-10">
          <h2 class="text-2xl font-black text-[#0A0A0F] mb-1">Masuk Akun</h2>
          <p class="text-sm text-gray-400 mb-8">Gunakan email dan password yang terdaftar</p>

          <?php if ($error): ?>
          <div class="alert alert-error mb-6 fade-up" id="error-alert">⚠️ <?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="POST" action="" id="login-form" novalidate class="space-y-5">
            <div class="form-group">
              <label for="email" class="form-label">Email</label>
              <input type="email" id="email" name="email"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                     placeholder="kamu@email.com"
                     class="form-input" required>
              <span class="form-error" id="err-email">Format email tidak valid.</span>
            </div>

            <div class="form-group">
              <label for="password" class="form-label">Password</label>
              <div class="relative">
                <input type="password" id="password" name="password"
                       placeholder="Masukkan password"
                       class="form-input pr-12" required>
                <button type="button" id="toggle-pass"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#FF312E] transition-colors text-lg" aria-label="Toggle password">
                  👁
                </button>
              </div>
              <span class="form-error" id="err-pass">Password wajib diisi.</span>
            </div>

            <button type="submit" id="btn-submit-login"
                    class="btn-primary w-full py-3.5 rounded-xl text-base">
              Masuk →
            </button>

            <div class="bg-gray-50 rounded-xl p-4 text-xs text-gray-500 space-y-1">
              <p class="font-semibold text-gray-600">Demo Akun:</p>
              <p>👤 User: <code class="bg-gray-200 px-1 rounded">budi@gmail.com</code> | <code class="bg-gray-200 px-1 rounded">password123</code></p>
              <p>⚙️ Admin: <code class="bg-gray-200 px-1 rounded">admin@portalbeasiswa.id</code> | <code class="bg-gray-200 px-1 rounded">admin123</code></p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

<script>
const form = document.getElementById('login-form');
const emailEl = document.getElementById('email');
const passEl  = document.getElementById('password');
const toggleP = document.getElementById('toggle-pass');

toggleP.addEventListener('click', () => {
  passEl.type = passEl.type === 'password' ? 'text' : 'password';
});

function showErr(id, show) {
  document.getElementById(id).classList.toggle('visible', show);
}

form.addEventListener('submit', e => {
  let valid = true;
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value.trim())) {
    showErr('err-email', true); valid = false;
  } else showErr('err-email', false);

  if (!passEl.value.trim()) {
    showErr('err-pass', true); valid = false;
  } else showErr('err-pass', false);

  if (!valid) e.preventDefault();
  else {
    document.getElementById('btn-submit-login').textContent = '⏳ Memproses...';
    document.getElementById('btn-submit-login').disabled = true;
  }
});

const errAlert = document.getElementById('error-alert');
if (errAlert) setTimeout(() => errAlert.style.opacity = '0', 5000);
</script>
</body>
</html>
