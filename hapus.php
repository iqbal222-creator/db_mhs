<?php
session_start();
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['nim'])) {
    header("Location: index.php");
    exit;
}

$nim = $_POST['nim'];

try {
    if (hapus($nim) > 0) {
        $_SESSION['notifikasi_sukses'] = 'Data mahasiswa berhasil dihapus.';
        header('Location: index.php');
        exit;
    } else {
        echo "<script>alert('Data mahasiswa tidak ditemukan!'); document.location.href = 'index.php';</script>";
    }
} catch (mysqli_sql_exception $e) {
    echo "<script>alert('Gagal menghapus data mahasiswa!'); document.location.href = 'index.php';</script>";
}
exit;
?>