-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 18 Bulan Mei 2026 pada 07.33
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
(1, 7, 1, 1, '2026-05-18 02:39:19', ''),
(2, 8, 1, 1, '2026-05-18 02:39:40', ''),
(3, 3, 1, 1, '2026-05-18 04:25:33', 'Adam mau pake');

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
(1, 8, 3, 1, 1, '2026-05-11 10:08:34', 'Received from PO #12'),
(2, 7, 8, 1, 1, '2026-05-18 02:34:30', 'Received from PO #13'),
(3, 7, 8, 1, 1, '2026-05-18 02:34:40', 'Received from PO #13'),
(4, 8, 8, 1, 1, '2026-05-18 02:36:24', 'Received from PO #14'),
(5, 3, 3, 1, 1, '2026-05-18 04:25:07', 'Received from PO #15');

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
(7, 9, 'INV/2026/100', '2026-05-18', '2026-05-18', 'draft', 'paid', 16650.00);

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
(1, 'Gudang A', 'A1', 1);

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
(7, 7, '2026-05-18 07:11:00', 'Transfer Bank', '', 16650.00, NULL);

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
(7, 'Konsultan PwC', 'PwC-001', 'service', 0.00, 15000000.00, 0, 3, 'orang'),
(8, 'Mie Yamin', 'JAYA-01', 'storable', 0.00, 15000.00, 0, 5, 'porsi'),
(10, 'Stipendium Romo', 'KAJ-001', 'service', 300000.00, 300000.00, 0, 0, 'pcs');

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
(2, 'PT XYZ', 'Jalan Pulomas', 'xyz@mail.com', '119', '1234567890', 'Cash', 'IDR'),
(3, 'Donutskalbis', 'Stasiun Bogor', 'adamzeinhadoop@gmail.com', '911', '67676767676', 'Cash', 'IDR'),
(4, 'Kalbis', 'Jalan Pulomas\r\n22', 'kalbis@ac.id', '112', '202410', 'Cash', 'IDR'),
(5, 'Horizon University', 'Karawang', 'horizon@mail.com', '000', '123', 'Cash', 'IDR'),
(6, 'Koperasi Susu Makmur', 'Jalan Pulomas\r\n22', 'kontak@susumakmur.com', '12345', '1234', 'Cash', 'IDR'),
(8, 'PT ABC', 'Jalan Pulomas\r\n22', 'mikaelabechristanto@gmail.com', '111', '12', 'Cash', 'IDR'),
(9, 'PT 123', 'Jalan Pulomas\r\n22', 'mikaelabechristanto@gmail.com', '1111111', '987654321234', 'Cash', 'IDR'),
(10, 'KMK Kalbis', 'Jalan Pulomas\r\n22', 'ukr.kmk@kalbis.ac.id', '69696', '911911', 'Cash', 'IDR');

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
(15, 3, '2026-05-18', NULL, 'done', 1000000.00, 110000.00, 1110000.00);

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
(26, 15, 3, 1, 1000000.00, 1000000.00);

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
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang_keluar`
--
ALTER TABLE `barang_keluar`
  MODIFY `id_keluar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `barang_masuk`
--
ALTER TABLE `barang_masuk`
  MODIFY `id_masuk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id_invoice` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `lokasi`
--
ALTER TABLE `lokasi`
  MODIFY `id_lokasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `payment`
--
ALTER TABLE `payment`
  MODIFY `id_payment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `product`
--
ALTER TABLE `product`
  MODIFY `id_product` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `transaksi_purchase`
--
ALTER TABLE `transaksi_purchase`
  MODIFY `id_purchase` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `transaksi_purchase_line`
--
ALTER TABLE `transaksi_purchase_line`
  MODIFY `id_purchase_line` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
