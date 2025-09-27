<?php
session_start();
include("includes/db.php");

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];
    $confirma = $_POST["confirma"];

    if ($senha !== $confirma) {
        $msg = "As senhas não coincidem!";
    } else {

        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $msg = "Este e-mail já está cadastrado!";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $hash);

            if ($stmt->execute()) {
                $_SESSION["user_id"] = $stmt->insert_id;
                $_SESSION["nome"] = $nome;

                header("Location: index.php");
                exit;
            } else {
                $msg = "Erro ao cadastrar. Tente novamente.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function validarFormulario(e){
            const senha = document.getElementById("senha").value;
            const confirma = document.getElementById("confirma").value;

            if (senha !== confirma) {
                e.preventDefault();
                alert("As senhas não coincidem!");
                return false;
            }
            return true;
        }

        function toggleSenha(fieldId, btnId){
            const campo = document.getElementById(fieldId);
            const botao = document.getElementById(btnId);

            if (campo.type === "password"){
                campo.type = "text";
                botao.textContent = "👁️";
            } else {
                campo.type = "password";
                botao.textContent = "👁️";
            }
        }
    </script>
</head>
<body>
    <div class="auth-container">
        <h1>Cadastro</h1>
        <form method="POST" onsubmit="return validarFormulario(event)">
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="email" id="email" name="email" placeholder="E-mail" required>

            <div class="password-field">
                <input type="password" id="senha" name="senha" placeholder="Senha" required aria-label="Senha">
                <button type="button" class="pw-toggle" id="btnSenha" aria-label="Mostrar senha" onclick="toggleSenha('senha','btnSenha')">👁️</button>
            </div>

            <div class="password-field">
                <input type="password" id="confirma" name="confirma" placeholder="Confirme a senha" required aria-label="Confirme a senha">
                <button type="button" class="pw-toggle" id="btnConfirma" aria-label="Mostrar senha" onclick="toggleSenha('confirma','btnConfirma')">👁️</button>
            </div>
        <button type="submit">Cadastrar</button>
    </form>
    <p styple="color:red;"><?php echo $msg; ?></p>
    <p>Já tem uma conta? <a href="login.php">Faça Login</a></p>
    </div>
</body>
</html>