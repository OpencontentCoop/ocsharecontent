<?php

class OCSCHelper
{
    static function AddSelectedLocations( $Module, $nodeID, $objectID, $locationIDSelection )
    {
        eZDebug::writeNotice( __METHOD__ );
        if ( eZOperationHandler::operationIsAvailable( 'content_addlocation' ) )
        {
            $operationResult = eZOperationHandler::execute( 'content',
                                                            'addlocation', array( 'node_id'              => $nodeID,
                                                                                  'object_id'            => $objectID,
                                                                                  'select_node_id_array' => $locationIDSelection ),
                                                            null,
                                                            true );            
        }
        else
        {
            $operationResult = eZContentOperationCollection::addAssignment( $nodeID, $objectID, $locationIDSelection );
        }
        
        return $operationResult;
    }
    
    static function RemoveSelectedLocations( $Module, $nodeID, $objectID, $locationIDSelection, $pageUriSuffix )
    {
        eZDebug::writeNotice( __METHOD__ );
        $http = eZHTTPTool::instance();
        $object = eZContentObject::fetch( $objectID );
        if ( !$object )
        {            
            eZDebug::writeNotice( 'Object not found ' . $objectID, __METHOD__ );
            return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
        }
        $user = eZUser::currentUser();
        if ( !$object->checkAccess( 'edit' ) &&
             !$user->hasManageLocations() )
        {
            eZDebug::writeNotice( 'Permission denied', __METHOD__ );
            return $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
        }
        $nodes = array();
        foreach ( $locationIDSelection as $locationID )
        {
            $nodes[] = eZContentObjectTreeNode::fetch( $locationID );
        }
        $removeList = array();
        $hasChildren = false;        
        foreach ( $nodes as $node )
        {
            if ( $node )
            {                
                if ( !$node->canRemove() &&
                     !$node->canRemoveLocation() )
                {
                    eZDebug::writeNotice( 'Can remove ' . $node->attribute( 'node_id' ), __METHOD__ );
                    continue;        
                }
                $removeList[$node->attribute( 'node_id' )] = 1;                    
                if ( $node->childrenCount( false ) > 0 )
                {
                    $hasChildren = true;
                }
            }
        }        
        if ( $hasChildren )
        {
            eZDebug::writeNotice( $nodeID . ' HasChildren', __METHOD__ );
            $http->setSessionVariable( 'CurrentViewMode', 'full' );
            $http->setSessionVariable( 'DeleteIDArray', array_keys( $removeList ) );
            $http->setSessionVariable( 'ContentNodeID', $nodeID );
            $http->setSessionVariable( 'ContentLanguage', false );
            return $Module->redirectTo( 'content/removeobject/' );
        }
        else
        {
            eZDebug::writeNotice( 'Remove location ' . var_export( $removeList, 1 ), __METHOD__ );
            if ( eZOperationHandler::operationIsAvailable( 'content_removelocation' ) )
            {
                $operationResult = eZOperationHandler::execute( 'content',
                                                                'removelocation', array( 'node_list' => array_keys( $removeList ) ),
                                                                null,
                                                                true );                
            }
            else
            {
                $operationResult = eZContentOperationCollection::removeNodes( array_keys( $removeList ) );
            }            
            return $Module->redirectTo( 'sharing/dashboard/' . $pageUriSuffix );
        }   
    }
    
    static public function Remove( $Module, $nodeID, $objectID, $pageUriSuffix, $remove = false )
    {
        eZDebug::writeNotice( __METHOD__ );
        if ( $remove )
        {
            $http = eZHTTPTool::instance();
            $node = eZContentObjectTreeNode::fetch( $nodeID );
            $parentNodeID = $node->attribute( 'parent_node_id' );
            
            $http->setSessionVariable( 'CurrentViewMode', 'full' );
            $http->setSessionVariable( 'ContentNodeID', $parentNodeID );
            $http->setSessionVariable( 'HideRemoveConfirmation', false );
            $http->setSessionVariable( 'DeleteIDArray', array( $nodeID ) );
    
            $http->setSessionVariable( 'RedirectURIAfterRemove', 'sharing/dashboard/' . $pageUriSuffix );
            $http->setSessionVariable( 'RedirectIfCancel', 'sharing/dashboard/' . $pageUriSuffix );
            
            if ( $object instanceof eZContentObject )
            {
                $section = eZSection::fetch( $object->attribute( 'section_id' ) );
            }
            if ( isset($section) && $section )
                $navigationPartIdentifier = $section->attribute( 'navigation_part_identifier' );
            else
                $navigationPartIdentifier = null;
            if ( $navigationPartIdentifier and $navigationPartIdentifier == 'ezusernavigationpart' )
            {
                return $Module->redirectTo( 'content/removeuserobject/' );
            }
            elseif ( $navigationPartIdentifier and $navigationPartIdentifier == 'ezmedianavigationpart' )
            {
                return $Module->redirectTo( 'content/removemediaobject/' );
            }
            else
            {
                return $Module->redirectTo( 'content/removeobject/' );
            }
        }
        else
        {
            $curNode = eZContentObjectTreeNode::fetch( $nodeID );
            if ( !$curNode )
                return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
            
            if ( !$curNode->attribute( 'can_hide' ) )
                return $Module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
            
            if ( eZOperationHandler::operationIsAvailable( 'content_hide' ) )
            {
                $operationResult = eZOperationHandler::execute( 'content',
                                                                'hide',
                                                                 array( 'node_id' => $nodeID ),
                                                                 null, true );
            }
            else
            {
                eZContentOperationCollection::changeHideStatus( $nodeID );
            }
            return $Module->redirectTo( 'sharing/dashboard/' . $pageUriSuffix );
        }
    }
    
    static public function SectionEdit( $Module, $nodeID, $objectID, $selectedSectionID, $pageUriSuffix )
    {        
        $selectedSection = eZSection::fetch( $selectedSectionID );
        $object = eZContentObject::fetch( $objectID );
        if ( !$object )
        {            
            eZDebug::writeNotice( 'Object not found ' . $objectID, __METHOD__ );
            return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
        }        
        if ( is_object( $selectedSection ) )
        {
            $currentUser = eZUser::currentUser();
            if ( $currentUser->canAssignSectionToObject( $selectedSectionID, $object ) )
            {
                $db = eZDB::instance();
                $db->begin();
                $objectID = $object->attribute( 'id' );
                $db->query( "UPDATE ezcontentobject SET section_id='$selectedSectionID' WHERE id = '$objectID'" );
                $db->query( "UPDATE ezsearch_object_word_link SET section_id='$selectedSectionID' WHERE  contentobject_id = '$objectID'" );
                eZSearch::updateObjectsSection( array( $objectID ), $selectedSectionID );
                $object->expireAllViewCache();
                $db->commit();
            }
            else
            {
                eZDebug::writeError( "You do not have permissions to assign the section <" . $selectedSection->attribute( 'name' ) .
                                     "> to the object <" . $object->attribute( 'name' ) . ">." );
            }
            return $Module->redirectTo( 'sharing/dashboard/' . $pageUriSuffix );
        }
    }
    
    
    static public function StateEdit( $Module, $nodeID, $objectID, $selectedStateIDList, $pageUriSuffix )
    {                
        $object = eZContentObject::fetch( $objectID );
        if ( !$object )
        {            
            eZDebug::writeNotice( 'Object not found ' . $objectID, __METHOD__ );
            return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
        }

        if ( eZOperationHandler::operationIsAvailable( 'content_updateobjectstate' ) )
        {
            $operationResult = eZOperationHandler::execute( 'content', 'updateobjectstate',
                                                            array( 'object_id'     => $objectID,
                                                                   'state_id_list' => $selectedStateIDList ) );
        }
        else
        {
            eZContentOperationCollection::updateObjectState( $objectID, $selectedStateIDList );
        }
        return $Module->redirectTo( 'sharing/dashboard/' . $pageUriSuffix );
    }
}
