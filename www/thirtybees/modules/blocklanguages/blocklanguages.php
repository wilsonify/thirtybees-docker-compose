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

class BlockLanguages extends Module
{
    public function __construct()
    {
        $this->name = 'blocklanguages';
        $this->tab = 'front_office_features';
        $this->version = '2.0.2';
        $this->author = 'thirty bees';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Block Languages');
        $this->description = $this->l('Adds a block allowing customers to select a language for your store\'s content.');
        $this->tb_versions_compliancy = '> 1.0.0';
        $this->tb_min_version = '1.0.0';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {
        return (
            parent::install() &&
            $this->registerHook('displayNav') &&
            $this->registerHook('displayHeader')
        );
    }

    /**
     * Returns true, if this store has multiple languages enabled
     *
     * @throws PrestaShopException
     */
    public function hasMultipleLanguages()
    {
        $languages = Language::getLanguages(true, $this->context->shop->id);
        return count($languages) > 1;
    }

    /**
     * @param array $params
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function _prepareHook($params)
    {
        if (!$this->hasMultipleLanguages()) {
            return false;
        }

        if ((int)Configuration::get('PS_REWRITING_SETTINGS')) {
            $link = $this->context->link;
            $rewriteUrls = [];

            $controller = Dispatcher::getInstance()->getController();

            if ($controller === 'product' && ($productId = (int)Tools::getValue('id_product'))) {
                foreach (Product::getUrlRewriteInformations($productId) as $infos) {
                    $rewriteUrls[$infos['id_lang']] = $link->getProductLink(
                        $productId,
                        $infos['link_rewrite'],
                        $infos['category_rewrite'],
                        $infos['ean13'],
                        (int)$infos['id_lang']
                    );
                }
            }

            if ($controller === 'category' && ($categoryId = (int)Tools::getValue('id_category'))) {
                foreach (Category::getUrlRewriteInformations($categoryId) as $infos) {
                    $rewriteUrls[$infos['id_lang']] = $link->getCategoryLink(
                        $categoryId,
                        $infos['link_rewrite'],
                        (int)$infos['id_lang']
                    );
                }
            }

            if ($controller === 'cms' && ($cmsId = (int)Tools::getValue('id_cms'))) {
                foreach (CMS::getUrlRewriteInformations($cmsId) as $infos) {
                    $rewriteUrls[$infos['id_lang']] = $link->getCMSLink(
                        $cmsId,
                        $infos['link_rewrite'],
                        null,
                        (int)$infos['id_lang']
                    );
                }
            }

            if ($controller === 'cms' && ($cmsCategoryId = (int)Tools::getValue('id_cms_category'))) {
                foreach (CMSCategory::getUrlRewriteInformations($cmsCategoryId) as $infos) {
                    $rewriteUrls[$infos['id_lang']] = $link->getCMSCategoryLink(
                        $cmsCategoryId,
                        $infos['link_rewrite'],
                        (int)$infos['id_lang']
                    );
                }
            }

            $this->smarty->assign('lang_rewrite_urls', $rewriteUrls);
        }

        return true;
    }

    /**
     * Returns module content for header
     *
     * @param array $params Parameters
     * @return string Content
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayTop($params)
    {
        if ($this->_prepareHook($params)) {
            return $this->display(__FILE__, 'blocklanguages.tpl');
        }
        return null;
    }

    /**
     * @param array $params
     * @return string|null
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayNav($params)
    {
        return $this->hookDisplayTop($params);
    }

    /**
     * @param array $params
     * @throws PrestaShopException
     */
    public function hookDisplayHeader($params)
    {
        if ($this->hasMultipleLanguages()) {
            $this->context->controller->addCSS($this->_path . 'blocklanguages.css', 'all');
        }
    }
}
