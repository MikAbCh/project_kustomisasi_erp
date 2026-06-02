<?php 
include 'config.php'; 

// 1. LOGIKA DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (mysqli_query($conn, "DELETE FROM product WHERE id_product = $id")) {
        header("Location: product.php?msg=deleted");
        exit;
    }
}

// 2. LOGIKA SAVE (Create & Update dengan Direct Stock Adjustment per Gudang)
if (isset($_POST['save_product'])) {
    $id       = $_POST['id_product'];
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_product']);
    $sku      = mysqli_real_escape_string($conn, $_POST['sku']);
    $tipe     = $_POST['tipe_product'];
    $harga    = $_POST['harga_jual'];
    $biaya    = $_POST['biaya_standar'];
    $satuan   = $_POST['satuan_unit'];
    $stok_min = $_POST['stok_minimal'];
    
    // Gunakan DB Transaction untuk menjamin integritas data multi-tabel
    mysqli_begin_transaction($conn);
    try {
        if (empty($id)) {
            // ==========================================
            // AKSI: CREATE NEW PRODUCT
            // ==========================================
            $stok_akt = $_POST['stok_aktual'] ?? 0;
            $sql = "INSERT INTO product (nama_product, sku, tipe_product, harga_jual, biaya_standar, stok_minimal, stok_aktual, satuan_unit) 
                    VALUES ('$nama', '$sku', '$tipe', '$harga', '$biaya', '$stok_min', '$stok_akt', '$satuan')";
            mysqli_query($conn, $sql);
            $new_product_id = mysqli_insert_id($conn);

            // Jika ada alokasi stok per gudang saat membuat produk baru
            if (isset($_POST['stok_gudang']) && is_array($_POST['stok_gudang'])) {
                $total_stok_baru = 0;
                foreach ($_POST['stok_gudang'] as $id_lokasi => $qty_baru) {
                    $qty_baru = (int)$qty_baru;
                    if ($qty_baru > 0) {
                        $tgl_now = date('Y-m-d H:i:s');
                        // Menggunakan id_supplier = 1 untuk menghindari FK Constraint Error
                        mysqli_query($conn, "INSERT INTO barang_masuk (id_product, id_supplier, id_lokasi, qty_masuk, tanggal_masuk, keterangan) 
                                             VALUES ($new_product_id, 1, $id_lokasi, $qty_baru, '$tgl_now', '[Initial Stock Allocation]')");
                        $total_stok_baru += $qty_baru;
                    }
                }
                // Sinkronisasi ulang total on-hand global ke master product
                mysqli_query($conn, "UPDATE product SET stok_aktual = $total_stok_baru WHERE id_product = $new_product_id");
            }
        } else {
            // ==========================================
            // AKSI: UPDATE PRODUCT & DIRECT ADJUSTMENT
            // ==========================================
            $total_stok_aktual = 0;
            $tgl_now = date('Y-m-d H:i:s');

            if (isset($_POST['stok_gudang']) && is_array($_POST['stok_gudang'])) {
                foreach ($_POST['stok_gudang'] as $id_lokasi => $qty_baru) {
                    $qty_baru = (int)$qty_baru;

                    // Hitung saldo riil gudang ini saat ini berdasarkan kalkulasi mutasi historis harian
                    $q_old = mysqli_query($conn, "SELECT 
                        (SELECT COALESCE(SUM(qty_masuk),0) FROM barang_masuk WHERE id_product = $id AND id_lokasi = $id_lokasi) AS masuk,
                        (SELECT COALESCE(SUM(qty_keluar),0) FROM barang_keluar WHERE id_product = $id AND id_lokasi = $id_lokasi) AS keluar");
                    $d_old = mysqli_fetch_assoc($q_old);
                    $stok_lama_gudang = $d_old['masuk'] - $d_old['keluar'];

                    // Bandingkan angka input baru vs angka kalkulasi lama untuk merekam mutasi penyeimbang
                    if ($qty_baru > $stok_lama_gudang) {
                        $selisih = $qty_baru - $stok_lama_gudang;
                        // Menggunakan id_supplier = 1 untuk menghindari FK Constraint Error
                        mysqli_query($conn, "INSERT INTO barang_masuk (id_product, id_supplier, id_lokasi, qty_masuk, tanggal_masuk, keterangan) 
                                             VALUES ($id, 1, $id_lokasi, $selisih, '$tgl_now', '[Adjustment Input Manual]')");
                    } elseif ($qty_baru < $stok_lama_gudang) {
                        $selisih = $stok_lama_gudang - $qty_baru;
                        mysqli_query($conn, "INSERT INTO barang_keluar (id_product, id_lokasi, qty_keluar, tanggal_keluar, keterangan) 
                                             VALUES ($id, $id_lokasi, $selisih, '$tgl_now', '[Adjustment Input Manual]')");
                    }
                    // Akumulasikan untuk total stok global baru
                    $total_stok_aktual += $qty_baru;
                }
            } else {
                $total_stok_aktual = $_POST['stok_aktual'];
            }

            $sql = "UPDATE product SET 
                    nama_product='$nama', sku='$sku', tipe_product='$tipe', 
                    harga_jual='$harga', biaya_standar='$biaya', 
                    stok_minimal='$stok_min', stok_aktual='$total_stok_aktual', satuan_unit='$satuan' 
                    WHERE id_product=$id";
            mysqli_query($conn, $sql);
        }

        mysqli_commit($conn);
        header("Location: product.php?msg=success");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
        exit;
    }
}

include 'header.php'; 

// 3. AMBIL DATA UNTUK EDIT
$val = [
    'id_product' => '', 'nama_product' => '', 'sku' => '', 
    'tipe_product' => 'storable', 'harga_jual' => 0, 
    'biaya_standar' => 0, 'satuan_unit' => 'pcs', 'stok_minimal' => 0, 'stok_aktual' => 0
];

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM product WHERE id_product = $id");
    if ($res) $val = mysqli_fetch_assoc($res);
}
?>

<div class="header-bar">
    <h1>Master Products</h1>
    <button onclick="toggleForm()" class="btn-orange">
        <?= isset($_GET['edit']) ? 'Editing Mode' : '+ Create New Product' ?>
    </button>
</div>

<div id="form-product" class="card" style="display: <?= isset($_GET['edit']) ? 'block' : 'none' ?>; margin-bottom: 25px;">
    <h3 style="margin-bottom: 15px;"><?= empty($val['id_product']) ? 'Add New' : 'Edit' ?> Product</h3>
    <form method="POST" action="product.php">
        <input type="hidden" name="id_product" value="<?= $val['id_product'] ?>">
        
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
            <div style="grid-column: span 2;">
                <label>Nama Produk</label>
                <input type="text" name="nama_product" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['nama_product'] ?>" required>
            </div>
            <div style="grid-column: span 2;">
                <label>SKU (Internal Reference)</label>
                <input type="text" name="sku" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['sku'] ?>" placeholder="e.g. LAP-001">
            </div>
            <div>
                <label>Tipe Produk</label>
                <select name="tipe_product" style="width:100%; padding:8px; margin-top:5px;">
                    <option value="storable" <?= $val['tipe_product'] == 'storable' ? 'selected' : '' ?>>Storable Product</option>
                    <option value="consumable" <?= $val['tipe_product'] == 'consumable' ? 'selected' : '' ?>>Consumable</option>
                    <option value="service" <?= $val['tipe_product'] == 'service' ? 'selected' : '' ?>>Service</option>
                </select>
            </div>
            <div>
                <label>Satuan Unit</label>
                <input type="text" name="satuan_unit" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['satuan_unit'] ?>">
            </div>
            <div>
                <label>Stok Minimal</label>
                <input type="number" name="stok_minimal" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['stok_minimal'] ?>">
            </div>
            <div>
                <label style="color: #ef7d00; font-weight: bold;">Stok Aktual (On Hand Total)</label>
                <input type="number" id="total_stok_global" name="stok_aktual" style="width:100%; padding:8px; margin-top:5px; border: 1px solid #ef7d00; background: #fdfdfd;" value="<?= $val['stok_aktual'] ?>" readonly>
            </div>
            <div style="grid-column: span 2;">
                <label>Harga Jual</label>
                <input type="number" step="0.01" name="harga_jual" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['harga_jual'] ?>">
            </div>
            <div style="grid-column: span 2;">
                <label>Biaya Standar (Modal)</label>
                <input type="number" step="0.01" name="biaya_standar" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['biaya_standar'] ?>">
            </div>
        </div>

        <div style="margin-top: 25px; padding: 15px; border-left: 4px solid #ef7d00; background: #fafafa; border-radius: 4px;">
            <h4 style="margin: 0 0 10px 0; color: #333;"><i class="fa fa-cubes"></i> Stock Quantities per Location Warehouse</h4>
            <p style="margin: 0 0 15px 0; font-size: 11px; color: #666;">Silakan ubah jumlah kuantitas stok langsung di masing-masing lokasi gudang berikut. Nilai total akumulasi di atas akan otomatis menyesuaikan diri.</p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <?php
                // Query seluruh daftar master lokasi gudang
                $gudang_query = mysqli_query($conn, "SELECT id_lokasi, nama_gudang, blok_rak, nomor_tingkat FROM lokasi ORDER BY nama_gudang ASC");
                if (mysqli_num_rows($gudang_query) > 0) {
                    while ($gd = mysqli_fetch_assoc($gudang_query)) {
                        $id_lokasi = $gd['id_lokasi'];
                        $stok_gudang_ini = 0;

                        // Jika sedang mengedit produk tertentu, hitung total saldo bersih di lokasi ini
                        if (!empty($val['id_product'])) {
                            $id_prod_curr = $val['id_product'];
                            $q_calc = mysqli_query($conn, "SELECT 
                                (SELECT COALESCE(SUM(qty_masuk),0) FROM barang_masuk WHERE id_product = $id_prod_curr AND id_lokasi = $id_lokasi) AS masuk,
                                (SELECT COALESCE(SUM(qty_keluar),0) FROM barang_keluar WHERE id_product = $id_prod_curr AND id_lokasi = $id_lokasi) AS keluar");
                            $d_calc = mysqli_fetch_assoc($q_calc);
                            $stok_gudang_ini = $d_calc['masuk'] - $d_calc['keluar'];
                        }
                        
                        echo '<div style="background: #fff; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; display: flex; align-items: center; justify-content: space-between;">';
                        echo '  <div>';
                        echo '      <strong style="font-size:13px; color:#2d3748;">WH/' . strtoupper($gd['nama_gudang']) . '</strong><br>';
                        echo '      <small style="color:#718096;">Rak: ' . $gd['blok_rak'] . ' | Lvl: ' . $gd['nomor_tingkat'] . '</small>';
                        echo '  </div>';
                        echo '  <div style="width: 120px;">';
                        echo '      <input type="number" name="stok_gudang['.$id_lokasi.']" class="qty-gudang-input" min="0" style="width:100%; padding:6px; text-align:right; font-weight:bold; border:1px solid #cbd5e0; border-radius:4px;" value="' . $stok_gudang_ini . '" oninput="hitungUlangTotalGlobal()">';
                        echo '  </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div style="grid-column: span 2; color:#e53e3e; font-size:12px;">Belum ada master lokasi gudang. Sila buat di lokasi.php lebih dulu.</div>';
                }
                ?>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" name="save_product" class="btn-orange">Simpan Produk</button>
            <a href="product.php" class="btn-orange" style="background:#888; text-decoration:none;">Batal</a>
        </div>
    </form>
</div>

<div style="margin-bottom: 15px;">
    <form method="GET" action="product.php" style="display:flex; gap:10px; align-items:center;">
        <input type="text" name="search" placeholder="Cari Nama Produk atau SKU..." style="padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px;" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit" class="btn-orange">Search</button>
        <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
            <a href="product.php" class="btn-orange" style="background:#888; text-decoration:none;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th style="text-align:center">Stok On Hand</th>
                <th style="text-align:right">Sales Price</th>
                <th style="text-align:right">Cost</th>
                <th style="text-align:center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $search_query = "";
            if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
                $search = mysqli_real_escape_string($conn, $_GET['search']);
                $search_query = " WHERE nama_product LIKE '%$search%' OR sku LIKE '%$search%' ";
            }
            $result = mysqli_query($conn, "SELECT * FROM product $search_query ORDER BY id_product DESC");
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    $stok_style = ($row['stok_aktual'] <= $row['stok_minimal']) ? "color:red; font-weight:bold;" : "font-weight:bold;";
                    
                    echo "<tr>
                        <td><code>" . ($row['sku'] ?: '-') . "</code></td>
                        <td><strong>{$row['nama_product']}</strong><br><small>Unit: {$row['satuan_unit']}</small></td>
                        <td style='text-align:center; $stok_style'>
                            {$row['stok_aktual']} <br>
                            <small style='color:#888; font-weight:normal;'>Min: {$row['stok_minimal']}</small>
                        </td>
                        <td style='text-align:right'>" . number_format($row['harga_jual'], 0, ',', '.') . "</td>
                        <td style='text-align:right'>" . number_format($row['biaya_standar'], 0, ',', '.') . "</td>
                        <td style='text-align:center'>
                            <a href='product.php?edit={$row['id_product']}' style='color:#ef7d00; text-decoration:none; font-weight:bold;'>Edit</a> | 
                            <a href='product.php?delete={$row['id_product']}' style='color:#d9534f; text-decoration:none; font-weight:bold;' onclick='return confirm(\"Hapus produk ini?\")'>Delete</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>Belum ada data produk.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
function toggleForm() {
    var x = document.getElementById("form-product");
    x.style.display = (x.style.display === "none") ? "block" : "none";
}

function hitungUlangTotalGlobal() {
    var inputs = document.getElementsByClassName('qty-gudang-input');
    var total = 0;
    for (var i = 0; i < inputs.length; i++) {
        total += parseInt(inputs[i].value) || 0;
    }
    document.getElementById('total_stok_global').value = total;
}
</script>

<?php echo "</div></body></html>"; ?>