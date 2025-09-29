<?php
session_start();
include("includes/db.php");

$q = isset($_GET['q']) ? trim($_GET['q']) : "";

$sql = "SELECT v.id, v.titulo, v.arquivo, v.jogo, v.data_upload, u.nome
        FROM videos v
        JOIN usuarios u ON v.user_id = u.id
        WHERE v.titulo LIKE ? OR v.jogo LIKE ?
        ORDER BY v.data_upload DESC";

$stmt = $conn->prepare($sql);
$searchTerm = "%$q%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
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
            <a href="historico.php">⏱ Histórico</a>
            <a href="perfil.php">📂 Seus vídeos</a>
            <a href="curtidos.php">❤️ Vídeos Curtidos</a>
        </div>
    </div>

    <main>
        <h1>🔍 Resultados para: <?php echo htmlspecialchars($q); ?></h1>
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
                    echo "<p>👤 " . htmlspecialchars($row['nome']) . "</p>";
                    echo "<p>📅 " . date('d/m/Y H:i', strtotime($row['data_upload'])) . "</p>";
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
