<?php

$Module = array( 'name' => 'OC Share Content',
                 'variable_params' => true );

$ViewList = array();

$ViewList['dashboard'] = array(
    'functions' => array( 'dashboard' ),
    'script' => 'dashboard.php',
    'default_navigation_part' => 'ezcontentnavigationpart',
    'single_post_actions' => array(
        'RemoveSelectedLocationsButton' => 'RemoveSelectedLocations',
        'RemoveFromStorageButton' => 'RemoveFromStorage',
        'RemoveFromStorageAndAddLocationButton' => 'RemoveFromStorageAndAddLocation',
        'HasBrowsedLocationDashboard' => 'HasBrowsedLocationDashboard',
        'EditButton' => 'Edit',
        'SectionEditButton' => 'SectionEdit',
        'StateEditButton' => 'StateEdit'        
    ),
    'post_action_parameters' => array(
        'RemoveSelectedLocations' => array(
            'SelectedLocation' => 'SelectedLocation'
        ),
        'SectionEdit' => array(
            'SelectedSectionId' => 'SelectedSectionId'
        ),
        'StateEdit' => array(
            'SelectedStateIDList' => 'SelectedStateIDList'
        )
    ),
    'params' => array(),
    'unordered_params' => array( 'language' => 'Language',
                                 'offset' => 'Offset',
                                 'year' => 'Year',
                                 'month' => 'Month',
                                 'day' => 'Day',
                                 'show' => 'Show', )
);


$ViewList['filters'] = array(
    'functions' => array( 'dashboard' ),
    'script' => 'filters.php',
    'default_navigation_part' => 'ezcontentnavigationpart',
    'params' => array( 'SourceIdentifier', 'ClassIdentifier' ),
    'unordered_params' => array(),
    'single_post_actions' => array(
        'RemoveLocationButton' => 'RemoveLocation',
        'AddLocationButton' => 'AddLocation',
        'HasBrowsedLocation' => 'HasBrowsedLocation',
        'RemoveUserButton' => 'RemoveUser',
        'AddUserButton' => 'AddUser',
        'HasBrowsedUser' => 'HasBrowsedUser'
    ),
    'post_action_parameters' => array(
        'RemoveLocation' => array(
            'SelectedLocation' => 'SelectedLocation'
        ),
        'RemoveUser' => array(
            'SelectedUser' => 'SelectedUser'
        )
    )
);

$ViewList['test'] = array(
    'functions' => array( 'dashboard' ),
    'script' => 'test.php',
    'default_navigation_part' => 'ezcontentnavigationpart',
    'params' => array( 'ObjectID' )
);


$FunctionList = array();
$FunctionList['dashboard'] = array();

?>
