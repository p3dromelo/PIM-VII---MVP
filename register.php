<?php
include("includes/db.php");

$msg ="";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];
    $confirma = $_POST["confirma"];

    if ($senha !== $confirma){
        $msg = "As senhas não coincidem!";
    } else {
        $hash = password_hash($senha, PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0){
        $msg = "E-mail já cadastrado!";
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $email, $senha);

        if ($stmt->execute()){
            $msg = "Usuário cadastrado com sucesso! <a href='login.php'>Fazer login</a>";
        } else {
            $msg = "Erro: " . $stmt->error;
        }
        $stmt->close();
    }
    $check->close();
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

        function toggleSenha(idCampo, idBotao){
            const campo = document.getElementById(idCampo);
            const botao = document.getElementById(idBotao);

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
    <h1>Cadastro</h1>
    <form method="POST" onsubmit="return validarFormulario(event)">
        <input type="text" name="nome" placeholder="Nome" required><br><br>
        <input type="email" id="email" name="email" placeholder="E-mail" required><br><br>

        <input type="password" id="senha" name="senha" placeholder="Senha" required>
        <button type="button" id="btnSenha" onclick="toggleSenha('senha', 'btnSenha')">👁️ Mostrar</button>
        <br><br>

        <input type="password" id="confirma" name="confirma" placeholder="Confirme a senha" required>
        <button type="button" id="btnConfirma" onclick="toggleSenha('confirma', 'btnConfirma')">👁️ Mostrar</button>
        <br><br>

        <button type="submit">Cadastrar</button>
    </form>
    <p styple="color:red;"><?php echo $msg; ?></p>
    <p>Já tem uma conta? <a href="login.php">Faça Login</a></p>
</body>
</html>