//This code can be directly copied and pasted onto frame 1 and should work 
//practically as is. Obviously this is not recommended practice but it should 
//give you a head start

import cinqetdemi.remoting.*;
import mx.utils.Delegate;
import mx.rpc.*;

//Change the gateway URL as needed
var gatewayUrl:String = "<?php echo $info['gatewayUrl']; ?>";

service = new RemotingService(gatewayUrl, "<?php echo $info['package'] . $info['class'] ?>");
service.addEventListener('timeout', this);
service.addEventListener('result', this);
service.addEventListener('fault', this);
<?php if($info['auth']){ ?>service.addEventListener('authFault', this);<?php } ?>

service.addEventListener('busy', this);
service.addEventListener('clear', this);

<?php if($info['auth']){ ?>service.setCredentials(user, pass);<?php } ?>
	
<?php foreach($info['methods'] as $key => $method) { ?>
	
//<?php echo $method['description']?>

function <?php echo $method['methodName']?>(<?php echo $method['typedArgs']?>)
{
	service.<?php echo $method['methodName']?>([<?php echo $method['args']?>], this, handle<?php echo ucfirst($method['methodName'])?>);
}
<?php } ?>
	
<?php foreach($info['methods'] as $key => $method) { ?>

function handle<?php echo ucfirst($method['methodName'])?>(re:ResultEvent, args:Array)
{
	//Implement custom callback code
}
<?php } ?>

function timeout()
{
	trace('Service has timed out');
}

function result(evtObj)
{
	trace('Result for call to: ' + evtObj.methodName);
}

function fault(evtObj)
{
	trace('Fault for: ' + evtObj.methodName);
}

<?php if($info['auth']){ ?>function authFault(evtObj)
{
	trace('Auth fault for: + evtObj.methodName);
}

<?php } ?>function busy(evtObj)
{
	trace('Been waiting for a while now, time to show a loading bar');
}

function clear(evtObj)
{
	trace('Done, time to hide the loading bar');
}
