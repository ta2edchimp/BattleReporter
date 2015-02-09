<?php

class User {
    
    private static $cookieName = "brSessionCookie";
    private static $cookieLifetime = 2592000;    // 30 days
    
    public static function isLoggedIn() {
        return isset($_SESSION["isLoggedIn"]);
    }
	
	public static function getUserID() {
		if (isset($_SESSION["isLoggedIn"]))
			return $_SESSION["isLoggedIn"];
		else
			return -1;
	}
	
	public static function getUserName() {
        $userInfos = self::getUserInfos();
        if ($userInfos == NULL)
            return "";
        
        return $userInfos["userName"];
	}
    
    public static function login($userName, $password, $autoLogin = false) {
        
        global $db, $app;
        
        $userInfo = $db->row(
            "select userID, userName, password from brUsers where userName = :userName and deactivatedTime is NULL",
            array(
                "userName" => $userName
            )
        );
        if ($userInfo == NULL)
            return false;
        
        if (!empty($userInfo["password"])) {
            $pw = $userInfo["password"];
            if (self::checkPassword($password, $pw)) {
                
                if ($autoLogin == true) {
                    
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $hash = $userName . "/" . hash("sha256", $userName . $hash . time());
                    
                    $validUntil = date("Y-m-d H:i:s", time() + self::$cookieLifetime);
                    $userAgent = $_SERVER["HTTP_USER_AGENT"];
                    $ip = self::getIP();
                    
                    $db->query(
                        "insert into brUsersSessions " .
                        "(userID, sessionHash, validUntil, userAgent, ip) " .
                        "values " .
                        "(:userID, :sessionHash, :validUntil, :userAgent, :ip)",
                        array(
                            "userID" => $userInfo["userID"],
                            "sessionHash" => $hash,
                            "validUntil" => $validUntil,
                            "userAgent" => $userAgent,
                            "ip" => $ip
                        )
                    );
                    
                    $app->setEncryptedCookie(self::$cookieName, $hash, time() + self::$cookieLifetime, "/");
                    
                }
                
                $_SESSION["isLoggedIn"] = $userInfo["userID"];
                return true;
                
            }
            
        }
        
        return false;
        
    }
    
    public static function logout() {
        
        global $db, $app;
        
        $sessionCookie = $app->getEncryptedCookie(self::$cookieName, false);
        
        global $db;
        
        $db->query(
            "delete from brUsersSessions where sessionHash = :sessionHash",
            array(
                "sessionHash" => $sessionCookie
            )
        );
        
        unset($_SESSION["isLoggedIn"]);
        
        setcookie(self::$cookieName, "", time() - self::$cookieName, "/", $_SERVER["HTTP_HOST"]);
        setcookie(self::$cookieName, "", time() - self::$cookieName, "/", "." . $_SERVER["HTTP_HOST"]);
        
    }
    
    public static function checkPassword($enteredPw, $storedPw) {
        
        global $db;
        
        if (empty($enteredPw) || empty($storedPw))
            return false;
        
        if (password_verify($enteredPw, $storedPw))
            return true;
        
        return false;
        
    }
    
    public static function checkAutoLogin() {
        
        global $db, $app;
        
        $sessionCookie = $app->getEncryptedCookie(self::$cookieName, false);
        
        if (!empty($sessionCookie)) {
            $cookie = explode("/", $sessionCookie);
            $userName = $cookie[0];
            
            $userID = $db->single(
                "select userID from brUsers where userName = :userName and deactivatedTime is NULL",
                array(
                    "userName" => $userName
                )
            );
            if ($userID == NULL)
                return false;
            
            $sessionHashes = $db->query(
                "select sessionHash from brUsersSessions where userID = :userID",
                array(
                    "userID" => $userID
                )
            );
            if ($sessionHashes == NULL)
                return false;
            
            foreach ($sessionHashes as $hash) {
                $hash = $hash["sessionHash"];
                if ($sessionCookie == $hash) {
                    $_SESSION["isLoggedIn"] = $userID;
                    
                    return true;
                }
            }
        }
        
        return false;
        
    }
    
    public static function getUserInfos() {
        
        global $db;
        
        if (!isset($_SESSION["isLoggedIn"]))
            return NULL;
        
        $result = $db->row(
            "select * from brUsers where userID = :userID",
            array(
                "userID" => $_SESSION["isLoggedIn"]
            )
        );
        
        return $result;
        
    }
    
    public static function isAdmin() {
        
        $userInfos = self::getUserInfos();
        if ($userInfos == NULL)
            return false;
        
        return ($userInfos["isAdmin"] == 1);
        
    }
    
    public static function can($right = "") {
        
        if (empty($right))
            return false;
        
        $userInfos = self::getUserInfos();
        if ($userInfos == NULL)
            return false;
        
        // Admin has no restrictions
        if ($userInfos["isAdmin"] == 1)
            return true;
        
        // Rights to "create" and "edit" battle reports is
        // restricted to users of the same corporation
        if ($userInfos["corporationID"] == BR_OWNERCORP_ID)
            return true;
        
        return false;
        
    }
    
    public static function getIP() {
        
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]))
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        else
            $ip = $_SERVER['REMOTE_ADDR'];
        
        return $ip;
        
    }
    
    public static function checkRegistration($userName, $email) {
        
        global $db;
        
        $result = $db->query(
            "select userName, email from brUsers where userName = :userName or email = :email",
            array(
                "userName" => $userName,
                "email" => $email
            )
        );
        
        return $result;
        
    }
    
    public static function register($userName, $password, $email, $isAdmin = false) {
        
        global $db;
        
        if (isAdmin == false && (strtolower($userName) == "admin" || strtolower($userName) == "administrator" || strtolower($userName) == "root"))
            return false;
        
        if (self::checkRegistration($userName, $email)) {
            
            $pw = password_hash($password, PASSWORD_BCRYPT);
            $db->query(
                "insert into brUsers " .
                "(userName, password, email, isAdmin) " .
                "values " .
                "(:userName, :password, :email, :isAdmin)",
                array(
                    "userName" => $userName,
                    "password" => $pw,
                    "email" => $email,
                    "isAdmin" => ($isAdmin ? 1 : 0)
                )
            );
            
            return true;
            
        }
        
        return false;
        
    }
    
}