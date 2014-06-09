<?php

function color($string, $foreground = NULL, $background = NULL) {
    static $fg = array(
        'white' => '1;37',
        
        'light-gray' => '0;37',
        'silver' => '0;37',
        
        'gray' => '1;30',
        'dark-gray' => '1;30',
        
        'black' => '0;30',

        'red' => '0;31',
        'light-red' => '1;31',

        'green' => '0;32',
        'light-green' => '1;32',

        'blue' => '0;34',
        'light-blue' => '1;34',

        'cyan' => '0;36',
        'light-cyan' => '1;36',

        'purple' => '0;35',
        'light-purple' => '1;35',

        'golden' => '0;33',
        'yellow' => '1;33',
    );

    static $bg = array(
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'silver' => '47',
    );

    if (!isset($fg[$foreground]) && !isset($bg[$background]))
        return $string;
    
    $out = "";

    if (isset($fg[$foreground]))
        $out .= "\033[" . $fg[$foreground] . "m";

    if (isset($bg[$background]))
        $out .= "\033[" . $bg[$background] . "m";

    return $out . $string . "\033[0m";
}
