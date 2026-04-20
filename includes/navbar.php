<?php
// includes/navbar.php
$page = basename($_SERVER['PHP_SELF'], '.php');
$is_logged_in = isset($_SESSION['user_id']);
$is_admin     = isset($_SESSION['admin_id']);
$user_nama    = $is_logged_in ? htmlspecialchars($_SESSION['user_nama']) : '';

function nav_active($p, $current) {
    return $p === $current ? 'nav-pill active' : 'nav-pill text-gray-600';
}
?>
<nav class="bg-white/95 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-gray-100">
  <div class="container flex items-center justify-between h-[68px]">

    <!-- Logo -->
    <a href="index.php" class="flex items-center gap-2 group">
      <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#FF312E] to-[#FF6B35] flex items-center justify-center text-white font-black text-sm shadow-md group-hover:shadow-red-300/50 transition-shadow">
        🎓
      </div>
      <span class="font-black text-lg text-[#0A0A0F] tracking-tight">
        Scholar<span class="text-[#FF312E]">Hub</span>
      </span>
    </a>

    <!-- Desktop Nav -->
    <ul class="hidden md:flex items-center gap-1 text-sm">
      <li><a href="index.php"   class="<?= nav_active('index',   $page) ?>">Beranda</a></li>
      <li><a href="catalog.php" class="<?= nav_active('catalog', $page) ?>">Beasiswa</a></li>
      <?php if ($is_logged_in): ?>
      <li><a href="riwayat.php" class="<?= nav_active('riwayat', $page) ?>">Riwayat Saya</a></li>
      <?php endif; ?>
      <li><a href="profile.php" class="<?= nav_active('profile', $page) ?>">Tim Kami</a></li>
      <?php if ($is_admin): ?>
      <li>
        <a href="admin.php" class="nav-pill !bg-amber-500 !text-white flex items-center gap-1">
          ⚙️ Admin
        </a>
      </li>
      <?php endif; ?>
    </ul>

    <!-- Auth -->
    <div class="hidden md:flex items-center gap-2">
      <?php if ($is_admin): ?>
        <span class="text-xs text-amber-600 font-bold bg-amber-50 border border-amber-200 px-3 py-1 rounded-full">⚙️ Admin Mode</span>
        <a href="logout.php" class="btn-ghost text-xs">Keluar</a>
      <?php elseif ($is_logged_in): ?>
        <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-full px-3 py-1.5">
          <div class="w-6 h-6 rounded-full bg-gradient-to-br from-[#FF312E] to-[#FF6B35] flex items-center justify-center text-white text-xs font-bold">
            <?= mb_substr($user_nama, 0, 1) ?>
          </div>
          <span class="text-xs font-semibold text-gray-700"><?= $user_nama ?></span>
        </div>
        <a href="logout.php" class="btn-ghost text-xs">Keluar</a>
      <?php else: ?>
        <a href="register.php" id="nav-btn-daftar"
           class="btn-secondary text-xs px-4 py-2">Daftar</a>
        <a href="login.php" id="nav-btn-masuk"
           class="btn-primary text-xs px-4 py-2">Masuk</a>
      <?php endif; ?>
    </div>

    <!-- Hamburger -->
    <button id="menu-toggle" class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Toggle menu">
      <svg id="icon-open" class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
      <svg id="icon-close" class="w-5 h-5 text-gray-700 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>

  <!-- Mobile Menu -->
  <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100">
    <div class="container py-4 flex flex-col gap-2 text-sm">
      <a href="index.php"   class="block py-2 px-3 rounded-lg hover:bg-gray-50 font-medium">🏠 Beranda</a>
      <a href="catalog.php" class="block py-2 px-3 rounded-lg hover:bg-gray-50 font-medium">🎓 Beasiswa</a>
      <?php if ($is_logged_in): ?>
      <a href="riwayat.php" class="block py-2 px-3 rounded-lg hover:bg-gray-50 font-medium">📋 Riwayat Saya</a>
      <?php endif; ?>
      <a href="profile.php" class="block py-2 px-3 rounded-lg hover:bg-gray-50 font-medium">👥 Tim Kami</a>
      <?php if ($is_admin): ?>
      <a href="admin.php" class="block py-2 px-3 rounded-lg bg-amber-50 text-amber-700 font-bold">⚙️ Admin Dashboard</a>
      <?php endif; ?>
      <div class="flex gap-2 pt-2 border-t border-gray-100 mt-1">
        <?php if ($is_logged_in || $is_admin): ?>
          <a href="logout.php" class="btn-ghost text-xs flex-1 justify-center">Keluar</a>
        <?php else: ?>
          <a href="register.php" class="btn-secondary text-xs flex-1 justify-center">Daftar</a>
          <a href="login.php" class="btn-primary text-xs flex-1 justify-center">Masuk</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<script>
  const toggle = document.getElementById('menu-toggle');
  const menu   = document.getElementById('mobile-menu');
  const iconO  = document.getElementById('icon-open');
  const iconC  = document.getElementById('icon-close');
  toggle.addEventListener('click', () => {
    menu.classList.toggle('hidden');
    iconO.classList.toggle('hidden');
    iconC.classList.toggle('hidden');
  });
</script>
