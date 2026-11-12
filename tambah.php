<?php
session_start();
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}
require 'functions.php';

if (isset($_POST['submit'])) {
    try {
        if (tambah($_POST) > 0) {
            $_SESSION['notifikasi_sukses'] = 'Data mahasiswa berhasil ditambahkan.';
            header('Location: index.php');
            exit;
        } else {
            echo "<script>alert('Gagal menambahkan data. Periksa foto atau input!');</script>";
        }
    } catch (mysqli_sql_exception $e) {
        echo "<script>alert('NIM sudah terdaftar atau terjadi kesalahan database!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Mahasiswa</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 30px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px #ddd; }
        label { display: block; margin-top: 15px; margin-bottom: 7px; }
        input { width: 100%; padding: 11px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 11px 18px; background: #198754; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .kembali { display: inline-block; margin-top: 20px; color: #333; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h2>Tambah Data Mahasiswa</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" required>
        <label>NIM</label>
        <input type="text" name="nim" maxlength="10" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Program Studi</label>
        <input type="text" name="prodi" required>
        <label>Jurusan</label>
        <input type="text" name="jurusan" required>
        <label for="gambar">Foto Mahasiswa</label>
        <input type="file" name="gambar" id="gambar" accept=".jpg,.jpeg,.png">
        <button type="submit" name="submit">Simpan Data</button>
    </form>
    <a href="index.php" class="kembali">&larr; Kembali ke Data Mahasiswa</a>
</div>
</body>
</html>