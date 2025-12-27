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

{if isset($categoryProducts) && count($categoryProducts) > 0 && $categoryProducts !== false}
<div class="clearfix blockproductscategory">
	<h2 class="productscategory_h2">
		{if $categoryProducts|@count == 1}
			{l s='%s other product in the same category:' sprintf=[$categoryProducts|@count] mod='productscategory'}
		{else}
			{l s='%s other products in the same category:' sprintf=[$categoryProducts|@count] mod='productscategory'}
		{/if}
	</h2>
	<div id="{if count($categoryProducts) > 5}productscategory{else}productscategory_noscroll{/if}">
	{if count($categoryProducts) > 5}<a id="productscategory_scroll_left" title="{l s='Previous' mod='productscategory'}" href="javascript:{ldelim}{rdelim}">{l s='Previous' mod='productscategory'}</a>{/if}
	<div id="productscategory_list">
		<ul {if count($categoryProducts) > 5}style="width: {math equation="width * nbImages" width=107 nbImages=$categoryProducts|@count}px"{/if}>
			{foreach from=$categoryProducts item='categoryProduct' name=categoryProduct}
			<li {if count($categoryProducts) < 6}style="width:60px"{/if}>
				<a href="{$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category, $categoryProduct.ean13)}" class="lnk_img" title="{$categoryProduct.name|htmlspecialchars}"><img src="{$link->getImageLink($categoryProduct.link_rewrite, $categoryProduct.id_image, 'medium_default')|escape:'html'}" alt="{$categoryProduct.name|htmlspecialchars}" /></a>
				<p class="product_name">
					<a href="{$link->getProductLink($categoryProduct.id_product, $categoryProduct.link_rewrite, $categoryProduct.category, $categoryProduct.ean13)|escape:'html'}" title="{$categoryProduct.name|htmlspecialchars}">{$categoryProduct.name|truncate:14:'...'|escape:'html':'UTF-8'}</a>
				</p>
				{if $ProdDisplayPrice AND $categoryProduct.show_price == 1 AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
				<p class="price_display">
					<span class="price">{convertPrice price=$categoryProduct.displayed_price}</span>
				</p>
				{else}
				<br />
				{/if}
			</li>
			{/foreach}
		</ul>
	</div>
	{if count($categoryProducts) > 5}<a id="productscategory_scroll_right" title="{l s='Next' mod='productscategory'}" href="javascript:{ldelim}{rdelim}">{l s='Next' mod='productscategory'}</a>{/if}
	</div>
	<script type="text/javascript">
		$('#productscategory_list').trigger('goto', [{$middlePosition}-3]);
	</script>
</div>
{/if}
