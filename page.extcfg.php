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

isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';

?>
</div>

<div class="content">
<?php

extcfg_init();

switch ($action) {
  case "phone":
    extcfg_show_phone($_REQUEST['phone'], $_REQUEST['srv']);
  break;
  case "phone_save":
    extcfg_save_phone($_REQUEST['ext'], $_REQUEST['srv']);
  default:
    extcfg_show_list();
  break;
}
extcfg_exit();  
?>
</div>