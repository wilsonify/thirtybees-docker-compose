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
 * Class Dashactivity
 */
class Dashactivity extends Module
{
    const COLORS = ['#1F77B4', '#FF7F0E', '#2CA02C', "#BD7EBE", "#E60049"];

    /**
     * Dashactivity constructor.
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'dashactivity';
        $this->tab = 'dashboard';
        $this->version = '1.4.1';
        $this->author = 'thirty bees';
        $this->need_instance = 0;

        parent::__construct();
        $this->displayName = $this->l('Dashboard Activity');
        $this->description = 'Show recent users and other statistics.';
        $this->tb_versions_compliancy = '> 1.0.0';
        $this->tb_min_version = '1.0.0';
    }

    /**
     * Install this module
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {
        Configuration::updateValue('DASHACTIVITY_CART_ACTIVE', 30);
        Configuration::updateValue('DASHACTIVITY_CART_ABANDONED_MIN', 24);
        Configuration::updateValue('DASHACTIVITY_CART_ABANDONED_MAX', 48);
        Configuration::updateValue('DASHACTIVITY_VISITOR_ONLINE', 30);

        if (!parent::install()) {
            return false;
        }

        foreach ([
            'dashboardZoneOne',
            'dashboardData',
            'actionAdminControllerSetMedia',
        ] as $hook) {
            try {
                $this->registerHook($hook);
            } catch (PrestaShopException $e) {
                $this->context->controller->errors[] = sprintf($this->l('Dashboard activity module: Unable to register hook `%s`'), $hook);
            }
        }

        return true;
    }

    /**
     * Action admin controller set media
     */
    public function hookActionAdminControllerSetMedia()
    {
        if (get_class($this->context->controller) == 'AdminDashboardController') {
            if (method_exists($this->context->controller, 'addJquery')) {
                $this->context->controller->addJquery();
            }

            $this->context->controller->addJs($this->_path.'views/js/'.$this->name.'.js');
            $this->context->controller->addJs(
                [
                    _PS_JS_DIR_.'date.js',
                    _PS_JS_DIR_.'tools.js',
                ] // retro compat themes 1.5
            );
        }
    }

    /**
     * Hook to dashboard zone one
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws PrestaShopException
     */
    public function hookDashboardZoneOne()
    {
        /** @var Gapi $gapi */
        $gapiMode = 'configure';
        if (!Module::isInstalled('gapi')) {
            $gapiMode = 'install';
        } elseif (($gapi = Module::getInstanceByName('gapi')) && Validate::isLoadedObject($gapi) && $gapi->isConfigured()) {
            $gapiMode = false;
        }

        $this->context->smarty->assign($this->getConfigFieldsValues());
        $this->context->smarty->assign(
            [
                'gapi_mode'                => $gapiMode,
                'dashactivity_config_form' => $this->renderConfigForm(),
                'date_subtitle'            => $this->l('(from %s to %s)'),
                'date_format'              => $this->context->language->date_format_lite,
                'link'                     => $this->context->link,
            ]
        );

        return $this->display(__FILE__, 'dashboard_zone_one.tpl');
    }

    /**
     * @return array
     * @throws PrestaShopException
     * @throws PrestaShopException
     */
    public function getConfigFieldsValues()
    {
        return [
            'DASHACTIVITY_CART_ACTIVE'        => Tools::getValue('DASHACTIVITY_CART_ACTIVE', Configuration::get('DASHACTIVITY_CART_ACTIVE')),
            'DASHACTIVITY_CART_ABANDONED_MIN' => Tools::getValue('DASHACTIVITY_CART_ABANDONED_MIN', Configuration::get('DASHACTIVITY_CART_ABANDONED_MIN')),
            'DASHACTIVITY_CART_ABANDONED_MAX' => Tools::getValue('DASHACTIVITY_CART_ABANDONED_MAX', Configuration::get('DASHACTIVITY_CART_ABANDONED_MAX')),
            'DASHACTIVITY_VISITOR_ONLINE'     => Tools::getValue('DASHACTIVITY_VISITOR_ONLINE', Configuration::get('DASHACTIVITY_VISITOR_ONLINE')),
        ];
    }

    /**
     * Render the configuration form
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @throws PrestaShopException
     * @throws PrestaShopException
     */
    public function renderConfigForm()
    {
        $fieldsForm = [
            'form' => [
                'id_form' => 'step_carrier_general',
                'input'   => [],
                'submit'  => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right submit_dash_config',
                    'reset' => [
                        'title' => $this->l('Cancel'),
                        'class' => 'btn btn-default cancel_dash_config',
                    ],
                ],
            ],
        ];

        $fieldsForm['form']['input'][] = [
            'label'   => $this->l('Active cart'),
            'hint'    => $this->l('How long (in minutes) a cart is to be considered as active after the last recorded change (default: 30 min).'),
            'name'    => 'DASHACTIVITY_CART_ACTIVE',
            'type'    => 'select',
            'options' => [
                'query' => [
                    ['id' => 15, 'name' => 15],
                    ['id' => 30, 'name' => 30],
                    ['id' => 45, 'name' => 45],
                    ['id' => 60, 'name' => 60],
                    ['id' => 90, 'name' => 90],
                    ['id' => 120, 'name' => 120],
                ],
                'id'    => 'id',
                'name'  => 'name',
            ],
        ];
        $fieldsForm['form']['input'][] = [
            'label'   => $this->l('Online visitor'),
            'hint'    => $this->l('How long (in minutes) a visitor is to be considered as online after their last action (default: 30 min).'),
            'name'    => 'DASHACTIVITY_VISITOR_ONLINE',
            'type'    => 'select',
            'options' => [
                'query' => [
                    ['id' => 15, 'name' => 15],
                    ['id' => 30, 'name' => 30],
                    ['id' => 45, 'name' => 45],
                    ['id' => 60, 'name' => 60],
                    ['id' => 90, 'name' => 90],
                    ['id' => 120, 'name' => 120],
                ],
                'id'    => 'id',
                'name'  => 'name',
            ],
        ];
        $fieldsForm['form']['input'][] = [
            'label'  => $this->l('Abandoned cart (min)'),
            'hint'   => $this->l('How long (in hours) after the last action a cart is to be considered as abandoned (default: 24 hrs).'),
            'name'   => 'DASHACTIVITY_CART_ABANDONED_MIN',
            'type'   => 'text',
            'suffix' => $this->l('hrs'),
        ];
        $fieldsForm['form']['input'][] = [
            'label'  => $this->l('Abandoned cart (max)'),
            'hint'   => $this->l('How long (in hours) after the last action a cart is no longer to be considered as abandoned (default: 24 hrs).'),
            'name'   => 'DASHACTIVITY_CART_ABANDONED_MAX',
            'type'   => 'text',
            'suffix' => $this->l('hrs'),
        ];

        /** @var AdminController $controller */
        $controller = $this->context->controller;
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitDashConfig';
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$fieldsForm]);
    }

    /**
     * Hook to data dashboard
     *
     * @param array $params
     *
     * @return array
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDashboardData($params)
    {
        if (strlen($params['date_from']) == 10) {
            $params['date_from'] .= ' 00:00:00';
        }
        if (strlen($params['date_to']) == 10) {
            $params['date_to'] .= ' 23:59:59';
        }

        $visits = $uniqueVisitors = 0;
        if (Configuration::get('PS_DASHBOARD_SIMULATION')) {
            $days = (strtotime($params['date_to']) - strtotime($params['date_from'])) / 3600 / 24;
            $onlineVisitors = rand(10, 50);
            $visits = rand(200, 2000) * $days;

            return [
                'data_value'      => [
                    'pending_orders'        => round(rand(0, 5)),
                    'return_exchanges'      => round(rand(0, 5)),
                    'abandoned_cart'        => round(rand(5, 50)),
                    'products_out_of_stock' => round(rand(1, 10)),
                    'new_messages'          => round(rand(1, 10) * $days),
                    'product_reviews'       => round(rand(5, 50) * $days),
                    'new_customers'         => round(rand(1, 5) * $days),
                    'online_visitor'        => round($onlineVisitors),
                    'active_shopping_cart'  => round($onlineVisitors / 10),
                    'new_registrations'     => round(rand(1, 5) * $days),
                    'total_suscribers'      => round(rand(200, 2000)),
                    'visits'                => round($visits),
                    'unique_visitors'       => round($visits * 0.6),
                ],
                'data_trends'     => [
                    'orders_trends' => ['way' => 'down', 'value' => 0.42],
                ],
                'data_list_small' => [
                    'dash_traffic_source' => [
                        '<i class="icon-circle" style="color:'.$this->getColor(0).'"></i> thirtybees.com' => round($visits / 2),
                        '<i class="icon-circle" style="color:'.$this->getColor(1).'"></i> google.com'     => round($visits / 3),
                        '<i class="icon-circle" style="color:'.$this->getColor(2).'"></i> Direct Traffic' => round($visits / 4),
                    ],
                ],
                'data_chart'      => [
                    'dash_trends_chart1' => [
                        'chart_type' => 'pie_chart_trends',
                        'data'       => [
                            ['key' => 'thirtybees.com', 'y' => round($visits / 2), 'color' => $this->getColor(0)],
                            ['key' => 'google.com', 'y' => round($visits / 3), 'color' => $this->getColor(1)],
                            ['key' => 'Direct Traffic', 'y' => round($visits / 4), 'color' => $this->getColor(2)],
                        ],
                    ],
                ],
            ];
        }

        /** @var Gapi $gapi */
        $gapi = Module::isInstalled('gapi') ? Module::getInstanceByName('gapi') : false;
        if (Validate::isLoadedObject($gapi) && $gapi->isConfigured()) {
            $visits = $uniqueVisitors = $onlineVisitors = 0;
            if ($result = $gapi->requestReportData('', 'rt:activeUsers', null, null, null, null, null, null)) {
                $visits = $result[0]['metrics']['rt:activeUsers'];
                $onlineVisitors = $uniqueVisitors = $result[0]['metrics']['rt:activeUsers'];
            }
        } else {
            if ($maintenanceIps = Configuration::get('PS_MAINTENANCE_IP')) {
                $maintenanceIps = implode(',', array_map('ip2long', array_map('trim', explode(',', $maintenanceIps))));
            }
            try {
                Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS((new DbQuery())
                    ->select('c.`id_guest`, c.`ip_address`, c.`date_add`, c.`http_referer`, "-" AS `page`')
                    ->from('connections', 'c')
                    ->innerJoin('guest', 'g', 'c.`id_guest` = g.`id_guest`')
                    ->where('g.`id_customer` IS NULL OR g.`id_customer` = 0 '.Shop::addSqlRestriction(false, 'c'))
                    ->where('TIME_TO_SEC(TIMEDIFF(\''.pSQL(date('Y-m-d H:i:00', time())).'\', c.`date_add`)) < 1800')
                    ->where($maintenanceIps ? 'c.`ip_address` NOT IN ('.preg_replace('/[^,0-9]/', '', $maintenanceIps).')' : '')
                    ->orderBy('c.`date_add` DESC'));
                $onlineVisitors = Db::getInstance()->NumRows();
            } catch (PrestaShopException $e) {
                $onlineVisitors = 0;
            }
        }

        try {
            $pendingOrders = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('COUNT(*)')
                    ->from('orders', 'o')
                    ->leftJoin('order_state', 'os', 'o.`current_state` = os.`id_order_state`')
                    ->where('os.`paid` = 1')
                    ->where('os.`shipped` = 0 '.Shop::addSqlRestriction(Shop::SHARE_ORDER))
            );
        } catch (PrestaShopException $e) {
            $pendingOrders = 0;
        }

        try {
            $abandonedCarts = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('COUNT(*)')
                    ->from('cart', 'c')
                    ->leftJoin('orders', 'o', 'o.`id_cart` = c.`id_cart` AND o.`id_order` IS NULL')
                    ->where('(o.`date_upd` BETWEEN "'.pSQL(date('Y-m-d H:i:s', strtotime('-'.(int) Configuration::get('DASHACTIVITY_CART_ABANDONED_MAX').' MIN'))).'" AND "'.pSQL(date('Y-m-d H:i:s', strtotime('-'.(int) Configuration::get('DASHACTIVITY_CART_ABANDONED_MIN').' MIN'))).'") '.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o'))
            );
        } catch (PrestaShopException $e) {
            $abandonedCarts = 0;
        }

        try {
            $returnExchanges = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('COUNT(*)')
                    ->from('orders', 'o')
                    ->leftJoin('order_return', 'or2', 'o.`id_order` = or2.`id_order`')
                    ->where('or2.`date_add` BETWEEN "'.pSQL($params['date_from']).'" AND "'.pSQL($params['date_to']).'" '.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o'))
            );
        } catch (PrestaShopException $e) {
            $returnExchanges = 0;
        }

        try {
            $productsOutOfStock = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('SUM(IF(IFNULL(stock.quantity, 0) > 0, 0, 1))')
                    ->from('product', 'p')
                    ->join(Shop::addSqlAssociation('product', 'p'))
                    ->leftJoin('product_attribute', 'pa', 'p.`id_product` = pa.`id_product`')
                    ->join(Product::sqlStock('p', 'pa'))
                    ->where('p.`active` = 1')
            );
        } catch (PrestaShopException $e) {
            $productsOutOfStock = 0;
        }

        $newMessages = AdminStatsController::getPendingMessages();

        try {
            $activeShoppingCarts = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('COUNT(*)')
                    ->from(bqSQL(Cart::$definition['table']))
                    ->where('date_upd > "'.pSQL(date('Y-m-d H:i:s', strtotime('-'.(int) Configuration::get('DASHACTIVITY_CART_ACTIVE').' MIN'))).'" '.Shop::addSqlRestriction(Shop::SHARE_ORDER))
            );
        } catch (PrestaShopException $e) {
            $activeShoppingCarts = 0;
        }

        try {
            $newCustomers = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('COUNT(*)')
                    ->from('customer')
                    ->where('`date_add` BETWEEN "'.pSQL($params['date_from']).'" AND "'.pSQL($params['date_to']).'" '.Shop::addSqlRestriction(Shop::SHARE_ORDER))
            );
        } catch (PrestaShopException $e) {
            $newCustomers = 0;
        }

        try {
            $newRegistrations = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('COUNT(*)')
                    ->from(bqSQL(Customer::$definition['table']))
                    ->where('`newsletter_date_add` BETWEEN "'.pSQL($params['date_from']).'" AND "'.pSQL($params['date_to']).'"')
                    ->where('`newsletter` = 1 '.Shop::addSqlRestriction(Shop::SHARE_ORDER))
            );
        } catch (PrestaShopException $e) {
            $newRegistrations = 0;
        }

        try {
            $totalSubscribers = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('COUNT(*)')
                    ->from(bqSQL(Customer::$definition['table']))
                    ->where('`newsletter` = 1 '.Shop::addSqlRestriction(Shop::SHARE_ORDER))
            );
        } catch (PrestaShopException $e) {
            $totalSubscribers = 0;
        }

        if (Module::isInstalled('blocknewsletter')) {
            try {
                $newRegistrations += (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                    (new DbQuery())
                        ->select('COUNT(*)')
                        ->from('newsletter')
                        ->where('`active` = 1')
                        ->where('`newsletter_date_add` BETWEEN "'.pSQL($params['date_from']).'" AND "'.pSQL($params['date_to']).'" '.Shop::addSqlRestriction(Shop::SHARE_ORDER))
                );
            } catch (PrestaShopException $e) {
            }

            try {
                $totalSubscribers += (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                    (new DbQuery())
                        ->select('COUNT(*)')
                        ->from('newsletter')
                        ->where('`active` = 1 '.Shop::addSqlRestriction(Shop::SHARE_ORDER))
                );
            } catch (PrestaShopException $e) {
            }
        }

        $productReviews = 0;
        if (Module::isInstalled('productcomments')) {
            try {
                $productReviews += Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                    (new DbQuery())
                        ->select('COUNT(*)')
                        ->from('product_comment', 'pc')
                        ->leftJoin('product', 'p', 'pc.`id_product` = p.`id_product` '.Shop::addSqlAssociation('product', 'p'))
                        ->where('pc.`deleted` = 0')
                        ->where('pc.`date_add` BETWEEN "'.pSQL($params['date_from']).'" AND "'.pSQL($params['date_to']).'" '.Shop::addSqlRestriction(Shop::SHARE_ORDER))
                );
            } catch (PrestaShopException $e) {
            }
        }

        $referers = $this->getReferer($params['date_from'], $params['date_to'], count(static::COLORS));

        return [
            'data_value'      => [
                'pending_orders'        => (int) $pendingOrders,
                'return_exchanges'      => (int) $returnExchanges,
                'abandoned_cart'        => (int) $abandonedCarts,
                'products_out_of_stock' => (int) $productsOutOfStock,
                'new_messages'          => (int) $newMessages,
                'product_reviews'       => (int) $productReviews,
                'new_customers'         => (int) $newCustomers,
                'online_visitor'        => (int) $onlineVisitors,
                'active_shopping_cart'  => (int) $activeShoppingCarts,
                'new_registrations'     => (int) $newRegistrations,
                'total_suscribers'      => (int) $totalSubscribers,
                'visits'                => (int) $visits,
                'unique_visitors'       => (int) $uniqueVisitors,
            ],
            'data_trends'     => [
                'orders_trends' => ['way' => 'down', 'value' => 0.42],
            ],
            'data_list_small' => [
                'dash_traffic_source' => $this->getTrafficSources($referers),
            ],
            'data_chart'      => [
                'dash_trends_chart1' => $this->getChartTrafficSource($referers),
            ],
        ];
    }

    /**
     * Get traffic sources
     *
     * @param array $referrers
     *
     * @return array
     */
    protected function getTrafficSources($referrers)
    {
        $trafficSources = [];
        $colorIndex = 0;
        foreach ($referrers as $referrerName => $n) {
            $color = $this->getColor($colorIndex++);
            $trafficSources['<i class="icon-circle" style="color:'.$color.'"></i> '.$referrerName] = $n;
        }

        return $trafficSources;
    }

    /**
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $limit
     *
     * @return array
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function getReferer($dateFrom, $dateTo, $limit)
    {
        /** @var Gapi $gapi */
        $gapi = Module::isInstalled('gapi') ? Module::getInstanceByName('gapi') : false;
        if (Validate::isLoadedObject($gapi) && $gapi->isConfigured()) {
            $websites = [];
            if ($result = $gapi->requestReportData(
                'ga:source',
                'ga:visitors',
                substr($dateFrom, 0, 10),
                substr($dateTo, 0, 10),
                '-ga:visitors',
                null,
                1,
                $limit
            )
            ) {
                foreach ($result as $row) {
                    $websites[$row['dimensions']['source']] = $row['metrics']['visitors'];
                }
            }
        } else {
            $directLink = $this->l('Direct link');
            $websites = [$directLink => 0];

            try {
                $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
                    (new DbQuery())
                        ->select('c.http_referer')
                        ->select('count(1) as cnt')
                        ->from('connections', 'c')
                        ->where('c.`date_add` BETWEEN "'.pSQL($dateFrom).'" AND "'.pSQL($dateTo).'" '.Shop::addSqlRestriction())
                        ->groupBy('c.http_referer')
                );
            } catch (PrestaShopException $e) {
                $result = [];
            }
            foreach ($result as $row) {
                $referer = $row['http_referer'];
                $count = (int)$row['cnt'];
                if (empty($referer)) {
                    $websites[$directLink] += $count;
                } else {
                    $website = preg_replace('/^www./', '', parse_url($referer, PHP_URL_HOST));
                    if (!isset($websites[$website])) {
                        $websites[$website] = $count;
                    } else {
                        $websites[$website]+= $count;
                    }
                }
            }
            arsort($websites);
            $websites = array_slice($websites, 0, $limit);
        }

        return $websites;
    }

    /**
     * Get traffic sources for the chart
     *
     * @param array $referers
     *
     * @return array
     */
    protected function getChartTrafficSource($referers)
    {
        $return = ['chart_type' => 'pie_chart_trends', 'data' => []];
        $colorIndex = 0;
        foreach ($referers as $refererName => $n) {
            $color = $this->getColor($colorIndex++);
            $return['data'][] = [
                'key' => $refererName,
                'y' => $n,
                'color' => $color
            ];
        }

        return $return;
    }

    /**
     * Save widged configuration
     *
     * @param array $params
     * @throws PrestaShopException
     */
    public function saveDashConfig($params)
    {
        Configuration::updateValue('DASHACTIVITY_CART_ACTIVE', (int)$params['DASHACTIVITY_CART_ACTIVE']);
        Configuration::updateValue('DASHACTIVITY_CART_ABANDONED_MIN', (int)$params['DASHACTIVITY_CART_ABANDONED_MIN']);
        Configuration::updateValue('DASHACTIVITY_CART_ABANDONED_MAX', (int)$params['DASHACTIVITY_CART_ABANDONED_MAX']);
        Configuration::updateValue('DASHACTIVITY_VISITOR_ONLINE', (int)$params['DASHACTIVITY_VISITOR_ONLINE']);
    }

    /**
     * @param int $colorIndex
     *
     * @return string
     */
    protected function getColor($colorIndex)
    {
        $colorIndex = $colorIndex % count(static::COLORS);
        return static::COLORS[$colorIndex];
    }
}
