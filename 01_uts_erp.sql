-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 08 Apr 2026 pada 08.31
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
(1, 1, 'INV/2026/1', '2026-04-08', '2026-04-08', 'posted', 'paid', 1665.00);

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
(1, 1, '2026-04-08 08:13:00', 'Cash', '', 1665.00, NULL);

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
  `satuan_unit` varchar(10) DEFAULT 'pcs'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `product`
--

INSERT INTO `product` (`id_product`, `nama_product`, `sku`, `tipe_product`, `harga_jual`, `biaya_standar`, `stok_minimal`, `satuan_unit`) VALUES
(1, 'Donut Adam', '001', 'storable', 15.00, 10.00, 0, 'pcs');

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
(2, 'PT XYZ', 'Jalan Pulomas', 'xyz@mail.com', '111', '1234567890', 'Cash', 'IDR'),
(3, 'Donutskalbis', 'Stasiun Bogor', 'adamzeinhadoop@gmail.com', '911', '67676767676', 'Cash', 'IDR'),
(4, 'Kalbis', 'Jalan Pulomas\r\n22', 'kalbis@ac.id', '112', '202410', 'Cash', 'IDR');

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
(1, 3, '2026-04-07', NULL, 'draft', 1500.00, 165.00, 1665.00);

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
(1, 1, 1, 100, 15.00, 1500.00);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id_invoice`),
  ADD KEY `fk_purchase` (`fk_purchase`);

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
-- AUTO_INCREMENT untuk tabel `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id_invoice` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `payment`
--
ALTER TABLE `payment`
  MODIFY `id_payment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `product`
--
ALTER TABLE `product`
  MODIFY `id_product` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `transaksi_purchase`
--
ALTER TABLE `transaksi_purchase`
  MODIFY `id_purchase` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `transaksi_purchase_line`
--
ALTER TABLE `transaksi_purchase_line`
  MODIFY `id_purchase_line` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

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

