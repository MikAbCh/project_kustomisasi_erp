<?php 
include 'config.php';  

// Ambil parameter filter dari URL
$type        = $_GET['type'] ?? ''; 
$day         = $_GET['day'] ?? '';
$month       = $_GET['month'] ?? '';
$year_month  = $_GET['year_month'] ?? ''; // Tahun khusus untuk filter bulanan
$year_only   = $_GET['year_only'] ?? '';  // Tahun khusus untuk filter tahunan
$id_product  = $_GET['id_product'] ?? ''; 

$where_clauses = [];
$label = "Semua Waktu"; // Label default jika tidak ada filter waktu
$current_year = '';     // Variabel bantu untuk query

// Logika Penentuan Query berdasarkan Jenis Filter Waktu
if ($type == 'day' && !empty($day)) {
    $where_clauses[] = "p.tanggal_order = '$day'";
    $label = "Hari: " . date('d M Y', strtotime($day));
} elseif ($type == 'month' && !empty($month) && !empty($year_month)) {
    $where_clauses[] = "MONTH(p.tanggal_order) = '$month' AND YEAR(p.tanggal_order) = '$year_month'";
    $months_name = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
    $label = "Bulan: " . $months_name[intval($month)-1] . " " . $year_month;
    $current_year = $year_month;
} elseif ($type == 'year' && !empty($year_only)) {
    $where_clauses[] = "YEAR(p.tanggal_order) = '$year_only'";
    $label = "Tahun: " . $year_only;
    $current_year = $year_only;
}

// Tambahkan filter produk ke klausa WHERE jika dipilih
$product_label = "";
if (!empty($id_product)) {
    $where_clauses[] = "pl.fk_product = '$id_product'";
    
    // Ambil nama produk untuk label penanda filter aktif
    $res_p_name = mysqli_query($conn, "SELECT nama_product FROM product WHERE id_product = '$id_product'");
    $p_data = mysqli_fetch_assoc($res_p_name);
    if ($p_data) {
        $product_label = " | Produk: " . $p_data['nama_product'];
    }
}

// Menyusun Klausa WHERE SQL secara Dinamis
$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// 1. Query Aggregation (SUM & COUNT)
if (!empty($id_product)) {
    $query = "SELECT COUNT(DISTINCT p.id_purchase) as total_po, SUM(pl.subtotal) as grand_total 
              FROM transaksi_purchase p 
              JOIN transaksi_purchase_line pl ON p.id_purchase = pl.fk_purchase 
              $where_sql";
} else {
    $query = "SELECT COUNT(p.id_purchase) as total_po, SUM(p.total_keseluruhan) as grand_total 
              FROM transaksi_purchase p 
              $where_sql";
}

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

include 'header.php';
?>

<div class="header-bar">
    <h1>Purchase Analytics</h1>
</div>

<div class="card" style="margin-bottom: 20px; background: #f9f9f9;">
    <form method="GET" id="filter_form" style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
        
        <input type="hidden" name="type" id="filter_type" value="<?= $type ?>">

        <div style="border-right: 1px solid #ddd; padding-right: 20px;">
            <label><small><strong>Filter per Hari:</strong></small></label><br>
            <input type="date" name="day" value="<?= $day ?>" style="padding:5px;">
            <button type="submit" onclick="setFilterType('day')" class="btn-orange" style="padding:5px 10px; font-size:12px;">Go</button>
        </div>

        <div style="border-right: 1px solid #ddd; padding-right: 20px;">
            <label><small><strong>Filter per Bulan:</strong></small></label><br>
            <select name="month" style="padding:5px;">
                <option value="">-- Bulan --</option>
                <?php for($m=1; $m<=12; $m++): ?>
                    <option value="<?= sprintf('%02d', $m) ?>" <?= $month == sprintf('%02d', $m) ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
            <input type="number" name="year_month" value="<?= $year_month ?>" style="width:70px; padding:5px;" placeholder="Tahun">
            <button type="submit" onclick="setFilterType('month')" class="btn-orange" style="padding:5px 10px; font-size:12px;">Go</button>
        </div>

        <div style="border-right: 1px solid #ddd; padding-right: 20px;">
            <label><small><strong>Filter per Tahun:</strong></small></label><br>
            <input type="number" name="year_only" value="<?= $year_only ?>" style="width:80px; padding:5px;" placeholder="Tahun">
            <button type="submit" onclick="setFilterType('year')" class="btn-orange" style="padding:5px 10px; font-size:12px;">Go</button>
        </div>

        <div>
            <label><small><strong>Filter Berdasarkan Produk:</strong></small></label><br>
            <select name="id_product" style="padding:5px; max-width: 250px;">
                <option value="">-- Semua Produk --</option>
                <?php 
                $p_list = mysqli_query($conn, "SELECT id_product, nama_product FROM product");
                while($p = mysqli_fetch_assoc($p_list)) {
                    $sel = ($p['id_product'] == $id_product) ? 'selected' : '';
                    echo "<option value='{$p['id_product']}' $sel>{$p['nama_product']}</option>";
                }
                ?>
            </select>
            <button type="submit" class="btn-orange" style="padding:5px 10px; font-size:12px; background:#555;">Apply Product</button>
        </div>
        
        <a href="report_purchase.php" style="font-size:12px; color:#d9534f; font-weight:bold; margin-left: auto; align-self: center; text-decoration:none;">❌ Reset Filter</a>
    </form>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div class="card" style="text-align: center; border-top: 5px solid #ef7d00;">
        <h3 style="color: #555;">Total PO (COUNT)</h3>
        <p style="font-size: 35px; margin: 10px 0;"><?= $data['total_po'] ?? 0 ?></p>
        <small>Periode: <strong><?= $label . $product_label ?></strong></small>
    </div>
    <div class="card" style="text-align: center; border-top: 5px solid #2ecc71;">
        <h3 style="color: #555;">Total Belanja (SUM)</h3>
        <p style="font-size: 35px; margin: 10px 0; color: #2ecc71;">IDR <?= number_format($data['grand_total'] ?? 0, 0, ',', '.') ?></p>
        <small><?= !empty($id_product) ? 'Total pembelian produk ini' : 'Akumulasi biaya' ?> pada periode <strong><?= $label ?></strong></small>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <h3>Detail Transaksi: <?= $label . $product_label ?></h3>
    <table style="margin-top: 15px;">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>PO Ref</th>
                <th>Supplier</th>
                <th>Status</th>
                <?php if(!empty($id_product)): ?>
                    <th style="text-align:center">Qty Dipesan</th>
                    <th style="text-align:right">Harga Satuan</th>
                <?php endif; ?>
                <th style="text-align:right"><?= !empty($id_product) ? 'Subtotal Item' : 'Total Nilai Invoice' ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($id_product)) {
                $list_sql = "SELECT p.*, s.nama_perusahaan, pl.qty, pl.harga_satuan, pl.subtotal as item_subtotal
                             FROM transaksi_purchase p 
                             JOIN supplier s ON p.fk_supplier = s.id_supplier 
                             JOIN transaksi_purchase_line pl ON p.id_purchase = pl.fk_purchase
                             $where_sql ORDER BY p.tanggal_order DESC";
            } else {
                $list_sql = "SELECT p.*, s.nama_perusahaan 
                             FROM transaksi_purchase p 
                             JOIN supplier s ON p.fk_supplier = s.id_supplier 
                             $where_sql ORDER BY p.tanggal_order DESC";
            }
            
            $list_res = mysqli_query($conn, $list_sql);
            if ($list_res && mysqli_num_rows($list_res) > 0) {
                while($row = mysqli_fetch_assoc($list_res)) {
                    $nilai_tampil = !empty($id_product) ? $row['item_subtotal'] : $row['total_keseluruhan'];
                    
                    echo "<tr>
                            <td>{$row['tanggal_order']}</td>
                            <td><strong>PO/".date('Y', strtotime($row['tanggal_order']))."/".str_pad($row['id_purchase'], 4, '0', STR_PAD_LEFT)."</strong></td>
                            <td>{$row['nama_perusahaan']}</td>
                            <td><span class='badge'>".strtoupper($row['status_dokumen'])."</span></td>";
                    
                    if(!empty($id_product)) {
                        echo "<td style='text-align:center'>{$row['qty']}</td>
                              <td style='text-align:right'>Rp ".number_format($row['harga_satuan'], 0, ',', '.')."</td>";
                    }
                    
                    echo "  <td style='text-align:right; font-weight:bold;'>IDR ".number_format($nilai_tampil, 0, ',', '.')."</td>
                          </tr>";
                }
            } else {
                $total_cols = !empty($id_product) ? 7 : 5;
                echo "<tr><td colspan='{$total_cols}' style='text-align:center;'>Tidak ada data transaksi untuk kriteria filter ini.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
// Fungsi menjamin nilai tipe filter terisi dengan benar di input hidden sebelum form dikirim
function setFilterType(typeValue) {
    document.getElementById('filter_type').value = typeValue;
}
</script>

<?php echo "</div></body></html>"; ?>