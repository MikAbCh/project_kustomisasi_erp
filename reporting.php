<?php 
include 'config.php';  

// Ambil parameter filter dari URL
$module      = $_GET['module'] ?? 'purchase'; // purchase, sales, atau profit
$type        = $_GET['type'] ?? ''; 
$day         = $_GET['day'] ?? '';
$month       = $_GET['month'] ?? '';
$year_month  = $_GET['year_month'] ?? ''; 
$year_only   = $_GET['year_only'] ?? '';  
$id_product  = $_GET['id_product'] ?? ''; 

$where_clauses = [];
$label = "Semua Waktu"; 

// 1. Logika Penentuan Query berdasarkan Jenis Filter Waktu
if ($type == 'day' && !empty($day)) {
    $where_clauses[] = "t.tanggal_order = '$day'";
    $label = "Hari: " . date('d M Y', strtotime($day));
} elseif ($type == 'month' && !empty($month) && !empty($year_month)) {
    $where_clauses[] = "MONTH(t.tanggal_order) = '$month' AND YEAR(t.tanggal_order) = '$year_month'";
    $months_name = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
    $label = "Bulan: " . $months_name[intval($month)-1] . " " . $year_month;
} elseif ($type == 'year' && !empty($year_only)) {
    $where_clauses[] = "YEAR(t.tanggal_order) = '$year_only'";
    $label = "Tahun: " . $year_only;
}

// 2. Filter Produk
$product_label = "";
if (!empty($id_product)) {
    $where_clauses[] = "tl.fk_product = '$id_product'";
    
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

// --- LOGIKA TAMBAHAN UNTUK MENGHITUNG UTUNG / RUGI (PROFIT & LOSS) ---
// Kita ambil total belanja (purchase) pada periode filter ini
if (!empty($id_product)) {
    $q_pur = "SELECT SUM(tl.subtotal) as total FROM transaksi_purchase t JOIN transaksi_purchase_line tl ON t.id_purchase = tl.fk_purchase $where_sql";
    $q_sal = "SELECT SUM(tl.subtotal) as total FROM transaksi_sales t JOIN transaksi_sales_line tl ON t.id_sales = tl.fk_sales $where_sql";
} else {
    $q_pur = "SELECT SUM(t.total_keseluruhan) as total FROM transaksi_purchase t $where_sql";
    $q_sal = "SELECT SUM(t.total_keseluruhan) as total FROM transaksi_sales t $where_sql";
}
$res_pur = mysqli_fetch_assoc(mysqli_query($conn, $q_pur));
$res_sal = mysqli_fetch_assoc(mysqli_query($conn, $q_sal));

$total_purchase_amt = $res_pur['total'] ?? 0;
$total_sales_amt    = $res_sal['total'] ?? 0;
$selisih_profit     = $total_sales_amt - $total_purchase_amt; // Positif = Untung, Negatif = Rugi
// ---------------------------------------------------------------------

// 3. DEFINISI NAMA TABEL BERDASARKAN MODUL AKTIF
if ($module == 'sales') {
    $table_main = "transaksi_sales";
    $table_line = "transaksi_sales_line";
    $fk_main    = "id_sales";
    $fk_line    = "fk_sales";
    $module_title = "Sales Analytics";
    $doc_prefix = "SO";
    $partner_join = "JOIN customer c ON t.fk_customer = c.id_customer";
    $partner_col  = "c.nama_pelanggan AS nama_partner";
    $partner_label = "Customer";
} elseif ($module == 'profit') {
    $module_title = "Profit & Loss Statement";
    // Untuk mode profit, tabel default bawahnya kita set tampilkan sales log saja
    $table_main = "transaksi_sales";
    $table_line = "transaksi_sales_line";
    $fk_main    = "id_sales";
    $fk_line    = "fk_sales";
    $doc_prefix = "SO";
    $partner_join = "JOIN customer c ON t.fk_customer = c.id_customer";
    $partner_col  = "c.nama_pelanggan AS nama_partner";
    $partner_label = "Customer";
} else {
    $table_main = "transaksi_purchase";
    $table_line = "transaksi_purchase_line";
    $fk_main    = "id_purchase";
    $fk_line    = "fk_purchase";
    $module_title = "Purchase Analytics";
    $doc_prefix = "PO";
    $partner_join = "JOIN supplier s ON t.fk_supplier = s.id_supplier";
    $partner_col  = "s.nama_perusahaan AS nama_partner";
    $partner_label = "Supplier";
}

// 4. Query Aggregation Utama
if (!empty($id_product)) {
    $query = "SELECT COUNT(DISTINCT t.$fk_main) as total_doc, SUM(tl.subtotal) as grand_total 
              FROM $table_main t 
              JOIN $table_line tl ON t.$fk_main = tl.$fk_line 
              $where_sql";
} else {
    $query = "SELECT COUNT(t.$fk_main) as total_doc, SUM(t.total_keseluruhan) as grand_total 
              FROM $table_main t 
              $where_sql";
}
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

include 'header.php';
?>

<div class="header-bar">
    <h1><?= $module_title ?></h1>
</div>

<div class="card" style="margin-bottom: 20px; background: #f9f9f9;">
    <form method="GET" id="filter_form" style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
        
        <input type="hidden" name="type" id="filter_type" value="<?= $type ?>">

        <div style="border-right: 2px solid #ef7d00; padding-right: 20px;">
            <label><strong>Pilih Modul Laporan:</strong></label><br>
            <select name="module" style="padding:5px; font-weight:bold; border: 1px solid #ef7d00;" onchange="document.getElementById('filter_form').submit();">
                <option value="purchase" <?= $module == 'purchase' ? 'selected' : '' ?>>🛒 Purchase Reporting (Pembelian)</option>
                <option value="sales" <?= $module == 'sales' ? 'selected' : '' ?>>📈 Sales Reporting (Penjualan)</option>
                <option value="profit" <?= $module == 'profit' ? 'selected' : '' ?>>📊 Profit & Loss (Untung / Rugi)</option>
            </select>
        </div>

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
        
        <a href="reporting.php?module=<?= $module ?>" style="font-size:12px; color:#d9534f; font-weight:bold; margin-left: auto; align-self: center; text-decoration:none;">❌ Reset Filter</a>
    </form>
</div>

<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
    <div class="card" style="text-align: center; border-top: 5px solid #ef7d00;">
        <h3 style="color: #555;">Total Belanja (Purchase)</h3>
        <p style="font-size: 28px; margin: 10px 0; color: #ef7d00;">IDR <?= number_format($total_purchase_amt, 0, ',', '.') ?></p>
        <small>Periode: <strong><?= $label ?></strong></small>
    </div>
    
    <div class="card" style="text-align: center; border-top: 5px solid #2ecc71;">
        <h3 style="color: #555;">Total Pendapatan (Sales)</h3>
        <p style="font-size: 28px; margin: 10px 0; color: #2ecc71;">IDR <?= number_format($total_sales_amt, 0, ',', '.') ?></p>
        <small>Periode: <strong><?= $label ?></strong></small>
    </div>

    <?php 
        // Menentukan warna badge/text berdasarkan hasil untung atau rugi
        $box_color = ($selisih_profit >= 0) ? '#2ecc71' : '#d9534f'; 
        $status_text = ($selisih_profit >= 0) ? '🟢 UNTUNG (Surplus)' : '🔴 RUGI (Defisit)';
    ?>
    <div class="card" style="text-align: center; border-top: 5px solid <?= $box_color ?>; background: #fffdf9;">
        <h3 style="color: #555;">Selisih Bersih (Net Profit/Loss)</h3>
        <p style="font-size: 28px; margin: 10px 0; color: <?= $box_color ?>; font-weight: bold;">
            <?= ($selisih_profit >= 0) ? '+' : '' ?>IDR <?= number_format($selisih_profit, 0, ',', '.') ?>
        </p>
        <small>Kondisi: <strong><?= $status_text ?></strong></small>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <h3>Detail Log Transaksi Aktif (Modul: <?= strtoupper($module) ?>)</h3>
    <table style="margin-top: 15px;">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th><?= $doc_prefix ?> Ref</th>
                <th><?= $partner_label ?></th>
                <th>Status</th>
                <?php if(!empty($id_product)): ?>
                    <th style="text-align:center">Qty</th>
                    <th style="text-align:right">Harga Satuan</th>
                <?php endif; ?>
                <th style="text-align:right"><?= !empty($id_product) ? 'Subtotal Item' : 'Total Nilai Invoice' ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($id_product)) {
                $list_sql = "SELECT t.*, $partner_col, tl.qty, tl.harga_satuan, tl.subtotal as item_subtotal
                             FROM $table_main t 
                             $partner_join
                             JOIN $table_line tl ON t.$fk_main = tl.$fk_line
                             $where_sql ORDER BY t.tanggal_order DESC";
            } else {
                $list_sql = "SELECT t.*, $partner_col 
                             FROM $table_main t 
                             $partner_join 
                             $where_sql ORDER BY t.tanggal_order DESC";
            }
            
            $list_res = mysqli_query($conn, $list_sql);
            if ($list_res && mysqli_num_rows($list_res) > 0) {
                while($row = mysqli_fetch_assoc($list_res)) {
                    $nilai_tampil = !empty($id_product) ? $row['item_subtotal'] : $row['total_keseluruhan'];
                    
                    echo "<tr>
                            <td>{$row['tanggal_order']}</td>
                            <td><strong>{$doc_prefix}/".date('Y', strtotime($row['tanggal_order']))."/".str_pad($row[$fk_main], 4, '0', STR_PAD_LEFT)."</strong></td>
                            <td>{$row['nama_partner']}</td>
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
function setFilterType(typeValue) {
    document.getElementById('filter_type').value = typeValue;
}
</script>

<?php echo "</div></body></html>"; ?>