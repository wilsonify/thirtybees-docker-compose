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

use Db;

/**
 * Class DatabaseCharset
 *
 * This class represents character set and collate settings
 *
 * @version 1.1.0 Initial version.
 */
class DatabaseCharset
{
    /**
     * @var string charset
     */
    protected $charset = null;

    /**
     * @var string collation
     */
    protected $collate = null;

    /**
     * DatabaseCharset constructor.
     *
     * @param string $charset
     * @param string $collate
     *
     * @version 1.1.0 Initial version.
     */
    public function __construct($charset = null, $collate = null)
    {
        $this->charset = $charset;
        $this->collate = $collate;
    }

    /**
     * @return string
     *
     * @version 1.1.0 Initial version.
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     *
     * @version 1.1.0 Initial version.
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     *
     * @version 1.1.0 Initial version.
     */
    public function getCollate()
    {
        return $this->collate;
    }

    /**
     * @param string $collate
     *
     * @version 1.1.0 Initial version.
     */
    public function setCollate($collate)
    {
        $this->collate = $collate;
    }

    /**
     * Returns true if both settings are equal
     *
     * @param DatabaseCharset $other
     *
     * @return bool
     *
     * @version 1.1.0 Initial version.
     */
    public function equals(DatabaseCharset $other)
    {
        return (
            $this->getCharset() === $other->getCharset()  &&
            $this->getCollate() === $other->getCollate()
        );
    }

    /**
     * Describes character set and collation
     *
     * @return string
     *
     * @version 1.1.0 Initial version.
     */
    public function describe()
    {
        $charset = $this->getCharset();
        $collate = $this->getCollate();
        if ($charset && $collate) {
            return "$charset/$collate";
        }

        return 'NONE';
    }

    /**
     * Loads available character sets from database information schema
     *
     * @param Db $conn database connection
     *
     * @version 1.1.0 Initial version.
     */
    public static function loadCharsets(Db $conn)
    {
        try {
            $results = $conn->executeS('SELECT * FROM information_schema.CHARACTER_SETS');
            $charsets = [];
            foreach ($results as $row) {
                $charsets[$row['CHARACTER_SET_NAME']] = $row['DEFAULT_COLLATE_NAME'];
            }
            return $charsets;
        } catch (\Exception $e) {
            return [];
        }
    }
}
