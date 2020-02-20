<?php
echo '<pre>';
$smpphost = "pmg-acg-sms01.ref1.lightsurf.net"; // your host address
$smppport = 8008;
$systemid = "1432"; // Your system id
$password = "n3x41A99"; // Your smpp account password
$system_type = ""; // Your userid
$from = "13369570515"; // From number

$smpp = new SMPPClass();
$smpp->SetSender($from);
/* bind to smpp server */
$smpp->Start($smpphost, $smppport, $systemid, $password, $system_type);
/* send enquire link PDU to smpp server */
$smpp->TestLink();
/* send single message; large messages are automatically split */
$smpp->Send("13369570515", "This is a test message. Give me a missed call if you get this. sajith");
/* send unicode message */

/* send message to multiple recipients at once */
$smpp->SendMulti("31648072766,31651931985", "This is my PHP message");
/* unbind from smpp server */
$smpp->End();
echo '</pre>';
//Click read more for smppclass.php  library


/*

File		:	smppclass.php
Implements	:	SMPPClass()
Description	:	This class can send messages via the SMPP protocol. Also supports unicode and multi-part messages.
License		:	GNU Lesser Genercal Public License: http://www.gnu.org/licenses/lgpl.html
Commercial advertisement: Contact info@chimit.nl for SMS connectivity and more elaborate SMPP libraries in PHP and other languages.

*/

/*

The following are the SMPP PDU types that we are using in this class.
Apart from the following 5 PDU types,  there are a lot of SMPP directives
that are not implemented in this version.

*/
define('CM_BIND_TRANSMITTER', 0x00000002);
define('CM_BIND_TRANSCEIVER', 0x00000009);
define('CM_QUERY_SM', 0x00000003);
define('CM_SUBMIT_SM', 0x00000021);
define('CM_SUBMIT_MULTI', 0x00000021);
define('CM_UNBIND', 0x00000006);
define('CM_DELIVER_SM', 0x00000005);
define('CM_ENQUIRELINK', 0x00000015);

class SMPPClass {

	private $_dest_addr; // 

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
		$this->_message_id = "";
		$this->dlvrSms = array();
		$this->_dest_addr = null;
		$this->pdu_queue = array();
		$this->_timeout = 1200;
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
			$this->debug("Error: sender id too long.<br />");
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
		$this->debug("No TCP support in this version of PHP.<br />");
		return false;
		}
		*/
		$this->_socket = fsockopen($host, $port, $errno, $errstr, 20);
		// todo: sanity check on input parameters
		if (!$this->_socket) {
			$this->debug("Error opening SMPP session.<br />");
			$this->debug("Error was: $errstr.<br />");
			return;
		}
		socket_set_timeout($this->_socket, $this->_timeout);
		$status = $this->SendBindTransceiver($username, $password, $system_type);
		if ($status != 0) {
			$this->debug("Error binding to SMPP server. Invalid credentials?<br />");
		} else $this->_sequence_number = 1;
		return ($status == 0);
	}


	//

	//
	public function GetMessageID(){

		return $this->_message_id;
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
	function Send($to, $text, $unicode = false, $time = "", $flash = false)
	{
		if (strlen($to) > 20) {
			$this->debug("to-address too long.<br />");
			return;
		}
		if (!$this->_socket) {
			$this->debug("Not connected, while trying to send SUBMIT_SM.<br />");
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
		$this->_dest_addr = $to;
		$esm_class = 3;
		$protocol_id = 0;
		$priority_flag = 0;
		$schedule_delivery_time = $time;
		$validity_period = $time;
		$registered_delivery_flag = 1;
		$replace_if_present_flag = 0;
		if($flash)
			$data_coding = 240;
		else
			$data_coding = 241;
		$sm_default_msg_id = 0;
		if ($unicode) {
			$text = mb_convert_encoding($text, "UCS-2BE", "UTF-8,HTML-ENTITIES"); /* UCS-2BE *///Patched by iZonder
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
				$this->debug("SMPP server returned error $status.<br />");
				$result = $status;//false; //Patched by iZonder
			}
		}
		return $result;
	}

	/**
	 * Read one SMS from SMSC. Can be executed only after bindReceiver() call.
	 * This method bloks. Method returns on socket timeout or enquire_link signal from SMSC.
	 * @return sms associative array or false when reading failed or no more sms.
	 */
	public function readSMS()
	{
		$command_id=SMPP::DELIVER_SM;
		// Check the queue
		$ql = count($this->pdu_queue);
		for($i=0;$i<$ql;$i++) {
			$pdu=$this->pdu_queue[$i];
			if($pdu->id==$command_id) {
				//remove response
				array_splice($this->pdu_queue, $i, 1);
				return $this->parseSMS($pdu);
			}
		}
		// Read pdu
		do{
			if(!$this->_socket) return false; 

			$pdu = $this->readPDU();
			if ($pdu === false) return false; // Just in case
			//check for enquire link command
			if($pdu->id==SMPP::ENQUIRE_LINK) {
				$response = new SmppPdu(SMPP::ENQUIRE_LINK_RESP, SMPP::ESME_ROK, $pdu->sequence, "\x00");
				$this->_sendPDU($response);
			} else if ($pdu->id!=$command_id) { // if this is not the correct PDU add to queue
				array_push($this->pdu_queue, $pdu);
			}
		} while($pdu && $pdu->id!=$command_id);

		if($pdu) return $this->parseSMS($pdu);
		return false;
	}

	/**
	 * Reads incoming PDU from SMSC.
	 * @return SmppPdu
	 */
	protected function readPDU()
	{
		// Read PDU length
		$bufLength = fread($this->_socket, 4);
		if(!$bufLength) return false;
		extract(unpack("Nlength", $bufLength));

		// Read PDU headers
		$bufHeaders = fread($this->_socket, 12);
		if(!$bufHeaders)return false;
		extract(unpack("Ncommand_id/Ncommand_status/Nsequence_number", $bufHeaders));

		// Read PDU body
		$len = $length-16;
		if($len>0){
			$body = '';
			$got = 0;
			while (($got = strlen($body)) < $len) {
				$body .= fread($this->_socket, $len - $got);
			}
			if(!$body) throw new RuntimeException('Could not read PDU body');
		} else {
			$body=null;
		}

		if($this->_debug) {
			$this->debug("Read PDU         : $length bytes");
			$this->debug(' '.chunk_split(bin2hex($bufLength.$bufHeaders.$body),2," "));
			$this->debug(" command id      : 0x".dechex($command_id));
			$this->debug(" command status  : 0x".dechex($command_status)." ".SMPP::getStatusMessage($command_status));
			$this->debug(' sequence number : '.$sequence_number."<br />");
		}
		return new SmppPdu($command_id, $command_status, $sequence_number, $body);
	}

	/**
	 * Parse received PDU from SMSC.
	 * @param SmppPdu $pdu - received PDU from SMSC.
	 * @return parsed PDU as array.
	 */
	protected function parseSMS(SmppPdu $pdu)
	{
		// Check command id
		if($pdu->id != SMPP::DELIVER_SM) throw new InvalidArgumentException('PDU is not an received SMS');

		// Unpack PDU
		$ar=unpack("C*",$pdu->body);

		// Read mandatory params
		$service_type = $this->getString($ar,6,true);

		$source_addr_ton = next($ar);
		$source_addr_npi = next($ar);
		$source_addr = $this->getString($ar,21);
		$source = new SmppAddress($source_addr,$source_addr_ton,$source_addr_npi);

		$dest_addr_ton = next($ar);
		$dest_addr_npi = next($ar);
		$destination_addr = $this->getString($ar,21);
		$destination = new SmppAddress($destination_addr,$dest_addr_ton,$dest_addr_npi);

		$esmClass = next($ar);
		$protocolId = next($ar);
		$priorityFlag = next($ar);
		next($ar); // schedule_delivery_time
		next($ar); // validity_period
		$registeredDelivery = next($ar);
		next($ar); // replace_if_present_flag
		$dataCoding = next($ar);
		next($ar); // sm_default_msg_id
		$sm_length = next($ar);
		//$message = $this->getString($ar,$sm_length);
		//PATCH

		$message = '';
		while(current($ar) !== false) $message .= $this->getOctets($ar, 255);

		// Check for optional params, and parse them
		if (current($ar) !== false) {
			$tags = array();
			do {
				$tag = $this->parseTag($ar);
				if ($tag !== false) $tags[] = $tag;
			} while (current($ar) !== false);
		} else {
			$tags = null;
		}

		if (($esmClass & SMPP::ESM_DELIVER_SMSC_RECEIPT) != 0) {
			$sms = new SmppDeliveryReceipt($pdu->id, $pdu->status, $pdu->sequence, $pdu->body, $service_type, $source, $destination, $esmClass, $protocolId, $priorityFlag, $registeredDelivery, $dataCoding, $message, $tags);
			$sms->parseDeliveryReceipt();
		} else {
			$sms = new SmppSms($pdu->id, $pdu->status, $pdu->sequence, $pdu->body, $service_type, $source, $destination, $esmClass, $protocolId, $priorityFlag, $registeredDelivery, $dataCoding, $message, $tags);
		}

		if($this->_debug) $this->debug("Received sms:\n".print_r($sms,true));

		// Send response of recieving sms
		$response = new SmppPdu(SMPP::DELIVER_SM_RESP, SMPP::ESME_ROK, $pdu->sequence, "\x00");
		$this->_sendPDU($response);
		return $sms;
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
			$this->debug("SMPP Server returned error $status.<br />");
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


	function StatusSMS($message_id)
	{
		if (!$this->_socket) {
			// not connected
			return;
		}
		$pdu = "";

		$message_id = $message_id;

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

		$status = $this->SendQuerySM($message_id,$source_addr_ton,$source_addr_npi,$source_addr);

		if ($status != 0) {
			$this->debug("SMPP server returned error $status.<br />");
		}

		return $status;

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
			$this->debug("Not connected, while trying to send SUBMIT_MULTI.<br />");
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
			$text = mb_convert_encoding($text, "UCS-2BE", "UTF-8,HTML-ENTITIES"); //Patched by iZonder
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
				$this->debug("SMPP server returned error $status.<br />");
				$result = $status; //false; //Patched by iZonder
			}
		}
		return $result;
	}

	// private members (not documented):

	function ExpectPDU($our_sequence_number)
	{
		do {
			$this->debug("Trying to read PDU.<br />");
			if (feof($this->_socket)) {
				$this->debug("Socket was closed.!!<br />");
			}
			$elength = fread($this->_socket, 4);
			if (empty($elength)) {
				$this->debug("Connection lost.<br />");
				return;
			}
			extract(unpack("Nlength", $elength));
			$this->debug("Reading PDU     : $length bytes.<br />");
			$stream = fread($this->_socket, $length - 4);
			$this->debug("Stream len      : " . strlen($stream) . "<br />");
			extract(unpack("Ncommand_id/Ncommand_status/Nsequence_number", $stream));
			$command_id &= 0x0fffffff;
			$this->debug("Command id      : $command_id.<br />");
			$this->debug("Command status  : $command_status.<br />");
			$this->debug("sequence_number : $sequence_number.<br />");
			$pdu = substr($stream, 12);
			switch ($command_id) {
				case CM_BIND_TRANSMITTER:
					$this->debug("Got CM_BIND_TRANSMITTER_RESP.<br />");
					$spec = "asystem_id";
					extract($this->unpack2($spec, $pdu));
					$this->debug("system id       : $system_id.<br />");
					break;
				case CM_BIND_TRANSCEIVER:
					$this->debug("Got CM_BIND_TRANSCEIVER_RESP.<br />");
					$spec = "asystem_id";
					extract($this->unpack2($spec, $pdu));
					$this->debug("system id       : $system_id.<br />");
					break;
				case CM_UNBIND:
					$this->debug("Got CM_UNBIND_RESP.<br />");
					break;
				case CM_SUBMIT_SM:
					$this->debug("Got CM_SUBMIT_SM_RESP.<br />");
					if ($command_status == 0) {
						$spec = "amessage_id";
						extract($this->unpack2($spec, $pdu));
						$this->debug("message id      : $message_id.<br />");
						$this->_message_id = $message_id;
					}
					break;

				case CM_DELIVER_SM:
					 
					$body = substr($stream, 8, $length);
					/*$res = $this->parseSMS($body, $sequence_number);
					 echo "<br />$length<hr />";
					var_dump($stream);
					echo "<hr />";
					var_dump($body);
					echo "<hr />";
					var_dump($res);
					echo "<hr />";
					$state = $this->procsessms($res);*/
						
					$st = mb_strpos($stream,"Zid:");
					$ls = mb_strrpos($stream, ":")+8;
						
					$sms['short_message'] = mb_substr($stream, $st,  $ls-$st);
					$state = $this->procsessms($sms);
						
					$stat = substr($state['stat'],0,7);
					//$this->debug("dest_addr: ".$res['source_addr']."<br />");
					$this->debug("id_message: ".$state['Zid']."<br />");
					$this->debug("status: ".substr($state['stat'],0,7)."<br />");
					break;

				case CM_QUERY_SM:
					$this->debug("Got CM_QUERY_SM_RESP.<br />");
					if ($command_status == 0) {
						$spec = "amessage_id/cfinal_date/cmessage_state/cerror_code";
						//extract($this->unpack2($spec, $pdu));
						extract($this->unpack2($spec, $pdu));
						$this->debug("final_date     : $final_date.<br />");
						$this->debug("message_state      : $message_state.<br />");
						$this->debug("mess_id      : $message_id.<br />");
						$this->debug("error_code     : $error_code.<br />");
					}
					break;
				case CM_SUBMIT_MULTI:
					$this->debug("Got CM_SUBMIT_MULTI_RESP.<br />");
					$spec = "amessage_id/cno_unsuccess/";
					extract($this->unpack2($spec, $pdu));
					$this->debug("message id      : $message_id.<br />");
					$this->debug("no_unsuccess    : $no_unsuccess.<br />");
					break;
				case CM_ENQUIRELINK:
					$this->debug("GOT CM_ENQUIRELINK_RESP.<br />");
					break;
				default:
					$this->debug("Got unknown SMPP pdu.<br />");
					break;
			}
			$this->debug("<br />Received PDU: ");
			for ($i = 0; $i < strlen($stream); $i++) {
				if (ord($stream[$i]) < 32) $this->debug("(" . ord($stream[$i]) . ")"); else $this->debug($stream[$i]);
			}
			$this->debug("<br />");
		} while ($sequence_number != $our_sequence_number);
		return $command_status;
	}


	function SendPDU($command_id, $pdu)
	{
		$length = strlen($pdu) + 16;
		$header = pack("NNNN", $length, $command_id, $this->_command_status, $this->_sequence_number);
		$this->debug("Sending PDU, len == $length<br />");
		$this->debug("Sending PDU, header-len == " . strlen($header) .  "<br />");
		$this->debug("Sending PDU, command_id == " . $command_id  .  "<br />");
		fwrite($this->_socket, $header . $pdu, $length);
		$status = $this->ExpectPDU($this->_sequence_number);
		$this->_sequence_number = $this->_sequence_number + 1;
		return $status;
	}



	/**
	 * Prepares and sends PDU to SMSC.
	 * @param SmppPdu $pdu
	 */
	protected function _sendPDU(SmppPdu $pdu)
	{
		$length=strlen($pdu->body) + 16;
		$header=pack("NNNN", $length, $pdu->id, $pdu->status, $pdu->sequence);
		if($this->_debug) {
			$this->debug("Send PDU         : $length bytes");
			$this->debug(' '.chunk_split(bin2hex($header.$pdu->body),2," "));
			$this->debug(' command_id      : 0x'.dechex($pdu->id));
			$this->debug(' sequence number : '.$pdu->sequence.'<br />');
		}
		fwrite($this->_socket, $header.$pdu->body, $length);
	}

	function SendBindTransmitter($system_id, $smpppassword, $system_type)
	{
		$system_id = $system_id . chr(0);
		$system_id_len = strlen($system_id);
		$smpppassword = $smpppassword . chr(0);
		$smpppassword_len = strlen($smpppassword);
		$system_type = $system_type . chr(0);
		$system_type_len = strlen($system_type);
		$pdu = pack("a{$system_id_len}a{$smpppassword_len}a{$system_type_len}CCCa1", $system_id, $smpppassword, $system_type, 0x33, 0, 0, chr(0));
		$this->debug("Bind Transmitter PDU: ");
		for ($i = 0; $i < strlen($pdu); $i++) {
			$this->debug(ord($pdu[$i]) . " ");
		}
		$this->debug("<br />");
		$status = $this->SendPDU(CM_BIND_TRANSMITTER, $pdu);
		return $status;
	}

	function SendBindTransceiver($system_id, $smpppassword, $system_type)
	{
		$system_id = $system_id . chr(0);
		$system_id_len = strlen($system_id);
		$smpppassword = $smpppassword . chr(0);
		$smpppassword_len = strlen($smpppassword);
		$system_type = $system_type . chr(0);
		$system_type_len = strlen($system_type);
		$pdu = pack("a{$system_id_len}a{$smpppassword_len}a{$system_type_len}CCCa1", $system_id, $smpppassword, $system_type, 0x33, 0, 0, chr(0));
		$this->debug("Bind Transceiver PDU: ");
		for ($i = 0; $i < strlen($pdu); $i++) {
			$this->debug(ord($pdu[$i]) . " ");
		}
		$this->debug("<br />");
		$status = $this->SendPDU(CM_BIND_TRANSCEIVER, $pdu);
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
		$this->debug("PDU spec: $spec.<br />");

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

		$this->debug("<br />Multi PDU: ");
		for ($i = 0; $i < strlen($pdu); $i++) {
			if (ord($pdu[$i]) < 32) $this->debug("."); else $this->debug($pdu[$i]);
		}
		$this->debug("<br />");

		$status = $this->SendPDU(CM_SUBMIT_MULTI, $pdu);
		return $status;
	}

	function SendQuerySM($message_id,$source_addr_ton,$source_addr_npi,$source_addr){

		$message_id = $message_id.chr(0);
		$message_id_len  = strlen($message_id);
		$source_addr_ton = $source_addr_ton.chr(0);
		$source_addr_npi = $source_addr_npi.chr(0);
		$source_addr = $source_addr . chr(0);
		$source_addr_len = strlen($source_addr);

		$pdu = pack("a{$message_id_len}cca{$source_addr_len}",$message_id,$source_addr_ton,$source_addr_npi,$source_addr);
		$status = $this->SendPDU(CM_QUERY_SM, $pdu);
		return $status;

	}

	function split_message($text)
	{
		$this->debug("In split_message.<br />");
		$max_len = 153;
		$res = array();
		if (strlen($text) <= 160) {
			$this->debug("One message: " . strlen($text) . "<br />");
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
			$this->debug("<br />");
			$this->debug("Split: $ttext.<br />");
		}
		return $res;
	}

	function split_message_unicode($text)
	{
		$this->debug("In split_message.<br />");
		$max_len = 134;
		$res = array();
		if (mb_strlen($text) <= 140) {
			$this->debug("One message: " . mb_strlen($text) . "<br />");
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
			$this->debug("<br />");
			$this->debug("Split: $ttext.<br />");
		}
		return $res;
	}

	function unpack2($spec, $data)
	{
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

	function procsessms($sms)
	{


		$mass=array();

		$dl = explode(' ', $sms['short_message']);

		foreach($dl as $arr){
			$res = explode(':',$arr);
			if(isset($res[1])) $mass[$res[0]]=$res[1];
		}

		return $mass;
	}

	/*function parseSMS($body, $sequence_number)
	 {
	//check command id

	//unpack PDU
	$ar = unpack("C*", $body);
	//$ar=unpack("C*",$pdu['body']);
	$sms = array('service_type' => $this->getString($ar, 6), 'source_addr_ton' => array_shift($ar), 'source_addr_npi' => array_shift($ar), 'source_addr' => $this->getString($ar, 21), 'dest_addr_ton' => array_shift($ar), 'dest_addr_npi' => array_shift($ar), 'destination_addr' => $this->getString($ar, 21), 'esm_class' => array_shift($ar), 'protocol_id' => array_shift($ar), 'priority_flag' => array_shift($ar), 'schedule_delivery_time' => array_shift($ar), 'validity_period' => array_shift($ar), 'registered_delivery' => array_shift($ar), 'replace_if_present_flag' => array_shift($ar), 'data_coding' => array_shift($ar), 'sm_default_msg_id' => array_shift($ar), 'sm_length' => array_shift($ar), 'short_message' => $this->getString($ar, 255));

	// $this->_sequence_number=$sequence_number;

	//send responce of recieving sms

	return $sms;
	}*/

	/*function getString(&$ar, $maxlen = 255)
	 {
	$s = "";
	$i = 0;
	do {

	$c = array_shift($ar);
	if ($c != 0)
		$s .= chr($c);
	$i++;
	} while ($i < $maxlen && $c != 0);
	return $s;
	}*/

	/**
	 * Reads C style null padded string from the char array.
	* Reads until $maxlen or null byte.
	*
	* @param array $ar - input array
	* @param integer $maxlen - maximum length to read.
	* @param boolean $firstRead - is this the first bytes read from array?
	* @return read string.
	*/
	protected function getString(&$ar, $maxlen=255, $firstRead=false)
	{
		$s="";
		$i=0;
		do{
			$c = ($firstRead && $i==0) ? current($ar) : next($ar);
			if ($c != 0) $s .= chr($c);
			$i++;
		} while($i<$maxlen && $c !=0);
		return $s;
	}

	/**
	 * Read a specific number of octets from the char array.
	 * Does not stop at null byte
	 *
	 * @param array $ar - input array
	 * @param intger $length
	 */
	protected function getOctets(&$ar,$length)
	{
		$s = "";
		for($i=0;$i<$length;$i++) {
			$c = next($ar);
			if ($c === false) return $s;
			$s .= chr($c);
		}
		return $s;
	}

	protected function parseTag(&$ar)
	{
		$unpackedData = unpack('nid/nlength',pack("C2C2",next($ar),next($ar),next($ar),next($ar)));
		if (!$unpackedData) throw new InvalidArgumentException('Could not read tag data');
		extract($unpackedData);

		// Sometimes SMSC return an extra null byte at the end
		if ($length==0 && $id == 0) {
			return false;
		}

		$value = $this->getOctets($ar,$length);
		$tag = new SmppTag($id, $value, $length);
		if ($this->_debug) {
			$this->debug("Parsed tag:");
			$this->debug(" id     :0x".dechex($tag->id));
			$this->debug(" length :".$tag->length);
			$this->debug(" value  :".chunk_split(bin2hex($tag->value),2," "));
		}
		return $tag;
	}

	/**
	 * @private function
	 * Prints the binary string as hex bytes.
	 * @param $maxlen - maximum length to read.
	 */
	function printHex($pdu)
	{
		$a = "";
		$ar = unpack("C*", $pdu);
		foreach ($ar as $v) {
			$s = dechex($v);
			if (strlen($s) < 2)
				$s = "0$s";
			$a .= "$s ";
		}
		return $a . "<br />";
	}


	function strToHTML($text) {
		$trans = array(
				"а" => "&#1072;",
				"б" => "&#1073;",
				"в" => "&#1074;",
				"г" => "&#1075;",
				"д" => "&#1076;",
				"е" => "&#1077;",
				"ё" => "&#1105;",
				"ж" => "&#1078;",
				"з" => "&#1079;",
				"и" => "&#1080;",
				"й" => "&#1081;",
				"к" => "&#1082;",
				"л" => "&#1083;",
				"м" => "&#1084;",
				"н" => "&#1085;",
				"о" => "&#1086;",
				"п" => "&#1087;",
				"р" => "&#1088;",
				"с" => "&#1089;",
				"т" => "&#1090;",
				"у" => "&#1091;",
				"ф" => "&#1092;",
				"х" => "&#1093;",
				"ц" => "&#1094;",
				"ч" => "&#1095;",
				"ш" => "&#1096;",
				"щ" => "&#1097;",
				"ъ" => "&#1098;",
				"ы" => "&#1099;",
				"ь" => "&#1100;",
				"э" => "&#1101;",
				"ю" => "&#1102;",
				"я" => "&#1103;",
				"і" => "&#1110;",
				"ї"=>  "&#1111;",
				"є" => "&#1108;",

				"А" => "&#1040;",
				"Б" => "&#1041;",
				"В" => "&#1042;",
				"Г" => "&#1043;",
				"Д" => "&#1044;",
				"Е" => "&#1045;",
				"Ё" => "&#1025;",
				"Ж" => "&#1046;",
				"З" => "&#1047;",
				"І" => "&#1030;",
				"И" => "&#1048;",
				"Й" => "&#1049;",
				"К" => "&#1050;",
				"Л" => "&#1051;",
				"М" => "&#1052;",
				"Н" => "&#1053;",
				"О" => "&#1054;",
				"П" => "&#1055;",
				"Р" => "&#1056;",
				"С" => "&#1057;",
				"Т" => "&#1058;",
				"У" => "&#1059;",
				"Ф" => "&#1060;",
				"Х" => "&#1061;",
				"Ц" => "&#1062;",
				"Ч" => "&#1063;",
				"Ш" => "&#1064;",
				"Щ" => "&#1065;",
				"Ъ" => "&#1066;",
				"Ы" => "&#1067;",
				"Ь" => "&#1068;",
				"Э" => "&#1069;",
				"Ю" => "&#1070;",
				"Я" => "&#1071;",
				"Ї" => "&#1031;",
				"Є" => "&#1028;",

				"«" => "&laquo;",
				"»" => "&raquo;"
		);

		if(preg_match("/[а-яА-Я]/", $text)) {
			return strtr($text, $trans);
		}
		else {
			return $text;
		}
	}

	function debug($str)
	{
		if ($this->_debug) {
			echo $str;
		}
	}
}

class SmppException extends RuntimeException
{

}


/**
 * Numerous constants for SMPP v3.4
 * Based on specification at: http://www.smsforum.net/SMPP_v3_4_Issue1_2.zip
 */

class SMPP
{
	// Command ids - SMPP v3.4 - 5.1.2.1 page 110-111
	const GENERIC_NACK = 0x80000000;
	const BIND_RECEIVER = 0x00000001;
	const BIND_RECEIVER_RESP = 0x80000001;
	const BIND_TRANSMITTER = 0x00000002;
	const BIND_TRANSMITTER_RESP = 0x80000002;
	const QUERY_SM = 0x00000003;
	const QUERY_SM_RESP = 0x80000003;
	const SUBMIT_SM = 0x00000004;
	const SUBMIT_SM_RESP = 0x80000004;
	const DELIVER_SM = 0x00000005;
	const DELIVER_SM_RESP = 0x80000005;
	const UNBIND = 0x00000006;
	const UNBIND_RESP = 0x80000006;
	const REPLACE_SM = 0x00000007;
	const REPLACE_SM_RESP = 0x80000007;
	const CANCEL_SM = 0x00000008;
	const CANCEL_SM_RESP = 0x80000008;
	const BIND_TRANSCEIVER = 0x00000009;
	const BIND_TRANSCEIVER_RESP = 0x80000009;
	const OUTBIND = 0x0000000B;
	const ENQUIRE_LINK = 0x00000015;
	const ENQUIRE_LINK_RESP = 0x80000015;

	//  Command status - SMPP v3.4 - 5.1.3 page 112-114
	const ESME_ROK = 0x00000000; // No Error
	const ESME_RINVMSGLEN = 0x00000001; // Message Length is invalid
	const ESME_RINVCMDLEN = 0x00000002; // Command Length is invalid
	const ESME_RINVCMDID = 0x00000003; // Invalid Command ID
	const ESME_RINVBNDSTS = 0x00000004; // Incorrect BIND Status for given command
	const ESME_RALYBND = 0x00000005; // ESME Already in Bound State
	const ESME_RINVPRTFLG = 0x00000006; // Invalid Priority Flag
	const ESME_RINVREGDLVFLG = 0x00000007; // Invalid Registered Delivery Flag
	const ESME_RSYSERR = 0x00000008; // System Error
	const ESME_RINVSRCADR = 0x0000000A; // Invalid Source Address
	const ESME_RINVDSTADR = 0x0000000B; // Invalid Dest Addr
	const ESME_RINVMSGID = 0x0000000C; // Message ID is invalid
	const ESME_RBINDFAIL = 0x0000000D; // Bind Failed
	const ESME_RINVPASWD = 0x0000000E; // Invalid Password
	const ESME_RINVSYSID = 0x0000000F; // Invalid System ID
	const ESME_RCANCELFAIL = 0x00000011; // Cancel SM Failed
	const ESME_RREPLACEFAIL = 0x00000013; // Replace SM Failed
	const ESME_RMSGQFUL = 0x00000014; // Message Queue Full
	const ESME_RINVSERTYP = 0x00000015; // Invalid Service Type
	const ESME_RINVNUMDESTS = 0x00000033; // Invalid number of destinations
	const ESME_RINVDLNAME = 0x00000034; // Invalid Distribution List name
	const ESME_RINVDESTFLAG = 0x00000040; // Destination flag (submit_multi)
	const ESME_RINVSUBREP = 0x00000042; // Invalid ‘submit with replace’ request (i.e. submit_sm with replace_if_present_flag set)
	const ESME_RINVESMSUBMIT = 0x00000043; // Invalid esm_SUBMIT field data
	const ESME_RCNTSUBDL = 0x00000044; // Cannot Submit to Distribution List
	const ESME_RSUBMITFAIL = 0x00000045; // submit_sm or submit_multi failed
	const ESME_RINVSRCTON = 0x00000048; // Invalid Source address TON
	const ESME_RINVSRCNPI = 0x00000049; // Invalid Source address NPI
	const ESME_RINVDSTTON = 0x00000050; // Invalid Destination address TON
	const ESME_RINVDSTNPI = 0x00000051; // Invalid Destination address NPI
	const ESME_RINVSYSTYP = 0x00000053; // Invalid system_type field
	const ESME_RINVREPFLAG = 0x00000054; // Invalid replace_if_present flag
	const ESME_RINVNUMMSGS = 0x00000055; // Invalid number of messages
	const ESME_RTHROTTLED = 0x00000058; // Throttling error (ESME has exceeded allowed message limits)
	const ESME_RINVSCHED = 0x00000061; // Invalid Scheduled Delivery Time
	const ESME_RINVEXPIRY = 0x00000062; // Invalid message (Expiry time)
	const ESME_RINVDFTMSGID = 0x00000063; // Predefined Message Invalid or Not Found
	const ESME_RX_T_APPN = 0x00000064; // ESME Receiver Temporary App Error Code
	const ESME_RX_P_APPN = 0x00000065; // ESME Receiver Permanent App Error Code
	const ESME_RX_R_APPN = 0x00000066; // ESME Receiver Reject Message Error Code
	const ESME_RQUERYFAIL = 0x00000067; // query_sm request failed
	const ESME_RINVOPTPARSTREAM = 0x000000C0; // Error in the optional part of the PDU Body.
	const ESME_ROPTPARNOTALLWD = 0x000000C1; // Optional Parameter not allowed
	const ESME_RINVPARLEN = 0x000000C2; // Invalid Parameter Length.
	const ESME_RMISSINGOPTPARAM = 0x000000C3; // Expected Optional Parameter missing
	const ESME_RINVOPTPARAMVAL = 0x000000C4; // Invalid Optional Parameter Value
	const ESME_RDELIVERYFAILURE = 0x000000FE; // Delivery Failure (data_sm_resp)
	const ESME_RUNKNOWNERR = 0x000000FF; // Unknown Error

	// SMPP v3.4 - 5.2.5 page 117
	const TON_UNKNOWN = 0x00;
	const TON_INTERNATIONAL = 0x01;
	const TON_NATIONAL = 0x02;
	const TON_NETWORKSPECIFIC = 0x03;
	const TON_SUBSCRIBERNUMBER = 0x04;
	const TON_ALPHANUMERIC = 0x05;
	const TON_ABBREVIATED = 0x06;

	// SMPP v3.4 - 5.2.6 page 118
	const NPI_UNKNOWN = 0x00;
	const NPI_E164 = 0x01;
	const NPI_DATA = 0x03;
	const NPI_TELEX = 0x04;
	const NPI_E212 = 0x06;
	const NPI_NATIONAL = 0x08;
	const NPI_PRIVATE = 0x09;
	const NPI_ERMES = 0x0a;
	const NPI_INTERNET = 0x0e;
	const NPI_WAPCLIENT = 0x12;

	// ESM bits 1-0 - SMPP v3.4 - 5.2.12 page 121-122
	const ESM_SUBMIT_MODE_DATAGRAM = 0x01;
	const ESM_SUBMIT_MODE_FORWARD = 0x02;
	const ESM_SUBMIT_MODE_STOREANDFORWARD = 0x03;
	// ESM bits 5-2
	const ESM_SUBMIT_BINARY = 0x04;
	const ESM_SUBMIT_TYPE_ESME_D_ACK = 0x08;
	const ESM_SUBMIT_TYPE_ESME_U_ACK = 0x10;
	const ESM_DELIVER_SMSC_RECEIPT = 0x04;
	const ESM_DELIVER_SME_ACK = 0x08;
	const ESM_DELIVER_U_ACK = 0x10;
	const ESM_DELIVER_CONV_ABORT = 0x18;
	const ESM_DELIVER_IDN = 0x20; // Intermediate delivery notification
	// ESM bits 7-6
	const ESM_UHDI = 0x40;
	const ESM_REPLYPATH = 0x80;

	// SMPP v3.4 - 5.2.17 page 124
	const REG_DELIVERY_NO = 0x00;
	const REG_DELIVERY_SMSC_BOTH = 0x01; // both success and failure
	const REG_DELIVERY_SMSC_FAILED = 0x02;
	const REG_DELIVERY_SME_D_ACK = 0x04;
	const REG_DELIVERY_SME_U_ACK = 0x08;
	const REG_DELIVERY_SME_BOTH = 0x10;
	const REG_DELIVERY_IDN = 0x16; // Intermediate notification

	// SMPP v3.4 - 5.2.18 page 125
	const REPLACE_NO = 0x00;
	const REPLACE_YES = 0x01;

	// SMPP v3.4 - 5.2.19 page 126
	const DATA_CODING_DEFAULT = 0;
	const DATA_CODING_IA5 = 1; // IA5 (CCITT T.50)/ASCII (ANSI X3.4)
	const DATA_CODING_BINARY_ALIAS = 2;
	const DATA_CODING_ISO8859_1 = 3; // Latin 1
	const DATA_CODING_BINARY = 4;
	const DATA_CODING_JIS = 5;
	const DATA_CODING_ISO8859_5 = 6; // Cyrllic
	const DATA_CODING_ISO8859_8 = 7; // Latin/Hebrew
	const DATA_CODING_UCS2 = 8; // UCS-2BE (Big Endian)
	const DATA_CODING_PICTOGRAM = 9;
	const DATA_CODING_ISO2022_JP = 10; // Music codes
	const DATA_CODING_KANJI = 13; // Extended Kanji JIS
	const DATA_CODING_KSC5601 = 14;

	// SMPP v3.4 - 5.2.25 page 129
	const DEST_FLAG_SME = 1;
	const DEST_FLAG_DISTLIST = 2;

	// SMPP v3.4 - 5.2.28 page 130
	const STATE_ENROUTE = 1;
	const STATE_DELIVERED = 2;
	const STATE_EXPIRED = 3;
	const STATE_DELETED = 4;
	const STATE_UNDELIVERABLE = 5;
	const STATE_ACCEPTED = 6;
	const STATE_UNKNOWN = 7;
	const STATE_REJECTED = 8;


	public static function getStatusMessage($statuscode)
	{
		switch ($statuscode) {
			case SMPP::ESME_ROK: return 'No Error';
			case SMPP::ESME_RINVMSGLEN: return 'Message Length is invalid';
			case SMPP::ESME_RINVCMDLEN: return 'Command Length is invalid';
			case SMPP::ESME_RINVCMDID: return 'Invalid Command ID';
			case SMPP::ESME_RINVBNDSTS: return 'Incorrect BIND Status for given command';
			case SMPP::ESME_RALYBND: return 'ESME Already in Bound State';
			case SMPP::ESME_RINVPRTFLG: return 'Invalid Priority Flag';
			case SMPP::ESME_RINVREGDLVFLG: return 'Invalid Registered Delivery Flag';
			case SMPP::ESME_RSYSERR: return 'System Error';
			case SMPP::ESME_RINVSRCADR: return 'Invalid Source Address';
			case SMPP::ESME_RINVDSTADR: return 'Invalid Dest Addr';
			case SMPP::ESME_RINVMSGID: return 'Message ID is invalid';
			case SMPP::ESME_RBINDFAIL: return 'Bind Failed';
			case SMPP::ESME_RINVPASWD: return 'Invalid Password';
			case SMPP::ESME_RINVSYSID: return 'Invalid System ID';
			case SMPP::ESME_RCANCELFAIL: return 'Cancel SM Failed';
			case SMPP::ESME_RREPLACEFAIL: return 'Replace SM Failed';
			case SMPP::ESME_RMSGQFUL: return 'Message Queue Full';
			case SMPP::ESME_RINVSERTYP: return 'Invalid Service Type';
			case SMPP::ESME_RINVNUMDESTS: return 'Invalid number of destinations';
			case SMPP::ESME_RINVDLNAME: return 'Invalid Distribution List name';
			case SMPP::ESME_RINVDESTFLAG: return 'Destination flag (submit_multi)';
			case SMPP::ESME_RINVSUBREP: return 'Invalid ‘submit with replace’ request (i.e. submit_sm with replace_if_present_flag set)';
			case SMPP::ESME_RINVESMSUBMIT: return 'Invalid esm_SUBMIT field data';
			case SMPP::ESME_RCNTSUBDL: return 'Cannot Submit to Distribution List';
			case SMPP::ESME_RSUBMITFAIL: return 'submit_sm or submit_multi failed';
			case SMPP::ESME_RINVSRCTON: return 'Invalid Source address TON';
			case SMPP::ESME_RINVSRCNPI: return 'Invalid Source address NPI';
			case SMPP::ESME_RINVDSTTON: return 'Invalid Destination address TON';
			case SMPP::ESME_RINVDSTNPI: return 'Invalid Destination address NPI';
			case SMPP::ESME_RINVSYSTYP: return 'Invalid system_type field';
			case SMPP::ESME_RINVREPFLAG: return 'Invalid replace_if_present flag';
			case SMPP::ESME_RINVNUMMSGS: return 'Invalid number of messages';
			case SMPP::ESME_RTHROTTLED: return 'Throttling error (ESME has exceeded allowed message limits)';
			case SMPP::ESME_RINVSCHED: return 'Invalid Scheduled Delivery Time';
			case SMPP::ESME_RINVEXPIRY: return 'Invalid message (Expiry time)';
			case SMPP::ESME_RINVDFTMSGID: return 'Predefined Message Invalid or Not Found';
			case SMPP::ESME_RX_T_APPN: return 'ESME Receiver Temporary App Error Code';
			case SMPP::ESME_RX_P_APPN: return 'ESME Receiver Permanent App Error Code';
			case SMPP::ESME_RX_R_APPN: return 'ESME Receiver Reject Message Error Code';
			case SMPP::ESME_RQUERYFAIL: return 'query_sm request failed';
			case SMPP::ESME_RINVOPTPARSTREAM: return 'Error in the optional part of the PDU Body.';
			case SMPP::ESME_ROPTPARNOTALLWD: return 'Optional Parameter not allowed';
			case SMPP::ESME_RINVPARLEN: return 'Invalid Parameter Length.';
			case SMPP::ESME_RMISSINGOPTPARAM: return 'Expected Optional Parameter missing';
			case SMPP::ESME_RINVOPTPARAMVAL: return 'Invalid Optional Parameter Value';
			case SMPP::ESME_RDELIVERYFAILURE: return 'Delivery Failure (data_sm_resp)';
			case SMPP::ESME_RUNKNOWNERR: return 'Unknown Error';
			default:
				return 'Unknown statuscode: '.dechex($statuscode);
		}
	}
}

/**
 * Primitive class for encapsulating PDUs
 * @author hd@onlinecity.dk
 */
class SmppPdu
{
	public $id;
	public $status;
	public $sequence;
	public $body;

	/**
	 * Create new generic PDU object
	 *
	 * @param integer $id
	 * @param integer $status
	 * @param integer $sequence
	 * @param string $body
	 */
	public function __construct($id, $status, $sequence, $body)
	{
		$this->id = $id;
		$this->status = $status;
		$this->sequence = $sequence;
		$this->body = $body;
	}
}

/**
 * An extension of a SMS, with data embedded into the message part of the SMS.
 * @author hd@onlinecity.dk
 */
class SmppDeliveryReceipt extends SmppSms
{
	public $id;
	public $sub;
	public $dlvrd;
	public $submitDate;
	public $doneDate;
	public $stat;
	public $err;
	public $text;

	/**
	 * Parse a delivery receipt formatted as specified in SMPP v3.4 - Appendix B
	 * It accepts all chars except space as the message id
	 *
	 * @throws InvalidArgumentException
	 */
	public function parseDeliveryReceipt()
	{
		$numMatches = preg_match('/^id:([^ ]+) sub:(\d{1,3}) dlvrd:(\d{3}) submit date:(\d{10}) done date:(\d{10}) stat:([A-Z]{7}) err:(\d{3}) text:(.*)$/ms', $this->message, $matches);
		if ($numMatches == 0) {
			throw new InvalidArgumentException('Could not parse delivery receipt: '.$this->message."\n".bin2hex($this->body));
		}
		list($matched, $this->id, $this->sub, $this->dlvrd, $this->submitDate, $this->doneDate, $this->stat, $this->err, $this->text) = $matches;

		// Convert dates
		$dp = str_split($this->submitDate,2);
		$this->submitDate = gmmktime($dp[3],$dp[4],0,$dp[1],$dp[2],$dp[0]);
		$dp = str_split($this->doneDate,2);
		$this->doneDate = gmmktime($dp[3],$dp[4],0,$dp[1],$dp[2],$dp[0]);
	}
}

/**
 * Primitive type to represent SMSes
 * @author hd@onlinecity.dk
 */
class SmppSms extends SmppPdu
{
	public $service_type;
	public $source;
	public $destination;
	public $esmClass;
	public $protocolId;
	public $priorityFlag;
	public $registeredDelivery;
	public $dataCoding;
	public $message;
	public $tags;

	// Unused in deliver_sm
	public $scheduleDeliveryTime;
	public $validityPeriod;
	public $smDefaultMsgId;
	public $replaceIfPresentFlag;

	/**
	 * Construct a new SMS
	 *
	 * @param integer $id
	 * @param integer $status
	 * @param integer $sequence
	 * @param string $body
	 * @param string $service_type
	 * @param Address $source
	 * @param Address $destination
	 * @param integer $esmClass
	 * @param integer $protocolId
	 * @param integer $priorityFlag
	 * @param integer $registeredDelivery
	 * @param integer $dataCoding
	 * @param string $message
	 * @param array $tags (optional)
	 * @param string $scheduleDeliveryTime (optional)
	 * @param string $validityPeriod (optional)
	 * @param integer $smDefaultMsgId (optional)
	 * @param integer $replaceIfPresentFlag (optional)
	 */
	public function __construct($id, $status, $sequence, $body, $service_type, SmppAddress $source, SmppAddress $destination,
			$esmClass, $protocolId, $priorityFlag, $registeredDelivery, $dataCoding, $message, $tags,
			$scheduleDeliveryTime=null, $validityPeriod=null, $smDefaultMsgId=null, $replaceIfPresentFlag=null)
	{
		parent::__construct($id, $status, $sequence, $body);
		$this->service_type = $service_type;
		$this->source = $source;
		$this->destination = $destination;
		$this->esmClass = $esmClass;
		$this->protocolId = $protocolId;
		$this->priorityFlag = $priorityFlag;
		$this->registeredDelivery = $registeredDelivery;
		$this->dataCoding = $dataCoding;
		$this->message = $message;
		$this->tags = $tags;
		$this->scheduleDeliveryTime = $scheduleDeliveryTime;
		$this->validityPeriod = $validityPeriod;
		$this->smDefaultMsgId = $smDefaultMsgId;
		$this->replaceIfPresentFlag = $replaceIfPresentFlag;
	}

}

/**
 * Primitive class for encapsulating smpp addresses
 * @author hd@onlinecity.dk
 */
class SmppAddress
{
	public $ton; // type-of-number
	public $npi; // numbering-plan-indicator
	public $value;

	/**
	 * Construct a new object of class Address
	 *
	 * @param string $value
	 * @param integer $ton
	 * @param integer $npi
	 * @throws InvalidArgumentException
	 */
	public function __construct($value,$ton=SMPP::TON_UNKNOWN,$npi=SMPP::NPI_UNKNOWN)
	{
		// Address-Value field may contain 10 octets (12-length-type), see 3GPP TS 23.040 v 9.3.0 - section 9.1.2.5 page 46.
		if ($ton == SMPP::TON_ALPHANUMERIC && strlen($value) > 11) throw new InvalidArgumentException('Alphanumeric address may only contain 11 chars');
		if ($ton == SMPP::TON_INTERNATIONAL && $npi == SMPP::NPI_E164 && strlen($value) > 15) throw new InvalidArgumentException('E164 address may only contain 15 digits');

		$this->value = (string) $value;
		$this->ton = $ton;
		$this->npi = $npi;
	}
}

/**
 * Primitive class to represent SMPP optional params, also know as TLV (Tag-Length-Value) params
 * @author hd@onlinecity.dk
 */
class SmppTag
{
	public $id;
	public $length;
	public $value;
	public $type;

	const DEST_ADDR_SUBUNIT = 0x0005;
	const DEST_NETWORK_TYPE = 0x0006;
	const DEST_BEARER_TYPE = 0x0007;
	const DEST_TELEMATICS_ID = 0x0008;
	const SOURCE_ADDR_SUBUNIT = 0x000D;
	const SOURCE_NETWORK_TYPE = 0x000E;
	const SOURCE_BEARER_TYPE = 0x000F;
	const SOURCE_TELEMATICS_ID = 0x0010;
	const QOS_TIME_TO_LIVE = 0x0017;
	const PAYLOAD_TYPE = 0x0019;
	const ADDITIONAL_STATUS_INFO_TEXT = 0x001D;
	const RECEIPTED_MESSAGE_ID = 0x001E;
	const MS_MSG_WAIT_FACILITIES = 0x0030;
	const PRIVACY_INDICATOR = 0x0201;
	const SOURCE_SUBADDRESS = 0x0202;
	const DEST_SUBADDRESS = 0x0203;
	const USER_MESSAGE_REFERENCE = 0x0204;
	const USER_RESPONSE_CODE = 0x0205;
	const SOURCE_PORT = 0x020A;
	const DESTINATION_PORT = 0x020B;
	const SAR_MSG_REF_NUM = 0x020C;
	const LANGUAGE_INDICATOR = 0x020D;
	const SAR_TOTAL_SEGMENTS = 0x020E;
	const SAR_SEGMENT_SEQNUM = 0x020F;
	const SC_INTERFACE_VERSION = 0x0210;
	const CALLBACK_NUM_PRES_IND = 0x0302;
	const CALLBACK_NUM_ATAG = 0x0303;
	const NUMBER_OF_MESSAGES = 0x0304;
	const CALLBACK_NUM = 0x0381;
	const DPF_RESULT = 0x0420;
	const SET_DPF = 0x0421;
	const MS_AVAILABILITY_STATUS = 0x0422;
	const NETWORK_ERROR_CODE = 0x0423;
	const MESSAGE_PAYLOAD = 0x0424;
	const DELIVERY_FAILURE_REASON = 0x0425;
	const MORE_MESSAGES_TO_SEND = 0x0426;
	const MESSAGE_STATE = 0x0427;
	const USSD_SERVICE_OP = 0x0501;
	const DISPLAY_TIME = 0x1201;
	const SMS_SIGNAL = 0x1203;
	const MS_VALIDITY = 0x1204;
	const ALERT_ON_MESSAGE_DELIVERY = 0x130C;
	const ITS_REPLY_TYPE = 0x1380;
	const ITS_SESSION_INFO = 0x1383;


	/**
	 * Construct a new TLV param.
	 * The value must either be pre-packed with pack(), or a valid pack-type must be specified.
	 *
	 * @param integer $id
	 * @param string $value
	 * @param integer $length (optional)
	 * @param string $type (optional)
	 */
	public function __construct($id, $value, $length=null, $type='a*')
	{
		$this->id = $id;
		$this->value = $value;
		$this->length = $length;
		$this->type = $type;
	}

	/**
	 * Get the TLV packed into a binary string for transport
	 * @return string
	 */
	public function getBinary()
	{
		return pack('nn'.$this->type, $this->id, ($this->length ? $this->length : strlen($this->value)), $this->value);
	}
}