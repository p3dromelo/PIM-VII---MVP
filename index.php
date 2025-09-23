<?php
session_start();
include("includes/db.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MVP - Plataforma de Highlights</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>🎮 MVP</h1>

        <nav>
            <?php if (isset($_SESSION["user_id"])): ?>
                <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION["nome"]); ?></span> |
                <a href="upload.php">➕ Postar</a> |
                <a href="logout.php">Sair</a>
            <?php else: ?>
                <a href="login.php">Login</a> |
                <a href="register.php">Cadastrar</a>
            <?php endif; ?>
        </nav>
        <hr>
    </header>
    
    <main>
        <h2>📺 Últimos vídeos</h2>
        <div class="videos">
            <?php
            $sql = "SELECT v.id, v.titulo, v.jogo, v.arquivo, v.data_upload, u.nome FROM videos v JOIN usuarios u ON v.user_id = u.id ORDER BY v.data_upload DESC LIMIT 6";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()){
                echo "<div class='video-card'>";
                    echo "<a href='video.php?id=" . $row['id'] . "'>";
                    echo "<video width='320' controls> 
                            <source src='uploads/" . htmlspecialchars($row['arquivo']) . "'type='video/mp4'></video>";
                    echo "</a>";
                    echo "<h3>" . htmlspecialchars($row['titulo']) . "</h3>";
                    echo "<p>🎮 Jogo: " . htmlspecialchars($row['jogo']) . "</p>";
                    echo "<p>👤 Enviado por: " . htmlspecialchars($row['nome']) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>Nenhum vídeo disponível.</p>";
        }
        ?>
        </div>
    </main>
</body>
</html>