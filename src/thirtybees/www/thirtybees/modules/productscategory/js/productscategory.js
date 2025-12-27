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

function pc_serialScrollFixLock(event, targeted, scrolled, items, position)
{
	var leftArrow = position == 0;
	var rightArrow = position + 5 >= $('#productscategory_list li:visible').length;

	$('a#productscategory_scroll_left').css('cursor', leftArrow ? 'default' : 'pointer').fadeTo(0, leftArrow ? 0 : 1);
	$('a#productscategory_scroll_right').css('cursor', rightArrow ? 'default' : 'pointer').fadeTo(0, rightArrow ? 0 : 1).css('display', rightArrow ? 'none' : 'block');

	return true;
}

$(document).ready(function()
{
	$('#productscategory_list').serialScroll({
		items: 'li',
		prev: 'a#productscategory_scroll_left',
		next: 'a#productscategory_scroll_right',
		axis: 'x',
		offset: 0,
		stop: true,
		onBefore: pc_serialScrollFixLock,
		duration: 300,
		step: 1,
		lazy: true,
		lock: false,
		force: false,
		cycle: false });
	$('#productscategory_list').trigger( 'goto', 0);
});
