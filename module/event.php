<?php

class Module_Event extends Module_Abstract
{

    static $events;

    function transaction (&$city, $end_time = null)
    {
        if (! $end_time) {
            $end_time = $this->time();
        } else {
            $end_time -= 1;
        }
        if (! is_array($city)) {
            $city = $this->model_city->getById(array(
                'id' => $city
            ));
        }
        $old = $city;
        $this->eventCombine($city, $end_time);
        while (count(self::$events)) {
            $event = array_shift(self::$events);
            if ($event['time'] > $end_time) {
                break;
            }
            $this->dealFoodCost($city, $event);
            $method_name = 'deal' . $event['type'];
            if (method_exists($this, $method_name)) {
                call_user_func(array(
                    $this,
                    $method_name
                ), $city, $event);
            }
            $city['updated_at'] = $event['time'];
        }
        if ($this->cityDiff($city, $old)) {
            $this->model_city->setById(array(
                'id' => $city['id'],
            ), array(
                'food' => $city['food'],
                'population' => $city['population'],
                'gold' => $city['gold'],
                'updated_at' => $end_time,
            ));
        }
        return $city;
    }

    function cityDiff ($new, $old)
    {
        static $keys = array('food', 'population', 'gold');
        foreach ($keys as $key) {
            if ($new[$key] != $old[$key]) {
                return true;
            }
        }
        return false;
    }

    function eventCombine ($city, $end_time)
    {
        $taxs = $this->getTaxTime($city['updated_at'], $end_time);
        $trains = $this->getTrainTime($city, $end_time);
        $battles = $this->getBattleTime($city, $end_time);
        self::$events = array_merge($taxs, $trains, $battles, array(array(
            'type' => 'now',
            'time' => $end_time,
        )));
        $this->eventsSort();
    }

    function eventsSort ()
    {
        $cmp_function = function ($a, $b) {
            $v1 = $a['time'];
            $v2 = $b['time'];
            if ($v1 == $v2) {
                return 0;
            }
            return ($v1 > $v2) ? 1 : -1;
        };
        usort(self::$events, $cmp_function);
    }

    function getTaxTime ($start_time, $end_time)
    {
        $times = 0;
        $time_gap = $end_time - $start_time;
        if ($time_gap > Vender_Conf::TAX_TIME) {
            $times += (int) $time_gap / Vender_Conf::TAX_TIME;
        }
        $start_date = getdate($start_time);
        $end_data   = getdate($end_time);
        if ($start_date['hours'] != $end_data['hours'] && $start_date['minutes'] > $end_data['minutes']) {
            $times += 1;
        }
        $list = array();
        if ($times > 0) {
            $t = strtotime(date("Y-m-d {$end_data['hours']}:00:00"));
            for ($i = 0; $i < $times; $i++) {
                array_unshift($list, array(
                    'type' => 'Tax',
                    'time' => $t - Vender_Conf::TAX_TIME * $i,
                ));
            }
        }
        return $list;
    }

    function dealTax (&$city, $event)
    {
        $gold = (int) ($city['population'] * $city['tax'] / 100);
        $city['gold'] += $gold;
        $population = (int) ($city['population'] * Vender_Conf::TAX_POPULATION_RATE);
        if ($population == 0) {
            $population = 1;
        }
        if ($city['population'] > $city['tax'] * Vender_Conf::TAX_POPULATION_LIMIT) {
            $population = -$population;
        }
	$city['population'] += $population;
        if ($city['food'] <= 0) {
            $armys = $this->army->get($city['id']);
            foreach ($armys as $k => $army) {
                $loss = $army['num'] * Vender_Conf::ARMY_LOSS_RATE;
                if ($loss < 1) {
                    $loss = 1;
                }
                $this->army->sub($city['id'], $army['type'], $loss);
            }
        }
    }

    function getTrainTime ($city)
    {
        $list = array();
        $trains = $this->train->get($city['id']);
        if (count($trains) > 0) {
            foreach ($trains as $train) {
                $list[] = array(
                    'type' => 'train',
                    'time' => $train['created_at'] + $train['cost_time'],
                    'info' => $train,
                );
            }
        }
        return $list;
    }

    function dealTrain ($city, $event)
    {
        $this->army->add($city['id'], $event['info']['type'], $event['info']['num']);
        $this->model_train->delById(array(
            'id' => $event['info']['id']
        ));
    }

    function getFoodCost ($city)
    {
        if ($city['capital']) {
            $cost = Vender_Conf::CITY_FOOD_CAPITAL;
        } else {
            $cost = Vender_Conf::CITY_FOOD_NORMAL;
        }
        $armys = $this->army->get($city['id']);
        $battle_armys = $this->battle_army->getByCityId($city['id']);
        foreach ($armys as $army) {
            $conf = Vender_Conf::$army[$army['type']];
            $cost -= $conf['food'];
        }
        foreach ($battle_armys as $army) {
            $conf = Vender_Conf::$army[$army['army_type']];
            $cost -= $conf['food'];
        }
        return $cost;
    }

    function dealFoodCost (&$city, $event)
    {
        $time_gap = $event['time'] - $city['updated_at'];
        $cost = $this->getFoodCost($city);
        $food = $cost * $time_gap / 3600;
        $city['food'] += $food;
        if ($city['food'] < 0) {
            $city['food'] = 0;
        }
    }

    function getBattleTime ($city)
    {
        $battles = array_merge(
            $this->battle->get('city_id', $city['id']),
            $this->battle->get('target_city_id', $city['id'])
        );
        $list = array();
        foreach ($battles as $battle) {
            $data = array(
                'time' => $battle['finish_at'],
                'info' => $battle,
            );
            if ($battle['status'] == 0) {
                $data['type'] = 'battle';
            } else if ($battle['city_id'] == $city['id']){
                $data['type'] = 'return';
            }
            $list[] = $data;
        }
        return $list;
    }

    function dealBattle ($city, $event)
    {
        $battle_id = $event['info']['id'];
        if ($event['info']['city_id'] == $city['id']) {
            $target_city = $this->transaction($event['info']['target_city_id'], $event['time']);
        }
        if ($event['info']['target_city_id'] == $city['id']) {
            $target_city = $this->transaction($event['info']['city_id'], $event['time']);
        }
        $attack_army_src = $this->battle_army->index($battle_id);
        foreach ($attack_army_src as $army) {
            $attack_army[] = array(
                'type' => $army['army_type'],
                'num' => $army['num'],
            );
        }
        $defense_army_src = $this->army->get($event['info']['target_city_id']);
        $defense_army = array();
        foreach ($defense_army_src as $army) {
            $defense_army[] = array(
                'type' => $army['type'],
                'num' => $army['num'],
            );
        }
        $attack_army_diff = $this->blackBox($attack_army);
        foreach ($attack_army_diff as $army) {
            $this->battle_army->update($battle_id, $army['type'], $army['num']);
        }
        $defense_army_diff = $this->blackBox($defense_army);
        foreach ($defense_army_diff as $army) {
            $this->army->update($event['info']['target_city_id'], $army['type'], $army['num']);
        }
        $event['info']['status'] = 1;
        $event['info']['finish_at'] += $event['info']['cost_time'];
        $this->battle->update($battle_id, $event['info']['status'], $event['info']['finish_at']);
        self::$events[] = array(
            'type' => 'return',
            'time' => $event['info']['finish_at'],
            'info' => $event['info'],
        );
        $this->eventsSort();
    }

    function blackBox($army)
    {
        foreach ($army as $k => $one) {
            $army[$k]['num'] -= mt_rand(0, $one['num']);
        }
        return $army;
    }

    function dealReturn ($city, $event)
    {
        $armys = $this->battle_army->index($event['info']['id']);
        foreach ($armys as $army) {
            $this->army->add($city['id'], $army['army_type'], $army['num']);
        }
        $this->battle->delById($event['info']['id']);
    }

}
