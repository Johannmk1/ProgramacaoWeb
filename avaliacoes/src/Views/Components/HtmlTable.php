<?php

require_once __DIR__ . '/HtmlElement.php';

class HtmlTable extends HtmlElement
{
    private array $headers = [];
    private array $rows = [];
    private array $tbodyAttributes = [];

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function setTbodyAttributes(array $attrs): void
    {
        $this->tbodyAttributes = $attrs;
    }

    public function addRow(array $cells, array $rowAttributes = []): void
    {
        $this->rows[] = [
            'cells' => array_map(function ($cell) {
                if (is_array($cell)) {
                    return [
                        'content' => $cell['content'] ?? '',
                        'attributes' => $cell['attributes'] ?? [],
                        'tag' => $cell['tag'] ?? 'td',
                    ];
                }
                return ['content' => $cell, 'attributes' => [], 'tag' => 'td'];
            }, $cells),
            'attributes' => $rowAttributes,
        ];
    }

    public function getColumnCount(): int
    {
        return count($this->headers);
    }

    public function renderHtml(): string
    {
        $html = '<table' . $this->buildAttributes() . '>';
        if (!empty($this->headers)) {
            $html .= '<thead><tr>';
            foreach ($this->headers as $header) {
                if (is_array($header)) {
                    $text = $header['text'] ?? '';
                    $attrs = $header['attributes'] ?? [];
                } else {
                    $text = $header;
                    $attrs = [];
                }
                $html .= '<th' . $this->buildAttributes($attrs) . '>' . $text . '</th>';
            }
            $html .= '</tr></thead>';
        }
        $html .= '<tbody' . $this->buildAttributes($this->tbodyAttributes) . '>';
        foreach ($this->rows as $row) {
            $html .= '<tr' . $this->buildAttributes($row['attributes']) . '>';
            foreach ($row['cells'] as $cell) {
                $html .= '<' . $cell['tag'] . $this->buildAttributes($cell['attributes']) . '>' . $cell['content'] . '</' . $cell['tag'] . '>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }
}
