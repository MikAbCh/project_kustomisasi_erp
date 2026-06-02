<?php 
include 'config.php'; 

// 1. LOGIKA DELETE (Hapus Data)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM customer WHERE id_customer = $id";
    if (mysqli_query($conn, $query)) {
        header("Location: customer.php?msg=deleted");
        exit;
    }
}

// 2. LOGIKA SAVE (Create & Update)
if (isset($_POST['save_customer'])) {
    $id     = $_POST['id_customer'];
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $email  = mysqli_real_escape_string($conn, $_POST['email']);
    $telp   = mysqli_real_escape_string($conn, $_POST['telepon']);
    $term   = $_POST['term_pembayaran'];

    if (empty($id)) {
        // Proses Create
        $sql = "INSERT INTO customer (nama_pelanggan, alamat, email, telepon, term_pembayaran) 
                VALUES ('$nama', '$alamat', '$email', '$telp', '$term')";
    } else {
        // Proses Update
        $sql = "UPDATE customer SET 
                nama_pelanggan='$nama', alamat='$alamat', email='$email', 
                telepon='$telp', term_pembayaran='$term' 
                WHERE id_customer=$id";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: customer.php?msg=success");
        exit;
    }
}

// 3. LOGIKA AMBIL DATA UNTUK EDIT
$val = [
    'id_customer' => '', 'nama_pelanggan' => '', 'alamat' => '', 
    'email' => '', 'telepon' => '', 'term_pembayaran' => 'Net 30'
];

if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM customer WHERE id_customer = $id");
    if ($res) {
        $val = mysqli_fetch_assoc($res);
    }
}
include 'header.php'; 
?>

<div class="header-bar">
    <h1>Master Customers</h1>
    <button onclick="toggleForm()" class="btn-orange">
        <?= isset($_GET['edit']) ? 'Editing Mode' : '+ Create New Customer' ?>
    </button>
</div>

<div id="form-customer" class="card" style="display: <?= isset($_GET['edit']) ? 'block' : 'none' ?>; margin-bottom: 25px;">
    <h3 style="margin-bottom: 15px;"><?= empty($val['id_customer']) ? 'Add New' : 'Edit' ?> Customer</h3>
    <form method="POST" action="customer.php">
        <input type="hidden" name="id_customer" value="<?= $val['id_customer'] ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <label>Nama Pelanggan / Perusahaan</label>
                <input type="text" name="nama_pelanggan" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['nama_pelanggan'] ?>" required>
            </div>
            <div>
                <label>Email Pelanggan</label>
                <input type="email" name="email" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['email'] ?>">
            </div>
            <div style="grid-column: span 2;">
                <label>Alamat Lengkap</label>
                <textarea name="alamat" style="width:100%; padding:8px; margin-top:5px;" rows="2"><?= $val['alamat'] ?></textarea>
            </div>
            <div>
                <label>No. Telepon</label>
                <input type="text" name="telepon" style="width:100%; padding:8px; margin-top:5px;" value="<?= $val['telepon'] ?>">
            </div>
            <div>
                <label>Termin Pembayaran Default</label>
                <select name="term_pembayaran" style="width:100%; padding:8px; margin-top:5px;">
                    <option value="Cash" <?= $val['term_pembayaran'] == 'Cash' ? 'selected' : '' ?>>Cash on Delivery</option>
                    <option value="Net 15" <?= $val['term_pembayaran'] == 'Net 15' ? 'selected' : '' ?>>Net 15</option>
                    <option value="Net 30" <?= $val['term_pembayaran'] == 'Net 30' ? 'selected' : '' ?>>Net 30</option>
                </select>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" name="save_customer" class="btn-orange">Simpan Data</button>
            <a href="customer.php" class="btn-orange" style="background:#888; text-decoration:none;">Batal</a>
        </div>
    </form>
</div>

<div style="margin-bottom: 15px;">
    <form method="GET" action="customer.php" style="display:flex; gap:10px; align-items:center;">
        <input type="text" name="search" placeholder="Cari Nama Pelanggan atau Email..." style="padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px;" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit" class="btn-orange">Search</button>
        <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
            <a href="customer.php" class="btn-orange" style="background:#888; text-decoration:none;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Contact Info</th>
                <th>Payment Term</th>
                <th style="text-align:center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $search_query = "";
            if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
                $search = mysqli_real_escape_string($conn, $_GET['search']);
                $search_query = " WHERE nama_pelanggan LIKE '%$search%' OR email LIKE '%$search%' ";
            }
            $result = mysqli_query($conn, "SELECT * FROM customer $search_query ORDER BY id_customer DESC");
            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td>
                            <strong>{$row['nama_pelanggan']}</strong><br>
                            <small style='color:#666'>{$row['alamat']}</small>
                        </td>
                        <td>
                            {$row['email']}<br>
                            <small>{$row['telepon']}</small>
                        </td>
                        <td><span class='badge bg-draft'>{$row['term_pembayaran']}</span></td>
                        <td style='text-align:center'>
                            <a href='customer.php?edit={$row['id_customer']}' style='color:#ef7d00; text-decoration:none; font-weight:bold;'>Edit</a> | 
                            <a href='customer.php?delete={$row['id_customer']}' style='color:#d9534f; text-decoration:none; font-weight:bold;' onclick='return confirm(\"Yakin ingin menghapus customer ini?\")'>Delete</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center; padding:20px;'>Belum ada data customer.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
function toggleForm() {
    var x = document.getElementById("form-customer");
    if (x.style.display === "none") {
        x.style.display = "block";
    } else {
        x.style.display = "none";
    }
}
</script>

<?php echo "</div></body></html>"; ?>