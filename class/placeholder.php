<?php

class Placeholder 
{
    /**
    * Display image
    */
    function render() 
    {
        header('Content-type: image/png');
        
        readfile(dirname(__FILE__) . "/../images/yetti-upgrade.png");
    }
}