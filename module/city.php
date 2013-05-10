<?php

class Module_City extends Module_Abstract
{
    public final function create ($user_id, $x, $y)
    {
        try {
            return $this->model->create(array(
                'user_id' => $user_id,
                'x' => $x,
                'y' => $y,
                'tax' => Vender_Conf::CITY_TAX_INIT,
                'population' => Vender_Conf::CITY_POPULATION_INIT,
                'updated_at' => $this->time(),
            ));
        } catch (Exception $e) {
            $this->error('city already init');
        }
    }

    public final function index ($user_id)
    {
        $rs = $this->model->find(array(
            'user_id' => $user_id,
        ));
        $list = array();
        foreach ($rs as $city) {
            $list[] = $this->show($city);
        }
        return $list;
    }

    public final function show ($city_id, $is_show_train = true, $is_show_battle = true)
    {
        $rs = $this->get($city_id);
        if ($is_show_train) {
            $rs['train'] = $this->train->get($rs['id']);
            $rs['army']  = $this->army->get($rs['id']);
        }
        if ($is_show_battle) {
            $rs['battle'] = $this->battle->getAll($rs['id']);
        }
        return $rs;
    }

    public final function updateCapital ($city_id)
    {
        $rs = $this->get($city_id);
        $capital = $this->model->getById(array(
            'user_id' => $rs['user_id'],
            'capital' => 1,
        ));
        if ($capital) {
            if ($capital['id'] == $city_id) {
                $this->error('city.already.capital');
            }
            $this->get($capital);
        }
        $this->model->setById(array(
            'user_id' => $rs['user_id']
        ), array(
            'capital' => 0
        ));
        $this->model->setById(array(
            'id' => $city_id,
        ), array(
            'capital' => 1
        ));
        return true;
    }

    public final function updateTax ($city_id, $tax)
    {
        $rs = $this->get($city_id);
        $this->model->setById(array(
            'id' => $city_id,
        ), array(
            'tax' => $tax,
        ));
        return true;
    }

    public function get ($city_id, $user_id = null)
    {
        if (! is_array($city_id)) {
            $rs = $this->model->getById(array(
                'id' => $city_id
            ));
        } else {
            $rs = $city_id;
        }
        if (! $rs || ($user_id && $rs['user_id'] != $user_id)) {
            $this->error('city.not.yours OR city.not.exist');
        }
        $this->event->transaction($rs);
        $rs['food'] = (int) $rs['food'];
        return $rs;
    }

}
