<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
}
include("connection.php");

if (isset($_POST["submit"])) {
    $nim = htmlentities(strip_tags(trim($_POST["nim"])));
    $name = htmlentities(strip_tags(trim($_POST["name"])));
    $birth_city = htmlentities(strip_tags(trim($_POST["birth_city"])));
    $faculty = htmlentities(strip_tags(trim($_POST["faculty"])));
    $department = htmlentities(strip_tags(trim($_POST["department"])));
    $gpa = htmlentities(strip_tags(trim($_POST["gpa"])));
    $birth_date = htmlentities(strip_tags(trim($_POST["birth_date"])));
    $birth_month = htmlentities(strip_tags(trim($_POST["birth_month"])));
    $birth_year = htmlentities(strip_tags(trim($_POST["birth_year"])));

    $error_message = "";

    if (empty($name)) {
        $error_message .= "- Nama belum diisi <br>";
    }
    if (empty($birth_city)) {
        $error_message .= "- Tempat lahir belum diisi <br>";
    }
    if (empty($department)) {
        $error_message .= "- Jurusan belum diisi <br>";
    }

    $select_ftib = ""; $select_fteic = "";
    switch ($faculty) {
        case 'FTIB':
            $select_ftib = "selected";
            break;
        case 'FTEIC':
            $select_fteic = "selected";
            break;
    }

    if (!is_numeric($gpa) OR ($gpa <= 0)) {
        $error_message .= "- IPK harus diisi dengan angka";
    }

    if ($error_message === "") {
        $nim = mysqli_real_escape_string($connection, $nim);
        $name = mysqli_real_escape_string($connection, $name);
        $birth_city = mysqli_real_escape_string($connection, $birth_city);
        $faculty = mysqli_real_escape_string($connection, $faculty);
        $department = mysqli_real_escape_string($connection, $department);
        $birth_date = mysqli_real_escape_string($connection, $birth_date);
        $birth_month = mysqli_real_escape_string($connection, $birth_month);
        $birth_year = mysqli_real_escape_string($connection, $birth_year);
        $gpa = (float) $gpa;

        $birth_date_full = $birth_year."-".$birth_month."-".$birth_date;

        $query = "UPDATE student SET ";
        $query .= "name='$name', birth_city='$birth_city', ";
        $query .= "birth_date='$birth_date_full', faculty='$faculty', ";
        $query .= "department='$department', gpa=$gpa ";
        $query .= "WHERE nim='$nim'";

        $result = mysqli_query($connection, $query);
        if($result) {
            $message = "Data mahasiswa dengan nama = \"<b>$name</b>\" sudah berhasil diubah";
            $message = urlencode($message);
            header("Location: student_view.php?message={$message}");
        }
        else {
            die ("Query gagal dijalankan: ".mysqli_errno($connection). " - ".mysqli_error($connection));
        }
    }
}
else {
    if (!isset($_GET["nim"])) {
        header("Location: student_view.php");
        exit;
    }
    $error_message = "";
    $nim = mysqli_real_escape_string($connection, $_GET["nim"]);
    $query = "SELECT * FROM student WHERE nim='$nim'";
    $result = mysqli_query($connection, $query);
    if(!$result) {
        die ("Query Error: ".mysqli_errno($connection)." - ".mysqli_error($connection));
    }
    if (mysqli_num_rows($result) == 0) {
        header("Location: student_view.php");
        exit;
    }
    $data = mysqli_fetch_assoc($result);
    mysqli_free_result($result);

    $nim = $data["nim"];
    $name = $data["name"];
    $birth_city = $data["birth_city"];
    $department = $data["department"];
    $gpa = $data["gpa"];
    $select_ftib = ($data["faculty"] == "FTIB") ? "selected" : "";
    $select_fteic = ($data["faculty"] == "FTEIC") ? "selected" : "";

    $birth_parts = explode("-", $data["birth_date"]);
    $birth_year = (int) $birth_parts[0];
    $birth_month = (int) $birth_parts[1];
    $birth_date = (int) $birth_parts[2];
}

$arr_month = [
    "1"=>"Januari",
    "2"=>"Februari",
    "3"=>"Maret",
    "4"=>"April",
    "5"=>"Mei",
    "6"=>"Juni",
    "7"=>"Juli",
    "8"=>"Agustus",
    "9"=>"September",
    "10"=>"Oktober",
    "11"=>"Nopember",
    "12"=>"Desember"
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Mahasiswa</title>
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div id="header">
        <h1 id="logo">Data Mahasiswa</h1>
    </div>
    <hr>
    <nav>
        <ul>
            <li><a href="student_view.php">Tampil</a></li>
            <li><a href="student_add.php">Tambah</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <h2>Edit Data Mahasiswa</h2>
    <?php
    if ($error_message !== "") {
        echo "<div class='error'>$error_message</div>";
    }
    ?>
    <form id="form_mahasiswa" action="student_edit.php" method="post">
        <fieldset>
            <legend>Edit Mahasiswa</legend>
            <p>
                <label for="nim">NIM : </label>
                <input type="text" id="nim" value="<?php echo $nim ?>" disabled> (tidak dapat diubah)
                <input type="hidden" name="nim" value="<?php echo $nim ?>">
            </p>
            <p>
                <label for="name">Nama : </label>
                <input type="text" name="name" id="name" value="<?php echo $name ?>">
            </p>
            <p>
                <label for="birth_city">Tempat Lahir : </label>
                <input type="text" name="birth_city" id="birth_city" value="<?php echo $birth_city ?>">
            </p>
            <p>
                <label for="birth_date">Tanggal Lahir : </label>
                <select name="birth_date" id="birth_date">
                    <?php
                    for ($i = 1; $i <= 31; $i++) {
                        if ($i == $birth_date){
                            echo "<option value=$i selected>";
                        }
                        else {
                            echo "<option value=$i>";
                        }
                        echo str_pad($i, 2, "0", STR_PAD_LEFT);
                        echo "</option>";
                    }
                    ?>
                </select>
                <select name="birth_month">
                    <?php
                    foreach ($arr_month as $key => $value) {
                        if ($key == $birth_month){
                            echo "<option value=\"{$key}\" selected>{$value}</option>";
                        }
                        else {
                            echo "<option value=\"{$key}\">{$value}</option>";
                        }
                    }
                    ?>
                </select>
                <select name="birth_year">
                    <?php
                    for ($i = 1990; $i <= 2005; $i++) {
                        if ($i == $birth_year){
                            echo "<option value=$i selected>";
                        }
                        else {
                            echo "<option value=$i>";
                        }
                        echo "$i </option>";
                    }
                    ?>
                </select>
            </p>
            <p>
                <label for="faculty">Fakultas : </label>
                <select name="faculty" id="faculty">
                    <option value="FTIB" <?php echo $select_ftib ?>>FTIB</option>
                    <option value="FTEIC" <?php echo $select_fteic ?>>FTEIC</option>
                </select>
            </p>
            <p>
                <label for="department">Jurusan : </label>
                <input type="text" name="department" id="department" value="<?php echo $department ?>">
            </p>
            <p>
                <label for="gpa">IPK : </label>
                <input type="text" name="gpa" id="gpa" value="<?php echo $gpa ?>"> (angka desimal dipisah dengan karakter titik ".")
            </p>
        </fieldset>
        <br>
        <p>
            <input type="submit" name="submit" value="Simpan Perubahan">
            <a href="student_view.php">Batal</a>
        </p>
    </form>
</div>
</body>
</html>
<?php
mysqli_close($connection);
?>