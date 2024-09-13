<?php
/**
 * Copyright (C) 2017-2023 thirty bees
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
 * @copyright 2017-2023 thirty bees
 * @copyright 2007-2016 PrestaShop SA
 * @license   Academic Free License (AFL 3.0)
 * PrestaShop is an internationally registered trademark of PrestaShop SA.
 */

if (!defined('_TB_VERSION_')) {
    exit;
}

class StatsCatalog extends StatsModule
{
    /**
     * @var string
     */
    protected $join = '';
    /**
     * @var string
     */
    protected $where = '';

    public function __construct()
    {
        parent::__construct();
        $this->type = static::TYPE_CUSTOM;

        $this->displayName = $this->l('Catalog statistics');
    }

    /**
     * @return array|false
     * @throws PrestaShopException
     */
    public function getQuery1()
    {
        $sql = 'SELECT COUNT(DISTINCT p.`id_product`) AS total, SUM(product_shop.`price`) / COUNT(product_shop.`price`) AS average_price, COUNT(DISTINCT i.`id_image`) AS images
				FROM `' . _DB_PREFIX_ . 'product` p
				' . Shop::addSqlAssociation('product', 'p') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON i.`id_product` = p.`id_product`
				' . $this->join . '
				WHERE product_shop.`active` = 1
					' . $this->where;

        return DB::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    /**
     * @return int
     * @throws PrestaShopException
     */
    public function getTotalPageViewed()
    {
        if ($this->utils->trackingPageViews()) {
            return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT SUM(pv.`counter`)
            FROM `' . _DB_PREFIX_ . 'product` p
            ' . Shop::addSqlAssociation('product', 'p') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'page` pa ON p.`id_product` = pa.`id_object`
            LEFT JOIN `' . _DB_PREFIX_ . 'page_type` pt ON (pt.`id_page_type` = pa.`id_page_type` AND pt.`name` IN ("product.php", "product"))
            LEFT JOIN `' . _DB_PREFIX_ . 'page_viewed` pv ON pv.`id_page` = pa.`id_page`
            ' . $this->join . '
            WHERE product_shop.`active` = 1
            ' . $this->where);
        }
        return 0;
    }

    /**
     * @return int
     * @throws PrestaShopException
     */
    public function getTotalProductViewed()
    {
        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT COUNT(DISTINCT pa.`id_object`)
		FROM `' . _DB_PREFIX_ . 'page_viewed` pv
		LEFT JOIN `' . _DB_PREFIX_ . 'page` pa ON pv.`id_page` = pa.`id_page`
		LEFT JOIN `' . _DB_PREFIX_ . 'page_type` pt ON pt.`id_page_type` = pa.`id_page_type`
		LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = pa.`id_object`
		' . Shop::addSqlAssociation('product', 'p') . '
		' . $this->join . '
		WHERE pt.`name` IN ("product.php", "product")
		AND product_shop.`active` = 1
		' . $this->where);
    }

    /**
     * @return int
     * @throws PrestaShopException
     */
    public function getTotalBought()
    {
        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT SUM(od.`product_quantity`)
		FROM `' . _DB_PREFIX_ . 'orders` o
		LEFT JOIN `' . _DB_PREFIX_ . 'order_detail` od ON o.`id_order` = od.`id_order`
		LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
		' . $this->join . '
		WHERE o.valid = 1
		' . Shop::addSqlRestriction(false, 'o') . '
		' . $this->where);
    }

    /**
     * @param int $id_lang
     *
     * @return array
     * @throws PrestaShopException
     */
    public function getProductsNB($id_lang)
    {
        $sql = 'SELECT p.`id_product`
				FROM `' . _DB_PREFIX_ . 'orders` o
				LEFT JOIN `' . _DB_PREFIX_ . 'order_detail` od ON o.`id_order` = od.`id_order`
				LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
				' . Shop::addSqlAssociation('product', 'p') . '
				' . $this->join . '
				WHERE o.valid = 1
		            ' . Shop::addSqlRestriction(false, 'o') . '
					' . $this->where . '
					AND product_shop.`active` = 1
				GROUP BY p.`id_product`';
        $precalc = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $precalc2 = [];
        foreach ($precalc as $array) {
            $precalc2[] = (int)$array['id_product'];
        }

        $sql = 'SELECT p.id_product, pl.name, pl.link_rewrite
				FROM `' . _DB_PREFIX_ . 'product` p
				' . Shop::addSqlAssociation('product', 'p') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
					ON (pl.`id_product` = p.`id_product` AND pl.id_lang = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
				' . $this->join . '
				WHERE product_shop.`active` = 1
					' . (count($precalc2) ? 'AND p.`id_product` NOT IN (' . implode(',', $precalc2) . ')' : '') . '
					' . $this->where;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return [
            'total' => Db::getInstance(_PS_USE_SQL_SLAVE_)->NumRows(),
            'result' => $result
        ];
    }

    /**
     * @return string
     * @throws PrestaShopException
     */
    public function hookAdminStatsModules()
    {
        $product_token = Tools::getAdminToken('AdminProducts' . (int)Tab::getIdFromClassName('AdminProducts') . (int)$this->context->employee->id);
        $irow = 0;

        if ($id_category = (int)Tools::getValue('id_category')) {
            $this->join = ' LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON (cp.`id_product` = p.`id_product`)';
            $this->where = ' AND cp.`id_category` = ' . $id_category;
        }

        $result1 = $this->getQuery1();
        $total = $result1['total'];
        $average_price = $result1['average_price'];
        $total_pictures = $result1['images'];
        $average_pictures = $total ? $total_pictures / $total : 0;

        $never_bought = $this->getProductsNB($this->context->language->id);
        $total_nb = $never_bought['total'];
        $products_nb = $never_bought['result'];

        $total_bought = $this->getTotalBought();
        $average_purchase = $total ? ($total_bought / $total) : 0;

        $extra = '';
        $productsPageViewed = '';
        $productsNeverViewed = '';
        $avaragedViewed = '';
        $conversionRate ='';

        if ($this->utils->trackingPageViews()) {
            $total_page_viewed = $this->getTotalPageViewed();
            $average_viewed = $total ? ($total_page_viewed / $total) : 0;
            $conversion = number_format((float)($total_page_viewed ? ($total_bought / $total_page_viewed) : 0), 2, '.', '');
            if ($conversion_reverse = number_format((float)($total_bought ? ($total_page_viewed / $total_bought) : 0), 2, '.', '')) {
                $conversion .= sprintf($this->l('(1 purchase / %d visits)'), $conversion_reverse);
            }
            $total_nv = $total - $this->getTotalProductViewed();

            $productsPageViewed = '<li class="list-group-item">' . $this->returnLine($this->l('Product pages viewed:'), '<span class="badge">' . (int)$total_page_viewed) . '</span></li>';
            $productsNeverViewed = ' <li class="list-group-item">' . $this->returnLine($this->l('Products never viewed:'), '<span class="badge">' . (int)$total_nv . ' / ' . (int)$total) . '</span></li>';
            $avaragedViewed = '<li class="list-group-item">' . $this->returnLine($this->l('Average number of page visits:'), '<span class="badge">' . number_format((float)$average_viewed, 2, '.', '')) . '</span></li>';
            $conversionRate = '<li class="list-group-item">' . $this->returnLine($this->l('Conversion rate*:'), '<span class="badge">' . $conversion) . '</span></li>';
            $extra = '<p class="row row-margin-bottom"><b>*</b>&nbsp;' . $this->l('Defines the average conversion rate for the product page. It is possible to purchase a product without viewing the product page, so this rate can be greater than 1.') . ' </p>';
        }

        $html = '
		<script type="text/javascript">$(\'#calendar\').hide();</script>
			<div class="panel-heading">
				' . $this->displayName . '
			</div>
			<form action="#" method="post" id="categoriesForm" class="form-horizontal">
				<div class="row row-margin-bottom">
					<label class="control-label col-lg-3">
						' . $this->l('Choose a category') . '
					</label>
					<div class="col-lg-9">
						<select name="id_category" onchange="$(\'#categoriesForm\').submit();">
						' . $this->utils->getCategoryOptions($id_category). '
						</select>
					</div>
				</div>
			</form>
			<div class="row row-margin-bottom">
                <ul class="list-group">
                    <li class="list-group-item">' . $this->returnLine($this->l('Products available:'), '<span class="badge">' . (int)$total) . '</span></li>
                    <li class="list-group-item">' . $this->returnLine($this->l('Average price (base price):'), '<span class="badge">' . Tools::displayPrice($average_price, $this->context->currency)) . '</span></li>
                    '. $productsPageViewed . '
                    <li class="list-group-item">' . $this->returnLine($this->l('Products bought:'), '<span class="badge">' . (int)$total_bought) . '</span></li>
                    '. $avaragedViewed . '
                    <li class="list-group-item">' . $this->returnLine($this->l('Average number of purchases:'), '<span class="badge">' . number_format((float)$average_purchase, 2, '.', '')) . '</span></li>
                    <li class="list-group-item">' . $this->returnLine($this->l('Images available:'), '<span class="badge">' . (int)$total_pictures) . '</span></li>
                    <li class="list-group-item">' . $this->returnLine($this->l('Average number of images:'), '<span class="badge">' . number_format((float)$average_pictures, 2, '.', '')) . '</span></li>
                    '. $productsNeverViewed . '
                    <li class="list-group-item">' . $this->returnLine($this->l('Products never purchased:'), '<span class="badge">' . (int)$total_nb . ' / ' . (int)$total) . '</span></li>
                    '. $conversionRate . '
                </ul>'
                . $extra . '
            </div>';

        if ($products_nb) {
            $html .= '
				<div class="panel-heading">' . $this->l('Products never purchased') . '</div>
				<table class="table">
					<thead>
						<tr>
							<th><span class="title_box active">' . $this->l('ID') . '</span></th>
							<th><span class="title_box active">' . $this->l('Name') . '</span></th>
							<th><span class="title_box active">' . $this->l('Edit / View') . '</span></th>
						</tr>
					</thead>
					<tbody>';
            foreach (array_slice($products_nb, 0, 50) as $product) {
                $html .= '
					<tr' . ($irow++ % 2 ? ' class="alt_row"' : '') . '>
						<td>' . $product['id_product'] . '</td>
						<td>' . $product['name'] . '</td>
						<td class="left">
							<div class="btn-group btn-group-action">
								<a class="btn btn-default" href="' . Tools::safeOutput('index.php?tab=AdminProducts&id_product=' . $product['id_product'] . '&addproduct&token=' . $product_token) . '" target="_blank">
									<i class="icon-edit"></i> ' . $this->l('Edit') . '
								</a>
								<button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button">
									<span class="caret">&nbsp;</span>
								</button>
								<ul class="dropdown-menu">
									<li>
										<a href="' . Tools::safeOutput($this->context->link->getProductLink($product['id_product'], $product['link_rewrite'])) . '" target="_blank">
											<i class="icon-eye-open"></i> ' . $this->l('View') . '
										</a>
									</li>
								</ul>
							</div>
						</td>
					</tr>';
            }
            $html .= '
					</tbody>
				</table>';
        }

        return $html;
    }

    /**
     * @param string $label
     * @param string $data
     *
     * @return string
     */
    private function returnLine($label, $data)
    {
        return $label . $data;
    }
}
