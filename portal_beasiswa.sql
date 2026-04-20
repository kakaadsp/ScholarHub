-- ============================================================
-- PORTAL BEASISWA — Schema Database + Seed Data
-- Kelompok 3 | Pemrograman Web | SI-D | 2025/2026
-- SDGs: Quality Education (Goal 4)
-- ============================================================
-- ⚠️  CARA IMPORT DI INFINITYFREE:
--   1. Buat database dari panel InfinityFree (Manage MySQL → Create DB)
--   2. Buka phpMyAdmin → pilih database yang baru dibuat
--   3. Klik tab "Import" → pilih file SQL ini → klik "Go"
-- ============================================================

-- ── Hapus tabel lama jika ada ──────────────────────────────
DROP TABLE IF EXISTS pengajuan;
DROP TABLE IF EXISTS beasiswa;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admin;

-- ── Tabel ADMIN ────────────────────────────────────────────
CREATE TABLE admin (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100)  NOT NULL,
    email      VARCHAR(100)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel USERS (Pengguna) ─────────────────────────────────
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nama       VARCHAR(100)  NOT NULL,
    email      VARCHAR(100)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    no_hp      VARCHAR(20)   DEFAULT NULL,
    universitas VARCHAR(200) DEFAULT NULL,
    prodi      VARCHAR(150)  DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel BEASISWA (Master) ────────────────────────────────
CREATE TABLE beasiswa (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nama_beasiswa VARCHAR(200)                              NOT NULL,
    pengelola     VARCHAR(150)                              NOT NULL,
    tipe          ENUM('Prestasi','Reguler','Leadership')  NOT NULL,
    deskripsi     TEXT,
    syarat        TEXT,
    kuota         INT           DEFAULT 50,
    nilai_beasiswa BIGINT       DEFAULT 0,
    deadline      DATE,
    is_active     TINYINT(1)    DEFAULT 1,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Tabel PENGAJUAN (Transaksi) ────────────────────────────
CREATE TABLE pengajuan (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT            NOT NULL,
    beasiswa_id INT            NOT NULL,
    universitas VARCHAR(200)   NOT NULL,
    prodi       VARCHAR(150)   NOT NULL,
    semester    TINYINT        NOT NULL,
    ipk         DECIMAL(3, 2)  NOT NULL,
    ukt         BIGINT         NOT NULL DEFAULT 0,
    motivasi    TEXT,
    status      ENUM('Pending','Diterima','Ditolak') DEFAULT 'Pending',
    catatan_admin TEXT         DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (beasiswa_id) REFERENCES beasiswa(id) ON DELETE CASCADE,
    UNIQUE KEY unik_pengajuan (user_id, beasiswa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED DATA
-- ============================================================

-- ── 1 Admin (password: admin123) ──────────────────────────
INSERT INTO admin (nama, email, password) VALUES
    ('Administrator', 'admin@portalbeasiswa.id', 'admin123');

-- ── 12 Pengguna (password: password123) ────────────────────
INSERT INTO users (nama, email, password, no_hp, universitas, prodi) VALUES
    ('Budi Santoso',       'budi@gmail.com',    'password123', '08211234001', 'UPN Veteran Jawa Timur',           'Sistem Informasi'),
    ('Siti Rahayu',        'siti@gmail.com',    'password123', '08211234002', 'Universitas Brawijaya',             'Teknik Informatika'),
    ('Ahmad Fauzi',        'ahmad@gmail.com',   'password123', '08211234003', 'Institut Teknologi Sepuluh Nopember', 'Sistem Informasi'),
    ('Dewi Lestari',       'dewi@gmail.com',    'password123', '08211234004', 'Universitas Airlangga',             'Manajemen Informatika'),
    ('Rizky Pratama',      'rizky@gmail.com',   'password123', '08211234005', 'Universitas Diponegoro',            'Ilmu Komputer'),
    ('Fajar Nugroho',      'fajar@gmail.com',   'password123', '08211234006', 'Universitas Gadjah Mada',           'Teknologi Informasi'),
    ('Ayu Wulandari',      'ayu@gmail.com',     'password123', '08211234007', 'Universitas Indonesia',             'Sistem Informasi'),
    ('Dimas Pratama',      'dimas@gmail.com',   'password123', '08211234008', 'Universitas Padjadjaran',           'Informatika'),
    ('Rina Kusumawati',    'rina@gmail.com',    'password123', '08211234009', 'UPN Veteran Jawa Timur',            'Teknik Informatika'),
    ('Kevin Wijaya',       'kevin@gmail.com',   'password123', '08211234010', 'Universitas Binus',                 'Computer Science'),
    ('Nadia Putri',        'nadia@gmail.com',   'password123', '08211234011', 'UPN Veteran Jawa Timur',            'Sistem Informasi'),
    ('Hendra Saputra',     'hendra@gmail.com',  'password123', '08211234012', 'Universitas Negeri Surabaya',       'Pendidikan Teknologi');

-- ── 9 Program Beasiswa — 3 kategori masing-masing 3 ───────
INSERT INTO beasiswa (nama_beasiswa, pengelola, tipe, deskripsi, syarat, kuota, nilai_beasiswa, deadline) VALUES
-- PRESTASI (3)
(
    'Beasiswa Prestasi Akademik UPN',
    'UPN Veteran Jawa Timur',
    'Prestasi',
    'Beasiswa penghargaan untuk mahasiswa yang berhasil mempertahankan prestasi akademik terbaik di program studi masing-masing setiap semester. Program ini mendukung SDG Goal 4 (Quality Education) dengan mendorong mahasiswa untuk terus berprestasi.',
    'IPK minimal 3.50 | Mahasiswa aktif minimal semester 2 | Tidak sedang menerima beasiswa akademik lain | Aktif dalam kegiatan kampus minimal 1 organisasi',
    30, 3000000, '2026-05-31'
),
(
    'Beasiswa Djarum Plus',
    'Djarum Foundation',
    'Prestasi',
    'Program beasiswa pengembangan diri yang tidak hanya memberikan tunjangan finansial, tetapi juga pelatihan soft-skill, networking nasional, dan mentoring dari para profesional industri terkemuka.',
    'IPK minimal 3.20 | Aktif di minimal satu organisasi kemahasiswaan | Mahasiswa semester 3-7 | Belum pernah mendapat beasiswa Djarum sebelumnya | Tidak sedang menerima beasiswa lain',
    75, 6000000, '2026-05-15'
),
(
    'Beasiswa PPA (Peningkatan Prestasi Akademik)',
    'Kemdikbudristek',
    'Prestasi',
    'Beasiswa PPA diberikan kepada mahasiswa berprestasi di perguruan tinggi negeri dan swasta sebagai penghargaan atas capaian akademik yang luar biasa. Bertujuan mendorong budaya akademis yang kompetitif.',
    'IPK minimal 3.00 | Mahasiswa aktif minimal semester 2 | Direkomendasikan oleh perguruan tinggi | Tidak sedang menerima beasiswa sejenis dari sumber pemerintah',
    200, 2400000, '2026-06-01'
),
-- REGULER (3)
(
    'KIP Kuliah 2026',
    'Kemdikbudristek',
    'Reguler',
    'Bantuan biaya pendidikan dan biaya hidup bagi mahasiswa dari keluarga kurang mampu yang berprestasi. Program KIP Kuliah merupakan transformasi dari beasiswa Bidikmisi dan menjangkau lebih banyak penerima manfaat.',
    'IPK minimal 2.75 | Penghasilan orang tua < Rp 4.000.000/bulan | Mahasiswa aktif semester 1-8 | Tidak sedang menerima beasiswa lain | Memiliki KIP atau bukti ketidakmampuan ekonomi',
    100, 7200000, '2026-06-30'
),
(
    'LPDP Reguler 2026',
    'Kemenkeu RI',
    'Reguler',
    'Beasiswa penuh dari Lembaga Pengelola Dana Pendidikan untuk studi lanjut S2/S3 di dalam dan luar negeri bagi calon pemimpin masa depan Indonesia. Mencakup biaya pendidikan, biaya hidup, dan tunjangan penelitian.',
    'Lulusan S1 IPK minimal 3.00 | Usia maksimal 35 tahun (S2) / 40 tahun (S3) | Tidak sedang/sudah menempuh S2 yang dibiayai pemerintah | Lolos seleksi administrasi, tulis, dan wawancara',
    500, 50000000, '2026-07-15'
),
(
    'Beasiswa Bank Indonesia',
    'Bank Indonesia',
    'Reguler',
    'Program Beasiswa Bank Indonesia untuk mendukung pendidikan mahasiswa berprestasi di bidang ekonomi, keuangan, dan teknologi informasi yang menjadi prioritas Bank Indonesia dalam mendukung transformasi digital perbankan nasional.',
    'IPK minimal 3.00 | Program studi relevan (SI, TI, Ekonomi, Manajemen, Akuntansi) | Mahasiswa semester 4-7 | Aktif berorganisasi | Bukan penerima beasiswa BI sebelumnya',
    60, 6000000, '2026-08-01'
),
-- LEADERSHIP (3)
(
    'Beasiswa Leadership Nasional',
    'Tanoto Foundation',
    'Leadership',
    'Program beasiswa komprehensif untuk calon pemimpin masa depan Indonesia yang memiliki rekam jejak kepemimpinan yang kuat dan visi perubahan nyata. Program ini termasuk leadership camp, mentoring CEO, dan study tour.',
    'IPK minimal 3.00 | Terbukti aktif berorganisasi (minimal pengurus tingkat fakultas) | Usia maksimal 23 tahun | Memiliki pengalaman kepemimpinan yang didokumentasikan minimal 1 tahun',
    50, 12000000, '2026-06-15'
),
(
    'Beasiswa ETOS Pertamina',
    'Pertamina Foundation',
    'Leadership',
    'Program beasiswa holistik yang menggabungkan bantuan finansial, pembinaan karakter, kepemimpinan, dan pengembangan entrepreneurship untuk mahasiswa dari keluarga tidak mampu namun berpotensi memimpin.',
    'IPK minimal 3.00 | Berasal dari keluarga kurang mampu (penghasilan < Rp 5.000.000/bulan) | Aktif organisasi | Mahasiswa semester 2-6 | Jurusan IPA/teknik/TI diprioritaskan',
    40, 9600000, '2026-05-30'
),
(
    'Beasiswa Pemimpin Muda Indonesia',
    'Yayasan Pendidikan Telkom',
    'Leadership',
    'Program beasiswa eksklusif bagi mahasiswa yang terbukti memiliki jiwa kepemimpinan, inovasi sosial, dan komitmen nyata terhadap pemberdayaan masyarakat sesuai prinsip SDGs. Termasuk inkubasi ide sosial dan dana hibah proyek.',
    'IPK minimal 3.10 | Aktif sebagai pengurus inti organisasi kampus atau komunitas sosial | Memiliki proyek atau inisiatif sosial yang dapat dibuktikan | Semester 3-7 | Lolos seleksi esai dan presentasi',
    25, 15000000, '2026-07-01'
);

-- ── Pengajuan Beasiswa (Transaksi) ─────────────────────────
-- Setiap user punya pengajuan yang berbeda
-- User 1: Budi Santoso
INSERT INTO pengajuan (user_id, beasiswa_id, universitas, prodi, semester, ipk, ukt, motivasi, status) VALUES
(1, 4, 'UPN Veteran Jawa Timur', 'Sistem Informasi', 4, 3.52, 2500000,
 'Saya berasal dari keluarga dengan kondisi ekonomi menengah ke bawah. Ayah saya bekerja sebagai buruh dan penghasilan orang tua tidak mencukupi untuk biaya kuliah sepenuhnya. Dengan KIP Kuliah, saya berkomitmen untuk mempertahankan prestasi dan membuktikan bahwa keterbatasan ekonomi bukan hambatan.', 'Pending'),
(1, 1, 'UPN Veteran Jawa Timur', 'Sistem Informasi', 4, 3.52, 2500000,
 'IPK saya konsisten di atas 3.50 sejak semester 1. Saya selalu masuk dalam daftar mahasiswa berprestasi prodi setiap semester. Saya sangat termotivasi untuk terus meningkatkan kualitas akademik.', 'Diterima'),
(1, 2, 'UPN Veteran Jawa Timur', 'Sistem Informasi', 4, 3.52, 2500000,
 'Saya aktif sebagai anggota BEM Fakultas dan mengikuti berbagai kompetisi pemrograman. Beasiswa Djarum Plus akan sangat membantu pengembangan soft-skill saya.', 'Pending');

-- User 2: Siti Rahayu
INSERT INTO pengajuan (user_id, beasiswa_id, universitas, prodi, semester, ipk, ukt, motivasi, status) VALUES
(2, 4, 'Universitas Brawijaya', 'Teknik Informatika', 3, 3.78, 3000000,
 'Kondisi ekonomi keluarga saya sangat membutuhkan dukungan finansial. Ibu saya single parent yang bekerja sebagai guru honorer. Dengan bantuan KIP Kuliah, saya dapat lebih fokus pada studi.', 'Diterima'),
(2, 7, 'Universitas Brawijaya', 'Teknik Informatika', 3, 3.78, 3000000,
 'Saya telah memimpin UKM Robotics sejak semester 2 dan berhasil meraih juara 2 Kontes Robot Nasional 2025. Saya juga aktif sebagai mentor coding untuk siswa SMA di sekitar kampus.', 'Pending');

-- User 3: Ahmad Fauzi
INSERT INTO pengajuan (user_id, beasiswa_id, universitas, prodi, semester, ipk, ukt, motivasi, status) VALUES
(3, 5, 'Institut Teknologi Sepuluh Nopember', 'Sistem Informasi', 6, 3.65, 0,
 'Saya berencana melanjutkan studi S2 di bidang AI di UGM. Saya telah mempublikasikan 1 paper di konferensi internasional tentang NLP dan sedang mengerjakan thesis tentang Machine Learning.', 'Pending'),
(3, 6, 'Institut Teknologi Sepuluh Nopember', 'Sistem Informasi', 6, 3.65, 4000000,
 'Program studi SI saya sangat relevan dengan kebutuhan digitalisasi perbankan. Saya memiliki pengalaman magang di fintech startup selama 3 bulan.', 'Ditolak');

-- User 4: Dewi Lestari
INSERT INTO pengajuan (user_id, beasiswa_id, universitas, prodi, semester, ipk, ukt, motivasi, status) VALUES
(4, 1, 'Universitas Airlangga', 'Manajemen Informatika', 5, 3.91, 2800000,
 'Saya merupakan mahasiswa dengan IPK tertinggi di angkatan 2022 prodi MI UNAIR dengan berbagai penghargaan akademik termasuk Mahasiswa Berprestasi tingkat Universitas.', 'Diterima'),
(4, 2, 'Universitas Airlangga', 'Manajemen Informatika', 5, 3.91, 2800000,
 'Prestasi saya meliputi juara 1 GEMASTIK 2025, finalis Asia Pacific ICT Alliance, dan aktif sebagai asisten dosen. Beasiswa Djarum akan mendukung pengembangan soft-skill saya.', 'Diterima'),
(4, 9, 'Universitas Airlangga', 'Manajemen Informatika', 5, 3.91, 2800000,
 'Saya menjabat sebagai Ketua BEM Fakultas Vokasi UNAIR periode 2025-2026 dengan program kerja yang berhasil menggerakkan 500+ mahasiswa untuk kegiatan sosial dan inovasi.', 'Pending');

-- User 5: Rizky Pratama
INSERT INTO pengajuan (user_id, beasiswa_id, universitas, prodi, semester, ipk, ukt, motivasi, status) VALUES
(5, 4, 'Universitas Diponegoro', 'Ilmu Komputer', 2, 3.40, 3200000,
 'Saya berasal dari keluarga petani di Jawa Tengah dengan penghasilan terbatas. KIP Kuliah akan memungkinkan saya fokus belajar tanpa harus kerja part-time.', 'Pending'),
(5, 3, 'Universitas Diponegoro', 'Ilmu Komputer', 2, 3.40, 3200000,
 'Meski baru semester 2, IPK saya telah memenuhi persyaratan beasiswa PPA. Saya berkomitmen untuk terus meningkatkan prestasi akademik.', 'Pending'),
(5, 8, 'Universitas Diponegoro', 'Ilmu Komputer', 2, 3.40, 3200000,
 'Saya tergabung dalam komunitas coding POROS UNDIP dan telah membuat 3 aplikasi open-source. Aktif sebagai pengurus HMIK. Beasiswa ETOS akan mendukung pertumbuhan saya.', 'Ditolak');

-- User 6: Fajar Nugroho
INSERT INTO pengajuan (user_id, beasiswa_id, universitas, prodi, semester, ipk, ukt, motivasi, status) VALUES
(6, 1, 'Universitas Gadjah Mada', 'Teknologi Informasi', 4, 3.72, 3500000,
 'Saya konsisten mempertahankan IPK di atas 3.70 dengan predikat cum laude semester lalu. Saya juga aktif menulis jurnal ilmiah di bidang keamanan siber.', 'Diterima'),
(6, 7, 'Universitas Gadjah Mada', 'Teknologi Informasi', 4, 3.72, 3500000,
 'Sebagai Ketua Divisi Riset BEM Fakultas TI UGM, saya telah mengkoordinasikan 3 project penelitian yang melibatkan 50+ mahasiswa dan bermitra dengan industri.', 'Diterima');

-- User 7: Ayu Wulandari
INSERT INTO pengajuan (user_id, beasiswa_id, universitas, prodi, semester, ipk, ukt, motivasi, status) VALUES
(7, 6, 'Universitas Indonesia', 'Sistem Informasi', 5, 3.55, 5000000,
 'Program studi Sistem Informasi UI sangat relevan dengan ekosistem digital Bank Indonesia. Saya magang di OJK selama 4 bulan dan memahami regulasi keuangan digital.', 'Diterima'),
(7, 2, 'Universitas Indonesia', 'Sistem Informasi', 5, 3.55, 5000000,
 'Saya merupakan runner-up hackathon COMPFEST UI 2025 dan aktif sebagai asisten lab pemrograman. Beasiswa Djarum akan membuka jaringan profesional saya lebih luas.', 'Pending');

-- User 8: Dimas Pratama
INSERT INTO pengajuan (user_id, beasiswa_id, universitas, prodi, semester, ipk, ukt, motivasi, status) VALUES
(8, 8, 'Universitas Padjadjaran', 'Informatika', 4, 3.30, 4000000,
 'Berasal dari keluarga tidak mampu di Bandung. Aktif di Tim Robotika UNPAD dan mendirikan kelas coding gratis untuk anak-anak di daerah pinggiran kota.', 'Pending'),
(8, 3, 'Universitas Padjadjaran', 'Informatika', 4, 3.30, 4000000,
 'IPK saya memenuhi syarat PPA dan saya berkomitmen meningkatkannya. Saya sangat mengandalkan beasiswa ini untuk melunasi tunggakan UKT semester ini.', 'Diterima');

-- User 9: Rina Kusumawati
INSERT INTO pengajuan (user_id, beasiswa_id, universitas, prodi, semester, ipk, ukt, motivasi, status) VALUES
(9, 4, 'UPN Veteran Jawa Timur', 'Teknik Informatika', 6, 3.85, 2500000,
 'Kondisi ekonomi keluarga kami memerlukan bantuan sejak ibu saya sakit. Saya tetap mempertahankan prestasi demi membuktikan bahwa cobaan tidak menghentikan langkah.', 'Diterima'),
(9, 9, 'UPN Veteran Jawa Timur', 'Teknik Informatika', 6, 3.85, 2500000,
 'Sebagai Ketua HMTIF UPN Jatim, saya menginisiasi program "Code for Village" yang telah menjangkau 5 desa dan membangun sistem informasi desa dari nol.', 'Diterima');

-- User 10: Kevin Wijaya
INSERT INTO pengajuan (user_id, beasiswa_id, universitas, prodi, semester, ipk, ukt, motivasi, status) VALUES
(10, 2, 'Universitas Binus', 'Computer Science', 3, 3.60, 12000000,
 'Saya meraih Best Student Award di Binus semester lalu dan aktif berkompetisi di ICPC regional. Beasiswa Djarum akan memfasilitasi pengembangan diri dan jaringan saya.', 'Pending'),
(10, 1, 'Universitas Binus', 'Computer Science', 3, 3.60, 12000000,
 'Walaupun UKT Binus tinggi, prestasi saya konsisten. IPK 3.60 dan aktif di 2 UKM tech. Beasiswa ini akan meringankan orang tua yang membiayai 3 anak kuliah sekaligus.', 'Pending');

-- User 11: Nadia Putri
INSERT INTO pengajuan (user_id, beasiswa_id, universitas, prodi, semester, ipk, ukt, motivasi, status) VALUES
(11, 1, 'UPN Veteran Jawa Timur', 'Sistem Informasi', 4, 3.76, 2500000,
 'Saya merupakan juara 1 lomba esai ilmiah tingkat nasional 2025 dan aktif dalam program pengabdian masyarakat kampus. Konsisten mempertahankan IPK di atas 3.75 sejak semester 1.', 'Diterima'),
(11, 7, 'UPN Veteran Jawa Timur', 'Sistem Informasi', 4, 3.76, 2500000,
 'Menginisiasi komunitas "Women in Tech" UPN yang kini beranggotakan 120+ mahasiswi. Bermitra dengan 3 startup lokal untuk program mentoring perempuan di bidang teknologi.', 'Pending');

-- User 12: Hendra Saputra
INSERT INTO pengajuan (user_id, beasiswa_id, universitas, prodi, semester, ipk, ukt, motivasi, status) VALUES
(12, 4, 'Universitas Negeri Surabaya', 'Pendidikan Teknologi', 3, 3.20, 2000000,
 'Latar belakang keluarga petani dengan penghasilan di bawah UMR. Saya kuliah sambil mengajar privat untuk membantu biaya hidup. KIP Kuliah akan sangat meringankan beban ini.', 'Pending'),
(12, 8, 'Universitas Negeri Surabaya', 'Pendidikan Teknologi', 3, 3.20, 2000000,
 'Aktif membina adik-adik di panti asuhan sekitar kampus dengan program belajar komputer gratis setiap Sabtu. Inisiatif ini selaras dengan visi Beasiswa ETOS Pertamina.', 'Diterima');
