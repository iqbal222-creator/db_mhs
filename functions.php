<?php

$conn = mysqli_connect("localhost", "root", "", "db_mahasiswa");

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Mengambil data dari database
function query($sql) {
    global $conn;

    $result = mysqli_query($conn, $sql);
    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}

// Fungsi Registrasi User Baru
function registrasi($data) {
    global $conn;

    $username = strtolower(stripslashes($data["username"]));
    $password = mysqli_real_escape_string($conn, $data["password"]);
    $confirm_pwd = mysqli_real_escape_string($conn, $data["confirm_pwd"]);

    // Cek username sudah ada atau belum
    $result = mysqli_query($conn, "SELECT username FROM tb_user WHERE username = '$username'");
    if (mysqli_fetch_assoc($result)) {
        echo "<script>
                alert('Maaf! Username sudah ada');
              </script>";
        return false;
    }

    // Konfirmasi password
    if ($password !== $confirm_pwd) {
        echo "<script>
                alert('Password tidak sesuai');
              </script>";
        return false;
    }

    // Enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);

    // Tambahkan user baru ke database
    mysqli_query($conn, "INSERT INTO tb_user VALUES('', '$username', '$password')");

    return mysqli_affected_rows($conn);
}

// Mencari data mahasiswa berdasarkan kata kunci
function cari($keyword) {
    global $conn;

    $sql = "SELECT * FROM mahasiswa
            WHERE nama LIKE ?
               OR nim LIKE ?
               OR email LIKE ?
               OR prodi LIKE ?
               OR jurusan LIKE ?
            ORDER BY nama ASC";

    $stmt = mysqli_prepare($conn, $sql);
    $pola = '%' . $keyword . '%';

    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $pola,
        $pola,
        $pola,
        $pola,
        $pola
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $rows;
}

// Membersihkan input
function aman($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Menyimpan foto dengan nama file asli agar mudah dikenali
function uploadFoto($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) {
        return false;
    }

    $namaFile = basename($file['name']);
    $ekstensi = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
    $ekstensiValid = ['jpg', 'jpeg', 'png'];

    if (
        $namaFile === ''
        || strlen($namaFile) > 255
        || !in_array($ekstensi, $ekstensiValid, true)
        || getimagesize($file['tmp_name']) === false
    ) {
        return false;
    }

    $folder = __DIR__ . '/img/';

    if (!is_dir($folder) && !mkdir($folder, 0777, true)) {
        return false;
    }

    $tujuan = $folder . $namaFile;

    if (file_exists($tujuan)) {
        $hashSumber = hash_file('sha256', $file['tmp_name']);
        $hashTujuan = hash_file('sha256', $tujuan);

        return $hashSumber !== false && $hashSumber === $hashTujuan
            ? $namaFile
            : false;
    }

    if (move_uploaded_file($file['tmp_name'], $tujuan)) {
        return $namaFile;
    }

    return false;
}

// Menambahkan data mahasiswa
function tambah($data) {
    global $conn;

    $nama = trim($data['nama']);
    $nim = trim($data['nim']);
    $email = trim($data['email']);
    $prodi = trim($data['prodi']);
    $jurusan = trim($data['jurusan']);

    $gambar = uploadFoto($_FILES['gambar'] ?? null);

    if ($gambar === false) {
        return 0;
    }

    $sql = "INSERT INTO mahasiswa
            (nama, nim, email, prodi, jurusan, gambar)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $nama,
        $nim,
        $email,
        $prodi,
        $jurusan,
        $gambar
    );

    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt);

    return $hasil;
}

// Mengubah data mahasiswa
function ubah($data) {
    global $conn;

    $nimLama = $data['nim_lama'];
    $nama = trim($data['nama']);
    $nim = trim($data['nim']);
    $email = trim($data['email']);
    $prodi = trim($data['prodi']);
    $jurusan = trim($data['jurusan']);

    $gambarLama = $data['gambar_lama'];
    $gambarBaru = uploadFoto($_FILES['gambar'] ?? null);

    if ($gambarBaru === false) {
        return 0;
    }

    $gambar = $gambarBaru ?? $gambarLama;

    $sql = "UPDATE mahasiswa SET
            nama = ?,
            nim = ?,
            email = ?,
            prodi = ?,
            jurusan = ?,
            gambar = ?
            WHERE nim = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sssssss",
        $nama,
        $nim,
        $email,
        $prodi,
        $jurusan,
        $gambar,
        $nimLama
    );

    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt);

    return $hasil;
}

// Menghapus data mahasiswa
function hapus($nim) {
    global $conn;

    $sql = "DELETE FROM mahasiswa WHERE nim = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $nim);

    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt);

    return $hasil;
}

?>