<?php
/**
 * 2007-2016 PrestaShop
 *
 * thirty bees is an extension to the PrestaShop e-commerce software developed by PrestaShop SA
 * Copyright (C) 2017-2018 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <modules@thirtybees.com>
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2017-2018 thirty bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  PrestaShop is an internationally registered trademark & property of PrestaShop SA
 */

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Copy all image files referenced in the database to the /img/homeslider/ directory
 *
 * @return bool
 *
 * @throws PrestaShopDatabaseException
 * @throws PrestaShopException
 */
function upgrade_module_2_1_0()
{
    $sourceDir = __DIR__  . '/../images/';
    $targetDir = HomeSlider::getImageDir();
    if (file_exists($sourceDir)) {

        // find images from the database
        $images = array_column(Db::getInstance()->executeS((new DbQuery())
            ->select('DISTINCT(image) as image')
            ->from('homeslider_slides_lang')
        ), 'image');

        // copy them to destination
        foreach ($images as $image) {
            if (file_exists($sourceDir . $image)) {
                if (! file_exists($targetDir . $image)) {
                    copy($sourceDir . $image, $targetDir . $image);
                }
            }
        }
    }
    return true;
}
