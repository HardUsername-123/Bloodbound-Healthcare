<?php 

    declare(strict_types= 1);
    require_once("../includes/dbh.inc.php");
    require_once("../includes/session.inc.php");

    if($_SERVER["REQUEST_METHOD"]==="POST")
    {
        $name = $_POST["name"];
        $email = $_POST["email"];
        $pwd = $_POST["pwd"];
        $username = $_POST["username"];

        try {
            //errors
            $errors = [];
    
            if(checkInput($name,$email,$pwd,$username))
            {
                $errors["check_input"] = "Fill all fields!";
            }
            if(username_exists($pdo,$username))
            {
                $errors["user_exists"] = "Users already exists!";
            }
            if(email_exists($pdo,$email))
            {
                $errors["email_exists"] = "Email already exists!";
            }

            if($errors)
            {
                $_SESSION["admin_error_register"] = $errors;
                header("Location:register.php");
                die();
            }

            insert_user($pdo,$name,$username,$pwd,$email);

            $_SESSION["admin"]=$username;

            header("Location:register.php?register=success");

            $pdo = null;
            $stmt = null;

            die();


        } 
        catch (PDOException $e) 
        {
            //throw $th;
            die("query failed: ". $e->getMessage());
        }

    }
    else 
    {
        header("Location:register.php");
        die();
    }
    function checkInput(string $name, string $email, string $pwd, string $username)
    {
        return empty($name) || empty($email) || empty($pwd) || empty($username);
    }
    function username_exists(object $pdo,string $username)
    {
        $query = "SELECT username from admin where username=:username;";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($result) return true;
        return false;
    }
    function email_exists(object $pdo,string $email)
    {
        $query = "SELECT email from admin where email=:email;";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if($result) return true;
        return false;
    }
    function insert_user(object $pdo,string $name,string $username,string $pwd,string $email)
    {
        $query = "INSERT INTO admin(name,username,pwd,email) VALUES (:name,:username,:pwd,:email);";
        $stmt = $pdo->prepare($query);
        $options = [
            "cost"=>10
        ];
        $hashedPwd = password_hash($pwd, PASSWORD_BCRYPT,$options);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":pwd", $hashedPwd);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":name", $name);
        $stmt->execute();
    }
    
?>