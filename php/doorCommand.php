<?php

$database = [
    'host' => 'localhost',
    'dbname' => 'adlyst_homeIOT',
    'user' => 'adlyst_mobin',
    'pass' => 'Mobin-mobin7060'
];

try {
    $db = new PDO("mysql:host={$database['host']};dbname={$database['dbname']}", $database['user'], $database['pass']);
} catch (PDOException $e) {
    die("An error happend, Error: " . $e->getMessage());
}

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);


$botToken = "7149956048:AAGTLQmHuRydQ-smsmzWxtIDXeDps1o9-_A";
$webSite = "https://api.telegram.org/bot" . $botToken;

$update = file_get_contents("php://input");
$update = json_decode($update, TRUE);

$chatId = $update["message"]["chat"]["id"];
$message = $update["message"]["text"];

global $db;

$sql = "SELECT * FROM users WHERE telegramID = :chatID";
$stmt = $db->prepare($sql);
$stmt->bindParam(':chatID', $chatId, PDO::PARAM_STR); // یا PARAM_INT اگر عددی است
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($row);



if($row){
    if (strpos($message, "help") === 0) {
        sendMessage($chatId,"لیست دستورات مجاز برای استفاده در ربات\n adduser:username-displayName-telegramID-isAdmin-accessDoor-accessLight-notification");
    }
    else if (strpos($message, "adduser:") === 0) {
        if($row['isAdmin']){
            $data = str_replace("adduser:", "", $message);
            $parts = explode("-", $data);
            if (count($parts) === 7) {
                list($username,$displayName, $telegramID,$isAdmin ,$haveDoorPermission ,$haveLightPermission,$notification) = $parts;
    
                $sql = "INSERT INTO users (username,displayName, telegramID,isAdmin, haveDoorPermission, haveLightPermission,notification ) 
                        VALUES (:username,:displayName, :telegramID,:isAdmin, :haveDoorPermission, :haveLightPermission,:notification)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':displayName', $displayName);
                $stmt->bindParam(':telegramID', $telegramID);
                $stmt->bindParam(':haveDoorPermission', $haveDoorPermission);
                $stmt->bindParam(':haveLightPermission', $haveLightPermission);
                $stmt->bindParam(':isAdmin', $isAdmin);
                $stmt->bindParam(':notification', $notification);
                $stmt->execute();

                sendMessage($chatId,"کاربر با موفقیت افزوده شد.");
            }
            else{
                sendMessage($chatId,"دستور وارد شده اشتباه است. برای راهنمایی کلمه help .را ارسال کنید");
            }
        }
    }
    else if (strpos($message, "updateuser:") === 0) {
        if ($row['isAdmin']) {
            $data = str_replace("updateuser:", "", $message); // ← اصلاح کلید دستوری
            $parts = explode("-", $data);
            if (count($parts) === 8) {
                list($id,$username,$displayName, $telegramID,$isAdmin ,$haveDoorPermission ,$haveLightPermission,$notification) = $parts;
    
                $sql = "UPDATE users 
                        SET username = :username,
                            displayName = :displayName,
                            telegramID=:telegramID,
                            isAdmin = :isAdmin,
                            haveDoorPermission = :haveDoorPermission,
                            haveLightPermission = :haveLightPermission,
                            notification = :notification
                        WHERE id = :id";
                        
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':displayName', $displayName);
                $stmt->bindParam(':telegramID', $telegramID);
                $stmt->bindParam(':isAdmin', $isAdmin);
                $stmt->bindParam(':haveDoorPermission', $haveDoorPermission);
                $stmt->bindParam(':haveLightPermission', $haveLightPermission);
                $stmt->bindParam(':notification', $notification);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
    
                sendMessage($chatId, "✅ اطلاعات کاربر '$username' بروزرسانی شد.");
            } else {
                sendMessage($chatId, "❌ دستور وارد شده اشتباه است. برای راهنمایی، کلمه help را ارسال کنید.");
            }
        }
    }
    else if (strpos($message, "allusers") === 0) {
        if ($row['isAdmin']) {
            $sql = "SELECT * FROM users";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $allUsers="";
            foreach ($rows as $row){
                $allUsers.="id: " . $row['id'] ."|".
                " username: " . $row['userName'] ."|".
                " telegramID: " . $row['telegramID'] ."|".
                " displayName: " . $row['displayName'] ."|".
                " isAdmin: " . $row['isAdmin'] ."|".
                " haveDoorPermission: " . $row['haveDoorPermission'] ."|".
                " haveLightPermission: " . $row['haveLightPermission']."|".
                " notification: " . $row['notification']."\n";
            }
            sendMessage($chatId,$allUsers);
        }
    }

    else if($message=="open"||$message=="Open"){
        if($row['haveDoorPermission']==1){
            global $db;
            $sql = "UPDATE status SET doorStatus = '1',whichUser='$chatId'  WHERE id = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute();
        }
        else{
            sendPermissionDeniedMessage($chatId);
        }
    }
    else if($message=="light"||$message=="Light")
    {
        if($row['haveLightPermission']==1){
            global $db;
            $sql = "UPDATE status SET lightStatus = '1',whichUser='$chatId' WHERE id =1";
            $stmt = $db->prepare($sql);
            $stmt->execute();
        }
        else{
            sendPermissionDeniedMessage($chatId);
        }
        
    }
    else{
        sendMessage($chatId,"دستور وارد شده اشتباه است.");
    }
  	
}
else
    sendMessage($chatId,"دسترسی شما به این ربات غیر مجاز است.");




function sendMessage($chatId, $message)
{
    $url = $GLOBALS['webSite'] . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message);
    file_get_contents($url);
}
function sendPermissionDeniedMessage($chatId)
{
    $message="دسترسی شما به این بخش غیر فعال است.";
    $url = $GLOBALS['webSite'] . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message);
    file_get_contents($url);
}