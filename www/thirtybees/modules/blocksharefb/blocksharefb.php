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

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class blocksharefb extends Module
{

    /**
     * Constructor
     *
     * @throws PrestaShopException
     */
	public function __construct()
	{
		$this->name = 'blocksharefb';
		$this->tab = 'front_office_features';
		$this->version = '2.0.2';
		$this->author = 'thirty bees';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Block Facebook Share');
		$this->description = $this->l('Allows customers to share products or content on Facebook.');
		$this->tb_versions_compliancy = '> 1.0.0';
		$this->tb_min_version = '1.0.0';
		$this->ps_versions_compliancy = ['min' => '1.6', 'max' => '1.6.99.99'];
	}

    /**
     * Module installation method
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
	public function install()
	{
		return (parent::install() AND $this->registerHook('extraLeft'));
	}

    /**
     * Module uninstallation method
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
	public function uninstall()
	{
		//Delete configuration
		return (parent::uninstall() AND $this->unregisterHook(Hook::getIdByName('extraLeft')));
	}

    /**
     * Hook method
     *
     * @param array $params
     * @return string
     * @throws PrestaShopException
     * @throws SmartyException
     */
	public function hookExtraLeft($params)
	{
		$id_product = Tools::getValue('id_product');

		if (isset($id_product) && $id_product != '') {
			$product_infos = $this->context->controller->getProduct();
			$this->context->smarty->assign([
				'product_link' => urlencode($this->context->link->getProductLink($product_infos)),
				'product_title' => urlencode($product_infos->name),
			]);

			return $this->display(__FILE__, 'blocksharefb.tpl');
		} else {
			return '';
		}
	}
}
