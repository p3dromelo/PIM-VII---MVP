<?php
session_start();
include("includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST["titulo"]);
    $jogo   = trim($_POST["jogo"]);
    $userId = $_SESSION["user_id"];

    if (isset($_FILES["video"]) && $_FILES["video"]["error"] == 0) {
        $ext = strtolower(pathinfo($_FILES["video"]["name"], PATHINFO_EXTENSION));
        $permitidos = ["mp4"];

        if (!in_array($ext, $permitidos)) {
            $msg = "⚠️ Apenas arquivos MP4 são permitidos.";
        } else {
            $maxSize = 200 * 1024 * 1024;
            if ($_FILES["video"]["size"] > $maxSize) {
                $msg = "⚠️ O vídeo excede o limite de 200 MB.";
            } else {
                $novoNome = uniqid() . "." . $ext;
                $destino = "uploads/" . $novoNome;

                if (move_uploaded_file($_FILES["video"]["tmp_name"], $destino)) {
                    $duracao = 0;
                    $cmd = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($destino) . " 2>&1";
                    $output = shell_exec($cmd);
                    if ($output !== null) {
                        $duracao = (float)$output;
                    }

                    if ($duracao > 30) {
                        unlink($destino);
                        $msg = "⚠️ O vídeo ultrapassa 30 segundos!";
                    } else {
                        $stmt = $conn->prepare("INSERT INTO videos (titulo, jogo, arquivo, user_id) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("sssi", $titulo, $jogo, $novoNome, $userId);

                        if ($stmt->execute()) {
                            $msg = "✅ Vídeo enviado com sucesso!";
                        } else {
                            $msg = "❌ Erro ao salvar no banco: " . $stmt->error;
                        }
                        $stmt->close();
                    }
                } else {
                    $msg = "❌ Erro ao mover o arquivo.";
                }
            }
        }
    } else {
        $msg = "⚠️ Nenhum arquivo enviado.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Upload de Vídeo</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="upload-container">
    <h1>📤 Enviar Highlight</h1>

    <form method="POST" enctype="multipart/form-data">
        <label>Título:</label>
        <input type="text" name="titulo" required>

        <label>Jogo:</label>
        <input type="text" name="jogo" required>

        <label>Arquivo (MP4, máx 200MB e 30s):</label>
        <input type="file" name="video" id="video" accept="video/mp4" required>

        <button type="submit">Enviar</button>
    </form>

    <p style="color:red;"><?php echo $msg; ?></p>
    <p><a href="index.php">⬅ Voltar para Home</a></p>
    </div>

    <script>
    document.getElementById("video").addEventListener("change", function(){
        const file = this.files[0];
        if(file){
            if(file.size >= 200 * 1024 * 1024){
                alert("⚠️ O vídeo deve ter menos de 200 MB!");
                this.value = "";
                return;
            }

            const url = URL.createObjectURL(file);
            const video = document.createElement("video");
            video.preload = "metadata";
            video.src = url;
            video.onloadedmetadata = function() {
                window.URL.revokeObjectURL(video.src);
                if (video.duration >= 30) {
                    alert("⚠️ O vídeo deve ter menos de 30 segundos!");
                    document.getElementById("video").value = "";
                }
            };
        }
    });
    </script>
</body>
</html>
