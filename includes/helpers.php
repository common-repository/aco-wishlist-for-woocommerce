<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!function_exists('awwlm_empty')) {

    /**
     * @return string
     */
    function awwlm_empty($var)
    {
        if (is_array($var)) {
            return empty($var);
        } else {
            return ($var === null || $var === false || $var === '');
        }
    }

}
