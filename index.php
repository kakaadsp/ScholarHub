<?php
require_once 'conf.php';

// Ambil statistik dari DB
$total_beasiswa  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM beasiswa WHERE is_active=1"))['c'];
$total_users     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users"))['c'];
$total_pengajuan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM pengajuan"))['c'];
$total_diterima  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM pengajuan WHERE status='Diterima'"))['c'];

// Ambil 6 beasiswa terbaru
$featured = mysqli_fetch_all(
    mysqli_query($conn, "SELECT * FROM beasiswa WHERE is_active=1 ORDER BY created_at DESC LIMIT 6"),
    MYSQLI_ASSOC
);

// Ambil 5 pengajuan terbaru (untuk preview)
$recent_pengajuan = mysqli_fetch_all(mysqli_query($conn, "
    SELECT p.*, u.nama AS nama_user, b.nama_beasiswa, b.tipe
    FROM pengajuan p
    JOIN users u ON p.user_id = u.id
    JOIN beasiswa b ON p.beasiswa_id = b.id
    ORDER BY p.created_at DESC
    LIMIT 5
"), MYSQLI_ASSOC);

$tipe_icons = ['Prestasi'=>'🏆','Reguler'=>'🎓','Leadership'=>'🌟'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ScholarHub — Portal Beasiswa Indonesia | SDGs Quality Education</title>
  <meta name="description" content="Platform beasiswa digital terpercaya untuk mahasiswa Indonesia. Temukan, pelajari, dan ajukan beasiswa impianmu — Prestasi, Reguler, Leadership.">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-[#F8FAFC] min-h-screen flex flex-col">

<?php include 'includes/navbar.php'; ?>

<!-- ── HERO ─────────────────────────────────────────────── -->
<section class="relative overflow-hidden bg-[#0A0A0F] min-h-[88vh] flex items-center">
  <!-- Background grid pattern -->
  <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px); background-size: 48px 48px;"></div>
  <!-- Glow blobs -->
  <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#FF312E]/20 rounded-full blur-[120px] pointer-events-none"></div>
  <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-purple-600/10 rounded-full blur-[100px] pointer-events-none"></div>

  <div class="container relative z-10 py-24">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
      <!-- Left copy -->
      <div class="fade-up">
        <!-- SDGs Badge -->
        <span class="sdg-badge mb-6 inline-flex">
          🌱 SDGs Goal 4 — Quality Education
        </span>

        <div class="flex items-center gap-3 mb-5">
          <span class="inline-flex items-center gap-2 text-xs font-bold tracking-widest uppercase text-[#FF312E] bg-red-950/50 border border-red-800/40 px-3 py-1.5 rounded-full">
            <span class="pulse-dot w-2 h-2 bg-[#FF312E]"></span>
            <?= $total_beasiswa ?>+ Beasiswa Aktif
          </span>
        </div>

        <h1 class="text-5xl md:text-6xl xl:text-7xl font-black text-white leading-[1.05] mb-6">
          Temukan<br>
          <span class="text-gradient-red">Beasiswamu,</span><br>
          <span class="text-white/70">Wujudkan</span><br>
          <span class="text-white">Mimpimu.</span>
        </h1>

        <p class="text-white/55 text-base md:text-lg leading-relaxed mb-10 max-w-lg">
          Platform beasiswa digital yang menghubungkan mahasiswa Indonesia
          dengan peluang terbaik dari pemerintah, swasta & lembaga internasional.
          <strong class="text-white">Gratis, akurat, terpercaya.</strong>
        </p>

        <div class="flex flex-wrap gap-4 fade-up-1">
          <a href="catalog.php" id="hero-cta-explore"
             class="btn-primary text-sm px-8 py-4 rounded-xl text-base">
            🔍 Jelajahi Beasiswa
          </a>
          <?php if (!$is_logged_in): ?>
          <a href="register.php" id="hero-cta-daftar"
             class="border-2 border-white/20 text-white text-sm font-semibold px-8 py-4 rounded-xl hover:bg-white/10 transition-all text-base">
            Daftar Gratis →
          </a>
          <?php else: ?>
          <a href="riwayat.php"
             class="border-2 border-white/20 text-white text-sm font-semibold px-8 py-4 rounded-xl hover:bg-white/10 transition-all text-base">
            📋 Riwayat Saya
          </a>
          <?php endif; ?>
        </div>

        <!-- Trust bar -->
        <div class="flex items-center gap-6 mt-12 pt-8 border-t border-white/10">
          <?php
          $trust = [
            ['🎓', $total_beasiswa.'+', 'Program'],
            ['👥', $total_users.'+', 'Pengguna'],
            ['📋', $total_pengajuan.'+', 'Pengajuan'],
            ['✅', $total_diterima.'+', 'Diterima'],
          ];
          foreach ($trust as [$ic, $val, $lbl]): ?>
          <div class="text-center">
            <p class="text-white font-black text-xl"><?= $val ?></p>
            <p class="text-white/40 text-xs"><?= $lbl ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Right: Floating beasiswa cards preview -->
      <div class="hidden lg:block relative h-[520px] fade-up-2">
        <!-- Card 1 -->
        <div class="glass-dark absolute top-8 right-0 w-72 p-5 float-card">
          <div class="flex items-center gap-3 mb-3">
            <span class="text-2xl">🏆</span>
            <div>
              <p class="text-white font-bold text-sm">Beasiswa Djarum Plus</p>
              <p class="text-white/50 text-xs">Djarum Foundation</p>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <span class="badge badge-prestasi text-xs">🏆 Prestasi</span>
            <span class="text-[#FF312E] font-black text-sm">Rp 6 Juta/Sem</span>
          </div>
        </div>
        <!-- Card 2 -->
        <div class="glass-dark absolute top-44 right-16 w-72 p-5 float-card-2">
          <div class="flex items-center gap-3 mb-3">
            <span class="text-2xl">🎓</span>
            <div>
              <p class="text-white font-bold text-sm">KIP Kuliah 2026</p>
              <p class="text-white/50 text-xs">Kemdikbudristek</p>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <span class="badge badge-reguler text-xs">🎓 Reguler</span>
            <span class="text-green-400 font-black text-sm">Rp 7,2 Juta/Sem</span>
          </div>
        </div>
        <!-- Card 3 -->
        <div class="glass-dark absolute top-[22rem] right-4 w-72 p-5 float-card">
          <div class="flex items-center gap-3 mb-3">
            <span class="text-2xl">🌟</span>
            <div>
              <p class="text-white font-bold text-sm">Beasiswa Leadership</p>
              <p class="text-white/50 text-xs">Tanoto Foundation</p>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <span class="badge badge-leadership text-xs">🌟 Leadership</span>
            <span class="text-purple-400 font-black text-sm">Rp 12 Juta/Sem</span>
          </div>
        </div>
        <!-- Stat pill floating -->
        <div class="absolute top-4 left-8 bg-green-500 text-white text-xs font-bold px-4 py-2 rounded-full shadow-lg flex items-center gap-2 animate-bounce" style="animation-duration:2.5s">
          <span class="w-2 h-2 rounded-full bg-white inline-block"></span>
          <?= $total_diterima ?> Diterima Bulan Ini
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── KATEGORI FILTER BAR ────────────────────────────── -->
<section class="bg-white border-b border-gray-100 py-6">
  <div class="container">
    <div class="flex items-center gap-4 flex-wrap">
      <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori:</span>
      <div class="flex gap-2 flex-wrap">
        <a href="catalog.php" class="filter-chip">🔎 Semua</a>
        <a href="catalog.php?tipe=Prestasi" class="filter-chip">🏆 Prestasi</a>
        <a href="catalog.php?tipe=Reguler" class="filter-chip">🎓 Reguler</a>
        <a href="catalog.php?tipe=Leadership" class="filter-chip">🌟 Leadership</a>
      </div>
      <div class="ml-auto">
        <a href="catalog.php" class="text-[#FF312E] text-sm font-bold hover:underline">Lihat Semua Beasiswa →</a>
      </div>
    </div>
  </div>
</section>

<!-- ── BEASISWA UNGGULAN ──────────────────────────────── -->
<section class="container py-20">
  <div class="flex items-end justify-between mb-10">
    <div>
      <p class="section-label">Program Pilihan</p>
      <h2 class="text-3xl md:text-4xl font-black text-[#0A0A0F]">
        Beasiswa <span class="text-[#FF312E]">Tersedia</span>
      </h2>
    </div>
    <a href="catalog.php" class="btn-secondary text-sm hidden md:inline-flex">
      Lihat Semua →
    </a>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($featured as $i => $b): ?>
    <article class="card p-6 fade-up-<?= ($i%4)+1 ?>" id="beasiswa-card-<?= $b['id'] ?>">
      <div class="flex items-start justify-between mb-4">
        <div class="w-12 h-12 rounded-xl tipe-icon-<?= strtolower($b['tipe']) ?> flex items-center justify-center text-2xl">
          <?= $tipe_icons[$b['tipe']] ?? '🎓' ?>
        </div>
        <?= badge_tipe($b['tipe']) ?>
      </div>
      <h3 class="font-bold text-[#0A0A0F] text-base mb-1 line-clamp-2">
        <?= htmlspecialchars($b['nama_beasiswa']) ?>
      </h3>
      <p class="text-xs text-[#6B7280] mb-3 flex items-center gap-1">
        🏛️ <?= htmlspecialchars($b['pengelola']) ?>
      </p>
      <p class="text-sm text-gray-500 leading-relaxed line-clamp-2 mb-4">
        <?= htmlspecialchars(mb_substr($b['deskripsi'], 0, 100)) ?>...
      </p>
      <div class="divider mb-4"></div>
      <div class="flex items-center justify-between mb-4">
        <div>
          <p class="text-xs text-gray-400">Nilai Beasiswa</p>
          <p class="font-black text-[#FF312E] text-sm"><?= rupiah($b['nilai_beasiswa']) ?>/Sem</p>
        </div>
        <div class="text-right">
          <p class="text-xs text-gray-400">Kuota</p>
          <p class="font-bold text-gray-700 text-sm"><?= $b['kuota'] ?> Orang</p>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <span class="text-xs text-gray-400 flex items-center gap-1">
          ⏰ <?= date('d M Y', strtotime($b['deadline'])) ?>
        </span>
        <a href="detail.php?id=<?= $b['id'] ?>" id="btn-detail-<?= $b['id'] ?>"
           class="btn-primary text-xs px-4 py-2 rounded-lg">Detail →</a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

  <div class="text-center mt-10 md:hidden">
    <a href="catalog.php" class="btn-secondary">Lihat Semua Beasiswa</a>
  </div>
</section>

<!-- ── CARA KERJA ─────────────────────────────────────── -->
<section class="bg-[#0A0A0F] py-24 relative overflow-hidden">
  <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, rgba(255,49,46,.5) 1px, transparent 1px); background-size: 32px 32px;"></div>
  <div class="container relative z-10">
    <div class="text-center mb-16">
      <p class="section-label text-[#FF312E]">Proses Mudah</p>
      <h2 class="text-3xl md:text-4xl font-black text-white">
        Cara <span class="text-[#FF312E]">Menggunakan</span> ScholarHub
      </h2>
      <p class="text-white/40 text-sm mt-3 max-w-lg mx-auto">Empat langkah mudah menuju beasiswa impianmu</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php
      $steps = [
        ['01','👤','Buat Akun',        'Daftar gratis dan lengkapi profil akunmu dalam 2 menit.'],
        ['02','🔍','Temukan Beasiswa', 'Jelajahi daftar beasiswa dan filter berdasarkan kategori.'],
        ['03','📝','Ajukan Lamaran',   'Isi form pengajuan dengan data lengkap dan motivasi terbaik.'],
        ['04','📬','Pantau Status',    'Cek status pengajuanmu kapan saja di halaman Riwayat.'],
      ];
      foreach ($steps as $i => [$no, $icon, $title, $desc]): ?>
      <div class="text-center fade-up-<?= $i+1 ?>">
        <div class="relative mb-6">
          <div class="w-16 h-16 mx-auto rounded-2xl bg-[#FF312E] flex items-center justify-center text-3xl shadow-lg shadow-red-500/30">
            <?= $icon ?>
          </div>
          <div class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-white text-[#0A0A0F] flex items-center justify-center font-black text-xs">
            <?= $no ?>
          </div>
        </div>
        <h3 class="font-bold text-white text-base mb-2"><?= $title ?></h3>
        <p class="text-white/40 text-sm leading-relaxed"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── PENGAJUAN TERBARU ──────────────────────────────── -->
<?php if (!empty($recent_pengajuan)): ?>
<section class="container py-20">
  <div class="flex items-end justify-between mb-8">
    <div>
      <p class="section-label">Aktivitas Real-time</p>
      <h2 class="text-3xl font-black text-[#0A0A0F]">
        Pengajuan <span class="text-[#FF312E]">Terkini</span>
      </h2>
    </div>
    <?php if ($is_logged_in): ?>
    <a href="catalog.php" class="btn-primary text-sm hidden md:inline-flex">Ajukan Sekarang</a>
    <?php else: ?>
    <a href="login.php" class="btn-secondary text-sm hidden md:inline-flex">Masuk untuk Ajukan</a>
    <?php endif; ?>
  </div>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Pemohon</th>
          <th>Program Beasiswa</th>
          <th>Kategori</th>
          <th>Universitas</th>
          <th>IPK</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent_pengajuan as $i => $p): ?>
        <tr>
          <td class="text-gray-400 font-mono text-xs"><?= $i+1 ?></td>
          <td class="font-semibold text-sm"><?= htmlspecialchars($p['nama_user']) ?></td>
          <td class="text-sm"><?= htmlspecialchars($p['nama_beasiswa']) ?></td>
          <td><?= badge_tipe($p['tipe']) ?></td>
          <td class="text-sm text-gray-500"><?= htmlspecialchars($p['universitas']) ?></td>
          <td class="text-sm font-bold text-[#FF312E]"><?= number_format($p['ipk'], 2) ?></td>
          <td><?= badge_status($p['status']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php endif; ?>

<!-- ── KENAPA SCHOLARHUB ──────────────────────────────── -->
<section class="bg-gradient-to-br from-[#FF312E] to-[#CC1A18] py-24 relative overflow-hidden">
  <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(rgba(255,255,255,.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.1) 1px, transparent 1px); background-size: 40px 40px;"></div>
  <div class="container relative z-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-14 items-center">
      <div>
        <p class="section-label text-red-200">Tentang Platform</p>
        <h2 class="text-3xl md:text-4xl font-black text-white mb-6 leading-tight">
          Platform Independen<br>yang Berpihak pada<br>
          <span class="underline decoration-white/40">Mahasiswa Indonesia</span>
        </h2>
        <p class="text-white/75 text-sm leading-relaxed mb-6">
          ScholarHub dibangun dengan satu tujuan: menghapus kesenjangan informasi beasiswa.
          Sepenuhnya <strong class="text-white">gratis</strong>, tanpa biaya tersembunyi.
        </p>
        <a href="<?= $is_logged_in ? 'catalog.php' : 'register.php' ?>" class="btn-dark text-sm px-6 py-3 rounded-xl">
          <?= $is_logged_in ? '🔍 Jelajahi Beasiswa' : '✨ Mulai Sekarang' ?>
        </a>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <?php
        $values = [
          ['🎯','Akurasi',       'Data diverifikasi dari sumber resmi sebelum dipublikasikan.'],
          ['⚖️','Inklusivitas',  'Menjangkau mahasiswa dari semua latar belakang sosial.'],
          ['🔄','Update Rutin',  'Info diperbarui mengikuti siklus pendaftaran tiap program.'],
          ['🤝','Pendampingan', 'Panduan dan dukungan di setiap tahap pendaftaran.'],
        ];
        foreach ($values as [$icon, $title, $desc]): ?>
        <div class="bg-white/10 border border-white/20 rounded-2xl p-5 hover:bg-white/15 transition-colors">
          <div class="text-2xl mb-3"><?= $icon ?></div>
          <h3 class="text-white font-bold text-sm mb-2"><?= $title ?></h3>
          <p class="text-white/60 text-xs leading-relaxed"><?= $desc ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA BOTTOM ─────────────────────────────────────── -->
<?php if (!$is_logged_in): ?>
<section class="bg-white py-20">
  <div class="container text-center">
    <span class="sdg-badge mb-6 inline-flex">🌱 Mendukung SDG Goal 4</span>
    <h2 class="text-3xl md:text-4xl font-black text-[#0A0A0F] mb-4">
      Siap Menemukan <span class="text-[#FF312E]">Beasiswamu?</span>
    </h2>
    <p class="text-gray-500 text-base mb-8 max-w-md mx-auto">
      Bergabung dengan ribuan mahasiswa yang sudah memanfaatkan ScholarHub.
    </p>
    <div class="flex gap-4 justify-center flex-wrap">
      <a href="register.php" id="cta-daftar-bottom" class="btn-primary text-base px-10 py-4 rounded-xl">
        ✨ Daftar Gratis Sekarang
      </a>
      <a href="catalog.php" class="btn-secondary text-base px-10 py-4 rounded-xl">
        🔍 Lihat Beasiswa
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
</body>
</html>
