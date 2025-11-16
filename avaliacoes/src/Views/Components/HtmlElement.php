<?php

abstract class HtmlElement
{
    protected array $attributes = [];

    public function setAttribute(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function setAttributes(array $attrs): void
    {
        foreach ($attrs as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    protected function buildAttributes(array $attrs = null): string
    {
        $attrs = $attrs ?? $this->attributes;
        if (empty($attrs)) {
            return '';
        }

        $parts = [];
        foreach ($attrs as $name => $value) {
            if ($value === null || $value === false || $value === '') {
                continue;
            }
            if ($value === true) {
                $parts[] = $name;
            } else {
                $parts[] = sprintf('%s="%s"', $name, htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'));
            }
        }

        return $parts ? ' ' . implode(' ', $parts) : '';
    }

    abstract public function renderHtml(): string;
}

