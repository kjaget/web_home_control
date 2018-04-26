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

function sendX10($device, $action, $count) {
	
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
	else if ($device == "basement_tv")
	{
		if ($action == "on")
		{
			$command_dtv = "python3 /home/ubuntu/dtv_keypress.py poweron";
			system($command_dtv);
			$command_tv_on = "/home/ubuntu/bravia-auth-and-remote/send_command.sh 192.168.0.31 AAAAAQAAAAEAAAAuAw==";
			system($command_tv_on);
			$command_tv_hdmi3 = "/home/ubuntu/bravia-auth-and-remote/send_command.sh 192.168.0.31 AAAAAgAAABoAAABcAw==";
			system($command_tv_hdmi3);
			$command_rxv_on = "python3 /home/ubuntu/rxv/rxv.py 1";
			system($command_rxv_on);
			return $command_dtv . "\n" . $command_tv_on . "\n" . $command_tv_hdmi3 . "\n" . $command_rxv_on . " Basement TV turned on";
		}
		else if ($action == "off")
		{
			$command_dtv = "python3 /home/ubuntu/dtv_keypress.py poweroff";
			system($command_dtv);
			$command_tv_off = "/home/ubuntu/bravia-auth-and-remote/send_command.sh 192.168.0.31 AAAAAQAAAAEAAAAvAw==";
			system($command_tv_off);
			$command_rxv_off = "python3 /home/ubuntu/rxv/rxv.py 0";
			system($command_rxv_off);
			return $command_dtv . "\n" . $command_tv_off . "\n" . $command_rxv_off . " Basement TV turned off";
		}
	}
	else if ($device == "directv")
	{
		if ($action == "advanced")
		{
			$action = "advance";
		}
		$command_dtv = "python3 /home/ubuntu/dtv_keypress.py $action $count";
		system($command_dtv);
		return $command_dtv . " DirecTV";
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
$action = strtolower(isset($_GET["action"]) ? $_GET["action"] : "none");
$device = strtolower(isset($_GET["device"]) ? $_GET["device"] : "none");
$count = isset($_GET["count"]) ? $_GET["count"] : "1";
if ($count == "five")
	$count = 5;
else if ($count == "six")
	$count = 6;
else if ($count == "seven")
	$count = 7;
else if ($count == "eight")
	$count = 8;
else if ($count == "nine")
	$count = 9;
else if ($count == "ten")
	$count = 10;

if ($action != "none" && $device != "none")
	echo sendX10($device, $action, $count);
else
	echo "Invalid Device and/or Action.";

?>
