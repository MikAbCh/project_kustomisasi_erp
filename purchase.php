<?php 
include 'config.php'; 
include 'header.php'; 

// 1. LOGIKA DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM transaksi_purchase_line WHERE fk_purchase = $id");
    mysqli_query($conn, "DELETE FROM transaksi_purchase WHERE id_purchase = $id");
    header("Location: purchase.php?msg=deleted");
}

// 2. LOGIKA SAVE (Create & Update)
if (isset($_POST['save_purchase'])) {
    $id           = $_POST['id_product_hidden']; // ID Hidden
    $supplier_name = $_POST['supplier_input'];
    
    // Cari ID Supplier berdasarkan Nama (Karena kita pakai datalist/suggest)
    $res_s = mysqli_query($conn, "SELECT id_supplier FROM supplier WHERE nama_perusahaan = '$supplier_name'");
    $s_data = mysqli_fetch_assoc($res_s);
    $supplier_id = $s_data['id_supplier'];

    $tgl_order    = $_POST['tanggal_order'];
    $status       = $_POST['status_dokumen'];
    $product_id   = $_POST['fk_product'];
    $qty          = $_POST['qty'];
    $harga        = $_POST['harga_satuan'];
    $persen_ppn   = $_POST['persen_ppn'];

    $subtotal     = $qty * $harga;
    $ppn          = $subtotal * ($persen_ppn / 100);
    $total_all    = $subtotal + $ppn;

    if (empty($_POST['id_purchase'])) {
        mysqli_query($conn, "INSERT INTO transaksi_purchase (fk_supplier, tanggal_order, status_dokumen, total_sebelum_pajak, pajak_ppn, total_keseluruhan) 
                            VALUES ('$supplier_id', '$tgl_order', '$status', '$subtotal', '$ppn', '$total_all')");
        $new_id = mysqli_insert_id($conn);
        mysqli_query($conn, "INSERT INTO transaksi_purchase_line (fk_purchase, fk_product, qty, harga_satuan, subtotal) 
                            VALUES ('$new_id', '$product_id', '$qty', '$harga', '$subtotal')");
    } else {
        $id_p = $_POST['id_purchase'];
        mysqli_query($conn, "UPDATE transaksi_purchase SET fk_supplier='$supplier_id', tanggal_order='$tgl_order', status_dokumen='$status', 
                            total_sebelum_pajak='$subtotal', pajak_ppn='$ppn', total_keseluruhan='$total_all' WHERE id_purchase=$id_p");
        mysqli_query($conn, "UPDATE transaksi_purchase_line SET fk_product='$product_id', qty='$qty', harga_satuan='$harga', subtotal='$subtotal' WHERE fk_purchase=$id_p");
    }
    header("Location: purchase.php?msg=success");
}

$val = ['id_purchase'=>'','nama_perusahaan'=>'','tanggal_order'=>'','status_dokumen'=>'draft','fk_product'=>'','qty'=>'','harga_satuan'=>''];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT h.*, s.nama_perusahaan, l.fk_product, l.qty, l.harga_satuan FROM transaksi_purchase h 
                                JOIN supplier s ON h.fk_supplier = s.id_supplier
                                JOIN transaksi_purchase_line l ON h.id_purchase = l.fk_purchase WHERE h.id_purchase = $id");
    $val = mysqli_fetch_assoc($res);
}
?>

<div class="header-bar">
    <h1>Purchase Orders</h1>
    <button onclick="toggleForm()" class="btn-orange"><?= isset($_GET['edit']) ? 'Edit Mode' : '+ Create PO' ?></button>
</div>

<div id="form-purchase" class="card" style="display: <?= isset($_GET['edit']) ? 'block' : 'none' ?>; margin-bottom: 25px;">
    <form method="POST">
        <input type="hidden" name="id_purchase" value="<?= $val['id_purchase'] ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div>
                <label><strong>Supplier Name (Type to search)</strong></label>
                <input list="supplier_list" name="supplier_input" class="form-control" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['nama_perusahaan'] ?>" placeholder="Search supplier..." required>
                <datalist id="supplier_list">
                    <?php 
                    $s_list = mysqli_query($conn, "SELECT nama_perusahaan FROM supplier");
                    while($s = mysqli_fetch_assoc($s_list)) echo "<option value='{$s['nama_perusahaan']}'>";
                    ?>
                </datalist>
            </div>

            <div>
                <label><strong>Order Date</strong></label>
                <input type="date" name="tanggal_order" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['tanggal_order'] ?>" required>
            </div>

            <div>
                <label><strong>Document Status</strong></label>
                <select name="status_dokumen" style="width:100%; padding:8px; margin-top:5px;">
                    <?php $opts = ['draft','sent','purchase','done']; 
                    foreach($opts as $o) {
                        $sel = ($o == $val['status_dokumen']) ? 'selected' : '';
                        echo "<option value='$o' $sel>".ucfirst($o)."</option>";
                    } ?>
                </select>
            </div>

            <div style="grid-column: span 3; border-top: 2px solid #ef7d00; padding-top: 15px; margin-top: 10px;">
                <h4 style="margin-bottom: 10px;">Order Lines Detail</h4>
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px;">
                    <div>
                        <label><small>Select Product</small></label>
                        <select name="fk_product" style="width:100%; padding:8px; margin-top:5px;" required>
                            <option value="">-- Choose Product --</option>
                            <?php 
                            $p_list = mysqli_query($conn, "SELECT id_product, nama_product FROM product");
                            while($p = mysqli_fetch_assoc($p_list)) {
                                $sel = ($p['id_product'] == $val['fk_product']) ? 'selected' : '';
                                echo "<option value='{$p['id_product']}' $sel>{$p['nama_product']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label><small>Quantity (Qty)</small></label>
                        <input type="number" name="qty" style="width:100%; padding:8px; margin-top:5px;" placeholder="e.g. 10" value="<?= $val['qty'] ?>" required>
                    </div>
                    <div>
                        <label><small>Unit Price (IDR)</small></label>
                        <input type="number" name="harga_satuan" style="width:100%; padding:8px; margin-top:5px;" placeholder="e.g. 50000" value="<?= $val['harga_satuan'] ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <label><strong>Pajak PPN (%)</strong></label>
            <input type="number" name="persen_ppn" style="width:100%; padding:8px; margin-top:5px;" value="11" min="0">
        </div>

        <div style="margin-top: 25px;">
            <button type="submit" name="save_purchase" class="btn-orange">Confirm & Save PO</button>
            <a href="purchase.php" class="btn-orange" style="background:#888; text-decoration:none;">Cancel</a>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>PO Reference</th>
                <th>Supplier</th>
                <th>Date</th>
                <th style="text-align:right">Total (Incl. PPN)</th>
                <th>Status</th>
                <th style="text-align:center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT p.*, s.nama_perusahaan FROM transaksi_purchase p 
                    JOIN supplier s ON p.fk_supplier = s.id_supplier ORDER BY p.id_purchase DESC";
            $res = mysqli_query($conn, $sql);
            while($row = mysqli_fetch_assoc($res)) {
                $st_class = ($row['status_dokumen'] == 'purchase') ? 'bg-posted' : 'bg-draft';
                echo "<tr>
                    <td><strong>PO/2026/".str_pad($row['id_purchase'], 3, '0', STR_PAD_LEFT)."</strong></td>
                    <td>{$row['nama_perusahaan']}</td>
                    <td>{$row['tanggal_order']}</td>
                    <td style='text-align:right'>IDR ".number_format($row['total_keseluruhan'], 0, ',', '.')."</td>
                    <td><span class='badge $st_class'>".strtoupper($row['status_dokumen'])."</span></td>
                    <td style='text-align:center'>
                        <a href='purchase.php?edit={$row['id_purchase']}' style='color:#ef7d00; text-decoration:none;'>Edit</a> | 
                        <a href='purchase.php?delete={$row['id_purchase']}' style='color:red; text-decoration:none;' onclick='return confirm(\"Hapus PO ini?\")'>Delete</a>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
function toggleForm() {
    var x = document.getElementById("form-purchase");
    x.style.display = (x.style.display === "none") ? "block" : "none";
}
</script>

<?php echo "</div></body></html>"; ?>