<?php

namespace Middleware;

class Middleware
{
    public static function guest(): void
    {
        if (\Core\Auth::check()) {
            \Core\Router::redirect('/');
        }
    }

    public static function auth(): void
    {
        if (!\Core\Auth::check()) {
            \Core\Router::redirect('/login/');
        }
    }

    public static function teacher(): void
    {
        self::auth();
        if (\Core\Auth::guard() !== 'teacher') {
            \Core\Router::redirect('/student/');
        }
    }

    public static function student(): void
    {
        self::auth();
        if (\Core\Auth::guard() !== 'student') {
            \Core\Router::redirect('/');
        }
    }

    public static function settingsAccess(): void
    {
        self::teacher();
        $user = \Core\Auth::user();
        $role = \Core\Role::getActiveRole($user);
        if (!\Core\Role::canManageSettings($user, $role)) {
            \Core\Router::redirect('/login/');
        }
    }
}
