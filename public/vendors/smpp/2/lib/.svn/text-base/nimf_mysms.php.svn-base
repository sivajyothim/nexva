<?php
/*
 *
 * Requires:
 * nimf_logger     0.0.2+
 * nimf_sockclient 0.0.2+
 * nimf_smppclient 0.0.3+
 *
 */

/**
 * ESME state: disconnected
 */
define('ESS_DISCONNECTED', 0);
/**
 * ESME state: connected
 */
define('ESS_CONNECTED', 1);
/**
 * ESME state: binded TX
 */
define('ESS_BIND_TX', 2);
/**
 * ESME state: binded TX
 */
define('ESS_BIND_RX', 3);

/**
 * SMPP command: BIND RX
 */
define('BIND_RX'   ,0x00000001);
/**
 * SMPP command: BIND TX
 */
define('BIND_TX'   ,0x00000002);
/**
 * SMPP command: UNBIND
 */
define('UNBIND'    ,0x00000006);
/**
 * SMPP acknowledgement bits. (and GENERIC_NACK also)
 */
define('ACK'       ,0x80000000);
/**
 * SMPP command: SUBMIT SM
 */
define('SUBMIT_SM' ,0x00000004);
/**
 * SMPP command: DELIVER SM
 */
define('DELIVER_SM',0x00000005);
/**
 * SMPP command: ENQUIRELINK
 */
define('ENQUIRELINK',0x00000015);


/**
 * Nimf's esme class
 *
 * This file contains class of Nimf's esme.
 *
 * Requires:
 * nimf_logger     0.0.2+
 * nimf_sockclient 0.0.2+
 * nimf_smppclient 0.0.3+
 *
 * @package NIMF
 * @subpackage mysms-lib
 * @author Nimf <nimfin@gmail.com>
 * @version 0.0.1
 * @copyright Copyright (c) 2008, Nimf
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
class NIMF_mysms {
  /**
   * SMSC/SMS gateway host
   * @access private
   * @var string
   */
  var $host  = 'localhost';
  /**
   * SMSC/SMS gateway port
   * @access private
   * @var int
   */
  var $port  = 2775;
  /**
   * Connection socket
   * @access private
   * @var resource
   */
  var $sock  = null;
  /**
   * ESME login
   * @access private
   * @var string
   */
  var $login = 'smppclient';
  /**
   * ESME password
   * @access private
   * @var string
   */
  var $pass  = 'password';
  /**
   * Sequence number
   * @access private
   * @var int
   */
  var $sqn   = 0;
  /**
   * Connection type
   * @access private
   * @var string
   */
  var $dir   = 'TX';
  /**
   * ESME state
   * @access private
   * @var string
   */
  var $state = ESS_DISCONNECTED;
  /**
   * ESME System type
   * @access public
   * @var string
   */
  var $system_type = '';
  /**
   * ESME Address range
   * @access public
   * @var string
   */
  var $address_range = '3333';
  /**
   * ESME Address TON
   * @access public
   * @var string
   */
  var $addr_ton = 0;
  /**
   * ESME Address NPI
   * @access public
   * @var string
   */
  var $addr_npi = 1;
  /**
   * Default parameters for SUBMIT_SM. U can redefine
   * stype - service type
   * st    - source number TON
   * sn    - source number NPI
   * src   - source number
   * dt    - destination number TON
   * dn    - destination number NPI
   * proto - protocol id
   * prior - delivery priority
   * sdt   - scheduled delivery time
   * valid - validity period
   * deliv - request delivery report
   * repl  - replace if exists (in store&forward mode)
   * msgid - default message id
   * @access public
   * @var array
   */
  var $defsend = array(
    'stype' => '',
    'st'    => 0,
    'sn'    => 1,
    'src'   => '3333',
    'dt'    => 1,
    'dn'    => 1,
    'proto' => 0,
    'prior' => 0,
    'sdt'   => '',
    'valid' => '',
    'deliv' => 0,
    'repl'  => 0,
    'msgid' => 0
    
  );
  
  
 
  /**
   * Construction function. Assigns major settings.
   * @access public
   * @param string $host SMSC/SMS gateway host
   * @param int $port SMSC/SMS gateway port
   * @param string $login ESME login
   * @param string $pass ESME password
   * @return boolean Always TRUE
   */
  function NIMF_mysms($host='localhost',$port=2775,$login='smppclient',$pass='password') {
    $this->host = $host;
    $this->port = $port;
    $this->login = $login;
    $this->pass = $pass;
    return true;
  }
 
  /**
   * Connects to SMSC/SMS gateway
   * @access public
   * @return boolean TRUE on success, FALSE on error
   */
  function connect() {
    if ($this->sock == null) $this->sock = new NIMF_smppclient($this->host,$this->port);
    if (!$this->sock->connect()) return false;
    $this->state = ESS_CONNECTED;
    l('ESME state: ESS_CONNECTED');
    return true;
  }
 
  /**
   * Binds to SMSC/SMS gateway
   * @access public
   * @return boolean TRUE on success, FALSE on error
   */
  function bind() {
    if ($this->state != ESS_CONNECTED) if (!$this->connect()) return false;
    if ($this->dir == 'TX') $pdu = $this->form_pdu(BIND_TX);
    if ($this->dir == 'RX') $pdu = $this->form_pdu(BIND_RX);
    l('ESME sending BIND command...');
    if (!$this->sock->send($pdu)) return false;
    l('ESME reading ACK...');
    if (false === $pdu = $this->sock->pdu_wait_for(BIND_TX|ACK,$this->sqn)) {l('ESME pdu wait failed.',L_WARN); return false;}
    $res = $this->parse_pdu_header(substr($pdu,0,16));
    if ($res['stat'] === 0) {
      $this->state = ESS_BIND_TX;
      l('ESME state: ESS_BIND_TX');
      return true;
    } else {l('ESME bind failed ('.$res['stat'].')',L_CRIT); return false;}
  }
 
  /**
   * Checks if connection is valid
   * @access public
   * @return boolean TRUE on success, FALSE on error
   */
  function enquirelink() {
    $pdu = $this->form_pdu(ENQUIRELINK);
    l('ESME sending ENQUIRELINK command...');
    if (!$this->sock->send($pdu)) return false;
    if (false === $pdu = $this->sock->pdu_wait_for(ENQUIRELINK|ACK,$this->sqn)) {l('ESME pdu wait failed.',L_WARN); return false;}
    $res = $this->parse_pdu_header(substr($pdu,0,16));
    if ($res['stat'] !== 0) return false;
    return true;
  }
 
  /**
   * Sends an SMS. Converts message to unicode if needed. Separates into several parts if needed.
   * Array $msg should contain:
   * dst - destination number
   * text - message text
   * u can also add anything from default parameters for SUBMIT_SM
   * @access public
   * @param array $msg Message with properties
   * @return boolean TRUE on success, FALSE on error
   */
  function send_sms($msg) {
    if (preg_match('/[^a-z0-9@\$\^\{\}\\\~\[\] \!\"\#\%\&\`\(\)\*\+\,\-\.\/\:\;\<\=\>\?\_\r\n]/i',$msg['text'])) {
      $uni = true;
      $msg['text'] = mb_convert_encoding($msg['text'], 'UCS-2BE', 'UTF-8');
    } else $uni = false;
    $prts = $this->split_text($msg['text'],$uni);
    $pc = count($prts);
    $tmp = array();
    foreach($this->defsend as $k=>$v) if (isset($msg[$k])) $tmp[$k] = $msg[$k]; else $tmp[$k] = $v;
    $tmp['dst'] = $msg['dst'];
    if ($pc>1) $tmp['esm'] = 3+0x00000040; else $tmp['esm'] = 3;
    if ($uni) $tmp['dc'] = 8; else $tmp['dc'] = 241;
    
    $tmp['CARRIER_ID'] = 404 ;
    
    
    foreach($prts as $v) {
      $tmp['text'] = $v;
      $pdu = $this->form_pdu(SUBMIT_SM,$tmp);
      if (!$this->sock->send($pdu)) {l('Couldn\'t send pdu',L_WARN);return false;}
      if (false === $pdu = $this->sock->pdu_wait_for(SUBMIT_SM|ACK,$this->sqn)) {l('ESME pdu wait failed.',L_WARN); return false;}
      $res = $this->parse_pdu_header(substr($pdu,0,16));
      if ($res['stat'] !== 0) {l('ESME ack failed ('.$res['stat'].')',L_WARN); return false;}
    }
    l('All sent ok.');
    return true;
  }
 
  /**
   * Makes multipart message contents.
   * @access private
   * @param string $text Message text
   * @param boolean $uni Is message encoded in UCS-2BE?
   * @return array Array of message parts
   */
  function split_text($text,$uni=false) {
    $out = array();
    if ( (!$uni && strlen($text) <= 160) || ($uni && mb_strlen($text) <= 140) ) {
      $out []= $text;
      $this->sqn++;
      return $out;
    }
    if ($uni) {
      $parlen = 134;
      $txtlen = strlen($text);
    } else {
      $parlen = 153;
      $txtlen = mb_strlen($text);
    }
   
    $sqn = ++$this->sqn;
    $prts = ceil($txtlen/$parlen);
    for($i=0;$i<$prts;$i++) {
      $udh = pack("cccccc", 5, 0, 3, $sqn, $prts, ($i+1));
      if ($uni) $out []= $udh.mb_substr($text,$i*$parlen,$parlen);
        else $out []= $udh.substr($text,$i*$parlen,$parlen);
    }
    return $out;
  }
 
  /**
   * Parses PDU header.
   * @access private
   * @param string $header PDU header
   * @return array Array of length, command id, status and sequence no
   */
  function parse_pdu_header($header) {
    if (strlen($header) != 16) return false;
    return unpack('Nlen/Ncmd/Nstat/Nsqn',$header);
  }
 
  /**
   * Forms PDU.
   * @access private
   * @param int $cmd Command id
   * @param array $pars Parameters
   * @return string PDU
   */
  function form_pdu($cmd,$pars=array()) {
    $pdu = null;
    switch($cmd) {
      case BIND_TX:
      case BIND_RX:
        $pdu = pack(
          'a'.(strlen($this->login)+1).
          'a'.(strlen($this->pass)+1).
          'a'.(strlen($this->system_type)+1).
          'CCCa'.(strlen($this->address_range)+1),
          $this->login, $this->pass, $this->system_type,
          0x34, $this->addr_ton,
          $this->addr_npi, $this->address_range
        );
        $this->sqn++;
      break;
      case SUBMIT_SM:
        $pdu = pack(
          'a'.(strlen($pars['stype'])+1).
          'CCa'.(strlen($pars['src'])+1).
          'CCa'.(strlen($pars['dst'])+1).
          'CCCa'.(strlen($pars['sdt'])+1).
          'a'.(strlen($pars['valid'])+1).
          'CCCCC',
          $pars['stype'],
          $pars['st'],$pars['sn'],$pars['src'],
          $pars['dt'],$pars['dn'],$pars['dst'],
          $pars['esm'],$pars['proto'],$pars['prior'],$pars['sdt'],
          $pars['valid'],
          $pars['deliv'],$pars['repl'],$pars['dc'],$pars['msgid'],strlen($pars['text'])).$pars['text'];
      break;
      case ENQUIRELINK:
      case UNBIND:
        $this->sqn++;
      break;
    }
    return $this->form_pdu_header($cmd,strlen($pdu)).$pdu;
  }
 
  /**
   * Forms pdu header.
   * @access private
   * @param int $cmd Command id
   * @param int $pdulen Length of PDU body
   * @return string PDU header
   */
  function form_pdu_header($cmd,$pdulen) {
    $stat = 0;
    return pack('NNNN',$pdulen+16,$cmd,$stat,$this->sqn);
  }
 
  /**
   * Desconnects from SMSC/SMS gateway gracefully
   * @access public
   * @return boolean Always TRUE
   */
  function disconnect() {
    if ($this->state == ESS_BIND_TX || $this->state == ESS_BIND_RX) {
      l('Sending unbind command...');
      $this->sock->send($this->form_pdu(UNBIND));
      $pdu = $this->sock->pdu_wait_for(UNBIND|ACK,$this->sqn);
      $res = $this->parse_pdu_header(substr($pdu,0,16));
      if ($res['stat'] !== 0) l('UNBIND failed: '.$res['stat'].'.',L_WARN);
        else l('UNBIND done.');
    }
    $this->sock->disconnect();
    return true;
  }
 
}

