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

{if !empty($beesblogPopularPostsPosts)}
    <section>
        <div id="beesblog_column" class="col-xs-12 col-sm-12">
            <h2 class="title_block">
                <a href="{$beesblogPopularPostsBlogUrl|escape:'htmlall':'UTF-8'}"
                   title="{l s='Popular posts' mod='beesblogpopularposts'}">{l s='Popular posts' mod='beesblogpopularposts'}</a>
            </h2>
            {foreach $beesblogPopularPostsPosts as $post}
                <article>
                    <div class="col-xs-12 col-sm-4 col-md-3">
                        <div class="beesblogpopularposts-content">
                            <h3 class="post-name">
                                <a class="beesblogpopularposts-title" href="{$post->link|escape:'htmlall':'UTF-8'}"
                                   title="{$post->title|escape:'htmlall':'UTF-8'}">
                                    {$post->title|truncate:'20'|escape:'htmlall':'UTF-8'}
                                </a>
                            </h3>
                            <span>
                                <i class="icon icon-calendar"></i> {$post->published|date_format}
                                <i class="icon icon-eye"></i> {$post->viewed|intval}
                            </span>
                        </div>
                    </div>
                </article>
            {/foreach}
        </div>
    </section>
{/if}
