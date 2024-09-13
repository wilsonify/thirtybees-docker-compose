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

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class BlockViewed
 *
 * @since 1.0.0
 */
class BlockViewed extends Module
{

    /**
     * BlockViewed constructor.
     *
     * @throws PrestaShopException
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->name = 'blockviewed';
        $this->tab = 'front_office_features';
        $this->version = '2.1.0';
        $this->author = 'thirty bees';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Block Viewed Products');
        $this->description = $this->l('Adds a block displaying recently viewed products.');
        $this->tb_versions_compliancy = '> 1.0.0';
        $this->tb_min_version = '1.0.0';
    }

    /**
     * @return bool
     *
     * @throws HTMLPurifier_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 1.0.0
     */
    public function install()
    {

        if (!parent::install()
            || !$this->registerHook('header')
            || !$this->registerHook('leftColumn')
        ) {
            return false;
        }

        Configuration::updateValue('PRODUCTS_VIEWED_NBR', 2);

        return true;
    }

    /**
     * Called in administration -> module -> configure
     *
     * @return string
     * @throws HTMLPurifier_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitBlockViewed')) {
            if (!($productNbr = Tools::getValue('PRODUCTS_VIEWED_NBR')) || empty($productNbr))
                $output .= $this->displayError($this->l('You must fill in the \'Products displayed\' field.'));
            elseif ((int)($productNbr) == 0)
                $output .= $this->displayError($this->l('Invalid number.'));
            else
            {
                Configuration::updateValue('PRODUCTS_VIEWED_NBR', (int)$productNbr);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output.$this->renderForm();
    }

    /**
     * @param array $params
     *
     * @return string|null
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 1.0.0
     */
    public function hookRightColumn($params)
    {
        $context = Context::getContext();
        $cookie = $context->cookie;
        $languageId = (int)$context->language->id;
        $productsViewed = $this->getViewedProducts($cookie);

        if ($productsViewed) {
            $productIds = implode(',', $productsViewed);

            $productsData = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                        SELECT MAX(image_shop.id_image) id_image, p.id_product, il.legend, product_shop.active, pl.name, pl.description_short, pl.link_rewrite, cl.link_rewrite AS category_rewrite
                        FROM '._DB_PREFIX_.'product p
                        '.Shop::addSqlAssociation('product', 'p').'
                        LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product'.Shop::addSqlRestrictionOnLang('pl').')
                        LEFT JOIN '._DB_PREFIX_.'image i ON (i.id_product = p.id_product)'.
                        Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
                        LEFT JOIN '._DB_PREFIX_.'image_lang il ON (il.id_image = image_shop.id_image AND il.id_lang = '.$languageId.')
                        LEFT JOIN '._DB_PREFIX_.'category_lang cl ON (cl.id_category = product_shop.id_category_default'.Shop::addSqlRestrictionOnLang('cl').')
                        WHERE p.id_product IN ('.$productIds.')
                        AND pl.id_lang = '.$languageId.'
                        AND cl.id_lang = '.$languageId.'
                        AND product_shop.active
                        GROUP BY product_shop.id_product'
            );

            $productsImagesArray = [];
            foreach ($productsData as $row) {
                $productsImagesArray[(int)$row['id_product']] = $row;
            }

            $productsViewedObj = [];
            foreach ($productsViewed as $productViewed)
            {
                if (isset($productsImagesArray[$productViewed])) {
                    $row = $productsImagesArray[$productViewed];
                    $obj = new stdClass();
                    $obj->id = (int)$row['id_product'];
                    $obj->active = true;
                    $obj->id_image = (int)$row['id_image'];
                    $obj->cover = (int)$row['id_image'];
                    $obj->legend = $row['legend'];
                    $obj->name = $row['name'];
                    $obj->description_short = $row['description_short'];
                    $obj->link_rewrite = $row['link_rewrite'];
                    $obj->category_rewrite = $row['category_rewrite'];
                    $obj->product_link = $this->context->link->getProductLink($obj->id, $obj->link_rewrite, $obj->category_rewrite);
                    $productsViewedObj[] = $obj;
                }
            }

            if ($productsViewedObj) {
                $this->smarty->assign([
                    'productsViewedObj' => $productsViewedObj,
                    'mediumSize' => Image::getSize('medium'),
                ]);
                return $this->display(__FILE__, 'blockviewed.tpl');
            }
        }
        return null;
    }

    /**
     * @param array $params
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 1.0.0
     */
    public function hookLeftColumn($params)
    {
        return $this->hookRightColumn($params);
    }

    /**
     * @param array $params
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 1.0.0
     */
    public function hookFooter($params)
    {
        return $this->hookRightColumn($params);
    }

    /**
     * @param $params
     *
     * @throws PrestaShopException
     * @since 1.0.0
     */
    public function hookHeader($params)
    {
        $context = Context::getContext();
        if ($context->controller->php_self === 'product') {
            $productId = (int)Tools::getValue('id_product');
            $product = new Product((int)$productId);
            if (Validate::isLoadedObject($product) && $product->checkAccess((int)$this->context->customer->id)) {
                // add product to list of viewed products
                $cookie = $context->cookie;
                $productsViewed = $this->getViewedProducts($cookie);
                array_unshift($productsViewed, $productId);
                $this->saveViewedProducts($cookie, $productsViewed);
            }
        }

        $this->context->controller->addCSS($this->_path.'css/blockviewed.css');
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 1.0.0
     */
    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input'  => [
                    [
                        'type'  => 'text',
                        'label' => $this->l('Products to display'),
                        'name'  => 'PRODUCTS_VIEWED_NBR',
                        'desc'  => $this->l('Define the number of products displayed in this block'),
                        'class' => 'fixed-width-xs',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBlockViewed';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }

    /**
     * @return array
     *
     * @throws PrestaShopException
     * @since 1.0.0
     */
    public function getConfigFieldsValues()
    {
        return [
            'PRODUCTS_VIEWED_NBR' => (int) Tools::getValue('PRODUCTS_VIEWED_NBR', Configuration::get('PRODUCTS_VIEWED_NBR')),
        ];
    }

    /**
     * @param Cookie $cookie
     * @return int[]
     * @throws PrestaShopException
     */
    protected function getViewedProducts(Cookie $cookie)
    {
        if (isset($cookie->viewed)) {
            $limit = max(0, (int)Configuration::get('PRODUCTS_VIEWED_NBR'));
            $viewed = explode(',', $cookie->viewed);
            if ($viewed) {
                $viewed = array_unique(array_map('intval', $viewed), SORT_NUMERIC);
                if (count($viewed) > $limit) {
                    return $this->saveViewedProducts($cookie, $viewed);
                }
                return $viewed;
            }
        }
        return [];
    }

    /**
     * @param Cookie $cookie
     * @param int[] $viewed
     * @throws PrestaShopException
     * @return int[]
     */
    protected function saveViewedProducts(Cookie $cookie, array $viewed)
    {
        $viewed = array_unique(array_map('intval', $viewed), SORT_NUMERIC);
        $limit = max(0, (int)Configuration::get('PRODUCTS_VIEWED_NBR'));
        if (count($viewed) > $limit) {
            $viewed = array_slice($viewed, 0, $limit);
        }
        if ($viewed) {
            $value = implode(',', $viewed);
            if (! isset($cookie->viewed) || $cookie->viewed != $value) {
                $cookie->viewed = $value;
            }
        } else {
            if (isset($cookie->viewed)) {
                unset($cookie->viewed);
            }
        }
        return $viewed;
    }

}
