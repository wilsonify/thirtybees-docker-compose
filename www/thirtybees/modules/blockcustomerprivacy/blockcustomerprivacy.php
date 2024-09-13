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

class Blockcustomerprivacy extends Module
{
    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'blockcustomerprivacy';
        $this->tab = 'front_office_features';
        $this->version = '3.0.3';
        $this->author = 'thirty bees';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Block Customer Privacy');
        $this->description = $this->l('Adds a block displaying a message about a customer\'s privacy data.');
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
        $return = (parent::install()
            && $this->registerHook('createAccountForm')
            && $this->registerHook('displayCustomerIdentityForm')
            && $this->registerHook('actionBeforeSubmitAccount'));

        include 'fixtures.php'; // get Fixture array
        $languages = Language::getLanguages();
        $conf_keys = array('CUSTPRIV_MSG_AUTH', 'CUSTPRIV_MSG_IDENTITY');
        foreach ($conf_keys as $conf_key) {
            foreach ($languages as $lang) {
                if (isset($fixtures[$conf_key][$lang['language_code']])) {
                    Configuration::updateValue($conf_key, array(
                        $lang['id_lang'] => $fixtures[$conf_key][$lang['language_code']]
                    ));
                } else {
                    Configuration::updateValue($conf_key, array(
                        $lang['id_lang'] => 'The personal data you provide is used to answer queries, process orders or allow access to specific information. You have the right to modify and delete all the personal information found in the "My Account" page.'
                    ));
                }
            }
        }

        return $return;
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        return ($this->unregisterHook('createAccountForm')
            && $this->unregisterHook('displayCustomerIdentityForm')
            && $this->unregisterHook('actionBeforeSubmitAccount')
            && parent::uninstall());
    }

    /**
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function getContent()
    {
        $html = '';
        if (Tools::isSubmit('submitCustPrivMess')) {
            $message_trads = array('auth' => array(), 'identity' => array());
            foreach ($_POST as $key => $value) {
                if (preg_match('/CUSTPRIV_MSG_AUTH_/i', $key)) {
                    $id_lang = preg_split('/CUSTPRIV_MSG_AUTH_/i', $key);
                    $message_trads['auth'][(int)$id_lang[1]] = $value;
                } elseif (preg_match('/CUSTPRIV_MSG_IDENTITY_/i', $key)) {
                    $id_lang = preg_split('/CUSTPRIV_MSG_IDENTITY_/i', $key);
                    $message_trads['identity'][(int)$id_lang[1]] = $value;
                }
            }
            Configuration::updateValue('CUSTPRIV_MSG_AUTH', $message_trads['auth'], true);
            Configuration::updateValue('CUSTPRIV_MSG_IDENTITY', $message_trads['identity'], true);

            Configuration::updateValue('CUSTPRIV_AUTH_PAGE', (int)Tools::getValue('CUSTPRIV_AUTH_PAGE'));
            Configuration::updateValue('CUSTPRIV_IDENTITY_PAGE', (int)Tools::getValue('CUSTPRIV_IDENTITY_PAGE'));

            $this->_clearCache('blockcustomerprivacy.tpl');
            $this->_clearCache('blockcustomerprivacy-simple.tpl');
            $html .= $this->displayConfirmation($this->l('Configuration updated'));
        }

        $html .= $this->renderForm();

        return $html;
    }

    /**
     * @param $switch_key
     * @param $msg_key
     *
     * @return bool
     * @throws PrestaShopException
     */
    public function checkConfig($switch_key, $msg_key)
    {
        if (!$this->active) {
            return false;
        }

        if (!Configuration::get($switch_key)) {
            return false;
        }

        $message = Configuration::get($msg_key, $this->context->language->id);
        if (empty($message)) {
            return false;
        }

        return true;
    }

    /**
     * @param $params
     *
     * @return void
     * @throws PrestaShopException
     */
    public function hookActionBeforeSubmitAccount($params)
    {
        if (!$this->checkConfig('CUSTPRIV_AUTH_PAGE', 'CUSTPRIV_MSG_AUTH')) {
            return;
        }

        if (!Tools::getValue('customer_privacy')) {
            $this->context->controller->errors[] = $this->l('If you agree to the terms in the Customer Data Privacy message, please click the check box below.');
        }
    }

    /**
     * @param $params
     *
     * @return string|void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookCreateAccountForm($params)
    {
        if (!$this->checkConfig('CUSTPRIV_AUTH_PAGE', 'CUSTPRIV_MSG_AUTH')) {
            return;
        }

        if (!$this->isCached('blockcustomerprivacy.tpl', $this->getCacheId())) {
            $this->smarty->assign('privacy_message', Configuration::get('CUSTPRIV_MSG_AUTH', $this->context->language->id));
        }

        return $this->display(__FILE__, 'blockcustomerprivacy.tpl', $this->getCacheId());
    }

    /**
     * @param $params
     *
     * @return string|void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayCustomerIdentityForm($params)
    {
        if (!$this->checkConfig('CUSTPRIV_IDENTITY_PAGE', 'CUSTPRIV_MSG_IDENTITY')) {
            return;
        }

        if (!$this->isCached('blockcustomerprivacy-simple.tpl', $this->getCacheId())) {
            $this->smarty->assign(array(
                'privacy_message' => Configuration::get('CUSTPRIV_MSG_IDENTITY', $this->context->language->id),
                'privacy_id' => "blockcustomerprivacy-simple",
            ));
        }

        return $this->display(__FILE__, 'blockcustomerprivacy-simple.tpl', $this->getCacheId());
    }

    /**
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display on account creation form'),
                        'name' => 'CUSTPRIV_AUTH_PAGE',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'textarea',
                        'lang' => true,
                        'autoload_rte' => true,
                        'label' => $this->l('Customer data privacy message for account creation form:'),
                        'name' => 'CUSTPRIV_MSG_AUTH',
                        'desc' => $this->l('The customer data privacy message will be displayed in the account creation form.') . '<br>' . $this->l('Tip: If the customer privacy message is too long to be written directly in the form, you can add a link to one of your pages. This can easily be created via the "CMS" page under the "Preferences" menu.')
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display in customer area'),
                        'name' => 'CUSTPRIV_IDENTITY_PAGE',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'textarea',
                        'lang' => true,
                        'autoload_rte' => true,
                        'label' => $this->l('Customer data privacy message for customer area:'),
                        'name' => 'CUSTPRIV_MSG_IDENTITY',
                        'desc' => $this->l('The customer data privacy message will be displayed in the "Personal information" page, in the customer area.') . '<br>' . $this->l('Tip: If the customer privacy message is too long to be written directly on the page, you can add a link to one of your other pages. This can easily be created via the "CMS" page under the "Preferences" menu.')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCustPrivMess';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    /**
     * @return array
     * @throws PrestaShopException
     */
    public function getConfigFieldsValues()
    {
        $return = array();

        $return['CUSTPRIV_AUTH_PAGE'] = (int)Configuration::get('CUSTPRIV_AUTH_PAGE');
        $return['CUSTPRIV_IDENTITY_PAGE'] = (int)Configuration::get('CUSTPRIV_IDENTITY_PAGE');

        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $return['CUSTPRIV_MSG_AUTH'][(int)$lang['id_lang']] = Tools::getValue('CUSTPRIV_MSG_AUTH_' . (int)$lang['id_lang'], Configuration::get('CUSTPRIV_MSG_AUTH', (int)$lang['id_lang']));
        }
        foreach ($languages as $lang) {
            $return['CUSTPRIV_MSG_IDENTITY'][(int)$lang['id_lang']] = Tools::getValue('CUSTPRIV_MSG_IDENTITY_' . (int)$lang['id_lang'], Configuration::get('CUSTPRIV_MSG_IDENTITY', (int)$lang['id_lang']));
        }

        return $return;
    }
}
