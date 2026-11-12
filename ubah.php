<?php
session_start();
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}
require 'functions.php';

if (!isset($_GET['nim'])) {
    header("Location: index.php");
    exit;
}

$nimCari = $_GET['nim'];
$stmt = mysqli_prepare($conn, "SELECT * FROM mahasiswa WHERE nim = ?");
mysqli_stmt_bind_param($stmt, "s", $nimCari);
mysqli_stmt_execute($stmt);
$mhs = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$mhs) {
    echo "<script>alert('Data mahasiswa tidak ditemukan!'); document.location.href = 'index.php';</script>";
    exit;
}

if (isset($_POST['submit'])) {
    try {
        if (ubah($_POST) > 0) {
            $_SESSION['notifikasi_sukses'] = 'Data mahasiswa berhasil diubah.';
            header('Location: index.php');
            exit;
        } else {
            echo "<script>alert('Tidak ada perubahan data atau gagal mengubah data!'); document.location.href = 'index.php';</script>";
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        echo "<script>alert('NIM sudah digunakan mahasiswa lain atau terjadi kesalahan!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ubah Data Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#F8FAFC] text-slate-800 antialiased">
<main class="mx-auto max-w-5xl px-4 py-10">
    <div class="mb-8">
        <a href="index.php" class="text-sm font-medium text-slate-500 hover:text-blue-600">&larr; Kembali</a>
        <h1 class="mt-2 text-2xl font-semibold text-slate-900">Ubah Data Mahasiswa</h1>
    </div>
    <form method="post" enctype="multipart/form-data" class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
        <input type="hidden" name="nim_lama" value="<?= aman($nimCari); ?>">
        <input type="hidden" name="gambar_lama" value="<?= aman($mhs['gambar'] ?? ''); ?>">

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
                <input type="text" name="nama" value="<?= aman($mhs['nama']); ?>" required class="w-full mt-1 p-2.5 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">NIM</label>
                <input type="text" name="nim" maxlength="10" value="<?= aman($mhs['nim']); ?>" required class="w-full mt-1 p-2.5 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="<?= aman($mhs['email']); ?>" required class="w-full mt-1 p-2.5 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Program Studi</label>
                <input type="text" name="prodi" value="<?= aman($mhs['prodi']); ?>" required class="w-full mt-1 p-2.5 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Jurusan</label>
                <input type="text" name="jurusan" value="<?= aman($mhs['jurusan']); ?>" required class="w-full mt-1 p-2.5 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Foto Baru (Opsional)</label>
                <input type="file" name="gambar" accept=".jpg,.jpeg,.png" class="mt-1">
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" name="submit" class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-blue-700">Simpan Perubahan</button>
        </div>
    </form>
</main>
</body>
</html>