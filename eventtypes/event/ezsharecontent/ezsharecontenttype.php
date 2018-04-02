<?php

class eZShareContentType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "ezsharecontent";
	function __construct()
    {
        parent::__construct( eZShareContentType::WORKFLOW_TYPE_STRING, ezpI18n::tr( 'ocsharecontent', 'Share content workflow' ) );
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'after' ) ) ) );
    }

    function execute( $process, $event )
    {
        $parameterList = $process->attribute( 'parameter_list' );
        $objectID = $parameterList['object_id'];
        $object = eZContentObject::fetch( $objectID );
        
        if ( $object instanceof eZContentObject )
        {
            /*
            if ( $object->attribute( 'current_version' ) > 1 )
            {
                return eZWorkflowType::STATUS_ACCEPTED;
            }
            */
            
            $storageNodes = array();
            $parentsNodeIds = array();
            foreach( $object->attribute( 'assigned_nodes' ) as $node )
            {
                $parent = $node->attribute( 'parent' );
                if ( in_array( $parent->attribute( 'class_identifier' ), eZINI::instance( 'sharecontent.ini' )->variable( 'Storage', 'Classes' ) ) )
                {
                    $storageNodes[] = $parent;   
                }                
                $parentsNodeIds[] = $parent->attribute( 'node_id' );
            }
            
            if ( empty( $storageNodes ) )
            {
                return eZWorkflowType::STATUS_ACCEPTED;
            }
            
            OCSCHandler::loadAndRegisterAllSources();
            $sources = OCSCHandler::registeredSources();
            
            // cerco le collocazioni automatiche definite in sharecontent/filters
            $locations = array();
            foreach( $storageNodes as $storageNode )
            {
                foreach( $sources as $source )
                {
                    $source->setStorages( array( $storageNode ) );
                    if ( array_key_exists( $object->attribute( 'class_identifier' ), $source->getAvailableClassIdentifiers() ) )
                    {
                        if ( $source->runForUser( $object->attribute( 'owner_id' ), $object->attribute( 'class_identifier' ) ) )
                        {
                            $locationsByObject = $source->getLocationsByObject( $object );
                            $locationsByClass = $source->getLocationsByClass( $object->attribute( 'class_identifier' ) );
                            $locations = array_merge( $locations, $locationsByObject, $locationsByClass );
                        }
                    }
                }
            }
            $locations = array_unique( $locations );
            if ( !empty( $locations ) )
            {
                // rimuove tra le collocazioni automatiche quelle già presenti nell'oggetto
                $removeLocations = array();
                foreach( $parentsNodeIds as $parentsNodeId )
                {
                    if ( in_array( $parentsNodeId, $locations ) )
                    {
                        $removeLocations[] = $parentsNodeId;
                    }
                }
                $addLocations = array_diff( $locations, $removeLocations );
                sort( $addLocations );
                
                if ( !empty( $addLocations ) )
                {                
                    $newMainNodeId = array_pop( $addLocations );                
                    eZContentOperationCollection::moveNode( $object->attribute( 'main_node_id' ), $object->attribute( 'id' ), $newMainNodeId );
                    $newMainNode = eZContentObjectTreeNode::fetch( $newMainNodeId );
                    if ( $newMainNode instanceof eZContentObjectTreeNode )
                    {
                        eZContentCacheManager::clearContentCacheIfNeeded( $newMainNode->attribute( 'contentobject_id' ) );
                    }
                    if ( !empty( $addLocations ) )
                    {
                        eZContentOperationCollection::addAssignment( $object->attribute( 'main_node_id' ), $object->attribute( 'id' ), $addLocations );                
                    }
                }
            }
        }
        
        return eZWorkflowType::STATUS_ACCEPTED;
    }

}

eZWorkflowEventType::registerEventType( eZShareContentType::WORKFLOW_TYPE_STRING, 'eZShareContentType' );

?>
