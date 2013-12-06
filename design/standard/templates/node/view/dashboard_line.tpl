{run-once}
{ezscript_require( 'ezjsc::jquery' )}
{literal}
<script type="application/x-javascript">
$(document).ready(function(){
    $('.showdetail').bind( 'click', function(e){
        var detail = $(this).parents( '.dasboardline' ).find( '.dasboardline-detail' );
        detail.toggle();
        e.preventDefault();
    });
});
</script>
{/literal}
{/run-once}

{def $display_datatypes = cond( ezini_hasvariable( 'DisplayItem', 'DataTypes', 'sharecontent.ini' ), ezini( 'DisplayItem', 'DataTypes', 'sharecontent.ini' ), array() )
     $display_attributes = cond( ezini_hasvariable( 'DisplayItem', 'Attributes', 'sharecontent.ini' ), ezini( 'DisplayItem', 'Attributes', 'sharecontent.ini' ), array() )
     $display_attributes_per_class = cond( ezini_hasvariable( concat( 'DisplayItem_', $node.class_identifier ), 'Attributes', 'sharecontent.ini' ), ezini( concat( 'DisplayItem_', $node.class_identifier ), 'Attributes', 'sharecontent.ini' ), array() )
     $object = $node.object
     $sections = $object.allowed_assign_section_list
     $currentSectionName = 'unknown'}
<div class="{$style} float-break dasboardline">
    <div class="col-content"><div class="col-content-design">
        <div class="class-{$node.object.class_identifier} float-break">                    
            
            <div class="object-right"><a href="#" class="showdetail button"><small>{"Mostra/nascondi dettagli"|i18n("ocsharecontent")}</small></a></div>
            <strong><small>{$node.class_name}</small></strong>
            <h4> {$node.name|wash()}</h4>            
            <div class="dasboardline-detail hide">
                <a title="{"Anteprima"|i18n("ocsharecontent")}" href="{$node.url_alias|ezurl(no)}">Vedi anteprima</a>
                <table cellspacing="0" cellpadding="2" border="0" class="renderedtable">
                    <tr class="bglight">
                        <td class="text-right" style="width: 200px">
                            <strong>Data di pubblicazione</strong>
                        </td>
                        <td>
                            {$node.object.published|datetime( 'custom', '%d %F %Y' )}
                        </td>
                    </tr>
                
                {def $label = true()}
                {foreach $node.object.contentobject_attributes as $attribute sequence array( 'bgdark', 'bglight' ) as $_style}
                {if and( or(
                    $display_datatypes|contains( $attribute.data_type_string ),
                    $display_attributes|contains( $attribute.contentclass_attribute_identifier ),
                    $display_attributes_per_class|contains( $attribute.contentclass_attribute_identifier )
                    ), $attribute.has_content )}
                    <tr class="{$_style} attribute-{$attribute.contentclass_attribute_identifier}">
                        <td class="text-right">
                            <strong>{$attribute.contentclass_attribute_name}</strong>
                        </td>
                        <td>
                            {attribute_view_gui attribute=$attribute}
                        </td>
                    </tr>
                {/if}
                {/foreach}
                </table>
            </div>
            
            <div class="dashboard-action">                
                <fieldset>                                        
                    <div class="block">
                        <div class="split-2">
                            <ul class="text-left nobullet">
                                {def $removable = false()}
                                {foreach $node.object.assigned_nodes as $assigned_node}
                                    {def $parent = $assigned_node.parent}
                                    {if ezini( 'Storage', 'Classes', 'sharecontent.ini' )|contains( $parent.class_identifier )|not()}
                                    <li>
                                        {if $assigned_node.can_remove_location}
                                            <input type="checkbox" name="SelectedLocation[]" value="{$assigned_node.node_id}" />
                                            {set $removable = true()}
                                            {if $node.object.main_node_id|eq( $assigned_node.node_id )}
                                                <a href={$parent.url_alias|ezurl()}><strong>{$parent.name|wash()}</strong></a>
                                            {else}
                                                <a href={$parent.url_alias|ezurl()}>{$parent.name|wash()}</a>
                                            {/if}
                                        {/if}                                        
                                    </li>
                                    {/if}
                                    {undef $parent}
                                {/foreach}
                                                                                       
                                {if $removable}                        
                                    <input type="submit" name="RemoveSelectedLocationsButton" class="defaultbutton" value="{"Rimuovi collocazioni"|i18n("ocsharecontent")}" />                                    
                                {/if}
                                <input type="submit" name="RemoveFromStorageAndAddLocationButton" class="defaultbutton" value="{"Colloca in.."|i18n("ocsharecontent")}" />                                
                                {if $node.can_remove}
                                <input type="submit" name="RemoveFromStorageButton" class="button" value="{"Rimuovi dal pannello"|i18n("ocsharecontent")}" />                                
                                {/if}
                                
                                {if $node.can_edit}
                                    <input type="submit" name="EditButton" class="button" value="{"Modifica"|i18n("ocsharecontent")}" />
                                {/if}
                                {undef $removable}
                            </ul>
                        </div>
                        
                        <div class="split-2">
                            <div class="block">
                                {foreach $sections as $sectionItem }
                                    {if eq( $sectionItem.id, $object.section_id )}
                                        {set $currentSectionName=$sectionItem.name}
                                    {/if}
                                {/foreach}
                                <label>{"Section"|i18n( 'design/admin/node/view/full' )}</label>
                                <select name="SelectedSectionId">
                                {foreach $sections as $section}
                                    {if eq( $section.id, $object.section_id )}
                                    <option value="{$section.id}" selected="selected">{$section.name|wash}</option>
                                    {else}
                                    <option value="{$section.id}">{$section.name|wash}</option>
                                    {/if}
                                {/foreach}
                                </select>
                                <input type="submit" value="{'Choose section'|i18n( 'design/admin/node/view/full' )}" name="SectionEditButton" class="button" />
                            </div>
                    
                            {foreach $object.allowed_assign_state_list as $allowed_assign_state_info}
                            <div class="block">
                                <label for="SelectedStateIDList_{$object.id}">{$allowed_assign_state_info.group.current_translation.name|wash}</label>
                                <select id="SelectedStateIDList_{$object.id}" name="SelectedStateIDList[]" {if $allowed_assign_state_info.states|count|eq(1)}disabled="disabled"{/if}>
                                {if $allowed_assign_state_info.states}
                                    {set $enable_StateEditButton = true()}
                                {/if}
                                {foreach $allowed_assign_state_info.states as $state}
                                    <option value="{$state.id}" {if $object.state_id_array|contains($state.id)}selected="selected"{/if}>{$state.current_translation.name|wash}</option>
                                {/foreach}
                                </select>
                                <input type="submit" value="{'Set'|i18n( 'design/admin/node/view/full' )}" name="StateEditButton" class="button" />
                            </div>
                            {/foreach}
                        </div>
                    
                </fieldset>
            </div>
            
        </div>
    </div></div>
</div>