<?php

namespace Psa\Migration;

class HighlightSql
{
    public function __construct(
        private string $sql
    )
    {
    }

    public function content()
    {
        $esc = chr(27);

        $c = [
            'keyword'  => "{$esc}[38;5;197m",
            'function' => "{$esc}[38;5;81m",
            'string'   => "{$esc}[38;5;228m",
            'number'   => "{$esc}[38;5;141m",
            'comment'  => "{$esc}[38;5;59m",
            'operator' => "{$esc}[38;5;197m",
            'reset'    => "{$esc}[0m",
        ];

        $placeholders = [];
        $i = 0;

        // 1. Комментарии
        $sql = preg_replace_callback('/(\/\*.*?\*\/|--[^\n]*)/is', function($m) use ($c, &$placeholders, &$i) {
            $ph = "__PLACEHOLDER{$i}__";
            $placeholders[$ph] = $c['comment'] . $m[0] . $c['reset'];
            $i++;
            return $ph;
        }, $this->sql);

        // 2. Строки
        $sql = preg_replace_callback('/([\'"`])(?:\\\\\1|(?!\1).)*\1/s', function($m) use ($c, &$placeholders, &$i) {
            $ph = "__PLACEHOLDER{$i}__";
            $placeholders[$ph] = $c['string'] . $m[0] . $c['reset'];
            $i++;
            return $ph;
        }, $sql);

        // 3. Числа
        $sql = preg_replace_callback('/\b\d+(?:\.\d+)?\b/', fn($m) => $c['number'] . $m[0] . $c['reset'], $sql);

        // 4. Функции
        $sql = preg_replace_callback('/\b([A-Z_][A-Z0-9_]*)\s*(?=\()/i', fn($m) => $c['function'] . $m[0] . $c['reset'], $sql);

        // 5. Ключевые слова
        $keywords = implode('|', array_map('preg_quote', [
            'SELECT','INSERT','UPDATE','DELETE','FROM','WHERE','JOIN','INNER','LEFT','RIGHT','ON','AND','OR','IN','LIKE','GROUP','ORDER','BY','HAVING','LIMIT','OFFSET','UNION','AS','CASE','WHEN','THEN','ELSE','END','IF','NULL','NOT','IS','TRUE','FALSE','CREATE','TABLE','ALTER','DROP','INDEX','PRIMARY','KEY','FOREIGN','REFERENCES','DISTINCT','BETWEEN','EXISTS'
        ]));
        $sql = preg_replace_callback("/\b({$keywords})\b/i", fn($m) => $c['keyword'] . $m[0] . $c['reset'], $sql);

        // 6. Операторы
        $sql = preg_replace_callback('/(=|!=|<>|<=?|>=?|\+|-|\*|\/|%|\|\||&&)/', fn($m) => $c['operator'] . $m[0] . $c['reset'], $sql);

        // 7. Вставляем обратно подсвеченные строки и комментарии
        $sql = strtr($sql, $placeholders);

        return $sql;
    }
}