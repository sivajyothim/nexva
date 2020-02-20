<?php
/**
 * @package net
 * Class for receiving or sending sms through SMPP protocol.
 * @version 0.2
 * @author paladin
 * @since 04/25/2006
 * @see http://www.smpp.org/doc/public/index.html - SMPP 3.4 protocol specification
 */

echo '<pre>';
$tx=new SMPP('pmg-acg-sms01.ref1.lightsurf.net',8008);
$tx->debug=true;
$tx->system_type="";
$tx->addr_npi=1;
print "open status: ".$tx->state."\n";
$tx->bindTransmitter('1432','n3x41A99');
$tx->sms_source_addr_npi=1;
$tx->sms_source_addr_ton=1;
$tx->sms_dest_addr_ton=1;
$tx->sms_dest_addr_npi=1;
$tx->sendSMS("13369570515","13369570515","Hello world!");
$tx->close();
unset($tx);
echo '</pre>';


class SMPP{
	//SMPP bind parameters
	var $system_type="WWW";
	var $interface_version=0x34;
	var $addr_ton=0;
	var $addr_npi=0;
	var $address_range="";
	//ESME transmitter parameters
	var $sms_service_type="";
	var $sms_source_addr_ton=0;
	var $sms_source_addr_npi=0;
	var $sms_dest_addr_ton=0;
	var $sms_dest_addr_npi=0;
	var $sms_esm_class=0;
	var $sms_protocol_id=0;
	var $sms_priority_flag=0;
	var $sms_schedule_delivery_time="";
	var $sms_validity_period="";
	var $sms_registered_delivery_flag=0;
	var $sms_replace_if_present_flag=0;
	var $sms_data_coding=0;
	var $sms_sm_default_msg_id=0;

	/**
	 * Constructs the smpp class
	 * @param $host - SMSC host name or host IP
	 * @param $port - SMSC port
	 */
	function SMPP($host, $port=5016){
		//internal parameters
		$this->sequence_number=1;
		$this->debug=false;
		$this->pdu_queue=array();
		$this->host=$host;
		$this->port=$port;
		$this->state="closed";
		//open the socket
		$this->socket=fsockopen($this->host, $this->port, $errno, $errstr, 30);
		if($this->socket)$this->state="open";
	}
	/**
	 * Binds the receiver. One object can be bound only as receiver or only as trancmitter.
	 * @param $login - ESME system_id
	 * @param $port - ESME password
	 * @return true when bind was successful
	 */
	function bindReceiver($login, $pass){
		if($this->state!="open")return false;
		if($this->debug){
			echo "Binding receiver...\n\n";
		}
		$status=$this->_bind($login, $pass, 0x00000001);
		if($this->debug){
			echo "Binding status  : $status\n\n";
		}
		if($status===0)$this->state="bind_rx";
		return ($status===0);
	}
	/**
	 * Binds the transmitter. One object can be bound only as receiver or only as trancmitter.
	 * @param $login - ESME system_id
	 * @param $port - ESME password
	 * @return true when bind was successful
	 */
	function bindTransmitter($login, $pass){
		if($this->state!="open")return false;
		if($this->debug){
			echo "Binding transmitter...\n\n";
		}
		$status=$this->_bind($login, $pass, 0x00000002);
		if($this->debug){
			echo "Binding status  : $status\n\n";
		}
		if($status===0)$this->state="bind_tx";
		return ($status===0);
	}
	/**
	 * Closes the session on the SMSC server.
	 */
	function close(){
		if($this->state=="closed")return;
		if($this->debug){
			echo "Unbinding...\n\n";
		}
		$status=$this->sendCommand(0x00000006,"");
		if($this->debug){
			echo "Unbind status   : $status\n\n";
		}
		fclose($this->socket);
		$this->state="closed";
	}
	/**
	 * Read one SMS from SMSC. Can be executed only after bindReceiver() call. 
	 * This method bloks. Method returns on socket timeout or enquire_link signal from SMSC.
	 * @return sms associative array or false when reading failed or no more sms.
	 */
	function readSMS(){
		if($this->state!="bind_rx")return false;
		//stream_set_timeout($this->socket, 10);
		$command_id=0x00000005;
		//check the queue
		for($i=0;$i<count($this->pdu_queue);$i++){
			$pdu=$this->pdu_queue[$i];
			if($pdu['id']==$command_id){
				//remove responce
				array_splice($this->pdu_queue, $i, 1);
				return parseSMS($pdu);
			}
		}
		//read pdu
		do{
			if($this->debug){
				echo "read sms...\n\n";
			}
			$pdu=$this->readPDU();
			//check for enquire link command
			if($pdu['id']==0x00000015){
				$this->sendPDU(0x80000015, "", $pdu['sn']);
				return false;
			}
			array_push($this->pdu_queue, $pdu);
		}while($pdu && $pdu['id']!=$command_id);
		if($pdu){
			array_pop($this->pdu_queue);
			return $this->parseSMS($pdu);
		}
		return false;
	}
	/**
	 * Read one SMS from SMSC. Can be executed only after bindTransmitter() call.
	 * @return true on succesfull send, false if error encountered
	 */
	function sendSMS($from, $to, $message){
		if (strlen($from)>20 || strlen($to)>20 || strlen($message)>160)return false;
		if($this->state!="bind_tx")return false;
		$short_message = $text;
		//*
		$pdu = pack('a1cca'.(strlen($from)+1).'cca'.(strlen($to)+1).'ccca1a1ccccca'.(strlen($message)+1),
			$this->sms_service_type,
			$this->sms_source_addr_ton,
			$this->sms_source_addr_npi,
			$from,//source_addr
			$this->sms_dest_addr_ton,
			$this->sms_dest_addr_npi,
			$to,//destination_addr
			$this->sms_esm_class,
			$this->sms_protocol_id,
			$this->sms_priority_flag,
			$this->sms_schedule_delivery_time,
			$this->sms_validity_period,
			$this->sms_registered_delivery_flag,
			$this->sms_replace_if_present_flag,
			$this->sms_data_coding,
			$this->sms_sm_default_msg_id,
			strlen($message),//sm_length
			$message//short_message
		);
	//	$status=$this->sendCommand(0x00000004,$pdu);	
		$status=$this->sendCommand(0x00000021,$pdu);
		//*/
		return ($status===0);
	}

	////////////////private functions///////////////

	/**
	 * @private function
	 * Binds the socket and opens the session on SMSC
	 * @param $login - ESME system_id
	 * @param $port - ESME password
	 * @return bind status or false on error
	 */
	function _bind($login, $pass, $command_id){
		//make PDU
		$pdu = pack(
			'a'.(strlen($login)+1).
			'a'.(strlen($pass)+1).
			'a'.(strlen($this->system_type)+1).
			'CCCa'.(strlen($this->address_range)+1),
			$login, $pass, $this->system_type,
			$this->interface_version, $this->addr_ton,
			$this->addr_npi, $this->address_range);
		$status=$this->sendCommand($command_id,$pdu);
		return $status;
	}

	/**
	 * @private function
	 * Parse deliver PDU from SMSC.
	 * @param $pdu - deliver PDU from SMSC.
	 * @return parsed PDU as array.
	 */
	function parseSMS($pdu){
		//check command id
		if($pdu['id']!=0x00000005)return false;
		//unpack PDU
		$ar=unpack("C*",$pdu['body']);
		$sms=array('service_type'=>$this->getString($ar,6),
			'source_addr_ton'=>array_shift($ar),
			'source_addr_npi'=>array_shift($ar),
			'source_addr'=>$this->getString($ar,21),
			'dest_addr_ton'=>array_shift($ar),
			'dest_addr_npi'=>array_shift($ar),
			'destination_addr'=>$this->getString($ar,21),
			'esm_class'=>array_shift($ar),
			'protocol_id'=>array_shift($ar),
			'priority_flag'=>array_shift($ar),
			'schedule_delivery_time'=>array_shift($ar),
			'validity_period'=>array_shift($ar),
			'registered_delivery'=>array_shift($ar),
			'replace_if_present_flag'=>array_shift($ar),
			'data_coding'=>array_shift($ar),
			'sm_default_msg_id'=>array_shift($ar),
			'sm_length'=>array_shift($ar),
			'short_message'=>$this->getString($ar,255)
		);
		if($this->debug){
			echo "Delivered sms:\n";
			print_r($sms);
			echo "\n";
		}
		//send responce of recieving sms
		$this->sendPDU(0x80000005, "\0", $pdu['sn']);
		return $sms;
	}
	/**
	 * @private function
	 * Sends the PDU command to the SMSC and waits for responce.
	 * @param $command_id - command ID
	 * @param $pdu - PDU body
	 * @return PDU status or false on error
	 */
	function sendCommand($command_id, $pdu){
		if($this->state=="closed")return false;
		$this->sendPDU($command_id, $pdu, $this->sequence_number);
		$status=$this->readPDU_resp($this->sequence_number, $command_id);
		$this->sequence_number=$this->sequence_number+1;
		return $status;
	}
	/**
	 * @private function
	 * Prepares and sends PDU to SMSC.
	 * @param $command_id - command ID
	 * @param $pdu - PDU body
	 * @param $seq_number - PDU sequence number
	 */
	function sendPDU($command_id, $pdu, $seq_number){
		$length=strlen($pdu) + 16;
		$header=pack("NNNN", $length, $command_id, 0, $seq_number);
		if($this->debug){
			echo "Send PDU        : $length bytes\n";
			$this->printHex($header.$pdu);
			echo "command_id      : ".$command_id."\n";
			echo "sequence number : $seq_number\n\n";
		}
		fwrite($this->socket, $header.$pdu, $length);
	}
	/**
	 * @private function
	 * Waits for SMSC responce on specific PDU.
	 * @param $seq_number - PDU sequence number
	 * @param $command_id - PDU command ID
	 * @return PDU status or false on error
	 */
	function readPDU_resp($seq_number, $command_id){
		//create responce id
		$command_id=$command_id|0x80000000;
		//check queue
		for($i=0;$i<count($this->pdu_queue);$i++){
			$pdu=$this->pdu_queue[$i];
			if($pdu['sn']==$seq_number && $pdu['id']==$command_id){
				//remove responce
				array_splice($this->pdu_queue, $i, 1);
				return $pdu['status'];
			}
		}
		//read pdu
		do{
			$pdu=$this->readPDU();
			if($pdu)array_push($this->pdu_queue, $pdu);
		}while($pdu && ($pdu['sn']!=$seq_number || $pdu['id']!=$command_id));
		//remove responce from queue
		if($pdu){
			array_pop($this->pdu_queue);
			return $pdu['status'];
		}
		return false;
	}
	/**
	 * @private function
	 * Reads incoming PDU from SMSC.
	 * @return readed PDU or false on error.
	 */
	function readPDU(){
		//read PDU length
		$tmp=fread($this->socket, 4);
		if(!$tmp)return false;
		extract(unpack("Nlength", $tmp));
		//read PDU headers
		$tmp2=fread($this->socket, 12);
		if(!$tmp2)return false;
		extract(unpack("Ncommand_id/Ncommand_status/Nsequence_number", $tmp2));
		//read PDU body
		if($length-16>0){
			$body=fread($this->socket, $length-16);
			if(!$body)return false;
		}else{
			$body="";
		}
		if($this->debug){
			echo "Read PDU        : $length bytes\n";
			$this->printHex($tmp.$tmp2.$body);
			echo "body len        : " . strlen($body) . "\n";
			echo "Command id      : $command_id\n";
			echo "Command status  : $command_status\n";
			echo "sequence number : $sequence_number\n\n";
		}
		$pdu=array(
			'id'=>$command_id,
			'status'=>$command_status,
			'sn'=>$sequence_number,
			'body'=>$body);
		return $pdu;
	}
	/**
	 * @private function
	 * Reads C style zero padded string from the char array.
	 * @param $ar - input array
	 * @param $maxlen - maximum length to read.
	 * @return readed string.
	 */
	function getString(&$ar, $maxlen=255){
		$s="";
		$i=0;
		do{
			$c=array_shift($ar);
			if($c!=0)$s.=chr($c);
			$i++;
		}while($i<$maxlen && $c!=0);
		return $s;
	}
	/**
	 * @private function
	 * Prints the binary string as hex bytes.
	 * @param $maxlen - maximum length to read.
	 */
	function printHex($pdu){
		$ar=unpack("C*",$pdu);
		foreach($ar as $v){
			$s=dechex($v);
			if(strlen($s)<2)$s="0$s";
			print "$s ";
		}
		print "\n";
	}
}
?>