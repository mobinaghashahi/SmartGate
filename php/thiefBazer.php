<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);


$botToken = "7149956048:AAGTLQmHuRydQ-smsmzWxtIDXeDps1o9-_A";
$webSite = "https://api.telegram.org/bot" . $botToken;

$update = file_get_contents("php://input");
$update = json_decode($update, TRUE);

$chatId = $update["message"]["chat"]["id"];
$message = $update["message"]["text"];



date_default_timezone_set('Asia/Tehran');
$today = date("D m j Y G:i:s");
if($_POST['key']=="Mobin.7060.mobin.ali.$#@!"){

    if($_POST['action']=="ringON"){
        //send Message to Mobin
        sendMessage("159354346","یکی زنگ زد!! ");
        sendPhoto(159354346, "uploads/1.jpg",$botToken);
        //send Message to Sedighe
        sendMessage("168232585","یکی زنگ زد!! ");
        sendPhoto(168232585, "uploads/1.jpg",$botToken);
        //send Message to Mahsa
        sendMessage("128998617","یکی زنگ زد!! ");
        sendPhoto(128998617, "uploads/1.jpg",$botToken);
        //send Message to Mahdiyeh
        sendMessage("126813675","یکی زنگ زد!! ");
        sendPhoto(126813675, "uploads/1.jpg",$botToken);
        //send Message to Moein
        sendMessage("259898553","یکی زنگ زد!! ");
        sendPhoto(259898553, "uploads/1.jpg",$botToken);
        //send Message to AliReza
        sendMessage("284514919","یکی زنگ زد!! ");
        sendPhoto(284514919, "uploads/1.jpg",$botToken);
        //send Message to Mohadeseh
        sendMessage("6505981674","یکی زنگ زد!! ");
        sendPhoto(6505981674, "uploads/1.jpg",$botToken);
    }
    else if($_POST['action']=="thifAlert"){
        sendMessageToAll("یکی وارد خانه شد!! ");
    }
    else if($_POST['action']=="passwordChanged"){
        sendMessageToAll("رمز عبور درب به: ".$_POST['password']." تغییر کرده است.");
    }
    else if($_POST['action']=="wrongPassword"){
        sendMessageToAll("رمز عبور اشتباه وارد شده است => ".$_POST['password']);
    }
    else if($_POST['action']=="openedDoor"){
        sendMessageToAll("درب خانه توسط  ".whichUser($_POST['whichUser'])." باز شد.");
    }
    else if($_POST['action']=="turnOnLight"){
        sendMessageToAll("برق اتاق توسط  ".whichUser($_POST['whichUser'])." روشن شد.");
    }
    else if($_POST['action']=="turnOffLight"){
        sendMessageToAll("برق اتاق توسط  ".whichUser($_POST['whichUser'])." خاموش شد.");
    }
    else if($_POST['action']=="theDeviseIsReady"){
        sendMessageToAll("دستگاه آماده به کار است!! خیالت راحت باشه.");
    }
}




function sendMessage($chatId, $message)
{
    $url = $GLOBALS['webSite'] . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message);
    file_get_contents($url);
}

function sendPhoto($chatId, $photoPath,$botToken)
{

    $url = "https://api.telegram.org/bot$botToken/sendPhoto";

    $postData = [
        'chat_id' => $chatId,
        'caption' => '',
        'photo' => new CURLFile($photoPath) // ارسال فایل به صورت فرم داده‌ها
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}
function sendMessageToAll($message){
    //send Message to Mobin
    sendMessage("159354346",$message);
    send Message to Sedighe
  	sendMessage("168232585",$message);
  	//send Message to Mahsa
  	sendMessage("128998617",$message);
  	//send Message to Mahdiyeh
  	sendMessage("126813675",$message);
  	//send Message to Moein
  	sendMessage("259898553",$message);
  	//send Message to AliReza
    sendMessage("284514919",$message);
  	//send Message to Mohadeseh
  	sendMessage("6505981674",$message);
}
function whichUser($whichUser){
    if($whichUser==159354346)
        return "مبین";
    else if($whichUser==168232585)
        return "صدیقه";
    else if($whichUser==128998617)
        return "مهسا";
    else if($whichUser==126813675)
        return "مهدیه";
    else if($whichUser==259898553)
        return "معین";
    else if($whichUser==284514919)
        return "علیرضا";
    else if($whichUser==6505981674)
        return "محدثه";
    else if($whichUser=="bluetooth")
        return "بلوتوث";
    else if($whichUser=="lightSwitch")
        return "کلید";
    else if($whichUser=="keyPad")
        return "کیبورد";

}

