<?php 
include 'config.php'; 
include 'header.php'; 

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
    $fk_purchase    = $_POST['fk_purchase'];
    $no_vendor      = mysqli_real_escape_string($conn, $_POST['nomor_invoice_vendor']);
    $tgl_inv        = $_POST['tanggal_invoice'];
    $tgl_due        = $_POST['tanggal_jatuh_tempo'];
    $status_inv     = $_POST['status_invoice'];
    $status_pay     = $_POST['status_pembayaran'];
    
    // Ambil total tagihan otomatis dari PO jika tidak diisi manual
    $res_po = mysqli_query($conn, "SELECT total_keseluruhan FROM transaksi_purchase WHERE id_purchase = '$fk_purchase'");
    $po_data = mysqli_fetch_assoc($res_po);
    $total_tagihan = $po_data['total_keseluruhan'];

    if (empty($id)) {
        $sql = "INSERT INTO invoice (fk_purchase, nomor_invoice_vendor, tanggal_invoice, tanggal_jatuh_tempo, status_invoice, status_pembayaran, total_tagihan) 
                VALUES ('$fk_purchase', '$no_vendor', '$tgl_inv', '$tgl_due', '$status_inv', '$status_pay', '$total_tagihan')";
    } else {
        $sql = "UPDATE invoice SET 
                fk_purchase='$fk_purchase', nomor_invoice_vendor='$no_vendor', tanggal_invoice='$tgl_inv', 
                tanggal_jatuh_tempo='$tgl_due', status_invoice='$status_inv', status_pembayaran='$status_pay', 
                total_tagihan='$total_tagihan' WHERE id_invoice=$id";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: invoice.php?msg=success");
    }
}

// 3. AMBIL DATA UNTUK EDIT
$val = ['id_invoice'=>'','fk_purchase'=>'','nomor_invoice_vendor'=>'','tanggal_invoice'=>'','tanggal_jatuh_tempo'=>'','status_invoice'=>'draft','status_pembayaran'=>'unpaid','total_tagihan'=>0];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM invoice WHERE id_invoice = $id");
    $val = mysqli_fetch_assoc($res);
}
?>

<div class="header-bar">
    <h1>Vendor Invoices</h1>
    <button onclick="toggleForm()" class="btn-orange"><?= isset($_GET['edit']) ? 'Edit Mode' : '+ Create Bill' ?></button>
</div>

<div id="form-invoice" class="card" style="display: <?= isset($_GET['edit']) ? 'block' : 'none' ?>; margin-bottom: 25px;">
    <form method="POST">
        <input type="hidden" name="id_invoice" value="<?= $val['id_invoice'] ?>">
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
            <div>
                <label><strong>Source Document (PO Reference)</strong></label>
                <select name="fk_purchase" style="width:100%; padding:8px; margin-top:5px;" required>
                    <option value="">-- Select PO --</option>
                    <?php 
                    $po_list = mysqli_query($conn, "SELECT id_purchase, total_keseluruhan FROM transaksi_purchase");
                    while($p = mysqli_fetch_assoc($po_list)) {
                        $sel = ($p['id_purchase'] == $val['fk_purchase']) ? 'selected' : '';
                        echo "<option value='{$p['id_purchase']}' $sel>PO #00{$p['id_purchase']} (IDR ".number_format($p['total_keseluruhan'],0).")</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label><strong>Vendor Invoice Number</strong></label>
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
                <th>Invoice Ref</th>
                <th>Source PO</th>
                <th>Vendor Ref</th>
                <th>Due Date</th>
                <th style="text-align:right">Amount</th>
                <th>Status</th>
                <th style="text-align:center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT i.*, p.id_purchase FROM invoice i 
                    JOIN transaksi_purchase p ON i.fk_purchase = p.id_purchase ORDER BY i.id_invoice DESC";
            $res = mysqli_query($conn, $sql);
            while($row = mysqli_fetch_assoc($res)) {
                $pay_status = ($row['status_pembayaran'] == 'paid') ? 'bg-posted' : (($row['status_pembayaran'] == 'unpaid') ? 'bg-draft' : 'bg-sent');
                echo "<tr>
                    <td><strong>INV/".str_pad($row['id_invoice'], 3, '0', STR_PAD_LEFT)."</strong></td>
                    <td>PO #00{$row['id_purchase']}</td>
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

<script>function toggleForm() { var x = document.getElementById("form-invoice"); x.style.display = (x.style.display === "none") ? "block" : "none"; }</script>
<?php echo "</div></body></html>"; ?>