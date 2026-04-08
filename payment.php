<?php 
include 'config.php'; 
include 'header.php'; 

// 1. LOGIKA DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Opsional: Sebelum hapus, kembalikan status invoice ke 'unpaid'
    $res = mysqli_query($conn, "SELECT fk_invoice FROM payment WHERE id_payment = $id");
    $pay = mysqli_fetch_assoc($res);
    $inv_id = $pay['fk_invoice'];
    mysqli_query($conn, "UPDATE invoice SET status_pembayaran = 'unpaid' WHERE id_invoice = $inv_id");

    if (mysqli_query($conn, "DELETE FROM payment WHERE id_payment = $id")) {
        header("Location: payment.php?msg=deleted");
    }
}

// 2. LOGIKA SAVE (Create & Update)
if (isset($_POST['save_payment'])) {
    $id         = $_POST['id_payment'];
    $fk_invoice = $_POST['fk_invoice'];
    $tgl_bayar  = $_POST['tanggal_bayar'];
    $metode     = $_POST['metode_pembayaran'];
    $memo       = mysqli_real_escape_string($conn, $_POST['memo_referensi']);
    $jumlah     = $_POST['jumlah_bayar'];

    if (empty($id)) {
        // Create Payment
        $sql = "INSERT INTO payment (fk_invoice, tanggal_bayar, metode_pembayaran, memo_referensi, jumlah_bayar) 
                VALUES ('$fk_invoice', '$tgl_bayar', '$metode', '$memo', '$jumlah')";
        
        if (mysqli_query($conn, $sql)) {
            // OTOMATISASI: Update status di tabel invoice menjadi 'paid'
            mysqli_query($conn, "UPDATE invoice SET status_pembayaran = 'paid' WHERE id_invoice = $fk_invoice");
        }
    } else {
        // Update Payment
        $sql = "UPDATE payment SET 
                fk_invoice='$fk_invoice', tanggal_bayar='$tgl_bayar', metode_pembayaran='$metode', 
                memo_referensi='$memo', jumlah_bayar='$jumlah' WHERE id_payment=$id";
        mysqli_query($conn, $sql);
    }

    header("Location: payment.php?msg=success");
}

// 3. AMBIL DATA UNTUK EDIT
$val = ['id_payment'=>'','fk_invoice'=>'','tanggal_bayar'=>date('Y-m-d H:i'),'metode_pembayaran'=>'Transfer Bank','memo_referensi'=>'','jumlah_bayar'=>0];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM payment WHERE id_payment = $id");
    $val = mysqli_fetch_assoc($res);
}
?>

<div class="header-bar">
    <h1>Payments</h1>
    <button onclick="toggleForm()" class="btn-orange"><?= isset($_GET['edit']) ? 'Edit Mode' : '+ Register Payment' ?></button>
</div>

<div id="form-payment" class="card" style="display: <?= isset($_GET['edit']) ? 'block' : 'none' ?>; margin-bottom: 25px;">
    <form method="POST">
        <input type="hidden" name="id_payment" value="<?= $val['id_payment'] ?>">
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
            <div>
                <label><strong>Select Invoice to Pay</strong></label>
                <select name="fk_invoice" style="width:100%; padding:8px; margin-top:5px;" required>
                    <option value="">-- Select Unpaid Invoice --</option>
                    <?php 
                    // Tampilkan invoice yang belum lunas (atau yang sedang diedit)
                    $inv_list = mysqli_query($conn, "SELECT i.id_invoice, i.total_tagihan, s.nama_perusahaan 
                                                    FROM invoice i 
                                                    JOIN transaksi_purchase p ON i.fk_purchase = p.id_purchase 
                                                    JOIN supplier s ON p.fk_supplier = s.id_supplier");
                    while($i = mysqli_fetch_assoc($inv_list)) {
                        $sel = ($i['id_invoice'] == $val['fk_invoice']) ? 'selected' : '';
                        echo "<option value='{$i['id_invoice']}' $sel>INV #00{$i['id_invoice']} - {$i['nama_perusahaan']} (IDR ".number_format($i['total_tagihan'],0).")</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label><strong>Payment Date</strong></label>
                <input type="datetime-local" name="tanggal_bayar" style="width:100%; padding:8px; margin-top:5px;" value="<?= date('Y-m-d\TH:i', strtotime($val['tanggal_bayar'])) ?>" required>
            </div>
            <div>
                <label><strong>Payment Method</strong></label>
                <select name="metode_pembayaran" style="width:100%; padding:8px; margin-top:5px;">
                    <option value="Transfer Bank" <?= $val['metode_pembayaran']=='Transfer Bank'?'selected':'' ?>>Transfer Bank</option>
                    <option value="Cash" <?= $val['metode_pembayaran']=='Cash'?'selected':'' ?>>Cash</option>
                    <option value="Credit Card" <?= $val['metode_pembayaran']=='Credit Card'?'selected':'' ?>>Credit Card</option>
                </select>
            </div>
            <div>
                <label><strong>Amount Paid (IDR)</strong></label>
                <input type="number" step="0.01" name="jumlah_bayar" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['jumlah_bayar'] ?>" required>
            </div>
            <div style="grid-column: span 2;">
                <label><strong>Memo / Reference</strong></label>
                <input type="text" name="memo_referensi" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['memo_referensi'] ?>" placeholder="e.g. Bukti transfer Mandiri 998822">
            </div>
        </div>

        <div style="margin-top: 25px;">
            <button type="submit" name="save_payment" class="btn-orange">Post Payment</button>
            <a href="payment.php" class="btn-orange" style="background:#888; text-decoration:none;">Cancel</a>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Invoice Ref</th>
                <th>Date</th>
                <th>Method</th>
                <th style="text-align:right">Amount</th>
                <th style="text-align:center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT pay.*, inv.id_invoice FROM payment pay 
                    JOIN invoice inv ON pay.fk_invoice = inv.id_invoice ORDER BY pay.id_payment DESC";
            $res = mysqli_query($conn, $sql);
            while($row = mysqli_fetch_assoc($res)) {
                echo "<tr>
                    <td><strong>PAY/".str_pad($row['id_payment'], 3, '0', STR_PAD_LEFT)."</strong></td>
                    <td>INV #00{$row['id_invoice']}</td>
                    <td>{$row['tanggal_bayar']}</td>
                    <td>{$row['metode_pembayaran']}</td>
                    <td style='text-align:right'>IDR ".number_format($row['jumlah_bayar'], 0, ',', '.')."</td>
                    <td style='text-align:center'>
                        <a href='payment.php?edit={$row['id_payment']}' style='color:#ef7d00; text-decoration:none;'>Edit</a> | 
                        <a href='payment.php?delete={$row['id_payment']}' style='color:red; text-decoration:none;' onclick='return confirm(\"Hapus catatan pembayaran ini?\")'>Delete</a>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>function toggleForm() { var x = document.getElementById("form-payment"); x.style.display = (x.style.display === "none") ? "block" : "none"; }</script>
<?php echo "</div></body></html>"; ?>