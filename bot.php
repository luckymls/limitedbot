<?php

include 'http.php';

$content = file_get_contents("php://input");
$update  = json_decode($content, true);


$api = "botTOKEN";


$chat   = $update[message][chat];
$chatID = $chat[id];

$user     = $update[message][from];
$username = $user[username];

$msg = $update[message][text];
$id  = $update[message][reply_to_message][forward_from][id];

#---------------FUNCTION----------------


function sm($chat, $text, $pm = 'HTML')
  {
    
    global $api;
    
    $a = array(
        'chat_id' => $chat,
        'text' => $text,
        'parse_mode' => $pm
    );
    
    $r  = new http("post", "https://api.telegram.org/$api/sendmessage", $a);
    $rr = $r->getResponse();
    $ar = json_decode($rr, true);
    
    
    if ($ar[error_code] == "403")
      {
        return false;
      }
    else
      {
        return true;
      }
    
  }



#---------------Db setup-------------

$tab = 'title';
mysql_select_db("my_userapi"); //Name of Db

#----------------General Setup---------

$admin = -1001085160093; //Admin ID---- If Admin > 1 => Create a group with all the admins and type the chat id

if ($chatID == $admin or $user[id] == $admin)
    $isadmin = true;

#-----------------------------UPDATE/START DB POINT-----------------------------------

if ($update and $chatID > 0)
  {
    
    $a = mysql_fetch_assoc(mysql_query("select * from $tab where chat_id = '$chatID'"));
    if (!$a)
      {
        mysql_query("insert into $tab (chat_id, username, ban) values ('$chatID', '$username', '0')");
        
      }
    else
      {
        mysql_query("update $tab set username='$username' where chat_id='$chatID'");
        if ($a[ban])
            exit;
      }
  }


#-----------------Start Point-------------------------

if ($msg == "/start" and $chatID > 0)
  {
    $text = "Welcome! I am a bot for limited people! Type a message, my admins will reply you soon";
    if ($isadmin)
        $text = "Welcome! This is your bot! To reply: reply to forwarded message";
    sm($chatID, $text);
    break;
  }

#---------------Manage Point------------------------

if (strpos(' ' . $msg, "ban") and $isadmin)
  {
    if ($msg == "/ban")
        $x = 1;
    if ($msg == "/sban")
        $t = 's';
    sm($chatID, "$id $t" . "banned!");
    sm($id, "You have been $t" . "banned from the bot!");
    
    mysql_query("update $tab set ban='$x' where chat_id='$id'");
    break;
  }


#---------------Forward message to admins----------

if (!$isadmin and $chatID > 0)
  {
    
    
    $args = array(
        'chat_id' => $admin,
        'from_chat_id' => $chatID,
        'message_id' => $update[message][message_id]
    );
    
    
    $r = new http("post", "https://api.telegram.org/$api/forwardMessage", $args);
    
  }



#------------------------Reply to a message----------------

if ($msg and $isadmin and $id)
  {
    
    $type = "Staff";
    if ($chatID > 0)
        $type = "Admin";
    
    
    $a = sm($id, "<b>$type:</b> \n" . ucfirst($msg));
    
    if (!$a)
        sm($chatID, "$id disabled the bot");
    mysql_query("DELETE FROM $tab WHERE chat_id = $id");
    
  }
elseif (!$id and $msg and $chatID > 0 and $isadmin)
  {
    sm($chatID, "To reply, reply to a forwarded message");
  }


?>
