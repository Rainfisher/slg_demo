<?php

class Module_Battle extends Module_Abstract
{

    public final function index ($city_id)
    {
        return $this->get('city_id', $city_id);
    }

    function get ($key, $city_id)
    {
        $rs = $this->model->find(array(
            $key => $city_id
        ), 'finish_at asc');
        if (! $rs) {
            $rs = array();
        }
        return $rs;
    }

    function getAll ($city_id)
    {
        $sql = "select * from battle where city_id = {$city_id} OR target_city_id = {$city_id}";
        return $this->model->querySql($sql);
    }

    public final function create ($city_id, $target_city_id, $army = array())
    {
        if (is_string($army)) {
            $rs = explode(',', $army);
            $army = array();
            for ($i = 0; $i < count($rs); $i += 2) {
                $army[$rs[$i]] = $rs[$i + 1];
            }
        }
        $city = $this->city->get($city_id);
        $target_city = $this->city->get($target_city_id);
        if ($city['user_id'] == $target_city['user_id']) {
            $this->error('battle.cant.attack.yourself');
        }
        if (count($army) == 0) {
            $this->error('battle.army.cant.empty');
        }
        $speed = 0;
        foreach ($army as $type => $num) {
            $rs = $this->army->show($city_id, $type);
            if ($rs['num'] < $num) {
                $this->error('battle.array.not.enouth');
            }
            $conf = Vender_Conf::$army[$type];
            if ($speed == 0 || $speed > $conf['speed']) {
                $speed = $conf['speed'];
            }
        }
        $distance = (int) sqrt(pow($city['x'] - $target_city['x'], 2) + pow($city['y'] - $target_city['y'], 2));
        $cost_time = (int) ($distance / $speed * 3600);
        $battle_id = $this->model->create(array(
            'city_id' => $city_id,
            'target_city_id' => $target_city_id,
            'cost_time' => $cost_time,
            'finish_at' => $this->time() + $cost_time,
        ));;
        foreach ($army as $type => $num) {
            $this->battle_army->create($battle_id, $city_id, $type, $num);
            $this->army->sub($city_id, $type, $num);
        }
        return true;
    }

    function delById ($battle_id)
    {
        $this->model->delById(array(
            'id' => $battle_id,
        ));
        $this->battle_army->delById($battle_id);
    }

    function update ($battle_id, $status, $finish_at)
    {
        $this->model->setById(array(
            'id' => $battle_id,
        ), array(
            'status' => $status,
            'finish_at' => $finish_at,
        ));
    }

}
