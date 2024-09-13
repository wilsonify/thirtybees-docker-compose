<?php
/**
 * Copyright (C) 2019 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <modules@thirtybees.com>
 * @copyright 2019 thirty bees
 * @license   Academic Free License (AFL 3.0)
 */

namespace CoreUpdater;

use PrestaShopException;
use Translate;
use Db;



/**
 * Class DifferentColumnsOrder
 *
 * This difference represents situation when two database tables have the same
 * columns, but in different order.
 *
 * @version 1.1.0 Initial version.
 */
class DifferentColumnsOrder implements SchemaDifference
{
    /**
     * @var TableSchema
     */
    private $table;

    /**
     * DifferentColumnsOrder constructor.
     *
     * @param TableSchema $table
     *
     * @version 1.1.0 Initial version.
     */
    public function __construct(TableSchema $table)
    {
        $this->table = $table;
    }

    /**
     * Return description of the difference.
     *
     * @return string
     *
     * @version 1.1.0 Initial version.
     */
    public function describe()
    {
        return sprintf(
            Translate::getModuleTranslation('coreupdater', 'Columns in table [1]%1$s[/1] are in wrong order', 'coreupdater'),
            $this->table->getName()
        );
    }

    /**
     * Returns unique identification of this database difference.
     *
     * @return string
     */
    function getUniqueId()
    {
        return get_class($this) . ':' . $this->table->getName();
    }

    /**
     * This operation is NOT destructive
     *
     * @return bool
     */
    function isDestructive()
    {
        return false;
    }

    /**
     * Returns severity of this difference
     *
     * @return int severity
     */
    function getSeverity()
    {
        return self::SEVERITY_NORMAL;
    }

    /**
     * Applies fix to correct this database difference
     *
     * @param Db $connection
     * @return bool
     * @throws PrestaShopException
     */
    function applyFix(Db $connection)
    {
        $columns = [];
        $builder = new InformationSchemaBuilder($connection);
        $prev = null;
        foreach ($this->table->getColumnNames() as $columnName) {
            $column = $builder->getCurrentColumn($this->table->getName(), $columnName);
            $columns[] = "  MODIFY COLUMN " . $column->getDDLStatement($this->table) . ($prev ? " AFTER `$prev`" : " FIRST");
            $prev = pSQL($columnName);
        }
        $stmt = 'ALTER TABLE `' . bqSQL($this->table->getName()) . "`\n" . implode(",\n", $columns);
        return $connection->execute($stmt);
    }
}
