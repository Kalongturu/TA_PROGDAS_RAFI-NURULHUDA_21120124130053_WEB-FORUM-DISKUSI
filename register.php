<?php
session_start();
include("php/config.php");

$database = new Database();
$con = $database->con;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $dob = $_POST['dob'];


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email tidak valid.";
    }


    $dob = date('Y-m-d', strtotime($dob));
    $age = date_diff(date_create($dob), date_create('today'))->y;

    if ($age < 13) {
        $error = "Umur harus minimal 13 tahun.";
    }


    if (strlen($password) < 6) {
        $error = "Password harus terdiri dari minimal 6 karakter.";
    } elseif ($password !== $confirmPassword) {
        $error = "Konfirmasi password tidak cocok.";
    }


    if (!isset($error)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $con->prepare("INSERT INTO users (username, email, password, dob) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashedPassword, $dob);


        if ($stmt->execute()) {
            $_SESSION['message'] = "Pendaftaran berhasil. Silakan login.";
            header("Location: index.php");
            exit();
        } else {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Register</title>
</head>

<body>
    <div class="container">
        <div class="box">
            <div class="form-box">
                <header>Daftar</header>

                <?php if (isset($error)): ?>
                    <div class="message"><?= $error ?></div>
                <?php endif; ?>

                <form action="" method="post">
                    <div class="field input">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" autocomplete="off" required>
                    </div>
                    <div class="field input">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" autocomplete="off" required>
                    </div>

                    <div class="field input">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="field input">
                        <label for="confirm_password">Konfirmasi Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="field input">
                        <label for="dob">Tanggal Lahir</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                    <div class="field">
                        <button type="submit" class="btn">Daftar</button>
                    </div>

                </form>
                <p>Sudah memiliki akun? <a href="index.php">Login di sini</a></p>
            </div>
        </div>
    </div>
</body>

</html>