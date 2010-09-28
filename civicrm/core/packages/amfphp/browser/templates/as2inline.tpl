//This code can be directly copied and pasted onto frame 1 and should work 
//practically as is. Obviously this is not recommended practice but it should 
//give you a head start

import mx.remoting.*;
import mx.rpc.*;
import mx.utils.Delegate;
import mx.remoting.debug.NetDebug;

//Change the gateway URL as needed
var gatewayUrl:String = "<?php echo $info['gatewayUrl']; ?>";

NetDebug.initialize();
service = new Service(gatewayUrl, null, "<?php echo $info['package'] . $info['class'] ?>");
<?php if($info['auth']){ ?>service.connection.setCredentials(user, pass);<?php } ?>
	
<?php foreach($info['methods'] as $key => $method) { ?>
	
//<?php echo $method['description']?>

function <?php echo $method['methodName']?>(<?php echo $method['typedArgs']?>)
{
	var pc:PendingCall = service.<?php echo $method['methodName']?>(<?php echo $method['args']?>);
	pc.responder = new RelayResponder(this, "handle<?php echo ucfirst($method['methodName'])?>", null);
}
<?php } ?>
	
<?php foreach($info['methods'] as $key => $method) { ?>

function handle<?php echo ucfirst($method['methodName'])?>(re:ResultEvent)
{
	//Implement custom callback code
}
<?php } ?>
