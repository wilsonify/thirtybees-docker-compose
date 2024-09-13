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
/** @noinspection PhpUnhandledExceptionInspection */

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/blocklayered.php');

if (substr(Tools::encrypt('blocklayered/index'), 0, 10) != Tools::getValue('token') || !Module::isInstalled('blocklayered')) {
    die('Bad token');
}

if (!Tools::getValue('ajax')) {
    // Case of nothing to do but showing a message (1)
    if (Tools::getValue('return_message') !== false) {
        echo '1';
        die();
    }

    if (Tools::usingSecureMode()) {
        $domain = Tools::getShopDomainSsl(true);
    } else {
        $domain = Tools::getShopDomain(true);
    }
    // Return a content without waiting the end of index execution
    header('Location: ' . $domain . __PS_BASE_URI__ . 'modules/blocklayered/blocklayered-price-indexer.php?token=' . Tools::getValue('token') . '&return_message=' . (int)Tools::getValue('cursor'));
    flush();
}

if (Tools::getValue('full')) {
    echo BlockLayered::fullPricesIndexProcess((int)Tools::getValue('cursor'), (int)Tools::getValue('ajax'), true);
} else {
    echo BlockLayered::pricesIndexProcess((int)Tools::getValue('cursor'), (int)Tools::getValue('ajax'));
}
