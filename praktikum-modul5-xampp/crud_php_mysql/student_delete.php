<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}
include("connection.php");

if (isset($_GET["nim"])) {
    $nim = htmlentities(strip_tags(trim($_GET["nim"])));
    $nim = mysqli_real_escape_string($connection, $nim);

    $query = "SELECT * FROM student WHERE nim='$nim'";
    $result = mysqli_query($connection, $query);
    if(!$result) {
        die ("Query Error: ".mysqli_errno($connection)." - ".mysqli_error($connection));
    }

    if (mysqli_num_rows($result) == 0) {
        $message = "Data mahasiswa tidak ditemukan";
    }
    else {
        $data = mysqli_fetch_assoc($result);
        $name = $data["name"];

        $query = "DELETE FROM student WHERE nim='$nim'";
        $delete = mysqli_query($connection, $query);
        if ($delete) {
            $message = "Mahasiswa dengan nama = \"<b>$name</b>\" sudah berhasil dihapus";
        }
        else {
            die ("Query gagal dijalankan: ".mysqli_errno($connection)." - ".mysqli_error($connection));
        }
    }
    mysqli_free_result($result);
}
else {
    $message = "NIM tidak ditemukan";
}

mysqli_close($connection);
$message = urlencode($message);
header("Location: student_view.php?message={$message}");
?>