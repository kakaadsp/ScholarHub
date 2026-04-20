<?php
require_once 'conf.php';

// Harus admin
if (!$is_admin) {
    header('Location: login.php');
    exit;
}

$admin_nama = htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin');

// Stats
$stats = [
    'beasiswa'         => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM beasiswa"))['c'],
    'users'            => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users"))['c'],
    'pengajuan'        => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM pengajuan"))['c'],
    'pending'          => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM pengajuan WHERE status='Pending'"))['c'],
    'diterima'         => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM pengajuan WHERE status='Diterima'"))['c'],
    'ditolak'          => mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM pengajuan WHERE status='Ditolak'"))['c'],
];

// Latest pengajuan
$latest = mysqli_fetch_all(mysqli_query($conn, "
    SELECT p.*, u.nama AS nama_user, b.nama_beasiswa, b.tipe
    FROM pengajuan p
    JOIN users u ON p.user_id = u.id
    JOIN beasiswa b ON p.beasiswa_id = b.id
    ORDER BY p.created_at DESC
    LIMIT 8
"), MYSQLI_ASSOC);

$page = 'admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — ScholarHub</title>
  <meta name="description" content="Panel administrasi ScholarHub untuk mengelola beasiswa dan pengajuan.">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
  </style>
</head>
<body class="bg-[#F1F5F9]">

<!-- ── SIDEBAR ───────────────────────────────────────── -->
<aside class="admin-sidebar">
  <!-- Brand -->
  <div class="p-6 border-b border-white/10">
    <a href="index.php" class="flex items-center gap-2">
      <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#FF312E] to-[#FF6B35] flex items-center justify-center text-white font-black text-sm">🎓</div>
      <span class="font-black text-lg text-white">Scholar<span class="text-[#FF312E]">Hub</span></span>
    </a>
    <div class="mt-4 flex items-center gap-3">
      <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white font-black text-sm">
        <?= mb_substr($admin_nama, 0, 1) ?>
      </div>
      <div>
        <p class="text-white text-xs font-bold"><?= $admin_nama ?></p>
        <p class="text-white/40 text-xs">Administrator</p>
      </div>
    </div>
  </div>

  <!-- Nav -->
  <nav class="flex-1 py-4">
    <p class="px-6 text-xs font-bold text-white/20 uppercase tracking-wider mb-2">Menu Utama</p>
    <a href="admin.php"          class="sidebar-link active">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      Dashboard
    </a>
    <a href="admin_beasiswa.php" class="sidebar-link">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
      Kelola Beasiswa
    </a>
    <a href="admin_pengajuan.php" class="sidebar-link">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
      Kelola Pengajuan
      <?php if ($stats['pending'] > 0): ?>
      <span class="ml-auto bg-[#FF312E] text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $stats['pending'] ?></span>
      <?php endif; ?>
    </a>
    <a href="admin_users.php" class="sidebar-link">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      Data Pengguna
    </a>
    <div class="divider mx-4 my-3" style="background:rgba(255,255,255,.08)"></div>
    <a href="index.php" class="sidebar-link" target="_blank">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
      Lihat Website
    </a>
    <a href="logout.php" class="sidebar-link !text-red-400 hover:!text-red-300">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      Keluar
    </a>
  </nav>
</aside>

<!-- ── MAIN CONTENT ──────────────────────────────────── -->
<div class="admin-content">
  <!-- Top bar -->
  <header class="bg-white border-b border-gray-200 px-8 h-16 flex items-center justify-between sticky top-0 z-30">
    <div class="flex items-center gap-4">
      <button id="sidebar-toggle" class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
      <div>
        <h1 class="text-lg font-black text-[#0A0A0F]">Dashboard</h1>
        <p class="text-xs text-gray-400">Selamat datang, <?= $admin_nama ?></p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <span class="sdg-badge text-xs">🌱 SDG Goal 4</span>
      <span class="text-xs text-amber-600 font-bold bg-amber-50 border border-amber-200 px-3 py-1 rounded-full">⚙️ Admin</span>
    </div>
  </header>

  <div class="p-8">
    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
      <?php
      $statCards = [
        ['🎓', $stats['beasiswa'],  'Total Beasiswa',    'bg-blue-500'],
        ['👥', $stats['users'],     'Total Pengguna',    'bg-indigo-500'],
        ['📋', $stats['pengajuan'], 'Total Pengajuan',   'bg-gray-700'],
        ['⏳', $stats['pending'],   'Pending',           'bg-yellow-500'],
        ['✅', $stats['diterima'],  'Diterima',          'bg-green-500'],
        ['❌', $stats['ditolak'],   'Ditolak',           'bg-red-500'],
      ];
      foreach ($statCards as [$ic, $val, $lbl, $color]): ?>
      <div class="stat-card">
        <div class="stat-icon <?= $color ?>/10">
          <span class="text-xl"><?= $ic ?></span>
        </div>
        <div>
          <p class="text-2xl font-black text-[#0A0A0F]"><?= $val ?></p>
          <p class="text-xs text-gray-400 font-medium"><?= $lbl ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Quick actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <a href="admin_beasiswa.php?action=add" class="card p-5 flex items-center gap-4 hover:border-[#FF312E]/30 group">
        <div class="w-12 h-12 rounded-xl bg-[#FF312E]/10 flex items-center justify-center text-2xl group-hover:bg-[#FF312E] group-hover:text-white transition-colors">➕</div>
        <div>
          <p class="font-bold text-[#0A0A0F] text-sm">Tambah Beasiswa</p>
          <p class="text-xs text-gray-400">Buat program baru</p>
        </div>
      </a>
      <a href="admin_pengajuan.php?status=Pending" class="card p-5 flex items-center gap-4 hover:border-yellow-400/30 group">
        <div class="w-12 h-12 rounded-xl bg-yellow-100 flex items-center justify-center text-2xl group-hover:bg-yellow-400 transition-colors">⏳</div>
        <div>
          <p class="font-bold text-[#0A0A0F] text-sm">Review Pengajuan</p>
          <p class="text-xs text-gray-400"><?= $stats['pending'] ?> menunggu keputusan</p>
        </div>
      </a>
      <a href="admin_users.php" class="card p-5 flex items-center gap-4 hover:border-blue-400/30 group">
        <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-2xl group-hover:bg-blue-400 transition-colors">👥</div>
        <div>
          <p class="font-bold text-[#0A0A0F] text-sm">Lihat Pengguna</p>
          <p class="text-xs text-gray-400"><?= $stats['users'] ?> pengguna terdaftar</p>
        </div>
      </a>
    </div>

    <!-- Recent pengajuan -->
    <div class="card overflow-hidden">
      <div class="p-6 flex items-center justify-between border-b border-gray-100">
        <h2 class="font-black text-[#0A0A0F]">Pengajuan Terbaru</h2>
        <a href="admin_pengajuan.php" class="btn-ghost text-xs">Lihat Semua →</a>
      </div>
      <div class="table-wrapper" style="border:none;border-radius:0">
        <table>
          <thead>
            <tr>
              <th>Pemohon</th>
              <th>Beasiswa</th>
              <th>Kategori</th>
              <th>IPK</th>
              <th>Status</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($latest as $p): ?>
            <tr>
              <td class="font-semibold text-sm"><?= htmlspecialchars($p['nama_user']) ?></td>
              <td class="text-sm max-w-[200px]"><span class="line-clamp-1"><?= htmlspecialchars($p['nama_beasiswa']) ?></span></td>
              <td><?= badge_tipe($p['tipe']) ?></td>
              <td class="font-bold text-[#FF312E] text-sm"><?= number_format($p['ipk'], 2) ?></td>
              <td><?= badge_status($p['status']) ?></td>
              <td class="text-xs text-gray-400"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
              <td>
                <a href="admin_pengajuan.php?edit=<?= $p['id'] ?>"
                   class="text-[#FF312E] text-xs font-bold hover:underline">Review</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
// Sidebar toggle for mobile
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebar = document.querySelector('.admin-sidebar');
sidebarToggle && sidebarToggle.addEventListener('click', () => {
  sidebar.classList.toggle('open');
});
</script>
</body>
</html>
