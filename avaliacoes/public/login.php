<?php
require_once __DIR__ . '/../src/Views/Components/LoginTemplate.php';

$context = isset($_GET['context']) ? strtolower(trim($_GET['context'])) : 'default';
$panel = isset($_GET['panel']) ? strtolower(trim($_GET['panel'])) : null;
$showDevicePanel = ($context === 'admin' && $panel === 'device');
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
    if ($showDevicePanel) {
        $extraContent = <<<HTML
    <section id="adminPanel" class="login-card admin-panel-card" style="display:none;">
      <h3>Dispositivo</h3>
      <p class="login-subtitle">Selecione qual dispositivo este navegador representa.</p>
      <label for="admSelectDisp" class="sr-only">Dispositivo</label>
      <select id="admSelectDisp" class="input full"></select>
      <div class="login-actions">
        <button id="btnAdmSair" class="btn ghost" type="button">Sair</button>
        <button id="btnAdmSalvarDisp" class="btn" type="button">Salvar</button>
      </div>
      <p id="admPanelMsg" class="mensagem"></p>
      <p class="admin-link" style="text-align:center; margin-top:10px;">
        <a class="btn-link" href="admin/index.php" target="_blank">Abrir painel completo</a>
      </p>
    </section>
HTML;
        $extraScripts = ['js/app.js', 'js/admin.js'];
    }
}
$loginContext = htmlspecialchars($context);
$loginPanel = htmlspecialchars($panel ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">
    <meta name="theme-color" content="#0ea5e9">
    <title><?= htmlspecialchars($loginProps['title']); ?></title>
    <link rel="stylesheet" href="css/login.css">
  </head>
  <body class="login-page" data-login-context="<?= $loginContext; ?>" data-login-panel="<?= $loginPanel; ?>" data-login-base="../src/Controllers">
    <main class="login-wrapper">
      <?php include __DIR__ . '/partials/login-card.php'; ?>
      <?= $extraContent; ?>
    </main>
    <script src="js/login-page.js"></script>
    <?php foreach ($extraScripts as $script): ?>
      <script src="<?= htmlspecialchars($script); ?>"></script>
    <?php endforeach; ?>
  </body>
</html>
