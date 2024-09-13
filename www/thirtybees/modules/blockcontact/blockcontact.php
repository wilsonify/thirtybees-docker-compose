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

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

class Blockcontact extends Module
{
    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'blockcontact';
        $this->author = 'thirty bees';
        $this->tab = 'front_office_features';
        $this->version = '2.0.3';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Block Contact');
        $this->description = $this->l('Allows you to add additional information about your store\'s customer service.');
        $this->tb_versions_compliancy = '> 1.0.0';
        $this->tb_min_version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => '1.6.99.99'];
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {
        return parent::install()
            && Configuration::updateValue('BLOCKCONTACT_TELNUMBER', '')
            && Configuration::updateValue('BLOCKCONTACT_EMAIL', '')
            && $this->registerHook('displayNav')
            && $this->registerHook('displayHeader');
    }

    /**
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        // Delete configuration
        return Configuration::deleteByName('BLOCKCONTACT_TELNUMBER') && Configuration::deleteByName('BLOCKCONTACT_EMAIL') && parent::uninstall();
    }

    /**
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function getContent()
    {
        $html = '';
        // If we try to update the settings
        if (Tools::isSubmit('submitModule')) {
            Configuration::updateValue('BLOCKCONTACT_TELNUMBER', Tools::getValue('blockcontact_telnumber'));
            Configuration::updateValue('BLOCKCONTACT_EMAIL', Tools::getValue('blockcontact_email'));
            $this->_clearCache('blockcontact.tpl');
            $this->_clearCache('nav.tpl');
            $html .= $this->displayConfirmation($this->l('Configuration updated'));
        }

        $html .= $this->renderForm();

        return $html;
    }

    /**
     * @param array $params
     *
     * @return void
     */
    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS(($this->_path) . 'blockcontact.css', 'all');
    }

    /**
     * @param array $params
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayRightColumn($params)
    {
        $tpl = 'blockcontact';
        if (isset($params['blockcontact_tpl']) && $params['blockcontact_tpl']) {
            $tpl = $params['blockcontact_tpl'];
        }
        if (!$this->isCached($tpl . '.tpl', $this->getCacheId())) {
            $this->context->smarty->assign([
                'telnumber' => Configuration::get('BLOCKCONTACT_TELNUMBER'),
                'email' => Configuration::get('BLOCKCONTACT_EMAIL')
            ]);
        }
        return $this->display(__FILE__, $tpl . '.tpl', $this->getCacheId());
    }

    /**
     * @param array $params
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayLeftColumn($params)
    {
        return $this->hookDisplayRightColumn($params);
    }

    /**
     * @param $params
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayNav($params)
    {
        $params['blockcontact_tpl'] = 'nav';
        return $this->hookDisplayRightColumn($params);
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
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ],
                'description' => $this->l('This block displays in the header your phone number (‘Call us now’), and a link to the ‘Contact us’ page.') . '<br/><br/>' .
                    $this->l('To edit the email addresses for the ‘Contact us’ page: you should go to the ‘Contacts’ page under the ‘Customer’ menu.') . '<br/>' .
                    $this->l('To edit the contact details in the footer: you should go to the ‘Contact Information Block’ module.'),
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Telephone number'),
                        'name' => 'blockcontact_telnumber',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Email'),
                        'name' => 'blockcontact_email',
                        'desc' => $this->l('Enter here your customer service contact details.'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ]
            ],
        ];

        /** @var AdminController $controller */
        $controller = $this->context->controller;

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $controller->getLanguages(),
            'id_language' => $this->context->language->id
        ];

        return $helper->generateForm([$fields_form]);
    }

    /**
     * @return array
     *
     * @throws PrestaShopException
     */
    public function getConfigFieldsValues()
    {
        return [
            'blockcontact_telnumber' => Tools::getValue('blockcontact_telnumber', Configuration::get('BLOCKCONTACT_TELNUMBER')),
            'blockcontact_email' => Tools::getValue('blockcontact_email', Configuration::get('BLOCKCONTACT_EMAIL')),
        ];
    }
}
