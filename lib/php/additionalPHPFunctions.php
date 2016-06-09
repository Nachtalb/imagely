<?php
/**
 * Created by PhpStorm.
 * User: nicko
 * Date: 08.06.2016
 * Time: 21:08
 */

function str_lreplace($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);

    if ($pos !== FALSE) {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}