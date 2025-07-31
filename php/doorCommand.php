<?php
require 'config.php';

//لیست دکمه های موجود برای ربات، برای ساختن دکمه جدید، فیلد مورد نظر را اینجا تعریف کنید.
$commands=['light'=>'💡روشن/خاموش کردن چراغ',
    'door'=>'🚪باز کردن درب',
    'acceptDoorOpen'=>'بله اطمینان دارم!',
    'offNotification'=>'غیر فعال کردن Notification برای کابران',
    'onNotification'=>'فعال کردن Notification برای کاربران',
    'management'=>'👤مدیریت',
    'managementUsers'=>'مدیریت کاربران',
    'managementNotification'=>'مدیریت اعلان ها',
    'back'=>'بازگشت',];


try {
    $db = new PDO("mysql:host={$database['host']};dbname={$database['dbname']}", $database['user'], $database['pass']);
} catch (PDOException $e) {
    die("An error happend, Error: " . $e->getMessage());
}

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);



$webSite = "https://api.telegram.org/bot" . $botToken;

$update = file_get_contents("php://input");
$update = json_decode($update, TRUE);
/*echo "<var>";
var_dump($update);
echo "</var>";*/

if (isset($update["callback_query"])) {

    echo "<pre>";
    var_dump($update);
    echo "</pre>";



    $chatId = $update["callback_query"]["from"]["id"]; //آی دی شخصی که متن رو کلیک کرده
    $callbackData = $update["callback_query"]["data"]; // <-- متن دکمه کلیک‌شده
    $callbackQueryId = $update["callback_query"]["id"]; //آی دی درخواست

    if (strpos($callbackData, "updateAdmin_") === 0) {


        $telegramID = substr($callbackData, strlen("updateAdmin_"));
        //sendMessage($chatId,$telegramID);
        changeAdminPermission($chatId,$telegramID);
        $callbackData=$telegramID;


    }
    else if(strpos($callbackData, "updateHaveDoorPermission_") === 0){
        $telegramID = substr($callbackData, strlen("updateHaveDoorPermission_"));
        //sendMessage($chatId,$telegramID);
        changeDoorPermission($chatId,$telegramID);
        $callbackData=$telegramID;

    }
    else if(strpos($callbackData, "updateHaveLightPermission_") === 0){
        $telegramID = substr($callbackData, strlen("updateHaveLightPermission_"));
        //sendMessage($chatId,$telegramID);
        changeLightPermission($chatId,$telegramID);
        $callbackData=$telegramID;

    }
    else if(strpos($callbackData, "updateNotification_") === 0){
        $telegramID = substr($callbackData, strlen("updateNotification_"));
        //sendMessage($chatId,$telegramID);
        changeNotificationPermission($chatId,$telegramID);
        $callbackData=$telegramID;

    }
    else if(strpos($callbackData, "back") === 0){
        answerCallbackQuery($callbackQueryId,'در حال پردازش...');
        showAllUsersList($chatId,getUserByChatID($chatId),$commands);
    }
    answerCallbackQuery($callbackQueryId,'در حال پردازش...');
    $senderInformation = getUserByChatID($chatId);
    $userInformation = getUserByChatID($callbackData);

    $messageText = 'شما در حال تغییر تنظیمات '.$userInformation['displayName'].' هستید. برای تغییر هر یک از موارد زیر برروی آن ها کلیک کنید.';

    $statusAdmin = $userInformation['isAdmin'] == '1' ? 'کاربر ادمین' : 'کاربر عادی';
    $statusDoorPermission = $userInformation['haveDoorPermission'] == '1' ? '✅ دسترسی به درب دارد' : '❌ دسترسی به درب ندارد';
    $statusLightPermission = $userInformation['haveLightPermission'] == '1' ? '✅ می تواند چراغ را خاموش کند' : '❌ نمی تواند چراغ را خاموش کند';
    $statusNotificationPermission = $userInformation['notification'] == '1' ? '👁️ اعلان ها را می بیند' : '🙈 اعلان ها را نمی بیند';

    $rowButtons = [
        [
            [
                'text' => $statusAdmin,
                'callback_data' => 'updateAdmin_' . $userInformation['telegramID']
            ]
        ],
        [
            [
                'text' => $statusDoorPermission,
                'callback_data' => 'updateHaveDoorPermission_' . $userInformation['telegramID']
            ]
        ]
        ,
        [
            [
                'text' => $statusLightPermission,
                'callback_data' => 'updateHaveLightPermission_' . $userInformation['telegramID']
            ]
        ]
        ,
        [
            [
                'text' => $statusNotificationPermission,
                'callback_data' => 'updateNotification_' . $userInformation['telegramID']
            ]
        ],
        [
            [
                'text' => 'بازگشت',
                'callback_data' => 'back'
            ]
        ]
    ];




    sendMessageWithButtons($chatId, $messageText, $rowButtons);


    return 0;
}


else
{
    $chatId = $update["message"]["chat"]["id"];
    $message = $update["message"]["text"];

    if(havePermissionToBot($chatId)) {
        $row = getUserByChatID($chatId);
        //دستور خاموش شدن چراغ وارد شده است
        if($message==$commands['light'])
            changeLightState($row,$chatId);
        //دستور باز شدن درب وارد شده است
        else if($message==$commands['door'])
            sendAcceptOpenDoorMenu($commands,$chatId);
        //دستور تایید باز شدن درب وارد شده است
        else if($message==$commands['acceptDoorOpen']){
            openDoor($row,$chatId);
        }
        //دستور فعال شدن نوتیفیکیشن برای کاربران وارد شده است
        else if ($message==$commands['onNotification']) {
            onNotification($row,$chatId);
        }
        //دستور غیر فعال شدن نوتیفیکیشن برای کاربران وارد شده است
        else if ($message==$commands['offNotification']) {
            offNotification($row,$chatId);
        }
        //دستور وارد شدن به بخش مدیریت وارد شده است
        else if ($message==$commands['management']) {
            showMainManagementMenu($chatId,$row,$commands);
        }
        //دستور وارد شدن به بخش مدیریت اعلان ها وارد شده است
        else if ($message==$commands['managementNotification']) {
            showNotificationManagementMenu($chatId,$row,$commands);
        }
        //دستور وارد شدن به بخش کاربران وارد شده است
        else if ($message==$commands['managementUsers']) {
            showAllUsersList($chatId,$row,$commands);
        }


        $buttons = [
            [
                ['text' => $commands['light']]
            ],
            [
                ['text' => $commands['door']]
            ],
            [
                ['text'=>$commands['management']]
            ]
        ];

        $result = sendReplyKeyboard($chatId,$buttons,$row['displayName']. ' عزیز، چه کاری میتونم براتون انجام بدم؟');

    }
    return 1;
}







if(havePermissionToBot($chatId)){
    $row=getUserByChatID($chatId);
    if (strpos($message, "help") === 0 ||strpos($message, "Help") === 0) {
        $helpText="لیست دستورات مجاز برای استفاده در ربات

|دیدن تمام کاربرها|

allUsers
------------------------------------------------------------
|اضافه کردن کابر جدید|

addUser:username-displayName-telegramID-isAdmin-accessDoor-accessLight-notification
------------------------------------------------------------
|به روزرسانی کاربرها|

updateUser:id-username-displayName-telegramID-isAdmin-accessDoor-accessLight-notification
------------------------------------------------------------
|خاموش کردن اعلان پیام تمام کاربران|

offNotification
------------------------------------------------------------
|روشن کردن اعلان پیام تمام کاربران|

onNotification";
        sendMessage($chatId,$helpText);
    }
    else if (strpos($message, "addUser:") === 0) {
        if($row['isAdmin']){
            $data = str_replace("addUser:", "", $message);
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
    else if (strpos($message, "updateUser:") === 0) {
        if ($row['isAdmin']) {
            $data = str_replace("updateUser:", "", $message); // ← اصلاح کلید دستوری
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
    else if (strpos($message, "allUsers") === 0) {
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
                    " notification: " . $row['notification']."\n----------------------\n";
            }
            sendMessage($chatId,$allUsers);
        }
    }
    else if (strpos($message, "offNotification") === 0) {
        if ($row['isAdmin']) {
            $sql = "UPDATE users 
                        SET
                            notification = 0";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            sendMessage($chatId,"اعلان پیام ها برای تمام کاربرها قطع شد.");

        }else {
            sendMessage($chatId, "❌ دستور وارد شده اشتباه است. برای راهنمایی، کلمه help را ارسال کنید.");
        }


    }
    else if (strpos($message, "onNotification") === 0) {
        if ($row['isAdmin']) {
            $sql = "UPDATE users 
                        SET
                            notification = 1";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            sendMessage($chatId,"اعلان پیام ها برای تمام کاربرها وصل شد.");
        }else {
            sendMessage($chatId, "❌ دستور وارد شده اشتباه است. برای راهنمایی، کلمه help را ارسال کنید.");
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

function offNotification($user,$chatId){
    if ($user['isAdmin']) {
        global $db;
        $sql = "UPDATE users 
                        SET
                            notification = 0";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        sendMessage($chatId,"اعلان پیام ها برای تمام کاربرها قطع شد.");
    }
    else{
        sendPermissionDeniedMessage($chatId);
    }
}
function onNotification($user,$chatId){
    if ($user['isAdmin']) {
        global $db;
        $sql = "UPDATE users 
                        SET
                            notification = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        sendMessage($chatId,"اعلان پیام ها برای تمام کاربرها وصل شد.");
    }
    else{
        sendPermissionDeniedMessage($chatId);
    }
}
function openDoor($user,$chatId){
    if($user['haveDoorPermission']==1){
        global $db;
        $sql = "UPDATE status SET doorStatus = '1',whichUser='$chatId'  WHERE id = 1";
        $stmt = $db->prepare($sql);
        $stmt->execute();
    }
    else{
        sendPermissionDeniedMessage($chatId);
    }
}
function sendAcceptOpenDoorMenu($commands,$chatId){
    $buttons = [
        [
            ['text' => $commands['acceptDoorOpen']]
        ],
        [
            ['text' => $commands['back']]
        ]
    ];

    $result = sendReplyKeyboard($chatId,$buttons, 'از دستور وارد شده اطمینان دارید؟');
    die();
}
function changeLightState($user,$chatId){
    if($user['haveLightPermission']==1){
        global $db;
        $sql = "UPDATE status SET lightStatus = '1',whichUser='$chatId' WHERE id =1";
        $stmt = $db->prepare($sql);
        $stmt->execute();
    }
    else{
        sendPermissionDeniedMessage($chatId);
    }
}
function changeAdminPermission($chatId="",$telegramID){
    global $db;
    $sql = "SELECT isAdmin FROM users WHERE telegramID = :telegramID";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':telegramID', $telegramID);
    $stmt->execute();
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    //تغییر وضعیت ادمینی
    $currentAdminState=!$current['isAdmin'];


    $sql = "UPDATE users
        SET isAdmin = :isAdmin
        WHERE telegramID = :telegramID";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':isAdmin', $currentAdminState);
    $stmt->bindParam(':telegramID', $telegramID);
    $stmt->execute();
    return;
}
function changeDoorPermission($chatId="",$telegramID){
    global $db;
    $sql = "SELECT haveDoorPermission FROM users WHERE telegramID = :telegramID";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':telegramID', $telegramID);
    $stmt->execute();
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    //تغییر وضعیت ادمینی
    $currentHaveDoorPermission=!$current['haveDoorPermission'];


    $sql = "UPDATE users
        SET haveDoorPermission = :haveDoorPermission
        WHERE telegramID = :telegramID";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':haveDoorPermission', $currentHaveDoorPermission);
    $stmt->bindParam(':telegramID', $telegramID);
    $stmt->execute();
    return;
}
function changeLightPermission($chatId="",$telegramID){
    global $db;
    $sql = "SELECT haveLightPermission FROM users WHERE telegramID = :telegramID";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':telegramID', $telegramID);
    $stmt->execute();
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    //تغییر وضعیت ادمینی
    $currentHaveLightPermission=!$current['haveLightPermission'];


    $sql = "UPDATE users
        SET haveLightPermission = :haveLightPermission
        WHERE telegramID = :telegramID";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':haveLightPermission', $currentHaveLightPermission);
    $stmt->bindParam(':telegramID', $telegramID);
    $stmt->execute();
    return;
}
function changeNotificationPermission($chatId="",$telegramID){
    global $db;
    $sql = "SELECT notification FROM users WHERE telegramID = :telegramID";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':telegramID', $telegramID);
    $stmt->execute();
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    //تغییر وضعیت ادمینی
    $currentNotificationPermission=!$current['notification'];


    $sql = "UPDATE users
        SET notification = :notification
        WHERE telegramID = :telegramID";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':notification', $currentNotificationPermission);
    $stmt->bindParam(':telegramID', $telegramID);
    $stmt->execute();
    return;
}
function showAllUsersList($chatId,$user,$menuFields){
    if ($user['isAdmin']) {
        global $db;
        $sql = "SELECT * FROM users";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $buttons = [];
        $messageText = "یک گزینه را انتخاب کنید:";

        $perRow = 2; // تعداد دکمه در هر ردیف
        $rowCount = count($rows);

        for ($i = 0; $i < $rowCount; $i += $perRow) {
            $rowButtons = [];

            for ($j = 0; $j < $perRow; $j++) {
                if (($i + $j) < $rowCount) {
                    $user = $rows[$i + $j];
                    $rowButtons[] = [
                        'text' => $user['displayName'],
                        'callback_data' => $user['telegramID']
                    ];
                }
            }
            $buttons[] = $rowButtons;
        }

        sendMessageWithButtons($chatId, $messageText, $buttons);


        $buttons = [
            [
                ['text' => $menuFields['back']]
            ]
        ];
        $user=getUserByChatID($chatId);
        $result = sendReplyKeyboard($chatId, $buttons, $user['displayName'] . ' به بخش مدیریت کاربران خوش آمدید.');
        die();
    }
    else{
        sendPermissionDeniedMessage($chatId);
    }
}
function showNotificationManagementMenu($chatId,$user,$menuFields){
    if ($user['isAdmin']) {
        $buttons = [
            [
                ['text' => $menuFields['offNotification']]
            ],
            [
                ['text'=>$menuFields['onNotification']]
            ],
            [
                ['text'=>$menuFields['back']]
            ]
        ];
        $result = sendReplyKeyboard($chatId,$buttons,$user['displayName']. ' به بخش مدیریت خوش آمدید.');
        die();
    }
    else{
        sendPermissionDeniedMessage($chatId);
    }

}
function showMainManagementMenu($chatId,$user,$menuFields){
    if ($user['isAdmin']) {
        $buttons = [
            [
                ['text' => $menuFields['managementNotification']]
            ],
            [
                ['text'=>$menuFields['managementUsers']]
            ],
            [
                ['text'=>$menuFields['back']]
            ]
        ];
        $result = sendReplyKeyboard($chatId,$buttons,$user['displayName']. ' به بخش مدیریت خوش آمدید.');
        die();
    }else{
        sendPermissionDeniedMessage($chatId);
    }


}
function showMainMenu($chatId,$user,$menuFields){
    $buttons = [
        [
            ['text' => $menuFields['light']]
        ],
        [
            ['text' => $menuFields['door']]
        ],
        [
            ['text'=>$menuFields['management']]
        ]
    ];
    $result = sendReplyKeyboard($chatId,$buttons,$user['displayName']. ' عزیز، چه کاری میتونم براتون انجام بدم؟');
    die();
}
function havePermissionToBot($chatId){
    global $db;
    $sql = "SELECT * FROM users WHERE telegramID = :chatID";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':chatID', $chatId, PDO::PARAM_STR); // یا PARAM_INT اگر عددی است
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row)
        return 1;
    return 0;
}
function getUserByChatID($chatId){
    global $db;
    $sql = "SELECT * FROM users WHERE telegramID = :chatID";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':chatID', $chatId, PDO::PARAM_STR); // یا PARAM_INT اگر عددی است
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row;
}
function sendReplyKeyboard($chatId, $buttons,$message) {

    $url = $GLOBALS['webSite'] . "/sendMessage";

    $replyMarkup = [
        'keyboard' => $buttons,
        'resize_keyboard' => true,       // کوچیک/بزرگ شدن خودکار کیبورد
        'one_time_keyboard' => false     // کیبورد بعد از کلیک مخفی نشه
    ];

    $postData = [
        'chat_id' => $chatId,
        'text' => $message,
        'reply_markup' => json_encode($replyMarkup)
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

function sendMessageWithButtons($chatId, $message, $buttons)
{
    $url = $GLOBALS['webSite'] . "/sendMessage";

    // ساختار دکمه‌ها به شکل inline_keyboard
    $replyMarkup = [
        'inline_keyboard' => $buttons
    ];

    $postData = [
        'chat_id' => $chatId,
        'text' => $message,
        'reply_markup' => json_encode($replyMarkup)
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return "Curl error: " . $error_msg;
    }

    curl_close($ch);
    return $response;
}

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

function answerCallbackQuery($callbackId, $text = "", $showAlert = false) {

    $url = $GLOBALS['webSite'] ."/answerCallbackQuery";

    $data = [
        'callback_query_id' => $callbackId,
        'text' => $text,
        'show_alert' => $showAlert
    ];

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
    ];

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}
