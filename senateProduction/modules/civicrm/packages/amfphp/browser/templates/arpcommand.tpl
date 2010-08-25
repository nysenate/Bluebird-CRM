import mx.remoting.*;
import mx.rpc.*;
import mx.utils.Delegate;

import <?php echo $info['package'] ?>business.ServiceLocator;

/**
 * @version 1.0
 * @created <?php echo date("D M j G:i:s T Y"); ?> 
 */
class <?php echo $info['package'] ?>commands.<?php echo ucfirst($method['methodName']); ?>Command
{
   private var mViewRef:Object;

	/**
	 * <?php echo $method['description']?> 
	 * Args: <?php echo $method['typedArgs']?> 
	 */
	public function execute(pViewRef:Object):Void
	{
		trace("<?php echo ucfirst($method['methodName']); ?>Command::execute");
		
		//Save reference to view
		mViewRef = pViewRef;
		
		var service:RemotingService = ServiceLocator.getService('<?php echo $info['class']; ?>');
		var pc:PendingCall = service.<?php echo $method['methodName'] ?>(<?php echo $method['args']; ?>);
		pc.responder = new RelayResponder(this, "onResult", "onStatus");
	}
	
	/**
	 * onResult
	 */
	public function onResult(re:ResultEvent):Void 
	{
		trace("<?php echo ucfirst($method['methodName']) ?>Command::onResult");
		
		// Here, you can update your model (using the ModelLocator) and notify the view
		// that the command has succeeded.
	}

	/**
	 * onStatus
	 */
	public function onStatus(fe:FaultEvent):Void 
	{
		trace("<?php echo ucfirst($method['methodName']) ?>Command::onStatus");
		throw new Error ("Command failed: " + fe.fault.description);
	}
}
