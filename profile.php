<?php
require_once 'conf.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tim Kami — ScholarHub</title>
  <meta name="description" content="Kenali tim Kelompok 3 SI-D UPN Veteran Jawa Timur di balik ScholarHub.">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-[#F8FAFC] min-h-screen flex flex-col">

<?php include 'includes/navbar.php'; ?>

<!-- Hero -->
<section class="bg-gradient-to-br from-[#0A0A0F] to-[#1A1A2E] py-20 relative overflow-hidden">
  <div class="absolute top-0 right-0 w-80 h-80 bg-[#FF312E]/15 rounded-full blur-3xl"></div>
  <div class="container relative z-10 text-center">
    <span class="sdg-badge mb-4 inline-flex">🌱 SDGs Goal 4 — Quality Education</span>
    <h1 class="text-3xl md:text-5xl font-black text-white mb-3">
      Tim <span class="text-[#FF312E]">Kelompok 3</span>
    </h1>
    <p class="text-white/50 text-sm max-w-lg mx-auto">
      Pemrograman Web · Semester IV · Sistem Informasi – D<br>
      UPN "Veteran" Jawa Timur · TA 2025/2026
    </p>
  </div>
</section>

<!-- About Project -->
<section class="container py-16">
  <div class="max-w-3xl mx-auto text-center mb-14">
    <p class="section-label">Tentang Proyek</p>
    <h2 class="text-3xl font-black text-[#0A0A0F] mb-4">
      ScholarHub — <span class="text-[#FF312E]">Portal Beasiswa Digital</span>
    </h2>
    <p class="text-gray-500 leading-relaxed">
      ScholarHub adalah platform beasiswa berbasis web yang dibangun sebagai proyek UTS Pemrograman Web.
      Proyek ini mendukung <strong>SDG Goal 4 (Quality Education)</strong> dengan mempermudah akses informasi
      beasiswa bagi mahasiswa Indonesia. Dibangun dengan PHP Native, MySQL, dan Tailwind CSS.
    </p>
  </div>

  <!-- Tech Stack -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-2xl mx-auto mb-16">
    <?php
    $techs = [
      ['🐘', 'PHP Native',  'Server-side'],
      ['🗄️', 'MySQL',       'Database'],
      ['🎨', 'Tailwind CSS','Styling'],
      ['⚡', 'JavaScript',  'Interaktivitas'],
    ];
    foreach ($techs as [$ic, $name, $desc]): ?>
    <div class="card p-4 text-center hover:-translate-y-1 transition-transform">
      <div class="text-3xl mb-2"><?= $ic ?></div>
      <p class="font-bold text-sm text-[#0A0A0F]"><?= $name ?></p>
      <p class="text-xs text-gray-400"><?= $desc ?></p>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Team Members -->
  <p class="section-label text-center justify-center mb-4">Anggota Tim</p>
  <h2 class="text-2xl font-black text-center text-[#0A0A0F] mb-10">Yang Ada Di Balik <span class="text-[#FF312E]">ScholarHub</span></h2>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
    <?php
    $members = [
      ['A', 'Anggota 1',  'Sistem Informasi', '22083XXXXX', 'Full-Stack Developer',    '#FF312E'],
      ['B', 'Anggota 2',  'Sistem Informasi', '22083XXXXX', 'UI/UX & Frontend',        '#6366F1'],
      ['C', 'Anggota 3',  'Sistem Informasi', '22083XXXXX', 'Backend & Database',      '#10B981'],
      ['D', 'Anggota 4',  'Sistem Informasi', '22083XXXXX', 'Frontend & Documentation','F59E0B'],
      ['E', 'Anggota 5',  'Sistem Informasi', '22083XXXXX', 'QA & Testing',            '#8B5CF6'],
    ];
    // Tambahkan nama asli anggota tim kamu di sini
    foreach ($members as $i => [$initial, $name, $prodi, $nim, $role, $color]): ?>
    <div class="card p-6 text-center fade-up-<?= ($i%4)+1 ?>">
      <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center text-white font-black text-xl shadow-lg"
           style="background: linear-gradient(135deg, <?= $color ?>, <?= $color ?>99)">
        <?= $initial ?>
      </div>
      <h3 class="font-black text-[#0A0A0F] text-base mb-0.5"><?= htmlspecialchars($name) ?></h3>
      <p class="text-xs text-gray-400 mb-0.5"><?= $prodi ?></p>
      <p class="text-xs text-gray-400 mb-3">NIM: <?= $nim ?></p>
      <span class="badge text-xs" style="background:#F3F4F6;color:#374151;border:1px solid #E5E7EB;">
        <?= htmlspecialchars($role) ?>
      </span>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- SDGs Section -->
<section class="bg-gradient-to-br from-green-600 to-green-700 py-16 relative overflow-hidden">
  <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, rgba(255,255,255,.3) 1px, transparent 1px); background-size: 24px 24px;"></div>
  <div class="container relative z-10 text-center">
    <div class="text-6xl mb-4">🌱</div>
    <h2 class="text-2xl md:text-3xl font-black text-white mb-4">
      Mendukung Sustainable Development Goals
    </h2>
    <p class="text-white/70 text-base mb-6 max-w-lg mx-auto">
      <strong class="text-white">SDG Goal 4 — Quality Education</strong><br>
      ScholarHub berkontribusi pada pemerataan akses pendidikan berkualitas untuk semua mahasiswa Indonesia,
      tanpa memandang latar belakang ekonomi maupun geografis.
    </p>
    <div class="flex justify-center gap-6 flex-wrap">
      <?php
      $sdgs = ['📚 Pendidikan Merata', '🤝 Inklusif', '💻 Digital Access', '🌍 Berkelanjutan'];
      foreach ($sdgs as $s): ?>
      <span class="bg-white/20 text-white font-bold text-sm px-4 py-2 rounded-full"><?= $s ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
