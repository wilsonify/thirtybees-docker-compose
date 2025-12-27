<?php
/**
 * Copyright (C) 2017-2019 thirty bees
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
 * @copyright 2017-2019 thirty bees
 * @license   Academic Free License (AFL 3.0)
 */

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class TrackingFront
 */
class TrackingFront extends Module
{
    /**
     * TrackingFront constructor.
     */
    public function __construct()
    {
        $this->name = 'trackingfront';
        $this->tab = 'shipping_logistics';
        $this->version = '2.0.4';
        $this->author = 'thirty bees';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Tracking - Front Office');
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => '1.6.1.99'];
        $this->tb_min_version = '1.0.0';
        $this->tb_versions_compliancy = '>= 1.0.0';
        $this->description = $this->l('Enables your affiliates to access their own statistics. See Stats/Referrers.');
    }

    /**
     * @return void
     * @throws PrestaShopException
     * @throws Adapter_Exception
     */
    public function postProcess()
    {
        if (Tools::isSubmit('ajaxProductFilter')) {
            $fakeEmployee = new Employee();
            $fakeEmployee->id = 424242;
            $fakeEmployee->stats_date_from = $this->context->cookie->stats_date_from;
            $fakeEmployee->stats_date_to = $this->context->cookie->stats_date_to;

            if (empty($fakeEmployee->stats_date_from) || empty($fakeEmployee->stats_date_to) || $fakeEmployee->stats_date_from == '0000-00-00' || $fakeEmployee->stats_date_to == '0000-00-00') {
                if (empty($fakeEmployee->stats_date_from) || $fakeEmployee->stats_date_from == '0000-00-00') {
                    $fakeEmployee->stats_date_from = date('Y').'-01-01';
                }
                if (empty($fakeEmployee->stats_date_to) || $fakeEmployee->stats_date_to == '0000-00-00') {
                    $fakeEmployee->stats_date_to = date('Y').'-12-31';
                }
            }

            $result = Db::getInstance()->getRow(
                '
			SELECT `id_referrer`
			FROM `'._DB_PREFIX_.'referrer`
			WHERE `id_referrer` = '.(int) Tools::getValue('id_referrer').' AND `passwd` = \''.pSQL(Tools::getValue('token')).'\''
            );

            if (isset($result['id_referrer']) && (int) $result['id_referrer'] > 0) {
                Referrer::getAjaxProduct((int) $result['id_referrer'], (int) Tools::getValue('id_product'), $fakeEmployee);
            }
        } elseif (Tools::isSubmit('logout_tracking')) {
            unset($this->context->cookie->tracking_id);
            unset($this->context->cookie->tracking_passwd);
            Tools::redirect(Tools::getShopDomain(true, false).__PS_BASE_URI__.'modules/trackingfront/stats.php');
        } elseif (Tools::isSubmit('submitLoginTracking')) {
            $errors = [];
            $login = trim(Tools::getValue('login'));
            $passwd = trim(Tools::getValue('passwd'));
            if (empty($login)) {
                $errors[] = $this->l('login is required');
            } elseif (!Validate::isGenericName($login)) {
                $errors[] = $this->l('invalid login');
            } elseif (empty($passwd)) {
                $errors[] = $this->l('password is required');
            } elseif (!Validate::isPasswd($passwd, 1)) {
                $errors[] = $this->l('invalid password');
            } else {
                $sql = (new DbQuery())
                    ->select('id_referrer, passwd')
                    ->from('referrer')
                    ->where('name = "' . pSQL($login) . '"');
                $result = Db::getInstance()->getRow($sql);

                if (isset($result['id_referrer']) && isset($result['passwd'])) {
                    $trackingId = (int) $result['id_referrer'];
                    if ($trackingId) {
                        $hashedPassword = $result['passwd'];
                        if (password_verify($passwd, $hashedPassword)) {
                            die($this->authenticated($trackingId, $result['passwd']));
                        }
                    }
                }
                $errors[] = $this->l('authentication failed');
            }
            $this->smarty->assign('errors', $errors);
        }

        $from = date('Y-m-d');
        $to = date('Y-m-d');

        if (Tools::isSubmit('submitDatePicker')) {
            $from = Tools::getValue('datepickerFrom');
            $to = Tools::getValue('datepickerTo');
        }
        if (Tools::isSubmit('submitDateDay')) {
            $from = date('Y-m-d');
            $to = date('Y-m-d');
        }
        if (Tools::isSubmit('submitDateDayPrev')) {
            $yesterday = time() - 60 * 60 * 24;
            $from = date('Y-m-d', $yesterday);
            $to = date('Y-m-d', $yesterday);
        }
        if (Tools::isSubmit('submitDateMonth')) {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
        }
        if (Tools::isSubmit('submitDateMonthPrev')) {
            $m = (date('m') == 1 ? 12 : date('m') - 1);
            $y = ($m == 12 ? date('Y') - 1 : date('Y'));
            $from = $y.'-'.$m.'-01';
            $to = $y.'-'.$m.date('-t', mktime(12, 0, 0, $m, 15, $y));
        }
        if (Tools::isSubmit('submitDateYear')) {
            $from = date('Y-01-01');
            $to = date('Y-12-31');
        }
        if (Tools::isSubmit('submitDateYearPrev')) {
            $from = (date('Y') - 1).date('-01-01');
            $to = (date('Y') - 1).date('-12-31');
        }
        $this->context->cookie->stats_date_from = $from;
        $this->context->cookie->stats_date_to = $to;
    }

    /**
     * @return bool
     */
    public function isLogged()
    {
        if (!$this->context->cookie->tracking_id || !$this->context->cookie->tracking_passwd) {
            return false;
        }
        $result = Db::getInstance()->getRow(
            '
		SELECT `id_referrer`
		FROM `'._DB_PREFIX_.'referrer`
		WHERE `id_referrer` = '.(int) $this->context->cookie->tracking_id.' AND `passwd` = \''.pSQL($this->context->cookie->tracking_passwd).'\''
        );

        return isset($result['id_referrer']) ? $result['id_referrer'] : false;
    }

    /**
     * @return string
     */
    public function displayLogin()
    {
        return $this->display(__FILE__, 'login.tpl');
    }

    /**
     * @return string
     */
    public function displayAccount()
    {
        if (!isset($this->context->cookie->stats_date_from)) {
            $this->context->cookie->stats_date_from = date('Y-m-01');
        }
        if (!isset($this->context->cookie->stats_date_to)) {
            $this->context->cookie->stats_date_to = date('Y-m-t');
        }
        Referrer::refreshCache([['id_referrer' => (int) $this->context->cookie->tracking_id]]);

        $referrer = new Referrer((int) $this->context->cookie->tracking_id);
        $this->smarty->assign('referrer', $referrer);
        $this->smarty->assign('datepickerFrom', $this->context->cookie->stats_date_from);
        $this->smarty->assign('datepickerTo', $this->context->cookie->stats_date_to);

        $displayTab = [
            'uniqs'         => $this->l('Unique visitors'),
            'visitors'      => $this->l('Visitors'),
            'visits'        => $this->l('Visits'),
            'pages'         => $this->l('Pages viewed'),
            'registrations' => $this->l('Registrations'),
            'orders'        => $this->l('Orders'),
            'base_fee'      => $this->l('Base fee'),
            'percent_fee'   => $this->l('Percent fee'),
            'click_fee'     => $this->l('Click fee'),
            'sales'         => $this->l('Sales'),
            'cart'          => $this->l('Average cart'),
            'reg_rate'      => $this->l('Registration rate'),
            'order_rate'    => $this->l('Order rate'),
        ];
        $this->smarty->assign('displayTab', $displayTab);

        $products = Product::getSimpleProducts($this->context->language->id);
        $productsArray = [];
        foreach ($products as $product) {
            $productsArray[] = $product['id_product'];
        }

        $jsFiles = [];

        $jqueryFiles = Media::getJqueryPath();
        if (is_array($jqueryFiles)) {
            $jsFiles = array_merge($jsFiles, $jqueryFiles);
        } else {
            $jsFiles[] = $jqueryFiles;
        }

        $jqueryUiFiles = Media::getJqueryUIPath('ui.datepicker', 'base', true);

        $jsFiles = array_merge($jsFiles, $jqueryUiFiles['js']);
        $cssFiles = $jqueryUiFiles['css'];

        $jsFiles[] = $this->_path.'js/trackingfront.js';

        $jsTplVar = [
            'product_ids' => implode(', ', $productsArray),
            'referrer_id' => $referrer->id,
            'token'       => $this->context->cookie->tracking_passwd,
            'display_tab' => implode('", "', array_keys($displayTab)),
        ];

        $this->smarty->assign(
            [
                'js'         => $jsFiles,
                'css'        => $cssFiles,
                'js_tpl_var' => $jsTplVar,
            ]
        );

        return $this->display(__FILE__, 'views/templates/front/account.tpl');
    }

    /**
     * Method is called when user is successfully authenticated
     *
     * @param int $id - referrer id
     * @param string $password - referrer password, hashed
     * @throws PrestaShopException
     */
    public function authenticated($id, $password)
    {
        $this->context->cookie->tracking_id = $id;
        $this->context->cookie->tracking_passwd = $password;
        Tools::redirect(Tools::getShopDomain(true, false) . __PS_BASE_URI__ . 'modules/trackingfront/stats.php');
    }
}
