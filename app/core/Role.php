<?php

namespace Core;

class Role
{
    public const TEACHER = 'teacher';
    public const ADMIN = 'admin';
    public const UMO = 'umo';
    public const DEAN = 'dean';
    public const HEAD = 'head';

    public const ALL = [self::TEACHER, self::ADMIN, self::UMO, self::DEAN, self::HEAD];

    private const LABELS = [
        self::TEACHER => 'Преподаватель',
        self::ADMIN => 'Администратор',
        self::UMO => 'УМО / Ректорат',
        self::DEAN => 'Декан',
        self::HEAD => 'Завкафедрой',
    ];

    private const CSS_CLASSES = [
        self::TEACHER => 'text-primary',
        self::ADMIN => 'text-danger',
        self::UMO => 'text-success',
        self::DEAN => 'text-warning',
        self::HEAD => 'text-info',
    ];

    private const ICONS = [
        self::TEACHER => 'bi-person-workspace',
        self::ADMIN => 'bi-shield-lock-fill',
        self::UMO => 'bi-building',
        self::DEAN => 'bi-person-badge',
        self::HEAD => 'bi-diagram-3',
    ];

    public static function getAvailableRoles(array $user): array
    {
        if (empty($user)) return [];

        if (isset($user['fio'])) return [];

        $roles = [self::TEACHER];

        if ((int)($user['is_superuser'] ?? 0) === 1) {
            $roles[] = self::ADMIN;
        }

        $groupNames = self::getUserGroupNames($user['id']);

        if (in_array('УМО', $groupNames) || in_array('Ректорат', $groupNames)) {
            $roles[] = self::UMO;
        }
        if (in_array('Декан', $groupNames)) {
            $roles[] = self::DEAN;
        }
        if (in_array('Завкафедрой', $groupNames)) {
            $roles[] = self::HEAD;
        }

        return $roles;
    }

    public static function getActiveRole(?array $user): ?string
    {
        if (empty($user) || isset($user['fio'])) return null;

        $available = self::getAvailableRoles($user);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $role = $_SESSION['active_role'] ?? null;
        if ($role && in_array($role, $available)) {
            return $role;
        }

        $default = in_array(self::ADMIN, $available) ? self::ADMIN : self::TEACHER;
        $_SESSION['active_role'] = $default;
        return $default;
    }

    public static function setActiveRole(string $role, array $user): bool
    {
        $available = self::getAvailableRoles($user);
        if (!in_array($role, $available)) return false;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['active_role'] = $role;
        return true;
    }

    public static function isAdmin(?array $user, ?string $role): bool
    {
        return $role === self::ADMIN;
    }

    public static function isUmo(?array $user, ?string $role): bool
    {
        return $role === self::UMO;
    }

    public static function isDean(?array $user, ?string $role): bool
    {
        return $role === self::DEAN;
    }

    public static function isHead(?array $user, ?string $role): bool
    {
        return $role === self::HEAD;
    }

    public static function canManageSettings(?array $user, ?string $role): bool
    {
        return $role === self::ADMIN;
    }

    public static function canManageTeachers(?array $user, ?string $role): bool
    {
        return $role === self::ADMIN;
    }

    public static function canSeeAllCourses(?array $user, ?string $role): bool
    {
        return in_array($role, [self::ADMIN, self::UMO]);
    }

    public static function canSeeAllStudents(?array $user, ?string $role): bool
    {
        return in_array($role, [self::ADMIN, self::UMO]);
    }

    public static function getVisibleFacultyIds(?array $user, ?string $role): ?array
    {
        if (empty($user) || isset($user['fio'])) return [];

        if (in_array($role, [self::ADMIN, self::UMO])) {
            return null;
        }

        if ($role === self::DEAN) {
            $profile = self::getTeacherProfile($user['id']);
            if ($profile && !empty($profile['faculties'])) {
                return array_column($profile['faculties'], 'id');
            }
            return [];
        }

        if ($role === self::HEAD) {
            $profile = self::getTeacherProfile($user['id']);
            if ($profile && !empty($profile['faculties'])) {
                $facultyIds = array_column($profile['faculties'], 'id');
                $deptIds = \Core\Database::fetchAll(
                    "SELECT id FROM structure_department WHERE faculty_id IN (" . implode(',', array_fill(0, count($facultyIds), '?')) . ")",
                    $facultyIds
                );
                $deptIds = array_column($deptIds, 'id');
                if (!empty($deptIds)) {
                    $facultyIds = \Core\Database::fetchAll(
                        "SELECT DISTINCT faculty_id FROM structure_department WHERE id IN (" . implode(',', array_fill(0, count($deptIds), '?')) . ")",
                        $deptIds
                    );
                    return array_column($facultyIds, 'faculty_id');
                }
            }
            return [];
        }

        $profile = self::getTeacherProfile($user['id']);
        if ($profile && !empty($profile['faculties'])) {
            return array_column($profile['faculties'], 'id');
        }
        return [];
    }

    public static function getEditableCourseIds(?array $user, ?string $role, array $extraGroupIds = []): ?array
    {
        if (empty($user) || isset($user['fio'])) return [];

        if ($role === self::ADMIN) return null;

        if ($role === self::UMO) return [];

        $editPerms = ['edit', 'create_delete', 'full_access'];

        $userPerms = \Core\Database::fetchAll(
            "SELECT course_id FROM course_courseuserpermission WHERE user_id = ? AND permission IN (" . implode(',', array_fill(0, count($editPerms), '?')) . ")",
            array_merge([$user['id']], $editPerms)
        );
        $userIds = array_column($userPerms, 'course_id');

        $groupIds = self::getUserGroupIds($user['id']);
        if (!empty($extraGroupIds)) {
            $groupIds = array_unique(array_merge($groupIds, $extraGroupIds));
        }

        $groupPerms = [];
        if (!empty($groupIds)) {
            $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
            $sql = "SELECT course_id FROM course_coursegrouppermission WHERE group_id IN ({$placeholders}) AND permission IN (" . implode(',', array_fill(0, count($editPerms), '?')) . ")";
            $params = array_merge($groupIds, $editPerms);
            $groupPerms = \Core\Database::fetchAll($sql, $params);
            $groupIds = array_column($groupPerms, 'course_id');
        }

        return array_unique(array_merge($userIds, $groupIds));
    }

    public static function getVisibleCourseIds(?array $user, ?string $role): ?array
    {
        if (empty($user) || isset($user['fio'])) return [];

        if (in_array($role, [self::ADMIN, self::UMO])) return null;

        $userPerms = \Core\Database::fetchAll(
            "SELECT course_id FROM course_courseuserpermission WHERE user_id = ?",
            [$user['id']]
        );
        $userIds = array_column($userPerms, 'course_id');

        $groupIds = self::getUserGroupIds($user['id']);
        $groupPerms = [];
        if (!empty($groupIds)) {
            $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
            $groupPerms = \Core\Database::fetchAll("SELECT course_id FROM course_coursegrouppermission WHERE group_id IN ({$placeholders})", $groupIds);
        }

        return array_unique(array_merge($userIds, array_column($groupPerms, 'course_id')));
    }

    public static function getHighestPermission(?array $user, ?string $role, int $courseId): ?string
    {
        if ($role === self::ADMIN) return 'full_access';
        if ($role === self::UMO) return 'view';

        $permWeight = ['full_access' => 4, 'create_delete' => 3, 'edit' => 2, 'view' => 1];
        $best = null;
        $bestWeight = 0;

        $perms = \Core\Database::fetchAll(
            "SELECT permission FROM course_courseuserpermission WHERE user_id = ? AND course_id = ?",
            [$user['id'], $courseId]
        );
        foreach ($perms as $p) {
            $w = $permWeight[$p['permission']] ?? 0;
            if ($w > $bestWeight) { $bestWeight = $w; $best = $p['permission']; }
        }

        $groupIds = self::getUserGroupIds($user['id']);
        if (!empty($groupIds)) {
            $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
            $gPerms = \Core\Database::fetchAll(
                "SELECT permission FROM course_coursegrouppermission WHERE group_id IN ({$placeholders}) AND course_id = ?",
                array_merge($groupIds, [$courseId])
            );
            foreach ($gPerms as $p) {
                $w = $permWeight[$p['permission']] ?? 0;
                if ($w > $bestWeight) { $bestWeight = $w; $best = $p['permission']; }
            }
        }

        return $best;
    }

    private static function getUserGroupIds(int $userId): array
    {
        $rows = \Core\Database::fetchAll(
            "SELECT group_id FROM auth_user_groups WHERE user_id = ?",
            [$userId]
        );
        $ids = array_column($rows, 'group_id');

        $tgRows = \Core\Database::fetchAll(
            "SELECT teachergroup_id FROM accounts_teachergroup_users WHERE user_id = ?",
            [$userId]
        );
        $ids = array_merge($ids, array_column($tgRows, 'teachergroup_id'));

        return array_unique($ids);
    }

    private static function getUserGroupNames(int $userId): array
    {
        $names = \Core\Database::fetchAll(
            "SELECT g.name FROM auth_group g JOIN auth_user_groups ug ON g.id = ug.group_id WHERE ug.user_id = ?",
            [$userId]
        );
        $tgNames = \Core\Database::fetchAll(
            "SELECT tg.name FROM accounts_teachergroup tg JOIN accounts_teachergroup_users tgu ON tg.id = tgu.teachergroup_id WHERE tgu.user_id = ?",
            [$userId]
        );
        return array_unique(array_merge(array_column($names, 'name'), array_column($tgNames, 'name')));
    }

    private static function getTeacherProfile(int $userId): ?array
    {
        $profile = \Core\Database::fetch(
            "SELECT * FROM accounts_teacherprofile WHERE user_id = ?",
            [$userId]
        );
        if (!$profile) return null;

        $faculties = \Core\Database::fetchAll(
            "SELECT f.* FROM structure_faculty f JOIN accounts_teacherprofile_faculties pf ON f.id = pf.faculty_id WHERE pf.teacherprofile_id = ?",
            [$profile['id']]
        );
        $profile['faculties'] = $faculties;
        return $profile;
    }

    public static function label(string $role): string
    {
        return self::LABELS[$role] ?? $role;
    }

    public static function cssClass(string $role): string
    {
        return self::CSS_CLASSES[$role] ?? '';
    }

    public static function icon(string $role): string
    {
        return self::ICONS[$role] ?? 'bi-person';
    }
}
