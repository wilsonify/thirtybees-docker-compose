<?php
/**
 * Copyright (C) 2017-2019 thirty bees
 * Copyright (C) 2007-2016 PrestaShop SA
 *
 * thirty bees is an extension to the PrestaShop software by PrestaShop SA.
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017-2019 thirty bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   Academic Free License (AFL 3.0)
 * PrestaShop is an internationally registered trademark of PrestaShop SA.
 */

class HomeSlide extends ObjectModel
{
    /**
     * @var string|string[]
     */
    public $title;

    /**
     * @var string|string[]
     */
    public $description;

    /**
     * @var string|string[]
     */
    public $url;

    /**
     * @var string|string[]
     */
    public $legend;

    /**
     * @var string|string[]
     */
    public $image;

    /**
     * @var bool
     */
    public $active;

    /**
     * @var int
     */
    public $position;

    /**
     * @var int|null
     */
    public $id_shop;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'homeslider_slides',
        'primary' => 'id_homeslider_slides',
        'multilang' => true,
        'fields' => array(
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),

            // Lang fields
            'description' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4000),
            'title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
            'legend' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
            'url' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isUrl', 'required' => true, 'size' => 255),
            'image' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 255),
        )
    );

    /**
     * @param $id_slide
     * @param $id_lang
     * @param $id_shop
     * @param Context|null $context
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct($id_slide = null, $id_lang = null, $id_shop = null, Context $context = null)
    {
        parent::__construct($id_slide, $id_lang, $id_shop);
    }

    /**
     * @param $autodate
     * @param $null_values
     *
     * @return int|string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function add($autodate = true, $null_values = false)
    {
        $context = Context::getContext();
        $id_shop = $context->shop->id;

        $res = parent::add($autodate, $null_values);
        $res &= Db::getInstance()->execute('
			INSERT INTO `' . _DB_PREFIX_ . 'homeslider` (`id_shop`, `id_homeslider_slides`)
			VALUES(' . (int)$id_shop . ', ' . (int)$this->id . ')'
        );
        return $res;
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function delete()
    {
        $res = true;

        // delete image files
        foreach ($this->image as $image) {
            $filepath = HomeSlider::getImageDir() . $image;
            if ($image && file_exists($filepath)) {
                $res = unlink($filepath) && $res;
            }
        }

        // reorder positions
        $res = $this->reOrderPositions() && $res;

        // delete slides
        $res = Db::getInstance()->execute('
			DELETE FROM `' . _DB_PREFIX_ . 'homeslider`
			WHERE `id_homeslider_slides` = ' . (int)$this->id
        ) && $res;

        // delete record
        return parent::delete() && $res;
    }

    /**
     * @return true
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function reOrderPositions()
    {
        $id_slide = $this->id;
        $context = Context::getContext();
        $id_shop = $context->shop->id;

        $max = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT MAX(hss.`position`) as position
			FROM `' . _DB_PREFIX_ . 'homeslider_slides` hss, `' . _DB_PREFIX_ . 'homeslider` hs
			WHERE hss.`id_homeslider_slides` = hs.`id_homeslider_slides` AND hs.`id_shop` = ' . (int)$id_shop
        );

        if ((int)$max == (int)$id_slide) {
            return true;
        }

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`position` as position, hss.`id_homeslider_slides` as id_slide
			FROM `' . _DB_PREFIX_ . 'homeslider_slides` hss
			LEFT JOIN `' . _DB_PREFIX_ . 'homeslider` hs ON (hss.`id_homeslider_slides` = hs.`id_homeslider_slides`)
			WHERE hs.`id_shop` = ' . (int)$id_shop . ' AND hss.`position` > ' . (int)$this->position
        );

        foreach ($rows as $row) {
            $current_slide = new HomeSlide($row['id_slide']);
            --$current_slide->position;
            $current_slide->update();
            unset($current_slide);
        }

        return true;
    }

    /**
     * @param $id_slide
     *
     * @return array|false
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getAssociatedIdsShop($id_slide)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hs.`id_shop`
			FROM `' . _DB_PREFIX_ . 'homeslider` hs
			WHERE hs.`id_homeslider_slides` = ' . (int)$id_slide
        );

        if (!is_array($result)) {
            return false;
        }

        $return = array();

        foreach ($result as $id_shop) {
            $return[] = (int)$id_shop['id_shop'];
        }

        return $return;
    }

}
