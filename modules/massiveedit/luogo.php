<?php

$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
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

$action = false;
if ( isset( $Params['Action'] ) )
{
    $action = $Params['Action'];
}
elseif ( $http->hasPostVariable( 'Action' ) )
{
    $action = $http->postVariable( 'Action' );
}

if ( $action == 'EditItem' )
{
    if ( $http->hasPostVariable( 'EditNode' ) )
    {
        $ContentObjectID = key( $http->postVariable( 'EditNode' ) );
        $http->setSessionVariable( 'RedirectURIAfterPublish', $http->postVariable( 'RedirectURIAfterPublish' ) );
        $http->setSessionVariable( 'RedirectIfDiscarded', $http->postVariable( 'RedirectIfDiscarded' ) );
        $Module->redirectTo( 'content/edit/' . $ContentObjectID . '/f/' . eZINI::instance()->variable( 'RegionalSettings', 'ContentObjectLocale' ) );
        return;
    }
    elseif ( $http->hasPostVariable( 'RemoveNode' ) )
    {
        $viewMode = $http->postVariable( 'ViewMode', 'full' );
        $contentNodeID = key( $http->postVariable( 'RemoveNode' ) );
        $node = eZContentObjectTreeNode::fetch( $contentNodeID );
        $parentNodeID = $node->attribute( 'parent_node_id' );
        $contentObjectID = $node->object()->attribute( 'id' );
        $hideRemoveConfirm = false;
        $http->setSessionVariable( 'CurrentViewMode', $viewMode );
        $http->setSessionVariable( 'ContentNodeID', $parentNodeID );
        $http->setSessionVariable( 'HideRemoveConfirmation', $hideRemoveConfirm );
        $http->setSessionVariable( 'DeleteIDArray', array( $contentNodeID ) );
        $http->setSessionVariable( 'RedirectURIAfterRemove', $http->postVariable( 'RedirectURIAfterRemove', false ) );
        $http->setSessionVariable( 'RedirectIfCancel', $http->postVariable( 'RedirectIfCancel', false ) );
        $Module->redirectTo( 'content/removeobject/' );
    }
    elseif ( $http->hasPostVariable( 'SaveItem' ) || $http->hasPostVariable( 'ButtonAction' ) )
    {
        $Relations = $http->postVariable( 'SelectedRelationId' );
        
        if ( $http->hasPostVariable( 'SaveItem' ) )
            $nodeID = key( $http->postVariable( 'SaveItem' ) );
        elseif ( $http->hasPostVariable( 'ButtonAction' ) )
            $nodeID = $http->postVariable( 'ButtonAction' );
             
        $node = eZContentObjectTreeNode::fetch( $nodeID );
        
        if ( $node && $node->attribute( 'can_edit' ) )
        {
            $object = $node->object();
            $selectedRelationID = $Relations[$nodeID];
            
            if ( !is_array( $selectedRelationID ) )
            {
                $selectedRelationID = array( $selectedRelationID );
            }
    
            $objectToRelate = array();
            foreach ( $selectedRelationID as $selectedRelation )
            {
                if ( $selectedRelation > 0 )
                {
                    $objectSelectedRelation= eZContentObject::fetch( $selectedRelation );
                    if ( $objectSelectedRelation )
                    {
                        $objectToRelate[] = $objectSelectedRelation->attribute( 'id' );                        
                    }
                }
            }
                        
            $attributeList = array( 'tipo_luogo' => implode( '-', $objectToRelate ) );
            $params = array();
            $params['attributes'] = $attributeList;
            $result = eZContentFunctions::updateAndPublishObject( $object, $params );        
            
            $object->expireAllViewCache();
            
        }
    }
}

if ( $http->hasPostVariable( 'ButtonAction' ) && $http->postVariable( 'ButtonAction' ) != '' )
{
    echo $http->postVariable( 'ButtonAction' );
    eZExecution::cleanExit();
}
else
{
    $viewParameters = array( 'offset' => $Offset,
                             'year' => $Year,
                             'month' => $Month,
                             'day' => $Day,
                             'namefilter' => false );
    $viewParameters = array_merge( $viewParameters, $UserParameters );
    $tpl->setVariable( "view_parameters", $viewParameters );    
    
    $Result = array();
    $Result['content'] = $tpl->fetch( 'design:massiveedit/luogo.tpl' );
    $Result['path'] = array( array( 'text' => 'Massive Edit' ,
                                    'url' => 'massiveedit/luogo' ) );
}
?>