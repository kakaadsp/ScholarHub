<?php
require_once 'conf.php';

if (!$is_logged_in) {
    header('Location: login.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];

// Success/error flash
$flash_success = $_GET['success'] ?? '';
$flash_already = $_GET['already'] ?? '';

// Hapus pengajuan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $pid = (int)($_POST['pengajuan_id'] ?? 0);
    if ($pid) {
        // Pastikan milik user ini
        $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, status FROM pengajuan WHERE id=$pid AND user_id=$uid LIMIT 1"));
        if ($check && $check['status'] === 'Pending') {
            mysqli_query($conn, "DELETE FROM pengajuan WHERE id=$pid AND user_id=$uid");
            header('Location: riwayat.php?deleted=1');
            exit;
        }
    }
    header('Location: riwayat.php?error=1');
    exit;
}

// Ambil riwayat user
$riwayat = mysqli_fetch_all(mysqli_query($conn, "
    SELECT p.*, b.nama_beasiswa, b.pengelola, b.tipe, b.nilai_beasiswa, b.deadline
    FROM pengajuan p
    JOIN beasiswa b ON p.beasiswa_id = b.id
    WHERE p.user_id = $uid
    ORDER BY p.created_at DESC
"), MYSQLI_ASSOC);

$stats = [
    'Pending'  => 0,
    'Diterima' => 0,
    'Ditolak'  => 0,
];
foreach ($riwayat as $r) $stats[$r['status']] = ($stats[$r['status']] ?? 0) + 1;

$tipe_icons = ['Prestasi'=>'🏆','Reguler'=>'🎓','Leadership'=>'🌟'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Pengajuan — ScholarHub</title>
  <meta name="description" content="Lihat dan kelola semua pengajuan beasiswa kamu di ScholarHub.">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-[#F8FAFC] min-h-screen flex flex-col">

<?php include 'includes/navbar.php'; ?>

<!-- Page Header -->
<section class="bg-gradient-to-br from-[#0A0A0F] to-[#1A1A2E] py-14 relative overflow-hidden">
  <div class="absolute top-0 right-0 w-64 h-64 bg-[#FF312E]/10 rounded-full blur-3xl pointer-events-none"></div>
  <div class="container relative z-10">
    <p class="section-label text-[#FF312E]">Dashboard Saya</p>
    <h1 class="text-3xl font-black text-white mb-1">Riwayat Pengajuan</h1>
    <p class="text-white/50 text-sm">Halo, <strong class="text-white"><?= $user_nama ?></strong> — ini semua pengajuan beasiswamu.</p>
  </div>
</section>

<main class="container py-10 flex-1">

  <!-- Alerts -->
  <?php if ($flash_success): ?>
  <div class="alert alert-success fade-up mb-6" id="flash-msg">✅ Pengajuan berhasil dikirim! Pantau statusnya di sini.</div>
  <?php elseif ($flash_already): ?>
  <div class="alert alert-warning fade-up mb-6" id="flash-msg">⚠️ Kamu sudah mendaftar beasiswa ini sebelumnya.</div>
  <?php elseif (isset($_GET['deleted'])): ?>
  <div class="alert alert-info fade-up mb-6" id="flash-msg">🗑️ Pengajuan berhasil dihapus.</div>
  <?php elseif (isset($_GET['error'])): ?>
  <div class="alert alert-error fade-up mb-6" id="flash-msg">❌ Tidak bisa menghapus pengajuan yang sudah diproses.</div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <?php
    $sCards = [
      ['📋', count($riwayat),        'Total Pengajuan',   'bg-blue-50 text-blue-700 border-blue-200'],
      ['⏳', $stats['Pending'],       'Sedang Diproses',   'bg-yellow-50 text-yellow-700 border-yellow-200'],
      ['✅', $stats['Diterima'],      'Diterima',          'bg-green-50 text-green-700 border-green-200'],
      ['❌', $stats['Ditolak'],       'Ditolak',           'bg-red-50 text-red-700 border-red-200'],
    ];
    foreach ($sCards as [$ic, $val, $lbl, $cls]): ?>
    <div class="card p-5 <?= $cls ?> border flex items-center gap-3">
      <span class="text-2xl"><?= $ic ?></span>
      <div>
        <p class="text-2xl font-black"><?= $val ?></p>
        <p class="text-xs font-medium opacity-70"><?= $lbl ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- CTA -->
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-black text-[#0A0A0F]">Daftar Pengajuan</h2>
    <a href="catalog.php" class="btn-primary text-sm">+ Ajukan Beasiswa Baru</a>
  </div>

  <!-- List -->
  <?php if (empty($riwayat)): ?>
  <div class="card p-16 text-center">
    <div class="text-6xl mb-4">📭</div>
    <h3 class="text-xl font-black text-gray-700 mb-2">Belum ada pengajuan</h3>
    <p class="text-gray-400 text-sm mb-6">Mulai jelajahi beasiswa dan ajukan lamaranmu!</p>
    <a href="catalog.php" class="btn-primary">Jelajahi Beasiswa</a>
  </div>
  <?php else: ?>

  <div class="space-y-4" id="riwayat-list">
    <?php foreach ($riwayat as $i => $p): ?>
    <div class="card p-6 fade-up-<?= ($i%4)+1 ?>" id="riwayat-item-<?= $p['id'] ?>">
      <div class="flex flex-col md:flex-row gap-5 items-start">
        <!-- Icon -->
        <div class="w-12 h-12 rounded-xl tipe-icon-<?= strtolower($p['tipe']) ?> flex items-center justify-center text-2xl flex-shrink-0">
          <?= $tipe_icons[$p['tipe']] ?? '🎓' ?>
        </div>
        <!-- Info -->
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-4 mb-2 flex-wrap">
            <div>
              <h3 class="font-black text-[#0A0A0F] text-base"><?= htmlspecialchars($p['nama_beasiswa']) ?></h3>
              <p class="text-xs text-gray-400">🏛️ <?= htmlspecialchars($p['pengelola']) ?></p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
              <?= badge_tipe($p['tipe']) ?>
              <?= badge_status($p['status']) ?>
            </div>
          </div>

          <!-- Detail row -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3">
            <div class="bg-gray-50 rounded-xl p-2.5">
              <p class="text-xs text-gray-400">Universitas</p>
              <p class="text-xs font-semibold text-gray-700 line-clamp-1"><?= htmlspecialchars($p['universitas']) ?></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-2.5">
              <p class="text-xs text-gray-400">Prodi / Sem</p>
              <p class="text-xs font-semibold text-gray-700"><?= htmlspecialchars($p['prodi']) ?> · Sem <?= $p['semester'] ?></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-2.5">
              <p class="text-xs text-gray-400">IPK</p>
              <p class="text-xs font-black text-[#FF312E]"><?= number_format($p['ipk'], 2) ?></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-2.5">
              <p class="text-xs text-gray-400">Nilai Beasiswa</p>
              <p class="text-xs font-semibold text-green-600"><?= rupiah($p['nilai_beasiswa']) ?>/Sem</p>
            </div>
          </div>

          <!-- Catatan admin -->
          <?php if (!empty($p['catatan_admin'])): ?>
          <div class="mt-3 bg-amber-50 border border-amber-200 rounded-xl p-3">
            <p class="text-xs font-bold text-amber-700 mb-1">Catatan Admin:</p>
            <p class="text-xs text-amber-600"><?= htmlspecialchars($p['catatan_admin']) ?></p>
          </div>
          <?php endif; ?>

          <!-- Footer row -->
          <div class="flex items-center justify-between mt-4 flex-wrap gap-3">
            <p class="text-xs text-gray-400">
              Diajukan: <?= date('d M Y, H:i', strtotime($p['created_at'])) ?>
              · Deadline: <?= date('d M Y', strtotime($p['deadline'])) ?>
            </p>
            <div class="flex gap-2">
              <a href="detail.php?id=<?= $p['beasiswa_id'] ?>" class="btn-ghost text-xs px-3 py-1.5">Lihat Beasiswa</a>
              <?php if ($p['status'] === 'Pending'): ?>
              <button
                type="button"
                class="btn-ghost text-xs px-3 py-1.5 !text-red-500 !border-red-300 hover:!bg-red-50"
                onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nama_beasiswa'])) ?>')"
                id="btn-hapus-<?= $p['id'] ?>">
                🗑️ Hapus
              </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>

<!-- ── DELETE MODAL ─────────────────────────────────────── -->
<div id="delete-modal" class="modal-overlay">
  <div class="modal-box">
    <div class="modal-header">
      <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-2xl mb-3">🗑️</div>
      <h2 class="text-lg font-black text-[#0A0A0F]">Hapus Pengajuan?</h2>
    </div>
    <div class="modal-body">
      <p class="text-sm text-gray-500">
        Kamu akan menghapus pengajuan untuk:
        <strong class="text-[#0A0A0F]" id="delete-name"></strong>.
        Tindakan ini tidak bisa dibatalkan.
      </p>
    </div>
    <div class="modal-footer">
      <button type="button" onclick="closeModal()" class="btn-ghost text-sm">Batal</button>
      <form method="POST" action="" id="delete-form">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="pengajuan_id" id="delete-id">
        <button type="submit" id="btn-confirm-delete" class="btn-primary !bg-red-500 !border-red-500 text-sm">
          Ya, Hapus
        </button>
      </form>
    </div>
  </div>
</div>

<script>
function confirmDelete(id, name) {
  document.getElementById('delete-id').value = id;
  document.getElementById('delete-name').textContent = name;
  document.getElementById('delete-modal').classList.add('active');
}
function closeModal() {
  document.getElementById('delete-modal').classList.remove('active');
}
// Close on overlay click
document.getElementById('delete-modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// Auto dismiss flash
const flash = document.getElementById('flash-msg');
if (flash) setTimeout(() => { flash.style.opacity = '0'; }, 5000);
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
