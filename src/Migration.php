<?php

namespace Psa\Migration;

use Psa\Qb\Db;

/**
 * Class Migration
 *
 * A base migration class for creating database tables and defining column types.
 */
class Migration
{
    /**
     * The database instance used for executing SQL queries.
     *
     * @var Db
     */
    private Db $db;

    /**
     * @var mixed $app The application instance, expected to have a `db` component with a `connect()` method.
     */
    public function __construct(
    ) {
    }

    /**
     * The application instance.
     * Expected to provide access to components such as the database connector.
     *
     * @var mixed
     */
    public function setDbInstance(Db $db)
    {
        $this->db = $db;
    }

    /**
     * Creates a table with the specified name, columns, and options.
     *
     * @param string $tableName The name of the table to create.
     * @param array $columns An associative array of column definitions.
     * @param string $options Optional SQL table options (e.g., character set and engine).
     *
     * @return void
     */
    public function createTable($tableName, $columns, $options = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB')
    {
        $tableBuilder = new TableBuilder($tableName, $columns, $options);
        $sql = $tableBuilder->build();
        echo new HighlightSql($sql)->content() . PHP_EOL;
        $this->db->connect()->query($sql);
    }

    /**
     * Drops a table with the specified name.
     *
     * @param string $tableName The name of the table to drop.
     *
     * @return void
     */
    public function dropTable($tableName)
    {
        $sql = 'DROP TABLE `' . $tableName . '`';
        echo new HighlightSql($sql)->content() . PHP_EOL;
        $this->db->connect()->query($sql);
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param ColumnBuilder $type
     */
    public function addColumn($tableName, $columnName, $type)
    {
        $sql = 'ALTER TABLE `' . $tableName . '` ADD COLUMN `' . $columnName . '` ' . $type->build();
        echo new HighlightSql($sql)->content() . PHP_EOL;
        $this->db->connect()->query($sql);
    }

    /**
     * Creates a boolean column definition.
     *
     * @return ColumnBuilder
     *   Returns a ColumnBuilder configured as a TINYINT(1) with default value 0.
     */
    public function boolean()
    {
        return new ColumnBuilder('TINYINT')->length(1)->defaultValue(0);
    }

    /**
     * Creates a decimal column definition.
     *
     * @param int|null $precision
     *   The total number of digits (optional).
     * @param int|null $scale
     *   The number of digits to the right of the decimal point (optional).
     *
     * @return ColumnBuilder
     *   Returns a ColumnBuilder configured as a DECIMAL column.
     */
    public function decimal($precision = null, $scale = null)
    {
        return new ColumnBuilder('DECIMAL')->precision($precision)->scale($scale);
    }

    /**
     * Drops a column from a table.
     *
     * @param string $tableName
     *   The name of the table to modify.
     * @param string $columnName
     *   The name of the column to drop.
     *
     * @return void
     *   Executes an ALTER TABLE query to drop the specified column.
     */
    public function dropColumn($tableName, $columnName)
    {
        $sql = 'ALTER TABLE `' . $tableName . '` DROP COLUMN `' . $columnName . '`';
        echo new HighlightSql($sql)->content() . PHP_EOL;
        $this->db->connect()->query($sql);
    }

    /**
     * Inserts a record into a table.
     *
     * @param string $tableName
     *   The name of the table to insert into.
     * @param array $data
     *   An associative array of column => value pairs.
     *
     * @return mixed
     *   The result of the insert operation, usually the inserted ID or a boolean.
     */
    public function insert($tableName, $data)
    {
        $sql = $this->db->from($tableName)->insertSql($data);
        echo new HighlightSql($sql)->content() . PHP_EOL;
        return $this->db->from($tableName)->insert($data);
    }

    /**
     * Deletes rows from a table.
     *
     * @param string $tableName
     *   The name of the table to delete from.
     * @param array|string|null $condition
     *   Optional WHERE condition. Can be an associative array or raw SQL string.
     *
     * @return int
     *   The number of rows deleted.
     */
    public function delete($tableName, $condition = null)
    {
        $query = $this->db->from($tableName);
        if ($condition !== null) {
            $query->where($condition);
        }

        return $query->delete();
    }

    /**
     * Defines an `INT NOT NULL PRIMARY KEY` column.
     *
     * @return ColumnBuilder
     */
    public function primaryKey()
    {
        return new ColumnBuilder('INT')->notNull()->pk();
    }

    /**
     * Defines a `VARCHAR` column with optional length.
     *
     * @param int $length Length of the VARCHAR column (default is 255).
     *
     * @return ColumnBuilder
     */
    public function string($length = 255)
    {
        return (new ColumnBuilder('VARCHAR'))->length($length);
    }

    /**
     * Defines a `FLOAT` column with optional precision and scale.
     *
     * @param int|null $precision
     * @param int|null $scale
     *
     * @return ColumnBuilder
     */
    public function float($precision = null, $scale = null)
    {
        return (new ColumnBuilder('FLOAT'))->precision($precision)->scale($scale);
    }

    /**
     * Defines a `DATETIME` column.
     *
     * @return ColumnBuilder
     */
    public function datetime()
    {
        return (new ColumnBuilder('DATETIME'));
    }

    /**
     * Defines a `DATE` column.
     *
     * @return ColumnBuilder
     */
    public function date()
    {
        return (new ColumnBuilder('DATE'));
    }

    /**
     * Defines a `BIGINT` column with optional length.
     *
     * @param int|null $length Optional length of the BIGINT column.
     *
     * @return ColumnBuilder
     */
    public function bigInteger($length = null)
    {
        return (new ColumnBuilder('BIGINT'))->length($length);
    }

    /**
     * Creates an integer column definition.
     *
     * @param int|null $length
     *   Optional length of the integer column (e.g., 11). If null, the default database length is used.
     *
     * @return ColumnBuilder
     *   Returns a ColumnBuilder configured as an INT column.
     */
    public function integer($length = null)
    {
        return (new ColumnBuilder('INT'))->length($length);
    }

    /**
     * Defines a `TEXT` column.
     *
     * @return ColumnBuilder
     */
    public function text()
    {
        return new ColumnBuilder('TEXT');
    }

    /**
     * Defines a `JSON` column.
     *
     * @return ColumnBuilder
     */
    public function json()
    {
        return new ColumnBuilder('JSON');
    }

    /**
     * Defines an ENUM column.
     *
     * @param array $values List of allowed values.
     * @return ColumnBuilder
     */
    public function enum(array $values)
    {
        $escapedValues = array_map(fn($v) => "'$v'", $values);
        $type = 'ENUM(' . implode(', ', $escapedValues) . ')';
        return new ColumnBuilder($type);
    }

    /**
     * Adds a foreign key constraint.
     *
     * @param string $name Constraint name.
     * @param string $table Table name.
     * @param string|array $columns Column(s) in the table.
     * @param string $refTable Referenced table.
     * @param string|array $refColumns Referenced column(s).
     * @param string|null $delete ON DELETE action.
     * @param string|null $update ON UPDATE action.
     */
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        if (is_array($columns)) {
            $columns = implode(', ', array_map(fn($col) => "`$col`", $columns));
        } else {
            $columns = "`$columns`";
        }

        if (is_array($refColumns)) {
            $refColumns = implode(', ', array_map(fn($col) => "`$col`", $refColumns));
        } else {
            $refColumns = "`$refColumns`";
        }

        $sql = "ALTER TABLE `{$table}`
            ADD CONSTRAINT `{$name}`
            FOREIGN KEY ({$columns})
            REFERENCES `{$refTable}` ({$refColumns})";

        if ($delete) {
            $sql .= " ON DELETE {$delete}";
        }

        if ($update) {
            $sql .= " ON UPDATE {$update}";
        }

        echo new HighlightSql($sql)->content() . PHP_EOL;
        $this->db->connect()->query($sql);
    }

    /**
     * Drops a foreign key constraint.
     *
     * @param string $name Constraint name.
     * @param string $table Table name.
     */
    public function dropForeignKey($name, $table)
    {
        $sql = "ALTER TABLE `{$table}` DROP FOREIGN KEY `{$name}`";

        echo new HighlightSql($sql)->content() . PHP_EOL;
        $this->db->connect()->query($sql);
    }

    /** Runs the migration. */
    public function up() {
        echo "NOT IMPLEMENTED!" . PHP_EOL;
    }

    /** Reverts the migration. */
    public function down() {
        echo "NOT IMPLEMENTED!" . PHP_EOL;
    }
}