<?php 
include 'config.php'; 
include 'header.php'; 

// LOGIKA VALIDASI BARANG KELUAR
if (isset($_POST['validate_outgoing'])) {
    $id_prod   = $_POST['id_product'];
    $id_loc    = $_POST['id_lokasi'];
    $qty       = $_POST['qty_keluar'];
    $ref_so    = mysqli_real_escape_string($conn, $_POST['ref_so']);
    $note      = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tgl_keluar = date('Y-m-d H:i:s');

    // 1. Availability Check (Prinsip Odoo: Mencegah Stok Minus)
    $res_stok = mysqli_query($conn, "SELECT stok_aktual, nama_product FROM product WHERE id_product = $id_prod");
    $data_p   = mysqli_fetch_assoc($res_stok);
    $stok_sekarang = $data_p['stok_aktual'];

    if ($stok_sekarang < $qty) {
        echo "<script>alert('Stok Tidak Mencukupi! Sisa stok untuk {$data_p['nama_product']} adalah $stok_sekarang'); window.location='barangkeluar.php';</script>";
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        // Gabungkan referensi SO ke keterangan jika ada
        $final_note = (!empty($ref_so)) ? "[$ref_so] " . $note : $note;

        // 2. Insert ke histori barang_keluar
        $sql_out = "INSERT INTO barang_keluar (id_product, id_lokasi, qty_keluar, tanggal_keluar, keterangan) 
                    VALUES ('$id_prod', '$id_loc', '$qty', '$tgl_keluar', '$final_note')";
        mysqli_query($conn, $sql_out);
        
        // 3. Update Stok Fisik (Pengurangan)
        mysqli_query($conn, "UPDATE product SET stok_aktual = stok_aktual - $qty WHERE id_product = $id_prod");

        mysqli_commit($conn);
        header("Location: barangkeluar.php?msg=shipped");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
    }
}
?>

<div class="header-bar">
    <h1><i class="fa fa-upload"></i> Delivery Orders (Barang Keluar)</h1>
    <button onclick="toggleForm()" class="btn-orange">+ Create Delivery</button>
</div>

<!-- FORM BARANG KELUAR -->
<div id="form-keluar" class="card" style="display:none; margin-bottom: 25px; border-left: 5px solid #d9534f;">
    <h3>Shipment Validation</h3>
    <form method="POST" style="margin-top: 15px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
            <div>
                <label>Product</label>
                <select name="id_product" class="form-control" required style="width:100%; padding:8px;">
                    <option value="">-- Select Product --</option>
                    <?php 
                    $p_list = mysqli_query($conn, "SELECT id_product, nama_product, stok_aktual FROM product WHERE stok_aktual > 0");
                    while($p = mysqli_fetch_assoc($p_list)) {
                        echo "<option value='{$p['id_product']}'>{$p['nama_product']} (Stock: {$p['stok_aktual']})</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label>Source Location</label>
                <select name="id_lokasi" class="form-control" required style="width:100%; padding:8px;">
                    <?php 
                    $l_list = mysqli_query($conn, "SELECT id_lokasi, nama_gudang, blok_rak FROM lokasi");
                    while($l = mysqli_fetch_assoc($l_list)) echo "<option value='{$l['id_lokasi']}'>{$l['nama_gudang']} - {$l['blok_rak']}</option>";
                    ?>
                </select>
            </div>
            <div>
                <label>Quantity to Deliver</label>
                <input type="number" name="qty_keluar" min="1" class="form-control" required style="width:100%; padding:8px;">
            </div>
            <div>
                <label>Sales Order Ref (Optional)</label>
                <input type="text" name="ref_so" placeholder="e.g. SO/2026/001" class="form-control" style="width:100%; padding:8px;">
            </div>
            <div style="grid-column: span 2;">
                <label>Notes / Delivery Reason</label>
                <input type="text" name="keterangan" placeholder="e.g. Kirim ke pelanggan atau internal use" class="form-control" style="width:100%; padding:8px;">
            </div>
        </div>
        <div style="margin-top: 20px;">
            <button type="submit" name="validate_outgoing" class="btn-orange" style="background:#d9534f;">Validate Delivery</button>
            <button type="button" onclick="toggleForm()" style="padding:8px 15px; background:#888; color:#fff; border:none; border-radius:4px; cursor:pointer;">Cancel</button>
        </div>
    </form>
</div>

<!-- TABEL HISTORI KELUAR -->
<div class="card">
    <table class="table-odoo">
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>From Location</th>
                <th style="text-align:right">Quantity</th>
                <th>Memo</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = mysqli_query($conn, "SELECT bk.*, p.nama_product, l.nama_gudang, l.blok_rak 
                                       FROM barang_keluar bk 
                                       JOIN product p ON bk.id_product = p.id_product 
                                       JOIN lokasi l ON bk.id_lokasi = l.id_lokasi 
                                       ORDER BY id_keluar DESC");
            while($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?= date('d M Y H:i', strtotime($row['tanggal_keluar'])) ?></td>
                <td><strong><?= $row['nama_product'] ?></strong></td>
                <td>WH/STOCK/<?= strtoupper($row['nama_gudang']) ?>/<?= $row['blok_rak'] ?></td>
                <td style="text-align:right; color:red; font-weight:bold;">- <?= $row['qty_keluar'] ?></td>
                <td><small><?= $row['keterangan'] ?></small></td>
                <td><span class="badge" style="background:#5bc0de; color:white; padding:4px 8px; border-radius:4px; font-size:10px;">SHIPPED</span></td>
            </tr>
            <?php endwhile; ?>
            <?php if(mysqli_num_rows($res) == 0): ?>
            <tr><td colspan="6" style="text-align:center; padding:20px; color:#999;">No delivery records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function toggleForm() {
    var x = document.getElementById("form-keluar");
    x.style.display = (x.style.display === "none") ? "block" : "none";
}
</script>