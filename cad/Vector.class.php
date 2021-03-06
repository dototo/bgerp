<?php

/**
 * Вектор
 */
class cad_Vector {
    
    function __construct($x, $y, $type = 'cartesian', $angleUnit = 'rad')
    {
        if($type == 'polar') {
            if($angleUnit != 'rad') {
                $x = deg2rad($x);
            }
            $this->x = $y * cos($x);
            $this->y = $y * sin($x);
            $this->a = $x;
            $this->r = $y;
        } else {
            $this->x = $x;
            $this->y = $y;
            $this->a = $this->getA($x, $y);
            $this->r = sqrt($this->x * $this->x + $this->y * $this->y);
        }
    }

    private function getA($x, $y)
    {
        if($x == 0 && $y == 0) {

            return 0;
        }

        if($x == 0) {
            if($y > 0) {

                return pi()/2;
            } else {

                return pi() + pi()/2;
            }
        }

        if($y == 0) {
            if($x > 0) {

                return 0;
            } else {

                return pi();
            }
        }

        $a = atan(abs($y / $x));

        if($x > 0 && $y > 0) {

            return $a;
        }

        if($x < 0 && $y > 0) {

            return pi() - $a;
        }

        if($x < 0 && $y < 0) {

            return pi() + $a;
        }

        return 2 * pi() - $a;
    }


    function neg()
    {
        return new cad_Vector(-$this->x, -$this->y);
    }


    function add($v)
    {
        return new cad_Vector($this->x + $v->x, $this->y + $v->y);
    }

}