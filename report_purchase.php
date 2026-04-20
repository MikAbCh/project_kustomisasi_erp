<?php 
include 'config.php'; 
include 'header.php'; 

// Ambil parameter filter dari URL
$type  = $_GET['type'] ?? 'month'; // default ke bulan
$day   = $_GET['day'] ?? date('Y-m-d');
$month = $_GET['month'] ?? date('m');
$year  = $_GET['year'] ?? date('Y');

// Logika Penentuan Query berdasarkan Jenis Filter
if ($type == 'day') {
    $where_clause = "WHERE p.tanggal_order = '$day'";
    $label = "Hari: " . date('d M Y', strtotime($day));
} elseif ($type == 'month') {
    $where_clause = "WHERE MONTH(p.tanggal_order) = '$month' AND YEAR(p.tanggal_order) = '$year'";
    $months_name = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
    $label = "Bulan: " . $months_name[$month-1] . " " . $year;
} elseif ($type == 'year') {
    $where_clause = "WHERE YEAR(p.tanggal_order) = '$year'";
    $label = "Tahun: " . $year;
}

// 1. Query Aggregation (SUM & COUNT)
$query = "SELECT COUNT(id_purchase) as total_po, SUM(total_keseluruhan) as grand_total 
          FROM transaksi_purchase p $where_clause";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);
?>

<div class="header-bar">
    <h1>Purchase Analytics</h1>
</div>

<div class="card" style="margin-bottom: 20px; background: #f9f9f9;">
    <form method="GET" style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
        
        <div style="border-right: 1px solid #ddd; padding-right: 20px;">
            <label><small><strong>Filter per Hari:</strong></small></label><br>
            <input type="date" name="day" value="<?= $day ?>" style="padding:5px;">
            <button type="submit" name="type" value="day" class="btn-orange" style="padding:5px 10px; font-size:12px;">Go</button>
        </div>

        <div style="border-right: 1px solid #ddd; padding-right: 20px;">
            <label><small><strong>Filter per Bulan:</strong></small></label><br>
            <select name="month" style="padding:5px;">
                <?php for($m=1; $m<=12; $m++): ?>
                    <option value="<?= sprintf('%02d', $m) ?>" <?= $month == $m ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
            <input type="number" name="year" value="<?= $year ?>" style="width:70px; padding:5px;" placeholder="Year">
            <button type="submit" name="type" value="month" class="btn-orange" style="padding:5px 10px; font-size:12px;">Go</button>
        </div>

        <div>
            <label><small><strong>Filter per Tahun:</strong></small></label><br>
            <input type="number" name="year" value="<?= $year ?>" style="width:80px; padding:5px;">
            <button type="submit" name="type" value="year" class="btn-orange" style="padding:5px 10px; font-size:12px;">Go</button>
        </div>
        
        <a href="report_purchase.php" style="font-size:12px; color:#888;">Reset Filter</a>
    </form>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <div class="card" style="text-align: center; border-top: 5px solid #ef7d00;">
        <h3 style="color: #555;">Total PO (COUNT)</h3>
        <p style="font-size: 35px; margin: 10px 0;"><?= $data['total_po'] ?? 0 ?></p>
        <small>Ditemukan pada periode <?= $label ?></small>
    </div>
    <div class="card" style="text-align: center; border-top: 5px solid #2ecc71;">
        <h3 style="color: #555;">Total Belanja (SUM)</h3>
        <p style="font-size: 35px; margin: 10px 0; color: #2ecc71;">IDR <?= number_format($data['grand_total'] ?? 0, 0, ',', '.') ?></p>
        <small>Akumulasi biaya pada periode <?= $label ?></small>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <h3>Detail Transaksi: <?= $label ?></h3>
    <table style="margin-top: 15px;">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>PO Ref</th>
                <th>Supplier</th>
                <th>Status</th>
                <th style="text-align:right">Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $list_sql = "SELECT p.*, s.nama_perusahaan 
                         FROM transaksi_purchase p 
                         JOIN supplier s ON p.fk_supplier = s.id_supplier 
                         $where_clause ORDER BY p.tanggal_order DESC";
            $list_res = mysqli_query($conn, $list_sql);
            if (mysqli_num_rows($list_res) > 0) {
                while($row = mysqli_fetch_assoc($list_res)) {
                    echo "<tr>
                            <td>{$row['tanggal_order']}</td>
                            <td><strong>PO/".str_pad($row['id_purchase'], 3, '0', STR_PAD_LEFT)."</strong></td>
                            <td>{$row['nama_perusahaan']}</td>
                            <td>{$row['status_dokumen']}</td>
                            <td style='text-align:right'>IDR ".number_format($row['total_keseluruhan'], 0, ',', '.')."</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>Tidak ada data transaksi.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php echo "</div></body></html>"; ?>