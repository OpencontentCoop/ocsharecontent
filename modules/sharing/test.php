<?php
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$sharecontentIni = eZINI::instance( 'sharecontent.ini' );
$Module = $Params['Module'];
$ObjectID = $Params['ObjectID'];
$object = eZContentObject::fetch( $ObjectID );

if ( $object instanceof eZContentObject )
{
    echo $object->attribute( 'id' ) . "<br />";
    echo $object->attribute( 'class_identifier' );
    
    $locations = array();
    
    OCSCHandler::loadAndRegisterAllSources();
    
    $storageNodes = eZContentObjectTreeNode::subTreeByNodeID( array( 'ClassFilterType' => 'include',
                                                                     'ClassFilterArray' => eZINI::instance( 'sharecontent.ini' )->variable( 'Storage', 'Classes' ) ),
                                                             eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
    
    foreach( $storageNodes as $storageNode )
    {
        $sources = OCSCHandler::registeredSources();
        echo '<h1>' . $storageNode->attribute( 'name' ) . ' ID #' . $storageNode->attribute( 'contentobject_id' )  . '</h1>';
        // cerco le collocazioni automatiche definite in sharecontent/filters        
        foreach( $sources as $source )
        {
            echo '<h2>' . $source->getSourceName() . '</h2>';
            
            $source->setStorages( array( $storageNode ) );
            //$source->setStorages( $storageNodes );
            
            if ( array_key_exists( $object->attribute( 'class_identifier' ), $source->getAvailableClassIdentifiers() ) )
            {
                if ( $source->runForUser( $object->attribute( 'owner_id' ), $object->attribute( 'class_identifier' ) ) )
                {
                    echo '<h3>By Objects</h3><ul>';
                    $locationsByObject = $source->getLocationsByObject( $object );
                    foreach( $locationsByObject as $location )
                    {
                        $node = eZContentObjectTreeNode::fetch( $location );
                        if ( $node )
                        {
                            echo '<li>' . $node->attribute( 'url_alias' ) . ' ' . $node->attribute( 'node_id' ) . '</li>';
                        }
                    }
                    echo '</ul>';
                    
                    echo '<h3>By Class</h3><ul>';
                    $locationsByClass = $source->getLocationsByClass( $object->attribute( 'class_identifier' ) );                                        
                    foreach( $locationsByClass as $location )
                    {
                        $node = eZContentObjectTreeNode::fetch( $location );
                        if ( $node )
                        {
                            echo '<li>' . $node->attribute( 'url_alias' ) . ' ' . $node->attribute( 'node_id' ) . '</li>';
                        }
                    }
                    echo '</ul>';
                    
                    $locations = array_merge( $locations, $locationsByObject, $locationsByClass );
                }
            }
            
            echo '<pre>';
            $data = $source->getData();
            print_r( $data[ $storageNode->attribute( 'contentobject_id' ) ][ $source->getSourceIdentifier() ][ $object->attribute( 'class_identifier' ) ] );
            echo '</pre>';
            
        }
    }
    
    echo '<pre>';    
    print_r( array_unique( $locations ) );
    echo '</pre>';
    
}

eZDisplayDebug();
eZExecution::cleanExit();


?>