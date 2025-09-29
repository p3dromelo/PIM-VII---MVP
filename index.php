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

<header class="main-header">

<form action="search.php" method="GET" class="search-bar" autocomplete="off">
    <input type="text" id="searchInput" name="q" placeholder="Pesquisar por título ou jogo..." required>
    
    <button type="submit" aria-label="Pesquisar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.7.7l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
        </svg>
    </button>
    
    <div id="suggestions" class="suggestions-box"></div>
</form>

<nav class="user-nav">
    <?php if (isset ($_SESSION["user_id"])): ?>
        <a href="upload.php" class="btn-postar"><img src="icons/Upload.png" class="nav-icon"> Postar</a>
        <a href="perfil.php" class="user-link">
            <img src="icons/Profile.png" class="nav-icon">
            <?php echo htmlspecialchars($_SESSION["nome"]); ?>
        </a>
        <a href="logout.php" class="logout-btn" title="Sair">
            <img src="icons/Logout.png" class="nav-icon">Sair
        </a>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Cadastrar</a>
    <?php endif; ?>
</nav>
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

const searchInput = document.getElementById("searchInput");
const suggestionsBox = document.getElementById("suggestions");

searchInput.addEventListener("input", () => {
    const query = searchInput.value.trim();
    if (query.length < 2) {
        suggestionsBox.innerHTML = "";
        return;
    }

    fetch(`autocomplete.php?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            suggestionsBox.innerHTML = "";
            data.forEach(item => {
                const suggestion = document.createElement("div");
                suggestion.classList.add("suggestion-item");
                suggestion.textContent = `${item.titulo} (${item.jogo})`;
                suggestion.onclick = () => {
                    window.location.href = `video.php?id=${item.id}`;
                };
                suggestionsBox.appendChild(suggestion);
            });
        });
});
</script>
</body>
</html>
