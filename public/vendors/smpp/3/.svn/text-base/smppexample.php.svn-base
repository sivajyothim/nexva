<?php

/*

File: smppexample.php
Implements: exmaple for smppclass.php::SMPPCLass()
License: GNU Lesser Genercal Public License: http://www.gnu.org/licenses/lgpl.html
Commercial advertisement: Contact info@chimit.nl for SMS connectivity and more elaborate SMPP libraries in PHP and other languages.

*/

/*

Caution: Replace the following values with your own parameters.
Leaving the values like this is not going to work!

*/

@ini_set('magic_quotes_runtime', 0);




define(CM_BIND_TRANSMITTER, 0x00000002);
//define(CM_SUBMIT_SM, 0x00000004);
define(CM_SUBMIT_SM, 0x00000021);
define(CM_SUBMIT_MULTI, 0x00000021);
define(CM_UNBIND, 0x00000006);
define(CM_ENQUIRELINK, 0x00000015);





class SMPPClass {
	// public members:
	/*
	 Constructor.
	Parameters:
	none.
	Example:
	$smpp = new SMPPClass();
	*/
	function SMPPClass()
	{
		/* seed random generator */
		list($usec, $sec) = explode(' ', microtime());
		$seed = (float) $sec + ((float) $usec * 100000);
		srand($seed);

		/* initialize member variables */
		$this->_debug = true; /* set this to false if you want to suppress debug output. */
		$this->_socket = NULL;
		$this->_command_status = 0;
		$this->_sequence_number = 1;
		$this->_source_address = "";
		$this->_message_sequence = rand(1,255);
	}

	/*
	 For SMS gateways that support sender-ID branding, the method
	can be used to set the originating address.
	Parameters:
	$from	:	Originating address
	Example:
	$smpp->SetSender("31495595392");
	*/
	function SetSender($from)
	{
		if (strlen($from) > 20) {
			$this->debug("Error: sender id too long.\n");
			return;
		}
		$this->_source_address = $from;
	}

	/*
	 This method initiates an SMPP session.
	It is to be called BEFORE using the Send() method.
	Parameters:
	$host		: SMPP ip to connect to.
	$port		: port # to connect to.
	$username	: SMPP system ID
	$password	: SMPP passord.
	$system_type	: SMPP System type
	Returns:
	true if successful, otherwise false
	Example:
	$smpp->Start("smpp.chimit.nl", 2345, "chimit", "my_password", "client01");
	*/
	function Start($host, $port, $username, $password, $system_type)
	{
		/*
		 $testarr = stream_get_transports();
		$have_tcp = false;
		reset($testarr);
		while (list(, $transport) = each($testarr)) {
		if ($transport == "tcpp") {
		$have_tcp = true;
		}
		}
		if (!$have_tcp) {
		$this->debug("No TCP support in this version of PHP.\n");
		return false;
		}
		*/
		$this->_socket = fsockopen($host, $port, $errno, $errstr, 20);
		// todo: sanity check on input parameters
		if (!$this->_socket) {
			$this->debug("Error opening SMPP session.\n");
			$this->debug("Error was: $errstr.\n");
			return;
		}
		socket_set_timeout($this->_socket, 1200);
		$status = $this->SendBindTransmitter($username, $password, $system_type);
		if ($status != 0) {
			$this->debug("Error binding to SMPP server. Invalid credentials?\n");
		}
		return ($status == 0);
	}

	/*
	 This method sends out one SMS message.
	Parameters:
	$to	: destination address.
	$text	: text of message to send.
	$unicode: Optional. Indicates if input string is html encoded unicode.
	Returns:
	true if messages sent successfull, otherwise false.
	Example:
	$smpp->Send("31649072766", "This is an SMPP Test message.");
	$smpp->Send("31648072766", "&#1589;&#1576;&#1575;&#1581;&#1575;&#1604;&#1582;&#1610;&#1585;", true);
	*/
	function Send($to, $text, $unicode = false)
	{
		if (strlen($to) > 20) {
			$this->debug("to-address too long.\n");
			return;
		}
		if (!$this->_socket) {
			$this->debug("Not connected, while trying to send SUBMIT_SM.\n");
			// return;
		}
		$service_type = "";
		//default source TON and NPI for international sender
		$source_addr_ton = 1;
		$source_addr_npi = 1;
		$source_addr = $this->_source_address;
		if (preg_match('/\D/', $source_addr)) //alphanumeric sender
		{
			$source_addr_ton = 5;
			$source_addr_npi = 0;
		}
		elseif (strlen($source_addr) < 11) //national or shortcode sender
		{
			$source_addr_ton = 2;
			$source_addr_npi = 1;
		}
		$dest_addr_ton = 1;
		$dest_addr_npi = 1;
		$destination_addr = $to;
		$esm_class = 3;
		$protocol_id = 0;
		$priority_flag = 0;
		$schedule_delivery_time = "";
		$validity_period = "";
		$registered_delivery_flag = 0;
		$replace_if_present_flag = 0;
		$data_coding = 241;
		$sm_default_msg_id = 0;
		if ($unicode) {
			$text = mb_convert_encoding($text, "UCS-2BE", "HTML-ENTITIES"); /* UCS-2BE */
			$data_coding = 8; /* UCS2 */
			$multi = $this->split_message_unicode($text);
		}
		else {
			$multi = $this->split_message($text);
		}
		$multiple = (count($multi) > 1);
		if ($multiple) {
			$esm_class += 0x00000040;
		}
		$result = true;
		reset($multi);
		while (list(, $part) = each($multi)) {
			$short_message = $part;
			$sm_length = strlen($short_message);
			$status = $this->SendSubmitSM($service_type, $source_addr_ton, $source_addr_npi, $source_addr, $dest_addr_ton, $dest_addr_npi, $destination_addr, $esm_class, $protocol_id, $priority_flag, $schedule_delivery_time, $validity_period, $registered_delivery_flag, $replace_if_present_flag, $data_coding, $sm_default_msg_id, $sm_length, $short_message);
			if ($status != 0) {
				$this->debug("SMPP server returned error $status.\n");
				$result = false;
			}
		}
		return $result;
	}

	/*
	 This method sends out one SMS in Binary mode.
	Parameters:
	$to	: destination address.
	$binary	: array()
	$udh: array()
	$data_coding: (optional, 4 = Octet unspecified (8-bit binary), 8 = UCS2 (ISO/IEC-10646), 241 = GSM 7-bit character set)
	Returns:
	true if messages sent successfull, otherwise false.
	Example:
	$smpp->Send("31649072766", "This is an SMPP Test message.");
	*/

	function SendBinary($to, $binary, $udh, $data_coding=4)
	{
		if (strlen($to) > 20) {
			$this->debug("to-address too long.\n");
			return;
		}
		if (!$this->_socket) {
			$this->debug("Not connected, while trying to send SUBMIT_SM.\n");
			return;
		}

		if(!is_array($binary)) {
			$this->debug("binary must be array\n");
			return;
		}

		if(!is_array($udh)) {
			$this->debug("udh must be array\n");
			return;
		}

		$udh_packed = '';
		$binary_packed = '';

		foreach($udh as $u) {

			if(!is_numeric($u)) {
				$this->debug("udh must be array of integers\n");
				return;
			}

			$udh_packed .= pack("c", $u);
		}

		foreach($binary as $b) {

			if(!is_numeric($b)) {
				$this->debug("binary must be array of integers\n");
				return;
			}

			$binary_packed .= pack("c", $b);
		}

		$this->debug("Split: UDH = ");
		for ($i = 0; $i < strlen($udh_packed); $i++) {
			$this->debug(ord($udh_packed[$i]) . " ");
		}
		$this->debug("\n");

		$this->debug("Split: Binary = ");
		for ($i = 0; $i < strlen($binary_packed); $i++) {
			$this->debug(ord($binary_packed[$i]) . " ");
		}
		$this->debug("\n");


		$service_type = "";
		//default source TON and NPI for international sender
		$source_addr_ton = 1;
		$source_addr_npi = 1;
		$source_addr = $this->_source_address;

		if (preg_match('/\D/', $source_addr)) //alphanumeric sender
		{
			$source_addr_ton = 5;
			$source_addr_npi = 0;
		}
		elseif (strlen($source_addr) < 11) //national or shortcode sender
		{
			$source_addr_ton = 2;
			$source_addr_npi = 1;
		}

		$dest_addr_ton = 1;
		$dest_addr_npi = 1;
		$destination_addr = $to;
		$esm_class = 0x00000043;
		$protocol_id = 0;
		$priority_flag = 0;
		$schedule_delivery_time = "";
		$validity_period = "";
		$registered_delivery_flag = 0;
		$replace_if_present_flag = 0;
		//$data_coding = 4;
		$sm_default_msg_id = 0;

		$result = true;


		$short_message = $udh_packed . $binary_packed;
		$sm_length = strlen($short_message);
		$status = $this->SendSubmitSM($service_type, $source_addr_ton, $source_addr_npi, $source_addr, $dest_addr_ton, $dest_addr_npi, $destination_addr, $esm_class, $protocol_id, $priority_flag, $schedule_delivery_time, $validity_period, $registered_delivery_flag, $replace_if_present_flag, $data_coding, $sm_default_msg_id, $sm_length, $short_message);
		if ($status != 0) {
			$this->debug("SMPP server returned error $status.\n");
			$result = false;
		}

		return $result;
	}


	/*
	 This method ends a SMPP session.
	Parameters:
	none
	Returns:
	true if successful, otherwise false
	Example: $smpp->End();
	*/
	function End()
	{
		if (!$this->_socket) {
			// not connected
			return;
		}
		$status = $this->SendUnbind();
		if ($status != 0) {
			$this->debug("SMPP Server returned error $status.\n");
		}
		fclose($this->_socket);
		$this->_socket = NULL;
		return ($status == 0);
	}

	/*
	 This method sends an enquire_link PDU to the server and waits for a response.
	Parameters:
	none
	Returns:
	true if successfull, otherwise false.
	Example: $smpp->TestLink()
	*/
	function TestLink()
	{
		$pdu = "";
		$status = $this->SendPDU(CM_ENQUIRELINK, $pdu);
		return ($status == 0);
	}

	/*
	 This method sends a single message to a comma separated list of phone numbers.
	There is no limit to the number of messages to send.
	Parameters:
	$tolist		: comma seperated list of phone numbers
	$text		: text of message to send
	$unicode: Optional. Indicates if input string is html encoded unicode string.
	Returns:
	true if messages received by smpp server, otherwise false.
	Example:
	$smpp->SendMulti("31777110204,31649072766,...,...", "This is an SMPP Test message.");
	*/
	function SendMulti($tolist, $text, $unicode = false)
	{
		if (!$this->_socket) {
			$this->debug("Not connected, while trying to send SUBMIT_MULTI.\n");
			// return;
		}
		$service_type = "";
		$source_addr = $this->_source_address;
		//default source TON and NPI for international sender
		$source_addr_ton = 1;
		$source_addr_npi = 1;
		$source_addr = $this->_source_address;
		if (preg_match('/\D/', $source_addr)) //alphanumeric sender
		{
			$source_addr_ton = 5;
			$source_addr_npi = 0;
		}
		elseif (strlen($source_addr) < 11) //national or shortcode sender
		{
			$source_addr_ton = 2;
			$source_addr_npi = 1;
		}
		$dest_addr_ton = 1;
		$dest_addr_npi = 1;
		$destination_arr = explode(",", $tolist);
		$esm_class = 3;
		$protocol_id = 0;
		$priority_flag = 0;
		$schedule_delivery_time = "";
		$validity_period = "";
		$registered_delivery_flag = 0;
		$replace_if_present_flag = 0;
		$data_coding = 241;
		$sm_default_msg_id = 0;
		if ($unicode) {
			$text = mb_convert_encoding($text, "UCS-2BE", "HTML-ENTITIES");
			$data_coding = 8; /* UCS2 */
			$multi = $this->split_message_unicode($text);
		}
		else {
			$multi = $this->split_message($text);
		}
		$multiple = (count($multi) > 1);
		if ($multiple) {
			$esm_class += 0x00000040;
		}
		$result = true;
		reset($multi);
		while (list(, $part) = each($multi)) {
			$short_message = $part;
			$sm_length = strlen($short_message);
			$status = $this->SendSubmitMulti($service_type, $source_addr_ton, $source_addr_npi, $source_addr, $dest_addr_ton, $dest_addr_npi, $destination_arr, $esm_class, $protocol_id, $priority_flag, $schedule_delivery_time, $validity_period, $registered_delivery_flag, $replace_if_present_flag, $data_coding, $sm_default_msg_id, $sm_length, $short_message);
			if ($status != 0) {
				$this->debug("SMPP server returned error $status.\n");
				$result = false;
			}
		}
		return $result;
	}

	// private members (not documented):

	function ExpectPDU($our_sequence_number)
	{
		do {
			$this->debug("Trying to read PDU.\n");
			if (feof($this->_socket)) {
				$this->debug("Socket was closed.!!\n");
			}
			$elength = fread($this->_socket, 4);
			if (empty($elength)) {
				$this->debug("Connection lost.\n");
				return;
			}
			extract(unpack("Nlength", $elength));
			$this->debug("Reading PDU     : $length bytes.\n");
			$stream = fread($this->_socket, $length - 4);
			$this->debug("Stream len      : " . strlen($stream) . "\n");
			extract(unpack("Ncommand_id/Ncommand_status/Nsequence_number", $stream));
			
			echo "<br>----<br>".bindec($command_status)."<br>----<br>";
			
			for ($i = 0; $i < strlen($stream); $i++) {
				if (ord($stream[$i]) < 32) $this->debug("" . ord($stream[$i]) . ""); else $this->debug($stream[$i]);
			}
			
			echo "<br>|$elength|<br><br>";
			
			
			
		//	$pduSS = unpack('Nid/Nstatus/Nseq', $stream);
			
		//	var_dump($pduSS);
			
			
		//	echo "<br><br><br> ------- $pdu   --------- <br><br><br> ";
		//	echo "<br><br><br> -------".unpack('N/N/N', $stream)."--------- <br><br><br> ";
		//	echo "<br><br><br> -------".	bin2hex($stream)."--------- <br><br><br> ";
			
		
		//	echo "<br><br><br> -------".sprintf("%08b", ord($stream))."--------- <br><br><br> ";
			
			
			
			
			$command_id &= 0x0fffffff;
			$this->debug("Command id      : $command_id.\n");
			$this->debug("Command status  : $command_status.\n");
			$this->debug("sequence_number : $sequence_number.\n");
			$pdu = substr($stream, 12);
			switch ($command_id) {
				case CM_BIND_TRANSMITTER:
					$this->debug("Got CM_BIND_TRANSMITTER_RESP.\n");
					$spec = "asystem_id";
					extract($this->unpack2($spec, $pdu));
					$this->debug("system id       : $system_id.\n");
					break;
				case CM_UNBIND:
					$this->debug("Got CM_UNBIND_RESP.\n");
					break;
				case CM_SUBMIT_SM:
					$this->debug("Got CM_SUBMIT_SM_RESP.\n");
					if ($command_status == 0) {
						$spec = "amessage_id";
						extract($this->unpack2($spec, $pdu));
						$this->debug("message id      : $message_id.\n");
					}
					break;
				case CM_SUBMIT_MULTI:
					$this->debug("Got CM_SUBMIT_MULTI_RESP.\n");
					$spec = "amessage_id/cno_unsuccess/";
					extract($this->unpack2($spec, $pdu));
					$this->debug("message id      : $message_id.\n");
					$this->debug("no_unsuccess    : $no_unsuccess.\n");
					break;
				case CM_ENQUIRELINK:
					$this->debug("GOT CM_ENQUIRELINK_RESP.\n");
					break;
				default:
					$this->debug("Got unknown SMPP pdu.\n");
					break;
			}
			$this->debug("\nReceived PDU: ");
			for ($i = 0; $i < strlen($stream); $i++) {
				if (ord($stream[$i]) < 32) $this->debug("(" . ord($stream[$i]) . ")"); else $this->debug($stream[$i]);
			}
			$this->debug("\n");
		} while ($sequence_number != $our_sequence_number);
		return $command_status;
	}

	function SendPDU($command_id, $pdu)
	{
		$length = strlen($pdu) + 16;
		$header = pack("NNNN", $length, $command_id, $this->_command_status, $this->_sequence_number);
		$this->debug("Sending PDU: " . $pdu);
		$this->debug("Sending PDU, len == $length\n");
		$this->debug("Sending PDU, header-len == " . strlen($header) .  "\n");
		$this->debug("Sending PDU, command_id == " . $command_id  .  "\n");
		fwrite($this->_socket, $header . $pdu, $length);
		$status = $this->ExpectPDU($this->_sequence_number);
		

		$this->_sequence_number = $this->_sequence_number + 1;
		return $status;
	}
	
	
	
	function hextobin($hexstr)
	{
		$n = strlen($hexstr);
		$sbin="";
		$i=0;
		while($i<$n)
		{
			$a =substr($hexstr,$i,2);
			$c = pack("H*",$a);
			if ($i==0){
				$sbin=$c;
			}
			else {$sbin.=$c;
			}
			$i+=2;
		}
		return $sbin;
	}

	function SendBindTransmitter($system_id, $smpppassword, $system_type)
	{
		$system_id = $system_id . chr(0);
		$system_id_len = strlen($system_id);
		$smpppassword = $smpppassword . chr(0);
		$smpppassword_len = strlen($smpppassword);
		$system_type = $system_type . chr(0);
		$system_type_len = strlen($system_type);
		$pdu = pack("a{$system_id_len}a{$smpppassword_len}aCCCa1", $system_id, $smpppassword, chr(0), 0x34, 0, 0, chr(0));
		$this->debug("Bind Transmitter PDU: ");
		for ($i = 0; $i < strlen($pdu); $i++) {
			$this->debug(ord($pdu[$i]) . " ");
		}
		$this->debug("\n");
		$status = $this->SendPDU(CM_BIND_TRANSMITTER, $pdu);
		return $status;
	}

	function SendUnbind()
	{
		$pdu = "";
		$status = $this->SendPDU(CM_UNBIND, $pdu);
		return $status;
	}

	function SendSubmitSM($service_type, $source_addr_ton, $source_addr_npi, $source_addr, $dest_addr_ton, $dest_addr_npi, $destination_addr, $esm_class, $protocol_id, $priority_flag, $schedule_delivery_time, $validity_period, $registered_delivery_flag, $replace_if_present_flag, $data_coding, $sm_default_msg_id, $sm_length, $short_message)
	{


		if($data_coding == 241)
		{
			$short_message = $this->convert_to_7bit($short_message);
		}



		$service_type = $service_type . chr(0);
		$service_type_len = strlen($service_type);
		$source_addr = $source_addr . chr(0);
		$source_addr_len = strlen($source_addr);
		$destination_addr = $destination_addr . chr(0);
		$destination_addr_len = strlen($destination_addr);
		$schedule_delivery_time = $schedule_delivery_time . chr(0);
		$schedule_delivery_time_len = strlen($schedule_delivery_time);
		$validity_period = $validity_period . chr(0);
		$validity_period_len = strlen($validity_period);
		// $short_message = $short_message . chr(0);
		$message_len = $sm_length;
		$spec = "a{$service_type_len}cca{$source_addr_len}cca{$destination_addr_len}ccca{$schedule_delivery_time_len}a{$validity_period_len}ccccca{$message_len}";
		$this->debug("Message text: $short_message.\n");
		$this->debug("PDU spec: $spec.\n");

		$short_message = str_replace('ø',chr(12),$short_message);

		$pdu = pack($spec,
				$service_type,
				$source_addr_ton,
				$source_addr_npi,
				$source_addr,
				$dest_addr_ton,
				$dest_addr_npi,
				$destination_addr,
				$esm_class,
				$protocol_id,
				$priority_flag,
				$schedule_delivery_time,
				$validity_period,
				$registered_delivery_flag,
				$replace_if_present_flag,
				$data_coding,
				$sm_default_msg_id,
				$sm_length,
				$short_message);
		$status = $this->SendPDU(CM_SUBMIT_SM, $pdu);
		return $status;
	}

	function SendSubmitMulti($service_type, $source_addr_ton, $source_addr_npi, $source_addr, $dest_addr_ton, $dest_addr_npi, $destination_arr, $esm_class, $protocol_id, $priority_flag, $schedule_delivery_time, $validity_period, $registered_delivery_flag, $replace_if_present_flag, $data_coding, $sm_default_msg_id, $sm_length, $short_message)
	{

		if($data_coding == 241)
		{
			$short_message = $this->convert_to_7bit($short_message);
		}


		$service_type = $service_type . chr(0);
		$service_type_len = strlen($service_type);
		$source_addr = $source_addr . chr(0);
		$source_addr_len = strlen($source_addr);
		$number_destinations = count($destination_arr);
		$dest_flag = 1;
		$spec = "a{$service_type_len}cca{$source_addr_len}c";
		$pdu = pack($spec,
				$service_type,
				$source_addr_ton,
				$source_addr_npi,
				$source_addr,
				$number_destinations
		);

		$dest_flag = 1;
		reset($destination_arr);
		while (list(, $destination_addr) = each($destination_arr)) {
			$destination_addr .= chr(0);
			$dest_len = strlen($destination_addr);
			$spec = "ccca{$dest_len}";
			$pdu .= pack($spec, $dest_flag, $dest_addr_ton, $dest_addr_npi, $destination_addr);
		}
		$schedule_delivery_time = $schedule_delivery_time . chr(0);
		$schedule_delivery_time_len = strlen($schedule_delivery_time);
		$validity_period = $validity_period . chr(0);
		$validity_period_len = strlen($validity_period);
		$message_len = $sm_length;
		$spec = "ccca{$schedule_delivery_time_len}a{$validity_period_len}ccccca{$message_len}";

		$pdu .= pack($spec,
				$esm_class,
				$protocol_id,
				$priority_flag,
				$schedule_delivery_time,
				$validity_period,
				$registered_delivery_flag,
				$replace_if_present_flag,
				$data_coding,
				$sm_default_msg_id,
				$sm_length,
				$short_message);

		$this->debug("\nMulti PDU: ");
		for ($i = 0; $i < strlen($pdu); $i++) {
			if (ord($pdu[$i]) < 32) $this->debug("."); else $this->debug($pdu[$i]);
		}
		$this->debug("\n");

		$status = $this->SendPDU(CM_SUBMIT_MULTI, $pdu);
		
		
		
		return $status;
	}

	function split_message($text)
	{
		$this->debug("In split_message.\n");
		$max_len = 153;
		$res = array();
		if (strlen($text) <= 160) {
			$this->debug("One message: " . strlen($text) . "\n");
			$res[] = $text;
			return $res;
		}
		$pos = 0;
		$msg_sequence = $this->_message_sequence++;
		$num_messages = ceil(strlen($text) / $max_len);
		$part_no = 1;
		while ($pos < strlen($text)) {
			$ttext = substr($text, $pos, $max_len);
			$pos += strlen($ttext);
			$udh = pack("cccccc", 5, 0, 3, $msg_sequence, $num_messages, $part_no);
			$part_no++;
			$res[] = $udh . $ttext;
			$this->debug("Split: UDH = ");
			for ($i = 0; $i < strlen($udh); $i++) {
				$this->debug(ord($udh[$i]) . " ");
			}
			$this->debug("\n");
			$this->debug("Split: $ttext.\n");
		}
		return $res;
	}

	function split_message_unicode($text)
	{
		$this->debug("In split_message.\n");
		$max_len = 134;
		$res = array();
		if (mb_strlen($text) <= 140) {
			$this->debug("One message: " . mb_strlen($text) . "\n");
			$res[] = $text;
			return $res;
		}
		$pos = 0;
		$msg_sequence = $this->_message_sequence++;
		$num_messages = ceil(mb_strlen($text) / $max_len);
		$part_no = 1;
		while ($pos < mb_strlen($text)) {
			$ttext = mb_substr($text, $pos, $max_len);
			$pos += mb_strlen($ttext);
			$udh = pack("cccccc", 5, 0, 3, $msg_sequence, $num_messages, $part_no);
			$part_no++;
			$res[] = $udh . $ttext;
			$this->debug("Split: UDH = ");
			for ($i = 0; $i < strlen($udh); $i++) {
				$this->debug(ord($udh[$i]) . " ");
			}
			$this->debug("\n");
			$this->debug("Split: $ttext.\n");
		}
		return $res;
	}

	function unpack2($spec, $data)
	{
	    echo '<br>';
	    //var_dump($spec, 'dddd');
	    //var_dump($data, 'bbbb');
	    //echo '</br>';
	    
		$res = array();
		$specs = explode("/", $spec);
		$pos = 0;
		reset($specs);
		while (list(, $sp) = each($specs)) {
			$subject = substr($data, $pos);
			$type = substr($sp, 0, 1);
			$var = substr($sp, 1);
			switch ($type) {
				case "N":
					$temp = unpack("Ntemp2", $subject);
					$res[$var] = $temp["temp2"];
					$pos += 4;
					break;
				case "c":
					$temp = unpack("ctemp2", $subject);
					$res[$var] = $temp["temp2"];
					$pos += 1;
					break;
				case "a":
					$pos2 = strpos($subject, chr(0)) + 1;
					$temp = unpack("a{$pos2}temp2", $subject);
					$res[$var] = $temp["temp2"];
					$pos += $pos2;
					break;
			}
		}
		return $res;
	}

	function convert_to_7bit($string)
	{

		$replace_chr = array('@','£','$','¥','è','é','ù','ì','ò','Ç',"\n",'Ø','ø',"\r",'Å','å',null,'_',null,null,null,null,null,null,null,null,null,'Æ','æ','ß','É','!','"','#','¤','%','&',"'",'(',')','*','+',',','-','.','/','Ä','Ö','Ñ','Ü','§','¿','ä','ö','ñ','ü','à');

		$replace_by = array(chr(0),chr(1),chr(2),chr(3),chr(4),chr(5),chr(6),chr(7),chr(8),chr(9),chr(10),chr(11),chr(12),chr(13),chr(14),chr(15),chr(16),chr(17),chr(18),chr(19),chr(20),chr(21),chr(22),chr(23),chr(24),chr(25),chr(26),chr(28),chr(29),chr(30),chr(31),chr(33),chr(34),chr(35),chr(36),chr(37),chr(38),chr(39),chr(40),chr(41),chr(42),chr(43),chr(44),chr(45),chr(46),chr(47),chr(91),chr(92),chr(93),chr(94),chr(95),chr(96),chr(123),chr(124),chr(125),chr(126),chr(127));

		return str_replace($replace_chr,$replace_by,$string);
	}

	function debug($str)
	{
		if ($this->_debug) {
			echo $str;
		}
	}
};

echo '<pre>';

$smpphost = "pmg-acg-sms01.ref1.lightsurf.net";
$smppport = 8008;
$systemid = "1432";
$password = "n3x41A99";
$system_type = "";
$from = "+13369570515";

$smpp = new SMPPClass();
$smpp->SetSender($from);
/* bind to smpp server */
$smpp->Start($smpphost, $smppport, $systemid, $password, $system_type);
/* send enquire link PDU to smpp server */
$smpp->TestLink();
/* send single message; large messages are automatically split */
$smpp->Send("+15569570515", "This is my PHP message");
/* send unicode message */
//$smpp->Send("31648072766", "&#1589;&#1576;&#1575;&#1581;&#1575;&#1604;&#1582;&#1610;&#1585;", true);
/* send message to multiple recipients at once */
$smpp->SendMulti("+13369570515", "This is my PHP message");
/* unbind from smpp server */
//$smpp->End();
echo '</pre>';
?>