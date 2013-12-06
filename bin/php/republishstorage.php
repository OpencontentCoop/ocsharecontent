#!/usr/bin/env php
<?php
set_time_limit ( 0 );
require 'autoload.php';

$siteINI = eZINI::instance();
$siteINI->setVariable( 'SearchSettings', 'DelayedIndexing', 'enabled' );

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "Clean Storage" ),
                                      'use-session' => true,
                                      'use-modules' => true,                                      
                                      'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( "[node-id:]",
                                "",
                                array( 'node-id' => "Storage Node ID" )
                                );
$script->initialize();

if ( $options['node-id'] == NULL )
{
    $cli->error( 'Select a storage node' );
}
else
{
    $user = eZUser::fetchByName( 'admin' );
    if ( !$user )
    {
        $user = eZUser::currentUser();
    }
    eZUser::setCurrentlyLoggedInUser( $user, $user->attribute( 'contentobject_id' ) );
    
    $storageNode = $options['node-id'];
    $rootNode = eZContentObjectTreeNode::fetch( $storageNode )->attribute( 'parent_node_id' );          
    $count = eZContentObjectTreeNode::subTreeCountByNodeID( array( 'MainNodeOnly' => true ), $storageNode );    
    $cli->notice( "Number of objects to clean: $count" );    
    $length = 100;
    $params = array( 'Offset' => 0 , 'Limit' => $length, 'SortBy' => array( 'contentobject_id', true ) );    
    $script->resetIteration( $count );        
    do
    {
        //eZContentObject::clearCache();
        $nodes = eZContentObjectTreeNode::subTreeByNodeID( $params, $storageNode );
        
        foreach ( $nodes as $node )
        {            
            $object = $node->attribute( 'object' );
            $cli->output( 'Republish ' . $object->attribute( 'name' ) );
            $db = eZDB::instance();
            $db->begin();
            $newVersion = $object->createNewVersion( false, true, false );
            if ( !$newVersion instanceof eZContentObjectVersion )
            {
                eZDebug::writeError( 'Unable to create a new version for object ' . $object->attribute( 'id' ), __METHOD__ );
                $db->rollback();
                continue;
            }
            $db->commit();
            $operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id' => $newVersion->attribute( 'contentobject_id' ),
                                                                                         'version'   => $newVersion->attribute( 'version' ) ) );            
        }            
        $params['Offset'] += $length;
    
    } while ( count( $nodes ) == $length );
}

$script->shutdown();
?>
