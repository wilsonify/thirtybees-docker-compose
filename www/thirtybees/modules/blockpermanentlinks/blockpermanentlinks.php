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

if (!defined('_TB_VERSION_'))
	exit;

class BlockPermanentLinks extends Module
{
	public function __construct()
	{
		$this->name = 'blockpermanentlinks';
		$this->tab = 'front_office_features';
		$this->version = '1.0.3';
		$this->author = 'thirty bees';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Block Permanent Links');
		$this->description = $this->l('Adds a block which  displays permanent links such as sitemap, contact, etc.');
		$this->tb_versions_compliancy = '> 1.0.0';
		$this->tb_min_version = '1.0.0';
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
	}

	public function install()
	{
			return (parent::install() && $this->registerHook('top') && $this->registerHook('header'));
	}

	/**
	* Returns module content for header
	*
	* @param array $params Parameters
	* @return string Content
	*/
	public function hookTop($params)
	{
		return $this->display(__FILE__, 'blockpermanentlinks-header.tpl', $this->getCacheId('blockpermanentlinks-header'));
	}

	public function hookDisplayNav($params)
	{
		return $this->hookTop($params);
	}

	/**
	* Returns module content for left column
	*
	* @param array $params Parameters
	* @return string Content
	*/
	public function hookLeftColumn($params)
	{
		return $this->display(__FILE__, 'blockpermanentlinks.tpl', $this->getCacheId());
	}

	public function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}

	public function hookFooter($params)
	{
		return $this->display(__FILE__, 'blockpermanentlinks-footer.tpl', $this->getCacheId('blockpermanentlinks-footer'));
	}

	public function hookHeader($params)
	{
		$this->context->controller->addCSS(($this->_path).'blockpermanentlinks.css', 'all');
	}
}
