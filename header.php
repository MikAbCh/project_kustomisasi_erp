<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Orange ERP - Local System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        /* BODY STANDAR: Menghapus display: flex agar konten tidak dipaksa berjejer ke kanan */
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background-color: #f4f7f6; 
            color: #333;
            display: block; 
        }

        /* SIDEBAR FIX: Menggunakan posisi fixed absolut di kiri agar tidak mendorong elemen lain */
        .sidebar { 
            width: 180px; 
            background-color: #2c3e50; 
            height: 100vh;                      
            position: fixed; 
            top: 0;
            left: 0;
            color: white; 
            overflow-y: auto;       
            z-index: 1000;
            
            /* Mengunci isi internal menu agar mengalir dari atas ke bawah */
            display: flex;
            flex-direction: column; 
        }
        
        /* Kustomisasi scrollbar internal milik sidebar */
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-track { background: #2c3e50; }
        .sidebar::-webkit-scrollbar-thumb { background: #ef7d00; border-radius: 3px; }
        .sidebar::-webkit-scrollbar-thumb:hover { background: #d67000; }

        .sidebar h2 { padding: 15px 10px; font-size: 1.2rem; color: #ef7d00; text-align: center; border-bottom: 1px solid #34495e; }
        .sidebar a { display: block; color: #ced4da; padding: 15px 20px; text-decoration: none; font-size: 14px; transition: 0.3s; }
        .sidebar a:hover { background-color: #ef7d00; color: white; padding-left: 25px; }
        .sidebar a.active { background-color: #ef7d00; color: white; border-left: 5px solid #fff; }

        /* KELOMPOK SUB-MENU (TETAP AMAN VERTIKAL) */
        .sidebar .submenu {
            background-color: #1e2b37; 
            display: flex;
            flex-direction: column; 
        }
        .sidebar .submenu a {
            padding-left: 35px; 
            font-size: 13px;                
            border-bottom: 1px solid #2c3e50;
        }
        .sidebar .submenu a:hover { padding-left: 45px; }

        /* MAIN CONTENT BLOCK: Menggunakan display: block murni */
        /* Ini menjamin 100% semua judul, form, dan tabel Anda mengalir normal dari ATAS KE BAWAH */
        .main-content { 
            margin-left: 180px; /* Memberi ruang agar tidak tertutup sidebar di kiri */
            width: calc(100% - 180px); 
            padding: 30px; 
            min-height: 100vh;
            display: block; 
        }
                
        /* UI COMPONENTS BAWAAN HALAMAN ANDA */
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .card { background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 3px solid #ef7d00; margin-bottom: 25px; }
        table, .table-odoo { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        table th, .table-odoo th { background-color: #f8f9fa; text-align: left; padding: 12px; border-bottom: 2px solid #eee; color: #666; }
        table td, .table-odoo td { padding: 12px; border-bottom: 1px solid #f1f1f1; }
        table tr:hover, .table-odoo tr:hover { background-color: #fffaf5; }

        /* BUTTONS */
        .btn-orange { background-color: #ef7d00; color: white; padding: 10px 18px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; font-size: 13px; font-weight: bold; }
        .btn-orange:hover { background-color: #d67000; }
                
        /* STATUS BADGES */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>ERP System</h2>
        <a href="index.php" style="border-bottom: 2px solid #1a252f; font-weight: bold;">🏠 Dashboard</a>
                
        <div style="padding: 10px 15px; font-size: 14px; color: #ef7d00; font-weight: bold; background: #1a252f;">
            🛒 Purchasing
        </div>
        <div class="submenu">
            <a href="purchase.php">Purchase Orders</a>
            <a href="supplier.php">Suppliers</a>
            <a href="product.php">Products</a>
        </div>
        
       <div style="padding: 10px 15px; font-size: 14px; color: #ef7d00; font-weight: bold; background: #1a252f;">
            Finance
        </div>
        <div class="submenu">
            <a href="invoice.php">Invoices</a>
            <a href="payment.php">Payments</a>
             <a href="reporting.php">Reporting</a>
        </div>
                
        <div style="padding: 10px 15px; font-size: 14px; color: #ef7d00; font-weight: bold; background: #1a252f;">
            📦 Inventory
        </div>
        <div class="submenu">
            <a href="barangmasuk.php">Barang Masuk</a>
            <a href="lokasi.php">Lokasi</a>
            <a href="barangkeluar.php">Barang Keluar</a>
        </div>
                
        <div style="padding: 10px 15px; font-size: 14px; color: #ef7d00; font-weight: bold; background: #1a252f;">
            Sales
        </div>
        <div class="submenu">
            <a href="customer.php">Customer</a>
            <a href="sales.php">Sales Order</a>
        </div>
    </div>

    <div class="main-content">