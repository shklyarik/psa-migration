<?php

namespace Psa\Migration;

/**
 * Class TableBuilder
 *
 * Responsible for building a SQL `CREATE TABLE` statement from table name, columns, and options.
 */
class TableBuilder
{
    /**
     * TableBuilder constructor.
     *
     * @param string $table_name The name of the table to create.
     * @param array $columns An associative array of column names and their corresponding ColumnBuilder instances.
     * @param string $options SQL table options (e.g., charset, collation, engine).
     */
    public function __construct(
        private string $table_name,
        private array $columns,
        private string $options
    )
    {}

    /**
     * Builds the full SQL `CREATE TABLE` statement.
     *
     * @return string The generated SQL statement.
     */
    public function build()
    {
        $columns = [];

        foreach ($this->columns as $name => $column) {
            $columns[] = '    `' . $name . '` ' . $column->build();
        }

        return "CREATE TABLE `" . $this->table_name . "`(\n"
        . implode(",\n", $columns)
        . "\n) " . $this->options . ";\n";
    }
}