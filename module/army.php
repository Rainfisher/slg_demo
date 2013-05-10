<?php

class Module_Army extends Module_Abstract
{

    function index ($city_id)
    {
        return $this->get($city_id);
    }

    function show ($city_id, $type)
    {
        return $this->model->getById(array(
            'city_id' => $city_id,
            'type' => $type
        ));
    }

    function add ($city_id, $type, $num)
    {
        $rs = $this->model->getById(array(
            'city_id' => $city_id,
            'type' => $type,
        ));
        if ($rs) {
            $this->model->setById(array(
                'city_id' => $city_id,
                'type' => $type,
            ), array(
                array(
                    'num = num + :num',
                    'num' => $num,
                )
            ));
        } else {
            $this->model->create(array(
                'city_id' => $city_id,
                'type' => $type,
                'num' => $num
            ));
        }
    }

    function sub ($city_id, $type, $num)
    {
        $this->model->setById(array(
            'city_id' => $city_id,
            'type' => $type,
        ), array(
            array(
                'num = num - :num',
                'num' => $num,
            )
        ));
    }

    function update ($city_id, $type, $num)
    {
        $this->model->setById(array(
            'city_id' => $city_id,
            'type' => $type,
        ), array(
            'num' => $num,
        ));
    }

    function get ($city_id)
    {
        return $this->model->find(array(
            'city_id' => $city_id,
            array(
                'num > :num',
                'num' => 0,
            )
        ));
    }

}
