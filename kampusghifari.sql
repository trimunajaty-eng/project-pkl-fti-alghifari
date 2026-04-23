-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 23, 2026 at 06:38 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kampusghifari`
--

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id_dosen` int NOT NULL,
  `kode_dosen` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_dosen` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `program_studi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` int DEFAULT NULL,
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id_dosen`, `kode_dosen`, `nama_dosen`, `jenis_kelamin`, `email`, `program_studi`, `id_user`, `dibuat_pada`, `diubah_pada`) VALUES
(1, 'DSN001', 'Rizky Pratama, S.Kom', 'Laki-laki', 'rizky.pratama@unfari.ac.id', 'Sistem Informasi S1', 8, '2026-04-22 04:07:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id_mahasiswa` int NOT NULL,
  `tanggal_registrasi` date NOT NULL,
  `periode_pendaftaran` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_pendaftaran` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jalur_pendaftaran` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `program_studi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nim` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jalur_keuangan` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_mahasiswa` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tempat_lahir` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `golongan_darah` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agama` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ukuran_seragam` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nisn` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npwp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kewarganegaraan` enum('WNI','WNA') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provinsi` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kab_kota` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kecamatan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelurahan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jalan` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dusun` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rt` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rw` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_pos` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_tinggal` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alat_transportasi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penerima_kps` enum('Ya','Tidak') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_kps` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ayah` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir_ayah` date DEFAULT NULL,
  `pendidikan_ayah` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pekerjaan_ayah` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penghasilan_ayah` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ibu` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir_ibu` date DEFAULT NULL,
  `pendidikan_ibu` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pekerjaan_ibu` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penghasilan_ibu` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_wali` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir_wali` date DEFAULT NULL,
  `pendidikan_wali` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pekerjaan_wali` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penghasilan_wali` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asal_sekolah` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_ijazah` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_sekolah` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kodepos_sekolah` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_sekolah` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon_sekolah` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website_sekolah` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asal_jurusan` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sks_diakui` int DEFAULT NULL,
  `asal_perguruan_tinggi` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `asal_program_studi` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `akun_dicetak` tinyint(1) NOT NULL DEFAULT '0',
  `akun_dicetak_pada` timestamp NULL DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id_mahasiswa`, `tanggal_registrasi`, `periode_pendaftaran`, `jenis_pendaftaran`, `jalur_pendaftaran`, `program_studi`, `kelas`, `nim`, `jalur_keuangan`, `nama_mahasiswa`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `golongan_darah`, `agama`, `ukuran_seragam`, `nik`, `nisn`, `npwp`, `kewarganegaraan`, `provinsi`, `kab_kota`, `kecamatan`, `kelurahan`, `jalan`, `dusun`, `rt`, `rw`, `kode_pos`, `jenis_tinggal`, `alat_transportasi`, `telepon`, `hp`, `email`, `penerima_kps`, `no_kps`, `nama_ayah`, `tanggal_lahir_ayah`, `pendidikan_ayah`, `pekerjaan_ayah`, `penghasilan_ayah`, `nama_ibu`, `tanggal_lahir_ibu`, `pendidikan_ibu`, `pekerjaan_ibu`, `penghasilan_ibu`, `nama_wali`, `tanggal_lahir_wali`, `pendidikan_wali`, `pekerjaan_wali`, `penghasilan_wali`, `asal_sekolah`, `no_ijazah`, `alamat_sekolah`, `kodepos_sekolah`, `email_sekolah`, `telepon_sekolah`, `website_sekolah`, `asal_jurusan`, `sks_diakui`, `asal_perguruan_tinggi`, `asal_program_studi`, `akun_dicetak`, `akun_dicetak_pada`, `id_user`, `dibuat_pada`, `diubah_pada`) VALUES
(3, '2026-04-21', '2026/2027 Genap', 'Peserta didik baru', 'Reguler', 'Sistem Informasi S1', 'Reguler A1', 'FTI265720102', '-', 'Akbar', 'Jakarta', '2026-04-21', 'Laki-laki', 'O', 'Islam', 'XL', '123456789012', '123456789012', '123456789012', 'WNI', 'jawa barat', 'Banjar', 'Banjar', 'Sukarame', 'Jl. Gang setia 123', 'Sukarame', '01', '02', '46313', 'Bersama Orang Tua', 'Sepeda', '082295080124', '082295080124', 'akbar@gmail.com', 'Tidak', NULL, 'Sumanto', '1970-01-01', 'S1', 'Wirausaha', 'Rp. 2.000.000 - Rp. 4.999.999', 'Lilis', '1965-01-01', 'S1', 'Wirausaha', 'Rp. 2.000.000 - Rp. 4.999.999', NULL, NULL, NULL, NULL, NULL, 'SMKN 1 Banjar', '123456789012', 'Link. Parung lesang', '46313', 'smkn1banjar@gmail.com', '081234567890', 'smkn1banjar.sch.id', 'Rekayasa Perangkat Lunak', NULL, NULL, NULL, 1, '2026-04-21 02:38:24', 7, '2026-04-21 02:33:50', '2026-04-21 02:38:24');

-- --------------------------------------------------------

--
-- Table structure for table `master_opsi_dropdown`
--

CREATE TABLE `master_opsi_dropdown` (
  `id_opsi` int NOT NULL,
  `grup` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `kode_ref` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_nim` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_opsi_dropdown`
--

INSERT INTO `master_opsi_dropdown` (`id_opsi`, `grup`, `label`, `value`, `urutan`, `kode_ref`, `kode_nim`, `is_active`, `dibuat_pada`, `diubah_pada`) VALUES
(27, 'periode_pendaftaran', '2025/2026 Ganjil', '2025/2026 Ganjil', 1, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(28, 'periode_pendaftaran', '2025/2026 Genap', '2025/2026 Genap', 2, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(29, 'periode_pendaftaran', '2026/2027 Ganjil', '2026/2027 Ganjil', 3, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(30, 'periode_pendaftaran', '2026/2027 Genap', '2026/2027 Genap', 4, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(31, 'jenis_pendaftaran', 'Peserta didik baru', 'Peserta didik baru', 1, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(32, 'jenis_pendaftaran', 'Pindahan', 'Pindahan', 2, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(33, 'jenis_pendaftaran', 'Alih jenjang', 'Alih jenjang', 3, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(34, 'jenis_pendaftaran', 'Lintas jalur', 'Lintas jalur', 4, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(35, 'jalur_pendaftaran', 'Prestasi', 'Prestasi', 1, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(36, 'jalur_pendaftaran', 'Reguler', 'Reguler', 2, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(37, 'jalur_pendaftaran', 'RPL', 'RPL', 3, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(38, 'program_studi', 'Teknik Informatika S1', 'Teknik Informatika S1', 1, 'FTI', '55202', 1, '2026-04-02 03:59:37', '2026-04-02 05:08:47'),
(39, 'program_studi', 'Sistem Informasi S1', 'Sistem Informasi S1', 2, 'FTI', '57201', 1, '2026-04-02 03:59:37', '2026-04-02 05:08:47'),
(40, 'kelas', 'Reguler', 'Reguler', 1, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(41, 'kelas', 'Reguler Sore B', 'Reguler Sore B', 2, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(42, 'kelas', 'Reguler Sore A', 'Reguler Sore A', 3, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(43, 'kelas', 'Karyawan MJ', 'Karyawan MJ', 4, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(44, 'kelas', 'AMIK HASS WD', 'AMIK HASS WD', 5, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(45, 'kelas', 'AMIK HASS WE', 'AMIK HASS WE', 6, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(46, 'kelas', 'Miftahul Huda', 'Miftahul Huda', 7, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(47, 'kelas', 'Reguler A1', 'Reguler A1', 8, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(48, 'kelas', 'Reguler A2', 'Reguler A2', 9, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(49, 'kelas', 'Reguler A3', 'Reguler A3', 10, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(50, 'kelas', 'Reguler A4', 'Reguler A4', 11, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(51, 'kelas', 'Reguler B1', 'Reguler B1', 12, NULL, NULL, 1, '2026-04-02 03:59:37', NULL),
(52, 'kelas', 'Reguler B2', 'Reguler B2', 13, NULL, NULL, 1, '2026-04-02 03:59:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `nilai_mahasiswa`
--

CREATE TABLE `nilai_mahasiswa` (
  `id_nilai` int NOT NULL,
  `id_mahasiswa` int NOT NULL,
  `id_dosen` int DEFAULT NULL,
  `tahun_akademik` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester` enum('Ganjil','Genap') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tugas` decimal(5,2) DEFAULT NULL,
  `uts` decimal(5,2) DEFAULT NULL,
  `uas` decimal(5,2) DEFAULT NULL,
  `kehadiran` decimal(5,2) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `grade` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_user_input` int DEFAULT NULL,
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nilai_mahasiswa`
--

INSERT INTO `nilai_mahasiswa` (`id_nilai`, `id_mahasiswa`, `id_dosen`, `tahun_akademik`, `semester`, `tugas`, `uts`, `uas`, `kehadiran`, `nilai_akhir`, `grade`, `keterangan`, `id_user_input`, `dibuat_pada`, `diubah_pada`) VALUES
(1, 3, 1, '2026/2027', 'Genap', '70.00', '70.00', '70.00', '70.00', '70.00', '0', 'Lulus', 6, '2026-04-22 04:23:48', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `role` enum('admin','akademik','dosen','mahasiswa') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_lengkap` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `role`, `username`, `password_hash`, `nama_lengkap`, `status`, `dibuat_pada`, `diubah_pada`) VALUES
(1, 'admin', 'unfari', '$2y$10$oURFyI8nefr0wk2D4wei/.qTZtt8WzK36xWNddUKlc8Kt.kz34AVO', 'Universitas Al-ghifari', 'aktif', '2026-02-03 14:21:15', '2026-02-04 04:40:19'),
(6, 'akademik', 'infakd', '$2y$10$Uqg398LlrbUNeRuaa4luAO2YWJsJIW1.4dJp979MXpy6Cb90R915C', 'Bagian Akademik', 'aktif', '2026-04-17 07:02:30', '2026-04-17 07:03:20'),
(7, 'mahasiswa', 'FTI265720102', '$2y$10$TBWaDivckljcWu3cVzChmuUhCXuAXSvGunH8sb9f9LziPTytoEMe.', 'Akbar', 'aktif', '2026-04-21 02:38:24', '2026-04-21 02:40:04'),
(8, 'dosen', 'DSN001', '$2y$10$Jx7rjR5vUqM2S0v0vL2j7O9P6Q7Qw/9S2A1P0v4Hq0xv2d9XUQ4lC', 'Dosen Sistem Informasi', 'aktif', '2026-04-22 04:07:23', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id_dosen`),
  ADD UNIQUE KEY `uniq_kode_dosen` (`kode_dosen`),
  ADD UNIQUE KEY `uniq_email_dosen` (`email`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id_mahasiswa`),
  ADD UNIQUE KEY `nim_unique` (`nim`);

--
-- Indexes for table `master_opsi_dropdown`
--
ALTER TABLE `master_opsi_dropdown`
  ADD PRIMARY KEY (`id_opsi`),
  ADD UNIQUE KEY `uniq_grup_value` (`grup`,`value`);

--
-- Indexes for table `nilai_mahasiswa`
--
ALTER TABLE `nilai_mahasiswa`
  ADD PRIMARY KEY (`id_nilai`),
  ADD UNIQUE KEY `uniq_nilai_mahasiswa_periode` (`id_mahasiswa`,`tahun_akademik`,`semester`),
  ADD KEY `idx_nilai_id_dosen` (`id_dosen`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id_dosen` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id_mahasiswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `master_opsi_dropdown`
--
ALTER TABLE `master_opsi_dropdown`
  MODIFY `id_opsi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `nilai_mahasiswa`
--
ALTER TABLE `nilai_mahasiswa`
  MODIFY `id_nilai` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
