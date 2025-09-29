<?php
session_start();
include("includes/db.php");

if(!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$sql = "SELECT v.id, v.titulo, v.arquivo, v.jogo, h.data_visualizacao 
        FROM historico h 
        JOIN videos v ON h.video_id = v.id 
        WHERE h.user_id = $user_id 
        ORDER BY h.data_visualizacao DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="video-page">

    <span class="menu-toggle" onclick="toggleMenu()">☰</span>

    <div class="sidebar" id="sidebar">
        <div class="logo">
            <a href="index.php" class="logo-text">🎮 MVP</a>
        </div>

        <div class="menu-section">
            <h3>Você:</h3>
            <a href="historico.php">
                <img src="icons/History.png" class="icon"> Histórico
            </a>
            <a href="perfil.php">
                <img src="icons/Files.png" class="icon"> Seus Vídeos
            </a>
            <a href="curtidos.php">
                <img src="icons/Favorite.png" class="icon"> Vídeos Curtidos
            </a>
        </div>

        <div class="menu-section">
            <h3>Seguindo:</h3>
            <p style="color: #aaa;">Em breve...</p>
        </div>
    </div>

    <main>
        <h1>Histórico</h1>
        <div class="videos">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<a class='video-card' href='video.php?id=" . $row['id'] . "'>";
                        echo "<video muted>
                                <source src='uploads/" . htmlspecialchars($row['arquivo']) . "' type='video/mp4'>
                              </video>";
                        echo "<h3>" . htmlspecialchars($row['titulo']) . "</h3>";
                        echo "<p>🎮 " . htmlspecialchars($row['jogo']) . "</p>";
                        echo "<p>📅 " . date('d/m/Y H:i', strtotime($row['data_visualizacao'])) . "</p>";
                    echo "</a>";
                }
            } else {
                echo "<p>Você ainda não assistiu nenhum vídeo.</p>";
            }
            ?>
        </div>
    </main>

    <script>
    function toggleMenu() {
        document.getElementById("sidebar").classList.toggle("active");
    }
    </script>
</body>
</html>
