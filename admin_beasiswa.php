<?php
require_once 'conf.php';
if (!$is_admin) { header('Location: login.php'); exit; }

$admin_nama = htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin');
$action = $_GET['action'] ?? '';
$edit_id = (int)($_GET['edit'] ?? 0);

$error = $success = '';

// ── TAMBAH ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['_action'] === 'add') {
    $fields = ['nama_beasiswa','pengelola','tipe','deskripsi','syarat','kuota','nilai_beasiswa','deadline'];
    $d = [];
    foreach ($fields as $f) $d[$f] = trim($_POST[$f] ?? '');
    if (empty($d['nama_beasiswa']) || empty($d['pengelola']) || empty($d['tipe']) || empty($d['deadline'])) {
        $error = 'Field wajib tidak boleh kosong.';
    } else {
        $n  = mysqli_real_escape_string($conn, $d['nama_beasiswa']);
        $pg = mysqli_real_escape_string($conn, $d['pengelola']);
        $tp = in_array($d['tipe'],['Prestasi','Reguler','Leadership']) ? $d['tipe'] : 'Reguler';
        $ds = mysqli_real_escape_string($conn, $d['deskripsi']);
        $sy = mysqli_real_escape_string($conn, $d['syarat']);
        $ku = (int)$d['kuota'];
        $nb = (int)$d['nilai_beasiswa'];
        $dl = mysqli_real_escape_string($conn, $d['deadline']);
        $q  = "INSERT INTO beasiswa (nama_beasiswa,pengelola,tipe,deskripsi,syarat,kuota,nilai_beasiswa,deadline)
               VALUES ('$n','$pg','$tp','$ds','$sy',$ku,$nb,'$dl')";
        if (mysqli_query($conn, $q)) { $success = 'Beasiswa berhasil ditambahkan.'; $action = ''; }
        else $error = 'Gagal menyimpan: ' . mysqli_error($conn);
    }
}

// ── EDIT ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['_action'] === 'edit') {
    $eid = (int)$_POST['id'];
    $fields = ['nama_beasiswa','pengelola','tipe','deskripsi','syarat','kuota','nilai_beasiswa','deadline','is_active'];
    $d = [];
    foreach ($fields as $f) $d[$f] = trim($_POST[$f] ?? '');
    if (empty($d['nama_beasiswa'])) {
        $error = 'Nama beasiswa tidak boleh kosong.'; $action = 'edit'; $edit_id = $eid;
    } else {
        $n  = mysqli_real_escape_string($conn, $d['nama_beasiswa']);
        $pg = mysqli_real_escape_string($conn, $d['pengelola']);
        $tp = in_array($d['tipe'],['Prestasi','Reguler','Leadership']) ? $d['tipe'] : 'Reguler';
        $ds = mysqli_real_escape_string($conn, $d['deskripsi']);
        $sy = mysqli_real_escape_string($conn, $d['syarat']);
        $ku = (int)$d['kuota'];
        $nb = (int)$d['nilai_beasiswa'];
        $dl = mysqli_real_escape_string($conn, $d['deadline']);
        $ac = (int)$d['is_active'];
        $q  = "UPDATE beasiswa SET nama_beasiswa='$n',pengelola='$pg',tipe='$tp',deskripsi='$ds',
               syarat='$sy',kuota=$ku,nilai_beasiswa=$nb,deadline='$dl',is_active=$ac WHERE id=$eid";
        if (mysqli_query($conn, $q)) { $success = 'Beasiswa berhasil diperbarui.'; }
        else $error = 'Gagal update: ' . mysqli_error($conn);
    }
}

// ── DELETE ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['_action'] === 'delete') {
    $did = (int)$_POST['id'];
    mysqli_query($conn, "DELETE FROM beasiswa WHERE id=$did");
    $success = 'Beasiswa berhasil dihapus.';
}

// Fetch all beasiswa
$tipe_f = $_GET['tipe'] ?? '';
$valid_tipe = ['Prestasi','Reguler','Leadership'];
$where = '1=1';
if (in_array($tipe_f, $valid_tipe)) {
    $tf = mysqli_real_escape_string($conn, $tipe_f);
    $where .= " AND tipe='$tf'";
}
$beasiswa_list = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM beasiswa WHERE $where ORDER BY created_at DESC"), MYSQLI_ASSOC);

// Fetch for edit
$edit_data = null;
if ($edit_id) {
    $edit_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM beasiswa WHERE id=$edit_id LIMIT 1"));
}

$tipe_icons = ['Prestasi'=>'🏆','Reguler'=>'🎓','Leadership'=>'🌟'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Beasiswa — Admin ScholarHub</title>
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
    <a href="admin_beasiswa.php" class="sidebar-link active"><span>📚</span> Kelola Beasiswa</a>
    <a href="admin_pengajuan.php" class="sidebar-link"><span>📋</span> Kelola Pengajuan</a>
    <a href="admin_users.php"    class="sidebar-link"><span>👥</span> Data Pengguna</a>
    <div class="divider mx-4 my-3" style="background:rgba(255,255,255,.08)"></div>
    <a href="index.php" class="sidebar-link" target="_blank"><span>🔗</span> Lihat Website</a>
    <a href="logout.php" class="sidebar-link !text-red-400"><span>🚪</span> Keluar</a>
  </nav>
</aside>

<div class="admin-content">
  <header class="bg-white border-b border-gray-200 px-8 h-16 flex items-center justify-between sticky top-0 z-30">
    <h1 class="text-lg font-black text-[#0A0A0F]">Kelola Beasiswa</h1>
    <a href="admin_beasiswa.php?action=add" class="btn-primary text-sm">+ Tambah Beasiswa</a>
  </header>

  <div class="p-8">
    <?php if ($success): ?>
    <div class="alert alert-success mb-6"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
    <div class="alert alert-error mb-6"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- FORM TAMBAH / EDIT -->
    <?php if ($action === 'add' || $edit_data): ?>
    <div class="card p-8 mb-8">
      <h2 class="text-lg font-black text-[#0A0A0F] mb-6">
        <?= $edit_data ? '✏️ Edit Beasiswa' : '➕ Tambah Beasiswa Baru' ?>
      </h2>
      <form method="POST" action="" class="space-y-5">
        <input type="hidden" name="_action" value="<?= $edit_data ? 'edit' : 'add' ?>">
        <?php if ($edit_data): ?>
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="form-group">
            <label class="form-label">Nama Beasiswa *</label>
            <input type="text" name="nama_beasiswa" class="form-input"
                   value="<?= htmlspecialchars($edit_data['nama_beasiswa'] ?? '') ?>"
                   placeholder="Contoh: KIP Kuliah 2026" required>
          </div>
          <div class="form-group">
            <label class="form-label">Pengelola / Penyelenggara *</label>
            <input type="text" name="pengelola" class="form-input"
                   value="<?= htmlspecialchars($edit_data['pengelola'] ?? '') ?>"
                   placeholder="Contoh: Kemdikbudristek" required>
          </div>
          <div class="form-group">
            <label class="form-label">Kategori / Tipe *</label>
            <select name="tipe" class="form-input" required>
              <?php foreach (['Prestasi','Reguler','Leadership'] as $t): ?>
              <option value="<?= $t ?>" <?= (($edit_data['tipe']??'') === $t)?'selected':'' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Deadline Pendaftaran *</label>
            <input type="date" name="deadline" class="form-input"
                   value="<?= htmlspecialchars($edit_data['deadline'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Nilai Beasiswa (Rp/Sem)</label>
            <input type="number" name="nilai_beasiswa" class="form-input"
                   value="<?= $edit_data['nilai_beasiswa'] ?? 0 ?>"
                   placeholder="Contoh: 6000000" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">Kuota (orang)</label>
            <input type="number" name="kuota" class="form-input"
                   value="<?= $edit_data['kuota'] ?? 50 ?>"
                   placeholder="Contoh: 50" min="1">
          </div>
          <?php if ($edit_data): ?>
          <div class="form-group">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-input">
              <option value="1" <?= (($edit_data['is_active']??1)==1)?'selected':'' ?>>✅ Aktif</option>
              <option value="0" <?= (($edit_data['is_active']??1)==0)?'selected':'' ?>>⛔ Nonaktif</option>
            </select>
          </div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label">Deskripsi Program</label>
          <textarea name="deskripsi" class="form-input" rows="4"
                    placeholder="Deskripsi lengkap program beasiswa..."><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Persyaratan (pisahkan dengan |)</label>
          <textarea name="syarat" class="form-input" rows="3"
                    placeholder="IPK minimal 3.00 | Mahasiswa aktif | Tidak sedang menerima beasiswa lain"><?= htmlspecialchars($edit_data['syarat'] ?? '') ?></textarea>
          <p class="text-xs text-gray-400 mt-1">Gunakan karakter | (pipe) untuk memisahkan setiap syarat</p>
        </div>

        <div class="flex gap-3">
          <button type="submit" class="btn-primary">
            <?= $edit_data ? '💾 Simpan Perubahan' : '➕ Tambah Beasiswa' ?>
          </button>
          <a href="admin_beasiswa.php" class="btn-ghost">Batal</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- FILTER -->
    <div class="flex items-center gap-3 mb-5 flex-wrap">
      <p class="text-sm font-bold text-gray-500">Filter:</p>
      <a href="admin_beasiswa.php" class="filter-chip <?= !$tipe_f ? 'active' : '' ?> text-xs">Semua</a>
      <?php foreach (['Prestasi','Reguler','Leadership'] as $t): ?>
      <a href="admin_beasiswa.php?tipe=<?= $t ?>" class="filter-chip <?= $tipe_f===$t ? 'active' : '' ?> text-xs"><?= $tipe_icons[$t] ?> <?= $t ?></a>
      <?php endforeach; ?>
    </div>

    <!-- TABLE -->
    <div class="card overflow-hidden">
      <div class="table-wrapper" style="border:none;border-radius:0">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Nama Beasiswa</th>
              <th>Tipe</th>
              <th>Pengelola</th>
              <th>Nilai/Sem</th>
              <th>Kuota</th>
              <th>Deadline</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($beasiswa_list)): ?>
            <tr><td colspan="9" class="text-center py-8 text-gray-400">Belum ada data beasiswa.</td></tr>
            <?php else: ?>
            <?php foreach ($beasiswa_list as $i => $b): ?>
            <tr>
              <td class="text-gray-400 text-xs font-mono"><?= $i+1 ?></td>
              <td class="font-semibold text-sm max-w-[200px]"><span class="line-clamp-1"><?= htmlspecialchars($b['nama_beasiswa']) ?></span></td>
              <td><?= badge_tipe($b['tipe']) ?></td>
              <td class="text-sm text-gray-500"><?= htmlspecialchars($b['pengelola']) ?></td>
              <td class="text-sm font-bold text-[#FF312E]"><?= rupiah($b['nilai_beasiswa']) ?></td>
              <td class="text-sm"><?= $b['kuota'] ?></td>
              <td class="text-xs text-gray-500"><?= date('d M Y', strtotime($b['deadline'])) ?></td>
              <td>
                <span class="badge text-xs <?= $b['is_active'] ? 'badge-diterima' : 'badge-ditolak' ?>">
                  <?= $b['is_active'] ? '✅ Aktif' : '⛔ Nonaktif' ?>
                </span>
              </td>
              <td>
                <div class="flex gap-2">
                  <a href="admin_beasiswa.php?edit=<?= $b['id'] ?>"
                     class="text-blue-600 text-xs font-bold hover:underline">Edit</a>
                  <button type="button" onclick="confirmDelBeasiswa(<?= $b['id'] ?>, '<?= htmlspecialchars(addslashes($b['nama_beasiswa'])) ?>')"
                          class="text-red-500 text-xs font-bold hover:underline"
                          id="del-beasiswa-<?= $b['id'] ?>">Hapus</button>
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
      <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-2xl mb-3">⚠️</div>
      <h2 class="text-lg font-black text-[#0A0A0F]">Hapus Beasiswa?</h2>
    </div>
    <div class="modal-body">
      <p class="text-sm text-gray-500">Beasiswa <strong id="del-name"></strong> dan semua pengajuannya akan dihapus permanen. Yakin?</p>
    </div>
    <div class="modal-footer">
      <button onclick="document.getElementById('del-modal').classList.remove('active')" class="btn-ghost text-sm">Batal</button>
      <form method="POST" action="" id="del-form">
        <input type="hidden" name="_action" value="delete">
        <input type="hidden" name="id" id="del-id">
        <button type="submit" class="btn-primary !bg-red-500 !border-red-500 text-sm">Ya, Hapus</button>
      </form>
    </div>
  </div>
</div>
<script>
function confirmDelBeasiswa(id, name) {
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
