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

class Blockcontactinfos extends Module
{
    /**
     * @var string[]
     */
    const CONTACT_FIELDS = [
        'BLOCKCONTACTINFOS_COMPANY',
        'BLOCKCONTACTINFOS_ADDRESS',
        'BLOCKCONTACTINFOS_PHONE',
        'BLOCKCONTACTINFOS_EMAIL',
    ];

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'blockcontactinfos';
        $this->author = 'thirty bees';
        $this->tab = 'front_office_features';
        $this->version = '2.0.4';
        $this->need_instance = false;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Block Contact Infos');
        $this->description = $this->l('This module will allow you to display your e-store\'s contact information in a customizable block.');
        $this->tb_versions_compliancy = '> 1.0.0';
        $this->tb_min_version = '1.0.0';
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => '1.6.99.99'];
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {
        Configuration::updateValue('BLOCKCONTACTINFOS_COMPANY', Configuration::get('PS_SHOP_NAME'));
        Configuration::updateValue('BLOCKCONTACTINFOS_ADDRESS', trim(preg_replace('/ +/', ' ', Configuration::get('PS_SHOP_ADDR1') . ' ' . Configuration::get('PS_SHOP_ADDR2') . "\n" . Configuration::get('PS_SHOP_CODE') . ' ' . Configuration::get('PS_SHOP_CITY') . "\n" . Country::getNameById(Configuration::get('PS_LANG_DEFAULT'), Configuration::get('PS_SHOP_COUNTRY_ID')))));
        Configuration::updateValue('BLOCKCONTACTINFOS_PHONE', Configuration::get('PS_SHOP_PHONE'));
        Configuration::updateValue('BLOCKCONTACTINFOS_EMAIL', Configuration::get('PS_SHOP_EMAIL'));
        $this->_clearCache('blockcontactinfos.tpl');
        return (parent::install() && $this->registerHook('header') && $this->registerHook('footer'));
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        foreach (static::CONTACT_FIELDS as $field) {
            Configuration::deleteByName($field);
        }
        return (parent::uninstall());
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function getContent()
    {
        $html = '';
        if (Tools::isSubmit('submitModule')) {
            foreach (static::CONTACT_FIELDS as $field) {
                Configuration::updateValue($field, Tools::getValue($field), true);
            }
            $this->_clearCache('blockcontactinfos.tpl');
            $html = $this->displayConfirmation($this->l('Configuration updated'));
        }

        return $html . $this->renderForm();
    }

    /**
     * @return void
     */
    public function hookHeader()
    {
        $this->context->controller->addCSS(($this->_path) . 'blockcontactinfos.css', 'all');
    }

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     * @throws SmartyException
     */
    public function hookFooter($params)
    {
        if (!$this->isCached('blockcontactinfos.tpl', $this->getCacheId())) {
            foreach (static::CONTACT_FIELDS as $field) {
                $this->smarty->assign(strtolower($field), Configuration::get($field));
            }
        }
        return $this->display(__FILE__, 'blockcontactinfos.tpl', $this->getCacheId());
    }

    /**
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
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Company name'),
                        'name' => 'BLOCKCONTACTINFOS_COMPANY',
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Address'),
                        'name' => 'BLOCKCONTACTINFOS_ADDRESS',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Phone number'),
                        'name' => 'BLOCKCONTACTINFOS_PHONE',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Email'),
                        'name' => 'BLOCKCONTACTINFOS_EMAIL',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save')
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
            'fields_value' => [],
            'languages' => $controller->getLanguages(),
            'id_language' => $this->context->language->id
        ];
        foreach (static::CONTACT_FIELDS as $field) {
            $helper->tpl_vars['fields_value'][$field] = Tools::getValue($field, Configuration::get($field));
        }
        return $helper->generateForm([$fields_form]);
    }
}
