/*
* +--------------------------------------------------------------------+
* | CiviCRM version 3.4                                                |
* +--------------------------------------------------------------------+
* | Copyright CiviCRM LLC (c) 2004-2011                                |
* +--------------------------------------------------------------------+
* | This file is a part of CiviCRM.                                    |
* |                                                                    |
* | CiviCRM is free software; you can copy, modify, and distribute it  |
* | under the terms of the GNU Affero General Public License           |
* | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
* |                                                                    |
* | CiviCRM is distributed in the hope that it will be useful, but     |
* | WITHOUT ANY WARRANTY; without even the implied warranty of         |
* | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
* | See the GNU Affero General Public License for more details.        |
* |                                                                    |
* | You should have received a copy of the GNU Affero General Public   |
* | License and the CiviCRM Licensing Exception along                  |
* | with this program; if not, contact CiviCRM LLC                     |
* | at info[AT]civicrm[DOT]org. If you have questions about the        |
* | GNU Affero General Public License or the licensing of CiviCRM,     |
* | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
* +--------------------------------------------------------------------+
*/ 
(function($){ $.fn.crmaccordions = function(){
	if ($('.crm-accordion-processed').length == 0 ||
	    $('.crm-accordion-processed').length < $('.crm-accordion-wrapper').length ){
	  var crmAccordionWrapper = $('.crm-accordion-wrapper');  
	  crmAccordionWrapper.delegate('div.crm-accordion-header', 'mouseover', function() {$(this).addClass('crm-accordion-header-hover')});
	  crmAccordionWrapper.delegate('div.crm-accordion-header', 'mouseout', function() {$(this).removeClass('crm-accordion-header-hover')});
	  crmAccordionWrapper.undelegate('click');
	  crmAccordionWrapper.delegate('div.crm-accordion-header', 'click', function () {
		$(this).parent().toggleClass('crm-accordion-open');
		$(this).parent().toggleClass('crm-accordion-closed');
		//return false to prevent wiring of click event
		return false;
		});
	$('.crm-accordion-wrapper').addClass('crm-accordion-processed'); // only attached to accordions processed during first run
	};
};
})(jQuery);
