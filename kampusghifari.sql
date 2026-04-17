-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 17, 2026 at 06:42 AM
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
(1, '2026-02-27', '2025/2026 Ganjil', 'Peserta didik baru', 'Prestasi', 'Sistem Informasi S1', 'Reguler', '265720101', '-', 'Akbar Maulana', 'Banjar', '2026-02-27', 'Laki-laki', 'O', 'Islam', 'L', '87438783873847824783', '843473878387982', '48484398594', 'WNI', 'Jawa Barat', 'Banjar', 'Sukarame', 'Sukarame', 'suk', 'suk', '302', '2929', '46313', 'Bersama Orang Tua', 'Jalan Kaki', '34434343495339032', '081234567890', 'a2@gmail.com', 'Tidak', '-', 'bapak', '2026-02-27', 'SMA', 'Wiraswasta', 'Rp. 500.000 - Rp. 999.000', 'lilis', '2026-02-27', 'SMA', 'Wiraswasta', 'Rp. 500.000 - Rp. 999.000', NULL, NULL, NULL, NULL, NULL, 'SMKN 1 BANJAR', '9233948384384938', 'Balokang', '46313', 'a@gmailc.com', '4234324343', 'sjcnajsnc', 'rpl', NULL, NULL, NULL, 1, '2026-03-02 04:54:24', 5, '2026-02-27 06:52:14', '2026-03-02 04:54:24'),
(2, '2026-03-02', '2025/2026 Ganjil', 'Peserta didik baru', 'Reguler', 'Teknik Informatika S1', 'Reguler A1', '265520201', '-', 'Fikri Nudin', 'Jakarta', '2007-01-01', 'Laki-laki', 'AB', 'Islam', 'XL', '123456789000', '123456789000', '123456789000', 'WNI', 'Jawa Barat', 'Banjar', 'Banjar', 'Balokang', 'Jl. Pahlawan permai', 'Balokang', '12', '13', '46313', 'Bersama Orang Tua', 'Jalan Kaki', '081234567890', '081234567890', 'fikri@gmail.com', 'Tidak', NULL, 'Husen', '1971-01-01', 'S1', 'PNS/TNI/Polri', 'Rp. 5.000.000 - Rp. 20.000.000', 'Siti', '1975-10-15', 'S1', 'PNS/TNI/Polri', 'Rp. 5.000.000 - Rp. 20.000.000', NULL, NULL, NULL, NULL, NULL, 'SMKN 1 BANDUNG', '123456789000', 'Bandung Pusat', '12345', 'smkn1bandung@gmail.com', '12345', 'smeabandung.id', 'Akuntansi', NULL, NULL, NULL, 0, NULL, NULL, '2026-03-02 06:57:40', NULL);

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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `role` enum('admin','dosen','mahasiswa') COLLATE utf8mb4_unicode_ci NOT NULL,
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
(5, 'mahasiswa', '265720101', '$2y$10$3noMDmIAJ4tPbVENx6YJs.YqQ2i28ULr5xDOX/z1s3IMgZeh1lit.', 'Akbar Maulana', 'aktif', '2026-03-02 04:54:24', '2026-04-02 02:43:09');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id_mahasiswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `master_opsi_dropdown`
--
ALTER TABLE `master_opsi_dropdown`
  MODIFY `id_opsi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
