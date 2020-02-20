<?php
 /**
 * Nimf's smpp client
 *
 * This file contains class of Nimf's smpp client.
 *
 * Requires:
 * nimf_logger     0.0.2+
 * nimf_sockclient 0.0.2+
 *
 * @package NIMF
 * @subpackage smppclient
 * @author Nimf <nimfin@gmail.com>
 * @version 0.0.3
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

class NIMF_smppclient extends NIMF_sockclient {
  /**
   * Array of received PDUs
   * @access public
   * @var array
   */
  var $pdu_inc = array();
  /**
   * Buffer for incoming data
   * @access private
   * @var string
   */
  var $pdu_ib  = null;
 
  /**
   * Waits for particular PDU (with known command id and sequence no) for some period of time ($to).
   * All unmatched PDUs go to $this->pdu_inc array
   * @access public
   * @param int $cmd Command id
   * @param int $sqn Sequence no
   * @param int $to Timeout (miliseconds)
   * @return mixed PDU on success, FALSE on error
   */
  function pdu_wait_for($cmd,$sqn,$to=60000) {
    list($usec, $sec) = explode(' ', microtime());
    $now = $start = $sec + $usec;
    while($now-$start < ($to/1000)) {
      if (false !== $pdu = $this->pdu_read()) {
        $hdr = unpack('Nlen/Ncmd/Nstat/Nsqn',substr($pdu,0,16));
        if ( ($hdr['cmd'] == $cmd) && ($hdr['sqn'] == $sqn)) return $pdu;
          else $this->pdu_inc []= $pdu;
      }
      list($usec, $sec) = explode(' ', microtime());
      $now = $sec + $usec;
    }
    l('PDU we were waiting for didn\'t arrive.');
    return false;
  }
 
  /**
   * Tries to read PDU completly in $timeout miliseconds
   * @access private
   * @param int $timeout Timeout (miliseconds)
   * @return boolean TRUE on success, FALSE on error
   */
  function pdu_read($timeout=5000){
    while ($this->select($timeout)) {
      if (false === $buf = $this->read(1024000)) {l('Could not read from socket. Disconnected?',L_WARN); return false;}
      $this->pdu_ib .= $buf;
      if (strlen($this->pdu_ib)>=16) {
        $hdr = unpack('Nlen',substr($this->pdu_ib,0,4));
        if (strlen($this->pdu_ib) >= $hdr['len']) {
          if ($this->v) l('Read PDU:'."\n".$this->cute_pdu(substr($this->pdu_ib,0,$hdr['len'])));
          $read = substr($this->pdu_ib,0,$hdr['len']);
          $this->pdu_ib = substr($this->pdu_ib,$hdr['len']);
          return $read;
        }
      }
    }
    l('Could not get complete PDU in '.($timeout/1000).' second(s).',L_WARN);
    return false;
  }
 
  /**
   * For cute logging. Formats PDU gracefully.
   * @access private
   * @param string $pdu PDU
   * @return string PDU formatted for print out
   */
  function cute_pdu($pdu) {
    $hdr = substr($pdu,0,16);
    $res = unpack('Nlen/Ncmd/Nstat/Nsqn',$hdr);
    $out = 'Header: '.$this->format_hex($hdr,4,16)."\n".'Length: '.$res['len'].
' Command: '.$this->translate_cmd($res['cmd']).' Status: '.$res['stat'].' Sequence:'.$res['sqn'];
    if ($res['len']>16) $out .= ' Body:'."\n".$this->format_hex(substr($pdu,16),1,16,' ')."\n".substr($pdu,16);
    return $out;
  }
 
  /**
   * For cute logging. Formats sequence of bytes.
   * @access private
   * @param string $num Bytes sequence
   * @param int $grp Group by (count)
   * @param int $lb Bytes per line
   * @param string $sep Group separator
   * @return string Formatted string
   */
  function format_hex($num,$grp=1,$lb=16,$sep=':') {
    $ar = unpack("C*",$num);
    $out = '';
    foreach($ar as $k=>$v){
      $out .= sprintf('%02X',$v);
      if (($k)%$lb == 0) {$out .= "\n"; continue;}
      if (($k)%$grp == 0) $out .= $sep;
    }
    if (substr($out,-1) == $sep) return substr($out,0,-1);
      else if (substr($out,-1) == "\n") return substr($out,0,-1);
        else return $out;
  }
 
  /**
   * For cute logging. Gets text representation of a command id.
   * @access private
   * @param int $cmd Command id
   * @return string Text representation
   */
  function translate_cmd($cmd) {
    $out = '';
    if (hexdec($cmd) >= 0x80000000) {
      if ($cmd == 0x80000000) return 'GENERIC_NACK';
        else $out = '_ACK';
      $cmd ^= 0x80000000;
    }
    switch($cmd) {
      case 0x00000001: return 'BIND_RX'.$out;
      case 0x00000002: return 'BIND_TX'.$out;
      case 0x00000004: return 'SUBMIT_SM'.$out;
      case 0x00000005: return 'DELIVER_SM'.$out;
      case 0x00000006: return 'UNBIND'.$out;
      case 0x00000015: return 'ENQUIRELINK'.$out;
    }
  }
}

