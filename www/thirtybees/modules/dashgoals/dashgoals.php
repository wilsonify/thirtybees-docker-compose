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

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class DashGoals
 */
class DashGoals extends Module
{
    const TYPES = ['traffic', 'conversion', 'avg_cart_value'];
    const COLORS = ['#9E5BA1', '#00A89C', '#3AC4ED', '#F99031'];
    const MORE_COLOR = ['#803E84', '#008E7E', '#20B2E7', '#F66E1B'];
    const LESS_COLOR = ['#BC77BE', '#00C2BB', '#51D6F2', '#FBB244'];
    const ZONE_REAL = 'real';
    const ZONE_MORE = 'more';
    const ZONE_LESS = 'less';

    /**
     * DashGoals constructor.
     *
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'dashgoals';
        $this->tab = 'dashboard';
        $this->version = '2.0.8';
        $this->author = 'thirty bees';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Dashboard Goals');
        $this->description = $this->l('Adds a block with your store\'s forecast.');
        $this->tb_versions_compliancy = '> 1.0.0';
        $this->tb_min_version = '1.0.0';
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {
        Configuration::updateValue('PS_DASHGOALS_CURRENT_YEAR', date('Y'));
        for ($month = '01'; $month <= 12; $month = sprintf('%02d', $month + 1)) {
            $key = strtoupper('dashgoals_traffic_' . $month . '_' . date('Y'));
            if (!ConfigurationKPI::get($key)) {
                ConfigurationKPI::updateValue($key, 600);
            }
            $key = strtoupper('dashgoals_conversion_' . $month . '_' . date('Y'));
            if (!ConfigurationKPI::get($key)) {
                ConfigurationKPI::updateValue($key, 2);
            }
            $key = strtoupper('dashgoals_avg_cart_value_' . $month . '_' . date('Y'));
            if (!ConfigurationKPI::get($key)) {
                ConfigurationKPI::updateValue($key, 80);
            }
        }

        // Prepare tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminDashgoals';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Dashgoals';
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;

        if (!parent::install()) {
            return false;
        }

        $tab->add();
        $this->registerHook('actionAdminControllerSetMedia');
        $this->registerHook('dashboardZoneTwo');
        $this->registerHook('dashboardData');
        $this->registerHook('displayBackOfficeHeader');

        return true;
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        $idTab = (int)Tab::getIdFromClassName('AdminDashgoals');
        if ($idTab) {
            $tab = new Tab($idTab);
            $tab->delete();
        }

        return parent::uninstall();
    }

    /**
     * Action admin controller set media
     */
    public function hookActionAdminControllerSetMedia()
    {
        if (get_class($this->context->controller) == 'AdminDashboardController') {
            $this->context->controller->addJquery();
            $this->context->controller->addJs($this->_path . 'views/js/' . $this->name . '.js');
        }
    }

    /**
     * @return void
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (get_class($this->context->controller) === 'AdminDashboardController') {
            $this->context->controller->addJs($this->_path . 'views/js/' . $this->name . '.js');
        }
    }

    /**
     * @param array $params
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDashboardZoneTwo($params)
    {
        $year = Configuration::get('PS_DASHGOALS_CURRENT_YEAR');
        $months = $this->setMonths($year);

        $this->context->smarty->assign(
            [
                'colors' => static::COLORS,
                'currency' => $this->context->currency,
                'goals_year' => $year,
                'goals_months' => $months,
                'dashgoals_ajax_link' => $this->context->link->getAdminLink('AdminDashgoals'),
            ]
        );

        return $this->display(__FILE__, 'dashboard_zone_two.tpl');
    }

    /**
     * @param int $year
     *
     * @return array
     * @throws PrestaShopException
     */
    public function setMonths($year)
    {
        $months = [];
        for ($i = '01'; $i <= 12; $i = sprintf('%02d', $i + 1)) {
            $months[$i . '_' . $year] = ['label' => $this->getMonthLabel($i), 'values' => []];
        }

        foreach (static::TYPES as $type) {
            foreach ($months as $month => &$monthRow) {
                $key = 'dashgoals_' . $type . '_' . $month;
                if (Tools::isSubmit('submitDashGoals')) {
                    ConfigurationKPI::updateValue(strtoupper($key), (float)Tools::getValue($key));
                }
                $monthRow['values'][$type] = ConfigurationKPI::get(strtoupper($key));
            }
        }

        return $months;
    }

    /**
     * @param array $params
     *
     * @return array[]
     * @throws PrestaShopException
     */
    public function hookDashboardData($params)
    {
        $year = ((isset($params['extra']) && $params['extra'] > 1970 && $params['extra'] < 2999) ? $params['extra'] : Configuration::get('PS_DASHGOALS_CURRENT_YEAR'));

        return ['data_chart' => ['dash_goals_chart1' => $this->getChartData($year)]];
    }

    /**
     * @param int $year
     *
     * @return array
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getChartData($year)
    {
        // There are stream types (different charts) and for each types there are 3 available zones (one color for the goal, one if you over perform and one if you under perfom)
        $streamTypes = [
            ['type' => 'traffic', 'title' => $this->l('Traffic'), 'unit_text' => $this->l('visits')],
            ['type' => 'conversion', 'title' => $this->l('Conversion'), 'unit_text' => ''],
            ['type' => 'avg_cart_value', 'title' => $this->l('Average cart value'), 'unit_text' => ''],
            ['type' => 'sales', 'title' => $this->l('Sales'), 'unit_text' => ''],
        ];
        $streamZones = [
            ['zone' => static::ZONE_REAL, 'text' => ''],
            ['zone' => static::ZONE_MORE, 'text' => $this->l('Goal exceeded')],
            ['zone' => static::ZONE_LESS, 'text' => $this->l('Goal not reached')],
        ];

        // We initialize all the streams types for all the zones
        $streams = [];
        $averageGoals = [];

        foreach ($streamTypes as $key => $streamType) {
            $streams[$streamType['type']] = [];
            foreach ($streamZones as $streamZone) {
                $streams[$streamType['type']][$streamZone['zone']] = [
                    'key' => $streamType['type'] . '_' . $streamZone['zone'],
                    'title' => $streamType['title'],
                    'unit_text' => $streamType['unit_text'],
                    'zone_text' => $streamZone['text'],
                    'color' => $this->getZoneColor($streamZone['zone'], $key),
                    'values' => [],
                    'disabled' => (isset($streamType['type']) && $streamType['type'] == 'sales') ? false : true,
                ];
            }

            if (isset($streamType['type'])) {
                $averageGoals[$streamType['type']] = 0;
            }
        }

        if (Configuration::get('PS_DASHBOARD_SIMULATION')) {
            $visits = $orders = $sales = [];
            $from = strtotime(date('Y-01-01 00:00:00'));
            $to = strtotime(date('Y-12-31 00:00:00'));
            for ($date = $from; $date <= $to; $date = strtotime('+1 day', $date)) {
                $visits[$date] = round(rand(2000, 5000));
                $orders[$date] = round(rand(40, 100));
                $sales[$date] = round(rand(3000, 9000), 2);
            }

            // We need to calculate the average value of each goals for the year, this will be the base rate for "100%"
            for ($i = '01'; $i <= 12; $i = sprintf('%02d', $i + 1)) {
                $averageGoals['traffic'] += $this->fakeConfigurationKPI_get('DASHGOALS_TRAFFIC_' . $i . '_' . $year);
                $averageGoals['conversion'] += $this->fakeConfigurationKPI_get('DASHGOALS_CONVERSION_' . $i . '_' . $year);
                $averageGoals['avg_cart_value'] += $this->fakeConfigurationKPI_get('DASHGOALS_AVG_CART_VALUE_' . $i . '_' . $year);
            }
            foreach ($averageGoals as &$averageGoal) {
                $averageGoal /= 12;
            }
            $averageGoals['sales'] = $averageGoals['traffic'] * $averageGoals['conversion'] / 100 * $averageGoals['avg_cart_value'];

            // Now we can calculate the value for every months
            for ($i = '01'; $i <= 12; $i = sprintf('%02d', $i + 1)) {
                $timestamp = strtotime($year . '-' . $i . '-01');

                $month_goal = $this->fakeConfigurationKPI_get('DASHGOALS_TRAFFIC_' . $i . '_' . $year);
                $value = (isset($visits[$timestamp]) ? $visits[$timestamp] : 0);
                $stream_values = $this->getValuesFromGoals($averageGoals['traffic'], $month_goal, $value, $this->getMonthLabel($i));
                $goal_diff = $value - $month_goal;
                $stream_values[static::ZONE_REAL]['traffic'] = $value;
                $stream_values[static::ZONE_REAL]['goal'] = $month_goal;
                if ($value > 0) {
                    $stream_values[static::ZONE_REAL]['goal_diff'] = round(($goal_diff * 100) / ($month_goal > 0 ? $month_goal : 1), 2);
                }

                $stream_values[static::ZONE_LESS]['traffic'] = $value;
                $stream_values[static::ZONE_MORE]['traffic'] = $value;

                if ($value > 0 && $value < $month_goal) {
                    $stream_values[static::ZONE_LESS]['goal_diff'] = $goal_diff;
                } elseif ($value > 0) {
                    $stream_values[static::ZONE_MORE]['goal_diff'] = $goal_diff;
                }

                if ($value == 0) {
                    $streams['traffic'][static::ZONE_LESS]['zone_text'] = $this->l('Goal set:');
                    $stream_values[static::ZONE_LESS]['goal'] = $month_goal;
                }

                foreach ($streamZones as $streamZone) {
                    $streams['traffic'][$streamZone['zone']]['values'][] = $stream_values[$streamZone['zone']];
                }

                $month_goal = $this->fakeConfigurationKPI_get('DASHGOALS_CONVERSION_' . $i . '_' . $year);
                $value = 100 * ((isset($visits[$timestamp]) && $visits[$timestamp] && isset($orders[$timestamp]) && $orders[$timestamp]) ? ($orders[$timestamp] / $visits[$timestamp]) : 0);
                $stream_values = $this->getValuesFromGoals($averageGoals['conversion'], $month_goal, $value, $this->getMonthLabel($i));
                $goal_diff = $value - $month_goal;
                $stream_values[static::ZONE_REAL]['conversion'] = round($value, 2);
                $stream_values[static::ZONE_REAL]['goal'] = round($month_goal, 2);
                if ($value > 0) {
                    $stream_values[static::ZONE_REAL]['goal_diff'] = round(($goal_diff * 100) / ($month_goal > 0 ? $month_goal : 1), 2);
                }

                $stream_values[static::ZONE_LESS]['conversion'] = $value;
                $stream_values[static::ZONE_MORE]['conversion'] = $value;

                if ($value > 0 && $value < $month_goal) {
                    $stream_values[static::ZONE_LESS]['goal_diff'] = round(($goal_diff * 100) / ($month_goal > 0 ? $month_goal : 1), 2);
                } elseif ($value > 0) {
                    $stream_values[static::ZONE_MORE]['goal_diff'] = round(($goal_diff * 100) / ($month_goal > 0 ? $month_goal : 1), 2);
                }

                if ($value == 0) {
                    $streams['conversion'][static::ZONE_LESS]['zone_text'] = $this->l('Goal set:');
                    $stream_values[static::ZONE_LESS]['goal'] = $month_goal;
                }

                foreach ($streamZones as $streamZone) {
                    $streams['conversion'][$streamZone['zone']]['values'][] = $stream_values[$streamZone['zone']];
                }

                $month_goal = $this->fakeConfigurationKPI_get('DASHGOALS_AVG_CART_VALUE_' . $i . '_' . $year);
                $value = ((isset($orders[$timestamp]) && $orders[$timestamp] && isset($sales[$timestamp]) && $sales[$timestamp]) ? ($sales[$timestamp] / $orders[$timestamp]) : 0);
                $stream_values = $this->getValuesFromGoals($averageGoals['avg_cart_value'], $month_goal, $value, $this->getMonthLabel($i));
                $goal_diff = $value - $month_goal;
                $stream_values[static::ZONE_REAL]['avg_cart_value'] = $value;
                $stream_values[static::ZONE_REAL]['goal'] = $month_goal;
                if ($value > 0) {
                    $stream_values[static::ZONE_REAL]['goal_diff'] = round(($goal_diff * 100) / ($month_goal > 0 ? $month_goal : 1), 2);
                }

                $stream_values[static::ZONE_LESS]['avg_cart_value'] = $value;
                $stream_values[static::ZONE_MORE]['avg_cart_value'] = $value;

                if ($value > 0 && $value < $month_goal) {
                    $stream_values[static::ZONE_LESS]['goal_diff'] = $goal_diff;
                } elseif ($value > 0) {
                    $stream_values[static::ZONE_MORE]['goal_diff'] = $goal_diff;
                }

                if ($value == 0) {
                    $streams['avg_cart_value'][static::ZONE_LESS]['zone_text'] = $this->l('Goal set:');
                    $stream_values[static::ZONE_LESS]['goal'] = $month_goal;
                }

                foreach ($streamZones as $streamZone) {
                    $streams['avg_cart_value'][$streamZone['zone']]['values'][] = $stream_values[$streamZone['zone']];
                }

                $month_goal = $this->fakeConfigurationKPI_get('DASHGOALS_TRAFFIC_' . $i . '_' . $year) * $this->fakeConfigurationKPI_get('DASHGOALS_CONVERSION_' . $i . '_' . $year) / 100 * $this->fakeConfigurationKPI_get('DASHGOALS_AVG_CART_VALUE_' . $i . '_' . $year);
                $value = (isset($sales[$timestamp]) ? $sales[$timestamp] : 0);
                $stream_values = $this->getValuesFromGoals($averageGoals['sales'], $month_goal, $value, $this->getMonthLabel($i));
                $goal_diff = $value - $month_goal;
                $stream_values[static::ZONE_REAL]['sales'] = $value;
                $stream_values[static::ZONE_REAL]['goal'] = $month_goal;

                if ($value > 0) {
                    $stream_values[static::ZONE_REAL]['goal_diff'] = round(($goal_diff * 100) / ($month_goal > 0 ? $month_goal : 1), 2);
                }

                $stream_values[static::ZONE_LESS]['sales'] = $value;
                $stream_values[static::ZONE_MORE]['sales'] = $value;

                if ($value > 0 && $value < $month_goal) {
                    $stream_values[static::ZONE_LESS]['goal_diff'] = $goal_diff;
                } elseif ($value > 0) {
                    $stream_values[static::ZONE_MORE]['goal_diff'] = $goal_diff;
                }

                if ($value == 0) {
                    $streams['sales'][static::ZONE_LESS]['zone_text'] = $this->l('Goal set:');
                    $stream_values[static::ZONE_LESS]['goal'] = $month_goal;
                }

                foreach ($streamZones as $streamZone) {
                    $streams['sales'][$streamZone['zone']]['values'][] = $stream_values[$streamZone['zone']];
                }
            }
        } else {
            // Retrieve gross data from AdminStatsController
            $visits = AdminStatsController::getVisits(false, $year . date('-01-01'), $year . date('-12-31'), 'month');
            $orders = AdminStatsController::getOrders($year . date('-01-01'), $year . date('-12-31'), 'month');
            $sales = AdminStatsController::getTotalSales($year . date('-01-01'), $year . date('-12-31'), 'month');

            // We need to calculate the average value of each goals for the year, this will be the base rate for "100%"
            for ($i = '01'; $i <= 12; $i = sprintf('%02d', $i + 1)) {
                $averageGoals['traffic'] += ConfigurationKPI::get('DASHGOALS_TRAFFIC_' . $i . '_' . $year);
                $averageGoals['conversion'] += ConfigurationKPI::get('DASHGOALS_CONVERSION_' . $i . '_' . $year) / 100;
                $averageGoals['avg_cart_value'] += ConfigurationKPI::get('DASHGOALS_AVG_CART_VALUE_' . $i . '_' . $year);
            }
            foreach ($averageGoals as &$averageGoal) {
                $averageGoal /= 12;
            }
            $averageGoals['sales'] = $averageGoals['traffic'] * $averageGoals['conversion'] * $averageGoals['avg_cart_value'];

            // Now we can calculate the value for every months
            for ($i = '01'; $i <= 12; $i = sprintf('%02d', $i + 1)) {
                $timestamp = strtotime($year . '-' . $i . '-01');

                $month_goal = ConfigurationKPI::get('DASHGOALS_TRAFFIC_' . $i . '_' . $year);
                $value = (isset($visits[$timestamp]) ? $visits[$timestamp] : 0);
                $stream_values = $this->getValuesFromGoals($averageGoals['traffic'], $month_goal, $value, $this->getMonthLabel($i));
                $goal_diff = $value - $month_goal;
                $stream_values[static::ZONE_REAL]['traffic'] = $value;
                $stream_values[static::ZONE_REAL]['goal'] = $month_goal;
                if ($value > 0) {
                    $stream_values[static::ZONE_REAL]['goal_diff'] = round(($goal_diff * 100) / ($month_goal > 0 ? $month_goal : 1), 2);
                }

                $stream_values[static::ZONE_LESS]['traffic'] = $value;
                $stream_values[static::ZONE_MORE]['traffic'] = $value;

                if ($value > 0 && $value < $month_goal) {
                    $stream_values[static::ZONE_LESS]['goal_diff'] = $goal_diff;
                } elseif ($value > 0) {
                    $stream_values[static::ZONE_MORE]['goal_diff'] = $goal_diff;
                }

                if ($value == 0) {
                    $streams['traffic'][static::ZONE_LESS]['zone_text'] = $this->l('Goal set:');
                    $stream_values[static::ZONE_LESS]['goal'] = $month_goal;
                }

                foreach ($streamZones as $streamZone) {
                    $streams['traffic'][$streamZone['zone']]['values'][] = $stream_values[$streamZone['zone']];
                }

                $month_goal = ConfigurationKPI::get('DASHGOALS_CONVERSION_' . $i . '_' . $year);
                $value = 100 * ((isset($visits[$timestamp]) && $visits[$timestamp] && isset($orders[$timestamp]) && $orders[$timestamp]) ? ($orders[$timestamp] / $visits[$timestamp]) : 0);
                $stream_values = $this->getValuesFromGoals($averageGoals['conversion'] * 100, $month_goal, $value, $this->getMonthLabel($i));
                $goal_diff = $value - $month_goal;
                $stream_values[static::ZONE_REAL]['conversion'] = round($value, 2);
                $stream_values[static::ZONE_REAL]['goal'] = round($month_goal, 2);
                if ($value > 0) {
                    $stream_values[static::ZONE_REAL]['goal_diff'] = round(($goal_diff * 100) / ($month_goal > 0 ? $month_goal : 1), 2);
                }

                $stream_values[static::ZONE_LESS]['conversion'] = $value;
                $stream_values[static::ZONE_MORE]['conversion'] = $value;

                if ($value > 0 && $value < $month_goal) {
                    $stream_values[static::ZONE_LESS]['goal_diff'] = round(($goal_diff * 100) / ($month_goal > 0 ? $month_goal : 1), 2);
                } elseif ($value > 0) {
                    $stream_values[static::ZONE_MORE]['goal_diff'] = round(($goal_diff * 100) / ($month_goal > 0 ? $month_goal : 1), 2);
                }

                if ($value == 0) {
                    $streams['conversion'][static::ZONE_LESS]['zone_text'] = $this->l('Goal set:');
                    $stream_values[static::ZONE_LESS]['goal'] = $month_goal;
                }

                foreach ($streamZones as $streamZone) {
                    $streams['conversion'][$streamZone['zone']]['values'][] = $stream_values[$streamZone['zone']];
                }

                $month_goal = ConfigurationKPI::get('DASHGOALS_AVG_CART_VALUE_' . $i . '_' . $year);
                $value = ((isset($orders[$timestamp]) && $orders[$timestamp] && isset($sales[$timestamp]) && $sales[$timestamp]) ? ($sales[$timestamp] / $orders[$timestamp]) : 0);
                $stream_values = $this->getValuesFromGoals($averageGoals['avg_cart_value'], $month_goal, $value, $this->getMonthLabel($i));
                $goal_diff = $value - $month_goal;
                $stream_values[static::ZONE_REAL]['avg_cart_value'] = $value;
                $stream_values[static::ZONE_REAL]['goal'] = $month_goal;
                if ($value > 0) {
                    $stream_values[static::ZONE_REAL]['goal_diff'] = round(($goal_diff * 100) / ($month_goal > 0 ? $month_goal : 1), 2);
                }

                $stream_values[static::ZONE_LESS]['avg_cart_value'] = $value;
                $stream_values[static::ZONE_MORE]['avg_cart_value'] = $value;

                if ($value > 0 && $value < $month_goal) {
                    $stream_values[static::ZONE_LESS]['goal_diff'] = $goal_diff;
                } elseif ($value > 0) {
                    $stream_values[static::ZONE_MORE]['goal_diff'] = $goal_diff;
                }

                if ($value == 0) {
                    $streams['avg_cart_value'][static::ZONE_LESS]['zone_text'] = $this->l('Goal set:');
                    $stream_values[static::ZONE_LESS]['goal'] = $month_goal;
                }

                foreach ($streamZones as $streamZone) {
                    $streams['avg_cart_value'][$streamZone['zone']]['values'][] = $stream_values[$streamZone['zone']];
                }

                $month_goal = ConfigurationKPI::get('DASHGOALS_TRAFFIC_' . $i . '_' . $year) * ConfigurationKPI::get('DASHGOALS_CONVERSION_' . $i . '_' . $year) / 100 * ConfigurationKPI::get('DASHGOALS_AVG_CART_VALUE_' . $i . '_' . $year);
                $value = (isset($sales[$timestamp]) && $sales[$timestamp]) ? $sales[$timestamp] : 0;
                $stream_values = $this->getValuesFromGoals($averageGoals['sales'], $month_goal, isset($sales[$timestamp]) ? $sales[$timestamp] : 0, $this->getMonthLabel($i));
                $goal_diff = $value - $month_goal;
                $stream_values[static::ZONE_REAL]['sales'] = $value;
                $stream_values[static::ZONE_REAL]['goal'] = $month_goal;

                if ($value > 0) {
                    $stream_values[static::ZONE_REAL]['goal_diff'] = round(($goal_diff * 100) / ($month_goal > 0 ? $month_goal : 1), 2);
                }

                $stream_values[static::ZONE_LESS]['sales'] = $value;
                $stream_values[static::ZONE_MORE]['sales'] = $value;

                if ($value > 0 && $value < $month_goal) {
                    $stream_values[static::ZONE_LESS]['goal_diff'] = $goal_diff;
                } elseif ($value > 0) {
                    $stream_values[static::ZONE_MORE]['goal_diff'] = $goal_diff;
                }

                if ($value == 0) {
                    $streams['sales'][static::ZONE_LESS]['zone_text'] = $this->l('Goal set:');
                    $stream_values[static::ZONE_LESS]['goal'] = $month_goal;
                }

                foreach ($streamZones as $streamZone) {
                    $streams['sales'][$streamZone['zone']]['values'][] = $stream_values[$streamZone['zone']];
                }
            }
        }

        // Merge all the streams before sending
        $all_streams = [];
        foreach ($streamTypes as $streamType) {
            foreach ($streamZones as $streamZone) {
                $all_streams[] = $streams[$streamType['type']][$streamZone['zone']];
            }
        }

        return ['chart_type' => 'bar_chart_goals', 'data' => $all_streams];
    }

    /**
     * @param string $key
     *
     * @return float|int|void
     */
    protected function fakeConfigurationKPI_get($key)
    {
        $start = [
            'TRAFFIC' => 3000,
            'CONVERSION' => 2,
            'AVG_CART_VALUE' => 90,
        ];

        if (preg_match('/^DASHGOALS_([A-Z_]+)_([0-9]{2})/', $key, $matches)) {
            if ($matches[1] == 'TRAFFIC') {
                return $start[$matches[1]] * (1 + ($matches[2] - 1) / 10);
            } else {
                return $start[$matches[1]];
            }
        }
    }

    /**
     * @param float $average_goal
     * @param float $month_goal
     * @param float $value
     * @param string $label
     *
     * @return array[]
     */
    protected function getValuesFromGoals($average_goal, $month_goal, $value, $label)
    {
        // Initialize value for each zone
        $stream_values = [
            'real' => ['x' => $label, 'y' => 0],
            'less' => ['x' => $label, 'y' => 0],
            'more' => ['x' => $label, 'y' => 0],
        ];

        // Calculate the percentage of fullfilment of the goal
        $fullfilment = 0;
        if ($value && $month_goal) {
            $fullfilment = round($value / $month_goal, 2);
        }

        // Base rate is essential here : it determines the value of the goal compared to the "100%" of the chart legend
        $base_rate = 0;
        if ($average_goal && $month_goal) {
            $base_rate = $month_goal / $average_goal;
        }

        // Fullfilment of 1 means that we performed exactly anticipated
        if ($fullfilment == 1) {
            $stream_values[static::ZONE_REAL] = ['x' => $label, 'y' => round($base_rate, 2)];
        } // Fullfilment lower than 1 means that we UNDER performed
        elseif ($fullfilment < 1) {
            $stream_values[static::ZONE_REAL] = ['x' => $label, 'y' => round($fullfilment * $base_rate, 2)];
            $stream_values[static::ZONE_LESS] = ['x' => $label, 'y' => round($base_rate - ($fullfilment * $base_rate), 2)];
        } // Fullfilment greater than 1 means that we OVER performed
        elseif ($fullfilment > 1) {
            $stream_values[static::ZONE_REAL] = ['x' => $label, 'y' => round($base_rate, 2)];
            $stream_values[static::ZONE_MORE] = ['x' => $label, 'y' => round(($fullfilment * $base_rate) - $base_rate, 2)];
        }

        return $stream_values;
    }

    /**
     * @param string $month
     *
     * @return string
     */
    protected function getMonthLabel($month)
    {
        switch ($month) {
            case '01':
                return $this->l('January');
            case '02':
                return $this->l('February');
            case '03':
                return $this->l('March');
            case '04':
                return $this->l('April');
            case '05':
                return $this->l('May');
            case '06':
                return $this->l('June');
            case '07':
                return $this->l('July');
            case '08':
                return $this->l('August');
            case '09':
                return $this->l('September');
            case '10':
                return $this->l('October');
            case '11':
                return $this->l('November');
            case '12':
                return $this->l('December');
        }
        return $this->l('Unknown');
    }

    /**
     * @param string $zone
     * @param int $key
     *
     * @return string
     */
    protected function getZoneColor($zone, int $key)
    {
        switch ($zone) {
            case static::ZONE_MORE:
                return static::MORE_COLOR[$key];
            case static::ZONE_LESS:
                return static::LESS_COLOR[$key];
            case static::ZONE_REAL:
            default:
                return static::COLORS[$key];
        }
    }
}
