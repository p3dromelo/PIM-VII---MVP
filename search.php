<?php
session_start();
include("includes/db.php");
$q = trim($_GET["q"] ?? "");
$resultados = [];

if ($q !== "") {
    $sql = "SELECT id, titulo, jogo, arquivo FROM videos 
            WHERE titulo LIKE ? OR jogo LIKE ? 
            ORDER BY data_upload DESC";
    $stmt = $conn->prepare($sql);
    $like = "%$q%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $resultados = $res->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados da busca</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
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
    </div>

    <main>
    <h1>🔎 Resultados para "<?php echo htmlspecialchars($q); ?>"</h1>

    <div class="videos <?php echo (count($resultados) === 1) ? 'single-result' : ''; ?>">
        <?php
        if (count($resultados) > 0) {
            foreach ($resultados as $row) {
                echo "<a class='video-card' href='video.php?id=" . $row['id'] . "'>";
                echo "<video muted>
                        <source src='uploads/" . htmlspecialchars($row['arquivo']) . "' type='video/mp4'>
                      </video>";
                echo "<h3>" . htmlspecialchars($row['titulo']) . "</h3>";
                echo "<p>🎮 " . htmlspecialchars($row['jogo']) . "</p>";
                echo "</a>";
            }
        } else {
            echo "<p>Nenhum vídeo encontrado.</p>";
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
