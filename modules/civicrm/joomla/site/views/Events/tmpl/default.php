<?php // no direct access
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
*/
/*
 * Copyright (C) 2009 Elin Waring
 * Licensed to CiviCRM under the Academic Free License version 3.0.
 */
defined('_JEXEC') or die('Restricted access'); ?>
<?php if ( $this->params->def( 'show_page_title', 1 ) ) : ?>
	<div class="componentheading <?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo $this->escape($this->params->get('page_title')); ?>
	</div>
<?php endif; ?>

<?php if ( ($this->params->def('image', -1) != -1) || $this->params->def('show_comp_description', 1) ) : ?>
<table width="100%" cellpadding="4" cellspacing="0" border="0" align="center" class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<td valign="top" class="contentdescription <?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php
		if ( isset($this->image) ) :  echo $this->image; endif;
		echo $this->params->get('comp_description');
	?>
	</td>
</tr>
</table>
<?php endif; ?>

<div class="vevent">
	<h2><span class="summary"><?php echo $this->$event.title ?></span></h2>	
    <div class="display-block">
	<table class="form-layout">
      <?php if  $event.summary ?>
		<tr><td colspan="2" class="report"><?php echo $this->$event.summary ?></td></tr>
      	<?php endif; ?>
      	<?php if $event.description ?>
      		<tr><td colspan="2" class="report">
		<span class="summary"><?php echo $this->$event.description ?></span></td></tr>
		<?php endif; ?>
	<tr><td><label><?php echo JText::_('When') ?></label></td>
            <td width="90%">
	    <abbr class="dtstart" title="<?php echo $event.event_start_date ?>">
	    	<?php echo $event.event_start_date|crmDate ?></abbr>
	
	<?php if $event.event_end_date}
		&nbsp; JText::_('through') &nbsp;
                /* Only show end time if end date = start date */
                <?php if $event.event_end_date|date_format:"%Y%m%d" == $event.event_start_date|date_format:"%Y%m%d" ?>
			<abbr class="dtend" title="<?php echo this->$event.event_end_date ?>">
			<?php echo this->$event.event_end_date|crmDate:0:1 ?>
			</abbr>        
                else
			<abbr class="dtend" title="<?php echo this->$event.event_end_date?>">
			<?php echo this->$event.event_end_date|crmDate ?>
			</abbr> 	
               <?php endif; ?>
           <?php endif; ?>
            </td>
	</tr>
	
	<?php if $isShowLocation ?>
        	<?php if $location.1.name || $location.1.address ?>
        	    <tr><td><label>JText::_('Location')</label></td>
              	        <td><?php if $location.1.name ?>
				<span class="fn org">{$location.1.name}</span><br />{/if}
                 		<?php echo this->$location.1.address.display|nl2br ?>
                		<?php if ( $event.is_map && $config->mapAPIKey && ( is_numeric($location.1.address.geo_code_1)  || ( $config->mapGeoCoding && $location.1.address.city AND $location.1.address.state_province ) ) ) ?>
                 		<br/><a href="<?php echo this->$mapURL ?>" title="JText::_('Map this Address')">JText::_('Map this Location')</a>
					<?php endif; ?>
                 	</td>
          	    </tr>
		     <?php endif; ?>
      	       <?php endif; ?>/*End of isShowLocation condition*/	
			   <?php if $location.1.phone.1.phone || $location.1.email.1.email ?>
				<tr><td><label>JText::_('Contact')</label></td>
					<td>	/* loop on any phones and emails for this event */
               		<?php foreach from=$location.1.phone item=phone ?>
                		<?php if $phone.phone ?>
               		     		<?php if $phone.phone_type}{$phone.phone_type_display}:  ?>
						<span class="tel">"<?php echo this->$phone.phone ?>"</span> <br />
                			<?php endif; ?>
					<?php endforeach; ?>

					<?php foreach from=$location.1.email item=email ?>
						<?php if $email.email ?>
                    			JText::_('Email:') <span class="email"><a href="mailto:{$email.email}">{$email.email}</a></span>
                  		<?php endif; ?>
                	<?php endforeach; ?>
            	</td>
            </tr>
       <?php endif; ?>
    
	<?php if $event.is_monetary eq 1 && $feeBlock.value ?> 
      	<tr><td style="vertical-align:top;"><label>{$event.fee_label}</label></td>
            <td>
            <table class="form-layout-compressed">
			//need to do this part
			{section name=loop start=1 loop=11}
        	    {assign var=idx value=$smarty.section.loop.index}
                	<tr><td>{$feeBlock.label.$idx}</td>
                        <td>{$feeBlock.value.$idx|crmMoney}</td>
                    </tr>
         	 {/section}
         	</table>
            </td>
        </tr>
       <?php endif; ?>
	</table>

    <?php include file="CRM/Contact/Page/View/InlineCustomData.tpl" mainEditForm=1 ?>

    /* Show link to Event Registration page if event if configured for online reg AND we are NOT coming from Contact Dashboard (CRM-2046) */
	<?php if  $is_online_registration AND $context NEQ 'dashboard' ?>
        <div class="action-link">
			/*change to joomla url*/
            <strong><a href="{$registerURL}" title="{$registerText}">{$registerText}</a></strong>
        </div>
    <?php endif; ?>
	</div>
	<?php if  $event.is_public ?>
      <div class="action-link">
         {capture assign=icalFile}{crmURL p='civicrm/event/ical' q="reset=1&id=`$event.id`"}{/capture}
         {capture assign=icalFeed}{crmURL p='civicrm/event/ical' q="reset=1&page=1&id=`$event.id`"}{/capture}

         <a href="{$icalFile}">&raquo; JText::_('Download iCalendar File')</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="{$icalFeed}" title="JText::_('iCalendar Feed')"><img src="{$config->resourceBase}i/ical_feed.gif" alt="JText::_('iCalendar Feed')" /></a> 
      </div>
    <?php endif; ?>
</div>

