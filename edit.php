<?php
session_start();
include("php/config.php");

$database = new Database();
$con = $database->con;

$edit_successful = false;  
$unsuccesful = false; 

if (!isset($_SESSION['valid'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $dob = $_POST['dob']; 

    $id = $_SESSION['id'];

    $dobDate = new DateTime($dob);
    $currentDate = new DateTime();
    $age = $currentDate->diff($dobDate)->y;

   
    if ($age < 13) {
        $unsuccesful = true;
    } else {
       
        $edit_query = mysqli_query($con, "UPDATE users SET Username='$username', Email='$email', dob='$dob' WHERE Id=$id");
        
        if ($edit_query) {
            $edit_successful = true;  
        } else {
            echo "Terjadi kesalahan saat memperbarui profil.";
        }
    }
} else {
    $id = $_SESSION['id'];
    $query = mysqli_query($con, "SELECT * FROM users WHERE Id = $id");

    if ($query) {
        $result = mysqli_fetch_assoc($query);

        $username = isset($result['Username']) ? $result['Username'] : '';
        $email = isset($result['Email']) ? $result['Email'] : '';
        $dob = isset($result['dob']) ? $result['dob'] : ''; 
    } else {
        echo "Query gagal dijalankan.";
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
    <title>Edit Profile</title>
</head>
<body>
    <div class="nav">
        <div class="logo">
            <p><a href="home.php">Home</a></p>
        </div>

        <div class="right-links">
            <a href="#">Ubah Profil</a>
            <a href="php/logout.php"><button class="btn">Log Out</button></a>
        </div>
    </div>

    <div class="container">
        <div class="box form-box">
            <header>Ubah Profil</header>
            <form action="" method="post">
                <div class="field input">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" value="<?php echo $username; ?>" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" value="<?php echo $email; ?>" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="dob">Tanggal Lahir</label>
                    <input type="date" name="dob" id="dob" value="<?php echo $dob; ?>" required>
                </div>

                <div class="field">
                    <input type="submit" class="btn" name="submit" value="Update" required>
                </div>
            </form>
        </div>

       
        <?php if ($edit_successful): ?>
        <div class="popup show" id="successPopup">
            <div class="popup-content">
                <p>Profil berhasil diupdate!!!</p>
                <button class="btn-close" onclick="document.getElementById('successPopup').style.display='none'">Tutup</button>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if ($unsuccesful): ?>
        <div class="popup show" id="errorPopup">
            <div class="popup-content">
                <p>Umur Anda harus minimal 13 tahun.</p>
                <button class="btn-close" onclick="document.getElementById('errorPopup').style.display='none'">Tutup</button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        
        function closePopup() {
            document.getElementById('errorPopup').style.display = 'none';
            document.getElementById('successPopup').style.display = 'none';
        }
    </script>
</body>
</html>
