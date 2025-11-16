<?php

function render_login_form(array $props = []): string {
    $formId = $props['form_id'] ?? null;
    $userId = $props['user_id'] ?? 'loginUser';
    $passId = $props['pass_id'] ?? 'loginPass';
    $messageId = $props['message_id'] ?? null;
    $buttons = $props['buttons'] ?? [
        ['id' => $props['cancel_id'] ?? null, 'text' => $props['cancel_text'] ?? 'Voltar', 'class' => 'btn ghost', 'type' => 'button', 'show' => ($props['show_cancel'] ?? false)],
        ['id' => $props['submit_id'] ?? 'btnLogin', 'text' => $props['submit_text'] ?? 'Entrar', 'class' => 'btn', 'type' => $props['submit_type'] ?? 'submit', 'show' => true],
    ];

    ob_start(); ?>
    <form class="login-form"
        <?= $formId ? ' id="' . htmlspecialchars($formId) . '"' : ''; ?>
        data-login-form="true"
        data-user-field="<?= htmlspecialchars($userId); ?>"
        data-pass-field="<?= htmlspecialchars($passId); ?>"
        data-message-field="<?= htmlspecialchars($messageId ?? ''); ?>"
        data-wrapper="<?= htmlspecialchars($props['wrapper_id'] ?? ''); ?>"
    >
        <label for="<?= htmlspecialchars($userId); ?>">Usuário</label>
        <input id="<?= htmlspecialchars($userId); ?>" class="input" type="text" autocomplete="username" placeholder="Digite seu usuário" />
        <label for="<?= htmlspecialchars($passId); ?>">Senha</label>
        <input id="<?= htmlspecialchars($passId); ?>" class="input" type="password" autocomplete="current-password" placeholder="Digite sua senha" />
        <div class="login-actions">
            <?php foreach ($buttons as $btn):
                if (empty($btn['show'])) continue;
                $id = $btn['id'] ?? null;
                $type = $btn['type'] ?? 'button';
                $class = $btn['class'] ?? 'btn';
                $text = $btn['text'] ?? 'Enviar';
            ?>
            <button<?= $id ? ' id="' . htmlspecialchars($id) . '"' : ''; ?> type="<?= htmlspecialchars($type); ?>" class="<?= htmlspecialchars($class); ?>"><?= htmlspecialchars($text); ?></button>
            <?php endforeach; ?>
        </div>
        <?php if ($messageId): ?>
            <p id="<?= htmlspecialchars($messageId); ?>" class="mensagem"></p>
        <?php endif; ?>
    </form>
    <?php
    return trim(ob_get_clean());
}

function render_login_card(array $props = []): string {
    $title = $props['title'] ?? 'Área Administrativa';
    $subtitle = $props['subtitle'] ?? null;
    $wrapperId = $props['wrapper_id'] ?? null;
    $formHtml = render_login_form($props);

    ob_start(); ?>
    <section class="login-card"<?= $wrapperId ? ' id="' . htmlspecialchars($wrapperId) . '"' : ''; ?>>
        <?php if ($title): ?><h2><?= htmlspecialchars($title); ?></h2><?php endif; ?>
        <?php if ($subtitle): ?><p class="login-subtitle"><?= htmlspecialchars($subtitle); ?></p><?php endif; ?>
        <?= $formHtml; ?>
    </section>
    <?php
    return trim(ob_get_clean());
}
