<?php
$config = require 'config.php';
$database = [
    'host' => $config['DB_HOST'],
    'dbname' => $config['DB_NAME'],
    'user' => $config['DB_USER'],
    'pass' => $config['DB_PASSWORD']
];

try {
    $db = new PDO("mysql:host={$database['host']};dbname={$database['dbname']}", $database['user'], $database['pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("An error happened, Error: " . $e->getMessage());
}

if(isset($_POST["key"]) && $_POST["key"]==$config['KEY_WEBSITE']) {
    
                $sql = "SELECT doorStatus, lightStatus,whichUser FROM status WHERE id = 1";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_OBJ);
                
                if($result->doorStatus){
                    $db->beginTransaction();
                    try {
                        $sql = "UPDATE status SET doorStatus = '0' WHERE id = 1";
                        $stmt = $db->prepare($sql);
                        $stmt->execute();
                        $db->commit();

                    } catch (Exception $e) {
                        $db->rollBack();
                    }
                }
                else if($result->lightStatus){
                    $db->beginTransaction();
                    try {
                        $sql = "UPDATE status SET lightStatus = '0' WHERE id = 1";
                        $stmt = $db->prepare($sql);
                        $stmt->execute();
                        $db->commit();
                    } catch (Exception $e) {
                        $db->rollBack();
                    }
                }
                
                
                header('Content-Type: application/json');
                echo json_encode([
                    'doorStatus' => $result->doorStatus,
                    'lightStatus' => $result->lightStatus,
                    'whichUser' => $result->whichUser
                ]);
} 

 else {
            header('Content-Type: application/json');
                echo json_encode([
                    'error' => "Invalid or missing security key"
                ]);
}
?>