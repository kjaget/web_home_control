<?php
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// 	ALEXA - X10 MODULE
//
//	Alexa script to send X10 signals to the house via IFTTT command 'trigger'
//
//	Copyright (c) 2016 Naikel Aparicio
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// 	X10 HANDLER
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function sendX10($device, $action) {
	
	global $ini_x10;

	// Replace spaces with underscores in the name of the appliance
	
	$appliance = str_replace(' ','_',$device);
	
	// Now we need to know the code of the appliance
	
	if ($device == "projector")
	{
		if ($action == "on")
		{
			$command1 = "python /home/ubuntu/epson_5030ub.py --device \"0x2712 192.168.0.157 5c73c134ea34\" ON";
			system($command1);
			sleep(1);
			$command2 = "python /home/ubuntu/yamaha_3.py --device \"0x2712 192.168.0.157 5c73c134ea34\" \"PWR: ON (ALL)\"";
			system($command2);
			sleep(1);
			$command3 = "python /home/ubuntu/directv.py --device \"0x2712 192.168.0.157 5c73c134ea34\" POWER";
			system($command3);
			sleep(1);
			return $command1 . " " . $command2 . " " . $command3 . "Projector turned on";
		}
		else if ($action == "off")
		{
			$command1 = "python /home/ubuntu/epson_5030ub.py --device \"0x2712 192.168.0.157 5c73c134ea34\" OFF";
			system($command1);
			sleep(1);
			$command2 = "python /home/ubuntu/yamaha_3.py --device \"0x2712 192.168.0.157 5c73c134ea34\" \"PWR: STANDBY (ALL)\"";
			system($command2);
			sleep(1);
			$command3 = "python /home/ubuntu/directv.py --device \"0x2712 192.168.0.157 5c73c134ea34\" POWER";
			system($command3);
			sleep(1);
			return $command1 . " " . $command2 . " " . $command3 . "Projector turned on";
		}
		return "Projector : unknown action";
	}
	else if (isset($ini_x10[$appliance]['code'])) {
		
		if (($action == "dim" || $action == "bright") && isset($ini_x10[$appliance]['dim']))
			return "The appliance doesn't support dim or bright commands.";
		
		#$hostname = $ini_x10['general']['hostname'];
		#$port = $ini_x10['general']['port'];
		
		#$fp = fsockopen($hostname, $port, $errno, $errstr);
		#if (!$fp) {
			#return "There was an error trying to connect to the X10 server: " . $errstr;
		#} else {
			$loop = 1;
			if ($action == "dim" || $action == "bright")
				$loop = $ini_x10[$appliance]['dim'];
					
			for ($i = 0; $i < $loop; $i++) { 
		#		$command = "rf " . $ini_x10[$appliance]['code'] . " " . $action . "\n";
		#		fwrite($fp, $command);
				
				$command = "heyu f" . $action . " " . $ini_x10[$appliance]['code'];
				system($command);
				sleep(1);
		  	}
		#	fclose($fp);
		    
			$verb =  (preg_match('/lights/',$appliance)) ? "have" : "has";
		    
			if ($action == "dim")
				$strAction = "dimmed";
			else if ($action == "bright")
				$strAction = "brighten";
			else
				$strAction = "turned " . $action;
		    	
			return ucfirst($appliance . " " . $verb . " been " . $strAction . ".");
		#}
	}
	else
		return "Couldn't find the appliance named " . $appliance;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// 	MAIN
//
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$install_path = '/opt/alexa';
$x10_config = $install_path . "/etc/x10.ini";
$ini_x10 = parse_ini_file($x10_config, true);
$action = isset($_GET["action"]) ? $_GET["action"] : "none";
$device = isset($_GET["device"]) ? $_GET["device"] : "none";

if ($action != "none" && $device != "none")
	echo sendX10($device, $action);
else
	echo "Invalid Device and/or Action.";

?>
