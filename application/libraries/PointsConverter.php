<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Converts question/course points to string explaining knowledge level
 * of particular question/course
 */
class PointsConverter extends BaseLibrary {
    
    private $intMinPoints;
    private $intMaxPoints;

    public function __construct() {
        parent::__construct();
        $this->intMinPoints = $this->CI->config->item('minPoints');
        $this->intMaxPoints = $this->CI->config->item('maxPoints');
    }

    public function pointsToString($intPoints) {
        // new course with no questions has 0 average points - none
        if ($intPoints == 0) {
            return "None";
        }

        $intPointsRange = $this->intMaxPoints - $this->intMinPoints;
        
        // from min to 1/3 of range - good
        if ($intPoints < $this->intMinPoints + $intPointsRange * 1/3) {
            return "Good";
        }

        // from 1/3 to 2/3 of range - average
        if ($intPoints >= $this->intMinPoints + $intPointsRange * 1/3 && $intPoints <= $this->intMinPoints + $intPointsRange * 2/3) {
            return "Average";
        }

        // from 2/3 of range to max - bad
        if ($intPoints > $this->intMinPoints + $intPointsRange * 2/3) {
            return "Bad";
        }
    }

}