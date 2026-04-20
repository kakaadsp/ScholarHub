<?php
// ============================================================
// KONFIGURASI DATABASE — Portal Beasiswa
// ============================================================
// Untuk InfinityFree, ganti 4 nilai di bawah ini:
//   Host    → lihat di panel > MySQL Database (contoh: sql304.epizy.com)
//   Username → epiz_XXXXXXX (dari panel)
//   Password → password yang kamu buat di panel
//   Database → epiz_XXXXXXX_portal_beasiswa (buat di panel dulu)
// ============================================================

$host = "localhost";          // InfinityFree: sql***.epizy.com
$username = "root";               // InfinityFree: epiz_XXXXXXX
$pass = "kakasql10";          // InfinityFree: password dari panel
$nama_db = "portal_beasiswa";    // InfinityFree: epiz_XXXXXXX_nama_db

$conn = mysqli_connect($host, $username, $pass, $nama_db);

if (!$conn) {
    http_response_code(500);
    die("<div style='font-family:monospace;color:red;padding:20px'>
        <b>Koneksi Database Gagal!</b><br>
        Error: " . mysqli_connect_error() . "<br><br>
        Pastikan konfigurasi di conf.php sudah benar.
    </div>");
}

mysqli_set_charset($conn, "utf8mb4");

// Mulai session (hanya jika belum aktif)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: cek login user
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['admin_id']);
$user_nama = $is_logged_in ? htmlspecialchars($_SESSION['user_nama']) : '';

// Helper: format rupiah
function rupiah($n)
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

// Helper: badge tipe beasiswa
function badge_tipe($tipe)
{
    $map = [
        'Prestasi' => ['badge badge-prestasi', '🏆'],
        'Reguler' => ['badge badge-reguler', '🎓'],
        'Leadership' => ['badge badge-leadership', '🌟'],
    ];
    [$cls, $icon] = $map[$tipe] ?? ['badge', ''];
    return "<span class='$cls'>$icon $tipe</span>";
}

// Helper: badge status pengajuan
function badge_status($status)
{
    $map = [
        'Pending' => 'badge badge-pending',
        'Diterima' => 'badge badge-diterima',
        'Ditolak' => 'badge badge-ditolak',
    ];
    $cls = $map[$status] ?? 'badge';
    $icons = ['Pending' => '⏳', 'Diterima' => '✅', 'Ditolak' => '❌'];
    $icon = $icons[$status] ?? '';
    return "<span class='$cls'>$icon $status</span>";
}
?>