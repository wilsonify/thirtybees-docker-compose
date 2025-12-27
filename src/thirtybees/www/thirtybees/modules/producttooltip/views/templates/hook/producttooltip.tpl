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

<script type="text/javascript">
    $(document).ready(function () {ldelim}
        {if isset($nb_people)}
        $.growl({ldelim}title: '', message: '{if $nb_people == 1}{l s='%d person is currently watching this product.' sprintf=$nb_people mod='producttooltip' js=1}{else}{l s='%d people are currently watching this product.' sprintf=$nb_people mod='producttooltip' js=1}{/if}'{rdelim});
        {/if}

        {if isset($date_last_order)}
        $.growl({ldelim}title: '', message: '{l s='Last time this product was bought: ' mod='producttooltip' js=1} {dateFormat date=$date_last_order full=1}'{rdelim});
        {/if}

        {if isset($date_last_cart)}
        $.growl({ldelim}title: '', message: '{l s='Last time this product was added to a cart: ' mod='producttooltip' js=1} {dateFormat date=$date_last_cart full=1}'{rdelim});
        {/if}

        {rdelim});
</script>
