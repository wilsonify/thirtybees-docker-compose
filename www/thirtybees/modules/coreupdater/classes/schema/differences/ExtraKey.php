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

use ObjectModel;
use Translate;
use Db;



/**
 * Class ExtraKey
 *
 * Represents extra / unknown table key
 *
 * @version 1.1.0 Initial version.
 */
class ExtraKey implements SchemaDifference
{
    /**
     * @var TableSchema table
     */
    public $table;

    /**
     * @var TableKey key
     */
    public $key;

    /**
     * ExtraKey constructor.
     *
     * @param TableSchema $table
     * @param TableKey $key
     *
     * @version 1.1.0 Initial version.
     */
    public function __construct(TableSchema $table, TableKey $key)
    {
        $this->table = $table;
        $this->key = $key;
    }

    /**
     * Return description of the difference.
     *
     * @return string
     *
     * @version 1.1.0 Initial version.
     */
    function describe()
    {
        return sprintf(
            Translate::getModuleTranslation('coreupdater', 'Extra [1]%1$s[/1] in table [2]%2$s[/2]', 'coreupdater'),
            $this->key->describeKey(),
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
        return get_class($this) . ':' . $this->table->getName() . '.' . $this->key->getName();
    }

    /**
     * This operation is NOT destructive -- dropping keys doesn't really mean loss of data
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
        switch ($this->key->getType()) {
            case ObjectModel::PRIMARY_KEY:
                return self::SEVERITY_CRITICAL;
            case ObjectModel::UNIQUE_KEY:
                return self::SEVERITY_CRITICAL;
            case ObjectModel::FOREIGN_KEY;
                return self::SEVERITY_NORMAL;
            default:
                return self::SEVERITY_NOTICE;
        }
    }

    /**
     * Applies fix to correct this database difference - drops key
     *
     * @param Db $connection
     * @return bool
     * @throws \PrestaShopException
     */
    function applyFix(Db $connection)
    {
        $stmt = 'ALTER TABLE `' . bqSQL($this->table->getName()) . '` DROP KEY `' . bqSQL($this->key->getName()) . '`';
        return $connection->execute($stmt);
    }

}

