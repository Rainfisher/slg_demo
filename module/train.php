<?php

class Module_Train extends Module_Abstract
{

    public final function create ($city_id, $army_type, $num)
    {
        if (! array_key_exists($army_type, Vender_Conf::$army)) {
            $this->error('train.army.type.invalid');
        }
        if ($num < 0) {
            $this->error('train.num.invalid');
        }
        $rs = $this->get($city_id);
        if (count($rs) >= Vender_Conf::TRAIN_LIST_MAXIMUM) {
            $this->error('train.list.full');
        }
        $conf = Vender_Conf::$army[$army_type];
        $city = $this->city->get($city_id);
        if ($city['gold'] < $conf['gold'] * $num) {
            $this->error('train.gold.not.enough');
        }
        if ($city['population'] < $num) {
            $this->error('train.population.not.enough');
        }
        $this->model_city->setById(array(
            'id' => $city_id,
        ), array(
            array(
                'gold = gold - :gold',
                'gold' => $conf['gold'] * $num,
            ),
            array(
                'population = population - :population',
                'population' => $num
            )
        ));
        $this->model->create(array(
            'city_id' => $city_id,
            'type' => $army_type,
            'num' => $num,
            'cost_time' => $conf['time'] * $num * 60,
            'created_at' => $this->time(),
        ));
        return true;
    }

    function index ($city_id)
    {
        return $this->get($city_id);
    }

    public final function delete ($city_id, $train_id)
    {
        $rs = $this->get($city_id);
        if (count($rs) <= 1) {
            $this->error('train.cant.cancel');
        }
        foreach ($rs as $i => $one) {
            if ($one['id'] == $train_id) {
                if ($i == 0) {
                    $this->error('train.cant.cancel');
                } else {
                    $this->model->delById(array(
                        'id' => $train_id
                    ));
                }
            }
        }
    }

    function get ($city_id)
    {
        return $this->model->find(array(
            'city_id' => $city_id
        ), 'created_at asc');
    }

}
