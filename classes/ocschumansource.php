
<?php

class OCSCHumanEditorSource extends OCSCAbstractSource
{
    protected $classes;
    
    function getSourceName()
    {
        return 'Editor manuale';
    }

    function getSourceUser()
    {        
        return false;
    }
    
    function getAvailableClassIdentifiers()
    {
        if( $this->classes == null )
        {
            $classes = eZContentClass::fetchAllClasses( false );
            foreach( $classes as $class )
            {
                $this->classes[eZContentClass::classIdentifierByID( $class['id'] )] = $class['name'];
            }
        }
        return $this->classes;
    }
        
    
    function getBaseLocations()
    {        
        $locations = array();
        foreach( $this->storages as $storage )
        {
            if ( $storage instanceof eZContentObjectTreeNode )
                $locations[] = $storage->attribute( 'node_id' );
        }
        return $locations;
    }
}
