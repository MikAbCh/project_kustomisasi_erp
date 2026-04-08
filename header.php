<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Orange ERP - Local System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f7f6; display: flex; color: #333; }

        /* SIDEBAR ODOO STYLE */
        .sidebar { width: 220px; background-color: #2c3e50; min-height: 100vh; position: fixed; color: white; }
        .sidebar h2 { padding: 25px 20px; font-size: 1.2rem; color: #ef7d00; text-align: center; border-bottom: 1px solid #34495e; }
        .sidebar a { display: block; color: #ced4da; padding: 15px 20px; text-decoration: none; font-size: 14px; transition: 0.3s; }
        .sidebar a:hover { background-color: #ef7d00; color: white; padding-left: 30px; }
        .sidebar a.active { background-color: #ef7d00; color: white; border-left: 5px solid #fff; }

        /* CONTENT AREA */
        .main-content { margin-left: 220px; width: calc(100% - 220px); padding: 30px; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        
        /* UI COMPONENTS */
        .card { background: white; padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 3px solid #ef7d00; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        table th { background-color: #f8f9fa; text-align: left; padding: 12px; border-bottom: 2px solid #eee; color: #666; }
        table td { padding: 12px; border-bottom: 1px solid #f1f1f1; }
        table tr:hover { background-color: #fffaf5; }

        /* BUTTONS */
        .btn-orange { background-color: #ef7d00; color: white; padding: 10px 18px; border: none; border-radius: 3px; cursor: pointer; text-decoration: none; font-size: 13px; font-weight: bold; }
        .btn-orange:hover { background-color: #d67000; }
        
        /* STATUS BADGES */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; }
        .bg-draft { background: #e9ecef; color: #495057; }
        .bg-purchase { background: #d4edda; color: #155724; }
        .bg-paid { background: #cce5ff; color: #004085; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>ERP System</h2>
        <a href="index.php" style="border-bottom: 2px solid #1a252f; font-weight: bold;">🏠 Dashboard</a>
        <a href="supplier.php">Suppliers</a>
        <a href="product.php">Products</a>
        <a href="purchase.php">Purchasing</a>
        <a href="invoice.php">Invoices</a>
        <a href="payment.php">Payments</a>
    </div>
    <div class="main-content">