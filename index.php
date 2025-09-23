<?php 
session_start();
include("includes/db.php"); 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MVP</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>🎮 MVP</h1>

    <?php if (isset($_SESSION["user_id"])): ?>
        <p>Bem-vindo, <?php echo htmlspecialchars($_SESSION["nome"]); ?>!</p>
        <a href="upload.php">Enviar vídeo</a> | 
        <a href="logout.php">Sair</a>
    <?php else: ?>
        <a href="login.php">Login</a> | 
        <a href="register.php">Cadastrar</a>
    <?php endif; ?>
    
    <hr>

    <h2>Últimos vídeos</h2>
    <div class="videos">
        <?php
        $sql ="SELECT v.titulo, v.arquivo, u.nome
        FROM videos v
        JOIN usuarios u ON v.user_id = u.id
        ORDER BY v.data_upload DESC LIMIT 5";
    $result = $conn->query($sql);

    if ($result->num_rows > 0){
        while($row = $result->fetch_assoc()) {
            echo "<div class='video-card'>";
            echo "<h3>".htmlspecialchars($row['titulo'])."</h3>";
            echo "<video width='320' controls>
                    <source src='uploads/".$row['arquivo']."' type='video/mp4'>
                      </video>";
            echo "<p>Enviado por: ".htmlspecialchars($row['nome'])."</p>";
            echo "</div>";
        }
    } else {
        echo "<p>Nenhum vídeo disponível.</p>";
    }
    ?>
    </div>
</body>
</html>