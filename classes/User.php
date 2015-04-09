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
        if ($userInfos === NULL)
            return "";
        
        return $userInfos["userName"];
	}
    
    public static function login($userName, $password, $autoLogin = false) {
        
        $db = Db::getInstance();
        
        $userInfo = $db->row(
            "select userID, userName, password, corporationID, isAdmin from brUsers where userName = :userName and deactivatedTime is NULL",
            array(
                "userName" => $userName
            )
        );
        if ($userInfo === FALSE)
            return false;
        
        if (!empty($userInfo["password"])) {
            $pw = $userInfo["password"];
            if (self::checkPassword($password, $pw)) {
                
				if ($userInfo["isAdmin"] != 1 && BR_LOGIN_ONLY_OWNERCORP === true && $userInfo["corporationID"] != BR_OWNERCORP_ID)
					return false;
				
                if ($autoLogin === true) {
                    
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
                    
                    \Slim\Slim::getInstance()->setEncryptedCookie(self::$cookieName, $hash, time() + self::$cookieLifetime, "/");
                    
                }
                
                $_SESSION["isLoggedIn"] = $userInfo["userID"];
                return true;
                
            }
            
        }
        
        return false;
        
    }
    
    public static function logout() {
        
        $sessionCookie = \Slim\Slim::getInstance()->getEncryptedCookie(self::$cookieName, false);
        
        Db::getInstance()->query(
            "delete from brUsersSessions where sessionHash = :sessionHash",
            array(
                "sessionHash" => $sessionCookie
            )
        );
        
        unset($_SESSION["isLoggedIn"]);
        
        setcookie(self::$cookieName, "", time() - self::$cookieLifetime, "/", $_SERVER["HTTP_HOST"]);
        setcookie(self::$cookieName, "", time() - self::$cookieLifetime, "/", "." . $_SERVER["HTTP_HOST"]);
        
    }
    
    public static function checkPassword($enteredPw, $storedPw) {
        
        if (empty($enteredPw) || empty($storedPw))
            return false;
        
        if (password_verify($enteredPw, $storedPw))
            return true;
        
        return false;
        
    }
    
    public static function checkAutoLogin() {
        
        $db = Db::getInstance();
        
        $sessionCookie = \Slim\Slim::getInstance()->getEncryptedCookie(self::$cookieName, false);
        
        if (!empty($sessionCookie)) {
            $cookie = explode("/", $sessionCookie);
            $userName = $cookie[0];
            
            $userID = $db->single(
                "select userID from brUsers where userName = :userName and deactivatedTime is NULL",
                array(
                    "userName" => $userName
                )
            );
            if ($userID === FALSE)
                return false;
            
            $sessionHashes = $db->query(
                "select sessionHash from brUsersSessions where userID = :userID",
                array(
                    "userID" => $userID
                )
            );
            if ($sessionHashes === FALSE)
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
	
	public static function checkEVESSOLogin($characterID, $characterName) {
		
		if (BR_LOGINMETHOD_EVE_SSO !== true)
			return false;
		
		$db = Db::getInstance();
		
		// Check if he's already in the database
		$userInfo = $db->row(
			"select *, IFNULL(deactivatedTime, 0) as deact from brUsers " .
			"where characterID = :characterID",
			array(
				"characterID" => $characterID
			)
		);
		
		$apiLookUp = null;
		try {
			// ALWAYS check for Character Affiliation during
			// login per EVE SSO
			$pheal = new \Pheal\Pheal(null, null, "eve");
			$apiLookUp = $pheal->CharacterInfo(array("characterID" => $characterID));
		} catch (\Pheal\Exceptions\PhealException $ex) {
			$app->log->warn("Could not fetch CharacterInfo for charName \"" . $characterName . "\", charID " . $characterID . ":\n" . $ex);
			
			// Either way, as the user can't
			// be verified, this will be the end ...
			if ($userInfo === FALSE)
				return false;
		}
		
		// User does not exist yet, so create him using
		// the fetched Character Details (Corp, Alliance) ...
		if ($userInfo === FALSE) {
			// ... but ONLY, if he either is member of the BR_OWNERCORP
			// or login is enabled for everyone.
			if (BR_LOGIN_ONLY_OWNERCORP === true && $apiLookUp->corporationID != BR_OWNERCORP_ID)
				return false;
			
			if (!self::register($characterName, '', '', false))
				return false;
			
			// No need to worry, userName column values have to be unique
			$userInfo = $db->query(
				"update brUsers " .
				"set characterID = :characterID " .
				"where userName = :characterName",
				array(
					"characterID" => $characterID,
					"characterName" => $characterName
				)
			);
			if ($userInfo != 1)
				return false;
			
			// Fetch the user infos again
			$userInfo = $db->row(
				"select *, IFNULL(deactivatedTime, 0) as deact from brUsers " .
				"where characterID = :characterID",
				array(
					"characterID" => $characterID
				)
			);
			// Something went horribly wrong
			if ($userInfo === FALSE)
				return false;
		}
		// ... has been deactivated
		if ($userInfo["deact"] > 0)
			return false;
		
		$updUser = array(
			"userID" => $userInfo["userID"],
			"corporationID" => $apiLookUp->corporationID
		);
		if (!empty($apiLookUp->allianceID) && !empty($apiLookUp->alliance))
			$updUser["allianceID"] = $apiLookUp->allianceID;
		$db->query(
			"update brUsers " .
			"set corporationID = :corporationID, " .
				(!empty($apiLookUp->allianceID) && !empty($apiLookUp->alliance) ? "allianceID = :allianceID " : "allianceID = NULL ") .
			"where userID = :userID",
			$updUser
		);
		
		if ($userInfo["isAdmin"] == 1 || (BR_LOGIN_ONLY_OWNERCORP !== true || $apiLookUp->corporationID == BR_OWNERCORP_ID)) {
			$_SESSION["isLoggedIn"] = $userInfo["userID"];
			// Try to update SSO users' rights
			self::updatePermissionsByRoles();
			return true;
		}
		
		return false;
		
	}
    
    public static function getUserInfos() {
        
        $db = Db::getInstance();
        
        if (!self::isLoggedIn())
            return NULL;
        
        $result = $db->row(
            "select * from brUsers where userID = :userID",
            array(
                "userID" => $_SESSION["isLoggedIn"]
            )
        );
        
        return ($result === FALSE) ? NULL : $result;
        
    }
    
    public static function isAdmin() {
        
        $userInfos = self::getUserInfos();
        if ($userInfos == NULL)
            return false;
        
        return ($userInfos["isAdmin"] == 1);
        
    }
    
    public static function can($right = "") {
        
		$right = strtoupper($right);
		
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
        if (($right == "CREATE" || $right == "EDIT") && $userInfos["corporationID"] == BR_OWNERCORP_ID)
            return true;
        
		$db = Db::getInstance();
		$result = $db->row(
			"SELECT * FROM brUserPermissions WHERE brUserID = :brUserID AND brPermission = :brPermission",
			array(
				"brUserID" => self::getUserID(),
				"brPermission" => $right
			)
		);
		if ($result !== FALSE)
			return true;
		
        return false;
        
    }
	
	public static function is($what = "") {
		
		return self::can($what);
		
	}
	
	public static function updatePermissionsByRoles() {
		
		if (BR_LOGINMETHOD_EVE_SSO !== true)
			return false;
		
		$app = \Slim\Slim::getInstance();
		$db = Db::getInstance();
		
		$roleCheckKey = $db->row("SELECT * FROM brEveApiKeys WHERE brApiKeyName = 'RoleCheck' AND brApiKeyOwnerID = 0 AND brApiKeyActive = 1");
		
		// If there's no (active) RoleCheck API Key: bye bye
		if ($roleCheckKey === FALSE)
			return false;
		
		try {
			$pheal = new \Pheal\Pheal($roleCheckKey["keyID"], $roleCheckKey["vCode"], "corp");
			$result = $pheal->MemberSecurity();
		} catch (\Pheal\Exceptions\PhealException $ex) {
			$app->log->error("Could not fetch MemberSecurity because of an Exception:\n" . $ex);
			return false;
		}
		
		// Delete all sso users' permissions
		$db->query(
			"DELETE FROM brUserPermissions " .
			"WHERE brUserID IN (" .
				"SELECT brUserID FROM brUsers WHERE characterID IS NOT NULL AND corporationID = :corporationID" .
			")",
			array(
				"corporationID" => BR_OWNERCORP_ID
			)
		);
		
		// Loop through all current corp members
		foreach ($result->members as $character) {
			
			$roleDirector = false;
			
			foreach ($character->roles as $role) {
				if ($role->roleName == "roleDirector") {
					$roleDirector = true;
					break;
				}
			}
			
			$app->log->debug(
				"Found \"" . $character->name . "\" #" . $character->characterID .
				($roleDirector === true ? " IS " : " IS NOT ") .
				"a director."
			);
			
			if ($roleDirector !== true)
				continue;
			
			$userID = $db->single(
				"SELECT userID FROM brUsers " .
				"WHERE userName = :userName AND characterID = :characterID AND corporationID = :corporationID",
				array(
					"userName" => $character->name,
					"characterID" => $character->characterID,
					"corporationID" => BR_OWNERCORP_ID
				)
			);
			
			if ($userID === FALSE)
				continue;
			
			$insRes = $db->query(
				"INSERT INTO brUserPermissions (brUserID, brPermission) " .
				"VALUES " .
				"(:brDeleteUserID, :brDeletePermission), " .
				"(:brDirectorUserID, :brDirectorPermission)",
				array(
					"brDeleteUserID" => $userID,
					"brDeletePermission" => "DELETE",
					"brDirectorUserID" => $userID,
					"brDirectorPermission" => "DIRECTOR"
				)
			);
			
			if ($insRes !== 2)
				$app->log->warn("Could not insert 'DELETE' and 'DIRECTOR' permissions for user with ID " . $userID);
			
		}
		
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
        
        $db = Db::getInstance();
        
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
        
        $db = Db::getInstance();
        
        if ($isAdmin === false && (strtolower($userName) == "admin" || strtolower($userName) == "administrator" || strtolower($userName) == "root"))
            return false;
        
        if (self::checkRegistration($userName, $email)) {
            
            $pw = password_hash($password, PASSWORD_BCRYPT);
            $result = $db->query(
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
            
            return ($result == 1);
            
        }
        
        return false;
        
    }
	
}
