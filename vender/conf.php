<?php

class Vender_Conf
{
    const CITY_TAX_INIT = 20;

    const CITY_POPULATION_INIT = 100;

    const CITY_FOOD_CAPITAL = 10000;

    const CITY_FOOD_NORMAL = 1000;

    const TAX_TIME = 3600;

    const TAX_POPULATION_RATE = 0.05;

    const TAX_POPULATION_LIMIT = 10;

    const TRAIN_LIST_MAXIMUM = 5;

    const ARMY_LOSS_RATE = 0.1;

    static $army = array(
        '1' => array(
            'gold' => 1,
            'time' => 3,
            'food' => 10,
            'speed' => 1.5,
        ),
        '2' => array(
            'gold' => 3,
            'time' => 12,
            'food' => 13,
            'speed' => 2,
        ),
        '3' => array(
            'gold' => 10,
            'time' => 50,
            'food' => 30,
            'speed' => 10,
        ),
    );
}
