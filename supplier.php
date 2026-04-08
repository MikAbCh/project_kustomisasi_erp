<?php 
include 'config.php'; 
include 'header.php'; 

// 1. LOGIKA DELETE (Hapus Data)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM supplier WHERE id_supplier = $id";
    if (mysqli_query($conn, $query)) {
        header("Location: supplier.php?msg=deleted");
    }
}

// 2. LOGIKA SAVE (Create & Update)
if (isset($_POST['save_supplier'])) {
    $id     = $_POST['id_supplier'];
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_perusahaan']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat_lengkap']);
    $email  = mysqli_real_escape_string($conn, $_POST['email_bisnis']);
    $telp   = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $npwp   = mysqli_real_escape_string($conn, $_POST['npwp_tax_id']);
    $term   = $_POST['term_pembayaran_default'];

    if (empty($id)) {
        // Proses Create
        $sql = "INSERT INTO supplier (nama_perusahaan, alamat_lengkap, email_bisnis, no_telepon, npwp_tax_id, term_pembayaran_default) 
                VALUES ('$nama', '$alamat', '$email', '$telp', '$npwp', '$term')";
    } else {
        // Proses Update
        $sql = "UPDATE supplier SET 
                nama_perusahaan='$nama', alamat_lengkap='$alamat', email_bisnis='$email', 
                no_telepon='$telp', npwp_tax_id='$npwp', term_pembayaran_default='$term' 
                WHERE id_supplier=$id";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: supplier.php?msg=success");
    }
}

// 3. LOGIKA AMBIL DATA UNTUK EDIT
$val = [
    'id_supplier' => '', 'nama_perusahaan' => '', 'alamat_lengkap' => '', 
    'email_bisnis' => '', 'no_telepon' => '', 'npwp_tax_id' => '', 'term_pembayaran_default' => 'Net 30'
];

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM supplier WHERE id_supplier = $id");
    if ($res) {
        $val = mysqli_fetch_assoc($res);
    }
}
?>

<div class="header-bar">
    <h1>Master Suppliers</h1>
    <button onclick="toggleForm()" class="btn-orange">
        <?= isset($_GET['edit']) ? 'Editing Mode' : '+ Create New Supplier' ?>
    </button>
</div>

<div id="form-supplier" class="card" style="display: <?= isset($_GET['edit']) ? 'block' : 'none' ?>; margin-bottom: 25px;">
    <h3 style="margin-bottom: 15px;"><?= empty($val['id_supplier']) ? 'Add New' : 'Edit' ?> Supplier</h3>
    <form method="POST" action="supplier.php">
        <input type="hidden" name="id_supplier" value="<?= $val['id_supplier'] ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <label>Nama Perusahaan</label>
                <input type="text" name="nama_perusahaan" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['nama_perusahaan'] ?>" required>
            </div>
            <div>
                <label>Email Bisnis</label>
                <input type="email" name="email_bisnis" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['email_bisnis'] ?>">
            </div>
            <div style="grid-column: span 2;">
                <label>Alamat Lengkap</label>
                <textarea name="alamat_lengkap" style="width:100%; padding:8px; margin-top:5px;" rows="2"><?= $val['alamat_lengkap'] ?></textarea>
            </div>
            <div>
                <label>No. Telepon</label>
                <input type="text" name="no_telepon" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['no_telepon'] ?>">
            </div>
            <div>
                <label>NPWP / Tax ID</label>
                <input type="text" name="npwp_tax_id" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['npwp_tax_id'] ?>">
            </div>
            <div>
                <label>Termin Pembayaran</label>
                <select name="term_pembayaran_default" style="width:100%; padding:8px; margin-top:5px;">
                    <option value="Cash" <?= $val['term_pembayaran_default'] == 'Cash' ? 'selected' : '' ?>>Cash on Delivery</option>
                    <option value="Net 15" <?= $val['term_pembayaran_default'] == 'Net 15' ? 'selected' : '' ?>>Net 15</option>
                    <option value="Net 30" <?= $val['term_pembayaran_default'] == 'Net 30' ? 'selected' : '' ?>>Net 30</option>
                </select>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" name="save_supplier" class="btn-orange">Simpan Data</button>
            <a href="supplier.php" class="btn-orange" style="background:#888; text-decoration:none;">Batal</a>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Company Name</th>
                <th>Contact Info</th>
                <th>Tax ID / NPWP</th>
                <th>Payment Term</th>
                <th style="text-align:center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM supplier ORDER BY id_supplier DESC");
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td>
                            <strong>{$row['nama_perusahaan']}</strong><br>
                            <small style='color:#666'>{$row['alamat_lengkap']}</small>
                        </td>
                        <td>
                            {$row['email_bisnis']}<br>
                            <small>{$row['no_telepon']}</small>
                        </td>
                        <td>{$row['npwp_tax_id']}</td>
                        <td><span class='badge bg-draft'>{$row['term_pembayaran_default']}</span></td>
                        <td style='text-align:center'>
                            <a href='supplier.php?edit={$row['id_supplier']}' style='color:#ef7d00; text-decoration:none; font-weight:bold;'>Edit</a> | 
                            <a href='supplier.php?delete={$row['id_supplier']}' style='color:#d9534f; text-decoration:none; font-weight:bold;' onclick='return confirm(\"Yakin ingin menghapus supplier ini?\")'>Delete</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>Belum ada data supplier.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
function toggleForm() {
    var x = document.getElementById("form-supplier");
    if (x.style.display === "none") {
        x.style.display = "block";
    } else {
        x.style.display = "none";
    }
}
</script>

<?php echo "</div></body></html>"; ?>