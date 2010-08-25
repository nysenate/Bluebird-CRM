import com.ariaware.arp.ControllerTemplate; // ControllerTemplate

import <?php echo $info['package'] ?>command.*;
import <?php echo $info['package'] ?>view.*;

/**
 * @version 1.0
 * @created <?php echo date("D M j G:i:s T Y"); ?> 
 */
class <?php echo $info['package'] ?>control.Controller extends ControllerTemplate
{
	private static var inst:Controller;

	private function addEventListeners ()
	{
		// Listen for events from the views. Two separate screens may dispatch
		// the same event and these will be handled by the same event handler.
		// The views are contained in the app variable
	}

	private function addCommands ()
	{
		// Note: Commands are added as references to the classes. Allows a 
		// single command to be called from multiple views.
		trace ("INFO Controller::addCommands()");
		
<?php foreach($commands as $command) { ?>
		addCommand ( "<?php echo $command ?>", <?php echo ucfirst($command) ?> );
<?php } ?>
	}

	public static function getInstance ( appRef )
	{
		//
		// Return reference to singleton instance
		//
		if ( inst == null )
		{
			// create a single instance of the singleton
			inst = new Controller();
			if ( appRef != undefined )
			{
				// register the application
				inst.registerApp ( appRef );
			}
			return inst;
		}
		else
		{
			// instance already exists, return a reference to it
			return inst;
		}
	}
}
