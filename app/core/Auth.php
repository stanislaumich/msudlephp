<?php

namespace Core;

class Auth
{
    public static function user(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }

    public static function guard(): ?string
    {
        $user = self::user();
        if (!$user) return null;

        return isset($user['fio']) ? 'student' : 'teacher';
    }

    public static function login(array $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($user['password']);
        $_SESSION['user'] = $user;
    }

    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['user']);
        unset($_SESSION['active_role']);
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function student(): ?array
    {
        $user = self::user();
        return isset($user['fio']) ? $user : null;
    }

    public static function teacher(): ?array
    {
        $user = self::user();
        return !isset($user['fio']) ? $user : null;
    }
}
