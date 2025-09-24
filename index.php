<?php
session_start();
include("includes/db.php");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MVP - Plataforma de Highlights</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <span class="menu-toggle" onclick="toggleMenu()">☰</span>

    <div class="sidebar" id="sidebar">
        <div class="logo">
            <span class="logo-text">🎮 MVP</span>
    </div>

    <div class="menu-section">
        <h3>Você:</h3>
        <a href="historico.php">⏱ Histórico</a>
        <a href="perfil.php">📂 Seus vídeos</a>
        <a href="curtidos.php">❤️ Vídeos Curtidos</a>
    </div>

    <div class="menu-section">
        <h3>Seguindo:</h3>
    </div>
</div>

<header>
    <h1>🎮 MVP - Plataforma de Highlights</h1>
    <nav>
        <?php if (isset($_SESSION["user_id"])): ?>
            <span>Bem-vindo, <?php echo htmlspecialchars($_SESSION["nome"]); ?>!</span> |
            <a href="upload.php">➕ Postar</a> |
            <a href="logout.php">Sair</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Cadastrar</a>
        <?php endif; ?>
    </nav>
    <hr>
</header>

<main>
    <h2>📺 Últimos vídeos:</h2>
    <div class="videos" id="video-container">
        <?php
        $sql = "SELECT v.id, v.titulo, v.jogo, v.arquivo, v.data_upload, u.nome 
                    FROM videos v
                    JOIN usuarios u ON v.user_id = u.id
                    ORDER BY v.data_upload DESC LIMIT 6";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0){
            while ($row = $result->fetch_assoc()) {
                echo "<div class='video-card'>";
                    echo "<a href='video.php?id=" . $row['id'] . "'>";
                    echo "<video muted controls>
                        <source src='uploads/" . htmlspecialchars($row['arquivo']) . "'type='video/mp4'> </video>";
                    echo "<h3>" . htmlspecialchars($row['titulo']) . "</h3>";
                    echo "<p>🎮 " . htmlspecialchars($row['jogo']) . "</p>";
                    echo "<p>👤 " . htmlspecialchars($row['nome']) . "</p>";
                    echo "<p>📅 " . date("d/m/Y H:i", strtotime($row['data_upload'])) . "</p>";
                echo "</div>";
                }
            } else {
                echo "<p>Nenhum vídeo disponível.</p>";
            }
            ?>
    </div>
</main>

<script>
function toggleMenu(){
    document.getElementById("sidebar").classList.toggle("active");
}

let offset = 6;
let carregando = false;

window.addEventListener("scroll", () => {
    if (window.innerWidth <= 768){
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
            if (!carregando) {
                carregando = true;
                fetch('load_videos.php?offset=${offset}')
                .then(response => response.text())
                .then(data => {
                    if (data.trim() !== "") {
                        document.getElementById("video-container").insertAdjacentHTML("beforeend", data);
                        offset += 6;
                        carregando = false;
                    }
                });
            }
        }
    }
});
</script>
</body>
</html>
