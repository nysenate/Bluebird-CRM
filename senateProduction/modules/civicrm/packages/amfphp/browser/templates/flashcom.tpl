//This code is meant to be used with FlashCom / FMS2
load("netservices.asc");

NetServices.setDefaultGatewayUrl("<?php echo $info['gatewayUrl']; ?>");
var nsc = NetServices.createGatewayConnection();

var service = nsc.getService("<?php echo $info['package'] . $info['class'] ?>", new <?php echo $info['class'] ?>());
<?php if($info['auth']){ ?>nsc.setCredentials(user, pass);<?php } ?>


//Here's how you would call each remote method
<?php foreach($info['methods'] as $key => $method) { ?>
	
//<?php echo $method['description']?>

service.<?php echo $method['methodName']?>(<?php echo $method['args']?>);
<?php } ?>

//Callback class
function <?php echo $info['class'] ?>()
{
	
}

<?php foreach($info['methods'] as $key => $method) { ?>

<?php echo $info['class'] ?>.prototype.<?php echo $method['methodName']?>_Result = function(result)
{
	//Implement custom callback code
}
<?php } ?>
