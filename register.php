<?php
$conn=new mysqli('localhost','root','','userdb');
if($conn->connect_error){die();}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim($_POST['name']??'');
    $username=trim($_POST['username']??'');
    $email=trim($_POST['email']??'');
    $password=$_POST['password']??'';
    $confirm=$_POST['confirm_password']??'';
    if($name===''||$username===''||$email===''||$password===''||$password!==$confirm){
        header('Location: register.html?error=validation');
        exit;
    }
    $hash=password_hash($password,PASSWORD_DEFAULT);
    $stmt=$conn->prepare('INSERT INTO users (name,username,email,password) VALUES (?,?,?,?)');
    $stmt->bind_param('ssss',$name,$username,$email,$hash);
    if($stmt->execute()){
        header('Location: login.html');
    }else{
        header('Location: register.html?error=duplicate');
    }
    $stmt->close();
}
$conn->close();
?>