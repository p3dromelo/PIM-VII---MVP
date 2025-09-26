<?php
session_start();
include("includes/db.php");

if(!isset($_GET["id"])) {
    header("Location: index.php");
    exit;
}
$video_id = (int) $_GET ["id"];

if (isset($_SESSION["user_id"])) {
    $uid = $_SESSION["user_id"];
    $conn->query("INSERT INTO historico (user_id, video_id, data_visualizacao) VALUES ($uid, $video_id, NOW())");
}

$sql = "SELECT v.id, v.titulo, v.jogo, v.arquivo, v.data_upload, u.nome FROM videos v JOIN usuarios u ON v.user_id = u.id WHERE v.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $video_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    echo "Vídeo não encontrado.";
    exit;
}
$video = $result->fetch_assoc();

$totalCurtidas = $conn->query("SELECT COUNT(*) as total FROM curtidas WHERE video_id = $video_id")->fetch_assoc()["total"];
$curtido = false;
if (isset($_SESSION["user_id"])){
    $uid = $_SESSION["user_id"];
    $checkCurtida = $conn->query("SELECT * FROM curtidas WHERE video_id = $video_id AND user_id = $uid");
    if ($checkCurtida->num_rows > 0) $curtido = true;
}

if (isset($_POST["curtir"])){
    if (isset($_SESSION["user_id"])){
        if($curtido){
            $conn->query("DELETE FROM curtidas WHERE video_id = $video_id AND user_id = $uid");
        } else {
            $conn->query("INSERT INTO curtidas (video_id, user_id) VALUES ($video_id, $uid)");
        }
        header("Location: video.php?id=$video_id");
        exit;
    } else {
        echo "<p style='color:red;'>⚠️ Faça login para curtir.</p>";
    }
}

if (isset($_POST["comentario"]) && !empty(trim($_POST["comentario"]))) {
    if (isset($_SESSION["user_id"])){
        $comentario = trim($_POST["comentario"]);
        $stmt = $conn->prepare("INSERT INTO comentarios (user_id, video_id, comentario) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $uid, $video_id, $comentario);
        $stmt->execute();
        header("Location: video.php?id=$video_id");
        exit;
    } else {
        echo "<p style='color:red;'>⚠️ Faça login para comentar.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="video-page">
    <!-- Botão Hamburguer -->
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

        <div class="menu-section">
            <h3>Seguindo:</h3>
            <p style="color: #aaa;">Em breve...</p>
        </div>
    </div>
</body>
    <!-- Conteúdo principal -->
    <main>
    <div class="video-detalhe">
    <video width="1100" controls>
        <source src="uploads/<?php echo htmlspecialchars($video["arquivo"]); ?>" type="video/mp4">
    </video>
        <form method="POST">
        <button type="submit" name="curtir">
            <?php echo $curtido ? "💔 Descurtir" : "❤️ Curtir"; ?>
        </button>
        <span><?php echo $totalCurtidas; ?> curtidas</span>
    </form>
    <br>
    <p titulo> <?php echo htmlspecialchars($video["titulo"]); ?> </p>
    <p>🎮 Jogo: <?php echo htmlspecialchars($video["jogo"]); ?></p>
    <p>👤 Autor: <?php echo htmlspecialchars($video["nome"]); ?></p>
    <p>📅 Publicado em: <?php echo date("d/m/Y H:i", strtotime($video["data_upload"])); ?></p>
<hr>
    <h2>💬 Comentários</h2>

    <?php if (isset($_SESSION["user_id"])): ?>
        <form method="POST">
            <textarea name="comentario" placeholder="Escreva um comentário..." required></textarea><br>
            <button type="submit">Enviar</button>
        </form>
    <?php else: ?>
        <p>⚠️ <a href="login.php">Faça login</a> para comentar.</p>
    <?php endif; ?>
    <br>
    <hr>
        <?php
    $comentarios = $conn->query("SELECT c.comentario, c.data_comentario, u.nome FROM comentarios c JOIN usuarios u ON c.user_id  = u.id WHERE c.video_id = $video_id ORDER BY c.data_comentario DESC");
    if ($comentarios->num_rows > 0){
        while ($row = $comentarios->fetch_assoc()) {
            echo "<div class='comentario'>";
            echo "<strong>" . htmlspecialchars($row["nome"]) . "</strong>: " . htmlspecialchars($row["comentario"]);
            echo "<br><small>📅" . date("d/m/Y H:i", strtotime($row["data_comentario"])) . "</small>";
            echo "</div><hr>";
        }
    } else {
        echo "<p>Sem comentário ainda. Seja o primeiro!</p>";
    }
    ?>
    <script>
    function toggleMenu() {
        document.getElementById("sidebar").classList.toggle("active");
    }
    </script>
    </main>

</html>