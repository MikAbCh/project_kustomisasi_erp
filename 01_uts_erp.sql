-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 02 Jun 2026 pada 10.50
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `01_uts_erp`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang_keluar`
--

CREATE TABLE `barang_keluar` (
  `id_keluar` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `id_lokasi` int(11) NOT NULL,
  `qty_keluar` int(11) NOT NULL,
  `tanggal_keluar` datetime DEFAULT current_timestamp(),
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `barang_keluar`
--

INSERT INTO `barang_keluar` (`id_keluar`, `id_product`, `id_lokasi`, `qty_keluar`, `tanggal_keluar`, `keterangan`) VALUES
(1, 11, 2, 10, '2026-05-25 08:36:47', 'Adam pake buat konsum meeting dengan petinggi PwC'),
(2, 11, 1, 2, '2026-05-25 08:57:50', 'Test'),
(3, 11, 2, 1, '2026-05-25 09:29:25', '[SO/2026/0001] haus habis main golf bareng ceo PwC'),
(4, 7, 1, 1, '2026-05-25 09:37:07', '[SO/2026/0003] enak'),
(5, 11, 3, 5, '2026-05-25 09:46:32', '[SO/2026/0004] '),
(6, 8, 3, 5, '2026-05-25 09:47:09', 'ke cilibut'),
(7, 11, 2, 5, '2026-05-25 10:10:18', '[SO/2026/0006] ');

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang_masuk`
--

CREATE TABLE `barang_masuk` (
  `id_masuk` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `id_lokasi` int(11) NOT NULL,
  `qty_masuk` int(11) NOT NULL,
  `tanggal_masuk` datetime DEFAULT current_timestamp(),
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `barang_masuk`
--

INSERT INTO `barang_masuk` (`id_masuk`, `id_product`, `id_supplier`, `id_lokasi`, `qty_masuk`, `tanggal_masuk`, `keterangan`) VALUES
(3, 11, 1, 1, 1, '2026-05-25 08:34:10', '[Adjustment Input Manual]'),
(4, 11, 1, 2, 1, '2026-05-25 08:34:10', '[Adjustment Input Manual]'),
(5, 11, 2, 2, 10, '2026-05-25 08:35:20', 'Received from PO #22'),
(6, 11, 1, 1, 1, '2026-05-25 08:58:18', '[Adjustment Input Manual]'),
(7, 7, 1, 1, 1, '2026-05-25 09:35:30', '[Adjustment Input Manual]'),
(8, 8, 1000, 3, 10, '2026-05-25 09:43:20', 'Received from PO #23'),
(9, 11, 1000, 3, 10, '2026-05-25 09:43:20', 'Received from PO #23'),
(10, 11, 8, 2, 1, '2026-05-25 10:06:50', 'Received from PO #24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer`
--

CREATE TABLE `customer` (
  `id_customer` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `term_pembayaran` varchar(50) DEFAULT 'Net 30',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customer`
--

INSERT INTO `customer` (`id_customer`, `nama_pelanggan`, `alamat`, `email`, `telepon`, `term_pembayaran`, `created_at`) VALUES
(1, 'Adam', 'Bogor', 'adamzeinhadooppwc@gmail.com', '12345678900987654321', 'Cash', '2026-05-25 07:04:30'),
(2, 'Aslam', 'cilebut', 'adam@gamil.com', '098765787', 'Net 30', '2026-05-25 07:31:14'),
(3, 'Abe', 'Jalan Pulomas\r\n22', 'mikaelabechristanto@gmail.com', ' 110', 'Net 30', '2026-05-25 08:08:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `invoice`
--

CREATE TABLE `invoice` (
  `id_invoice` int(11) NOT NULL,
  `fk_purchase` int(11) DEFAULT NULL,
  `nomor_invoice_vendor` varchar(100) DEFAULT NULL,
  `tanggal_invoice` date DEFAULT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `status_invoice` enum('draft','posted','cancel') DEFAULT 'draft',
  `status_pembayaran` enum('unpaid','partial','paid') DEFAULT 'unpaid',
  `total_tagihan` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `invoice`
--

INSERT INTO `invoice` (`id_invoice`, `fk_purchase`, `nomor_invoice_vendor`, `tanggal_invoice`, `tanggal_jatuh_tempo`, `status_invoice`, `status_pembayaran`, `total_tagihan`) VALUES
(1, 1, 'INV/2026/1', '2026-04-08', '2026-04-08', 'posted', 'paid', 1665.00),
(2, 2, 'INV-SM-2026-001', '2026-04-13', '2026-04-13', 'posted', 'paid', 1110000.00),
(3, 3, 'INV/2026/2', '2026-04-13', '2026-04-13', 'posted', 'paid', 1110000.00),
(4, 4, 'INV/2026/3', '2026-04-13', '2026-04-13', 'posted', 'paid', 1110000.00),
(5, 7, 'INV/2026/7', '2026-04-27', '2026-04-27', 'posted', 'paid', 333000.00),
(6, 10, 'INV/2026/6767', '2026-05-18', '2026-05-19', 'draft', 'paid', 33300000.00),
(7, 9, 'INV/2026/100', '2026-05-18', '2026-05-18', 'draft', 'paid', 16650.00),
(9, 23, 'INV/2026/6777878787', '2026-05-25', '2026-05-25', 'posted', 'paid', 222000.00),
(10, 24, 'INV/2026/65432', '2026-05-25', '2026-05-25', 'posted', 'paid', 5550.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `lokasi`
--

CREATE TABLE `lokasi` (
  `id_lokasi` int(11) NOT NULL,
  `nama_gudang` varchar(50) NOT NULL,
  `blok_rak` varchar(10) NOT NULL,
  `nomor_tingkat` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `lokasi`
--

INSERT INTO `lokasi` (`id_lokasi`, `nama_gudang`, `blok_rak`, `nomor_tingkat`) VALUES
(1, 'Gudang A', 'A1', 1),
(2, 'Gudang B', 'A2', 2),
(3, 'Gudang C', 'C3', 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `payment`
--

CREATE TABLE `payment` (
  `id_payment` int(11) NOT NULL,
  `fk_invoice` int(11) DEFAULT NULL,
  `tanggal_bayar` datetime DEFAULT current_timestamp(),
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `memo_referensi` varchar(255) DEFAULT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `fk_akun_bank` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `payment`
--

INSERT INTO `payment` (`id_payment`, `fk_invoice`, `tanggal_bayar`, `metode_pembayaran`, `memo_referensi`, `jumlah_bayar`, `fk_akun_bank`) VALUES
(1, 1, '2026-04-08 08:13:00', 'Cash', '', 1665.00, NULL),
(2, 2, '2026-04-13 06:08:00', 'Transfer Bank', 'Pembayaran Susu 100 Liter', 1110000.00, NULL),
(3, 3, '2026-04-13 08:23:00', 'Transfer Bank', 'Lisensi Hadoop untuk Big Data', 1110000.00, NULL),
(4, 4, '2026-04-13 08:47:00', 'Cash', 'Pembelian Lisensi', 1110000.00, NULL),
(5, 5, '2026-04-27 08:20:00', 'Cash', '', 333333.00, NULL),
(6, 6, '2026-05-18 06:57:00', 'Cash', '', 33300000.00, NULL),
(7, 7, '2026-05-18 07:11:00', 'Transfer Bank', '', 16650.00, NULL),
(8, 9, '2026-05-25 09:41:00', 'Transfer Bank', 'bagus', 222000.00, NULL),
(9, 10, '2026-05-25 10:05:00', 'Cash', '', 5500.00, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `product`
--

CREATE TABLE `product` (
  `id_product` int(11) NOT NULL,
  `nama_product` varchar(100) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `tipe_product` enum('storable','consumable','service') DEFAULT 'storable',
  `harga_jual` decimal(15,2) DEFAULT 0.00,
  `biaya_standar` decimal(15,2) DEFAULT 0.00,
  `stok_minimal` int(11) DEFAULT 0,
  `stok_aktual` int(11) DEFAULT 0,
  `satuan_unit` varchar(10) DEFAULT 'pcs'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `product`
--

INSERT INTO `product` (`id_product`, `nama_product`, `sku`, `tipe_product`, `harga_jual`, `biaya_standar`, `stok_minimal`, `stok_aktual`, `satuan_unit`) VALUES
(1, 'Donut Adam', '001', 'storable', 15.00, 10.00, 0, 0, 'pcs'),
(2, 'Susu Sapi Mentah', 'RAW-MILK-01', 'storable', 0.00, 11000.00, 0, 0, 'Liter'),
(3, 'Lisensi Hadoop', '', 'storable', 0.00, 1000000.00, 0, 0, 'tahun'),
(7, 'Konsultan PwC', 'PwC-001', 'service', 20000000.00, 15000000.00, 0, 0, 'orang'),
(8, 'Mie Yamin', 'JAYA-01', 'storable', 0.00, 15000.00, 0, 5, 'porsi'),
(10, 'Stipendium Romo', 'KAJ-001', 'service', 300000.00, 300000.00, 0, 0, 'pcs'),
(11, 'Pocari 500ML', 'POCARI_01', 'storable', 5000.00, 3000.00, 0, 1, 'pcs');

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int(11) NOT NULL,
  `nama_perusahaan` varchar(100) NOT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `email_bisnis` varchar(50) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `npwp_tax_id` varchar(30) DEFAULT NULL,
  `term_pembayaran_default` varchar(50) DEFAULT NULL,
  `mata_uang` char(3) DEFAULT 'IDR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `nama_perusahaan`, `alamat_lengkap`, `email_bisnis`, `no_telepon`, `npwp_tax_id`, `term_pembayaran_default`, `mata_uang`) VALUES
(1, 'INTERNAL ADJUSTMENT / SYSTEM', NULL, NULL, NULL, NULL, NULL, 'IDR'),
(2, 'PT XYZ', 'Jalan Pulomas', 'xyz@mail.com', '119', '1234567890', 'Cash', 'IDR'),
(3, 'Donutskalbis', 'Stasiun Bogor', 'adamzeinhadoop@gmail.com', '911', '67676767676', 'Cash', 'IDR'),
(4, 'Kalbis', 'Jalan Pulomas\r\n22', 'kalbis@ac.id', '112', '202410', 'Cash', 'IDR'),
(5, 'Horizon University', 'Karawang', 'horizon@mail.com', '000', '123', 'Cash', 'IDR'),
(6, 'Koperasi Susu Makmur', 'Jalan Pulomas\r\n22', 'kontak@susumakmur.com', '12345', '1234', 'Cash', 'IDR'),
(8, 'PT ABC', 'Jalan Pulomas\r\n22', 'mikaelabechristanto@gmail.com', '111', '12', 'Cash', 'IDR'),
(9, 'PT 123', 'Jalan Pulomas\r\n22', 'mikaelabechristanto@gmail.com', '1111111', '987654321234', 'Cash', 'IDR'),
(10, 'KMK Kalbis', 'Jalan Pulomas\r\n22', 'ukr.kmk@kalbis.ac.id', '69696', '911911', 'Cash', 'IDR'),
(1000, 'PT. Jayabaya', 'pulogadung', 'mikaelabechristanto@gmail.com', '110', '', 'Net 30', 'IDR'),
(1001, 'Bang Doel', 'Kandep', 'doel@mail.com', '0000000000', '12345678901234', 'Net 30', 'IDR');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_purchase`
--

CREATE TABLE `transaksi_purchase` (
  `id_purchase` int(11) NOT NULL,
  `fk_supplier` int(11) DEFAULT NULL,
  `tanggal_order` date DEFAULT NULL,
  `tanggal_target_kedatangan` date DEFAULT NULL,
  `status_dokumen` enum('draft','sent','purchase','done','cancel') DEFAULT 'draft',
  `total_sebelum_pajak` decimal(15,2) DEFAULT 0.00,
  `pajak_ppn` decimal(15,2) DEFAULT 0.00,
  `total_keseluruhan` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi_purchase`
--

INSERT INTO `transaksi_purchase` (`id_purchase`, `fk_supplier`, `tanggal_order`, `tanggal_target_kedatangan`, `status_dokumen`, `total_sebelum_pajak`, `pajak_ppn`, `total_keseluruhan`) VALUES
(1, 3, '2026-04-07', NULL, 'draft', 200000.00, 2000.00, 202000.00),
(2, 6, '2026-04-13', NULL, 'done', 1000000.00, 110000.00, 1110000.00),
(3, 8, '2026-04-13', NULL, 'done', 1000000.00, 110000.00, 1110000.00),
(4, 9, '2026-04-13', NULL, 'done', 1000000.00, 110000.00, 1110000.00),
(5, 3, '2026-04-13', NULL, 'done', 16100000.00, 1771000.00, 17871000.00),
(6, 3, '2026-04-20', NULL, 'done', 150000.00, 16500.00, 166500.00),
(7, 10, '2026-04-27', NULL, 'done', 300000.00, 33000.00, 333000.00),
(8, 2, '2026-05-11', NULL, 'done', 15000.00, 1650.00, 16650.00),
(9, 3, '2026-05-11', NULL, 'done', 15000.00, 1650.00, 16650.00),
(10, 3, '2026-05-11', NULL, 'done', 30000000.00, 3300000.00, 33300000.00),
(11, 2, '2026-05-11', NULL, 'done', 15015000.00, 1651650.00, 16666650.00),
(12, 3, '2026-05-11', NULL, 'done', 15000.00, 1650.00, 16650.00),
(13, 8, '2026-05-18', NULL, 'done', 15000000.00, 1650000.00, 16650000.00),
(14, 8, '2026-05-18', NULL, 'done', 15000.00, 1650.00, 16650.00),
(15, 3, '2026-05-18', NULL, 'done', 1000000.00, 110000.00, 1110000.00),
(18, 2, '2026-05-18', NULL, 'done', 15000.00, 1650.00, 16650.00),
(19, 2, '2026-05-18', NULL, 'done', 10000.00, 1100.00, 11100.00),
(20, 2, '2026-05-18', NULL, 'done', 25000.00, 2750.00, 27750.00),
(21, 2, '2026-05-18', NULL, 'done', 5000.00, 550.00, 5550.00),
(22, 2, '2026-05-25', NULL, 'done', 50000.00, 5500.00, 55500.00),
(23, 1000, '2026-05-25', NULL, 'done', 200000.00, 22000.00, 222000.00),
(24, 8, '2026-05-25', NULL, 'done', 5000.00, 550.00, 5550.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_purchase_line`
--

CREATE TABLE `transaksi_purchase_line` (
  `id_purchase_line` int(11) NOT NULL,
  `fk_purchase` int(11) DEFAULT NULL,
  `fk_product` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi_purchase_line`
--

INSERT INTO `transaksi_purchase_line` (`id_purchase_line`, `fk_purchase`, `fk_product`, `qty`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 1, 100, 2000.00, 200000.00),
(2, 2, 2, 100, 10000.00, 1000000.00),
(3, 3, 3, 1, 1000000.00, 1000000.00),
(4, 4, 3, 1, 1000000.00, 1000000.00),
(11, 5, 3, 1, 1000000.00, 1000000.00),
(12, 5, 7, 1, 15000000.00, 15000000.00),
(13, 5, 2, 1, 100000.00, 100000.00),
(15, 6, 8, 10, 15000.00, 150000.00),
(16, 7, 10, 1, 300000.00, 300000.00),
(17, 8, 8, 1, 15000.00, 15000.00),
(18, 9, 8, 1, 15000.00, 15000.00),
(19, 10, 7, 2, 15000000.00, 30000000.00),
(20, 11, 8, 1, 15000.00, 15000.00),
(21, 11, 7, 1, 15000000.00, 15000000.00),
(22, 12, 8, 1, 15000.00, 15000.00),
(24, 13, 7, 1, 15000000.00, 15000000.00),
(25, 14, 8, 1, 15000.00, 15000.00),
(26, 15, 3, 1, 1000000.00, 1000000.00),
(33, 18, 11, 1, 5000.00, 5000.00),
(34, 18, 2, 1, 10000.00, 10000.00),
(35, 19, 11, 2, 5000.00, 10000.00),
(36, 20, 11, 2, 5000.00, 10000.00),
(37, 20, 8, 1, 15000.00, 15000.00),
(38, 21, 11, 1, 5000.00, 5000.00),
(39, 22, 11, 10, 5000.00, 50000.00),
(40, 23, 8, 10, 15000.00, 150000.00),
(41, 23, 11, 10, 5000.00, 50000.00),
(43, 24, 11, 1, 5000.00, 5000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_sales`
--

CREATE TABLE `transaksi_sales` (
  `id_sales` int(11) NOT NULL,
  `fk_customer` int(11) DEFAULT NULL,
  `tanggal_order` date DEFAULT NULL,
  `tanggal_target_pengiriman` date DEFAULT NULL,
  `status_dokumen` enum('draft','sent','sale','done','cancel') DEFAULT 'draft',
  `total_sebelum_pajak` decimal(15,2) DEFAULT 0.00,
  `pajak_ppn` decimal(15,2) DEFAULT 0.00,
  `total_keseluruhan` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi_sales`
--

INSERT INTO `transaksi_sales` (`id_sales`, `fk_customer`, `tanggal_order`, `tanggal_target_pengiriman`, `status_dokumen`, `total_sebelum_pajak`, `pajak_ppn`, `total_keseluruhan`) VALUES
(1, 1, '2026-05-25', NULL, 'done', 5000.00, 550.00, 5550.00),
(2, 2, '2026-05-25', NULL, 'sale', 185000.00, 20350.00, 205350.00),
(3, 2, '2026-05-25', NULL, 'done', 20000000.00, 2200000.00, 22200000.00),
(4, 1, '2026-05-25', NULL, 'done', 130000.00, 14300.00, 144300.00),
(5, 3, '2026-05-25', NULL, 'sale', 25000.00, 2750.00, 27750.00),
(6, 3, '2026-05-25', NULL, 'done', 25000.00, 2750.00, 27750.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_sales_line`
--

CREATE TABLE `transaksi_sales_line` (
  `id_sales_line` int(11) NOT NULL,
  `fk_sales` int(11) DEFAULT NULL,
  `fk_product` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi_sales_line`
--

INSERT INTO `transaksi_sales_line` (`id_sales_line`, `fk_sales`, `fk_product`, `qty`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 11, 1, 5000.00, 5000.00),
(2, 2, 11, 5, 25000.00, 125000.00),
(3, 2, 1, 2, 30000.00, 60000.00),
(4, 3, 7, 1, 20000000.00, 20000000.00),
(5, 4, 8, 5, 20000.00, 100000.00),
(6, 4, 11, 5, 6000.00, 30000.00),
(7, 5, 11, 5, 5000.00, 25000.00),
(8, 6, 11, 5, 5000.00, 25000.00);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD PRIMARY KEY (`id_keluar`),
  ADD KEY `id_product` (`id_product`),
  ADD KEY `id_lokasi` (`id_lokasi`);

--
-- Indeks untuk tabel `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD PRIMARY KEY (`id_masuk`),
  ADD KEY `id_product` (`id_product`),
  ADD KEY `id_supplier` (`id_supplier`),
  ADD KEY `id_lokasi` (`id_lokasi`);

--
-- Indeks untuk tabel `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id_customer`);

--
-- Indeks untuk tabel `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id_invoice`),
  ADD KEY `fk_purchase` (`fk_purchase`);

--
-- Indeks untuk tabel `lokasi`
--
ALTER TABLE `lokasi`
  ADD PRIMARY KEY (`id_lokasi`);

--
-- Indeks untuk tabel `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id_payment`),
  ADD KEY `fk_invoice` (`fk_invoice`);

--
-- Indeks untuk tabel `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id_product`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Indeks untuk tabel `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indeks untuk tabel `transaksi_purchase`
--
ALTER TABLE `transaksi_purchase`
  ADD PRIMARY KEY (`id_purchase`),
  ADD KEY `fk_supplier` (`fk_supplier`);

--
-- Indeks untuk tabel `transaksi_purchase_line`
--
ALTER TABLE `transaksi_purchase_line`
  ADD PRIMARY KEY (`id_purchase_line`),
  ADD KEY `fk_purchase` (`fk_purchase`),
  ADD KEY `fk_product` (`fk_product`);

--
-- Indeks untuk tabel `transaksi_sales`
--
ALTER TABLE `transaksi_sales`
  ADD PRIMARY KEY (`id_sales`),
  ADD KEY `fk_customer` (`fk_customer`);

--
-- Indeks untuk tabel `transaksi_sales_line`
--
ALTER TABLE `transaksi_sales_line`
  ADD PRIMARY KEY (`id_sales_line`),
  ADD KEY `fk_sales` (`fk_sales`),
  ADD KEY `fk_product` (`fk_product`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang_keluar`
--
ALTER TABLE `barang_keluar`
  MODIFY `id_keluar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `barang_masuk`
--
ALTER TABLE `barang_masuk`
  MODIFY `id_masuk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `customer`
--
ALTER TABLE `customer`
  MODIFY `id_customer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id_invoice` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `lokasi`
--
ALTER TABLE `lokasi`
  MODIFY `id_lokasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `payment`
--
ALTER TABLE `payment`
  MODIFY `id_payment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `product`
--
ALTER TABLE `product`
  MODIFY `id_product` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1002;

--
-- AUTO_INCREMENT untuk tabel `transaksi_purchase`
--
ALTER TABLE `transaksi_purchase`
  MODIFY `id_purchase` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `transaksi_purchase_line`
--
ALTER TABLE `transaksi_purchase_line`
  MODIFY `id_purchase_line` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT untuk tabel `transaksi_sales`
--
ALTER TABLE `transaksi_sales`
  MODIFY `id_sales` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `transaksi_sales_line`
--
ALTER TABLE `transaksi_sales_line`
  MODIFY `id_sales_line` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `barang_keluar`
--
ALTER TABLE `barang_keluar`
  ADD CONSTRAINT `barang_keluar_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `product` (`id_product`),
  ADD CONSTRAINT `barang_keluar_ibfk_2` FOREIGN KEY (`id_lokasi`) REFERENCES `lokasi` (`id_lokasi`);

--
-- Ketidakleluasaan untuk tabel `barang_masuk`
--
ALTER TABLE `barang_masuk`
  ADD CONSTRAINT `barang_masuk_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `product` (`id_product`),
  ADD CONSTRAINT `barang_masuk_ibfk_2` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`),
  ADD CONSTRAINT `barang_masuk_ibfk_3` FOREIGN KEY (`id_lokasi`) REFERENCES `lokasi` (`id_lokasi`);

--
-- Ketidakleluasaan untuk tabel `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`fk_purchase`) REFERENCES `transaksi_purchase` (`id_purchase`);

--
-- Ketidakleluasaan untuk tabel `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`fk_invoice`) REFERENCES `invoice` (`id_invoice`);

--
-- Ketidakleluasaan untuk tabel `transaksi_purchase`
--
ALTER TABLE `transaksi_purchase`
  ADD CONSTRAINT `transaksi_purchase_ibfk_1` FOREIGN KEY (`fk_supplier`) REFERENCES `supplier` (`id_supplier`);

--
-- Ketidakleluasaan untuk tabel `transaksi_purchase_line`
--
ALTER TABLE `transaksi_purchase_line`
  ADD CONSTRAINT `transaksi_purchase_line_ibfk_1` FOREIGN KEY (`fk_purchase`) REFERENCES `transaksi_purchase` (`id_purchase`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_purchase_line_ibfk_2` FOREIGN KEY (`fk_product`) REFERENCES `product` (`id_product`);

--
-- Ketidakleluasaan untuk tabel `transaksi_sales`
--
ALTER TABLE `transaksi_sales`
  ADD CONSTRAINT `transaksi_sales_ibfk_1` FOREIGN KEY (`fk_customer`) REFERENCES `customer` (`id_customer`);

--
-- Ketidakleluasaan untuk tabel `transaksi_sales_line`
--
ALTER TABLE `transaksi_sales_line`
  ADD CONSTRAINT `transaksi_sales_line_ibfk_1` FOREIGN KEY (`fk_sales`) REFERENCES `transaksi_sales` (`id_sales`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_sales_line_ibfk_2` FOREIGN KEY (`fk_product`) REFERENCES `product` (`id_product`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
