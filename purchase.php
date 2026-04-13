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

// 2. LOGIKA SAVE (Multi-Product Support)
if (isset($_POST['save_purchase'])) {
    $supplier_name = $_POST['supplier_input'];
    $res_s = mysqli_query($conn, "SELECT id_supplier FROM supplier WHERE nama_perusahaan = '$supplier_name'");
    $s_data = mysqli_fetch_assoc($res_s);
    $supplier_id = $s_data['id_supplier'];

    $tgl_order    = $_POST['tanggal_order'];
    $status       = $_POST['status_dokumen'];
    $persen_ppn   = $_POST['persen_ppn'];

    // Menghitung Total dari semua baris produk
    $total_subtotal = 0;
    foreach ($_POST['qty'] as $key => $qty) {
        $total_subtotal += ($qty * $_POST['harga_satuan'][$key]);
    }

    $ppn       = $total_subtotal * ($persen_ppn / 100);
    $total_all = $total_subtotal + $ppn;

    if (empty($_POST['id_purchase'])) {
        // Simpan Header
        mysqli_query($conn, "INSERT INTO transaksi_purchase (fk_supplier, tanggal_order, status_dokumen, total_sebelum_pajak, pajak_ppn, total_keseluruhan) 
                            VALUES ('$supplier_id', '$tgl_order', '$status', '$total_subtotal', '$ppn', '$total_all')");
        $new_id = mysqli_insert_id($conn);

        // Simpan Lines (Looping)
        foreach ($_POST['fk_product'] as $key => $prod_id) {
            $q = $_POST['qty'][$key];
            $h = $_POST['harga_satuan'][$key];
            $sub = $q * $h;
            mysqli_query($conn, "INSERT INTO transaksi_purchase_line (fk_purchase, fk_product, qty, harga_satuan, subtotal) 
                                VALUES ('$new_id', '$prod_id', '$q', '$h', '$sub')");
        }
    } else {
        $id_p = $_POST['id_purchase'];
        // Update Header
        mysqli_query($conn, "UPDATE transaksi_purchase SET fk_supplier='$supplier_id', tanggal_order='$tgl_order', status_dokumen='$status', 
                            total_sebelum_pajak='$total_subtotal', pajak_ppn='$ppn', total_keseluruhan='$total_all' WHERE id_purchase=$id_p");
        
        // Update Lines: Hapus baris lama lalu insert ulang agar sinkron
        mysqli_query($conn, "DELETE FROM transaksi_purchase_line WHERE fk_purchase=$id_p");
        foreach ($_POST['fk_product'] as $key => $prod_id) {
            $q = $_POST['qty'][$key];
            $h = $_POST['harga_satuan'][$key];
            $sub = $q * $h;
            mysqli_query($conn, "INSERT INTO transaksi_purchase_line (fk_purchase, fk_product, qty, harga_satuan, subtotal) 
                                VALUES ('$id_p', '$prod_id', '$q', '$h', '$sub')");
        }
    }
    header("Location: purchase.php?msg=success");
}

$val = ['id_purchase'=>'','nama_perusahaan'=>'','tanggal_order'=>date('Y-m-d'),'status_dokumen'=>'draft'];
$lines = []; 
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT h.*, s.nama_perusahaan FROM transaksi_purchase h JOIN supplier s ON h.fk_supplier = s.id_supplier WHERE h.id_purchase = $id");
    $val = mysqli_fetch_assoc($res);
    $res_l = mysqli_query($conn, "SELECT * FROM transaksi_purchase_line WHERE fk_purchase = $id");
    while($l = mysqli_fetch_assoc($res_l)) $lines[] = $l;
}
?>

<div class="header-bar">
    <h1>Purchase Orders</h1>
    <button onclick="toggleForm()" class="btn-orange"><?= isset($_GET['edit']) ? 'Edit Mode' : '+ Create PO' ?></button>
</div>

<div id="form-purchase" class="card" style="display: <?= isset($_GET['edit']) ? 'block' : 'none' ?>; margin-bottom: 25px;">
    <form method="POST">
        <input type="hidden" name="id_purchase" value="<?= $val['id_purchase'] ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 100px; gap: 20px; align-items: end;">
            <div>
                <label><strong>Supplier</strong></label>
                <input list="supplier_list" name="supplier_input" style="width:100%; padding:8px;" value="<?= $val['nama_perusahaan'] ?>" placeholder="Search..." required>
                <datalist id="supplier_list">
                    <?php 
                    $s_list = mysqli_query($conn, "SELECT nama_perusahaan FROM supplier");
                    while($s = mysqli_fetch_assoc($s_list)) echo "<option value='{$s['nama_perusahaan']}'>";
                    ?>
                </datalist>
            </div>
            <div>
                <label><strong>Order Date</strong></label>
                <input type="date" name="tanggal_order" style="width:100%; padding:8px;" value="<?= $val['tanggal_order'] ?>" required>
            </div>
            <div>
                <label><strong>Status</strong></label>
                <select name="status_dokumen" style="width:100%; padding:8px;">
                    <?php $opts = ['draft','sent','purchase','done']; 
                    foreach($opts as $o) {
                        $sel = ($o == $val['status_dokumen']) ? 'selected' : '';
                        echo "<option value='$o' $sel>".ucfirst($o)."</option>";
                    } ?>
                </select>
            </div>
            <div>
                <label><strong>Tax %</strong></label>
                <input type="number" name="persen_ppn" style="width:100%; padding:8px;" value="11" min="0">
            </div>
        </div>

        <div style="margin-top: 20px; border-top: 2px solid #ef7d00; padding-top: 15px;">
            <h4 style="margin-bottom: 15px;">Order Lines</h4>
            <div id="line-container">
                <?php 
                $p_options = "";
                $p_list = mysqli_query($conn, "SELECT id_product, nama_product FROM product");
                while($p = mysqli_fetch_assoc($p_list)) $p_options .= "<option value='{$p['id_product']}'>{$p['nama_product']}</option>";

                if (empty($lines)) $lines[] = ['fk_product'=>'','qty'=>'','harga_satuan'=>'']; // Default 1 baris kosong
                foreach($lines as $index => $ln): ?>
                <div class="order-line" style="display: grid; grid-template-columns: 2fr 1fr 1fr 50px; gap: 10px; margin-bottom: 10px;">
                    <select name="fk_product[]" style="padding:8px;" required>
                        <option value="">-- Product --</option>
                        <?php 
                        $p_list = mysqli_query($conn, "SELECT id_product, nama_product FROM product");
                        while($p = mysqli_fetch_assoc($p_list)) {
                            $sel = ($p['id_product'] == $ln['fk_product']) ? 'selected' : '';
                            echo "<option value='{$p['id_product']}' $sel>{$p['nama_product']}</option>";
                        }
                        ?>
                    </select>
                    <input type="number" name="qty[]" placeholder="Qty" style="padding:8px;" value="<?= $ln['qty'] ?>" required>
                    <input type="number" name="harga_satuan[]" placeholder="Price" style="padding:8px;" value="<?= $ln['harga_satuan'] ?>" required>
                    <?php if($index > 0): ?>
                        <button type="button" onclick="this.parentElement.remove()" style="background:red; color:white; border:none; border-radius:4px; cursor:pointer;">X</button>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" onclick="addItem()" class="btn-orange" style="background:#555; padding: 5px 15px; font-size: 12px;">+ Add Item</button>
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
            $sql = "SELECT p.*, s.nama_perusahaan FROM transaksi_purchase p JOIN supplier s ON p.fk_supplier = s.id_supplier ORDER BY p.id_purchase DESC";
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

function addItem() {
    const container = document.getElementById('line-container');
    const firstLine = container.querySelector('.order-line');
    const newLine = firstLine.cloneNode(true);
    
    // Reset values in new line
    newLine.querySelectorAll('input').forEach(input => input.value = '');
    newLine.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    
    // Add remove button to new line
    const removeBtn = document.createElement('button');
    removeBtn.type = "button";
    removeBtn.innerText = "X";
    removeBtn.style = "background:red; color:white; border:none; border-radius:4px; cursor:pointer;";
    removeBtn.onclick = function() { this.parentElement.remove(); };
    
    newLine.replaceChild(removeBtn, newLine.lastElementChild);
    container.appendChild(newLine);
}
</script>

<?php echo "</div></body></html>"; ?>