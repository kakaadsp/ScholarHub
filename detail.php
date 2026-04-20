<?php
require_once 'conf.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: catalog.php'); exit; }

$stmt = "SELECT * FROM beasiswa WHERE id=$id AND is_active=1 LIMIT 1";
$b    = mysqli_fetch_assoc(mysqli_query($conn, $stmt));
if (!$b) { header('Location: catalog.php'); exit; }

// Kuota
$kq = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM pengajuan WHERE beasiswa_id=$id"));
$diajukan = (int)$kq['n'];
$sisa     = max(0, $b['kuota'] - $diajukan);
$pct      = $b['kuota'] > 0 ? min(100, round($diajukan/$b['kuota']*100)) : 0;

// Cek apakah user sudah apply
$already_applied = false;
if ($is_logged_in) {
    $uid = (int)$_SESSION['user_id'];
    $ca  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM pengajuan WHERE user_id=$uid AND beasiswa_id=$id LIMIT 1"));
    $already_applied = !empty($ca);
}

$tipe_icons = ['Prestasi'=>'🏆','Reguler'=>'🎓','Leadership'=>'🌟'];
$days_left  = (int)((strtotime($b['deadline']) - time()) / 86400);

// Syarat list
$syarat_list = array_filter(array_map('trim', explode('|', $b['syarat'])));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($b['nama_beasiswa']) ?> — ScholarHub</title>
  <meta name="description" content="<?= htmlspecialchars(mb_substr($b['deskripsi'], 0, 150)) ?>">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-[#F8FAFC] min-h-screen flex flex-col">

<?php include 'includes/navbar.php'; ?>

<!-- ── BREADCRUMB ────────────────────────────────────── -->
<div class="bg-white border-b border-gray-100">
  <div class="container py-3 text-xs text-gray-400 flex items-center gap-2">
    <a href="index.php" class="hover:text-[#FF312E] transition-colors">Beranda</a>
    <span>/</span>
    <a href="catalog.php" class="hover:text-[#FF312E] transition-colors">Beasiswa</a>
    <span>/</span>
    <span class="text-gray-700 font-semibold line-clamp-1"><?= htmlspecialchars($b['nama_beasiswa']) ?></span>
  </div>
</div>

<!-- ── HERO DETAIL ───────────────────────────────────── -->
<section class="bg-gradient-to-br from-[#0A0A0F] to-[#1A1A2E] py-16 relative overflow-hidden">
  <div class="absolute top-0 right-0 w-64 h-64 bg-[#FF312E]/15 rounded-full blur-3xl pointer-events-none"></div>
  <div class="container relative z-10">
    <div class="flex flex-col md:flex-row gap-8 items-start">
      <div class="w-20 h-20 rounded-3xl flex-shrink-0 flex items-center justify-center text-4xl
                  <?= $b['tipe']==='Prestasi'?'bg-yellow-100':($b['tipe']==='Leadership'?'bg-purple-100':'bg-blue-100') ?>">
        <?= $tipe_icons[$b['tipe']] ?? '🎓' ?>
      </div>
      <div class="flex-1">
        <?= badge_tipe($b['tipe']) ?>
        <h1 class="text-2xl md:text-3xl font-black text-white mt-2 mb-1">
          <?= htmlspecialchars($b['nama_beasiswa']) ?>
        </h1>
        <p class="text-white/50 text-sm mb-4 flex items-center gap-2">
          🏛️ <?= htmlspecialchars($b['pengelola']) ?>
          <span class="text-white/20">·</span>
          <span class="sdg-badge text-xs">🌱 SDG Goal 4</span>
        </p>

        <!-- Quick stats pills -->
        <div class="flex flex-wrap gap-3">
          <span class="glass-dark px-4 py-2 text-sm text-white rounded-xl font-semibold">
            💰 <?= rupiah($b['nilai_beasiswa']) ?>/Semester
          </span>
          <span class="glass-dark px-4 py-2 text-sm text-white rounded-xl font-semibold">
            👥 <?= $sisa ?> / <?= $b['kuota'] ?> Slot Tersisa
          </span>
          <span class="glass-dark px-4 py-2 text-sm rounded-xl font-semibold <?= $days_left < 0 ? 'text-red-400' : ($days_left <= 14 ? 'text-yellow-400' : 'text-green-400') ?>">
            ⏰ <?= $days_left < 0 ? 'Pendaftaran Ditutup' : ($days_left . ' hari tersisa') ?>
          </span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── MAIN CONTENT ──────────────────────────────────── -->
<main class="container py-12">
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- LEFT: detail -->
    <div class="lg:col-span-2 space-y-6">

      <!-- Deskripsi -->
      <div class="card p-7">
        <h2 class="text-lg font-black text-[#0A0A0F] mb-4 flex items-center gap-2">
          📋 Deskripsi Program
        </h2>
        <p class="text-gray-600 leading-relaxed"><?= nl2br(htmlspecialchars($b['deskripsi'])) ?></p>
      </div>

      <!-- Syarat -->
      <div class="card p-7">
        <h2 class="text-lg font-black text-[#0A0A0F] mb-4 flex items-center gap-2">
          ✅ Persyaratan
        </h2>
        <ul class="space-y-2.5">
          <?php foreach ($syarat_list as $s): ?>
          <li class="flex items-start gap-3 text-sm text-gray-600">
            <span class="w-5 h-5 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">✓</span>
            <?= htmlspecialchars(trim($s)) ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Kuota Progress -->
      <div class="card p-7">
        <h2 class="text-lg font-black text-[#0A0A0F] mb-4 flex items-center gap-2">
          📊 Status Pendaftaran
        </h2>
        <div class="flex justify-between text-sm mb-2">
          <span class="text-gray-500"><?= $diajukan ?> pendaftar</span>
          <span class="font-bold text-gray-700"><?= $pct ?>% terisi</span>
        </div>
        <div class="progress-track mb-3">
          <div class="progress-fill <?= $pct > 80 ? 'bg-red-500' : ($pct > 50 ? 'bg-yellow-500' : 'bg-green-500') ?>"
               style="width:<?= $pct ?>%"></div>
        </div>
        <div class="flex items-center justify-between text-xs text-gray-400">
          <span><?= $sisa ?> slot tersisa dari <?= $b['kuota'] ?> total</span>
          <?php if ($sisa === 0): ?>
          <span class="text-red-500 font-bold">Kuota Penuh</span>
          <?php elseif ($sisa < 10): ?>
          <span class="text-yellow-500 font-bold">⚠️ Hampir Penuh</span>
          <?php else: ?>
          <span class="text-green-500 font-bold">Masih Tersedia</span>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <!-- RIGHT: sidebar info + CTA -->
    <div class="space-y-5">

      <!-- CTA Card -->
      <div class="card p-6 border-2 border-[#FF312E]/20 bg-gradient-to-br from-red-50 to-white">
        <h3 class="font-black text-[#0A0A0F] text-base mb-4">Daftarkan Dirimu</h3>
        <?php if ($days_left < 0): ?>
          <div class="alert alert-warning text-xs">Pendaftaran untuk beasiswa ini telah ditutup.</div>
        <?php elseif ($sisa === 0): ?>
          <div class="alert alert-error text-xs">Kuota pendaftaran sudah habis.</div>
        <?php elseif ($already_applied): ?>
          <div class="alert alert-success text-xs mb-4">✅ Kamu sudah mengajukan beasiswa ini. Lihat status di Riwayat.</div>
          <a href="riwayat.php" class="btn-dark w-full justify-center">Lihat Riwayat</a>
        <?php elseif ($is_logged_in): ?>
          <a href="apply.php?id=<?= $b['id'] ?>" id="btn-apply-detail"
             class="btn-primary w-full justify-center text-sm py-3.5 mb-3">
            📝 Ajukan Sekarang
          </a>
          <p class="text-xs text-gray-400 text-center">Kamu akan mengisi form pengajuan</p>
        <?php else: ?>
          <a href="login.php?redirect=apply.php?id=<?= $b['id'] ?>" id="btn-login-to-apply"
             class="btn-primary w-full justify-center text-sm py-3.5 mb-3">
            Masuk untuk Mendaftar
          </a>
          <a href="register.php" class="btn-ghost w-full justify-center text-sm">
            Belum punya akun? Daftar
          </a>
        <?php endif; ?>
      </div>

      <!-- Info Card -->
      <div class="card p-6">
        <h3 class="font-black text-[#0A0A0F] text-sm mb-4">Informasi Program</h3>
        <div class="space-y-3">
          <?php $info = [
            ['Pengelola',      '🏛️', htmlspecialchars($b['pengelola'])],
            ['Tipe',           '🏷️', $b['tipe']],
            ['Nilai Beasiswa', '💰', rupiah($b['nilai_beasiswa']) . '/Semester'],
            ['Total Kuota',    '👥', $b['kuota'] . ' orang'],
            ['Deadline',       '📅', date('d F Y', strtotime($b['deadline']))],
          ];
          foreach ($info as [$lbl, $ic, $val]): ?>
          <div class="flex items-start gap-3">
            <span class="text-sm w-5 flex-shrink-0"><?= $ic ?></span>
            <div>
              <p class="text-xs text-gray-400"><?= $lbl ?></p>
              <p class="text-sm font-semibold text-gray-700"><?= $val ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Back -->
      <a href="catalog.php" class="btn-ghost w-full justify-center text-sm">
        ← Kembali ke Katalog
      </a>
    </div>
  </div>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>
