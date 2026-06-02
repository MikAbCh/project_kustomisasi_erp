<?php 
include 'config.php'; 

// LOGIKA VALIDASI PENERIMAAN DARI PO
if (isset($_POST['validate_po_receipt'])) {
    $id_po       = $_POST['fk_purchase'];
    $id_loc      = $_POST['id_lokasi'];
    $tgl_masuk   = date('Y-m-d H:i:s');

    mysqli_begin_transaction($conn);
    try {
        // 1. Ambil semua baris produk dari PO tersebut
        $lines = mysqli_query($conn, "SELECT * FROM transaksi_purchase_line WHERE fk_purchase = $id_po");
        
        // Ambil ID Supplier dari header PO
        $po_header = mysqli_fetch_assoc(mysqli_query($conn, "SELECT fk_supplier FROM transaksi_purchase WHERE id_purchase = $id_po"));
        $id_supp = $po_header['fk_supplier'];

        while ($item = mysqli_fetch_assoc($lines)) {
            $id_prod = $item['fk_product'];
            $qty     = $item['qty'];

            // 2. Insert ke Barang Masuk (Recording the Move)
            mysqli_query($conn, "INSERT INTO barang_masuk (id_product, id_supplier, id_lokasi, qty_masuk, tanggal_masuk, keterangan) 
                                 VALUES ('$id_prod', '$id_supp', '$id_loc', '$qty', '$tgl_masuk', 'Received from PO #$id_po')");
            
            // 3. Update Stok Fisik di Tabel Product
            mysqli_query($conn, "UPDATE product SET stok_aktual = stok_aktual + $qty WHERE id_product = $id_prod");
        }

        // 4. Update Status PO menjadi 'done' agar tidak diinput dua kali (Prinsip ERP)
        mysqli_query($conn, "UPDATE transaksi_purchase SET status_dokumen = 'done' WHERE id_purchase = $id_po");

        mysqli_commit($conn);
        header("Location: barangmasuk.php?msg=received");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    }
}

include 'header.php'; 
?>

<div class="header-bar">
    <h1><i class="fa fa-download"></i> Incoming Receipts</h1>
    <button onclick="toggleForm()" class="btn-orange">Receive from Purchase Order</button>
</div>

<!-- FORM PENERIMAAN BERDASARKAN PO -->
<div id="form-po" class="card" style="display:none; margin-bottom: 25px; border: 2px solid #ef7d00;">
    <h3>Select Purchase Order to Receive</h3>
    <form method="POST" style="margin-top: 15px;">
        <div style="display: grid; grid-template-columns: 2fr 2fr 1fr; gap: 15px;">
            <div>
                <label>Source Document (PO)</label>
                <select name="fk_purchase" class="form-control" required style="width:100%; padding:8px;">
                    <option value="">-- Select Confirmed PO --</option>
                    <?php 
                    // Hanya PO yang berstatus 'purchase' atau 'sent' yang bisa diterima (Bukan Draft / Done)
                    $po_list = mysqli_query($conn, "SELECT p.id_purchase, s.nama_perusahaan, p.total_keseluruhan 
                                                   FROM transaksi_purchase p 
                                                   JOIN supplier s ON p.fk_supplier = s.id_supplier 
                                                   WHERE p.status_dokumen IN ('purchase', 'sent')");
                    while($po = mysqli_fetch_assoc($po_list)) {
                        echo "<option value='{$po['id_purchase']}'>PO#{$po['id_purchase']} - {$po['nama_perusahaan']} (Total: ".number_format($po['total_keseluruhan']).")</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label>Destination Location</label>
                <select name="id_lokasi" class="form-control" required style="width:100%; padding:8px;">
                    <?php 
                    $l_list = mysqli_query($conn, "SELECT id_lokasi, nama_gudang, blok_rak FROM lokasi");
                    while($l = mysqli_fetch_assoc($l_list)) echo "<option value='{$l['id_lokasi']}'>{$l['nama_gudang']} - {$l['blok_rak']}</option>";
                    ?>
                </select>
            </div>
            <div style="align-self: end;">
                <button type="submit" name="validate_po_receipt" class="btn-orange" style="width: 100%;">Validate Receipt</button>
            </div>
        </div>
    </form>
</div>

<!-- TABEL HISTORI MASUK (SAMA SEPERTI SEBELUMNYA) -->
<div class="card">
    <table class="table-odoo">
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Source</th>
                <th>Location</th>
                <th>Qty Done</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = mysqli_query($conn, "SELECT bm.*, p.nama_product, l.nama_gudang FROM barang_masuk bm 
                                       JOIN product p ON bm.id_product = p.id_product 
                                       JOIN lokasi l ON bm.id_lokasi = l.id_lokasi ORDER BY id_masuk DESC");
            while($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?= $row['tanggal_masuk'] ?></td>
                <td><strong><?= $row['nama_product'] ?></strong></td>
                <td><?= $row['keterangan'] ?></td>
                <td><?= $row['nama_gudang'] ?></td>
                <td style="color:green; font-weight:bold;"> <?= $row['qty_masuk'] ?></td>
                <td><span class="badge bg-posted">DONE</span></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
function toggleForm() {
    var x = document.getElementById("form-po");
    x.style.display = (x.style.display === "none") ? "block" : "none";
}
</script>