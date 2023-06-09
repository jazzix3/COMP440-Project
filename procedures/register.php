<?php

// Check if user accessed this page by clicking button on signup.php, else redirect to signup.php
if (isset($_POST["submit"])) {

    require ("dbconnect.php");
    $username = $_POST["username"];
    $password = $_POST["password"];
    $cpassword = $_POST["cpassword"];
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $email = $_POST["email"];

    
    // Validate input--- if any case fails return user to index, include error message 
    // in header, and terminate script.

    // Fields must not be empty
    if (empty($username) || empty($password) || empty($cpassword) || empty($firstName) || empty($lastName) || empty($email)) {
        header("Location:../signup.php?error=emptyfields");
        exit();
    }

    // Password and confirmed password must match
    elseif ($password != $cpassword){
        header("Location:../signup.php?error=passwordmismatch");
        exit();
    }

    // *** MORE VALIDATION TO ADD TO PREVENT SQL INJECTION:
    // Username must be alphanumeric only
    elseif (!ctype_alnum($username)){
        header("Location:../signup.php?error=alphanumericonly");
        exit();
    }

    // Password must be between 3 and 20 characters (can't be under 3 or above 20 characters)
    elseif (strlen($password) <= 3 || strlen($password) >= 20){
        header("Location:../signup.php?error=passwordlength");
        exit();
    }

    // First name and last name must be letters only
    elseif (!ctype_alpha($firstName) && !ctype_alpha($lastName)){
        header("Location:../signup.php?error=lettersonly");
        exit();
    }

    // Email must be correct format (using https://www.w3schools.com/php/filter_validate_email.asp or pattern matching w/ regex)
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        header("Location:../signup.php?error=emailvalidation");
        exit();
    }

    // Username and email must not be duplicates
    else{
        $stmt = $conn->prepare("SELECT username, email FROM user WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                if ($row['username'] == $username && $row['email'] == $email){
                    header("Location:../signup.php?error=duplicateboth");
                    exit();
                }
                elseif ($row['username'] == $username) {
                    header("Location:../signup.php?error=duplicateuser");
                    exit();
                }
                elseif ($row['email'] == $email) {
                    header("Location:../signup.php?error=duplicateemail");
                    exit();
                }

            }
        }
        
        
        // All inputs are valid--- insert user information into database and redirect user to index.php to log in
        else{
            $stmt2 = $conn->prepare("INSERT INTO user (username, password, firstName, lastName, email) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("sssss", $username, $password, $firstName, $lastName, $email);
            $stmt2->execute();

            header("Location: ../index.php?error=none");
            exit();           
           
                        
            }
        }

        $stmt->close();
        $stmt2->close();
        $conn->close();
    }

    
else{
    header("Location: ../signup.php");
    exit();
}