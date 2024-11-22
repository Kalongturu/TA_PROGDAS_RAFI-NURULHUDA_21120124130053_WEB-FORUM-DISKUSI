<?php
session_start();
include("php/config.php");

$database = new Database();
$con = $database->con;

if (!isset($_SESSION['valid'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_reply'])) {
    $replyId = $_POST['reply_id'];

    $deleteReplyQuery = "DELETE FROM Replies WHERE Id = '$replyId' AND UserId = " . $_SESSION['id'];
    mysqli_query($con, $deleteReplyQuery);

    header("Location: home.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_thread'])) {
    $threadId = $_POST['thread_id'];

    $deleteRepliesQuery = "DELETE FROM Replies WHERE ThreadId = '$threadId'";
    mysqli_query($con, $deleteRepliesQuery);

    $deleteThreadQuery = "DELETE FROM Threads WHERE Id = '$threadId' AND UserId = " . $_SESSION['id'];
    mysqli_query($con, $deleteThreadQuery);

    header("Location: home.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_thread'])) {
    $userId = $_SESSION['id'];
    $content = mysqli_real_escape_string($con, $_POST['content']);
    $media = null;

   
    if (!empty($_FILES['media']['name'])) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4'];
        $fileExtension = strtolower(pathinfo($_FILES["media"]["name"], PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            die("File tidak diizinkan.");
        }

        $targetDir = "uploads/";
        $media = $targetDir . basename($_FILES["media"]["name"]);

        if (!move_uploaded_file($_FILES["media"]["tmp_name"], $media)) {
            die("Gagal mengunggah file.");
        }
    }

    $query = "INSERT INTO Threads (UserId, Content, Media) VALUES ('$userId', '$content', '$media')";
    mysqli_query($con, $query);

    header("Location: home.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_thread'])) {
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        die("Token CSRF tidak valid. Silakan coba lagi.");
    }

    $threadId = $_POST['thread_id'];
    $userId = $_SESSION['id'];
    $replyContent = mysqli_real_escape_string($con, $_POST['reply_content']);

    $query = "INSERT INTO Replies (ThreadId, UserId, Content) VALUES ('$threadId', '$userId', '$replyContent')";
    mysqli_query($con, $query);

    header("Location: home.php?thread_id=" . $threadId);
    exit();
}

$threads = mysqli_query($con, "SELECT Threads.*, users.username FROM Threads JOIN users ON Threads.UserId = users.Id ORDER BY Threads.CreatedAt DESC");

if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Home</title>
</head>

<body>
    <div class="nav">
        <div class="logo">
            <p><a href="home.php">Home</a></p>
        </div>
        <div class="right-links">
            <a href="edit.php">Ubah Profil</a>
            <a href="php/logout.php"><button class="btn">Log Out</button></a>
        </div>
    </div>
    <main>
        <div class="main-box">
            <div class="top">
                <div class="box">
                    <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
                </div>
            </div>
            <div class="bottom">
                <section>
                    <div class="post">
                        <section>
                            <h2>Buat Thread</h2>
                            <div class="input-thread">
                                <form action="" method="post" enctype="multipart/form-data">
                                    <textarea name="content" placeholder="Tulis threadmu..." required></textarea><br>
                                    <label for="file-upload" class="input-file-label">Pilih File</label>
                                    <input type="file" id="file-upload" name="media" class="input-file" accept="image/*,video/*">

                                  
                                    <div class="file-name">
                                        <span id="file-name-text">
                                            <?php
                                         
                                            if (isset($_FILES['media']['name']) && $_FILES['media']['name'] != '') {
                                                echo htmlspecialchars($_FILES['media']['name']);
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <button type="submit" name="post_thread" class="btn">Post Thread</button>
                                </form>
                            </div>
                        </section>
                    </div>
                    <?php while ($thread = mysqli_fetch_assoc($threads)): ?>
                        <div class="thread">
                            <h2>Threads</h2>
                            <div class="lanjut-thread">
                                <h3><?= isset($thread['username']) ? htmlspecialchars($thread['username']) : 'Unknown User' ?></h3>
                                <p><?= nl2br(htmlspecialchars($thread['Content'])) ?></p>
                                <?php if ($thread['Media']): ?>
                                    <?php if (strpos($thread['Media'], '.mp4') !== false): ?>
                                        <video controls src="<?= htmlspecialchars($thread['Media']) ?>" width="400"></video>
                                    <?php else: ?>
                                        <img src="<?= htmlspecialchars($thread['Media']) ?>" alt="Media" width="400">
                                    <?php endif; ?>
                                <?php endif; ?>
                                <p><small>Diunggah pada: <?= $thread['CreatedAt'] ?></small></p>
                                <?php if ($_SESSION['id'] == $thread['UserId']): ?>
                                    <form action="" method="post" style="display:inline;">
                                        <input type="hidden" name="thread_id" value="<?= $thread['Id'] ?>">
                                        <button type="submit" name="delete_thread" class="btn">Hapus Thread</button>
                                    </form>
                            </div>
                        <?php endif; ?>
                        <div class="lanjut-thread">
                            <form action="" method="post">
                                <textarea name="reply_content" placeholder="Balas..." required></textarea>
                                <input type="hidden" name="thread_id" value="<?= $thread['Id'] ?>">
                                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>"><br>
                                <button type="submit" name="reply_thread" class="btn">Balas</button>
                            </form>
                        </div>
                       
                        <div class="replies">
                            <h4>Balasan:</h4>
                            <?php
                            $replies = mysqli_query($con, "SELECT Replies.*, users.username FROM Replies JOIN users ON Replies.UserId = users.Id WHERE Replies.ThreadId = " . $thread['Id'] . " ORDER BY Replies.CreatedAt ASC");
                            while ($reply = mysqli_fetch_assoc($replies)):
                            ?>
                                <p><b><?= isset($reply['username']) ? htmlspecialchars($reply['username']) : 'Unknown User' ?>:</b> <?= nl2br(htmlspecialchars($reply['Content'])) ?></p>
                                <p><small>Dibalas pada: <?= $reply['CreatedAt'] ?></small></p>

                                
                                <?php if ($_SESSION['id'] == $reply['UserId']): ?>
                                    <form action="" method="post" style="display:inline;">
                                        <input type="hidden" name="reply_id" value="<?= $reply['Id'] ?>">
                                        <button type="submit" name="delete_reply" class="btn">Hapus Balasan</button>
                                    </form>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        </div>
                        </div>
                    <?php endwhile; ?>
                </section>
            </div>
        </div>
    </main>
</body>

</html>