<?php 
include 'config.php'; 
include 'header.php'; 

// Mengambil data ringkasan untuk Dashboard
$count_supplier = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM supplier"))['total'];
$count_product  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM product"))['total'];
$count_po       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi_purchase"))['total'];
$total_inv      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_tagihan) as total FROM invoice"))['total'];
?>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .menu-card {
        background: white;
        padding: 30px;
        border-radius: 8px;
        text-align: center;
        text-decoration: none;
        color: #333;
        transition: transform 0.2s, box-shadow 0.2s;
        border-bottom: 4px solid #ef7d00;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .menu-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        color: #ef7d00;
    }
    .icon-box {
        width: 60px;
        height: 60px;
        background: #fff4e6;
        color: #ef7d00;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 15px;
    }
    .stat-number {
        font-size: 1.5rem;
        font-weight: bold;
        margin: 10px 0;
    }
    .welcome-banner {
        background: linear-gradient(135deg, #ef7d00 0%, #ff9a33 100%);
        color: white;
        padding: 40px;
        border-radius: 8px;
        margin-bottom: 30px;
    }
</style>

<div class="welcome-banner">
    <h1>Selamat Datang di Sistem ERP</h1>
    <p>Sistem Manajemen Pembelian & Logistik Terpadu</p>
</div>

<h3 style="margin-bottom: 20px;">Menu Utama & Ringkasan</h3>

<div class="dashboard-grid">
    <a href="supplier.php" class="menu-card">
        <div class="icon-box">S</div>
        <h4>Suppliers</h4>
        <div class="stat-number"><?php echo $count_supplier; ?></div>
        <p style="font-size: 12px; color: #888;">Kelola Mitra Vendor</p>
    </a>

    <a href="product.php" class="menu-card">
        <div class="icon-box">P</div>
        <h4>Products</h4>
        <div class="stat-number"><?php echo $count_product; ?></div>
        <p style="font-size: 12px; color: #888;">Inventaris Barang</p>
    </a>

    <a href="purchase.php" class="menu-card">
        <div class="icon-box">O</div>
        <h4>Purchasing</h4>
        <div class="stat-number"><?php echo $count_po; ?></div>
        <p style="font-size: 12px; color: #888;">Order Pembelian</p>
    </a>

    <a href="invoice.php" class="menu-card">
        <div class="icon-box">I</div>
        <h4>Invoices</h4>
        <div class="stat-number">Rp <?php echo number_format($total_inv ?? 0, 0, ',', '.'); ?></div>
        <p style="font-size: 12px; color: #888;">Total Tagihan Vendor</p>
    </a>
</div>

<?php echo "</div></body></html>"; ?>