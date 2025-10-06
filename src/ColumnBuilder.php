<?php

namespace Psa\Migration;

class ColumnBuilder
{
    private $isNullable = true;
    private $unique = false;
    private $length = null;
    private $pk = false;
    private $autoIncrement = false;
    private $default = INF;
    private $precision = null;
    private $scale = null;

    public function __construct(
        private string $type // INT, VARCHAR
    )
    {
    }

    public function defaultValue($value)
    {
        $this->default = $value;
        return $this;
    }

    public function notNull()
    {
        $this->isNullable = false;
        return $this;
    }

    public function unique()
    {
        $this->unique = true;
        return $this;
    }

    public function length($length)
    {
        $this->length = $length;
        return $this;
    }

    public function precision($precision)
    {
        $this->precision = $precision;
        return $this;
    }

    public function scale($scale)
    {
        $this->scale = $scale;
        return $this;
    }

    public function pk()
    {
        $this->pk = true;
        $this->autoIncrement = true;
        return $this;
    }

    public function build(): string
    {
        $sql = $this->type;

        if ($this->length !== null) {
            $sql .= "({$this->length})";
        }

        if ($this->precision !== null || $this->scale !== null) {
            $sql .= "({$this->precision}, {$this->scale})";
        }

        $sql .= $this->isNullable ? " NULL" : " NOT NULL";

        if ($this->unique) {
            $sql .= " UNIQUE";
        }

        if ($this->pk) {
            $sql .= " PRIMARY KEY";
        }

        if ($this->autoIncrement) {
            $sql .= " AUTO_INCREMENT";
        }

        if ($this->default !== INF) {
            if (is_string($this->default)) {
                $escaped = addslashes($this->default);
                $sql .= " DEFAULT '{$escaped}'";
            } elseif (is_bool($this->default)) {
                $sql .= " DEFAULT " . ($this->default ? '1' : '0');
            } elseif (is_null($this->default)) {
                $sql .= " DEFAULT NULL";
            } else {
                $sql .= " DEFAULT {$this->default}";
            }
        }

        return $sql;
    }
}