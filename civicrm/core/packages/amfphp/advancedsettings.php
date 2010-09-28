<?php
/**
 * This file defines settings regarding custom class mappings
 * Custom class mapping is an advanced feature that enables mapping instances of 
 * custom Flash objects to PHP objects and vice-versa. If you want to implement
 * VOs (value objects) this is the perfect feature for this
 *
 * If you have no idea what a VO is chances are you don't really care about these
 * settings
 * 
 * Most of the time you will probably want to change the outgoing settings only
 * since incoming mappings are done for you automatically
 */
 
//One may choose to put mapped classes (incoming) outside of the services folder also
$gateway->setBaseCustomMappingsPath('services/vo/'); 

//Set incoming mappings
$incoming = array(
	//For incoming mappings to work, you need to use 
	//Object.registerClass('com.myClass', com.myClass) in Flash
	//To map an instance of com.myclass to com/myclass.php, you don't have to 
	//actually do anything, this is done automatically. To map an instance of 
	//com.myClass to org/myOtherClass.php, uncomment the following line:
	//'com.myClass' => 'org.myOtherClass'
	//AMFPHP will call the init function (if it exists) in your class right after 
	//instanciating it
);

$gateway->setCustomIncomingClassMappings($incoming);

//Set outgoing mappings
$outgoing = array(
	//To map an instance of a PHP class named 'MyPhpClass' (located in any folder or file)
	//to an ActionScript class named 'com.myproject.MyFlashClass', uncomment this line:
	//'myphpclass' => 'com.myproject.MyFlashClass'
	//Note that the left hand side is *lowercase*. This is because of the issue
	//in PHP4 that get_class returns the *lowercase* name of the class only.
	//Also note that this may be overriden if your class has a member called '_explicitType',
	//in which case _explicitType will be used
);

$gateway->setCustomOutgoingClassMappings($outgoing);

?>