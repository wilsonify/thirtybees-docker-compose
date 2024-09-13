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

/**
 * @since 1.5.0
 */
class BlocknewsletterVerificationModuleFrontController extends ModuleFrontController
{
    /**
     * @var Blocknewsletter
     */
    public $module;

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @throws PrestaShopException
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $this->message = $this->module->confirmEmail(Tools::getValue('token'));
    }

    /**
     * @throws PrestaShopException
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign('message', $this->message);
        $this->setTemplate('verification_execution.tpl');
    }
}
