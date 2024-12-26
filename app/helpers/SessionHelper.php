<?php

class SessionHelper {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['user']) && !empty($_SESSION['user']);
    }

    public static function getUserId() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        if (isset($_SESSION['user']['_id'])) {
            return $_SESSION['user']['_id'];
        }
        
        if (isset($_SESSION['user']['id'])) {
            return $_SESSION['user']['id'];
        }
        
        return null;
    }

    public static function getUser() {
        return self::isLoggedIn() ? $_SESSION['user'] : null;
    }

    public static function setUser($user) {
        self::start();
        
        if (empty($user)) {
            error_log('Warning: Attempting to set empty user data');
            return false;
        }

        if (isset($user['_id'])) {
            if (is_object($user['_id'])) {
                $user['_id'] = (string) $user['_id'];
            }
        } else if (isset($user['id'])) {
            $user['_id'] = $user['id'];
        } else {
            error_log('Warning: User data missing both _id and id fields');
            return false;
        }

        $_SESSION['user'] = $user;
        
        error_log('User data set in session: ' . json_encode($user));
        return true;
    }

    public static function destroy() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
        }
    }

    public static function debug() {
        error_log('Session status: ' . session_status());
        error_log('Session data: ' . json_encode($_SESSION));
        error_log('Session ID: ' . session_id());
        error_log('User logged in: ' . (self::isLoggedIn() ? 'Yes' : 'No'));
        if (self::isLoggedIn()) {
            error_log('User ID: ' . self::getUserId());
            error_log('User data: ' . json_encode(self::getUser()));
        }
    }
} 