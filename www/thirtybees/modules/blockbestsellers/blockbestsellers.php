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
 * Class BlockBestSellers
 */
class BlockBestSellers extends Module
{
    const CACHE_TTL = 'PS_BLOCK_BESTSELLERS_TTL';
    const CACHE_TIMESTAMP = 'PS_BLOCK_BESTSELLERS_TIMESTAMP';
    const BESTSELLERS_DISPLAY = 'PS_BLOCK_BESTSELLERS_DISPLAY';
    const BESTSELLERS_TO_DISPLAY = 'PS_BLOCK_BESTSELLERS_TO_DISPLAY';
    const BESTSELLERS_PRICE_ABOVE = 'PS_BLOCK_BESTSELLERS_PRICE_ABOVE';

    /**
     * BlockBestSellers constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'blockbestsellers';
        $this->tab = 'front_office_features';
        $this->version = '2.2.5';
        $this->author = 'thirty bees';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Block Best Sellers');
        $this->description = $this->l('Adds a block displaying your store\'s top-selling products.');
        $this->tb_versions_compliancy = '> 1.0.0';
        $this->tb_min_version = '1.0.0';

        if (Configuration::get(static::CACHE_TIMESTAMP) < (time() - Configuration::get(static::CACHE_TTL))) {
            $this->clearCache();
        }
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {
        $this->clearCache();

        if (!parent::install()
            || !$this->registerHook('header')
            || !$this->registerHook('leftColumn')
            || !$this->registerHook('actionOrderStatusPostUpdate')
            || !$this->registerHook('addproduct')
            || !$this->registerHook('updateproduct')
            || !$this->registerHook('deleteproduct')
            || !$this->registerHook('displayHome')
            || !$this->registerHook('displayHomeTab')
            || !$this->registerHook('displayHomeTabContent')
            || !ProductSale::fillProductSales()
        ) {
            return false;
        }

        Configuration::updateValue(static::BESTSELLERS_TO_DISPLAY, 4);
        Configuration::updateValue(static::BESTSELLERS_PRICE_ABOVE, 0);

        return true;
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function clearCache()
    {
        try {
            $caches = [
                'blockbestsellers.tpl'      => 'blockbestsellers-col',
                'blockbestsellers-home.tpl' => 'blockbestsellers-home',
                'tab.tpl'                   => 'blockbestsellers-tab',
            ];

            foreach ($caches as $template => $cacheId) {
                Tools::clearCache(Context::getContext()->smarty, $this->getTemplatePath($template), $cacheId);
            }

            Configuration::updateValue(static::CACHE_TIMESTAMP, time());
        } catch (Exception $e) {
            Logger::addLog("Block best sellers module: {$e->getMessage()}");
        }
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        $this->clearCache();

        return parent::uninstall();
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookAddProduct()
    {
        $this->clearCache();
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookUpdateProduct()
    {
        $this->clearCache();
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDeleteProduct()
    {
        $this->clearCache();
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionOrderStatusPostUpdate()
    {
        $this->clearCache();
    }

    /**
     * Called in administration -> module -> configure
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitBestSellers')) {
            Configuration::updateValue(
                static::BESTSELLERS_DISPLAY,
                (int) Tools::getValue(static::BESTSELLERS_DISPLAY)
            );
            Configuration::updateValue(
                static::BESTSELLERS_TO_DISPLAY,
                (int) Tools::getValue(static::BESTSELLERS_TO_DISPLAY)
            );
            Configuration::updateValue(
                static::BESTSELLERS_PRICE_ABOVE,
                (double) Tools::getValue(static::BESTSELLERS_PRICE_ABOVE)
            );
            Configuration::updateValue(
                static::CACHE_TTL,
                (int) Tools::getValue(static::CACHE_TTL) * 60
            );
            $this->clearCache();
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output.$this->renderForm();
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function renderForm()
    {
        $formFields = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type'  => 'text',
                        'label' => $this->l('Products to display'),
                        'name'  => static::BESTSELLERS_TO_DISPLAY,
                        'desc'  => $this->l('Determine the number of product to display in this block'),
                        'class' => 'fixed-width-xs',
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Products with price above'),
                        'name'  => static::BESTSELLERS_PRICE_ABOVE,
                        'desc'  => $this->l('Display only products with price higher than defined in this block, 0 for displaying all products'),
                        'class' => 'fixed-width-xs',
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Always display this block'),
                        'name'    => static::BESTSELLERS_DISPLAY,
                        'desc'    => $this->l('Show the block even if no best sellers are available.'),
                        'is_bool' => true,
                        'values'  => [
                            [
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type'   => 'text',
                        'label'  => $this->l('Cache lifetime'),
                        'name'   => static::CACHE_TTL,
                        'desc'   => $this->l('Determines for how long the bestseller block stays cached'),
                        'suffix' => $this->l('Minutes'),
                        'class'  => 'fixed-width-xs',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        /** @var AdminController $controller */
        $controller =  $this->context->controller;

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')
            ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')
            : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBestSellers';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$formFields]);
    }

    /**
     * @return array
     *
     * @throws PrestaShopException
     */
    public function getConfigFieldsValues()
    {
        return [
            static::BESTSELLERS_TO_DISPLAY => (int) Tools::getValue(
                static::BESTSELLERS_TO_DISPLAY,
                Configuration::get(static::BESTSELLERS_TO_DISPLAY)
            ),
            static::BESTSELLERS_PRICE_ABOVE => (double) Tools::getValue(
                static::BESTSELLERS_PRICE_ABOVE,
                Configuration::get(static::BESTSELLERS_PRICE_ABOVE)
            ),
            static::BESTSELLERS_DISPLAY    => (int) Tools::getValue(
                static::BESTSELLERS_DISPLAY,
                Configuration::get(static::BESTSELLERS_DISPLAY)
            ),
            static::CACHE_TTL              => (int) Tools::getValue(
                    static::CACHE_TTL,
                    Configuration::get(static::CACHE_TTL) / 60
                ),
        ];
    }

    /**
     * @throws PrestaShopException
     */
    public function hookHeader()
    {
        if (Configuration::get('PS_CATALOG_MODE')) {
            return;
        }
        if (isset($this->context->controller->php_self) && $this->context->controller->php_self == 'index') {
            $this->context->controller->addCSS(_THEME_CSS_DIR_.'product_list.css');
        }
        $this->context->controller->addCSS($this->_path.'blockbestsellers.css', 'all');
    }

    /**
     * @return bool|string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayHomeTab()
    {
        if (!$this->isCached('tab.tpl', $this->getCacheId('blockbestsellers-tab'))) {
            $bestSellers = $this->getBestSellers();
            if ($bestSellers === false) {
                return false;
            }
            $this->smarty->assign('best_sellers', $bestSellers);
        }

        return $this->display(__FILE__, 'tab.tpl', $this->getCacheId('blockbestsellers-tab'));
    }

    /**
     * Returns bestsellers products, using cache
     *
     * @return array|bool
     *
     * @throws PrestaShopException
     */
    protected function getBestSellers()
    {
        static $cache = null;
        if (is_null($cache)) {
            $cache = $this->resolveBestSellers();
        }
        return $cache;
    }

    /**
     * Returns bestsellers products without using cache
     *
     * @return array|false
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function resolveBestSellers()
    {
        if (Configuration::get('PS_CATALOG_MODE')) {
            return false;
        }

        if (!($result = $this->getBestSalesLight(
            (int) $this->context->language->id,
            0,
            (int) Configuration::get(static::BESTSELLERS_TO_DISPLAY),
            Configuration::get(static::BESTSELLERS_PRICE_ABOVE)))
        ) {
            return (Configuration::get(static::BESTSELLERS_DISPLAY) ? [] : false);
        }

        $currency = new Currency($this->context->currency->id);
        $usetax = (Product::getTaxCalculationMethod((int) $this->context->customer->id) != PS_TAX_EXC);
        foreach ($result as &$row) {
            $row['price_raw'] = round(Product::getPriceStatic((int) $row['id_product'], $usetax), _TB_PRICE_DATABASE_PRECISION_);
            $row['price'] = Tools::displayPrice($row['price_raw'], $currency);
        }

        return $result;
    }

    /**
     * Get required informations on best sales products
     *
     * @param int $idLang Language id
     * @param int $pageNumber Start from (optional)
     * @param int $nbProducts Number of products to return (optional)
     * @param int $priceAbove
     *
     * @return array|false keys : id_product, link_rewrite, name, id_image, legend, sales, ean13, upc, link
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function getBestSalesLight($idLang, $pageNumber = 0, $nbProducts = 10, $priceAbove = 0)
    {
        if ($pageNumber < 0) {
            $pageNumber = 0;
        }
        if ($nbProducts < 1) {
            $nbProducts = 10;
        }
        // no group by needed : there's only one attribute with default_on=1 for a given id_product + shop
        // same for image with cover=1
        $sql = '
        SELECT
            p.id_product, IFNULL(product_attribute_shop.id_product_attribute,0) id_product_attribute, pl.`link_rewrite`, pl.`name`, pl.`description_short`, product_shop.`id_category_default`,
            image_shop.`id_image` id_image, il.`legend`,
            ps.`quantity` AS sales, p.`ean13`, p.`upc`, p.`online_only`, cl.`link_rewrite` AS category, p.show_price, p.available_for_order, IFNULL(stock.quantity, 0) as quantity, p.customizable,
            IFNULL(pa.minimal_quantity, p.minimal_quantity) as minimal_quantity, stock.out_of_stock,
            product_shop.`date_add` > "'.date('Y-m-d', strtotime('-'.(false !== Configuration::get('PS_NB_DAYS_NEW_PRODUCT') ? (int) Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY')).'" as new,
            product_shop.`on_sale`, product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity
        FROM `'._DB_PREFIX_.'product_sale` ps
        LEFT JOIN `'._DB_PREFIX_.'product` p ON ps.`id_product` = p.`id_product`
        '.Shop::addSqlAssociation('product', 'p').'
        LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop
            ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop='.(int) $this->context->shop->id.')
        LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (product_attribute_shop.id_product_attribute=pa.id_product_attribute)
        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
            ON p.`id_product` = pl.`id_product`
            AND pl.`id_lang` = '.(int) $idLang.Shop::addSqlRestrictionOnLang('pl').'
        LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
            ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int) $this->context->shop->id.')
        LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int) $idLang.')
        LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
            ON cl.`id_category` = product_shop.`id_category_default`
            AND cl.`id_lang` = '.(int) $idLang.Shop::addSqlRestrictionOnLang('cl').Product::sqlStock('p', 0);
        // WHERE product_shop.`active` = 1 // this will never get into current query!
		$sql .= '
        WHERE product_shop.`active` = 1
        AND p.`visibility` != \'none\'
		AND p.`active` = 1
		AND p.`available_for_order` = 1
		';
        $sql .= ($priceAbove > 0 ? ' AND p.`price` > ' . (double) $priceAbove : '');
        if (Group::isFeatureActive()) {
            $groups = FrontController::getCurrentCustomerGroups();
            $sql .= ' AND EXISTS(SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp
                JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.id_category = cg.id_category AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1').')
                WHERE cp.`id_product` = p.`id_product`)';
        }
        $sql .= '
        ORDER BY ps.quantity DESC
        LIMIT '.(int) ($pageNumber * $nbProducts).', '.(int) $nbProducts;
        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
            return false;
        }
        return Product::getProductsProperties($idLang, $result);
    }

    /**
     * @return bool|string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayHomeTabContent()
    {
        return $this->hookDisplayHome();
    }

    /**
     * @return bool|string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayHome()
    {
        if (! $this->isCached('blockbestsellers-home.tpl', $this->getCacheId('blockbestsellers-home'))) {
            $bestSellers = $this->getBestSellers();
            if ($bestSellers === false) {
                return false;
            }
            $this->smarty->assign(
                [
                    'best_sellers' => $bestSellers,
                    'homeSize' => Image::getSize(ImageType::getFormatedName('home')),
                ]
            );
        }

        return $this->display(
            __FILE__,
            'blockbestsellers-home.tpl',
            $this->getCacheId('blockbestsellers-home')
        );
    }

    /**
     * @return bool|string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookLeftColumn()
    {
        return $this->hookRightColumn();
    }

    /**
     * @return bool|string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookRightColumn()
    {
        if (!$this->isCached('blockbestsellers.tpl', $this->getCacheId('blockbestsellers-col'))) {
            $bestSellers = $this->getBestSellers();
            if ($bestSellers === false) {
                return false;
            }
            $this->smarty->assign(
                [
                    'best_sellers'             => $bestSellers,
                    'display_link_bestsellers' => Configuration::get(static::BESTSELLERS_DISPLAY),
                    'mediumSize'               => Image::getSize(ImageType::getFormatedName('medium')),
                    'smallSize'                => Image::getSize(ImageType::getFormatedName('small')),
                ]
            );
        }

        return $this->display(__FILE__, 'blockbestsellers.tpl', $this->getCacheId('blockbestsellers-col'));
    }
}
