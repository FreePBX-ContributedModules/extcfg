<?php
//Copyright (C) 2006 Niklas Larsson
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.

// returns a associative arrays with keys 'destination' and 'description'
function extcfg_destinations() {
	return null;
}

function extcfg_get_config($engine) {

}

define("CW", "CW");
define("CFBS", "CFB");
define("CFNA", "CFU");
define("CFIM", "CF");
define("YAC", "YAC");
define("DND", "DND");

require("astman.inc");

function extcfg_init(){
  global $server, $amp_conf;

/*  mysql_connect('localhost', 'asteriskuser', 'amp109') or die ("Could not connect to MySQL");
  mysql_select_db('asterisk') or die ("Could not select asterisk database" . mysql_error());  
  
  $sql = "select * from servers;";
  $result = mysql_query($sql) or die ("Server Query failed" . mysql_error());
  
  $server_nr = mysql_num_rows($result);
  $i=0;
  while ($row = mysql_fetch_array($result)) {
    $server[$i]["db_host"] = $row["db_host"];
    $server[$i]["db_user"] = $row["db_user"];
    $server[$i]["db_passwd"] = $row["db_pass"];
    $server[$i]["db_db"] = $row["db_db"];
    $server[$i]["astman_host"] = $row["astman_host"];
    $server[$i]["astman_user"] = $row["astman_user"];
    $server[$i]["astman_passwd"] = $row["astman_pass"];
    $server[$i]["name"] = $row["host_name"];
    $server[$i]["astman"] = new AstMan;
    $i++;
  }

  */
  
  $server[] = array(
   "db_host" => $amp_conf['AMPDBHOST'],
   "db_user" => $amp_conf['AMPDBUSER'],
   "db_passwd" => $amp_conf['AMPDBPASS'], 
   "db_db" => $amp_conf['AMPDBNAME'],
   "astman_host" => 'localhost',
   "astman_user" => $amp_conf['AMPMGRUSER'], 
   "astman_passwd" => $amp_conf['AMPMGRPASS'], 
   "name" => "localhost",
   "astman" => new AstMan
  );
  /*
  $server[] = array(
   "db_host" => "192.168.102.38",
   "db_user" => "root",
   "db_passwd" => "xxxxxx", 
   "db_db" => "asterisk",
   "astman_host" => "192.168.102.38",
   "astman_user" => "root", 
   "astman_passwd" => "xxxxxxx", 
   "name" => "Alfa",
   "astman" => new AstMan
  );
  */
  
  $server_nr = count($server);
  
  for($i = 0; $i < $server_nr; $i++){
    $server[$i]['astman']->Login($server[$i]['astman_host'],$server[$i]['astman_user'],$server[$i]['astman_passwd']);
  }
}

function extcfg_exit(){
  global $server;
  foreach($server as $serv){
    $serv['astman']->Logout();
  }
}

function extcfg_show_list($status = false)
{
  global $server;
  $exts = array();
	$i = 1;
	
  foreach($server as $key => $serv){
    $exts = array_merge($exts, extcfg_get_extensions_amp($serv['db_host'], $serv['db_user'], $serv['db_passwd'], $serv['db_db'], $key));
		$status_arr = get_ext_status($serv['db_host'], $serv['db_user'], $serv['db_passwd'], $serv['db_db'], $key, $serv['astman']);

    $dnd[$key] = $serv['astman']->GetFamilyDB(DND);
    $cw[$key] = $serv['astman']->GetFamilyDB(CW);
    $cfim[$key] = $serv['astman']->GetFamilyDB(CFIM);
    $cfbs[$key] = $serv['astman']->GetFamilyDB(CFBS);
    $cfna[$key] = $serv['astman']->GetFamilyDB(CFNA);
  }
  sort($exts);
  
  echo "<table border='0' cellspacing='0' cellpadding='3' style=''><tr><th></th><th>Extension</th><th>DND</th><th>Call<br>Waiting</th><th>Call Forward<br>All</th><th>Call Forward<br>Busy</th><th>Call Forward<br>No Answer</th><th>IP</th><th>port</th><th>Status</th><th>Device</th><th>Tech</th></tr>";

  foreach($exts as $ext){
  	$status_bg = $status_arr[$ext[0]]['ok'] ? '#88ff88' : '#ff8888';
	$dnd_f = empty($dnd[$ext[1]][$ext[0]]) ? '' : '<img src="/admin/images/accept.png" border="0">';
	$cw_f = '';
	if(!empty($cw[$ext[1]][$ext[0]])){
		if($cw[$ext[1]][$ext[0]] == 'ENABLED'){
			$cw_f = '<img src="/admin/images/accept.png" border="0">';
		}
	}
	$cfim_f = empty($cfim[$ext[1]][$ext[0]]) ? '' : $cfim[$ext[1]][$ext[0]];
	$cfbs_f = empty($cfbs[$ext[1]][$ext[0]]) ? '' : $cfbs[$ext[1]][$ext[0]];
	$cfna_f = empty($cfna[$ext[1]][$ext[0]]) ? '' : $cfna[$ext[1]][$ext[0]];
	$ip = empty($status_arr[$ext[0]]['ip']) ? '' : $status_arr[$ext[0]]['ip'];
	$port = empty($status_arr[$ext[0]]['port']) ? '' : $status_arr[$ext[0]]['port'];
	$status = empty($status_arr[$ext[0]]['status']) ? '' : $status_arr[$ext[0]]['status'];
	$device = empty($status_arr[$ext[0]]['device']) ? '' : $status_arr[$ext[0]]['device'];
	$type = empty($status_arr[$ext[0]]['type']) ? '' : $status_arr[$ext[0]]['type'];
    echo "<tr bgcolor='" . varBg($i++) . "'>
			<td><a href='/admin/config.php?type=setup&display=extensions&extdisplay={$ext[0]}'><img src='/admin/images/telephone_edit.png' border=0 title='Edit extension'></a></td>
			<td><a href='" . $_SERVER['PHP_SELF'] . "?display=extcfg&type=tool&action=phone&phone={$ext[0]}&srv={$ext[1]}'>{$ext[0]} - {$ext[2]}</a></td>
			<td style='text-align: center;'>$dnd_f&nbsp;</td>
			<td style='text-align: center;'>$cw_f&nbsp;</td>
			<td>$cfim_f&nbsp;</td>
			<td>$cfbs_f&nbsp;</td>
			<td>$cfna_f&nbsp;</td>
			<td>$ip</td>
			<td>$port</td>
			<td style='background-color: $status_bg;'>$status</td>
			<td>$device</td>
			<td>$type</td>
		</tr>";
  }
  
  echo "</table>";
}

function extcfg_show_phone($ext, $srv)
{
  global $server;
  $serv = $server[$srv];
?>
<form name="phone" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <input name="ext" type="hidden" value="<?php echo $ext; ?>">
  <input name="srv" type="hidden" value="<?php echo $srv; ?>">
  <input type="hidden" name="display" value="extcfg">
  <input type="hidden" name="type" value="tool">
  <input type="hidden" name="action" value="phone_save">
  <table width="80%"  border="0">
    <tr>
      <td width="20%" scope="col">Extension</td>
      <td width="63%" scope="col"><b><?php echo $ext; ?></b></td>

    </tr>
<!--    <tr>
    
      <td><input type="text" name="YAC" value="<?php echo $serv['astman']->GetDB(YAC, $ext);?>"></td>
      <td>IP nummer / DNS-namn till datorn <a href="http://www.dynx.net/ASTERISK/misc-progs/YAC/yac-0.16-win32.zip">Programmet finns h&auml;r</a></td>
    </tr> -->
    <tr>
      
      <td><input name="DND" type="checkbox" value="DND" <?php if ($serv['astman']->GetDB(DND, $ext) == "YES") echo "checked";?>></td>
      <td>Do Not Disturb</td>
    </tr>
    <tr>
    
      <td><input type="checkbox" name="CW" value="CW" <?php if ($serv['astman']->GetDB(CW, $ext) == "ENABLED") echo "checked";?>></td>
      <td>Call Waiting</td>
    </tr>
    <tr>
      
      <td><input type="text" name="CFIM" value="<?php echo $serv['astman']->GetDB(CFIM, $ext);?>"></td>
      <td>Call Forward All</td>
    </tr>
    <tr>
    
      <td><input type="text" name="CFBS" value="<?php echo $serv['astman']->GetDB(CFBS, $ext);?>"></td>
      <td>Call Forward Busy</td>
    </tr>
    <tr>
    
      <td><input type="text" name="CFNA" value="<?php echo $serv['astman']->GetDB(CFNA, $ext);?>"></td>
      <td>Call Forward No Answer</td>
    </tr>
    <tr>
   
      <td colspan="2"><input type="submit" name="Submit" value="Submit">
      <input type="reset" name="Reset" value="Reset"></td>
    </tr>
  </table>
</form>  
<?php
}

function extcfg_save_phone($ext, $srv)
{
  global $server;
  $serv = $server[$srv];

  if (isset($_REQUEST["DND"]))
    $serv['astman']->PutDB(DND, $ext, "YES");
  else
    $serv['astman']->DelDB(DND, $ext);    
  if (isset($_REQUEST["CW"]))
    $serv['astman']->PutDB(CW, $ext, "ENABLED");
  else
    $serv['astman']->DelDB(CW, $ext);    
  if (!empty($_REQUEST["CFBS"]))
    $serv['astman']->PutDB(CFBS, $ext, $_REQUEST["CFBS"]);
  else
    $serv['astman']->DelDB(CFBS, $ext);    
  if (!empty($_REQUEST["CFIM"]))
    $serv['astman']->PutDB(CFIM, $ext, $_REQUEST["CFIM"]);
  else
    $serv['astman']->DelDB(CFIM, $ext);    
  if (!empty($_REQUEST["CFNA"]))
    $serv['astman']->PutDB(CFNA, $ext, $_REQUEST["CFNA"]);
  else
    $serv['astman']->DelDB(CFNA, $ext);    
}

function extcfg_get_extensions_amp($server, $user, $passwd, $db, $astman_nr)
{
  $exts = array();

  $sql = "SELECT id, description FROM devices d ORDER BY CAST(id AS UNSIGNED);";
  
  mysql_connect($server, $user, $passwd) or die ("Could not connect to MySQL");
  mysql_select_db($db) or die ("Could not select $db database");  
  
  $result = mysql_query($sql) or die ("Query failed");
  while ($kolumn = mysql_fetch_array($result)) {
    $exts[] = array($kolumn["id"], $astman_nr, $kolumn['description']);
  }
 	
  return ($exts);	
}

function get_ext_status($server, $user, $passwd, $db, $astman_nr, $astman){
	$arr = array();	
	$sccp_sep_arr = array();
	
	// SIP	
	$sip_res = $astman->Query2("Action: sippeers\r\n\r\n", 'Event: PeerlistComplete');

	$sip_peers = explode("\r\n\r\n", $sip_res);
	
	foreach ($sip_peers as $sip_peer) {
		if (strpos($sip_peer, 'ObjectName')){
			$extension = $astman->get_my_stuff($sip_peer, 'ObjectName: ', "\r\n");
			
			$arr[$extension] = array(
				'ip' => $astman->get_my_stuff($sip_peer, 'IPaddress: ', "\r\n"), 
				'port' => $astman->get_my_stuff($sip_peer, 'IPport: ', "\r\n"),
				'status' => $astman->get_my_stuff($sip_peer, 'Status: ', "\r\n"),
				'type' => 'SIP',
				'device' => '',
				'ok' => false
			);		
			if ($arr[$extension]['ip'] != '-none-' && $arr[$extension]['status'] != 'UNREACHABLE')
				$arr[$extension]['ok'] = true;
		}
	}
	
	// IAX
	
	$iax_res = $astman->Query2("Action: IAXpeerlist\r\n\r\n", 'PeerlistComplete');

	$iax_peers = explode("\r\n\r\n", $iax_res);
		
	foreach ($iax_peers as $iax_peer) {
		if (strpos($iax_peer, 'ObjectName')){
			$extension = $astman->get_my_stuff($iax_peer, 'ObjectName: ', "\r\n");
			
			$arr[$extension] = array(
				'ip' => $astman->get_my_stuff($iax_peer, 'IPaddress: ', "\r\n"), 
				'port' => $astman->get_my_stuff($iax_peer, 'Port: ', "\r\n"),
				'status' => $astman->get_my_stuff($iax_peer, 'Status: '),
				'type' => 'IAX2',
				'device' => '',
				'ok' => false
			);		
			if ($arr[$extension]['ip'] != '-none-' && $arr[$extension]['status'] != 'UNREACHABLE')
				$arr[$extension]['ok'] = true;				
		}
	}

	// SCCP
	
	$sccp_devices_res = $astman->Query("Action: Command\r\nCommand: sccp show devices\r\n\r\n");
	$sccp_lines_res = $astman->Query("Action: Command\r\nCommand: sccp show lines\r\n\r\n");

	$sccp_lines = get_astman_lines($sccp_lines_res);
	
	unset($sccp_lines[0]);
	unset($sccp_lines[1]);
	unset($sccp_lines[2]);
	unset($sccp_lines[3]);
	foreach ($sccp_lines as $sccp_line){
		$extension = trim(substr($sccp_line, 0, 16));
		$sep = trim(substr($sccp_line, 16, 16));
		if ($extension){
			$sccp_sep_arr[$sep] = $extension;
			$arr[$extension] = array('ip' => '', 'port' => '', 'status' => '', 'type' => 'SCCP', 'device' => $sep, 'ok' => false);
		}
	}
	
	$sccp_devices = get_astman_lines($sccp_devices_res);
	unset($sccp_devices[0]);
	unset($sccp_devices[1]);
	unset($sccp_devices[2]);
	unset($sccp_devices[3]);

	foreach($sccp_devices as $sccp_device){
		$status = trim(substr($sccp_device, -10));
		$sep = trim(substr($sccp_device, -27, 16));
		$ip = trim(substr($sccp_device, -43, 15));
		$extension = empty($sccp_sep_arr[$sep]) ? null : $sccp_sep_arr[$sep];
		$ok = $sep == '--' ? false : true;
		if ($extension){
			$arr[$extension] = array('ip' => $ip, 'port' => '', 'status' => $status, 'type' => 'SCCP', 'device' => $sep, 'ok' => $ok);
		}
	}
	
	// MGCP
	
	$mgcp_endpoints_res = $astman->Query("Action: Command\r\nCommand: mgcp show endpoints\r\n\r\n");

	if (strpos($mgcp_endpoints_res, 'Gateway')){
	
	$mgcp_endpoints = get_astman_lines($mgcp_endpoints_res);
	unset($mgcp_endpoints[0]);

	foreach($mgcp_endpoints as $mgcp_endpoint){
		$extension = '';
		$items = explode(' ', trim($mgcp_endpoint));
		if ($items[0] == 'Gateway'){ // First line
			$ip = $items[3];
			$device = str_replace("'", '', $items[1]);
		}else{
			$line = str_replace("'", '', $items[1]);
			$status = $items[5];
			
			$sql = "SELECT d.`id` 
							FROM devices d 
							WHERE d.`dial` = 'MGCP/$line'";
							
			if ($query = mysql_query($sql)){
				if ($row = mysql_fetch_assoc($query)){
					$extension = $row['id'];
				}
			}
		
			if ($extension){
				$ok = $ip == '0.0.0.0' ? false : true;
					 
				$arr[$extension] = array('ip' => $ip, 'port' => '', 'status' => $status, 'type' => 'MGCP', 'device' => $device, 'ok' => $ok);
			}
		}
	}
	}
	
	return $arr;	
}

function get_astman_lines($wrets){
	$value_start = strpos($wrets, "Response: Follows\r\n") + 19;
	$value_stop = strpos($wrets, "--END COMMAND--\r\n", $value_start);
	if ($value_start > 18){
		$wrets = substr($wrets, $value_start, $value_stop - $value_start);
	}
	$lines = explode("\n", $wrets);
	
	return $lines;
}

function varBg($i = 0){
	if($i%2 == 0)
		return '#ffffff';
	else
		return '#eeeeee';
}
?>
