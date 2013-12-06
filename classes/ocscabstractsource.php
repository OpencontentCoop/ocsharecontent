<?php

abstract class OCSCAbstractSource
{
    private $sourceString;
    
    protected $locations = array();
    protected $users = array();
    
    protected $storages = array();
    
    abstract function getSourceName();

    abstract function getSourceUser();    
    
    abstract function getAvailableClassIdentifiers();

    // ritorna la/e collocazione/i di default
    abstract function getBaseLocations();
    
    public function setStorages( array $storagesNodes )
    {        
        $this->storages = $storagesNodes;
    }
    
    public function getLocationsByClass( $classIdentifier )
    {
        if ( isset( $this->locations[$classIdentifier] ) )
        {
            return $this->locations[$classIdentifier];
        }
        
        $locations = $this->getStoredLocationsByClass( $classIdentifier );
        if ( !empty( $locations ) )
        {
            $this->locations[$classIdentifier] = $locations;
            array_unique( $this->locations[$classIdentifier] );
            return $this->locations[$classIdentifier];
        }
        
        return $this->getBaseLocations();
        
    }
    
    public function getLocationsByObject( eZContentObject $object )
    {
        return false;
    }
    
    final public function runForUser( $userID, $classIdentifier )
    {
        $userObject = eZUser::fetch( $userID );
        if ( !$userObject instanceof eZUser )
        {
            return false;
        }
        
        $forceUser = $this->getSourceUser;
        if ( $forceUser instanceof eZUser )
        {
            if ( $forceUser->attribute( 'contentobject_id' ) == $userObject->attribute( 'contentobject_id' ) )
            {
                return true;    
            }
            return false;
        }
        
        if ( !$this->getUsersByClass( $classIdentifier ) )
        {
            return true;
        }
        else
        {
            $users = array();
            $groups = array();
            foreach( $this->getUsersByClass( $classIdentifier ) as $id )
            {
                $users = array();
                if ( eZUser::fetch( $id ) instanceof eZUser )
                {
                    $users[] = $id;
                }
                else
                {
                    $groups[] = $id;
                }
                
            }
            
            $ok = false;
            if ( !empty( $users ) )
            {
                $ok = in_array( $userID, $users );
            }
                        
            if ( !empty( $groups ) )
            {
                if ( $ok == false )
                {
                    $ok = count( array_intersect( $groups, $userObject->groups( false ) ) ) > 0;
                }
            }
            
            return $ok;
        }
    }
    
    final public function getUsersByClass( $classIdentifier )
    {
        if ( isset( $this->users[$classIdentifier] ) )
        {
            return $this->users[$classIdentifier];
        }
        $users = $this->getStoredUsersByClass( $classIdentifier );
        if ( !empty( $users ) )
        {
            $this->users[$classIdentifier] = $users;
            array_unique( $this->users[$classIdentifier] );
            return $this->users[$classIdentifier];
        }
        return false;
    }
    
    final public function storeUsersForClass( $classIdentifier, array $users, $override = true )
    {        
        $data = $this->getData();        
        foreach( $this->storages as $storage )
        {
            if ( is_numeric( $storage ) )
            {
                $storage = eZContentObjectTreeNode::fetch( $storage );                
            }
            
            if ( !$storage instanceof eZContentObjectTreeNode ||
                 !eZContentCLass::fetchByIdentifier( $classIdentifier ) )
            {
                continue;
            }
            $storageID = $storage->attribute( 'contentobject_id' );
            $sourceIdentifer = $this->getSourceIdentifier();
            if ( isset( $data[$storageID][$sourceIdentifer][$classIdentifier]['users'] ) && !$override )
            {
                $users = array_merge( $users, $data[$storageID][$sourceIdentifer][$classIdentifier]['users'] );
            }            
            $data[$storageID][$sourceIdentifer][$classIdentifier]['users'] = array_unique( $users );
        }
        $this->storeData( $data );
    }
    
    final public function removeStoredUsersForClass( $classIdentifier, array $users )
    {
        $data = $this->getData();        
        foreach( $this->storages as $storage )
        {
            if ( is_numeric( $storage ) )
            {
                $storage = eZContentObjectTreeNode::fetch( $storage );                
            }
            
            if ( !$storage instanceof eZContentObjectTreeNode ||
                 !eZContentCLass::fetchByIdentifier( $classIdentifier ) )
            {
                continue;
            }
            $storageID = $storage->attribute( 'contentobject_id' );
            $sourceIdentifer = $this->getSourceIdentifier();
            if ( isset( $data[$storageID][$sourceIdentifer][$classIdentifier]['users'] ) )
            {
                $currentUsers = $data[$storageID][$sourceIdentifer][$classIdentifier]['users'];
                $newUsers = array_diff( $currentUsers, $users );
            }            
            $data[$storageID][$sourceIdentifer][$classIdentifier]['users'] = array_unique( $newUsers );            
        }
        $this->storeData( $data );
    }
    
    final public function getStoredUsersByClass( $classIdentifier )
    {
        $data = $this->getData();
        $users = array();
        foreach( $this->storages as $storage )
        {
            if ( is_numeric( $storage ) )
            {
                $storage = eZContentObjectTreeNode::fetch( $storage );                
            }
            
            if ( !$storage instanceof eZContentObjectTreeNode ||
                 !eZContentCLass::fetchByIdentifier( $classIdentifier ) )
            {
                continue;
            }
            $storageID = $storage->attribute( 'contentobject_id' );
            $sourceIdentifer = $this->getSourceIdentifier();
            if ( isset( $data[$storageID][$sourceIdentifer][$classIdentifier]['users'] ) )
            {
                $users = array_merge( $users, $data[$storageID][$sourceIdentifer][$classIdentifier]['users'] );
            }
        }
        return array_unique( $users );
    }
    
    final public function storeLocationsForClass( $classIdentifier, array $locations, $override = true )
    {
        $data = $this->getData();        
        foreach( $this->storages as $storage )
        {
            if ( is_numeric( $storage ) )
            {
                $storage = eZContentObjectTreeNode::fetch( $storage );                
            }
            
            if ( !$storage instanceof eZContentObjectTreeNode ||
                 !eZContentCLass::fetchByIdentifier( $classIdentifier ) )
            {                
                continue;
            }
            $storageID = $storage->attribute( 'contentobject_id' );
            $sourceIdentifer = $this->getSourceIdentifier();
            if ( isset( $data[$storageID][$sourceIdentifer][$classIdentifier]['locations'] ) && !$override )
            {
                $locations = array_merge( $locations, $data[$storageID][$sourceIdentifer][$classIdentifier]['locations'] );
            }
            $data[$storageID][$sourceIdentifer][$classIdentifier]['locations'] = array_unique( $locations );
        }

        $this->storeData( $data );
    }
    
    final public function removeStoredLocationsForClass( $classIdentifier, array $locations )
    {
        $data = $this->getData();        
        foreach( $this->storages as $storage )
        {
            if ( is_numeric( $storage ) )
            {
                $storage = eZContentObjectTreeNode::fetch( $storage );                
            }
            
            if ( !$storage instanceof eZContentObjectTreeNode ||
                 !eZContentCLass::fetchByIdentifier( $classIdentifier ) )
            {
                continue;
            }
            $storageID = $storage->attribute( 'contentobject_id' );
            $sourceIdentifer = $this->getSourceIdentifier();
            if ( isset( $data[$storageID][$sourceIdentifer][$classIdentifier]['locations'] ) )
            {
                $currentLocations = $data[$storageID][$sourceIdentifer][$classIdentifier]['locations'];
                $newLocations = array_diff( $currentLocations, $locations );
            }            
            $data[$storageID][$sourceIdentifer][$classIdentifier]['locations'] = array_unique( $newLocations );            
        }
        $this->storeData( $data );
    }
    
    final public function getStoredLocationsByClass( $classIdentifier )
    {
        $data = $this->getData();
        $locations = array();
        foreach( $this->storages as $storage )
        {
            if ( is_numeric( $storage ) )
            {
                $storage = eZContentObjectTreeNode::fetch( $storage );                
            }
            
            if ( !$storage instanceof eZContentObjectTreeNode ||
                 !eZContentCLass::fetchByIdentifier( $classIdentifier ) )
            {
                continue;
            }
            $storageID = $storage->attribute( 'contentobject_id' );
            $sourceIdentifer = $this->getSourceIdentifier();
            if ( isset( $data[$storageID][$sourceIdentifer][$classIdentifier]['locations'] ) )
            {
                $locations = $data[$storageID][$sourceIdentifer][$classIdentifier]['locations'];
            }
        }
        return array_unique( $locations );
    }        
    
    final function storeData( $data )
    {
       $sitedata = $this->getSiteData();
       $sitedata->setAttribute( 'value', serialize( $data ) );
       $sitedata->store();
    }
    
    final function getData()
    {
        $sitedata = $this->getSiteData();
        return unserialize( $sitedata->attribute( 'value' )  );
    }
    
    final function resetData()
    {
        $sitedata = $this->getSiteData();
        $sitedata->setAttribute( 'value', serialize( array() ) );
        $sitedata->store();
    }
    
    final function getSiteData()
    {
        $sitedata = eZSiteData::fetchByName( 'ocscdata' );
        if ( $sitedata === NULL )
        {
            $sitedata = new eZSiteData( array(
                'name' => 'ocscdata',
                'value' => serialize( array() )
            ));
            eZDebug::writeNotice( 'Create ezsitedata record for ocscdata' );
            $sitedata->store();
        }
        return $sitedata;
    }
    
    final function setSourceIdentifier( $sourceString )
    {
        $this->sourceString = $sourceString;
    }
    
    final function getSourceIdentifier()
    {
        return $this->sourceString;
    }

}
