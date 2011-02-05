<?php 
class AstMan {
  var $socket = false;
  var $error = "";
  var $_events = false;
  
  function AstMan($events = false){
		if ($events == true)
			$this->_events = true;    
  } 

  function Login($host="localhost", $username="admin", $password="amp111"){
    $this->socket = @fsockopen($host, "5038", $errno, $errstr, 1); 
    if (!$this->socket) {
      $this->error =  "Could not connect - $errstr ($errno)";
      return FALSE;
    }else{
      stream_set_timeout($this->socket, 3); 
  		
      if ($this->_events)
      	$query = "Action: Login\r\nUserName: $username\r\nSecret: $password\r\n\r\n";
      else 
      	$query = "Action: Login\r\nUserName: $username\r\nSecret: $password\r\nEvents: off\r\n\r\n";
      
      $wrets = $this->Query($query); 
  
     	if (strpos($wrets, "Message: Authentication accepted") != FALSE){
        return true;
      }else{
  		  $this->error = "Could not login - Authentication failed";
        fclose($this->socket); 
        $this->socket = FALSE;
  		  return FALSE;
   	  }
    }
  }
  
  function Logout(){
    $wrets = "";
    if ($this->socket){
      fputs($this->socket, "Action: Logoff\r\n\r\n"); 
      while (!feof($this->socket)) { 
        $wrets .= fread($this->socket, 8192); 
      } 
      fclose($this->socket); 
      $this->socket = "FALSE";
    }
  	return; 
  }
  
  function Query($query){
    $wrets = "";
    
    if ($this->socket === FALSE)
      return FALSE;
      
    fputs($this->socket, $query); 
    do
    {
      $line = fgets($this->socket, 4096);
      $wrets .= $line;
      $info = stream_get_meta_data($this->socket);
    }while ($line != "\r\n" && $info['timed_out'] == false );
    return $wrets;
  }
  
  function GetError(){
    return $this->error;
  }
  
  function GetDB($family, $key){
    $value = "";
  
    $wrets = $this->Query("Action: Command\r\nCommand: database get $family $key\r\n\r\n");
  
    if ($wrets){
      $value_start = strpos($wrets, "Value: ") + 7;
      $value_stop = strpos($wrets, "\n", $value_start);
    	if ($value_start > 8){
        $value = substr($wrets, $value_start, $value_stop - $value_start);
      }
  	}
   	return $value;
  }	
  
  function PutDB($family, $key, $value){
    $wrets = $this->Query("Action: Command\r\nCommand: database put $family $key $value\r\n\r\n");
  
  	if (strpos($wrets, "Updated database successfully") != FALSE){
  		return TRUE;
   	}
    $this->error =  "Could not updated database";
   	return FALSE;
  }	
  
  function DelDB($family, $key){
    $wrets = $this->Query("Action: Command\r\nCommand: database del $family $key\r\n\r\n");

  	if (strpos($wrets, "Database entry removed.") != FALSE){
  		return TRUE;
   	}
    $this->error =  "Database entry does not exist";
   	return FALSE;
  }	
    
  function GetFamilyDB($family){
    $wrets = $this->Query("Action: Command\r\nCommand: database show $family\r\n\r\n");
    if ($wrets){
      $value_start = strpos($wrets, "Response: Follows\r\n") + 19;
      $value_stop = strpos($wrets, "--END COMMAND--\r\n", $value_start);
    	if ($value_start > 18){
        $wrets = substr($wrets, $value_start, $value_stop - $value_start);
      }
      $lines = explode("\n", $wrets);
      foreach($lines as $line){
        if (strlen($line) > 4){
          $value_start = strpos($line, ": ") + 2;
          $value_stop = strpos($line, " ", $value_start);
        	$key = trim(substr($line, strlen($family) + 2, strpos($line, " ") - strlen($family) + 2));			
          $value[$key] = trim(substr($line, $value_start));
        }
      }
   	  return $value;
  	}
    return FALSE;
  }	  
  
  function GetChannel($account, $callerid){
  	$account_s = "Account: " . $account;
  	$callerid_s = "CallerID: " . $callerid;
  	$value_stop = 0;
  	
    $wrets = $this->Query2("Action: Status\r\n\r\n", "Event: StatusComplete\r\n");
    if ($wrets){
	    $w_len = strlen($wrets);
    	while ($value_stop <= $w_len){
	      $value_start = strpos($wrets, "Event: Status", $value_stop);
	      $value_stop = strpos($wrets, "Event:", $value_start + 6);
	    	if (($value_stop - $value_start) > 0){
	        $status = substr($wrets, $value_start, $value_stop - $value_start);
	        if (strpos($status, $account_s) !== false){
	        	if (strpos($status, $callerid_s) !== false){
  	      		$channel_start = strpos($status, "Channel: ") + 9;
	        		$channel_slut = strpos($status, "\r\n", $channel_start);
	        		$channel = substr($status, $channel_start, $channel_slut - $channel_start);
	        		return $channel;
	        	}
	        }
		    }else{
	    		return FALSE;
	    	}
    	}
    }
    return FALSE;  	
  }

  function GetAllChannel($account, $callerid, $cid_name){
  	$account_s = "Account: " . $account;
  	$callerid_s = "CallerID: " . $callerid;  // Not in use
		$cid_name_s = "CallerIDName: " . $cid_name;
  	$value_stop = 0;
  	$res = array();
  	
    $wrets = $this->Query2("Action: Status\r\n\r\n", "Event: StatusComplete\r\n");
    if ($wrets){
	    $w_len = strlen($wrets);
    	while ($value_stop <= $w_len){
	      $value_start = strpos($wrets, "Event: Status", $value_stop);
	      $value_stop = strpos($wrets, "Event:", $value_start + 6);
	    	if (($value_stop - $value_start) > 0){
	        $status = substr($wrets, $value_start, $value_stop - $value_start);
	        if (strpos($status, $account_s) !== false || strpos($status, $cid_name_s) !== false){
	      		$channel_start = strpos($status, "Channel: ") + 9;
        		$channel_slut = strpos($status, "\r\n", $channel_start);
        		$channel = substr($status, $channel_start, $channel_slut - $channel_start);
        		$res[] = $channel;
	        }
		    }else{
	    		return $res;
	    	}
    	}
    }
    return $res;  	
  }
    
	function Redirect($channel, $exten, $context = 'from-internal', $priority = 1){
    $wrets = $this->Query2("Action: Redirect\r\nChannel: $channel\r\nExten: $exten\r\nContext: $context\r\nPriority: $priority\r\n\r\n", "Message: Redirect successful\r\n");
		if (strpos('Redirect successful', $wrets) !== false)
			return true;
		else 
			return false;
	}
	
  function GetChannelActionID($actionid){
  	$actionid_s = "ActionID: " . $actionid;
  	
  	$value_stop = 0;
  	
    $wrets = $this->Query2("Action: Status\r\n$actionid_s\r\n\r\n", "Event: StatusComplete\r\n");
    if ($wrets){
	    $w_len = strlen($wrets);
      $value_start = strpos($wrets, "Event: Status");
      $value_stop = strpos($wrets, "Event:", $value_start + 6);
    	if (($value_stop - $value_start) > 6){
        $status = substr($wrets, $value_start, $value_stop - $value_start);
        if (strpos($status, $actionid_s) !== false){
      		$channel_start = strpos($status, "Channel: ") + 9;
      		$channel_slut = strpos($status, "\r\n", $channel_start);
      		$channel = substr($status, $channel_start, $channel_slut - $channel_start);
      		return $channel;
        }
	    }else{
    		return FALSE;
    	}
    }
    return FALSE;  	  	
  }
  
  function Hangup($channel){
    $wrets = $this->Query("Action: Hangup\r\nChannel: $channel\r\n\r\n");

  	if (strpos($wrets, "Message: Channel Hungup") != FALSE){
  		return TRUE;
   	}
    $this->error =  "Could not hangup $channel";
   	return FALSE;
  }	
  
  function Query2($query, $end_str){
    $wrets = "";
    
    if ($this->socket === FALSE)
      return FALSE;
      
    fputs($this->socket, $query); 
    do{
      $line = fgets($this->socket, 4096);
      $wrets .= $line;
      $info = stream_get_meta_data($this->socket);
    }while ($line != $end_str && $info['timed_out'] == false );
    return $wrets;
  }
  
  function ExtensionState($exten, $context = 'ext-local'){
/*
Action: ExtensionState
Context: ext-local
Exten: 8546

Response: Success
Message: Extension Status
Exten: 8546
Context: ext-local
Hint: SIP/8546
Status: 0

*/
		$wrets = $this->Query("Action: ExtensionState\r\nContext: $context\r\nExten: $exten\r\n\r\n");
		$status = (int) trim(substr($wrets, strpos($wrets, 'Status: ') + 8));
		return $status;
	}
	
	function get_my_stuff($str, $start_str, $slut_str = ""){
		if ($start_str == false){
			$start = 0;
		}else{
			$start = strpos($str, $start_str);
			if ($start === false)
				return "";
			$start += strlen($start_str);
		}
		if ($slut_str == ""){
			$slut = strlen($str);
		}else{
			$slut = strpos($str, $slut_str, $start);	
		}
		$res = trim(substr($str, $start, $slut - $start));
		return $res;
	}
}
