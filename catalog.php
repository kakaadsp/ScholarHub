<?php
require_once 'conf.php';

// Filter
$tipe_filter = $_GET['tipe'] ?? '';
$search      = trim($_GET['q'] ?? '');
$valid_tipe  = ['Prestasi','Reguler','Leadership'];

// Build query
$where = ["is_active = 1"];
if (in_array($tipe_filter, $valid_tipe)) {
    $t = mysqli_real_escape_string($conn, $tipe_filter);
    $where[] = "tipe = '$t'";
}
if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where[] = "(nama_beasiswa LIKE '%$s%' OR pengelola LIKE '%$s%' OR deskripsi LIKE '%$s%')";
}
$sql = "SELECT * FROM beasiswa WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$beasiswa_list = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Kuota sisa
$kuota_map = [];
$kq = mysqli_query($conn, "SELECT beasiswa_id, COUNT(*) AS n FROM pengajuan GROUP BY beasiswa_id");
while ($r = mysqli_fetch_assoc($kq)) $kuota_map[$r['beasiswa_id']] = (int)$r['n'];

$tipe_icons  = ['Prestasi'=>'🏆','Reguler'=>'🎓','Leadership'=>'🌟'];
$total_count = count($beasiswa_list);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Katalog Beasiswa — ScholarHub</title>
  <meta name="description" content="Katalog lengkap beasiswa Prestasi, Reguler, dan Leadership untuk mahasiswa Indonesia.">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-[#F8FAFC] min-h-screen flex flex-col">

<?php include 'includes/navbar.php'; ?>

<!-- ── PAGE HEADER ────────────────────────────────────── -->
<section class="bg-gradient-to-br from-[#0A0A0F] to-[#1A1A2E] py-16 relative overflow-hidden">
  <div class="absolute inset-0 opacity-15" style="background-image: radial-gradient(circle, rgba(255,49,46,.4) 1px, transparent 1px); background-size: 28px 28px;"></div>
  <div class="absolute top-0 right-0 w-80 h-80 bg-[#FF312E]/20 rounded-full blur-3xl pointer-events-none"></div>
  <div class="container relative z-10">
    <p class="section-label text-[#FF312E]">Katalog</p>
    <h1 class="text-3xl md:text-4xl font-black text-white mb-3">
      Semua Program <span class="text-[#FF312E]">Beasiswa</span>
    </h1>
    <p class="text-white/50 text-sm max-w-lg">
      Temukan beasiswa yang sesuai dengan profil dan kebutuhanmu dari <strong class="text-white"><?= $total_count ?> program</strong> tersedia.
    </p>
  </div>
</section>

<!-- ── FILTER BAR ─────────────────────────────────────── -->
<div class="bg-white border-b border-gray-100 sticky top-[68px] z-40 shadow-sm">
  <div class="container py-4">
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
      <!-- Search -->
      <form method="GET" action="" class="flex-1 flex gap-2" id="search-form">
        <div class="flex-1 relative">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
          <input
            type="text" name="q" id="search-input"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Cari nama beasiswa atau pengelola..."
            class="form-input pl-9 text-sm py-2.5"
          >
        </div>
        <?php if ($tipe_filter): ?>
        <input type="hidden" name="tipe" value="<?= htmlspecialchars($tipe_filter) ?>">
        <?php endif; ?>
        <button type="submit" class="btn-primary text-sm px-4 py-2.5">Cari</button>
        <?php if ($search || $tipe_filter): ?>
        <a href="catalog.php" class="btn-ghost text-sm px-3">✕</a>
        <?php endif; ?>
      </form>

      <!-- Filter chips -->
      <div class="flex gap-2 flex-wrap">
        <a href="catalog.php<?= $search ? '?q='.urlencode($search) : '' ?>"
           class="filter-chip <?= !$tipe_filter ? 'active' : '' ?>">Semua</a>
        <?php foreach (['Prestasi','Reguler','Leadership'] as $t): ?>
        <a href="catalog.php?tipe=<?= $t ?><?= $search ? '&q='.urlencode($search) : '' ?>"
           class="filter-chip <?= $tipe_filter===$t ? 'active' : '' ?>">
          <?= $tipe_icons[$t] ?> <?= $t ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Result info -->
    <div class="mt-3 flex items-center gap-2 text-xs text-gray-400">
      <span>Menampilkan <strong class="text-gray-700"><?= count($beasiswa_list) ?></strong> program</span>
      <?php if ($tipe_filter): ?>
      <span class="badge badge-<?= strtolower($tipe_filter) ?>">filter: <?= $tipe_filter ?></span>
      <?php endif; ?>
      <?php if ($search): ?>
      <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium">kata kunci: "<?= htmlspecialchars($search) ?>"</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ── BEASISWA LIST ───────────────────────────────────── -->
<main class="container py-10 flex-1">
  <?php if (empty($beasiswa_list)): ?>
  <div class="text-center py-24">
    <div class="text-6xl mb-4">🔍</div>
    <h2 class="text-xl font-bold text-gray-700 mb-2">Tidak ada hasil ditemukan</h2>
    <p class="text-gray-400 text-sm mb-6">Coba ubah kata kunci atau filter kategori</p>
    <a href="catalog.php" class="btn-primary text-sm">Lihat Semua Beasiswa</a>
  </div>
  <?php else: ?>

  <!-- Grid -->
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="beasiswa-grid">
    <?php foreach ($beasiswa_list as $i => $b):
      $diajukan = $kuota_map[$b['id']] ?? 0;
      $sisa = max(0, $b['kuota'] - $diajukan);
      $pct  = $b['kuota'] > 0 ? min(100, round($diajukan/$b['kuota']*100)) : 0;
      $deadline_ts  = strtotime($b['deadline']);
      $days_left    = (int)(($deadline_ts - time()) / 86400);
      $urgent       = $days_left <= 14 && $days_left >= 0;
    ?>
    <article class="card p-6 flex flex-col beasiswa-item fade-up-<?= ($i%4)+1 ?>"
             data-tipe="<?= $b['tipe'] ?>"
             data-nama="<?= strtolower($b['nama_beasiswa']) ?>"
             id="card-beasiswa-<?= $b['id'] ?>">
      <!-- Header -->
      <div class="flex items-start justify-between mb-4 gap-3">
        <div class="w-14 h-14 rounded-2xl tipe-icon-<?= strtolower($b['tipe']) ?> flex items-center justify-center text-3xl flex-shrink-0">
          <?= $tipe_icons[$b['tipe']] ?? '🎓' ?>
        </div>
        <div class="flex flex-col items-end gap-1.5">
          <?= badge_tipe($b['tipe']) ?>
          <?php if ($urgent && $days_left >= 0): ?>
          <span class="badge" style="background:#FEE2E2;color:#991B1B;border:1px solid #FECACA;font-size:.65rem;">
            🔥 <?= $days_left ?> hari lagi
          </span>
          <?php elseif ($days_left < 0): ?>
          <span class="badge" style="background:#F3F4F6;color:#6B7280;border:1px solid #E5E7EB;font-size:.65rem;">
            Tutup
          </span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Title & organizer -->
      <h2 class="font-black text-[#0A0A0F] text-base mb-1 line-clamp-2">
        <?= htmlspecialchars($b['nama_beasiswa']) ?>
      </h2>
      <p class="text-xs text-gray-400 mb-3 flex items-center gap-1">
        🏛️ <?= htmlspecialchars($b['pengelola']) ?>
      </p>
      <p class="text-sm text-gray-500 leading-relaxed line-clamp-3 mb-4 flex-1">
        <?= htmlspecialchars(mb_substr($b['deskripsi'], 0, 140)) ?>...
      </p>

      <!-- Divider -->
      <div class="divider mb-4"></div>

      <!-- Stats row -->
      <div class="grid grid-cols-3 gap-2 mb-4">
        <div class="bg-gray-50 rounded-xl p-2.5 text-center">
          <p class="text-[#FF312E] font-black text-sm"><?= rupiah($b['nilai_beasiswa']) ?></p>
          <p class="text-gray-400 text-xs">/Semester</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-2.5 text-center">
          <p class="font-black text-gray-700 text-sm"><?= $sisa ?> <span class="text-gray-400 font-normal text-xs">/ <?= $b['kuota'] ?></span></p>
          <p class="text-gray-400 text-xs">Sisa Kuota</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-2.5 text-center">
          <p class="font-black text-gray-700 text-sm"><?= date('d M', strtotime($b['deadline'])) ?></p>
          <p class="text-gray-400 text-xs">Deadline</p>
        </div>
      </div>

      <!-- Quota progress -->
      <div class="mb-4">
        <div class="flex justify-between text-xs text-gray-400 mb-1">
          <span>Pendaftar</span>
          <span><?= $pct ?>%</span>
        </div>
        <div class="progress-track">
          <div class="progress-fill <?= $pct > 80 ? 'bg-red-500' : ($pct > 50 ? 'bg-yellow-500' : 'bg-green-500') ?>"
               style="width:<?= $pct ?>%"></div>
        </div>
      </div>

      <!-- CTA -->
      <div class="flex gap-2">
        <a href="detail.php?id=<?= $b['id'] ?>" id="btn-detail-cat-<?= $b['id'] ?>"
           class="btn-ghost flex-1 justify-center text-xs rounded-lg">Detail</a>
        <?php if ($is_logged_in): ?>
        <a href="apply.php?id=<?= $b['id'] ?>" id="btn-apply-<?= $b['id'] ?>"
           class="btn-primary flex-1 justify-center text-xs rounded-lg">Ajukan →</a>
        <?php else: ?>
        <a href="login.php?redirect=apply.php?id=<?= $b['id'] ?>" id="btn-login-apply-<?= $b['id'] ?>"
           class="btn-primary flex-1 justify-center text-xs rounded-lg">Masuk dulu</a>
        <?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>
</main>

<script>
// ── Client-side live filter ─────────────────────────────
const searchInput = document.getElementById('search-input');
const items       = document.querySelectorAll('.beasiswa-item');

searchInput && searchInput.addEventListener('input', function() {
  const q = this.value.toLowerCase().trim();
  items.forEach(item => {
    const nama = item.dataset.nama || '';
    item.style.display = (!q || nama.includes(q)) ? '' : 'none';
  });
});

// Chip JS filter (client side juga)
document.querySelectorAll('.filter-chip').forEach(chip => {
  chip.addEventListener('click', function(e) {
    // Let server-side handle tipe filter via href
  });
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
