<?php
require_once 'conf.php';

// Harus login
if (!$is_logged_in) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$uid = (int)$_SESSION['user_id'];
$bid = (int)($_GET['id'] ?? 0);
if (!$bid) { header('Location: catalog.php'); exit; }

// Ambil beasiswa
$b = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM beasiswa WHERE id=$bid AND is_active=1 LIMIT 1"));
if (!$b) { header('Location: catalog.php'); exit; }

// Cek sudah apply
$ca = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM pengajuan WHERE user_id=$uid AND beasiswa_id=$bid LIMIT 1"));
if ($ca) {
    header('Location: riwayat.php?already=1');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Server-side validation
    $universitas = trim($_POST['universitas'] ?? '');
    $prodi       = trim($_POST['prodi']       ?? '');
    $semester    = (int)($_POST['semester']   ?? 0);
    $ipk         = (float)str_replace(',', '.', $_POST['ipk'] ?? '');
    $ukt         = (int)($_POST['ukt']        ?? 0);
    $motivasi    = trim($_POST['motivasi']    ?? '');

    if (empty($universitas) || empty($prodi) || $semester < 1 || $ipk <= 0 || empty($motivasi)) {
        $error = 'Semua field wajib diisi dengan benar.';
    } elseif ($semester < 1 || $semester > 14) {
        $error = 'Semester harus antara 1 s.d. 14.';
    } elseif ($ipk < 1.0 || $ipk > 4.0) {
        $error = 'IPK harus antara 1.00 – 4.00.';
    } elseif (strlen($motivasi) < 50) {
        $error = 'Motivasi minimal 50 karakter.';
    } else {
        $un  = mysqli_real_escape_string($conn, $universitas);
        $pr  = mysqli_real_escape_string($conn, $prodi);
        $mot = mysqli_real_escape_string($conn, $motivasi);
        $q   = "INSERT INTO pengajuan (user_id, beasiswa_id, universitas, prodi, semester, ipk, ukt, motivasi)
                VALUES ($uid, $bid, '$un', '$pr', $semester, $ipk, $ukt, '$mot')";
        if (mysqli_query($conn, $q)) {
            header('Location: riwayat.php?success=1');
            exit;
        } else {
            $error = 'Terjadi kesalahan. Coba lagi.';
        }
    }
}

// Ambil profil user untuk pre-fill
$user_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$uid LIMIT 1"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ajukan Beasiswa — <?= htmlspecialchars($b['nama_beasiswa']) ?></title>
  <meta name="description" content="Form pendaftaran beasiswa <?= htmlspecialchars($b['nama_beasiswa']) ?> di ScholarHub.">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-[#F8FAFC] min-h-screen flex flex-col">

<?php include 'includes/navbar.php'; ?>

<!-- Breadcrumb -->
<div class="bg-white border-b border-gray-100">
  <div class="container py-3 text-xs text-gray-400 flex items-center gap-2">
    <a href="index.php" class="hover:text-[#FF312E]">Beranda</a>
    <span>/</span>
    <a href="catalog.php" class="hover:text-[#FF312E]">Beasiswa</a>
    <span>/</span>
    <a href="detail.php?id=<?= $bid ?>" class="hover:text-[#FF312E] line-clamp-1"><?= htmlspecialchars($b['nama_beasiswa']) ?></a>
    <span>/</span>
    <span class="text-gray-700 font-semibold">Ajukan</span>
  </div>
</div>

<main class="container py-12 flex-1">
  <div class="max-w-3xl mx-auto">

    <!-- Header card -->
    <div class="card p-6 mb-6 bg-gradient-to-r from-[#0A0A0F] to-[#1A1A2E]">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl <?= $b['tipe']==='Prestasi'?'bg-yellow-100':($b['tipe']==='Leadership'?'bg-purple-100':'bg-blue-100') ?> flex items-center justify-center text-3xl flex-shrink-0">
          <?= $b['tipe']==='Prestasi'?'🏆':($b['tipe']==='Leadership'?'🌟':'🎓') ?>
        </div>
        <div>
          <p class="text-white/50 text-xs mb-0.5">Mengajukan untuk</p>
          <h1 class="text-white font-black text-lg"><?= htmlspecialchars($b['nama_beasiswa']) ?></h1>
          <p class="text-white/50 text-xs">🏛️ <?= htmlspecialchars($b['pengelola']) ?></p>
        </div>
        <div class="ml-auto text-right hidden md:block">
          <?= badge_tipe($b['tipe']) ?>
          <p class="text-[#FF312E] font-black text-sm mt-1"><?= rupiah($b['nilai_beasiswa']) ?>/Sem</p>
        </div>
      </div>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error mb-6 fade-up" id="error-alert">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- FORM -->
    <form method="POST" action="" id="apply-form" novalidate>
      <div class="card p-8 space-y-6">
        <div>
          <h2 class="text-lg font-black text-[#0A0A0F] mb-1">Data Akademik</h2>
          <p class="text-sm text-gray-400">Pastikan data sesuai dengan kondisi aktual kamu</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <!-- Universitas -->
          <div class="form-group md:col-span-2">
            <label for="universitas" class="form-label">Nama Universitas *</label>
            <input type="text" id="universitas" name="universitas" class="form-input"
                   value="<?= htmlspecialchars($_POST['universitas'] ?? $user_data['universitas'] ?? '') ?>"
                   placeholder="Contoh: Universitas Brawijaya" required>
            <span class="form-error" id="err-univ">Universitas wajib diisi.</span>
          </div>

          <!-- Prodi -->
          <div class="form-group">
            <label for="prodi" class="form-label">Program Studi *</label>
            <input type="text" id="prodi" name="prodi" class="form-input"
                   value="<?= htmlspecialchars($_POST['prodi'] ?? $user_data['prodi'] ?? '') ?>"
                   placeholder="Contoh: Sistem Informasi" required>
            <span class="form-error" id="err-prodi">Program studi wajib diisi.</span>
          </div>

          <!-- Semester -->
          <div class="form-group">
            <label for="semester" class="form-label">Semester Saat Ini *</label>
            <select id="semester" name="semester" class="form-input" required>
              <option value="">Pilih semester</option>
              <?php for ($s=1;$s<=14;$s++): ?>
              <option value="<?= $s ?>" <?= (($_POST['semester']??'') == $s)?'selected':'' ?>>
                Semester <?= $s ?>
              </option>
              <?php endfor; ?>
            </select>
            <span class="form-error" id="err-semester">Pilih semester terlebih dahulu.</span>
          </div>

          <!-- IPK -->
          <div class="form-group">
            <label for="ipk" class="form-label">IPK Terakhir *</label>
            <input type="number" id="ipk" name="ipk" class="form-input"
                   value="<?= htmlspecialchars($_POST['ipk'] ?? '') ?>"
                   placeholder="Contoh: 3.75" step="0.01" min="1.00" max="4.00" required>
            <span class="form-error" id="err-ipk">IPK harus antara 1.00 – 4.00.</span>
          </div>

          <!-- UKT -->
          <div class="form-group">
            <label for="ukt" class="form-label">UKT per Semester (Rp)</label>
            <input type="number" id="ukt" name="ukt" class="form-input"
                   value="<?= htmlspecialchars($_POST['ukt'] ?? '') ?>"
                   placeholder="Contoh: 3500000" min="0">
            <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak relevan</p>
          </div>
        </div>

        <div class="divider"></div>

        <!-- Motivasi -->
        <div class="form-group">
          <label for="motivasi" class="form-label">Surat Motivasi *</label>
          <textarea id="motivasi" name="motivasi" class="form-input" rows="6"
                    placeholder="Jelaskan mengapa kamu layak menerima beasiswa ini. Ceritakan latar belakang, prestasi, dan rencana ke depanmu. (minimal 50 karakter)"
                    required><?= htmlspecialchars($_POST['motivasi'] ?? '') ?></textarea>
          <div class="flex justify-between mt-1">
            <span class="form-error" id="err-motivasi">Surat motivasi minimal 50 karakter.</span>
            <span class="text-xs text-gray-400" id="char-count">0 karakter</span>
          </div>
        </div>

        <!-- Submit -->
        <div class="flex gap-3 flex-wrap">
          <button type="submit" id="btn-submit-apply"
                  class="btn-primary flex-1 py-3.5 rounded-xl text-base">
            📝 Kirim Pengajuan
          </button>
          <a href="detail.php?id=<?= $bid ?>" class="btn-ghost flex-1 py-3.5 justify-center rounded-xl">
            Batal
          </a>
        </div>

        <p class="text-xs text-gray-400 text-center">
          Dengan mengklik tombol di atas, kamu menyatakan bahwa semua data yang diisikan adalah benar dan dapat dipertanggungjawabkan.
        </p>
      </div>
    </form>
  </div>
</main>

<script>
// ── Client-side validation ──────────────────────────────
const form = document.getElementById('apply-form');

// Char counter for motivasi
const motivasiEl = document.getElementById('motivasi');
const charCount   = document.getElementById('char-count');
motivasiEl.addEventListener('input', () => {
  const n = motivasiEl.value.length;
  charCount.textContent = n + ' karakter';
  charCount.style.color = n >= 50 ? '#22C55E' : '#EF4444';
});

function showErr(id, show) {
  const el = document.getElementById(id);
  el.classList.toggle('visible', show);
}

form.addEventListener('submit', e => {
  let valid = true;

  const univ = document.getElementById('universitas').value.trim();
  if (!univ) { showErr('err-univ', true); valid = false; } else showErr('err-univ', false);

  const prodi = document.getElementById('prodi').value.trim();
  if (!prodi) { showErr('err-prodi', true); valid = false; } else showErr('err-prodi', false);

  const semester = document.getElementById('semester').value;
  if (!semester) { showErr('err-semester', true); valid = false; } else showErr('err-semester', false);

  const ipk = parseFloat(document.getElementById('ipk').value.replace(',','.'));
  if (isNaN(ipk) || ipk < 1 || ipk > 4) { showErr('err-ipk', true); valid = false; } else showErr('err-ipk', false);

  const mot = motivasiEl.value.trim();
  if (mot.length < 50) { showErr('err-motivasi', true); valid = false; } else showErr('err-motivasi', false);

  if (!valid) {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } else {
    document.getElementById('btn-submit-apply').textContent = '⏳ Mengirim...';
    document.getElementById('btn-submit-apply').disabled = true;
  }
});

// Dismiss error
const errAlert = document.getElementById('error-alert');
if (errAlert) setTimeout(() => errAlert.style.opacity = '0', 5000);
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
