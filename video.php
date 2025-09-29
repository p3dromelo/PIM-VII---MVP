<?php
session_start();
include("includes/db.php");

if (!isset($_GET["id"])) {
    header("Location: index.php");
    exit;
}
$video_id = (int) $_GET["id"];

if (isset($_SESSION["user_id"])) {
    $uid = $_SESSION["user_id"];

    $check = $conn->prepare("SELECT id FROM historico WHERE user_id = ? AND video_id = ? AND data_visualizacao > (NOW() - INTERVAL 2 HOUR)");
    $check->bind_param("ii", $uid, $video_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $insert = $conn->prepare("INSERT INTO historico (user_id, video_id, data_visualizacao) VALUES (?, ?, NOW())");
        $insert->bind_param("ii", $uid, $video_id);
        $insert->execute();
    }
}

$sql = "SELECT v.id, v.titulo, v.jogo, v.arquivo, v.data_upload, u.nome 
        FROM videos v 
        JOIN usuarios u ON v.user_id = u.id 
        WHERE v.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $video_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Vídeo não encontrado.";
    exit;
}
$video = $result->fetch_assoc();

$totalCurtidas = $conn->query("SELECT COUNT(*) as total FROM curtidas WHERE video_id = $video_id")->fetch_assoc()["total"];
$curtido = false;
if (isset($_SESSION["user_id"])) {
    $uid = $_SESSION["user_id"];
    $checkCurtida = $conn->query("SELECT * FROM curtidas WHERE video_id = $video_id AND user_id = $uid");
    if ($checkCurtida && $checkCurtida->num_rows > 0) $curtido = true;
}

if (isset($_POST["curtir"])) {
    if (isset($_SESSION["user_id"])) {
        if ($curtido) {
            $conn->query("DELETE FROM curtidas WHERE video_id = $video_id AND user_id = $uid");
        } else {
            $conn->query("INSERT INTO curtidas (video_id, user_id) VALUES ($video_id, $uid)");
        }
        header("Location: video.php?id=$video_id");
        exit;
    } else {
        $erro_msg = "⚠️ Faça login para curtir.";
    }
}

if (isset($_POST["comentario"]) && !empty(trim($_POST["comentario"]))) {
    if (isset($_SESSION["user_id"])) {
        $comentario = trim($_POST["comentario"]);
        $stmt = $conn->prepare("INSERT INTO comentarios (user_id, video_id, comentario) VALUES (?, ?, ?)");
        // tipos: i (int) user_id, i (int) video_id, s (string) comentario
        $stmt->bind_param("iis", $uid, $video_id, $comentario);
        $stmt->execute();
        header("Location: video.php?id=$video_id");
        exit;
    } else {
        $erro_msg = "⚠️ Faça login para comentar.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($video['titulo']); ?> - MVP</title>
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

    <main class="video-main">
        <div class="video-detalhe">
            <video controls>
                <source src="uploads/<?php echo htmlspecialchars($video['arquivo']); ?>" type="video/mp4">
                Seu navegador não suporta vídeo.
            </video>

            <div class="video-meta">
                <form method="POST" style="display:inline;">
                    <br>
                    <h2><?php echo htmlspecialchars($video['titulo']); ?></h2>
                    <button type="submit" name="curtir">
                        <?php echo $curtido ? "💔 Descurtir" : "❤️ Curtir"; ?>
                    </button>
                </form>
                <span style="margin-left:8px;"><?php echo $totalCurtidas; ?> curtidas</span>
            </div>

            <?php if (!empty($erro_msg ?? '')): ?>
                <p style="color: red;"><?php echo htmlspecialchars($erro_msg); ?></p>
            <?php endif; ?>
            <p><img src="icons/Game.png" class="icon"> Jogo: <?php echo htmlspecialchars($video["jogo"]); ?></p>
            <p><img src="icons/Profile.png" class="icon"> Autor: <?php echo htmlspecialchars($video["nome"]); ?></p>
            <p><img src="icons/Calendar.png" class="icon"> Publicado em: <?php echo date("d/m/Y H:i", strtotime($video["data_upload"])); ?></p>
            <hr>
            <h3>💬 Comentários</h3>
            <?php if (isset($_SESSION["user_id"])): ?>
                <form method="POST">
                    <textarea name="comentario" placeholder="Escreva um comentário..." required></textarea><br>
                    <button type="submit">Enviar</button>
                </form>
            <?php else: ?>
                <p>⚠️ <a href="login.php">Faça login</a> para comentar.</p>
            <?php endif; ?>

            <hr>
            <?php
            $comentarios = $conn->query("SELECT c.comentario, c.data_comentario, u.nome 
                                         FROM comentarios c 
                                         JOIN usuarios u ON c.user_id = u.id 
                                         WHERE c.video_id = $video_id 
                                         ORDER BY c.data_comentario DESC");
            if ($comentarios && $comentarios->num_rows > 0) {
                while ($row = $comentarios->fetch_assoc()) {
                    echo "<div class='comentario'>";
                    echo "<strong>" . htmlspecialchars($row['nome']) . "</strong>: " . htmlspecialchars($row['comentario']);
                    echo "<br><small>📅 " . date("d/m/Y H:i", strtotime($row['data_comentario'])) . "</small>";
                    echo "</div><hr>";
                }
            } else {
                echo "<p>Sem comentários ainda. Seja o primeiro!</p>";
            }
            ?>
        </div>

        <aside class="sugestoes">
            <h3>📺 Sugestões</h3>
            <?php
            $sugestoes = $conn->query("SELECT id, titulo, arquivo, jogo FROM videos WHERE id != $video_id ORDER BY RAND() LIMIT 5");
            if ($sugestoes && $sugestoes->num_rows > 0) {
                while ($s = $sugestoes->fetch_assoc()) {
                    echo "<a class='sugestao-card' href='video.php?id=" . (int)$s['id'] . "'>";
                        echo "<video muted width='160' height='90' preload='metadata'>";
                        echo "<source src='uploads/" . htmlspecialchars($s['arquivo']) . "' type='video/mp4'>";
                        echo "</video>";
                        echo "<div class='sugestao-info'>";
                        echo "<h4>" . htmlspecialchars($s['titulo']) . "</h4>";
                        echo "<p><img src='icons/Game.png' class='icon'>" . htmlspecialchars($s['jogo']) . "</p>";
                        echo "</div>";
                    echo "</a>";
                }
            } else {
                echo "<p>Nenhum vídeo sugerido.</p>";
            }
            ?>
        </aside>
    </main>

    <script>
    function toggleMenu() {
        document.getElementById("sidebar").classList.toggle("active");
    }
    </script>
</body>
</html>
