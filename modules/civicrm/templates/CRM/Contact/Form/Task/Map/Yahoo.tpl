{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{if $showDirectly}
    {assign var=height value="350px"}
    {assign var=width  value="450px"}
{else}	
    {assign var=height value="600px"}
    {assign var=width  value="100%"}
{/if}
{literal}
<script type="text/javascript" src="http://api.maps.yahoo.com/ajaxymap?v=3.0&appid={/literal}{$mapKey}{literal}"></script>
<style type="text/css">
    #mapContainer {
        height: {/literal}{$height}{literal}; 
        width : {/literal}{$width}{literal}; 
    } 
    #mapContainer table {
        border: none;
    }         
    #mapContainer td {
        padding: 0px;
        vertical-align: top;
        white-space: nowrap;
    }
</style>
{/literal}

<div id="mapContainer"></div>
{literal}
<script type="text/javascript">

    /* Create a lat/lng object */
    var myPoint = new YGeoPoint({/literal}{$center.lat},{$center.lng}{literal});

    /* Create a map object */
    var map = new  YMap(document.getElementById('mapContainer'));
    {/literal}{if !$showDirectly}{literal}
	/* Add a pan control */
	map.addPanControl();
    {/literal}{/if}{literal}
    /* Add map type */
    map.addTypeControl();

    /* Add a slider zoom control */
    map.addZoomLong();

    /* Display the map centered on a latitude and longitude */
    /* map.drawZoomAndCenter(myPoint,13); */

    function createYahooMarker(geopoint, data, img) { 
        var myImage  = new YImage(); 
        myImage.src  = 'http://us.i1.yimg.com/us.yimg.com/i/us/map/gr/mt_ic_cw.gif'; 
        myImage.size = new YSize(20,20); 
        myImage.offsetSmartWindow = new YCoordPoint(0,0); 
        var marker   = new YMarker(geopoint,myImage); 
        var label    = img;
        marker.addLabel(label);
	YEvent.Capture(marker,EventsList.MouseClick, function() { marker.openSmartWindow(data) }); 
        return marker; 
    } 

    {/literal}
    var count=0;	
    {foreach from=$locations item=location}	
        {literal} 
        var GeoPoint = new YGeoPoint({/literal}{$location.lat},{$location.lng});

        {if $location.url and ! $profileGID}
            {literal}
            var data = "{/literal}<a href='{$location.url}'>{$location.displayName}</a><br />{if !$skipLocationType}{$location.location_type}<br />{/if}{$location.address}<br /><br />Get Directions FROM:&nbsp;<input type=hidden id=to value='{$location.displayAddress}'><input id=from type=text onclick='this.focus();'/>&nbsp;<a href=\"javascript:ypopUp();\">&raquo; Go</a>";

        {else}
            {capture assign="profileURL"}{crmURL p='civicrm/profile/view' q="reset=1&id=`$location.contactID`&gid=$profileGID"}{/capture}
            {literal}
            var data = "{/literal}<a href='{$profileURL}'>{$location.displayName}</a><br />{if !$skipLocationType}{$location.location_type}<br />{/if}{$location.address}<br /><br />Get Directions FROM:&nbsp;<input type=hidden id=to value='{$location.displayAddress}'><input id=from type=text onclick='this.focus();'/>&nbsp;<a href=\"javascript:ypopUp();\">&raquo; Go</a>";
        {/if}
	{literal}
        var img  = '{/literal}{$location.image|replace:"'":"\'"}{literal}';
        var marker = createYahooMarker(GeoPoint, data, img); 
        map.addOverlay(marker); 
        count++;
        {/literal} 
    {/foreach}

    if (count>1)
        map.drawZoomAndCenter(myPoint,15);
    else
        map.drawZoomAndCenter(myPoint,5);
    {literal}
    function ypopUp() {
        var from   = document.getElementById('from').value;
        var to     = document.getElementById('to').value;	
        var URL    = "http://maps.yahoo.com/#mvt=m&q1=" + from + "&q2=" + to;
        day = new Date();
        id  = day.getTime();
        eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=780,height=640,left = 202,top = 100');");
    }
</script> 
{/literal}
