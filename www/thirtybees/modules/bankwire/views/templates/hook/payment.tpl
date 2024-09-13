{**
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
 *}

<p class="payment_module">
  <a href="{$link->getModuleLink('bankwire', 'payment')|escape:'htmlall':'UTF-8'}" title="{l s='Pay by bank wire' mod='bankwire'}">
    <img src="{$module_dir|escape:'htmlall':'UTF-8'}bankwire.jpg" alt="{l s='Pay by bank wire' mod='bankwire'}" width="86" height="49"/>
    {l s='Pay by bank wire' mod='bankwire'}&nbsp;<span>{l s='(order processing will be longer)' mod='bankwire'}</span>
  </a>
</p>
