<?php
require_once 'conf.php';

if ($is_logged_in) {
    header('Location: catalog.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama            = trim($_POST['nama']             ?? '');
    $email           = trim($_POST['email']            ?? '');
    $password        = trim($_POST['password']         ?? '');
    $confirm_password= trim($_POST['confirm_password'] ?? '');

    // Server-side validation
    if (empty($nama) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field wajib diisi.';
    } elseif (strlen($nama) < 3) {
        $error = 'Nama minimal 3 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $email_safe = mysqli_real_escape_string($conn, $email);

        // Cek email duplikat
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email_safe' LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Email sudah terdaftar. Gunakan email lain atau langsung masuk.';
        } else {
            $nama_safe = mysqli_real_escape_string($conn, $nama);
            $pass_safe = mysqli_real_escape_string($conn, $password);

            $insert = "INSERT INTO users (nama, email, password) VALUES ('$nama_safe', '$email_safe', '$pass_safe')";
            if (mysqli_query($conn, $insert)) {
                $new_id = mysqli_insert_id($conn);
                $_SESSION['user_id']    = $new_id;
                $_SESSION['user_nama']  = $nama;
                $_SESSION['user_email'] = $email;
                header('Location: catalog.php?welcome=1');
                exit;
            } else {
                $error = 'Terjadi kesalahan. Coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Akun — Portal Beasiswa</title>
  <meta name="description" content="Buat akun Portal Beasiswa secara gratis dan mulai perjalananmu menemukan beasiswa.">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="font-['IBM_Plex_Mono',monospace] bg-[#FAFAFA] min-h-screen flex flex-col">

<?php include 'includes/navbar.php'; ?>

<main class="flex-1 flex items-center justify-center py-16 px-4">
  <div class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row fade-up">

    <!-- Left panel -->
    <div class="bg-[#000803] md:w-2/5 p-10 flex flex-col justify-center">
      <div class="mb-8">
        <div class="w-12 h-12 rounded-xl bg-[#FF312E] flex items-center justify-center text-white text-2xl mb-6">✨</div>
        <h1 class="text-white text-3xl font-black mb-3">
          Buat<br><span class="text-[#FF312E]">Akunmu</span> Sekarang
        </h1>
        <p class="text-white/50 text-sm leading-relaxed">
          Daftar gratis dan mulai jelajahi ratusan peluang beasiswa yang menunggumu.
        </p>
      </div>
      <div class="space-y-3">
        <div class="flex items-center gap-3 text-white/60 text-xs">
          <span class="text-[#FF312E]">✓</span> Gratis, tidak ada biaya tersembunyi
        </div>
        <div class="flex items-center gap-3 text-white/60 text-xs">
          <span class="text-[#FF312E]">✓</span> Daftar ke banyak beasiswa sekaligus
        </div>
        <div class="flex items-center gap-3 text-white/60 text-xs">
          <span class="text-[#FF312E]">✓</span> Pantau semua status dari satu tempat
        </div>
        <div class="flex items-center gap-3 text-white/60 text-xs">
          <span class="text-[#FF312E]">✓</span> Data kamu aman dan terjaga
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="flex-1 p-10 overflow-y-auto">
      <h2 class="text-2xl font-black text-[#000803] mb-2">Daftar Akun</h2>
      <p class="text-sm text-[#515052] mb-8">
        Sudah punya akun?
        <a href="login.php" class="text-[#FF312E] font-semibold hover:underline">Masuk di sini</a>
      </p>

      <?php if ($error): ?>
      <div id="error-alert" class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
        <span>⚠️</span> <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="" id="register-form" novalidate class="space-y-5">
        <!-- Nama -->
        <div>
          <label for="nama" class="block text-xs font-bold text-[#333138] uppercase tracking-wider mb-2">Nama Lengkap</label>
          <input type="text" id="nama" name="nama"
            value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
            placeholder="Nama lengkap kamu"
            class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm" required>
          <p id="nama-error" class="text-red-500 text-xs mt-1 hidden"></p>
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-xs font-bold text-[#333138] uppercase tracking-wider mb-2">Email</label>
          <input type="email" id="email" name="email"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            placeholder="kamu@email.com"
            class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm" required>
          <p id="email-error" class="text-red-500 text-xs mt-1 hidden"></p>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-xs font-bold text-[#333138] uppercase tracking-wider mb-2">Password</label>
          <div class="relative">
            <input type="password" id="password" name="password"
              placeholder="Minimal 6 karakter"
              class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm pr-12" required>
            <button type="button" id="toggle-pass"
              class="absolute right-4 top-1/2 -translate-y-1/2 text-[#515052] text-sm hover:text-[#FF312E]">👁</button>
          </div>
          <p id="pass-error" class="text-red-500 text-xs mt-1 hidden"></p>
          <!-- Strength bar -->
          <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div id="strength-bar" class="h-full w-0 rounded-full transition-all duration-300"></div>
          </div>
          <p id="strength-text" class="text-xs text-gray-400 mt-1"></p>
        </div>

        <!-- Confirm Password -->
        <div>
          <label for="confirm_password" class="block text-xs font-bold text-[#333138] uppercase tracking-wider mb-2">Konfirmasi Password</label>
          <input type="password" id="confirm_password" name="confirm_password"
            placeholder="Ulangi password"
            class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm" required>
          <p id="confirm-error" class="text-red-500 text-xs mt-1 hidden"></p>
        </div>

        <button id="submit-btn" type="submit"
          class="btn-primary w-full py-3.5 rounded-lg text-sm mt-2">
          Buat Akun →
        </button>
      </form>
    </div>
  </div>
</main>

<script>
const form     = document.getElementById('register-form');
const namaEl   = document.getElementById('nama');
const emailEl  = document.getElementById('email');
const passEl   = document.getElementById('password');
const confEl   = document.getElementById('confirm_password');
const toggleP  = document.getElementById('toggle-pass');
const strBar   = document.getElementById('strength-bar');
const strText  = document.getElementById('strength-text');

toggleP.addEventListener('click', () => {
  passEl.type = passEl.type === 'password' ? 'text' : 'password';
});

// Password strength indicator
passEl.addEventListener('input', () => {
  const v = passEl.value;
  let score = 0;
  if (v.length >= 6)  score++;
  if (v.length >= 10) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  const levels = [
    { pct: '20%', color: '#EF4444', label: 'Sangat lemah' },
    { pct: '40%', color: '#F97316', label: 'Lemah' },
    { pct: '60%', color: '#EAB308', label: 'Cukup' },
    { pct: '80%', color: '#22C55E', label: 'Kuat' },
    { pct: '100%', color: '#16A34A', label: 'Sangat kuat' },
  ];
  const lvl = levels[Math.min(score - 1, 4)];
  if (lvl) {
    strBar.style.width = lvl.pct;
    strBar.style.background = lvl.color;
    strText.textContent = lvl.label;
    strText.style.color = lvl.color;
  } else {
    strBar.style.width = '0';
    strText.textContent = '';
  }
});

function showErr(id, msg, show) {
  const el = document.getElementById(id);
  el.textContent = msg;
  el.classList.toggle('hidden', !show);
}

form.addEventListener('submit', (e) => {
  let valid = true;

  if (namaEl.value.trim().length < 3) {
    showErr('nama-error', 'Nama minimal 3 karakter.', true);
    valid = false;
  } else showErr('nama-error', '', false);

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value.trim())) {
    showErr('email-error', 'Format email tidak valid.', true);
    valid = false;
  } else showErr('email-error', '', false);

  if (passEl.value.trim().length < 6) {
    showErr('pass-error', 'Password minimal 6 karakter.', true);
    valid = false;
  } else showErr('pass-error', '', false);

  if (passEl.value !== confEl.value) {
    showErr('confirm-error', 'Password tidak cocok.', true);
    valid = false;
  } else showErr('confirm-error', '', false);

  if (!valid) e.preventDefault();
});

const errAlert = document.getElementById('error-alert');
if (errAlert) setTimeout(() => errAlert.style.opacity = '0', 5000);
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
