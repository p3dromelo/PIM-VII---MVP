<?php
session_start();
include("includes/db.php");

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $stmt = $conn->prepare("SELECT id, nome, senha, tipo FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0) {
        $stmt->bind_result($id, $nome, $hash, $tipo);
        $stmt->fetch();
    
        if(password_verify($senha, $hash)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["nome"] = $nome;
            $_SESSION["tipo"] = $tipo;

            header("Location: index.php");
            exit;
        } else {
            $msg = "Senha incorreta!";
        }
    } else {
        $msg = "Usuário não encontrado!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function validarLogin(e){
            const email = document.getElementById("email").value.trim();
            const senha = document.getElementById("senha").value.trim();

            if (email === "" || senha === ""){
                e.preventDefault();
                alert("Prrencha todos os campos!");
                return false;
            }
            return true;
        }

        function toggleSenha(){
            const campo = document.getElementById("senha");
            const botao = document.getElementById("btnSenha");

            if (campo.type === "password"){
                campo.type = "text";
                botao.textContent = "👁️ Ocultar";
            } else {
                campo.type = "password";
                botao.textContent = "👁️ Mostrar";
            }
        }
    </script>
</head>
<body>
    <h1>Login</h1>
    <form method="POST">
        <input type="email" id="email" name="email" placeholder="E-mail" required><br><br>
        
        <input type="password" id="senha" name="senha" placeholder="Senha" required>
        <button type="button" id="btnSenha" onclick="toggleSenha()">👁️ Mostrar</button>
        <br><br>
        
        <button type="submit">Entrar</button>
    </form>
    <p style="color:red;"><?php echo $msg; ?></p>
    <p>Não tem conta? <a href="register.php">Cadastre-se</a></p>
</body>
</html>