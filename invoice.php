<?php 
include 'config.php'; 

// 1. LOGIKA DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (mysqli_query($conn, "DELETE FROM invoice WHERE id_invoice = $id")) {
        header("Location: invoice.php?msg=deleted");
    }
}

// 2. LOGIKA SAVE (Create & Update)
if (isset($_POST['save_invoice'])) {
    $id             = $_POST['id_invoice'];
    $jenis_invoice  = $_POST['jenis_invoice'];
    $no_vendor      = mysqli_real_escape_string($conn, $_POST['nomor_invoice_vendor']);
    $tgl_inv        = $_POST['tanggal_invoice'];
    $tgl_due        = $_POST['tanggal_jatuh_tempo'];
    $status_inv     = $_POST['status_invoice'];
    $status_pay     = $_POST['status_pembayaran'];
    
    // Penentuan FK dinamis berdasarkan jenis invoice
    if ($jenis_invoice == 'vendor') {
        $fk_purchase = $_POST['fk_purchase'];
        $fk_sales    = "NULL";
        
        // Ambil total tagihan otomatis dari PO
        $res_po = mysqli_query($conn, "SELECT total_keseluruhan FROM transaksi_purchase WHERE id_purchase = '$fk_purchase'");
        $po_data = mysqli_fetch_assoc($res_po);
        $total_tagihan = $po_data['total_keseluruhan'] ?? 0;
    } else {
        $fk_purchase = "NULL";
        $fk_sales    = $_POST['fk_sales'];
        
        // Ambil total tagihan otomatis dari SO
        $res_so = mysqli_query($conn, "SELECT total_keseluruhan FROM transaksi_sales WHERE id_sales = '$fk_sales'");
        $so_data = mysqli_fetch_assoc($res_so);
        $total_tagihan = $so_data['total_keseluruhan'] ?? 0;
    }

    if (empty($id)) {
        // INSERT menyertakan jenis_invoice dan fk_sales
        $sql = "INSERT INTO invoice (jenis_invoice, fk_purchase, fk_sales, nomor_invoice_vendor, tanggal_invoice, tanggal_jatuh_tempo, status_invoice, status_pembayaran, total_tagihan) 
                VALUES ('$jenis_invoice', " . ($fk_purchase == "NULL" ? "NULL" : "'$fk_purchase'") . ", " . ($fk_sales == "NULL" ? "NULL" : "'$fk_sales'") . ", '$no_vendor', '$tgl_inv', '$tgl_due', '$status_inv', '$status_pay', '$total_tagihan')";
    } else {
        // UPDATE menyertakan jenis_invoice dan fk_sales
        $sql = "UPDATE invoice SET 
                jenis_invoice='$jenis_invoice',
                fk_purchase=" . ($fk_purchase == "NULL" ? "NULL" : "'$fk_purchase'") . ", 
                fk_sales=" . ($fk_sales == "NULL" ? "NULL" : "'$fk_sales'") . ", 
                nomor_invoice_vendor='$no_vendor', tanggal_invoice='$tgl_inv', 
                tanggal_jatuh_tempo='$tgl_due', status_invoice='$status_inv', status_pembayaran='$status_pay', 
                total_tagihan='$total_tagihan' WHERE id_invoice=$id";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: invoice.php?msg=success");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

include 'header.php'; 

// 3. AMBIL DATA UNTUK EDIT
$val = ['id_invoice'=>'','jenis_invoice'=>'vendor','fk_purchase'=>'','fk_sales'=>'','nomor_invoice_vendor'=>'','tanggal_invoice'=>'','tanggal_jatuh_tempo'=>'','status_invoice'=>'draft','status_pembayaran'=>'unpaid','total_tagihan'=>0];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM invoice WHERE id_invoice = $id");
    $val = mysqli_fetch_assoc($res);
}
?>

<div class="header-bar">
    <h1>Invoicing Ledger (Vendor & Customer)</h1>
    <button onclick="toggleForm()" class="btn-orange"><?= isset($_GET['edit']) ? 'Edit Mode' : '+ Create Bill / Invoice' ?></button>
</div>

<div id="form-invoice" class="card" style="display: <?= isset($_GET['edit']) ? 'block' : 'none' ?>; margin-bottom: 25px;">
    <form method="POST">
        <input type="hidden" name="id_invoice" value="<?= $val['id_invoice'] ?>">
        
        <div style="margin-bottom: 20px; max-width: 300px;">
            <label><strong>Invoice Category</strong></label>
            <select name="jenis_invoice" id="jenis_invoice" style="width:100%; padding:8px; margin-top:5px; font-weight: bold;" onchange="updateFormFields()" required>
                <option value="vendor" <?= $val['jenis_invoice']=='vendor'?'selected':'' ?>>Vendor Bill (Purchasing / PO)</option>
                <option value="customer" <?= $val['jenis_invoice']=='customer'?'selected':'' ?>>Customer Invoice (Sales / SO)</option>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
            
            <div id="box-purchase">
                <label><strong>Source Document (PO Reference)</strong></label>
                <select name="fk_purchase" id="fk_purchase" style="width:100%; padding:8px; margin-top:5px;">
                    <option value="">-- Select PO --</option>
                    <?php 
                    // PERUBAHAN MINOR: Menambahkan ORDER BY id_purchase DESC agar PO terbaru berada di paling atas
                    $po_list = mysqli_query($conn, "SELECT id_purchase, total_keseluruhan FROM transaksi_purchase ORDER BY id_purchase DESC");
                    while($p = mysqli_fetch_assoc($po_list)) {
                        $sel = ($p['id_purchase'] == $val['fk_purchase']) ? 'selected' : '';
                        echo "<option value='{$p['id_purchase']}' $sel>PO #00{$p['id_purchase']} (IDR ".number_format($p['total_keseluruhan'],0, ',', '.').")</option>";
                    }
                    ?>
                </select>
            </div>

            <div id="box-sales" style="display:none;">
                <label><strong>Source Document (SO Reference)</strong></label>
                <select name="fk_sales" id="fk_sales" style="width:100%; padding:8px; margin-top:5px;">
                    <option value="">-- Select SO --</option>
                    <?php 
                    // PERUBAHAN MINOR: Menambahkan ORDER BY id_sales DESC agar SO terbaru berada di paling atas
                    $so_list = mysqli_query($conn, "SELECT id_sales, total_keseluruhan FROM transaksi_sales WHERE status_dokumen='sale' ORDER BY id_sales DESC");
                    while($s = mysqli_fetch_assoc($so_list)) {
                        $sel = ($s['id_sales'] == $val['fk_sales']) ? 'selected' : '';
                        echo "<option value='{$s['id_sales']}' $sel>SO #00{$s['id_sales']} (IDR ".number_format($s['total_keseluruhan'],0, ',', '.').")</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <label><strong id="label-ref">Vendor Invoice Number</strong></label>
                <input type="text" name="nomor_invoice_vendor" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['nomor_invoice_vendor'] ?>" placeholder="e.g. INV/2026/XYZ" required>
            </div>
            
            <div>
                <label><strong>Invoice Date</strong></label>
                <input type="date" name="tanggal_invoice" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['tanggal_invoice'] ?>" required>
            </div>
            
            <div>
                <label><strong>Due Date (Jatuh Tempo)</strong></label>
                <input type="date" name="tanggal_jatuh_tempo" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['tanggal_jatuh_tempo'] ?>" required>
            </div>
            
            <div>
                <label><strong>Invoice Status</strong></label>
                <select name="status_invoice" style="width:100%; padding:8px; margin-top:5px;">
                    <option value="draft" <?= $val['status_invoice']=='draft'?'selected':'' ?>>Draft</option>
                    <option value="posted" <?= $val['status_invoice']=='posted'?'selected':'' ?>>Posted (Approved)</option>
                    <option value="cancel" <?= $val['status_invoice']=='cancel'?'selected':'' ?>>Cancelled</option>
                </select>
            </div>
            
            <div>
                <label><strong>Payment Status</strong></label>
                <select name="status_pembayaran" style="width:100%; padding:8px; margin-top:5px;">
                    <option value="unpaid" <?= $val['status_pembayaran']=='unpaid'?'selected':'' ?>>Unpaid</option>
                    <option value="partial" <?= $val['status_pembayaran']=='partial'?'selected':'' ?>>Partial</option>
                    <option value="paid" <?= $val['status_pembayaran']=='paid'?'selected':'' ?>>Paid</option>
                </select>
            </div>
        </div>

        <div style="margin-top: 25px;">
            <button type="submit" name="save_invoice" class="btn-orange">Confirm Invoice</button>
            <a href="invoice.php" class="btn-orange" style="background:#888; text-decoration:none;">Cancel</a>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Invoice Ref</th>
                <th>Source Document</th>
                <th>Reference Num</th>
                <th>Due Date</th>
                <th style="text-align:right">Amount</th>
                <th>Status</th>
                <th style="text-align:center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT i.*, p.id_purchase, s.id_sales 
                    FROM invoice i 
                    LEFT JOIN transaksi_purchase p ON i.fk_purchase = p.id_purchase 
                    LEFT JOIN transaksi_sales s ON i.fk_sales = s.id_sales
                    ORDER BY i.id_invoice DESC";
            $res = mysqli_query($conn, $sql);
            while($row = mysqli_fetch_assoc($res)) {
                $pay_status = ($row['status_pembayaran'] == 'paid') ? 'bg-posted' : (($row['status_pembayaran'] == 'unpaid') ? 'bg-draft' : 'bg-sent');
                
                if ($row['jenis_invoice'] == 'vendor') {
                    $type_badge = "<span style='color:#d9534f; font-weight:bold;'>[BILL]</span>";
                    $source_doc = "PO #00" . $row['id_purchase'];
                } else {
                    $type_badge = "<span style='color:#2ecc71; font-weight:bold;'>[INV]</span>";
                    $source_doc = "SO #00" . $row['id_sales'];
                }

                echo "<tr>
                    <td>{$type_badge}</td>
                    <td><strong>INV/".str_pad($row['id_invoice'], 3, '0', STR_PAD_LEFT)."</strong></td>
                    <td>{$source_doc}</td>
                    <td>{$row['nomor_invoice_vendor']}</td>
                    <td>{$row['tanggal_jatuh_tempo']}</td>
                    <td style='text-align:right'>IDR ".number_format($row['total_tagihan'], 0, ',', '.')."</td>
                    <td><span class='badge $pay_status'>".strtoupper($row['status_pembayaran'])."</span></td>
                    <td style='text-align:center'>
                        <a href='invoice.php?edit={$row['id_invoice']}' style='color:#ef7d00; text-decoration:none;'>Edit</a> | 
                        <a href='invoice.php?delete={$row['id_invoice']}' style='color:red; text-decoration:none;' onclick='return confirm(\"Hapus Invoice ini?\")'>Delete</a>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
function toggleForm() { 
    var x = document.getElementById("form-invoice"); 
    x.style.display = (x.style.display === "none") ? "block" : "none"; 
}

function updateFormFields() {
    var jenis = document.getElementById("jenis_invoice").value;
    var boxPurchase = document.getElementById("box-purchase");
    var boxSales = document.getElementById("box-sales");
    var labelRef = document.getElementById("label-ref");
    var selectPurchase = document.getElementById("fk_purchase");
    var selectSales = document.getElementById("fk_sales");

    if (jenis === "vendor") {
        boxPurchase.style.display = "block";
        boxSales.style.display = "none";
        labelRef.innerText = "Vendor Invoice Number";
        
        selectPurchase.setAttribute("required", "required");
        selectSales.removeAttribute("required");
    } else {
        boxPurchase.style.display = "none";
        boxSales.style.display = "block";
        labelRef.innerText = "Customer Invoice / Faktur Ref";
        
        selectSales.setAttribute("required", "required");
        selectPurchase.removeAttribute("required"); 
    }
}

window.onload = function() {
    updateFormFields();
};
</script>
<?php echo "</div></body></html>"; ?>