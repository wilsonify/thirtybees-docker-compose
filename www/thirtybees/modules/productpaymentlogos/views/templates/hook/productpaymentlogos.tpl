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

<!-- Productpaymentlogos module -->
<div id="product_payment_logos">
	<div class="box-security">
    <h5 class="product-heading-h5">{$banner_title|escape:'htmlall':'UTF-8'}</h5>
  	{if $banner_link != ''}<a href="{$banner_link|escape:'htmlall':'UTF-8'}" title="{$banner_title|escape:'htmlall':'UTF-8'}">{/if}
		<img src="{$module_dir}{$banner_img|escape:'htmlall':'UTF-8'}" alt="{$banner_title|escape:'htmlall':'UTF-8'}" class="img-responsive" />
	{if $banner_link != ''}</a>{/if}
    </div>
</div>
<!-- /Productpaymentlogos module -->
