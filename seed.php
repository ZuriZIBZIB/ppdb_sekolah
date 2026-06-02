<?php
require_once 'config.php';

echo "=== Seeding Data PPDB ===\n\n";

// 1. Seed Admin User
echo "1. Creating Admin User...\n";
$admin_email = 'admin@ppdb.com';
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);

// Check if admin exists
$check_admin = $conn->query("SELECT id FROM users WHERE email = '$admin_email'");
if ($check_admin->num_rows == 0) {
    $admin_sql = "INSERT INTO users (username, email, password, nama_lengkap, jenis_kelamin, alamat, role, status) 
                  VALUES ('admin_ppdb', '$admin_email', '$admin_password', 'Admin PPDB', 'Laki-laki', 'Sekolah', 'admin', 'aktif')";
    
    if ($conn->query($admin_sql)) {
        echo "✓ Admin user created successfully\n";
        echo "  Email: admin@ppdb.com\n";
        echo "  Password: admin123\n";
    } else {
        echo "✗ Error creating admin: " . $conn->error . "\n";
    }
} else {
    echo "✓ Admin user already exists\n";
}

// 2. Seed Asal Sekolah (Origin Schools)
echo "\n2. Creating Asal Sekolah (Schools)...\n";
$schools = [
    ['nama_sekolah' => 'SMP Negeri 1 Palembang', 'alamat' => 'Jl. Merdeka No. 1', 'kota' => 'Palembang', 'kecamatan' => 'Ilir Barat'],
    ['nama_sekolah' => 'SMP Negeri 2 Palembang', 'alamat' => 'Jl. Sultan Mahmud Badaruddin', 'kota' => 'Palembang', 'kecamatan' => 'Ilir Timur'],
    ['nama_sekolah' => 'SMP Negeri 3 Palembang', 'alamat' => 'Jl. Jenderal Sudirman', 'kota' => 'Palembang', 'kecamatan' => 'Kemuning'],
    ['nama_sekolah' => 'SMP Swasta Al-Quran', 'alamat' => 'Jl. Situ Gintung', 'kota' => 'Palembang', 'kecamatan' => 'Ilir Barat'],
    ['nama_sekolah' => 'SMP Negeri 29 Palembang', 'alamat' => 'Jl. Komplek Jambi', 'kota' => 'Palembang', 'kecamatan' => 'Seberang Ulu I'],
];

$school_count = 0;
foreach ($schools as $school) {
    $check_school = $conn->query("SELECT id FROM asal_sekolah WHERE nama_sekolah = '" . $conn->real_escape_string($school['nama_sekolah']) . "'");
    if ($check_school->num_rows == 0) {
        $insert_school = "INSERT INTO asal_sekolah (nama_sekolah, alamat, kota, kecamatan) 
                         VALUES ('" . $conn->real_escape_string($school['nama_sekolah']) . "', 
                                 '" . $conn->real_escape_string($school['alamat']) . "', 
                                 '" . $conn->real_escape_string($school['kota']) . "', 
                                 '" . $conn->real_escape_string($school['kecamatan']) . "')";
        if ($conn->query($insert_school)) {
            $school_count++;
        }
    }
}
echo "✓ $school_count schools added\n";

// 3. Seed Jurusan (Majors)
echo "\n3. Creating Jurusan (Majors)...\n";
$majors = [
    ['nama_jurusan' => 'Rekayasa Perangkat Lunak (RPL)', 'deskripsi' => 'Program studi untuk mengembangkan aplikasi dan sistem informasi', 'kuota' => 60],
    ['nama_jurusan' => 'Teknik Komputer dan Jaringan (TKJ)', 'deskripsi' => 'Program studi untuk instalasi dan maintenance jaringan', 'kuota' => 50],
    ['nama_jurusan' => 'Teknik Mesin', 'deskripsi' => 'Program studi untuk manufaktur dan teknologi mesin', 'kuota' => 45],
    ['nama_jurusan' => 'Desain Grafis Multimedia', 'deskripsi' => 'Program studi untuk desain dan multimedia', 'kuota' => 40],
    ['nama_jurusan' => 'Administrasi Perkantoran', 'deskripsi' => 'Program studi untuk administrasi dan manajemen perkantoran', 'kuota' => 35],
];

$major_count = 0;
foreach ($majors as $major) {
    $check_major = $conn->query("SELECT id FROM jurusan WHERE nama_jurusan = '" . $conn->real_escape_string($major['nama_jurusan']) . "'");
    if ($check_major->num_rows == 0) {
        $insert_major = "INSERT INTO jurusan (nama_jurusan, deskripsi, kuota, kuota_tersisa) 
                        VALUES ('" . $conn->real_escape_string($major['nama_jurusan']) . "', 
                                '" . $conn->real_escape_string($major['deskripsi']) . "', 
                                " . $major['kuota'] . ", 
                                " . $major['kuota'] . ")";
        if ($conn->query($insert_major)) {
            $major_count++;
        }
    }
}
echo "✓ $major_count majors added\n";

// 4. Seed Fase Pendaftaran (Registration Phases)
echo "\n4. Creating Fase Pendaftaran (Phases)...\n";
$phases = [
    ['nomor_fase' => 1, 'nama_fase' => 'Pendaftaran Online', 'deskripsi' => 'Fase pendaftaran online untuk calon siswa', 'tanggal_mulai' => '2026-05-01', 'tanggal_selesai' => '2026-05-15', 'status' => 'berlangsung'],
    ['nomor_fase' => 2, 'nama_fase' => 'Seleksi Administrasi', 'deskripsi' => 'Fase seleksi kelengkapan berkas administrasi', 'tanggal_mulai' => '2026-05-16', 'tanggal_selesai' => '2026-05-22', 'status' => 'belum_dimulai'],
    ['nomor_fase' => 3, 'nama_fase' => 'Tes Tulis & Wawancara', 'deskripsi' => 'Fase tes tulis dan wawancara', 'tanggal_mulai' => '2026-05-24', 'tanggal_selesai' => '2026-05-30', 'status' => 'belum_dimulai'],
    ['nomor_fase' => 4, 'nama_fase' => 'Pengumuman Hasil', 'deskripsi' => 'Fase pengumuman hasil akhir penerimaan', 'tanggal_mulai' => '2026-06-01', 'tanggal_selesai' => '2026-06-05', 'status' => 'belum_dimulai'],
];

$phase_count = 0;
foreach ($phases as $phase) {
    $check_phase = $conn->query("SELECT id FROM fase_pendaftaran WHERE nomor_fase = " . $phase['nomor_fase']);
    if ($check_phase->num_rows == 0) {
        $insert_phase = "INSERT INTO fase_pendaftaran (nomor_fase, nama_fase, deskripsi, tanggal_mulai, tanggal_selesai, status) 
                        VALUES (" . $phase['nomor_fase'] . ", 
                                '" . $conn->real_escape_string($phase['nama_fase']) . "', 
                                '" . $conn->real_escape_string($phase['deskripsi']) . "', 
                                '" . $phase['tanggal_mulai'] . "', 
                                '" . $phase['tanggal_selesai'] . "', 
                                '" . $phase['status'] . "')";
        if ($conn->query($insert_phase)) {
            $phase_count++;
        }
    }
}
echo "✓ $phase_count phases added\n";

// 5. Seed Informasi Sekolah (School Information)
echo "\n5. Creating Informasi Sekolah (School Info)...\n";
$check_info = $conn->query("SELECT id FROM informasi_sekolah WHERE nama_sekolah = 'SMKN 4 Palembang'");
if ($check_info->num_rows == 0) {
    $info_sql = "INSERT INTO informasi_sekolah (nama_sekolah, akronim, alamat, kota, provinsi, no_telp, email, website, deskripsi, visi, misi) 
                 VALUES ('SMKN 4 Palembang', 'SMKN 4', 'Jl. Jenderal Sudirman No. 123', 'Palembang', 'Sumatera Selatan', '0711-123456', 'info@smkn4plg.sch.id', 'www.smkn4palembang.sch.id', 
                         'SMKN 4 Palembang adalah sekolah menengah kejuruan negeri yang berkomitmen menghasilkan lulusan berkualitas dengan kompetensi internasional.',
                         'Menjadi sekolah menengah kejuruan terdepan dalam menghasilkan sumber daya manusia yang kompeten dan berkarakter',
                         'Menyelenggarakan pendidikan dan pelatihan berkualitas tinggi; Mengembangkan keterampilan profesional; Membentuk karakter siswa yang berintegitas')";
    if ($conn->query($info_sql)) {
        echo "✓ School information added\n";
    }
} else {
    echo "✓ School information already exists\n";
}

echo "\n=== Seeding Completed Successfully ===\n";
echo "\nAkun Admin untuk Login:\n";
echo "Email: admin@ppdb.com\n";
echo "Password: admin123\n";
echo "\nRole: admin\n";

$conn->close();
?>
