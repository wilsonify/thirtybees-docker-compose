<?php
/**
 * Copyright (C) 2023-2023 thirty bees
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
 * @copyright 2023-2023 thirty bees
 * @license   Academic Free License (AFL 3.0)
 */

if (!defined('_TB_VERSION_')) {
    return;
}

/**
 * @param BlockNewProducts $module
 *
 * @return true
 * @throws PrestaShopException
 */
function upgrade_module_2_4_0($module)
{
    $module->registerHook('actionGetBlockTopMenuLinks');
    return true;
}
