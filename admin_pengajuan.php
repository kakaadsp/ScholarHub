<?php
require_once 'conf.php';
if (!$is_admin) { header('Location: login.php'); exit; }

$admin_nama = htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin');
$status_filter = $_GET['status'] ?? '';
$edit_id = (int)($_GET['edit'] ?? 0);
$error = $success = '';

// ── UPDATE STATUS ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action'])) {
    $pid     = (int)$_POST['id'];
    $action  = $_POST['_action'];

    if ($action === 'update_status') {
        $new_status = in_array($_POST['status'], ['Pending','Diterima','Ditolak']) ? $_POST['status'] : 'Pending';
        $catatan    = mysqli_real_escape_string($conn, trim($_POST['catatan_admin'] ?? ''));
        $ns         = mysqli_real_escape_string($conn, $new_status);
        mysqli_query($conn, "UPDATE pengajuan SET status='$ns', catatan_admin='$catatan' WHERE id=$pid");
        $success = "Status pengajuan berhasil diperbarui menjadi $new_status.";
    } elseif ($action === 'delete') {
        mysqli_query($conn, "DELETE FROM pengajuan WHERE id=$pid");
        $success = 'Pengajuan berhasil dihapus.';
    }
}

// Fetch pengajuan
$where = '1=1';
$valid_status = ['Pending','Diterima','Ditolak'];
if (in_array($status_filter, $valid_status)) {
    $sf = mysqli_real_escape_string($conn, $status_filter);
    $where .= " AND p.status='$sf'";
}
$pengajuan_list = mysqli_fetch_all(mysqli_query($conn, "
    SELECT p.*, u.nama AS nama_user, u.email AS email_user,
           b.nama_beasiswa, b.tipe, b.pengelola, b.nilai_beasiswa
    FROM pengajuan p
    JOIN users u ON p.user_id = u.id
    JOIN beasiswa b ON p.beasiswa_id = b.id
    WHERE $where
    ORDER BY FIELD(p.status,'Pending','Diterima','Ditolak'), p.created_at DESC
"), MYSQLI_ASSOC);

// Fetch for edit
$edit_data = null;
if ($edit_id) {
    $edit_data = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT p.*, u.nama AS nama_user, u.email, b.nama_beasiswa, b.tipe
        FROM pengajuan p JOIN users u ON p.user_id=u.id JOIN beasiswa b ON p.beasiswa_id=b.id
        WHERE p.id=$edit_id LIMIT 1
    "));
}

// Counts
$counts = [];
foreach ($valid_status as $s) {
    $c = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM pengajuan WHERE status='$s'"));
    $counts[$s] = $c['n'];
}
$counts['Total'] = array_sum($counts);

$tipe_icons = ['Prestasi'=>'🏆','Reguler'=>'🎓','Leadership'=>'🌟'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pengajuan — Admin ScholarHub</title>
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
    <a href="admin_pengajuan.php" class="sidebar-link active"><span>📋</span> Kelola Pengajuan
      <?php if ($counts['Pending'] > 0): ?>
      <span class="ml-auto bg-[#FF312E] text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $counts['Pending'] ?></span>
      <?php endif; ?>
    </a>
    <a href="admin_users.php"    class="sidebar-link"><span>👥</span> Data Pengguna</a>
    <div class="divider mx-4 my-3" style="background:rgba(255,255,255,.08)"></div>
    <a href="index.php" class="sidebar-link" target="_blank"><span>🔗</span> Lihat Website</a>
    <a href="logout.php" class="sidebar-link !text-red-400"><span>🚪</span> Keluar</a>
  </nav>
</aside>

<div class="admin-content">
  <header class="bg-white border-b border-gray-200 px-8 h-16 flex items-center justify-between sticky top-0 z-30">
    <h1 class="text-lg font-black text-[#0A0A0F]">Kelola Pengajuan</h1>
    <div class="flex items-center gap-2 text-sm text-gray-500">
      <span class="badge badge-pending">⏳ <?= $counts['Pending'] ?> Pending</span>
    </div>
  </header>

  <div class="p-8">
    <?php if ($success): ?>
    <div class="alert alert-success mb-6"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
    <div class="alert alert-error mb-6"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- EDIT / REVIEW PANEL -->
    <?php if ($edit_data): ?>
    <div class="card p-8 mb-8 border-2 border-yellow-300">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center text-xl">⚙️</div>
        <div>
          <h2 class="text-lg font-black text-[#0A0A0F]">Review Pengajuan #<?= $edit_data['id'] ?></h2>
          <p class="text-sm text-gray-500"><?= htmlspecialchars($edit_data['nama_user']) ?> → <?= htmlspecialchars($edit_data['nama_beasiswa']) ?></p>
        </div>
      </div>

      <!-- Applicant details -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <?php $details = [
          ['Universitas', $edit_data['universitas']],
          ['Prodi', $edit_data['prodi']],
          ['Semester', 'Semester '.$edit_data['semester']],
          ['IPK', number_format($edit_data['ipk'],2)],
          ['UKT', rupiah($edit_data['ukt'])],
          ['Kategori', $edit_data['tipe']],
          ['Email', $edit_data['email']],
          ['Status Saat Ini', $edit_data['status']],
        ];
        foreach ($details as [$lbl, $val]): ?>
        <div class="bg-gray-50 rounded-xl p-3">
          <p class="text-xs text-gray-400"><?= $lbl ?></p>
          <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($val) ?></p>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Motivasi -->
      <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6">
        <p class="text-xs font-bold text-blue-600 mb-2">📝 Surat Motivasi</p>
        <p class="text-sm text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($edit_data['motivasi'])) ?></p>
      </div>

      <!-- Update form -->
      <form method="POST" action="">
        <input type="hidden" name="_action" value="update_status">
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="form-group">
            <label class="form-label">Status Baru *</label>
            <select name="status" class="form-input" required>
              <?php foreach (['Pending','Diterima','Ditolak'] as $s): ?>
              <option value="<?= $s ?>" <?= $edit_data['status']===$s?'selected':'' ?>>
                <?= $s==='Pending'?'⏳':($s==='Diterima'?'✅':'❌') ?> <?= $s ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Catatan Admin (opsional)</label>
            <input type="text" name="catatan_admin" class="form-input"
                   value="<?= htmlspecialchars($edit_data['catatan_admin'] ?? '') ?>"
                   placeholder="Contoh: Selamat! Mohon siapkan berkas asli...">
          </div>
        </div>
        <div class="flex gap-3 mt-5">
          <button type="submit" class="btn-primary">💾 Simpan Keputusan</button>
          <a href="admin_pengajuan.php" class="btn-ghost">Batal</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- STATUS FILTER TABS -->
    <div class="flex gap-2 mb-5 flex-wrap">
      <a href="admin_pengajuan.php" class="filter-chip text-xs <?= !$status_filter ? 'active' : '' ?>">
        Semua (<?= $counts['Total'] ?>)
      </a>
      <?php foreach ($valid_status as $s): ?>
      <a href="admin_pengajuan.php?status=<?= $s ?>" class="filter-chip text-xs <?= $status_filter===$s ? 'active' : '' ?>">
        <?= $s==='Pending'?'⏳':($s==='Diterima'?'✅':'❌') ?> <?= $s ?> (<?= $counts[$s] ?>)
      </a>
      <?php endforeach; ?>
    </div>

    <!-- TABLE -->
    <div class="card overflow-hidden">
      <div class="table-wrapper" style="border:none;border-radius:0">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Pemohon</th>
              <th>Email</th>
              <th>Beasiswa</th>
              <th>Kategori</th>
              <th>Univ / Prodi</th>
              <th>IPK</th>
              <th>Status</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($pengajuan_list)): ?>
            <tr><td colspan="10" class="text-center py-8 text-gray-400">Tidak ada pengajuan.</td></tr>
            <?php else: ?>
            <?php foreach ($pengajuan_list as $i => $p): ?>
            <tr <?= $p['status']==='Pending' ? 'class="bg-yellow-50/30"' : '' ?>>
              <td class="text-gray-400 text-xs font-mono"><?= $i+1 ?></td>
              <td class="font-semibold text-sm"><?= htmlspecialchars($p['nama_user']) ?></td>
              <td class="text-xs text-gray-400"><?= htmlspecialchars($p['email_user']) ?></td>
              <td class="text-sm max-w-[150px]"><span class="line-clamp-1"><?= htmlspecialchars($p['nama_beasiswa']) ?></span></td>
              <td><?= badge_tipe($p['tipe']) ?></td>
              <td class="text-xs text-gray-500">
                <?= htmlspecialchars($p['universitas']) ?><br>
                <span class="text-gray-400"><?= htmlspecialchars($p['prodi']) ?> S<?= $p['semester'] ?></span>
              </td>
              <td class="font-black text-[#FF312E] text-sm"><?= number_format($p['ipk'], 2) ?></td>
              <td><?= badge_status($p['status']) ?></td>
              <td class="text-xs text-gray-400"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
              <td>
                <div class="flex gap-2">
                  <a href="admin_pengajuan.php?edit=<?= $p['id'] ?><?= $status_filter ? '&status='.$status_filter : '' ?>"
                     class="text-blue-600 text-xs font-bold hover:underline">Review</a>
                  <button onclick="confirmDelPengajuan(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nama_user'])) ?>')"
                          class="text-red-500 text-xs font-bold hover:underline">Hapus</button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div id="del-modal" class="modal-overlay">
  <div class="modal-box">
    <div class="modal-header">
      <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-2xl mb-3">🗑️</div>
      <h2 class="text-lg font-black text-[#0A0A0F]">Hapus Pengajuan?</h2>
    </div>
    <div class="modal-body">
      <p class="text-sm text-gray-500">Pengajuan dari <strong id="del-name"></strong> akan dihapus permanen.</p>
    </div>
    <div class="modal-footer">
      <button onclick="document.getElementById('del-modal').classList.remove('active')" class="btn-ghost text-sm">Batal</button>
      <form method="POST" action="">
        <input type="hidden" name="_action" value="delete">
        <input type="hidden" name="id" id="del-id">
        <button type="submit" class="btn-primary !bg-red-500 !border-red-500 text-sm">Hapus</button>
      </form>
    </div>
  </div>
</div>

<script>
function confirmDelPengajuan(id, name) {
  document.getElementById('del-id').value = id;
  document.getElementById('del-name').textContent = name;
  document.getElementById('del-modal').classList.add('active');
}
document.getElementById('del-modal').addEventListener('click', function(e) {
  if (e.target === this) this.classList.remove('active');
});
</script>
</body>
</html>
