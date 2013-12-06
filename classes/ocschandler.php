<?php

class OCSCHandler
{   
    /*!
     \static
     \return a list of sources which has been registered.     
    */
    static function registeredSources( $sourceIdentifier = false )
    {        
        $sources = isset( $GLOBALS["OCSCSources"] ) ? $GLOBALS["OCSCSources"] : null;        
        if ( isset( $sources ) )
        {
            foreach ( $sources as $sourceString => $className )
            {
                if ( !isset( $GLOBALS["OCSCSourceObjects"][$sourceString] ) )
                {
                    $source = new $className();
                    if ( $source instanceof OCSCAbstractSource )
                    {
                        $GLOBALS["OCSCSourceObjects"][$sourceString] = $source;
                        $source->setSourceIdentifier( $sourceString );
                    }
                    else
                    {
                        unset( $source );
                        eZDebug::writeError( "Class '$sourceString' must extend OCSCAbstractSource", __METHOD__ );
                    }
                }
            }
            
            if ( $sourceIdentifier )
            {
                if ( isset( $GLOBALS["OCSCSourceObjects"][$sourceIdentifier] ) )
                    return $GLOBALS["OCSCSourceObjects"][$sourceIdentifier];
                return null;
            }
            
            uasort( $GLOBALS["OCSCSourceObjects"],
                    create_function( '$a, $b',
                                     'return strcmp( $a->getSourceIdentifier(), $b->getSourceIdentifier());' ) );
            return $GLOBALS["OCSCSourceObjects"];
        }
        return null;
    }

    /*!
     \static
     Registers the $source with string id \a $sourceString and
     class name \a $className. The class name is used for instantiating
     the class and should be in lowercase letters.
    */
    private static function register( $sourceString, $className )
    {
        $sources =& $GLOBALS["OCSCSources"];
        if ( !is_array( $sources ) )
            $sources = array();
        $sources[$sourceString] = $className;
    }
    
    static function allowedSources()
    {
        $allowedSources =& $GLOBALS["OCSCAllowedSources"];
        if ( !is_array( $allowedSources ) )
        {
            $sharecontentINI = eZINI::instance( 'sharecontent.ini' );
            $sources = $sharecontentINI->variable( 'SourceSettings', 'AvailableSources' );
            $allowedSources = array();
            foreach ( $sources as $key => $val )
            {
                $allowedSources[] = is_numeric( $key ) ? $val : $key;;
            }
        }
        return $allowedSources;
    }
    
    static function loadAndRegisterAllSources()
    {
        $allowedSources = self::allowedSources();
        foreach ( $allowedSources as $source )
        {
            self::loadAndRegisterSource( $source );
        }
    }
    
    static function loadAndRegisterSource( $source )
    {
        $sources =& $GLOBALS["OCSCSources"];
        if ( isset( $sources[$source] ) )
        {
            return false;
        }

        $sharecontentINI = eZINI::instance( 'sharecontent.ini' );
        $availableSources = $sharecontentINI->variable( 'SourceSettings', 'AvailableSources' );
        if ( array_key_exists( $source, $availableSources ) )
        {
            if ( class_exists( $availableSources[$source] ) )
            {
                self::register( $source, $availableSources[$source] );
                return true;
            }            
        }
        eZDebug::writeError( "Undefined source class: " . $availableSources[$source], __METHOD__ );
        return false;
    }
}
