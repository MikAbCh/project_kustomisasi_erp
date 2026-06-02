<?php 
include 'config.php'; 

// 1. LOGIKA DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM transaksi_sales_line WHERE fk_sales = $id");
    mysqli_query($conn, "DELETE FROM transaksi_sales WHERE id_sales = $id");
    header("Location: sales.php?msg=deleted");
    exit;
}

// 2. LOGIKA SAVE (Create & Update)
if (isset($_POST['save_sales'])) {
    $id_s          = $_POST['id_sales'];
    $customer_name = $_POST['customer_input'];
    
    // Cari ID Customer berdasarkan nama dari datalist
    $res_c         = mysqli_query($conn, "SELECT id_customer FROM customer WHERE nama_pelanggan = '$customer_name'");
    $c_data        = mysqli_fetch_assoc($res_c);
    $customer_id   = $c_data ? $c_data['id_customer'] : 0;

    $tgl_order    = $_POST['tanggal_order'];
    $status       = $_POST['status_dokumen'];
    $persen_ppn   = $_POST['persen_ppn'];

    // Hitung Total dari lines
    $total_subtotal = 0;
    foreach ($_POST['qty'] as $key => $qty) {
        $total_subtotal += ($qty * $_POST['harga_satuan'][$key]);
    }
    $ppn       = $total_subtotal * ($persen_ppn / 100);
    $total_all = $total_subtotal + $ppn;

    if (empty($id_s)) {
        // Simpan Header Baru
        $sql_h = "INSERT INTO transaksi_sales (fk_customer, tanggal_order, status_dokumen, total_sebelum_pajak, pajak_ppn, total_keseluruhan) 
                  VALUES ('$customer_id', '$tgl_order', '$status', '$total_subtotal', '$ppn', '$total_all')";
        mysqli_query($conn, $sql_h);
        $id_s = mysqli_insert_id($conn);
    } else {
        // Update Header
        $sql_h = "UPDATE transaksi_sales SET 
                  fk_customer='$customer_id', tanggal_order='$tgl_order', status_dokumen='$status', 
                  total_sebelum_pajak='$total_subtotal', pajak_ppn='$ppn', total_keseluruhan='$total_all' 
                  WHERE id_sales=$id_s";
        mysqli_query($conn, $sql_h);
        
        // Bersihkan lines lama untuk di-insert ulang
        mysqli_query($conn, "DELETE FROM transaksi_sales_line WHERE fk_sales=$id_s");
    }

    // Simpan Lines (Looping)
    foreach ($_POST['fk_product'] as $key => $prod_id) {
        if (!empty($prod_id)) {
            $q   = $_POST['qty'][$key];
            $h   = $_POST['harga_satuan'][$key];
            $sub = $q * $h;
            $sql_l = "INSERT INTO transaksi_sales_line (fk_sales, fk_product, qty, harga_satuan, subtotal) 
                      VALUES ('$id_s', '$prod_id', '$q', '$h', '$sub')";
            mysqli_query($conn, $sql_l);
        }
    }
    header("Location: sales.php?msg=success");
    exit;
}

include 'header.php'; 

// 3. AMBIL DATA UNTUK EDIT
$val = ['id_sales'=>'','nama_pelanggan'=>'','tanggal_order'=>date('Y-m-d'),'status_dokumen'=>'draft','pajak_ppn'=>0];
$lines = []; 
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT h.*, c.nama_pelanggan FROM transaksi_sales h LEFT JOIN customer c ON h.fk_customer = c.id_customer WHERE h.id_sales = $id");
    $val = mysqli_fetch_assoc($res);
    $res_l = mysqli_query($conn, "SELECT * FROM transaksi_sales_line WHERE fk_sales = $id");
    while($l = mysqli_fetch_assoc($res_l)) $lines[] = $l;
}
?>

<div class="header-bar">
    <h1>Sales Orders / Quotations</h1>
    <button onclick="toggleForm()" class="btn-orange"><?= isset($_GET['edit']) ? 'Editing SO: #'.$val['id_sales'] : '+ Create New SO' ?></button>
</div>

<div id="form-sales" class="card" style="display: <?= isset($_GET['edit']) ? 'block' : 'none' ?>; margin-bottom: 25px;">
    <form method="POST">
        <input type="hidden" name="id_sales" value="<?= $val['id_sales'] ?>">
        
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 100px; gap: 20px; align-items: end;">
            <div>
                <label><strong>Customer</strong></label>
                <input list="customer_list" name="customer_input" style="width:100%; padding:8px;" value="<?= $val['nama_pelanggan'] ?>" placeholder="Cari Customer..." required>
                <datalist id="customer_list">
                    <?php 
                    $c_list = mysqli_query($conn, "SELECT nama_pelanggan FROM customer");
                    while($c = mysqli_fetch_assoc($c_list)) echo "<option value='{$c['nama_pelanggan']}'>";
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
                    <?php 
                    $opts = ['draft','sent','sale','done','cancel']; 
                    foreach($opts as $o) {
                        $sel = ($o == $val['status_dokumen']) ? 'selected' : '';
                        $label_status = ($o == 'sale') ? 'Sales Order' : ucfirst($o);
                        echo "<option value='$o' $sel>".$label_status."</option>";
                    } ?>
                </select>
            </div>
            <div>
                <label><strong>VAT %</strong></label>
                <input type="number" name="persen_ppn" style="width:100%; padding:8px;" value="11">
            </div>
        </div>

        <div style="margin-top: 20px; border-top: 2px solid #ef7d00; padding-top: 15px;">
            <h4 style="margin-bottom: 15px;">Sales Lines</h4>
            <div id="line-container">
                <?php 
                if (empty($lines)) $lines[] = ['fk_product'=>'','qty'=>'','harga_satuan'=>'']; 
                foreach($lines as $ln): ?>
                <div class="order-line" style="display: grid; grid-template-columns: 2fr 1fr 1fr 50px; gap: 10px; margin-bottom: 10px;">
                    <select name="fk_product[]" style="padding:8px;" required>
                        <option value="">-- Select Product --</option>
                        <?php 
                        $p_list = mysqli_query($conn, "SELECT id_product, nama_product, harga_jual FROM product");
                        while($p = mysqli_fetch_assoc($p_list)) {
                            $sel = ($p['id_product'] == $ln['fk_product']) ? 'selected' : '';
                            echo "<option value='{$p['id_product']}' $sel>{$p['nama_product']} (Rp ".number_format($p['harga_jual'],0,',','.').")</option>";
                        }
                        ?>
                    </select>
                    <input type="number" name="qty[]" placeholder="Qty" style="padding:8px;" value="<?= $ln['qty'] ?>" required>
                    <input type="number" name="harga_satuan[]" placeholder="Selling Price" style="padding:8px;" value="<?= $ln['harga_satuan'] ?>" required>
                    <button type="button" onclick="this.parentElement.remove()" style="background:red; color:white; border:none; border-radius:4px; cursor:pointer;">X</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" onclick="addItem()" class="btn-orange" style="background:#555; padding: 5px 15px; font-size: 12px;">+ Add Product Line</button>
        </div>

        <div style="margin-top: 25px;">
            <button type="submit" name="save_sales" class="btn-orange">Save Sales Order</button>
            <a href="sales.php" class="btn-orange" style="background:#888; text-decoration:none;">Discard</a>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>SO Reference</th>
                <th>Customer</th>
                <th>Date</th>
                <th style="text-align:right">Total Amount</th>
                <th>Status</th>
                <th style="text-align:center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT p.*, c.nama_pelanggan FROM transaksi_sales p LEFT JOIN customer c ON p.fk_customer = c.id_customer ORDER BY p.id_sales DESC";
            $res = mysqli_query($conn, $sql);
            while($row = mysqli_fetch_assoc($res)) {
                $status = $row['status_dokumen'];
                
                $st_class = 'bg-draft';
                if($status == 'sale') $st_class = 'bg-orange'; // atau class warna penanda konfirmasi sales
                if($status == 'done') $st_class = 'bg-posted';
                if($status == 'cancel') $st_class = 'bg-cancel';
                
                echo "<tr>
                    <td><strong>SO/".date('Y', strtotime($row['tanggal_order']))."/".str_pad($row['id_sales'], 4, '0', STR_PAD_LEFT)."</strong></td>
                    <td>".($row['nama_pelanggan'] ?: '<em style="color:red;">Customer Deleted</em>')."</td>
                    <td>{$row['tanggal_order']}</td>
                    <td style='text-align:right; font-weight:bold;'>IDR ".number_format($row['total_keseluruhan'], 0, ',', '.')."</td>
                    <td><span class='badge $st_class'>".strtoupper($status)."</span></td>
                    <td style='text-align:center'>
                        <a href='sales.php?edit={$row['id_sales']}' style='color:#ef7d00; text-decoration:none; font-weight:bold;'>Edit</a> | 
                        <a href='sales.php?delete={$row['id_sales']}' style='color:#d9534f; text-decoration:none; font-weight:bold;' onclick='return confirm(\"Hapus dokumen SO ini?\")'>Delete</a>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
function toggleForm() {
    var x = document.getElementById("form-sales");
    x.style.display = (x.style.display === "none") ? "block" : "none";
}

function addItem() {
    const container = document.getElementById('line-container');
    const lines = container.getElementsByClassName('order-line');
    if(lines.length > 0) {
        const newLine = lines[0].cloneNode(true);
        newLine.querySelectorAll('input').forEach(input => input.value = '');
        newLine.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        container.appendChild(newLine);
    }
}
</script>

<?php echo "</div></body></html>"; ?>