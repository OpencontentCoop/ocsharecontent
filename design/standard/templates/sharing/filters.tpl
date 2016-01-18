<div class="class-sharefilters">
    <div class="attribute-header">
        <h1>Filtri di pubblicazione</h1>
    </div>
    
    {foreach $sources as $source}
        <h2>{$source.name|wash()}</h2>
        <table cellspacing="0" cellpadding="2" border="0" class="renderedtable">
            <tr>
                <th>Classe</th>                
                <th>Collocazioni automatiche</th>
                <th>Utenti</th>
            </tr>
            {foreach $source.class_identifiers as $class sequence array( 'bgdark', 'bglight' ) as $_style}
                <tr class="{$_style}">
                    <td>
                       <p>{$class.class_name} ({$class.class_identifier})</p>
                    </td>                    
                    <td>                        
                        <form action={concat( 'sharing/filters/', $source.identifier, '/', $class.class_identifier )|ezurl()} name="selectLcations" method="post">
                            {if count( $class.locations )|gt(0)}
                                {foreach $class.locations as $location}
                                    {def $locationNode = fetch( content, node, hash( 'node_id', $location ))}
                                    <p>
                                        {if $storages|contains($location)}
                                        <a href={$locationNode.url_alias|ezurl}><strong>{$locationNode.name|wash()} ({$location})</strong></a>
                                        {else}
                                        <input type="checkbox" name="SelectedLocation[]" value="{$location}" />
                                        <a href={$locationNode.url_alias|ezurl}>{$locationNode.name|wash()} ({$location})</a>                                    
                                        {/if}
                                        
                                    </p>
                                    {undef $locationNode}
                                {/foreach}                        
                                <input class="button" type="submit" name="RemoveLocationButton" value="{'Rimuovi selezionate'|i18n('ocsharecontent')}" />                        
                            {/if}
                            <input class="button" type="submit" name="AddLocationButton" value="{'Aggiungi'|i18n('ocsharecontent')}" />
                        </form>
                    </td>
                    <td>                        
                        <form action={concat( 'sharing/filters/', $source.identifier, '/', $class.class_identifier )|ezurl()} name="selectLcations" method="post">
                            {if count( $class.users )|gt(0)}
                            {foreach $class.users as $user}
                                {def $userObject = fetch( content, object, hash( 'object_id', $user ))}                                
                                <p>                                    
                                    <input type="checkbox" name="SelectedUser[]" value="{$user}" /> {$userObject.name|wash()}
                                </p>
                                {undef $userObject}
                            {/foreach}                        
                            <input class="button" type="submit" name="RemoveUserButton" value="{'Rimuovi selezionati'|i18n('ocsharecontent')}" />
                            {else}
                            <p>Qualisasi utente</p>
                            {/if}
                            <input class="button" type="submit" name="AddUserButton" value="{'Aggiungi limitazione'|i18n('ocsharecontent')}" />
                        </form>
                        
                    </td>
                </tr>
            {/foreach}
        </table>
    {/foreach}
    
</div>