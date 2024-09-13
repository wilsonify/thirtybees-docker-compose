{**
 * Copyright (C) 2019 thirty bees
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
 * @copyright 2019 thirty bees
 * @license   Academic Free License (AFL 3.0)
 *}

<div id="db-changes">
    <div class="running">
        {l s='Please wait, looking for database changes' mod='coreupdater'}
        <i class="icon icon-spin icon-spinner"></i>
    </div>

    <div class="alert alert-success no-changes">
        {l s='Congratulation, no database differences were found' mod='coreupdater'}
    </div>

    <div class="changes">
        <h2>{l s='List of differences' mod='coreupdater'}</h2>
        <table class="table">
            <thead>
                <th>{l s='Severity' mod='coreupdater'}</th>
                <th>{l s='Flags' mod='coreupdater'}</th>
                <th>{l s='Description' mod='coreupdater'}</th>
                <th>{l s='Actions' mod='coreupdater'}</th>
            </thead>
            <tbody id="db-changes-list">
                {* populated dynamically by javascript *}
            </tbody>
        </table>
        <div class="loading-spinner"><i class="icon icon-spin icon-spinner"></i></div>
    </div>

</div>

<script type="application/javascript">
    $(document).ready(function() {
        window.coreUpdater = initializeCoreUpdater({});
        coreUpdater.checkDatabase();
        $("#refresh-btn").click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            coreUpdater.checkDatabase();
        });
    })
</script>
