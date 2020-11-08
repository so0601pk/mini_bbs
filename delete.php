<?php
session_start();
require('dbconnect.php');
if(isset($_SESSION['id'])){
    $id = $_REQUEST['id'];
    
    $messages = $db->prepare('SELECT * FROM posts WHERE id=?');
    $messages->execute(array($id));
    $message = $messages->fetch();
 
    if($message['member_id'] == $_SESSION['id']){
        try {
            $del = $db->prepare('DELETE FROM posts WHERE id=?'); // ここにスペルミスがありました。
            $del->execute(array($id));
        } catch (PDOException $e){
            die($e->getMessage());
        } 
    }
}
header('Location: index.php');
exit();
?>