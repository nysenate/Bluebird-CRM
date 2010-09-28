import mx.remoting.debug.NetDebug;
import mx.utils.Delegate;
import mx.remoting.Service;

import com.ariaware.arp.ServiceLocatorTemplate;

/**
 * @version 1.0
 * @created <?php echo date("D M j G:i:s T Y"); ?> 
 */
class <?php echo $info['package'] ?>business.ServiceLocator extends ServiceLocatorTemplate 
{
	private static var GATEWAY_URL:String = "<?php echo $info['gatewayUrl']; ?>";
	private static var inst : ServiceLocator;

	/**
	 * Do nothing
	 */
	private function ServiceLocator()
	{
		super();
		NetDebug.initialize();
	}

	/**
	 * Get the locator instance
	 */
	public static function getInstance():ServiceLocator
	{
		if(inst == null)
		{
			inst = new ServiceLocator();
		}
		return inst;
	}

	/**
	 * Add services to service locator
	 */
	public function addServices():Void
	{
		var <?php echo $info['class']; ?>:RemotingService = new Service(GATEWAY_URL, null, "<?php echo $info['package'] . $info['class'] ?>");
		addService("<?php echo $info['class']; ?>", <?php echo $info['class']; ?>);
	}
	
	/**
	 * Get the default service
	 */
	public static function getDefaultService():Service
	{
		return Service(getInstance().getService('<?php echo $info['class']; ?>'));
	}
}