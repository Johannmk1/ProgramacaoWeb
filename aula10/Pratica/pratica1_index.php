<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Pessoa</title>
</head>
<body>
    <form action="pratica1_login.php" method="POST">
        <fieldset class="externo">
            <legend>Cadastro da Pessoa:</legend>
            <ul>
                <div class="box azul">
                    <li>
                        <label for="login">Login:</label>
                        <input type="text" id="login" name="login" required maxlength="100">
                    </li>
                    <li>
                        <label for="senha">Senha:</label>
                        <input type="password" id="senha" name="senha" required maxlength="100">
                    </li>
                </div>
            </ul>
			<input type="submit" value="Enviar">
			<input type="reset" value="Limpar">
        </fieldset>
    </form>
</body>
</html>

