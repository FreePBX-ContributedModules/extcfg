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

/* 	Generates dialplan for conferences
	We call this with retrieve_conf
*/
function extcfg_get_config($engine) {

}

define("CW", "CW");
define("CFBS", "CFB");
define("CFNA", "CFU");
define("CFIM", "CF");
define("YAC", "YAC");

require("astman.inc");

function extcfg_init(){
  global $server;
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
   "db_host" => "localhost",
   "db_user" => "asteriskuser",
   "db_passwd" => "amp109", 
   "db_db" => "asterisk",
   "astman_host" => "localhost",
   "astman_user" => "admin", 
   "astman_passwd" => "amp111", 
   "name" => "mlin1",
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

function extcfg_show_list()
{
  global $server;
  $exts = array();
  foreach($server as $key => $serv){
    $exts = array_merge($exts, extcfg_get_extensions_amp($serv['db_host'], $serv['db_user'], $serv['db_passwd'], $serv['db_db'], $key));
//    $yac[$key] = $serv['astman']->GetFamilyDB(YAC);
    $dnd[$key] = $serv['astman']->GetFamilyDB(DND);
    $cw[$key] = $serv['astman']->GetFamilyDB(CW);
    $cfim[$key] = $serv['astman']->GetFamilyDB(CFIM);
    $cfbs[$key] = $serv['astman']->GetFamilyDB(CFBS);
    $cfna[$key] = $serv['astman']->GetFamilyDB(CFNA);
//    $cfnas[$key] = $serv['astman']->GetFamilyDB(CFNAS);
  }
  sort($exts);
  
  echo "<table border='1'><tr><th>Ext</th><th>DND</th><th>Call<br>Waiting</th><th>Call Forward<br>All</th><th>Call Forward<br>Busy</th><th>Call Forward<br>No Answer</th></tr>";

  foreach($exts as $ext){
    echo "<tr><td>";
    echo "<a href='" . $PHP_SELF . "?display=extcfg&type=tool&action=phone&phone={$ext[0]}&srv={$ext[1]}'>{$ext[0]}</a>";
//    echo "</td><td>";
//    echo $yac[$ext[1]][$ext[0]] . "&nbsp;";
    echo "</td><td>";
    echo $dnd[$ext[1]][$ext[0]] . "&nbsp;";
    echo "</td><td>";
    echo $cw[$ext[1]][$ext[0]] . "&nbsp;";
    echo "</td><td>";
    echo $cfim[$ext[1]][$ext[0]] . "&nbsp;";
    echo "</td><td>";
    echo $cfbs[$ext[1]][$ext[0]] . "&nbsp;";
    echo "</td><td>";
    echo $cfna[$ext[1]][$ext[0]] . "&nbsp;";
//    echo "</td><td>";
//    echo $cfnas[$ext[1]][$ext[0]] . "&nbsp;";
    echo "</td></tr>";
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

/*  if ($_REQUEST["YAC"])
    $serv['astman']->PutDB(YAC, $ext, $_REQUEST["YAC"]);
  else
    $serv['astman']->DelDB(YAC, $ext);    */
  if ($_REQUEST["DND"])
    $serv['astman']->PutDB(DND, $ext, "YES");
  else
    $serv['astman']->DelDB(DND, $ext);    
  if ($_REQUEST["CW"])
    $serv['astman']->PutDB(CW, $ext, "ENABLED");
  else
    $serv['astman']->DelDB(CW, $ext);    
  if ($_REQUEST["CFBS"])
    $serv['astman']->PutDB(CFBS, $ext, $_REQUEST["CFBS"]);
  else
    $serv['astman']->DelDB(CFBS, $ext);    
  if ($_REQUEST["CFIM"])
    $serv['astman']->PutDB(CFIM, $ext, $_REQUEST["CFIM"]);
  else
    $serv['astman']->DelDB(CFIM, $ext);    
  if ($_REQUEST["CFNA"])
    $serv['astman']->PutDB(CFNA, $ext, $_REQUEST["CFNA"]);
  else
    $serv['astman']->DelDB(CFNA, $ext);    
}

function extcfg_get_extensions_amp($server, $user, $passwd, $db, $astman_nr)
{
  $exts = array();

  $sql_iax = "SELECT id,data FROM iax WHERE keyword = 'callerid' ORDER BY id";
  $sql_sip = "SELECT id,data FROM sip WHERE keyword = 'callerid' ORDER BY id";
  $sql_zap = "SELECT id,data FROM zap WHERE keyword = 'callerid' ORDER BY id";
  
  mysql_connect($server, $user, $passwd) or die ("Could not connect to MySQL");
  mysql_select_db($db) or die ("Could not select $db database");  
  
  $result = mysql_query($sql_iax) or die ("IAX Query failed");
  while ($kolumn = mysql_fetch_array($result)) {
    $exts[] = array($kolumn["id"],$astman_nr);
  }
  
  $result = mysql_query($sql_sip) or die ("SIP Query failed");
  while ($kolumn = mysql_fetch_array($result)) {
    $exts[] = array($kolumn["id"],$astman_nr);
  }
  
  $result = mysql_query($sql_zap) or die ("ZAP Query failed");
  while ($kolumn = mysql_fetch_array($result)) {
    $exts[] = array($kolumn["id"],$astman_nr);
  }
  sort($exts);
  return ($exts);
}
?>
