<?php

class User {
    
    public static function isLoggedIn() {
        return true;
    }
    
    public static function checkAutoLogin() {
        // ...
    }
    
    public static function can($right = "") {
        if (empty($right))
            return false;
        
        return true;
    }
}