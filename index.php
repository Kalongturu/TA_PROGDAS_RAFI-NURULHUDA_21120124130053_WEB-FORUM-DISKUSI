<?php
session_start();
include("php/config.php");

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $database = new Database();
    $con = $database->con;

    $stmt = $con->prepare("SELECT * FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    if ($row) {
        if (password_verify($password, $row['Password'])) {
          
            $_SESSION['valid'] = $row['Email'];
            $_SESSION['username'] = $row['Username']; 
            $_SESSION['age'] = $row['Age'];
            $_SESSION['id'] = $row['Id'];
    
            header("Location: home.php");
            exit();
        } else {
            $error = "Password atau email salah!";
        }
    } else {
        $error = "Pengguna tidak ditemukan!";
    }
}    
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Login</title>
</head>

<body>
    <div class="container">
        <div class="box form-box">
            <header>Login</header>

            <?php if (isset($error)): ?>
                <div class="message">
                    <p><?php echo $error; ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="field input">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>

                <div class="field">
                    <input type="submit" class="btn" name="submit" value="Login">
                </div>

                <div class="links">
                    Gaada akun? <a href="register.php">Sign Up Sekarang</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
