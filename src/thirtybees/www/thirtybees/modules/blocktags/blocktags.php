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

class BlockTags extends Module
{
    const CONFIG_MAX_TAGS_DISPLAYED = 'BLOCKTAGS_NBR';
    const CONFIG_MAX_LEVEL = 'BLOCKTAGS_MAX_LEVEL';
    const CONFIG_RANDOMIZE = 'BLOCKTAGS_RANDOMIZE';

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'blocktags';
        $this->tab = 'front_office_features';
        $this->version = '2.0.3';
        $this->author = 'thirty bees';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Block Tags');
        $this->description = $this->l('Adds a block containing your product tags.');
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
        $success = (parent::install()
            && $this->registerHook('header')
            && $this->registerHook('leftColumn')
            && $this->registerHook('addproduct')
            && $this->registerHook('updateproduct')
            && $this->registerHook('deleteproduct')
        );

        $this->_clearCache('*');

        return $success;
    }

    /**
     * @param string $template
     * @param string|null $cache_id
     * @param string|null $compile_id
     *
     * @return void
     * @throws PrestaShopException
     */
    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        parent::_clearCache('blocktags.tpl');
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        $this->_clearCache('*');

        Configuration::deleteByName(static::CONFIG_MAX_TAGS_DISPLAYED);
        Configuration::deleteByName(static::CONFIG_MAX_LEVEL);
        Configuration::deleteByName(static::CONFIG_RANDOMIZE);

        return parent::uninstall();
    }

    /**
     * @param array $params
     *
     * @return void
     * @throws PrestaShopException
     */
    public function hookAddProduct($params)
    {
        $this->_clearCache('*');
    }

    /**
     * @param array $params
     *
     * @return void
     * @throws PrestaShopException
     */
    public function hookUpdateProduct($params)
    {
        $this->_clearCache('*');
    }

    /**
     * @param array $params
     *
     * @return void
     * @throws PrestaShopException
     */
    public function hookDeleteProduct($params)
    {
        $this->_clearCache('*');
    }

    /**
     * @return string
     *
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function getContent()
    {
        $output = '';
        $errors = [];
        if (Tools::isSubmit('submitBlockTags')) {
            $tagsNbr = (int)Tools::getValue(static::CONFIG_MAX_TAGS_DISPLAYED);
            if ($tagsNbr <= 0) {
                $errors[] = $this->l('Invalid number.');
            }

            $tagsLevels = (int)Tools::getValue(static::CONFIG_MAX_LEVEL);
            if ($tagsLevels <= 0) {
                $errors[] = $this->l('Invalid value for "Tag levels". Choose a positive integer number.');
            }

            $randomize = (int)Tools::getValue(static::CONFIG_RANDOMIZE);

            if (count($errors)) {
                $output = $this->displayError(implode('<br />', $errors));
            } else {
                Configuration::updateValue(static::CONFIG_MAX_TAGS_DISPLAYED, $tagsNbr);
                Configuration::updateValue(static::CONFIG_MAX_LEVEL, $tagsLevels);
                Configuration::updateValue(static::CONFIG_RANDOMIZE, $randomize);
                $output = $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output . $this->renderForm();
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
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Displayed tags'),
                        'name' => static::CONFIG_MAX_TAGS_DISPLAYED,
                        'class' => 'fixed-width-xs',
                        'desc' => $this->l('Set the number of tags you would like to see displayed in this block. (default: 10)')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Tag levels'),
                        'name' => static::CONFIG_MAX_LEVEL,
                        'class' => 'fixed-width-xs',
                        'desc' => $this->l('Set the number of different tag levels you would like to use. (default: 3)')
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Random display'),
                        'name' => static::CONFIG_RANDOMIZE,
                        'class' => 'fixed-width-xs',
                        'desc' => $this->l('If enabled, displays tags randomly. By default, random display is disabled and the most used tags are displayed first.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            ]
                        ]
                    ]
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
        $helper->submit_action = 'submitBlockTags';
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
            static::CONFIG_MAX_TAGS_DISPLAYED => Tools::getValue(static::CONFIG_MAX_TAGS_DISPLAYED, $this->getMaxTagsDisplayed()),
            static::CONFIG_MAX_LEVEL => Tools::getValue(static::CONFIG_MAX_LEVEL, $this->getMaxLevel()),
            static::CONFIG_RANDOMIZE => Tools::getValue(static::CONFIG_RANDOMIZE, (int)$this->shouldRandomize()),
        ];
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
    public function hookRightColumn($params)
    {
        return $this->hookLeftColumn($params);
    }

    /**
     * Returns module content for left column
     *
     * @param array $params Parameters
     *
     * @return string Content
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookLeftColumn($params)
    {
        if (!$this->isCached('blocktags.tpl', $this->getCacheId('blocktags'))) {
            $languageId = (int)Context::getContext()->language->id;

            $tags = [];
            $max = -1;
            $min = -1;
            foreach (Tag::getMainTags($languageId, $this->getMaxTagsDisplayed()) as $tag) {
                if ($tag['name']) {
                    $tags[] = $tag;
                    if ($tag['times'] > $max) {
                        $max = $tag['times'];
                    }
                    if ($tag['times'] < $min || $min == -1) {
                        $min = $tag['times'];
                    }
                }
            }

            if ($min == $max) {
                $coef = $max;
            } else {
                $coef = ($this->getMaxLevel() - 1) / ($max - $min);
            }

            if (!count($tags)) {
                return false;
            }
            if ($this->shouldRandomize()) {
                shuffle($tags);
            }
            foreach ($tags as &$tag) {
                $tag['class'] = 'tag_level' . (int)(($tag['times'] - $min) * $coef + 1);
            }
            $this->smarty->assign('tags', $tags);
        }
        return $this->display(__FILE__, 'blocktags.tpl', $this->getCacheId('blocktags'));
    }

    /**
     * @param array $params
     *
     * @return void
     */
    public function hookHeader($params)
    {
        $this->context->controller->addCSS(($this->_path) . 'blocktags.css', 'all');
    }

    /**
     * @return int
     *
     * @throws PrestaShopException
     */
    protected function getMaxTagsDisplayed()
    {
        $value = (int)Configuration::get(static::CONFIG_MAX_TAGS_DISPLAYED);
        return ($value > 0) ? $value : 10;
    }

    /**
     * @return int
     *
     * @throws PrestaShopException
     */
    protected function getMaxLevel()
    {
        $value = (int)Configuration::get(static::CONFIG_MAX_LEVEL);
        return ($value > 0) ? $value : 3;
    }

    /**
     * @return bool
     *
     * @throws PrestaShopException
     */
    protected function shouldRandomize()
    {
        return (bool)Configuration::get(static::CONFIG_RANDOMIZE);
    }
}
