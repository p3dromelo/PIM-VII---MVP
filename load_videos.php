<?php
session_start();
include("includes/db.php");

$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$limit = 0;

$sql = "SELECT v.id, v.titulo, v.jogo, v.arquivo, v.data_upload, u.nome FROM videos v JOIN usuarios u ON v.user_id = u.id ORDER BY v.data_upload DESC LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

if($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
        echo "<div class='video-card'>";
        echo "<a href='video.php?id=" . $row['id'] . "'>";
        echo "<video muted controls>
                <source src='uploads?" . htmlspecialchars($row['arquivo']) . "' type='video/mp4'> </video>";
        echo "</a>";
        echo "<h3>" . htmlspecialchars($row['titulo']) . "</h3>";
        echo "<p>🎮 " . htmlspecialchars($row['jogo']) . "</p>";
        echo "<p>👤 " . htmlspecialchars($row['nome']) . "</p>";
        echo "<p>📅 " . date("d/m/Y H:i", strtotime($row['data_upload'])) . "</p>";
        echo "</div>";
    }
} else {
    echo "";
}