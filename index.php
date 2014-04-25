<?php
/**
 * Created by PhpStorm.
 * User: rail
 * Date: 25.04.14
 * Time: 23:34
 */

require('mydb.php');

//Быстрая генерация hashed юзеров. Для работы нужно закомментить все что после.
//$db = new myDB();
//$db->addUsersPlain('admin','pass');
//$db->addUsersPlain('test','test');
//$db->addUsersPlain('demo','demo');
//echo "good!";


//Быстрая генерация plain-text юзеров. Для работы нужно закомментить все что после.
//$db = new myDB();
//$db->addUsersHash('admin','pass');
//$db->addUsersHash('test','test');
//$db->addUsersHash('demo','demo');
//echo "good!";

session_start();

if(isset($_REQUEST['logout']))
    myDb::logout();
elseif(isset($_SESSION['user_id'])) {
    $mydb = new myDB();
    $mydb->log();
}
elseif(isset($_REQUEST['login']) && isset($_REQUEST['password'])) {
    $mydb = new myDB();
//    if($mydb->validateHash($_REQUEST['login'],$_REQUEST['password'])) {
    if($mydb->validatePlain($_REQUEST['login'],$_REQUEST['password'])) { //пароли plain-text
        $mydb->log();
        echo "Recorded!";
    }
    else {
        echo "Access denied";
        myDb::logout();
    }
}
else {
?>

    <!doctype html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Авторизация</title>
    </head>
    <body>
    <form action="/" method="POST" style="width:300px; margin:auto; text-align:center;">
        <input type="text" name="login" placeholder="Логин"/><br/>
        <input type="password" name="password" placeholder="Пароль"/><br/>
        <input type="submit" value="Поехали"/>
    </form>
    </body>
    </html>

<?php
}

?>