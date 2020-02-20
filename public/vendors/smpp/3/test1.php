<?

/*

File: smppclass.php
Implements: SMPPCLass()
License: GNU Lesser Genercal Public License: http://www.gnu.org/licenses/lgpl.html
Commercial advertisement: Contact info@chimit.nl for SMS connectivity and more elaborate SMPP libraries in PHP and other languages.

*/

/*

The following are the SMPP PDU types that we are using in this class.
Apart from the following 3 PDU types, there are a lot of SMPP directives
that are not implemented in this version.

*/

define(CM_BIND_TRANSMITTER, 0x00000002);
define(CM_SUBMIT_SM, 0x00000021);
define(CM_UNBIND, 0x00000006);

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
		$this->_socket = NULL;
		$this->_command_status = 0;
		$this->_sequence_number = 1;
		$this->_source_address = "3369570515";
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
			// todo
			echo "Error: sender id too long.\n";
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
	Example:
		$smpp->Start("smpp.chimit.nl", 2345, "chimit", "my_password", "client01");
	*/
	function Start($host, $port, $username, $password, $system_type)
	{
		$this->_socket = fsockopen($host, $port, $errno, $errstr);
		// todo: sanity check on input parameters
		if (!$this->_socket) {
			echo "Error opening SMPP session.\n";
			echo "Error was: $errstr.\n";
			return;
		}
		socket_set_timeout($this->_socket, 1200);
		$status = $this->SendBindTransmitter($username, $password, $system_type);
		if ($status != 0) {
			echo "Error binding to SMPP server. Invalid credentials?\n";
		}
	}

	/*
	This method sends out one SMS message.
	Parameters:
		$to	: destination address.
		$text	: text of message to send.
	Example:
		$smpp->Send("31495595392", "This is an SMPP Test message.");
	*/
	function Send($to, $text)
	{
		if (strlen($to) > 20) {
			echo "to-address too long.\n";
			return;
		}
		if (strlen($text) > 160) {
			echo "Message too long.\n";
			return;
		}
		if (!$this->_socket) {
			// not connected
			return;
		}
		$service_type = "";
		$source_addr_ton = 0;
		$source_addr_npi = 0;
		$source_addr = $this->_source_address;
		$dest_addr_ton = 1;
		$dest_addr_npi = 1;
		$destination_addr = $to;
		$esm_class = 0;
		$protocol_id = 0;
		$priority_flag = 0;
		$schedule_delivery_time = "";
		$validity_period = "";
		$registered_delivery_flag = 0;
		$replace_if_present_flag = 0;
		$data_coding = 0;
		$sm_default_msg_id = 0;
		$sm_length = strlen($text);
		$short_message = $text;
		$status = $this->SendSubmitSM($service_type, $source_addr_ton, $source_addr_npi, $source_addr, $dest_addr_ton, $dest_addr_npi, $destination_addr, $esm_class, $protocol_id, $priority_flag, $schedule_delivery_time, $validity_period, $registered_delivery_flag, $replace_if_present_flag, $data_coding, $sm_default_msg_id, $sm_length, $short_message);
		if ($status != 0) {
			echo "SMPP server returned error $status.\n";
		}
	}

	/*
	This method ends a SMPP session.
	Parameters:
		none
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
			echo "SMPP Server returned error $status.\n";
		}
		fclose($this->_socket);
		$this->_socket = NULL;
	}

// private members (not documented):

	function ExpectPDU($our_sequence_number)
	{
		do {
			$elength = fread($this->_socket, 4);
			extract(unpack("Nlength", $elength));
			$stream = fread($this->_socket, $length - 4);
			echo "Read PDU        : $length bytes.\n";
			echo "Stream len      : " . strlen($stream) . "\n";
			extract(unpack("Ncommand_id/Ncommand_status/Nsequence_number", $stream));
			$command_id &= 0x0fffffff;
			echo "Command id      : $command_id.\n";
			echo "Command status  : $command_status.\n";
			echo "sequence_number : $sequence_number.\n";
			switch ($command_id) {
			case CM_BIND_TRANSMITTER:
				echo "Got CM_BIND_TRANSMITTER_RESP.\n";
				break;
			case CM_UNBIND:
				echo "Got CM_UNBIND_RESP.\n";
				break;
			case CM_SUBMIT_SM:
				echo "Got CM_SUBMIT_SM_RESP.\n";
				break;
			default:
				echo "Got unknown SMPP pdu.\n";
				break;
			}
		} while ($sequence_number != $our_sequence_number);
		return $command_status;
	}
	
	function SendPDU($command_id, $pdu)
	{
		$length = strlen($pdu) + 16;
		$header = pack("NNNN", $length, $command_id, $this->_command_status, $this->_sequence_number);
		echo "Sending PDU, len == $length\n";
		echo "Sending PDU, header-len == " . strlen($header) .  "\n";
		echo "Sending PDU, command_id == " . $command_id  .  "\n";
		fwrite($this->_socket, $header . $pdu, $length);
		$status = $this->ExpectPDU($this->_sequence_number);
		$this->_sequence_number = $this->_sequence_number + 1;
		return $status;
	}

	function SendBindTransmitter($system_id, $smpppassword, $system_type)
	{
		$system_id_len = strlen($system_id) + 1;
		$smpppassword_len = strlen($smpppassword) + 1;
		$system_type_len = strlen($system_type) + 1;
		$pdu = pack("a{$system_id_len}a{$smpppassword_len}a{$system_type_len}CCCa1", $system_id, $smpppassword, $system_type, 3, 0, 0, "");
		$status = $this->SendPDU(CM_BIND_TRANSMITTER, $pdu);
		return $status;
	}

	function SendUnbind()
	{
		$pdu = "";
		$status = $this->SendPDU(CM_UNBIND, $pdu);
		return status;
	}

	function SendSubmitSM($service_type, $source_addr_ton, $source_addr_npi, $source_addr, $dest_addr_ton, $dest_addr_npi, $destination_addr, $esm_class, $protocol_id, $priority_flag, $schedule_delivery_time, $validity_period, $registered_delivery_flag, $replace_if_present_flag, $data_coding, $sm_default_msg_id, $sm_length, $short_message)
	{
		$service_type_len = strlen($service_type) + 1;
		$source_addr_len = strlen($source_addr) + 1;
		$destination_addr_len = strlen($destination_addr) + 1;
		$schedule_delivery_time_len = strlen($schedule_delivery_time) + 1;
		$validity_period_len = strlen($validity_period) + 1;
		$message_len = $sm_length + 1;
		echo "PDU spec: a{$service_type_len}cca{$source_addr_len}cca{$destination_addr_len}ccca{$schedule_delivery_time_len}a{$validity_period_len}ccccca{$message_len}\n";
		$pdu = pack("a{$service_type_len}cca{$source_addr_len}cca{$destination_addr_len}ccca{$schedule_delivery_time_len}a{$validity_period_len}ccccca{$message_len}", $service_type, $source_addr_ton, $source_addr_npi, $source_addr, $dest_addr_ton, $dest_addr_npi, $destination_addr, $esm_class, $protocol_id, $priority_flag, $schedule_delivery_time, $validity_period, $registered_delivery_flag, $replace_if_present_flag, $data_coding, $sm_default_msg_id, $sm_length, $short_message);
		$status = $this->SendPDU(CM_SUBMIT_SM, $pdu);
		return $status;
	}

};



@ini_set('magic_quotes_runtime', 0);
echo '<pre>';
$smpphost = "pmg-acg-sms01.ref1.lightsurf.net";
$smppport = 8008;
$systemid = "1432";
$password = "n3x41A99";
$system_type = "";
$from = "13369570515";


$smpphost = ' 41.223.58.132';
$smppPort = '31110';
$systemId = 'nex2v';
$password = 'nex2v';
$systemType = '';
$from = 'Airtel';



//$from = "31495595392";

$smpp = new SMPPClass();
$smpp->SetSender($from);
$smpp->Start($smpphost, $smppport, $systemid, $password, $system_type);
$smpp->Send("255784670217", "This is my PHP message");
$smpp->End();

echo '</pre>';


?>
