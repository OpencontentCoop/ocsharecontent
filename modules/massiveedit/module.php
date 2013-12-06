<?php

$Module = array( 'name' => 'OC Massive Action',
                 'variable_params' => true );

$ViewList = array();

$ViewList['luogo'] = array(
    'functions' => array( 'edit' ),
    'script' => 'luogo.php',
    'default_navigation_part' => 'ezcontentnavigationpart',
    'params' => array( 'Action', 'NodeID' ),
    'unordered_params' => array( 'language' => 'Language',
                                 'offset' => 'Offset',
                                 'year' => 'Year',
                                 'month' => 'Month',
                                 'day' => 'Day',
                                 'show' => 'Show', )
);


$FunctionList = array();
$FunctionList['edit'] = array();

?>
