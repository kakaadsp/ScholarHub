<?php
require_once 'conf.php';
if (!$is_admin) { header('Location: login.php'); exit; }

$admin_nama = htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin');

// Ambil semua user
$users = mysqli_fetch_all(mysqli_query($conn, "
    SELECT u.*,
           COUNT(p.id) AS total_pengajuan,
           SUM(p.status='Diterima') AS total_diterima
    FROM users u
    LEFT JOIN pengajuan p ON u.id = p.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
"), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Pengguna — Admin ScholarHub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-[#F1F5F9]">

<aside class="admin-sidebar">
  <div class="p-6 border-b border-white/10">
    <a href="index.php" class="flex items-center gap-2">
      <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#FF312E] to-[#FF6B35] flex items-center justify-center text-white font-black text-sm">🎓</div>
      <span class="font-black text-lg text-white">Scholar<span class="text-[#FF312E]">Hub</span></span>
    </a>
  </div>
  <nav class="flex-1 py-4">
    <a href="admin.php"          class="sidebar-link"><span>🏠</span> Dashboard</a>
    <a href="admin_beasiswa.php" class="sidebar-link"><span>📚</span> Kelola Beasiswa</a>
    <a href="admin_pengajuan.php" class="sidebar-link"><span>📋</span> Kelola Pengajuan</a>
    <a href="admin_users.php"    class="sidebar-link active"><span>👥</span> Data Pengguna</a>
    <div class="divider mx-4 my-3" style="background:rgba(255,255,255,.08)"></div>
    <a href="index.php" class="sidebar-link" target="_blank"><span>🔗</span> Lihat Website</a>
    <a href="logout.php" class="sidebar-link !text-red-400"><span>🚪</span> Keluar</a>
  </nav>
</aside>

<div class="admin-content">
  <header class="bg-white border-b border-gray-200 px-8 h-16 flex items-center justify-between sticky top-0 z-30">
    <h1 class="text-lg font-black text-[#0A0A0F]">Data Pengguna</h1>
    <span class="text-sm text-gray-500"><?= count($users) ?> pengguna terdaftar</span>
  </header>

  <div class="p-8">
    <!-- Search -->
    <div class="mb-5">
      <input type="text" id="user-search" class="form-input max-w-sm text-sm" placeholder="🔍 Cari nama atau email...">
    </div>

    <div class="card overflow-hidden">
      <div class="table-wrapper" style="border:none;border-radius:0">
        <table id="users-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Universitas</th>
              <th>Prodi</th>
              <th>Total Pengajuan</th>
              <th>Diterima</th>
              <th>Bergabung</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $i => $u): ?>
            <tr class="user-row">
              <td class="text-gray-400 text-xs font-mono"><?= $i+1 ?></td>
              <td>
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#FF312E] to-[#FF6B35] flex items-center justify-center text-white text-xs font-black">
                    <?= mb_substr($u['nama'], 0, 1) ?>
                  </div>
                  <span class="font-semibold text-sm user-name"><?= htmlspecialchars($u['nama']) ?></span>
                </div>
              </td>
              <td class="text-sm text-gray-500 user-email"><?= htmlspecialchars($u['email']) ?></td>
              <td class="text-sm text-gray-500"><?= htmlspecialchars($u['universitas'] ?? '-') ?></td>
              <td class="text-sm text-gray-500"><?= htmlspecialchars($u['prodi'] ?? '-') ?></td>
              <td class="text-center">
                <span class="badge badge-reguler"><?= $u['total_pengajuan'] ?></span>
              </td>
              <td class="text-center">
                <span class="badge badge-diterima"><?= $u['total_diterima'] ?></span>
              </td>
              <td class="text-xs text-gray-400"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
// Live search
const searchInput = document.getElementById('user-search');
searchInput.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.user-row').forEach(row => {
    const name  = row.querySelector('.user-name').textContent.toLowerCase();
    const email = row.querySelector('.user-email').textContent.toLowerCase();
    row.style.display = (!q || name.includes(q) || email.includes(q)) ? '' : 'none';
  });
});
</script>
</body>
</html>
