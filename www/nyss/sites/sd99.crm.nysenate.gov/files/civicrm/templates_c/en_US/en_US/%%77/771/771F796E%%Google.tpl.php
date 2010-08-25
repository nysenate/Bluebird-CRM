<?php /* Smarty version 2.6.26, created on 2010-08-20 12:19:50
         compiled from CRM/Contact/Form/Task/Map/Google.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'crmURL', 'CRM/Contact/Form/Task/Map/Google.tpl', 67, false),)), $this); ?>
<?php if ($this->_tpl_vars['showDirectly']): ?>
  <?php $this->assign('defaultZoom', 14); ?>  
  <?php $this->assign('height', '350px'); ?>
  <?php $this->assign('width', '425px'); ?>
<?php else: ?>	
  <?php $this->assign('height', '600px'); ?>
  <?php $this->assign('width', "100%"); ?>
<?php endif; ?>
<?php echo '
<script src="http://maps.google.com/maps?file=api&v=2&key='; ?>
<?php echo $this->_tpl_vars['mapKey']; ?>
<?php echo '" type="text/javascript"></script>
<script type="text/javascript">
    function initMap() {
	//<![CDATA[
	var map     = new GMap2(document.getElementById("google_map"));
	var span    = new GSize('; ?>
<?php echo $this->_tpl_vars['span']['lng']; ?>
,<?php echo $this->_tpl_vars['span']['lat']; ?>
<?php echo ');
	var center  = new GLatLng('; ?>
<?php echo $this->_tpl_vars['center']['lat']; ?>
,<?php echo $this->_tpl_vars['center']['lng']; ?>
<?php echo ');
	map.setUIToDefault();
	map.setCenter(new GLatLng( 0, 0 ), 0 );
	var bounds = new GLatLngBounds( );
	GEvent.addListener(map, \'resize\', function() { map.setCenter(bounds.getCenter()); map.checkResize(); });
	
	// Creates a marker whose info window displays the given number
	function createMarker(point, data, image) {
	    var icon = new GIcon();
 	    icon.image = image;
 	    icon.iconSize = new GSize(24, 24);
 	    icon.iconAnchor = new GPoint(12, 24);
 	    icon.infoWindowAnchor = new GPoint(18, 1);
	    var marker = new GMarker(point, icon);
	    GEvent.addListener(marker, "click", function() {
		marker.openInfoWindowHtml(data);
	    });
	    return marker;
	}
	
	'; ?>

	<?php $_from = $this->_tpl_vars['locations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['location']):
?>
	    <?php if ($this->_tpl_vars['location']['url'] && ! $this->_tpl_vars['profileGID']): ?>
		<?php echo '
		var data = "'; ?>
<a href='<?php echo $this->_tpl_vars['location']['url']; ?>
'><?php echo $this->_tpl_vars['location']['displayName']; ?>
</a><br /><?php if (! $this->_tpl_vars['skipLocationType']): ?><?php echo $this->_tpl_vars['location']['location_type']; ?>
<br /><?php endif; ?><?php echo $this->_tpl_vars['location']['address']; ?>
<br /><br />Get Directions FROM:&nbsp;<input type=hidden id=to value='<?php echo $this->_tpl_vars['location']['displayAddress']; ?>
'><input type=text id=from size=20>&nbsp;<a href=\"javascript:gpopUp();\">&raquo; Go</a>";
	    <?php else: ?>
		<?php ob_start(); ?><?php echo CRM_Utils_System::crmURL(array('p' => 'civicrm/profile/view','q' => "reset=1&id=".($this->_tpl_vars['location']['contactID'])."&gid=".($this->_tpl_vars['profileGID'])), $this);?>
<?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('profileURL', ob_get_contents());ob_end_clean(); ?>
		<?php echo '
		var data = "'; ?>
<a href='<?php echo $this->_tpl_vars['profileURL']; ?>
'><?php echo $this->_tpl_vars['location']['displayName']; ?>
</a><br /><?php if (! $this->_tpl_vars['skipLocationType']): ?><?php echo $this->_tpl_vars['location']['location_type']; ?>
<br /><?php endif; ?><?php echo $this->_tpl_vars['location']['address']; ?>
<br /><br />Get Directions FROM:&nbsp;<input type=hidden id=to value='<?php echo $this->_tpl_vars['location']['displayAddress']; ?>
'><input type=text id=from size=20>&nbsp;<a href=\"javascript:gpopUp();\">&raquo; Go</a>";
	    <?php endif; ?>
	    <?php echo '
	    var address = "'; ?>
<?php echo $this->_tpl_vars['location']['address']; ?>
<?php echo '";
	    '; ?>

	    <?php if ($this->_tpl_vars['location']['lat']): ?>
		var point  = new GLatLng(<?php echo $this->_tpl_vars['location']['lat']; ?>
,<?php echo $this->_tpl_vars['location']['lng']; ?>
);
		<?php if ($this->_tpl_vars['location']['marker_class'] == 'Individual'): ?>
 			var image = "<?php echo $this->_tpl_vars['config']->resourceBase; ?>
i/contact_ind.gif";
 		<?php endif; ?>
 		<?php if ($this->_tpl_vars['location']['marker_class'] == 'Household'): ?>
 			var image = "<?php echo $this->_tpl_vars['config']->resourceBase; ?>
i/contact_house.png";
 		<?php endif; ?>
 		<?php if ($this->_tpl_vars['location']['marker_class'] == 'Organization' || $this->_tpl_vars['location']['marker_class'] == 'Event'): ?>
 			var image = "<?php echo $this->_tpl_vars['config']->resourceBase; ?>
i/contact_org.gif";
 		<?php endif; ?>
               	var marker = createMarker(point, data, image);
		map.addOverlay(marker);
		bounds.extend(point);
	    <?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
	map.setMapType(G_NORMAL_MAP);
	map.setCenter(bounds.getCenter());
	<?php if (count ( $this->_tpl_vars['locations'] ) > 1): ?>  
 	    map.setZoom(map.getBoundsZoomLevel(bounds));
 	    map.setMapType(G_PHYSICAL_MAP);
 	<?php elseif ($this->_tpl_vars['location']['marker_class'] == 'Event' || $this->_tpl_vars['location']['marker_class'] == 'Individual' || $this->_tpl_vars['location']['marker_class'] == 'Household' || $this->_tpl_vars['location']['marker_class'] == 'Organization'): ?>
 	    map.setZoom(map.getBoundsZoomLevel(bounds));
	<?php else: ?> 
	    map.setZoom(<?php echo $this->_tpl_vars['defaultZoom']; ?>
); 
 	<?php endif; ?>
	<?php echo '	
	//]]>  
    }

    function gpopUp() {
	var from   = document.getElementById(\'from\').value;
	var to     = document.getElementById(\'to\').value;	
	var URL    = "http://maps.google.com/maps?saddr=" + from + "&daddr=" + to;
	day = new Date();
	id  = day.getTime();
	eval("page" + id + " = window.open(URL, \'" + id + "\', \'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=780,height=640,left = 202,top = 100\');");
    }

    if (window.addEventListener) {
        window.addEventListener("load", initMap, false);
    } else if (window.attachEvent) {
        document.attachEvent("onreadystatechange", initMap);
    }
</script>
'; ?>

<div id="google_map" style="width: <?php echo $this->_tpl_vars['width']; ?>
; height: <?php echo $this->_tpl_vars['height']; ?>
"></div>