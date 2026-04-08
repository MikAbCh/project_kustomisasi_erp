<?php 
include 'config.php'; 
include 'header.php'; 

// 1. LOGIKA DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (mysqli_query($conn, "DELETE FROM product WHERE id_product = $id")) {
        header("Location: product.php?msg=deleted");
    }
}

// 2. LOGIKA SAVE (Create & Update)
if (isset($_POST['save_product'])) {
    $id      = $_POST['id_product'];
    $nama    = mysqli_real_escape_string($conn, $_POST['nama_product']);
    $sku     = mysqli_real_escape_string($conn, $_POST['sku']);
    $tipe    = $_POST['tipe_product'];
    $harga   = $_POST['harga_jual'];
    $biaya   = $_POST['biaya_standar'];
    $satuan  = $_POST['satuan_unit'];
    $stok_min = $_POST['stok_minimal'];

    if (empty($id)) {
        // Create - Sesuaikan dengan kolom DESC Anda
        $sql = "INSERT INTO product (nama_product, sku, tipe_product, harga_jual, biaya_standar, stok_minimal, satuan_unit) 
                VALUES ('$nama', '$sku', '$tipe', '$harga', '$biaya', '$stok_min', '$satuan')";
    } else {
        // Update - Sesuaikan dengan kolom DESC Anda
        $sql = "UPDATE product SET 
                nama_product='$nama', sku='$sku', tipe_product='$tipe', 
                harga_jual='$harga', biaya_standar='$biaya', 
                stok_minimal='$stok_min', satuan_unit='$satuan' 
                WHERE id_product=$id";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: product.php?msg=success");
    }
}

// 3. AMBIL DATA UNTUK EDIT
$val = [
    'id_product' => '', 'nama_product' => '', 'sku' => '', 
    'tipe_product' => 'storable', 'harga_jual' => 0, 
    'biaya_standar' => 0, 'satuan_unit' => 'pcs', 'stok_minimal' => 0
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
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
            <div style="grid-column: span 2;">
                <label>Nama Produk</label>
                <input type="text" name="nama_product" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['nama_product'] ?>" required>
            </div>
            <div>
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
                <label>Harga Jual</label>
                <input type="number" step="0.01" name="harga_jual" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['harga_jual'] ?>">
            </div>
            <div>
                <label>Biaya Standar (Modal)</label>
                <input type="number" step="0.01" name="biaya_standar" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['biaya_standar'] ?>">
            </div>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" name="save_product" class="btn-orange">Simpan Produk</button>
            <a href="product.php" class="btn-orange" style="background:#888; text-decoration:none;">Batal</a>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Type</th>
                <th style="text-align:right">Sales Price</th>
                <th style="text-align:right">Cost</th>
                <th style="text-align:center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM product ORDER BY id_product DESC");
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td><code>" . ($row['sku'] ?: '-') . "</code></td>
                        <td><strong>{$row['nama_product']}</strong><br><small>Unit: {$row['satuan_unit']}</small></td>
                        <td>" . ucfirst($row['tipe_product']) . "</td>
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
</script>

<?php echo "</div></body></html>"; ?>