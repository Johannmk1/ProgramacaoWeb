<?php

require_once __DIR__ . '/../Components/HtmlTable.php';

function render_admin_table(PDO $pdo, string $resource): string
{
    $table = new HtmlTable();
    $table->setTbodyAttributes(['id' => 'tbody']);

    switch ($resource) {
        case 'usuarios':
            $table->setHeaders(['ID', 'Usuário', 'Status', 'Criado em', 'Ações']);
            return build_table_rows($table, Usuario::listar($pdo), function ($row) {
                $statusAttr = $row['status'] ? '1' : '0';
                return [
                    html_cell((int)$row['id']),
                    html_cell(html_escape($row['username']), ['contenteditable' => 'true', 'data-field' => 'username']),
                    html_cell($row['status'] ? 'Ativo' : 'Inativo', ['data-field' => 'status', 'data-status' => $statusAttr]),
                    html_cell(format_datetime($row['created_at'] ?? null)),
                    html_cell(action_buttons([
                        ['data-act' => 'save', 'label' => 'Salvar'],
                        ['data-act' => 'toggle', 'label' => $row['status'] ? 'Desativar' : 'Ativar'],
                        ['data-act' => 'reset', 'label' => 'Resetar Senha'],
                        ['data-act' => 'del', 'label' => 'Excluir'],
                    ]), ['class' => 'row-actions']),
                ];
            });

        case 'setores':
            $table->setHeaders(['ID', 'Nome', 'Status', 'Ações']);
            return build_table_rows($table, Setor::listar($pdo), function ($row) {
                $statusAttr = $row['status'] ? '1' : '0';
                return [
                    html_cell((int)$row['id']),
                    html_cell(html_escape($row['nome']), ['contenteditable' => 'true', 'data-field' => 'nome']),
                    html_cell($row['status'] ? 'Ativo' : 'Inativo', ['data-field' => 'status', 'data-status' => $statusAttr]),
                    html_cell(action_buttons([
                        ['data-act' => 'save', 'label' => 'Salvar'],
                        ['data-act' => 'toggle', 'label' => $row['status'] ? 'Desativar' : 'Ativar'],
                        ['data-act' => 'del', 'label' => 'Excluir'],
                    ]), ['class' => 'row-actions']),
                ];
            });

        case 'dispositivos':
            $table->setHeaders(['ID', 'Nome', 'Código', 'Setor', 'Status', 'Ações']);
            $setores = Setor::listar($pdo, true);
            return build_table_rows($table, Dispositivo::listar($pdo), function ($row) use ($setores) {
                $statusAttr = $row['status'] ? '1' : '0';
                $options = '<option value="">Sem setor</option>';
                foreach ($setores as $setor) {
                    $selected = ((int)$row['id_setor'] === (int)$setor['id']) ? ' selected' : '';
                    $options .= '<option value="' . (int)$setor['id'] . '"' . $selected . '>' . html_escape($setor['nome']) . '</option>';
                }
                $select = '<select data-field="id_setor">' . $options . '</select>';
                $setorAtual = $row['setor_nome'] ? '<small class="muted">' . html_escape($row['setor_nome']) . '</small>' : '';
                return [
                    html_cell((int)$row['id']),
                    html_cell(html_escape($row['nome']), ['contenteditable' => 'true', 'data-field' => 'nome']),
                    html_cell(html_escape($row['codigo']), ['contenteditable' => 'true', 'data-field' => 'codigo']),
                    html_cell($select . $setorAtual),
                    html_cell($row['status'] ? 'Ativo' : 'Inativo', ['data-field' => 'status', 'data-status' => $statusAttr]),
                    html_cell(action_buttons([
                        ['data-act' => 'save', 'label' => 'Salvar'],
                        ['data-act' => 'toggle', 'label' => $row['status'] ? 'Desativar' : 'Ativar'],
                        ['data-act' => 'del', 'label' => 'Excluir'],
                    ]), ['class' => 'row-actions']),
                ];
            });

        case 'perguntas':
            $table->setHeaders(['ID', 'Texto', 'Tipo', 'Ordem', 'Status', 'Ações']);
            return build_table_rows($table, Pergunta::listar($pdo), function ($row) {
                $statusAttr = $row['status'] ? '1' : '0';
                $tipoOptions = [
                    ['value' => 'nps', 'label' => '0 a 10'],
                    ['value' => 'texto', 'label' => 'Resposta escrita'],
                ];
                $select = '<select data-field="tipo">';
                foreach ($tipoOptions as $opt) {
                    $selected = ($row['tipo'] ?? 'nps') === $opt['value'] ? ' selected' : '';
                    $select .= '<option value="' . $opt['value'] . '"' . $selected . '>' . $opt['label'] . '</option>';
                }
                $select .= '</select>';
                return [
                    html_cell((int)$row['id']),
                    html_cell(html_escape($row['texto']), ['contenteditable' => 'true', 'data-field' => 'texto']),
                    html_cell($select),
                    html_cell((int)($row['ordem'] ?? 0), ['contenteditable' => 'true', 'data-field' => 'ordem']),
                    html_cell($row['status'] ? 'Ativa' : 'Inativa', ['data-field' => 'status', 'data-status' => $statusAttr]),
                    html_cell(action_buttons([
                        ['data-act' => 'save', 'label' => 'Salvar'],
                        ['data-act' => 'toggle', 'label' => $row['status'] ? 'Desativar' : 'Ativar'],
                        ['data-act' => 'del', 'label' => 'Excluir'],
                    ]), ['class' => 'row-actions']),
                ];
            });
    }

    return '<p class="mensagem">Tabela não disponível.</p>';
}

function build_table_rows(HtmlTable $table, array $rows, callable $map): string
{
    if (empty($rows)) {
        $table->addRow([[
            'content' => 'Nenhum registro encontrado.',
            'attributes' => ['colspan' => max(1, $table->getColumnCount()), 'class' => 'muted'],
        ]]);
        return $table->renderHtml();
    }

    foreach ($rows as $row) {
        $table->addRow($map($row), ['data-id' => $row['id']]);
    }
    return $table->renderHtml();
}

function html_escape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function html_cell($content, array $attributes = []): array
{
    return [
        'content' => is_scalar($content) ? (string)$content : $content,
        'attributes' => $attributes,
        'tag' => 'td',
    ];
}

function action_buttons(array $buttons): string
{
    $html = '';
    foreach ($buttons as $button) {
        $label = $button['label'] ?? '';
        $attrs = '';
        foreach ($button as $key => $value) {
            if ($key === 'label') {
                continue;
            }
            $attrs .= sprintf(' %s="%s"', $key, htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'));
        }
        $html .= '<button' . $attrs . '>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</button>';
    }
    return $html;
}

function format_datetime(?string $value): string
{
    if (!$value) {
        return '';
    }
    $ts = strtotime($value);
    if ($ts === false) {
        return $value;
    }
    return date('Y-m-d H:i:s', $ts);
}
