{ezscript_require(array( 'ezjsc::jquery' ) )}
<script type="text/javascript">
{literal}
//<![CDATA[
$(function() {
   
    var value = 0;
    $('input.submit').click(function(){
      value = $(this).attr('name');
      value = value.replace( 'SaveItem[', '' );
      value = value.replace( ']', '' );
      $('#button_action').val(value);
      $(this).hide();
      var spinner = $('<div class="spinner hide">Loading...</div>');
      spinner.show().insertAfter( $(this) );
      $.ajax( {
          type: "POST",
          url: $('#dashboard-form').attr( 'action' ),
          data: $('#dashboard-form').serialize(),
          success: function( response ) {
              var orginalColor = $('#activity-'+response).css( 'background-color' );
              $('#activity-'+response).animate({backgroundColor:"yellow"},  1000);
              $('#activity-'+response).animate({backgroundColor:orginalColor},  1000);
              $('.spinner', $('#activity-'+response) ).hide();
          }
      });
      return false;
    });
});
//]]>{/literal}
</script>

{def $sort_order = 'asc'}
{if is_set( $view_parameters.sort_order )}
    {switch match=$view_parameters.sort_order}
        {case match='asc'}
            {set $sort_order = 'asc'}
        {/case}
        {case}
            {set $sort_order = 'desc'}
        {/case}
    {/switch}
{/if}

{def $sort_by = hash( 'name', $sort_order )}
{if is_set( $view_parameters.sort_by )}
    {switch match=$view_parameters.sort_by}
        {case}
        {/case}
    {/switch}
{/if}

{def $search_text = ''}
{if ezhttp_hasvariable( 'SearchText', 'get' )}
    {set $search_text = ezhttp( 'SearchText', 'get' )}
{/if}

{def $activeFacetParameters = array()
     $filterParameters = array()
     $forceActiveFacet = false()}

{if or( ezhttp_hasvariable( 'activeFacets', 'get' ), ezhttp_hasvariable( 'filter', 'get' ), ezhttp_hasvariable( 'dateFilter', 'get' ), $search_text|ne( '' ) )}
    {if ezhttp_hasvariable( 'activeFacets', 'get' )}
        {set $activeFacetParameters = ezhttp( 'activeFacets', 'get' )}
    {/if}
    {set $filterParameters = fetch( 'ezfind', 'filterParameters' )} 
{/if}

{def $dateFilter=0}
{if ezhttp_hasvariable( 'dateFilter', 'get' )}
    {set $dateFilter = ezhttp( 'dateFilter', 'get' )}
    {switch match=$dateFilter}
        {case match=1}
            {def $dateFilterLabel="Last day"|i18n("design/standard/content/search")}
        {/case}
        {case match=2}
            {def $dateFilterLabel="Last week"|i18n("design/standard/content/search")}
        {/case}
        {case match=3}
            {def $dateFilterLabel="Last month"|i18n("design/standard/content/search")}
        {/case}
        {case match=4}
            {def $dateFilterLabel="Last three months"|i18n("design/standard/content/search")}
        {/case}
        {case match=5}
            {def $dateFilterLabel="Last year"|i18n("design/standard/content/search")}
        {/case}
    {/switch}
{/if}

{def $page_limit = 12
     $facetLimit = 100
     $classes = array( 'luogo' )
     $storageNodeId = ezini( 'NodeSettings', 'RootNode', 'content.ini' )
     $storageNode = fetch( 'content', 'node', hash( 'node_id', $storageNodeId ) )
     $defaultSearchFacets = array(
        hash( 'field', 'meta_path_si', 'name', 'Comunita', 'limit', $facetLimit )
     ) 
     $search_hash = hash( 'subtree_array', array( $storageNodeId ),
                          'query', $search_text,
                          'class_id', $classes,
                          'ignore_visibility', true(),
                          'facet', $defaultSearchFacets,
                          'filter', $filterParameters,
                          'sort_by', $sort_by,
                          'publish_date', $dateFilter,
                          'offset', $view_parameters.offset,
                          'limit', $page_limit)
     $search = fetch( ezfind, search, $search_hash )
     $search_result = $search['SearchResult']
     $search_count = $search['SearchCount']
     $search_extras = $search['SearchExtras']
     $search_data = $search
     $view_parameter_text = ''
     $comunita = fetch( 'content', 'list', hash( 'parent_node_id', 2, 'class_filter_type', 'include', 'class_filter_array', array( 'comunita' ), 'sort_by', array( 'name', 'asc' ) ) )     
     $locations = fetch( 'content', 'tree', hash( 'parent_node_id', 2621, 'class_filter_type', 'include', 'class_filter_array', array( 'tipo_luogo' ), 'sort_by', array( 'name', 'asc' ) ) )     
     $currentLocations = array()
     $comunitaNodeIDs = array()
}

{foreach $comunita as $c}
    {set $comunitaNodeIDs = $comunitaNodeIDs|append( $c.node_id )}
{/foreach}

{def $baseURI=concat( 'massiveedit/luogo?SearchText=', $search_text )}

{def $uriSuffix = ''}

{foreach $activeFacetParameters as $facetField => $facetValue}
    {set $uriSuffix = concat( $uriSuffix, '&activeFacets[', $facetField, ']=', $facetValue )}
{/foreach}

{foreach $filterParameters as $name => $value}
    {set $uriSuffix = concat( $uriSuffix, '&filter[]=', $name, ':', $value )}
{/foreach}

{if gt( $dateFilter, 0 )}
    {set $uriSuffix = concat( $uriSuffix, '&dateFilter=', $dateFilter )}
{/if}

{foreach $view_parameters as $key => $item}
    {if $item}
        {set $view_parameter_text = concat($view_parameter_text,'/(',$key,')/',$item)}
    {/if}
{/foreach}

<div class="attribute-header">
    <h1>Luoghi trovati {$search_count}</h1>
</div>

<div class="class-sharedashboard sharedashboard-noextrainfo">
    <div class="columns-sharedashboard float-break">

        <div class="main-column-position">
            <div class="main-column float-break">
                <div class="border-box">
                <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
                <div class="border-ml"><div class="border-mr"><div class="border-mc float-break">

                    {include name=navigator
                            uri='design:navigator/google.tpl'
                            page_uri='/massiveedit/luogo'
                            page_uri_suffix=concat('?SearchText=',$search_text|urlencode, $uriSuffix )
                            item_count=$search_count
                            view_parameters=$view_parameters
                            item_limit=$page_limit}

                    {if $search_count|gt(0)}
                    <form id="dashboard-form" method="post" action={concat('massiveedit/luogo',$view_parameter_text)|ezurl()}>
                    <table cellspacing="0" cellpadding="0" border="0" class="list float-break">
                        <tbody>
                        <tr>
                            <th>Nome</th>
                            <th>Data</th>                            
                            <th>Autore</th>
                            <th>Tipo di luogo</th>
                            <th>Geo</th>
                            <th class="tight">Edit</th>
                            <th class="tight">Delete</th>
                            <th class="tight"></th>
                        </tr>
                        {foreach $search_result as $child sequence array( 'bglight', 'bgdark' ) as $style}
                            {*<tr id="activity-{$child.node_id}-name" class="{$style}">
                                <td colspan="7">
                                    <strong>Title:</strong> <a class="activity-title" href={$child.url_alias|ezurl()}>{$child.name|wash()}</a>
                                    <input type="hidden" value="EditItem" name="Action" />
                                </td>
                            </tr>*}
                            <tr id="activity-{$child.node_id}" class="{$style} noborder">
                                <td>
                                    
                                    {if $child.is_hidden}
                                        {$child.name|wash()} <br /><small><em>(future)</em></small>
                                    {else}
                                        <strong><a class="activity-title" href={$child.url_alias|ezurl()}>{$child.name|wash()}</a></strong>
                                    {/if}
                                    
                                    {if $child|has_abstract()}
                                        <br/>{$child|abstract()|openpa_shorten(100)}
                                    {/if}
                                    <input type="hidden" value="EditItem" name="Action" />
                                </td>

                                <td>                                    
                                    {$child.object.published|l10n('shortdatetime')}                                
                                </td>

                                <td>
                                    {if is_set( $child.object.owner )}
                                        {$child.object.owner.name|wash()}
                                    {/if}
                                </td>

                                <td>
                                    {def $tmpArray = array()}
                                    {set $currentLocations = $child.data_map.tipo_luogo.content.relation_list}
                                    {foreach $currentLocations as $currentLocation}
                                        {set $tmpArray = $tmpArray|append( $currentLocation.contentobject_id )}
                                    {/foreach}
                                    <select name="SelectedRelationId[{$child.node_id}]">
                                        <option value="0">--</option>

                                    {foreach $locations as $location}                                        
                                        <option
                                        
                                        {if $tmpArray|contains( $location.contentobject_id )}
                                            selected="selected"
                                        {/if}
                                        value="{$location.contentobject_id}" /> {$location.name|wash} </option>                                        
                                    {/foreach}
                                    </select>
                                    {undef $tmpArray}
                                </td>
                                
                                <td>
                                    {$child.data_map.geo.content.latitude} - {$child.data_map.geo.content.longitude}
                                </td>

                                <td>
                                    {if $child.can_edit}
                                        <input type="image" src={"websitetoolbar/ezwt-icon-edit.png"|ezimage} value="Edit" name="EditNode[{$child.contentobject_id}]" class="button">
                                    {else}
                                        Published
                                    {/if}
                                </td>
                                <td>
                                    {if $child.can_remove}
                                        <input type="image" src={"websitetoolbar/ezwt-icon-remove.png"|ezimage} value="Edit" name="RemoveNode[{$child.node_id}]" class="button">
                                    {/if}
                                </td>

                                <td>
                                    
                                    <input type="submit" value="Save" name="SaveItem[{$child.node_id}]" class="button submit">
                                    
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                    
                    <input type="hidden" name="ButtonAction" id="button_action" />
                    <input type="hidden" name="RedirectURIAfterPublish" value={concat('massiveedit/luogo', $view_parameter_text, '?SearchText=',$search_text|urlencode, $uriSuffix )|ezurl()} />
                    <input type="hidden" name="RedirectIfDiscarded" value={concat('massiveedit/luogo', $view_parameter_text, '?SearchText=',$search_text|urlencode, $uriSuffix )|ezurl()} />
                    <input type="hidden" name="RedirectURIAfterRemove" value={concat('massiveedit/luogo', $view_parameter_text, '?SearchText=',$search_text|urlencode, $uriSuffix )|ezurl()} />
                    <input type="hidden" name="RedirectIfCancel" value={concat('massiveedit/luogo', $view_parameter_text, '?SearchText=',$search_text|urlencode, $uriSuffix )|ezurl()} />
                    </form>
                    {else}
                        <div class="warning">Nessun risultato</div>
                    {/if}

                    {include name=navigator
                            uri='design:navigator/google.tpl'
                            page_uri='/massiveedit/luogo'
                            page_uri_suffix=concat('?SearchText=',$search_text|urlencode, $uriSuffix )
                            item_count=$search_count
                            view_parameters=$view_parameters
                            item_limit=$page_limit}
                
                </div></div></div>
                <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
                </div>
            </div>
        </div>

        
        <div class="extrainfo-column-position">
            <div class="extrainfo-column">
                <div class="border-box">
                <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
                <div class="border-ml"><div class="border-mr"><div class="border-mc float-break spalla-list-art">
                
                    {def $activeFacetsCount=0}
                    {if or( $forceActiveFacet, ezhttp_hasvariable( 'activeFacets', 'get' ), ezhttp_hasvariable( 'dateFilter', 'get' ) )}
                    
                    <div class="mlt-activity">
                        <h2>Stai filtrando per</h2>
                    </div>
                    
                    <ul id="active-facets-list">
                    {foreach $defaultSearchFacets as $key => $defaultFacet}                    
                        {if array_keys( $activeFacetParameters )|contains( concat( $defaultFacet['field'], ':', $defaultFacet['name']  ) )}
                            {foreach $search_extras.facet_fields.$key.nameList as $key2 => $facetName}
                                {if eq( $activeFacetParameters[concat( $defaultFacet['field'], ':', $defaultFacet['name'] )], $facetName )}
                                    {set $activeFacetsCount=sum( $key, 1 )}
                                    {def $suffix=$uriSuffix|explode( concat( '&filter[]=', $search_extras.facet_fields.$key.queryLimit[$key2]|wash ) )|implode( '' )|explode( concat( '&activeFacets[', $defaultFacet['field'], ':', $defaultFacet['name'], ']=', $facetName ) )|implode( '' )}
                                    <li>
                                        
                                        {if ezhttp( 'activeFacets', 'get', 'hasVariable' )}
                                        <a href={concat( $baseURI, $suffix )|ezurl}>[x]</a>
                                        {/if}
                                        
                                        <strong>{$defaultFacet['name']}</strong>:
                                        
                                        {if or( $defaultFacet['field']|contains( 'node_id_si' ), $defaultFacet['field']|contains( 'path_si' ) )}
                                            {fetch( 'content', 'node', hash( 'node_id', $facetName ) ).name|wash()}
                                        {elseif $defaultFacet['field']|contains( 'id_si' )}
                                            {fetch( 'content', 'object', hash( 'object_id', $facetName ) ).name|wash()}
                                        {else}
                                            {$facetName}
                                        {/if}
                                        
                                    {undef $suffix}
                                    </li>
                                {/if}
                            {/foreach}
                        {/if}
                    {/foreach}

                    {if gt( $dateFilter, 0 )}
                        <li>
                            {set $activeFacetsCount=$activeFacetsCount|inc}
                            {def $suffix=$uriSuffix|explode( concat( '&dateFilter=', $dateFilter ) )|implode( '' )}
                            <a href={concat( $baseURI, $suffix )|ezurl}>[x]</a> <strong>{'Creation time'|i18n( 'extension/ezfind/facets' )}</strong>: {$dateFilterLabel}
                            {undef $suffix}
                        </li>
                    {/if}

                    {if or( ge( $activeFacetsCount, 2 ), and( ezhttp_hasvariable( 'activeFacets', 'get' ), $activeFacetsCount|eq(0) ) )}
                        <li>
                            <a href={$baseURI|ezurl}>[x]</a> <em>{'Clear all'|i18n( 'extension/ezfind/facets' )}</em>
                        </li>
                    {/if}
                    </ul>

                    {/if}
                    
                    {foreach $defaultSearchFacets as $key => $defaultFacet}
                        {if array_keys( $activeFacetParameters )|contains( concat( $defaultFacet['field'], ':', $defaultFacet['name']  ) )|not}
                          {def $facetData=$search_extras.facet_fields.$key}
                            <h2>Filtra per {$defaultFacet['name']}</h2>
                            <ul>
                              
                              {foreach $facetData.nameList as $key2 => $facetName}
                              {if ne( $key2, '' )}
                              
                                {def $do = true()}
                              
                                {if $defaultFacet['field']|eq( 'meta_path_si' )}
                                    
                                    {if $comunitaNodeIDs|contains( $facetName )|not()}                                        
                                        {set $do = false()}
                                    {/if}
                                    
                                {/if}
                              
                                {if $do}
                                    <li>
                                        <a href={concat( $baseURI, '&filter[]=', $facetData.queryLimit[$key2]|wash, '&activeFacets[', $defaultFacet['field'], ':', $defaultFacet['name'], ']=', $facetName, $uriSuffix )|ezurl}>                                                                                
                                          {if or( $defaultFacet['field']|contains( 'node_id_si' ), $defaultFacet['field']|contains( 'path_si' ) )}
                                              {fetch( 'content', 'node', hash( 'node_id', $facetName ) ).name|wash()}
                                          {elseif $defaultFacet['field']|contains( 'id_si' )}
                                              {fetch( 'content', 'object', hash( 'object_id', $facetName ) ).name|wash()}
                                          {else}
                                              {$facetName}
                                          {/if}
                                        </a> ({$facetData.countList[$key2]})
                                    </li>
                                {/if}
                                
                                {undef $do}
                                
                              {/if}
                              {/foreach}
                            </ul>
                            {undef $facetData}
                        {/if}
                    {/foreach}

                    {if eq( $dateFilter, 0 )}
                        <h2>Filtra per {'Creation time'|i18n( 'extension/ezfind/facets' )}</h2>
                        <ul>
                            <li>
                                <a href={concat( $baseURI, '&dateFilter=1', $uriSuffix )|ezurl}>{"Last day"|i18n("design/standard/content/search")}</a>
                            </li>
                            <li>
                                <a href={concat( $baseURI, '&dateFilter=2', $uriSuffix )|ezurl}>{"Last week"|i18n("design/standard/content/search")}</a>
                            </li>
                            <li>
                                <a href={concat( $baseURI, '&dateFilter=3', $uriSuffix )|ezurl}>{"Last month"|i18n("design/standard/content/search")}</a>
                            </li>
                            <li>
                                <a href={concat( $baseURI, '&dateFilter=4', $uriSuffix )|ezurl}>{"Last three months"|i18n("design/standard/content/search")}</a>
                            </li>
                            <li>
                                <a href={concat( $baseURI, '&dateFilter=5', $uriSuffix )|ezurl}>{"Last year"|i18n("design/standard/content/search")}</a>
                            </li>
                        </ul>
                    {/if}

                    <h2>Cerca</h2>
                    <div class="block">
                    <form method="get" action={concat('massiveedit/luogo', $view_parameter_text )|ezurl()}>
                        <input size="23" type="text" value="{$search_text|wash}" name="SearchText" />
                        <input type="submit" class="defaultbutton" value="Search" />
                        {foreach $uriSuffix|explode( '&' ) as $us}
                            {def $nV = $us|explode( '=' ) }
                            {if and( is_set( $nV[0]), is_set( $nV[1]) )}
                                <input type="hidden" name="{$nV[0]}" value="{$nV[1]}" />
                            {/if}
                            {undef $nV}
                        {/foreach}
                    </form>
                    </div>

                </div></div></div>
                <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
                </div>
            </div>
        </div>
        

    </div>
</div>