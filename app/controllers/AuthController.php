<?php

namespace Controllers;

use Core\Auth;
use Core\Router;
use Core\Role;
use Core\View;
use Models\User;
use Models\Student;
use Models\DeletedStudent;
use Models\StudentGroup;

class AuthController
{
    public function loginForm(): void
    {
        \Middleware\Middleware::guest();
        View::render('auth.login');
    }

    public function login(): void
    {
        \Middleware\Middleware::guest();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/login/');
            return;
        }

        $maxAttempts = 5;
        $lockoutSeconds = 300;
        $attempts = $_SESSION['login_attempts'] ?? 0;
        $firstAttempt = $_SESSION['login_first_attempt'] ?? time();

        if ($attempts >= $maxAttempts && (time() - $firstAttempt) < $lockoutSeconds) {
            $wait = $lockoutSeconds - (time() - $firstAttempt);
            View::render('auth.login', ['error' => "Слишком много попыток входа. Попробуйте через {$wait} секунд."]);
            return;
        }

        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$login || !$password) {
            View::render('auth.login', ['error' => 'Заполните все поля.', 'login' => $login]);
            return;
        }

        $student = Student::findByLogin($login);
        if ($student && Student::passwordVerify($password, $student['password'])) {
            unset($_SESSION['login_attempts'], $_SESSION['login_first_attempt']);
            session_regenerate_id(true);
            Auth::login($student);
            Router::redirect('/dashboard');
            return;
        }

        $user = User::findByUsernameOrEmail($login);
        if ($user && User::passwordVerify($password, $user['password'])) {
            unset($_SESSION['login_attempts'], $_SESSION['login_first_attempt']);
            session_regenerate_id(true);
            Auth::login($user);
            Router::redirect('/home');
            return;
        }

        $_SESSION['login_attempts'] = $attempts + 1;
        if ($attempts === 0) {
            $_SESSION['login_first_attempt'] = time();
        }

        View::render('auth.login', ['error' => 'Неверный логин или пароль.', 'login' => $login]);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['impersonate_original_admin_id'])) {
            $adminId = $_SESSION['impersonate_original_admin_id'];
            unset($_SESSION['impersonate_original_admin_id']);
            unset($_SESSION['impersonate_type']);

            $admin = User::find($adminId);
            if ($admin) {
                Auth::login($admin);
                $availableRoles = Role::getAvailableRoles($admin);
                $_SESSION['active_role'] = in_array(Role::ADMIN, $availableRoles)
                    ? Role::ADMIN
                    : (in_array(Role::UMO, $availableRoles) ? Role::UMO : Role::TEACHER);
                Router::redirect('/home');
                return;
            }
        }

        Auth::logout();
        Router::redirect('/');
    }

    public function impersonateStudent(int $studentId): void
    {
        \Middleware\Middleware::teacher();

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        if (!Role::isAdmin($user, $role)) {
            Router::redirect('/dashboard');
            return;
        }

        $student = Student::findOrFail($studentId);

        $_SESSION['impersonate_original_admin_id'] = $user['id'];
        $_SESSION['impersonate_type'] = 'student';

        Auth::login($student);
        Router::redirect('/dashboard');
    }

    public function stopImpersonation(): void
    {
        \Middleware\Middleware::auth();

        if (!isset($_SESSION['impersonate_original_admin_id'])) {
            Router::redirect('/dashboard');
            return;
        }

        $adminId = $_SESSION['impersonate_original_admin_id'];
        unset($_SESSION['impersonate_original_admin_id']);
        unset($_SESSION['impersonate_type']);

        $admin = User::find($adminId);
        if ($admin) {
            Auth::login($admin);
            $availableRoles = Role::getAvailableRoles($admin);
            $_SESSION['active_role'] = in_array(Role::ADMIN, $availableRoles)
                ? Role::ADMIN
                : (in_array(Role::UMO, $availableRoles) ? Role::UMO : Role::TEACHER);
        }

        Router::redirect('/home');
    }
}
