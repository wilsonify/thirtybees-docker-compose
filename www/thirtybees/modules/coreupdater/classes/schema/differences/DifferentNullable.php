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
 * Class ExtraColumn
 *
 * Difference in column's nullable settings
 *
 * @version 1.1.0 Initial version.
 */
class DifferentNullable implements SchemaDifference
{
    /**
     * @var TableSchema
     */
    private $table;

    /**
     * @var ColumnSchema
     */
    private $column;

    /**
     * DifferentNullable constructor.
     *
     * @param TableSchema $table
     * @param ColumnSchema $column
     *
     * @version 1.1.0 Initial version.
     */
    public function __construct(TableSchema $table, ColumnSchema $column)
    {
        $this->table = $table;
        $this->column = $column;
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
        $table = $this->table->getName();
        $col = $this->column->getName();
        return $this->column->isNullable()
            ? sprintf(Translate::getModuleTranslation('coreupdater', 'Column [1]%1$s.%2$s[/1] should be marked as [2]NULL[/2]', 'coreupdater'), $table, $col)
            : sprintf(Translate::getModuleTranslation('coreupdater', 'Column [1]%1$s.%2$s[/1] should be marked as [2]NOT NULL[/2]', 'coreupdater'), $table, $col);
    }

    /**
     * Returns unique identification of this database difference.
     *
     * @return string
     */
    function getUniqueId()
    {
        return get_class($this) . ':' . $this->table->getName() . '.' . $this->column->getName();
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
        return self::SEVERITY_CRITICAL;
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
        $builder = new InformationSchemaBuilder($connection);
        $column = $builder->getCurrentColumn($this->table->getName(), $this->column->getName());
        $column->setNullable($this->column->isNullable());
        if (! $column->isNullable() && $column->hasDefaultValueNull()) {
            $column->setDefaultValue(null);
        }
        $stmt = 'ALTER TABLE `' . bqSQL($this->table->getName()) . '` MODIFY COLUMN ' .$column->getDDLStatement($this->table);
        return $connection->execute($stmt);
    }
}
