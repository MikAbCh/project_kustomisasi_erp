<?php 
include 'config.php'; 

// 1. LOGIKA SAVE (Create & Update)
if (isset($_POST['save_lokasi'])) {
    $id    = $_POST['id_lokasi'];
    $nama  = mysqli_real_escape_string($conn, $_POST['nama_gudang']);
    $blok  = mysqli_real_escape_string($conn, $_POST['blok_rak']);
    $level = $_POST['nomor_tingkat'];

    if (empty($id)) {
        $sql = "INSERT INTO lokasi (nama_gudang, blok_rak, nomor_tingkat) VALUES ('$nama', '$blok', '$level')";
    } else {
        $sql = "UPDATE lokasi SET nama_gudang='$nama', blok_rak='$blok', nomor_tingkat='$level' WHERE id_lokasi=$id";
    }
    
    if (mysqli_query($conn, $sql)) {
        header("Location: lokasi.php?msg=success");
    }
}

// 2. LOGIKA DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Cek apakah lokasi masih digunakan di transaksi sebelum hapus (Best Practice ERP)
    $check_m = mysqli_query($conn, "SELECT id_masuk FROM barang_masuk WHERE id_lokasi = $id");
    if (mysqli_num_rows($check_m) > 0) {
        header("Location: lokasi.php?msg=error_used");
    } else {
        mysqli_query($conn, "DELETE FROM lokasi WHERE id_lokasi = $id");
        header("Location: lokasi.php?msg=deleted");
    }
}

// 3. AMBIL DATA UNTUK EDIT
$val = ['id_lokasi' => '', 'nama_gudang' => '', 'blok_rak' => '', 'nomor_tingkat' => '1'];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $res = mysqli_query($conn, "SELECT * FROM lokasi WHERE id_lokasi = $id");
    $val = mysqli_fetch_assoc($res);
}
include 'header.php'; 
?>

<div class="header-bar">
    <h1><i class="fa fa-map-marker-alt"></i> Warehouse Locations</h1>
    <button onclick="toggleForm()" class="btn-orange"><?= isset($_GET['edit']) ? 'Edit Mode' : '+ Create Location' ?></button>
</div>

<!-- FORM LOKASI -->
<div id="form-lokasi" class="card" style="display: <?= isset($_GET['edit']) ? 'block' : 'none' ?>; margin-bottom: 25px;">
    <form method="POST">
        <input type="hidden" name="id_lokasi" value="<?= $val['id_lokasi'] ?>">
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px;">
            <div>
                <label><strong>Warehouse Name</strong></label>
                <input type="text" name="nama_gudang" class="form-control" placeholder="e.g. Gudang Utama" value="<?= $val['nama_gudang'] ?>" required style="width:100%; padding:8px;">
            </div>
            <div>
                <label><strong>Rack Block</strong></label>
                <input type="text" name="blok_rak" class="form-control" placeholder="e.g. A1" value="<?= $val['blok_rak'] ?>" required style="width:100%; padding:8px;">
            </div>
            <div>
                <label><strong>Level (Tingkat)</strong></label>
                <input type="number" name="nomor_tingkat" class="form-control" value="<?= $val['nomor_tingkat'] ?>" style="width:100%; padding:8px;">
            </div>
        </div>
        <div style="margin-top: 15px;">
            <button type="submit" name="save_lokasi" class="btn-orange">Save Location</button>
            <a href="lokasi.php" class="btn-orange" style="background:#888; text-decoration:none;">Cancel</a>
        </div>
    </form>
</div>

<!-- DATA TABEL -->
<div class="card">
    <table class="table-odoo">
        <thead>
            <tr>
                <th>Location Reference</th>
                <th>Warehouse</th>
                <th>Rack</th>
                <th>Level</th>
                <th style="text-align:center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = mysqli_query($conn, "SELECT * FROM lokasi ORDER BY nama_gudang ASC, blok_rak ASC");
            while($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td>
                    <span style="color: #ef7d00; font-weight: bold;">
                        WH/<?= strtoupper($row['nama_gudang']) ?>/<?= $row['blok_rak'] ?>/L<?= $row['nomor_tingkat'] ?>
                    </span>
                </td>
                <td><?= $row['nama_gudang'] ?></td>
                <td><span class="badge bg-light"><?= $row['blok_rak'] ?></span></td>
                <td>Level <?= $row['nomor_tingkat'] ?></td>
                <td style="text-align:center">
                    <a href="lokasi.php?edit=<?= $row['id_lokasi'] ?>" style="color:#ef7d00; text-decoration:none;">Edit</a> | 
                    <a href="lokasi.php?delete=<?= $row['id_lokasi'] ?>" style="color:red; text-decoration:none;" onclick="return confirm('Hapus lokasi ini?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
function toggleForm() {
    var x = document.getElementById("form-lokasi");
    x.style.display = (x.style.display === "none") ? "block" : "none";
}
</script>