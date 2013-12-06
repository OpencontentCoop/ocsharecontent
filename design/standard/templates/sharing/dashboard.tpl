{def $page_limit = cond( ezini_hasvariable( 'Dashboard', 'PageLimit', 'sharecontent.ini' ), ezini( 'Dashboard', 'PageLimit', 'sharecontent.ini' ), 10 )
     $search = fetch( ezfind, search,
                        hash( 'query', $search_text,
                              'offset', $view_parameters.offset,
                              'limit', $page_limit,
                              'sort_by', hash( 'published', 'desc' ),
                              'class_id', $itemClasses,
                              'facet', $defaultSearchFacets,
                              'filter', $filterParameters,
                              'publish_date', $dateFilter,
                              'subtree_array', $subtreearray,
                              'sort_by', hash( published, desc )
                             ) )
     $search_result = $search['SearchResult']
     $search_count = $search['SearchCount']
     $search_extras = $search['SearchExtras']
     $search_data = $search}

{def $baseURI=concat( 'sharing/dashboard?SearchText=', $search_text )}

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

{if $storageFilters|count()|gt( 0 )}
    {set $uriSuffix = concat( $uriSuffix, '&storageFilter[]=', $storageFilters|implode( '&storageFilter[]=' ) )}
{/if}

<div class="class-sharedashboard extrainfo">
    <div class="columns-sharedashboard float-break">
        <div class="main-column-position">
            <div class="main-column float-break">
                <div class="border-box">
                <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
                <div class="border-ml"><div class="border-mr"><div class="border-mc float-break">

                        <div class="content-view-full">
                            <div class="class-blog-post float-break">
                                
                                <div class="attribute-header">
                                    <h1>Pannello condivisioni</h1>
                                </div>
                                
                                {include name=Navigator
                                    uri='design:navigator/google.tpl'
                                    page_uri='/sharing/dashboard'
                                    page_uri_suffix=concat('?SearchText=',$search_text|urlencode, $uriSuffix )
                                    item_count=$search_count
                                    view_parameters=$view_parameters
                                    item_limit=$page_limit}

                                {if $search_result }
                                    {foreach $search_result as $result sequence array( 'odd', 'even' ) as $style}
                                    <form method="post" action={'sharing/dashboard'|ezurl()}>
                                        {node_view_gui style=$style view=dashboard_line content_node=$result}
                                        <input type="hidden" name="NodeID" value="{$result.node_id}" />
                                        <input type="hidden" name="ObjectID" value="{$result.contentobject_id}" />
                                        <input type="hidden" name="StorageNodes" value="{$subtreearray|implode('-')}" />
                                        <input type="hidden" name="PageUriSuffix" value="{concat('?SearchText=',$search_text|urlencode, $uriSuffix )}" />
                                        {foreach $view_parameters as $key => $item}
                                            {if $item}
                                                <input type="hidden" name="view_parameters[{$key}]" value="{$item}" />
                                            {/if}
                                        {/foreach}

                                    </form>
                                    {/foreach}
                                {else}
                                    <div class="warning message-warning">Nessun contenuto trovato</div>
                                {/if}
                                
                                {include name=Navigator
                                    uri='design:navigator/google.tpl'
                                    page_uri='/sharing/dashboard'
                                    page_uri_suffix=concat('?SearchText=',$search_text|urlencode, $uriSuffix )
                                    item_count=$search_count
                                    view_parameters=$view_parameters
                                    item_limit=$page_limit}
                            </div>
                        </div>

                </div></div></div>
                <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
                </div>
            </div>
        </div>

        <div class="extrainfo-column-position">
            <div class="extrainfo-column">
                <div class="border-box">
                <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
                <div class="border-ml"><div class="border-mr"><div class="border-mc float-break">
                                        
                    <div class="block float-break">
                        <h2>{"Filtri di pubblicazione"|i18n("ocsharecontent")}:</h2>                        
                        {if $source_count|eq(1)}
                            <p>Cè un filtro attivo</p>
                        {elseif $source_count|gt(1)}
                            <p>Ci sono {$source_count} filtri attivi</p>
                        {/if}
                        <p><a href={'sharing/filters'|ezurl()}>Configura filtri di pubblicazione</a></p>
                    </div>
                    
                    
                    {def $user_list = fetch( 'user', 'logged_in_list', hash( 'sort_by', array( array( 'login', true() ) ) ) )}
                    {if count( $user_list )|gt( 0 )}
                    <div class="distance float-break">
                    <h2>{"Utenti loggati"|i18n("ocsharecontent")}:</h2>
                    <ul>
                    {foreach $user_list as $user}
                        <li>{$user|upword}</li>
                    {/foreach}
                    </ul>
                    </div>
                    {/if}
                    
                    {if $search_result }
                    {def $sections=fetch( 'content', 'section_list' )}
                    <div class="distance float-break">
                        <h2>{"Filtra contenuti"|i18n("ocsharecontent")}:</h2>
                        {def $activeFacetsCount=0}
                        <ul id="active-facets-list">
                        {foreach $defaultSearchFacets as $key => $defaultFacet}
                            {if array_keys( $activeFacetParameters )|contains( concat( $defaultFacet['field'], ':', $defaultFacet['name']  ) )}
                                {def $facetData=$search_extras.facet_fields.$key}
              
                                {foreach $facetData.nameList as $key2 => $facetName}
                                    {def $calcolate_name = false()}
                                    {if is_numeric( $facetName )}
                                        {set $calcolate_name = true()}        
                                    {/if}
                                    {if eq( $activeFacetParameters[concat( $defaultFacet['field'], ':', $defaultFacet['name'] )], $facetName )}
                                        
                                        {set $activeFacetsCount=sum( $key, 1 )}
                                        
                                        {def $suffix=$uriSuffix|explode( concat( '&filter[]=', $facetData.queryLimit[$key2]|wash ) )|implode( '' )|explode( concat( '&activeFacets[', $defaultFacet['field'], ':', $defaultFacet['name'], ']=', $facetName ) )|implode( '' )}
                                        <li>
                                            <a href={concat( $baseURI, $suffix )|ezurl}>[x]</a> <strong>{$defaultFacet['name']}</strong>:
                                            {if $calcolate_name}
                                                {if $facetData.queryLimit[$key2]|begins_with( 'meta_section_id_si' )}
                                                    {foreach $sections as $section}
                                                        {if $section.id|eq($facetName)}
                                                            {$section.name}
                                                        {/if}
                                                    {/foreach}
                                                {else}
                                                    {fetch( 'content', 'object', hash( 'object_id', $facetName ) ).name|wash()}
                                                {/if}
                                            {else}
                                                {$facetName}
                                            {/if}
                                        </li>
                                    {/if}
                                    {undef $calcolate_name}
                                {/foreach}
                                
                                {undef $facetData $suffix}
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
                        
                        {if and( $storageFilters|count()|gt( 0 ), $storages|count()|gt( 1 ) )}
                            <li>
                                {set $activeFacetsCount=$activeFacetsCount|inc}
                                {foreach $storageFilters as $storageFilter}
                                    {def $suffix=$uriSuffix|explode( concat( '&storageFilter[]=', $storageFilter ) )|implode( '' )}
                                    <a href={concat( $baseURI, $suffix )|ezurl}>[x]</a> <strong>{'Storage'|i18n( 'ocsharecontent' )}</strong>:
                                    {foreach $storages as $storage}
                                        {if $storage.node_id|eq( $storageFilter )}
                                            <a href="{$storage.url_alias|ezurl(no)}">{$storage.name|wash()}</a>
                                        {/if}
                                    {/foreach}
                                    {undef $suffix}
                                {/foreach}
                            </li>
                        {/if}
              
                        {if ge( $activeFacetsCount, 2 )}
                            <li>
                                <a href={$baseURI|ezurl}>[x]</a> <em>{'Clear all'|i18n( 'extension/ezfind/facets' )}</em>
                            </li>
                        {/if}
                        </ul>
              
                        <ul id="facet-list">
                        {if and( $storageFilters|count()|gt( 0 ), $storages|count()|gt( 1 ) )}
                            <li>
                                <span ><strong>{'Storage filter'|i18n( 'ocsharecontent' )}</strong></span>
                                <ul>
                                {foreach $storages as $storage_node}
                                    <li>
                                        <a href={concat( $baseURI, '&storageFilter[]=', $storage_node.node_id, $uriSuffix )|ezurl}>{$storage_node.name|wash()}</a>
                                    </li>
                                {/foreach}
                                </ul>
                            </li>
                        {/if}
                            
                        {foreach $defaultSearchFacets as $key => $defaultFacet}
                            {if array_keys( $activeFacetParameters )|contains( concat( $defaultFacet['field'], ':', $defaultFacet['name']  ) )|not}
                            <li>
                              {def $facetData=$search_extras.facet_fields.$key}
                                <span ><strong>{$defaultFacet['name']}</strong></span>
                                <ul>
                                  {foreach $facetData.nameList as $key2 => $facetName}
                                    {if ne( $key2, '' )}
                                        {def $calcolate_name = false()}
                                        {if is_numeric( $facetName )}
                                            {set $calcolate_name = true()}        
                                        {/if}
                                        <li>
                                            <a href={concat( $baseURI, '&filter[]=', $facetData.queryLimit[$key2]|wash, '&activeFacets[', $defaultFacet['field'], ':', $defaultFacet['name'], ']=', $facetName, $uriSuffix )|ezurl}>
                                                
                                            {if $calcolate_name}
                                                {if $facetData.queryLimit[$key2]|begins_with( 'meta_section_id_si' )}
                                                    {foreach $sections as $section}
                                                        {if $section.id|eq($facetName)}
                                                            {$section.name}
                                                        {/if}
                                                    {/foreach}
                                                {else}
                                                    {fetch( 'content', 'object', hash( 'object_id', $facetName ) ).name|wash()}
                                                {/if}
                                            {else}
                                                {$facetName}
                                            {/if}
                                            
                                            </a> ({$facetData.countList[$key2]})
                                        </li>
                                        {undef $calcolate_name}
                                    {/if}
                                  {/foreach}
                                </ul>
                                {undef $facetData}
                            </li>
                            {/if}
                        {/foreach}
              
                        {if eq( $dateFilter, 0 )}
                            <li>
                                <span ><strong>{'Creation time'|i18n( 'extension/ezfind/facets' )}</strong></span>
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
                            </li>
                         {/if}                    
                        </ul>
                    </div>
                    {/if}
                    
                </div></div></div>
                <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
                </div>
            </div>
        </div>
    </div>
</div>