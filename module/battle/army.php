<?php

class Module_Battle_Army extends Module_Abstract
{
    function create ($battle_id, $city_id, $army_type, $num)
    {
        $this->model->create(array(
            'battle_id' => $battle_id,
            'city_id' => $city_id,
            'army_type' => $army_type,
            'num' => $num
        ));
    }

    function update ($battle_id, $army_type, $num)
    {
        $this->model->setById(array(
            'battle_id' => $battle_id,
            'army_type' => $army_type,
        ), array(
            'num' => $num
        ));
    }

    function index ($battle_id)
    {
        return $this->model->find(array(
            'battle_id' => $battle_id,
            array(
                'num > :num',
                'num' => 0,
            )
        ));
    }

    function getByCityId ($city_id)
    {
        return $this->model->find(array(
            'city_id' => $city_id,
            array(
                'num > :num',
                'num' => 0,
            )
        ));
    }

    function delById ($battle_id)
    {
        $this->model->delById(array(
            'battle_id' => $battle_id
        ));
    }

}
