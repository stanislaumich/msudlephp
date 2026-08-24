<?php

namespace Controllers;

use Core\Auth;
use Core\Router;
use Core\View;
use Core\Role;
use Core\Flash;
use Models\GroupAnnouncement;
use Models\StudentGroup;

class GroupAnnouncementController
{
    public function list(int $groupId): void
    {
        \Middleware\Middleware::auth();

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $group = StudentGroup::findOrFail($groupId);

        $isStudent = isset($user['fio']);
        $facultyIds = Role::getVisibleFacultyIds($user, $role);

        if ($facultyIds === null) {
            $hasAccess = true;
        } elseif ($isStudent) {
            $hasAccess = isset($user['group_id']) && (int)$user['group_id'] === (int)$groupId;
        } else {
            $hasAccess = $group['faculty_id'] !== null && in_array($group['faculty_id'], $facultyIds);
        }

        if (!$hasAccess) {
            Router::redirect('/students/');
            return;
        }

        $announcements = GroupAnnouncement::where("group_id = ?", [$groupId], 'created_at DESC');

        $authorIds = array_column($announcements, 'author_id');
        $authors = [];
        if (!empty($authorIds)) {
            $placeholders = implode(',', array_fill(0, count($authorIds), '?'));
            $authorRows = \Core\Database::fetchAll(
                "SELECT id, first_name, last_name, username FROM auth_user WHERE id IN ($placeholders)",
                $authorIds
            );
            foreach ($authorRows as $a) $authors[$a['id']] = $a;
        }

        View::render('students.group_announcements', [
            'user' => $user,
            'role' => $role,
            'group' => $group,
            'announcements' => $announcements,
            'authors' => $authors,
        ]);
    }

    public function createForm(int $groupId): void
    {
        \Middleware\Middleware::teacher();

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $group = StudentGroup::findOrFail($groupId);

        $facultyIds = Role::getVisibleFacultyIds($user, $role);
        $hasAccess = $facultyIds === null
            || ($group['faculty_id'] !== null && in_array($group['faculty_id'], $facultyIds));

        if (!$hasAccess) {
            Router::redirect('/students/groups/');
            return;
        }

        View::render('students.group_announcement_form', [
            'user' => $user,
            'role' => $role,
            'group' => $group,
            'announcement' => null,
        ]);
    }

    public function create(int $groupId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/students/groups/{$groupId}/announcements/create/");
            return;
        }

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $group = StudentGroup::findOrFail($groupId);

        $facultyIds = Role::getVisibleFacultyIds($user, $role);
        $hasAccess = $facultyIds === null
            || ($group['faculty_id'] !== null && in_array($group['faculty_id'], $facultyIds));

        if (!$hasAccess) {
            Router::redirect('/students/groups/');
            return;
        }

        $text = trim($_POST['text'] ?? '');

        if (!$text) {
            View::render('students.group_announcement_form', [
                'user' => $user,
                'role' => $role,
                'group' => $group,
                'announcement' => null,
                'error' => 'Введите текст объявления.',
            ]);
            return;
        }

        GroupAnnouncement::create([
            'group_id' => $groupId,
            'author_id' => $user['id'],
            'text' => $text,
        ]);

        Flash::success('Объявление создано.');
        Router::redirect("/students/groups/{$groupId}/announcements/");
    }

    public function delete(int $groupId, int $announcementId): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/students/groups/{$groupId}/announcements/");
            return;
        }

        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $announcement = GroupAnnouncement::findOrFail($announcementId);

        if ((int)$announcement['group_id'] !== (int)$groupId) {
            Router::redirect('/students/groups/');
            return;
        }

        $group = StudentGroup::findOrFail($groupId);
        $facultyIds = Role::getVisibleFacultyIds($user, $role);
        $hasAccess = $facultyIds === null
            || ($group['faculty_id'] !== null && in_array($group['faculty_id'], $facultyIds));

        if (!$hasAccess) {
            Router::redirect('/students/groups/');
            return;
        }

        if ((int)$announcement['author_id'] !== (int)$user['id'] && !Role::isAdmin($user, $role)) {
            Router::redirect("/students/groups/{$groupId}/announcements/");
            return;
        }

        GroupAnnouncement::deleteWhere('id = ?', [$announcementId]);

        Flash::success('Объявление удалено.');
        Router::redirect("/students/groups/{$groupId}/announcements/");
    }
}
