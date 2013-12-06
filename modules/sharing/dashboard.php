<?php
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$sharecontentIni = eZINI::instance( 'sharecontent.ini' );
$Module = $Params['Module'];

if ( isset( $Params['UserParameters'] ) )
{
    $UserParameters = $Params['UserParameters'];
}
else
{
    $UserParameters = array();
}

if ( $Offset )
    $Offset = (int) $Offset;
if ( $Year )
    $Year = (int) $Year;
if ( $Month )
    $Month = (int) $Month;
if ( $Day )
    $Day = (int) $Day;
if ( $Show )
    $Show = $Show;
$viewParameters = array( 'offset' => $Offset,
                         'year' => $Year,
                         'month' => $Month,
                         'day' => $Day,
                         'namefilter' => false );
$viewParameters = array_merge( $viewParameters, $UserParameters );
$tpl->setVariable( "view_parameters", $viewParameters );

$nodeID = $http->postVariable( 'NodeID', false );
$objectID = $http->postVariable( 'ObjectID', false );
$storageNodes = $http->postVariable( 'StorageNodes', false );
$pageUriSuffix = $http->postVariable( 'PageUriSuffix', false );

if ( $Module->isCurrentAction( 'RemoveSelectedLocations' ) && $Module->hasActionParameter( 'SelectedLocation' ) )
{
    $locationIDSelection = $Module->actionParameter( 'SelectedLocation' );    
    return OCSCHelper::RemoveSelectedLocations( $Module, $nodeID, $objectID, $locationIDSelection, $pageUriSuffix );    
}

if ( $Module->isCurrentAction( 'RemoveFromStorage' ) )
{
    $locationIDSelection = array( $nodeID );
    $object = eZContentObject::fetch( $objectID );
    if ( !$object )
    {
        return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
    }
    
    if ( count( $object->attribute( 'assigned_nodes' ) ) > 1 )
    {
        return OCSCHelper::RemoveSelectedLocations( $Module, $nodeID, $objectID, $locationIDSelection, $pageUriSuffix );        
    }
    else
    {
        return OCSCHelper::Remove( $Module, $nodeID, $objectID, $pageUriSuffix );          
    }
}

if ( $Module->isCurrentAction( 'RemoveFromStorageAndAddLocation' ) )
{
    eZContentBrowse::browse( array( 'action_name' => 'SelectLocationsDashboard',
                                    'persistent_data' => array( 'HasBrowsedLocationDashboard' => '', 'NodeID' => $nodeID, 'ObjectID' => $objectID, 'StorageNodes' => $storageNodes, 'PageUriSuffix' => $pageUriSuffix ),
                                    'from_page' => $Module->currentRedirectionURI() ),
                             $Module );
}

if ( $Module->isCurrentAction( 'HasBrowsedLocationDashboard' ) )
{        
    $locationIDSelection = eZContentBrowse::result( 'SelectLocationsDashboard' );    
    if ( !empty( $locationIDSelection ) )
    {        
        OCSCHelper::AddSelectedLocations( $Module, $nodeID, $objectID, $locationIDSelection );        
        return OCSCHelper::RemoveSelectedLocations( $Module, $nodeID, $objectID, array( $nodeID ), $pageUriSuffix );        
    }
}

if ( $Module->isCurrentAction( 'Edit' ) )
{
    $http->setSessionVariable( 'RedirectURIAfterRemove', 'sharing/dashboard/' . $pageUriSuffix );
    $http->setSessionVariable( 'RedirectIfCancel', 'sharing/dashboard/' . $pageUriSuffix );
    return $Module->redirectTo( 'content/edit/' . $objectID . '/f' );
}


if ( $Module->isCurrentAction( 'SectionEdit' ) && $Module->hasActionParameter( 'SelectedSectionId' ) )
{
    $selectedSectionID = $Module->actionParameter( 'SelectedSectionId' );
    return OCSCHelper::SectionEdit( $Module, $nodeID, $objectID, $selectedSectionID, $pageUriSuffix );
}


if ( $Module->isCurrentAction( 'StateEdit' ) && $Module->hasActionParameter( 'SelectedStateIDList' ) )
{
    $selectedStateIDList = $Module->actionParameter( 'SelectedStateIDList' );
    return OCSCHelper::StateEdit( $Module, $nodeID, $objectID, $selectedStateIDList, $pageUriSuffix );
}


$searchText = $http->getVariable( 'SearchText', '' );
$tpl->setVariable( "search_text", $searchText );

$activeFacetParameters = $http->getVariable( 'activeFacets', array() );
$tpl->setVariable( "activeFacetParameters", $activeFacetParameters );


$dateFilter = $http->getVariable( 'dateFilter', 0 );
$dateFilterLabel = '';
switch ( $dateFilter )
{
    case 1:
        $dateFilterLabel =  ezpI18n::tr( "design/standard/content/search", "Last day" );
        break;
    case 2:
        $dateFilterLabel =  ezpI18n::tr( "design/standard/content/search", "Last week" );
        break;
    case 3:
        $dateFilterLabel =  ezpI18n::tr( "design/standard/content/search", "Last month" );
        break;
    case 4:
        $dateFilterLabel =  ezpI18n::tr( "design/standard/content/search", "Last three months" );
        break;
    case 5:
        $dateFilterLabel =  ezpI18n::tr( "design/standard/content/search", "Last year" );
        break;
    default:
       $dateFilter = null;
       $dateFilterLabel = null;
}
$tpl->setVariable( "dateFilter", $dateFilter );
$tpl->setVariable( "dateFilterLabel", $dateFilterLabel );

$storageClasses = $sharecontentIni->variable( 'Storage', 'Classes' );
$itemClasses = array();
if ( $sharecontentIni->hasVariable( 'Items', 'Classes' ) )
{
    $itemClasses = $sharecontentIni->variable( 'Items', 'Classes' );
}
$tpl->setVariable( "itemClasses", $itemClasses );

$storages = eZContentObjectTreeNode::subTreeByNodeID( array( 'ClassFilterType' => 'include', 'ClassFilterArray' => $storageClasses ), eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
$customStorageNodes = array();
if ( $sharecontentIni->hasVariable( 'Storage', 'CustomNodes' ) )
{
    $customStorageNodes = $sharecontentIni->variable( 'Storage', 'CustomNodes' );
    $storages = array_merge( $storages, eZContentObjectTreeNode::fetch( $customStorageNodes ) );
}
$tpl->setVariable( "storages", $storages );

$subtreearray = array();
foreach( $storages as $item )
{
    $subtreearray[] = $item->attribute( 'node_id' );
}
$storageFilters = $http->getVariable( 'storageFilter', array() );
if( !empty( $storageFilters ) )
{
    $subtreearray = $storageFilters;
}
$tpl->setVariable( "storageFilters", $storageFilters );
$tpl->setVariable( "subtreearray", $subtreearray );

$facetLimit = 50;
if ( $sharecontentIni->hasVariable( 'Dashboard', 'FacetLimit' ) )
{
    $facetLimit = $sharecontentIni->variable( 'Dashboard', 'FacetLimit' );
}
$defaultSearchFacets = array( array( 'field' => 'class', 'name' => 'Tipo di contenuto', 'limit' => $facetLimit ) );
$customFacets = $sharecontentIni->variable( 'Dashboard', 'Facets' );
foreach ( $customFacets as $field => $name )
{
	$defaultSearchFacets[] = array( 'field' => $field, 'name' => $name, 'limit' => $facetLimit );
}
$tpl->setVariable( "defaultSearchFacets", $defaultSearchFacets );

$filterParameters = array();
foreach( $storageClasses as $class )
{
    $filterParameters['-meta_contentclass_id_si'] = eZContentClass::classIDByIdentifier( $class );
}
if ( $http->hasGetVariable( 'filter' ) )
{
    foreach ( $http->getVariable( 'filter' ) as $filterCond )
    {
        list( $name, $value ) = explode( ':', $filterCond );
        $filterParameters[$name] = $value;
    }
}

$tpl->setVariable( "filterParameters", $filterParameters );



OCSCHandler::loadAndRegisterAllSources();
$sources = OCSCHandler::registeredSources();
$tpl->setVariable( "source_count", count( $sources ) );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:sharing/dashboard.tpl' );
$Result['path'] = array( array( 'text' => 'Share Dashboard' ,
                                'url' => 'sharing/dashboard' ) );
?>