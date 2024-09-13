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
 * Class SocialSharing
 */
class SocialSharing extends Module
{
    const CONFIG_KEY_DISPLAY_OPEN_GRAPH_TAGS = 'PS_SC_DISPLAY_OPEN_GRAPH_TAGS';
    const CONFIG_KEY_PRODUCT_IMAGE_TYPE = 'PS_SC_PRODUCT_IMAGE_TYPE';

    /**
     * @var string[]
     */
    protected static $networks = ['Facebook', 'Twitter', 'Pinterest'];

    /**
     * @var string
     */
    protected $html = '';

    /**
     * SocialSharing constructor.
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'socialsharing';
        $this->author = 'thirty bees';
        $this->tab = 'advertising_marketing';
        $this->need_instance = 0;
        $this->version = '2.2.1';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Block Social Sharing');
        $this->description = $this->l('Displays social sharing buttons (Twitter, Facebook and Pinterest) on every product page.');
        $this->tb_versions_compliancy = '> 1.0.0';
        $this->tb_min_version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => '1.6.99.99'];
    }

    /**
     * Install this module
     *
     * @return bool Indicates whether this module has been succesfully installed
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 1.0.0
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        // Activate every option by default
        Configuration::updateValue('PS_SC_TWITTER', 1);
        Configuration::updateValue('PS_SC_FACEBOOK', 1);
        Configuration::updateValue('PS_SC_PINTEREST', 1);

        // The module will add a meta in the product page header and add a javascript file
        $this->registerHook('header');

        // This hook could have been called only from the product page, but it's better to add the JS in all the pages with CCC
        /*
            $id_hook_header = Hook::getIdByName('header');
            $pages = array();
            foreach (Meta::getPages() as $page)
                if ($page != 'product')
                    $pages[] = $page;
            $this->registerExceptions($id_hook_header, $pages);
        */

        // The module need to clear the product page cache after update/delete
        $this->registerHook('actionObjectProductUpdateAfter');
        $this->registerHook('actionObjectProductDeleteAfter');

        // The module will then be hooked on the product and comparison pages
        $this->registerHook('displayRightColumnProduct');
        $this->registerHook('displayCompareExtraInformation');

        // The module will then be hooked and accessible with Smarty function
        $this->registerHook('displaySocialSharing');

        return true;
    }

    /**
     * Get module configuration page
     *
     * @return string Config page HTML
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 1.0.0
     */
    public function getContent()
    {
        if (Tools::isSubmit('submitSocialSharing')) {
            Configuration::updateValue(static::CONFIG_KEY_PRODUCT_IMAGE_TYPE, Tools::getValue(static::CONFIG_KEY_PRODUCT_IMAGE_TYPE));
            Configuration::updateValue(static::CONFIG_KEY_DISPLAY_OPEN_GRAPH_TAGS, (int)Tools::getValue(static::CONFIG_KEY_DISPLAY_OPEN_GRAPH_TAGS));
            foreach (self::$networks as $network) {
                Configuration::updateValue('PS_SC_' . strtoupper($network), (int)Tools::getValue('PS_SC_' . strtoupper($network)));
            }
            $this->html .= $this->displayConfirmation($this->l('Settings updated'));
            Tools::clearCache(Context::getContext()->smarty, $this->getTemplatePath('socialsharing.tpl'));
            Tools::clearCache(Context::getContext()->smarty, $this->getTemplatePath('socialsharing_compare.tpl'));
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&conf=6&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);

        }

        $helper = new HelperForm();
        $helper->submit_action = 'submitSocialSharing';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = ['fields_value' => $this->getConfigFieldsValues()];

        $fields = [];
        foreach (self::$networks as $network) {
            $fields[] = [
                'type' => 'switch',
                'label' => $network,
                'name' => 'PS_SC_' . strtoupper($network),
                'values' => [
                    [
                        'id' => strtolower($network) . '_active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => strtolower($network) . '_active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
            ];
        }


        $networkForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configure networks'),
                    'icon' => 'icon-share',
                ],
                'input' => $fields,
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $configForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Sharing settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Include Open Graph tags in page header'),
                        'name' => static::CONFIG_KEY_DISPLAY_OPEN_GRAPH_TAGS,
                        'values' => [
                            [
                                'id' => static::CONFIG_KEY_DISPLAY_OPEN_GRAPH_TAGS . '_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => static::CONFIG_KEY_DISPLAY_OPEN_GRAPH_TAGS . '_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Product image type'),
                        'name' => static::CONFIG_KEY_PRODUCT_IMAGE_TYPE,
                        'options' => array(
                            'query' => ImageType::getImagesTypes('products', true),
                            'id' => 'name',
                            'name' => 'name'
                        ),
                        'desc' => $this->l('Select image type you want to use for product sharing'),
                        'required' => true
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        return $this->html . $helper->generateForm([$networkForm, $configForm]);
    }

    /**
     * @return array
     * @throws PrestaShopException
     */
    public function getConfigFieldsValues()
    {
        $values = [
            static::CONFIG_KEY_PRODUCT_IMAGE_TYPE => $this->getSelectedProductImageType(),
            static::CONFIG_KEY_DISPLAY_OPEN_GRAPH_TAGS => $this->shouldDisplayOpenGraphTags(),
        ];

        foreach (self::$networks as $network) {
            $values['PS_SC_' . strtoupper($network)] = (int)Tools::getValue('PS_SC_' . strtoupper($network), Configuration::get('PS_SC_' . strtoupper($network)));
        }

        return $values;
    }

    /**
     * Hook to header display
     *
     * @param array $params Hook params
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayHeader($params)
    {
        if (!isset($this->context->controller->php_self) || !in_array($this->context->controller->php_self, ['product', 'products-comparison'])) {
            return '';
        }

        $this->context->controller->addCss($this->_path . 'css/socialsharing.css');
        $this->context->controller->addJS($this->_path . 'js/socialsharing.js');

        if ($this->shouldDisplayOpenGraphTags()) {
            if ($this->context->controller->php_self == 'product' && method_exists($this->context->controller, 'getProduct')) {
                $product = $this->context->controller->getProduct();

                if (!Validate::isLoadedObject($product)) {
                    return '';
                }
                if (!$this->isCached('socialsharing_header.tpl', $this->getCacheId('socialsharing_header|' . (isset($product->id) && $product->id ? (int)$product->id : '')))) {
                    $decimals = $this->getCurrencyDisplayPrecision($this->context->currency);

                    $this->context->smarty->assign(
                        [
                            'price' => Tools::ps_round($product->getPrice(!Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer), null), $decimals),
                            'pretax_price' => Tools::ps_round($product->getPrice(false, null), $decimals),
                            'weight' => $product->weight,
                            'weight_unit' => Configuration::get('PS_WEIGHT_UNIT'),
                            'cover' => isset($product->id) ? Product::getCover((int)$product->id) : '',
                            'coverImageType' => $this->getSelectedProductImageType(),
                            'link_rewrite' => isset($product->link_rewrite) && $product->link_rewrite ? $product->link_rewrite : '',
                        ]
                    );
                }
            }

            return $this->display(__FILE__, 'socialsharing_header.tpl', $this->getCacheId('socialsharing_header|' . (isset($product->id) && $product->id ? (int)$product->id : '')));
        } else {
            return null;
        }
    }

    /**
     * Display extra information on compare page
     *
     * @param array $params Hook params
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayCompareExtraInformation($params)
    {
        Media::addJsDef(
            [
                'sharing_name' => addcslashes($this->l('Product comparison'), "'"),
                'sharing_url' => addcslashes(
                    $this->context->link->getPageLink(
                        'products-comparison', null, $this->context->language->id,
                        ['compare_product_list' => Tools::getValue('compare_product_list')]
                    ),
                    "'"
                ),
                'sharing_img' => addcslashes(
                    $this->context->link->getMediaLink(_PS_IMG_ . Configuration::get('PS_LOGO')), "'"
                ),
            ]
        );

        if (!$this->isCached('socialsharing_compare.tpl', $this->getCacheId('socialsharing_compare'))) {
            $this->context->smarty->assign(
                [
                    'PS_SC_TWITTER' => Configuration::get('PS_SC_TWITTER'),
                    'PS_SC_FACEBOOK' => Configuration::get('PS_SC_FACEBOOK'),
                    'PS_SC_PINTEREST' => Configuration::get('PS_SC_PINTEREST'),
                ]
            );
        }

        return $this->display(__FILE__, 'socialsharing_compare.tpl', $this->getCacheId('socialsharing_compare'));
    }

    /**
     * Display right column of a product
     *
     * @param array $params
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayRightColumnProduct($params)
    {
        return $this->hookDisplaySocialSharing();
    }

    /**
     * Display social sharing HTML
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplaySocialSharing()
    {
        if (!isset($this->context->controller) || !method_exists($this->context->controller, 'getProduct')) {
            return '';
        }

        $product = $this->context->controller->getProduct();

        if (isset($product) && Validate::isLoadedObject($product)) {
            $imageCoverId = $product->getCover($product->id);
            if (is_array($imageCoverId) && isset($imageCoverId['id_image'])) {
                $imageCoverId = (int)$imageCoverId['id_image'];
            } else {
                $imageCoverId = 0;
            }

            Media::addJsDef(
                [
                    'sharing_name' => addcslashes($product->name, "'"),
                    'sharing_url' => addcslashes($this->context->link->getProductLink($product), "'"),
                    'sharing_img' => addcslashes($this->context->link->getImageLink($product->link_rewrite, $imageCoverId, $this->getSelectedProductImageType()), "'"),
                ]
            );
        }

        if (!$this->isCached('socialsharing.tpl', $this->getCacheId('socialsharing|' . (isset($product->id) && $product->id ? (int)$product->id : '')))) {
            $this->context->smarty->assign(
                [
                    'product' => isset($product) ? $product : '',
                    'PS_SC_TWITTER' => Configuration::get('PS_SC_TWITTER'),
                    'PS_SC_FACEBOOK' => Configuration::get('PS_SC_FACEBOOK'),
                    'PS_SC_PINTEREST' => Configuration::get('PS_SC_PINTEREST'),
                ]
            );
        }

        return $this->display(__FILE__, 'socialsharing.tpl', $this->getCacheId('socialsharing|' . (isset($product->id) && $product->id ? (int)$product->id : '')));
    }

    /**
     * @param array $params
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookExtraleft($params)
    {
        return $this->hookDisplaySocialSharing();
    }

    /**
     * @param array $params
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookProductActions($params)
    {
        return $this->hookDisplaySocialSharing();
    }

    /**
     * @param array $params
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookProductFooter($params)
    {
        return $this->hookDisplaySocialSharing();
    }

    /**
     * @param array $params
     *
     * @return false|int
     * @throws PrestaShopException
     */
    public function hookActionObjectProductUpdateAfter($params)
    {
        return $this->clearProductHeaderCache($params['object']->id);
    }

    /**
     * @param int $idProduct
     *
     * @return false|int
     * @throws PrestaShopException
     */
    protected function clearProductHeaderCache($idProduct)
    {
        return $this->_clearCache('socialsharing_header.tpl', 'socialsharing_header|' . (int)$idProduct);
    }

    /**
     * @param array $params
     *
     * @return false|int
     * @throws PrestaShopException
     */
    public function hookActionObjectProductDeleteAfter($params)
    {
        return $this->clearProductHeaderCache($params['object']->id);
    }

    /**
     * Returns selected product image type
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getSelectedProductImageType()
    {
        $imageTypes = [];
        foreach (ImageType::getImagesTypes('products', true) as $imageType) {
            $imageTypes[] = $imageType['name'];
        }

        $value = Configuration::get(static::CONFIG_KEY_PRODUCT_IMAGE_TYPE);
        if (!$value || !in_array($value, $imageTypes)) {
            $value = array_shift($imageTypes);
            Configuration::updateValue(static::CONFIG_KEY_PRODUCT_IMAGE_TYPE, $value);
        }
        return $value;
    }

    /**
     * Returns true, if open graph tags should be displayed in page header
     *
     * @return bool
     * @throws PrestaShopException
     */
    public function shouldDisplayOpenGraphTags()
    {
        $value = Configuration::get(static::CONFIG_KEY_DISPLAY_OPEN_GRAPH_TAGS);
        if ($value === false) {
            $value = 1;
            Configuration::updateValue(static::CONFIG_KEY_DISPLAY_OPEN_GRAPH_TAGS, $value);
        }
        return (bool)$value;
    }

    /**
     * @param Currency $currency
     *
     * @return int
     * @throws PrestaShopException
     */
    protected function getCurrencyDisplayPrecision($currency)
    {
        if (method_exists($currency, 'getDisplayPrecision')) {
            return $currency->getDisplayPrecision();
        }
        if ($currency->decimals) {
            return (int)Configuration::get('PS_PRICE_DISPLAY_PRECISION');
        }
        return 0;
    }
}
