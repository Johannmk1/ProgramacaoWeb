<?php
require_once __DIR__ . '/../../src/Views/Components/LoginTemplate.php';

$context = isset($_GET['context']) ? strtolower(trim($_GET['context'])) : 'default';
$loginProps = [
    'wrapper_id' => null,
    'title' => 'Entrar',
    'subtitle' => 'Informe usuário e senha para continuar.',
    'form_id' => 'loginForm',
    'user_id' => 'loginUser',
    'pass_id' => 'loginPass',
    'message_id' => 'loginMsg',
    'buttons' => [
        ['id' => 'btnLogin', 'text' => 'Entrar', 'class' => 'btn', 'type' => 'submit', 'show' => true],
    ],
];
$extraContent = '';
$extraScripts = [];

if ($context === 'admin') {
    $loginProps = [
        'wrapper_id' => 'adminLogin',
        'title' => 'Área Administrativa',
        'subtitle' => null,
        'form_id' => 'adminLoginForm',
        'user_id' => 'admUser',
        'pass_id' => 'admPass',
        'message_id' => 'admMsg',
        'buttons' => [
            ['id' => 'btnAdmVoltar', 'text' => 'Voltar', 'class' => 'btn ghost', 'type' => 'button', 'show' => true],
            ['id' => 'btnAdmLogin', 'text' => 'Entrar', 'class' => 'btn', 'type' => 'button', 'show' => true],
        ],
    ];
}
$loginContext = htmlspecialchars($context);
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#0ea5e9">
    <title><?= htmlspecialchars($loginProps['title']); ?></title>
    <link rel="stylesheet" href="../css/login.css">
  </head>
  <body class="login-page" data-login-context="<?= $loginContext; ?>" data-login-base="../../src/Controllers">
    <main class="login-wrapper">
      <?php include __DIR__ . '/loginView.php'; ?>
      <?= $extraContent; ?>
    </main>
    <script src="../js/login-page.js"></script>
    <?php foreach ($extraScripts as $script): ?>
      <script src="<?= htmlspecialchars($script); ?>"></script>
    <?php endforeach; ?>
  </body>
</html>
