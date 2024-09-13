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

if ( ! defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class MenuTopLinks
 */
class MenuTopLinks
{
    /**
     * @param int $idLang
     * @param int|null $idLinksmenutop
     * @param int $idShop
     *
     * @return array
     *
     * @throws PrestaShopException
     */
    public static function gets($idLang, $idLinksmenutop, $idShop)
    {
        $sql = 'SELECT l.id_linksmenutop, l.new_window, s.name, ll.link, ll.label
				FROM '._DB_PREFIX_.'linksmenutop l
				LEFT JOIN '._DB_PREFIX_.'linksmenutop_lang ll ON (l.id_linksmenutop = ll.id_linksmenutop AND ll.id_lang = '.(int) $idLang.' AND ll.id_shop='.(int) $idShop.')
				LEFT JOIN '._DB_PREFIX_.'shop s ON l.id_shop = s.id_shop
				WHERE 1 '.((!is_null($idLinksmenutop)) ? ' AND l.id_linksmenutop = "'.(int) $idLinksmenutop.'"' : '').'
				AND l.id_shop IN (0, '.(int) $idShop.')';

        $ret = Db::getInstance()->executeS($sql);
        return is_array($ret) ? $ret : [];
    }

    /**
     * @param int|null $idLinksmenutop
     * @param int $idLang
     * @param int $idShop
     *
     * @return array
     *
     * @throws PrestaShopException
     */
    public static function get($idLinksmenutop, $idLang, $idShop)
    {
        return self::gets($idLang, $idLinksmenutop, $idShop);
    }

    /**
     * @param int $idLinksmenutop
     * @param int $idShop
     *
     * @return array
     * @throws PrestaShopException
     */
    public static function getLinkLang($idLinksmenutop, $idShop)
    {
        $ret = Db::getInstance()->executeS('
			SELECT l.id_linksmenutop, l.new_window, ll.link, ll.label, ll.id_lang
			FROM '._DB_PREFIX_.'linksmenutop l
			LEFT JOIN '._DB_PREFIX_.'linksmenutop_lang ll ON (l.id_linksmenutop = ll.id_linksmenutop AND ll.id_shop='.(int) $idShop.')
			WHERE 1
			'.((!is_null($idLinksmenutop)) ? ' AND l.id_linksmenutop = "'.(int) $idLinksmenutop.'"' : '').'
			AND l.id_shop IN (0, '.(int) $idShop.')
		');

        $link = [];
        $label = [];
        $newWindow = false;

        foreach ($ret as $line) {
            $link[$line['id_lang']] = $line['link'];
            $label[$line['id_lang']] = $line['label'];
            $newWindow = (bool) $line['new_window'];
        }

        return [
            'link' => $link,
            'label' => $label,
            'new_window' => $newWindow
        ];
    }

    /**
     * @param array $link
     * @param string $label
     * @param int $newWindow
     * @param int $idShop
     *
     * @return bool
     *
     * @throws PrestaShopException
     */
    public static function add($link, $label, $newWindow, $idShop)
    {
        if (!is_array($label)) {
            return false;
        }
        if (!is_array($link)) {
            return false;
        }

        Db::getInstance()->insert(
            'linksmenutop',
            [
                'new_window' => (int) $newWindow,
                'id_shop'    => (int) $idShop,
            ]
        );
        $idLinksmenutop = Db::getInstance()->Insert_ID();

        $result = true;

        foreach ($label as $idLang => $labelValue) {
            $result = Db::getInstance()->insert(
                'linksmenutop_lang',
                [
                    'id_linksmenutop' => (int) $idLinksmenutop,
                    'id_lang'         => (int) $idLang,
                    'id_shop'         => (int) $idShop,
                    'label'           => pSQL($labelValue),
                    'link'            => pSQL($link[$idLang]),
                ]
            ) && $result;
        }

        return $result;
    }

    /**
     * @param array $link
     * @param array $labels
     * @param int $newWindow
     * @param int $idShop
     * @param int $idLink
     *
     * @return bool
     *
     * @throws PrestaShopException
     */
    public static function update($link, $labels, $newWindow, $idShop, $idLink)
    {
        if (!is_array($labels)) {
            return false;
        }
        if (!is_array($link)) {
            return false;
        }

        Db::getInstance()->update(
            'linksmenutop',
            [
                'new_window' => (int) $newWindow,
                'id_shop'    => (int) $idShop,
            ],
            'id_linksmenutop = '.(int) $idLink
        );

        $ret = true;
        foreach ($labels as $idLang => $label) {
            $ret = Db::getInstance()->update(
                'linksmenutop_lang',
                [
                    'id_shop' => (int) $idShop,
                    'label'   => pSQL($label),
                    'link'    => pSQL($link[$idLang]),
                ],
                'id_linksmenutop = '.(int) $idLink.' AND id_lang = '.(int) $idLang
            ) && $ret;
        }

        return $ret;
    }

    /**
     * @param int $idLinksmenutop
     * @param int $idShop
     *
     * @return bool
     *
     * @throws PrestaShopException
     */
    public static function remove($idLinksmenutop, $idShop)
    {
        $result = Db::getInstance()->delete('linksmenutop', 'id_linksmenutop = '.(int) $idLinksmenutop.' AND id_shop = '.(int) $idShop);
        $result = Db::getInstance()->delete('linksmenutop_lang', 'id_linksmenutop = '.(int) $idLinksmenutop) && $result;

        return $result;
    }
}
