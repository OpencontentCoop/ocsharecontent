<?php
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$sharecontentIni = eZINI::instance( 'sharecontent.ini' );
$Module = $Params['Module'];

$storageClasses = $sharecontentIni->variable( 'Storage', 'Classes' );
$storages = (array) eZContentObjectTreeNode::subTreeByNodeID( array( 'ClassFilterType' => 'include', 'ClassFilterArray' => $storageClasses ), eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
$customStorageNodes = array();
if ( $sharecontentIni->hasVariable( 'Storage', 'CustomNodes' ) )
{
    $customStorageNodes = $sharecontentIni->variable( 'Storage', 'CustomNodes' );
    $storages = array_merge( $storages, eZContentObjectTreeNode::fetch( $customStorageNodes ) );
}
$storageNodes = array();
foreach( $storages as $storage )
{
    $storageNodes[] = $storage->attribute( 'node_id' );
}
$tpl->setVariable( 'storages', $storageNodes );

$sourceArray = array();
OCSCHandler::loadAndRegisterAllSources();
$sources = OCSCHandler::registeredSources();
foreach( $sources as $source )
{
    $source->setStorages( $storages );    
    $array = array(
        'name' => $source->getSourceName(),
        'identifier' => $source->getSourceIdentifier(),
        'class_identifiers' => array()
    );
    
    foreach( $source->getAvailableClassIdentifiers() as $identifier => $class )
    {
        $array['class_identifiers'][$identifier] = array(
            'class_name' => $class,
            'class_identifier' => $identifier,
            'locations' => $source->getLocationsByClass( $identifier ),
            'users' => $source->getUsersByClass( $identifier )
        );        
    }    
    $sourceArray[] = $array;    
}

if ( $Module->isCurrentAction( 'AddLocation' ) )
{
    
    eZContentBrowse::browse( array( 'action_name' => 'SelectSourceNode',
                                    'persistent_data' => array( 'HasBrowsedLocation' => '', 'ContentClassHasInput' => false ),
                                    'from_page' => $Module->currentRedirectionURI() ),
                             $Module );
}

if ( $Module->isCurrentAction( 'HasBrowsedLocation' ) )
{
    $nodeSelection = eZContentBrowse::result( 'SelectSourceNode' );        
    if ( !empty( $nodeSelection ) )
    {
        $sourceIdentifier = $Params['SourceIdentifier'];
        $classIdentifier = $Params['ClassIdentifier'];        
        if ( isset( $sources[$sourceIdentifier] ) )
        {
            eZDebug::writeNotice( $sourceIdentifier );
            eZDebug::writeNotice( $classIdentifier );
            eZDebug::writeNotice( $nodeSelection );            
            $sources[$sourceIdentifier]->storeLocationsForClass( $classIdentifier, $nodeSelection, false );        
        }    
        $Module->redirectToView( 'filters' );        
        return;
    }
}

if ( $Module->isCurrentAction( 'RemoveLocation' ) && $Module->hasActionParameter( 'SelectedLocation' ) )
{
    $sourceIdentifier = $Params['SourceIdentifier'];
    $classIdentifier = $Params['ClassIdentifier'];
    $removeLocations = $Module->actionParameter( 'SelectedLocation' );
    if ( isset( $sources[$sourceIdentifier] ) )
    {
        $sources[$sourceIdentifier]->removeStoredLocationsForClass( $classIdentifier, $removeLocations );        
    }    
    $Module->redirectToView( 'filters' );
    return;
}

if ( $Module->isCurrentAction( 'AddUser' ) )
{
    eZContentBrowse::browse( array( 'action_name' => 'SelectSourceUser',
                                    'persistent_data' => array( 'HasBrowsedUser' => '', 'ContentClassHasInput' => false ),
                                    'from_page' => $Module->currentRedirectionURI() ),
                             $Module );
}

if ( $Module->isCurrentAction( 'HasBrowsedUser' ) )
{
    $userSelection = eZContentBrowse::result( 'SelectSourceUser' );    
    if ( !empty( $userSelection ) )
    {
        $sourceIdentifier = $Params['SourceIdentifier'];
        $classIdentifier = $Params['ClassIdentifier'];        
        if ( isset( $sources[$sourceIdentifier] ) )
        {
            $sources[$sourceIdentifier]->storeUsersForClass( $classIdentifier, $userSelection, false );        
        }    
        $Module->redirectToView( 'filters' );
        return;
    }
}

if ( $Module->isCurrentAction( 'RemoveUser' ) && $Module->hasActionParameter( 'SelectedUser' ) )
{
    $sourceIdentifier = $Params['SourceIdentifier'];
    $classIdentifier = $Params['ClassIdentifier'];
    $removeUsers = $Module->actionParameter( 'SelectedUser' );
    if ( isset( $sources[$sourceIdentifier] ) )
    {
        $sources[$sourceIdentifier]->removeStoredUsersForClass( $classIdentifier, $removeUsers );        
    }    
    $Module->redirectToView( 'filters' );
    return;
}


$tpl->setVariable( 'sources', $sourceArray );
$Result = array();
$Result['content'] = $tpl->fetch( 'design:sharing/filters.tpl' );
$Result['path'] = array( array( 'text' => 'Share Dashboard' ,
                                'url' => 'sharing/dashboard' ),
                         array( 'text' => 'Share Filters' ,
                                'url' => false ));
?>