<?php
include 'connect.php';
session_start();

if (isset($_POST['signUp'])) {
    $fistName = $_POST['fName'];
    $lastName = $_POST['lName'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email Address Already Exists!";
        header("Location: index.php");
        exit();
    } else {
        $insertQuery = "INSERT INTO users(fistName,lastName,email,password)
                                    VALUES ('$fistName','$lastName','$email','$password')";
        if ($conn->query($insertQuery) === TRUE) {
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Error: " . $conn->error;
            header("Location: index.php");
            exit();
        }
    }
}

if (isset($_POST['signIn'])) {
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['email'] = $row['email'];
        header("Location: homepage.php");
        exit();
    } else {
        $_SESSION['error'] = "Incorrect Email or Password";
        header("Location: index.php");
        exit();
    }
}
?>