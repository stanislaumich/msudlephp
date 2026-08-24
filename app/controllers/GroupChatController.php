<?php

namespace Controllers;

use Core\Auth;
use Core\Role;
use Core\Router;
use Core\View;
use Models\GroupChat;
use Models\GroupChatMessage;
use Models\Student;
use Models\Course;

class GroupChatController
{
    public function index(): void
    {
        \Middleware\Middleware::auth();
        $user = Auth::user();
        $role = Role::getActiveRole($user);

        if (Auth::guard() === 'student') {
            $group = \Core\Database::fetch(
                "SELECT sg.* FROM students_studentgroup sg JOIN students_student s ON sg.id = s.group_id WHERE s.id = ?",
                [$user['id']]
            );
            $groupChats = [];
            if ($group) {
                $placeholders = implode(',', array_fill(0, count([$group['id']]), '?'));
                $groupChats = \Core\Database::fetchAll(
                    "SELECT * FROM chat_groupchat WHERE group_id = ? ORDER BY created_at DESC",
                    [$group['id']]
                );
            }
        } else {
            $visibleIds = Role::getVisibleCourseIds($user, $role);
            $courseIds = $visibleIds !== null
                ? $visibleIds
                : array_column(Course::where('is_deleted = 0'), 'id');
            $groupChats = [];
            if ($courseIds) {
                $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
                $groupChats = \Core\Database::fetchAll(
                    "SELECT DISTINCT gc.* FROM chat_groupchat gc
                    JOIN course_coursegroupstudent cgs ON gc.group_id = cgs.group_id
                    WHERE cgs.course_id IN ({$placeholders})
                    ORDER BY gc.created_at DESC",
                    $courseIds
                );
            }
        }

        foreach ($groupChats as &$gc) {
            $gc['group'] = \Core\Database::fetch("SELECT * FROM students_studentgroup WHERE id = ?", [$gc['group_id']]);
        }

        View::render('chat.group_index', [
            'user' => $user,
            'role' => $role,
            'group_chats' => $groupChats,
        ]);
    }

    public function room(int $groupId): void
    {
        \Middleware\Middleware::auth();
        $user = Auth::user();
        $role = Role::getActiveRole($user);

        $groupChat = \Core\Database::fetch(
            "SELECT * FROM chat_groupchat WHERE group_id = ?",
            [$groupId]
        );

        if (!$groupChat) {
            Router::redirect('/chat/groups/');
            return;
        }

        $messages = GroupChatMessage::where("room_id = ?", [$groupChat['id']], 'created_at ASC');
        foreach ($messages as &$m) {
            $m['sender_student'] = $m['sender_student_id'] ? Student::find($m['sender_student_id']) : null;
        }

        if (Auth::guard() === 'student') {
            GroupChatMessage::updateWhere('room_id = ? AND is_read = 0', ['is_read' => 1], [$groupChat['id']]);
        }

        $group = \Core\Database::fetch("SELECT * FROM students_studentgroup WHERE id = ?", [$groupId]);

        View::render('chat.group_room', [
            'user' => $user,
            'role' => $role,
            'group_chat' => $groupChat,
            'group' => $group,
            'messages' => $messages,
        ]);
    }

    public function send(int $groupId): void
    {
        \Middleware\Middleware::auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/chat/groups/{$groupId}/");
            return;
        }

        $text = trim($_POST['text'] ?? '');
        if (!$text) {
            Router::redirect("/chat/groups/{$groupId}/");
            return;
        }

        $user = Auth::user();
        if (Auth::guard() !== 'student') {
            Router::redirect("/chat/groups/{$groupId}/");
            return;
        }

        $groupChat = \Core\Database::fetch("SELECT * FROM chat_groupchat WHERE group_id = ?", [$groupId]);
        if (!$groupChat) {
            $groupChat['id'] = \Core\Database::insert('chat_groupchat', ['group_id' => $groupId]);
        }

        GroupChatMessage::create([
            'room_id' => $groupChat['id'],
            'sender_student_id' => $user['id'],
            'text' => $text,
        ]);

        Router::redirect("/chat/groups/{$groupId}/");
    }

    public function markRead(int $groupId): void
    {
        \Middleware\Middleware::auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            View::json(['success' => false]);
            return;
        }

        $user = Auth::user();
        if (Auth::guard() !== 'student') {
            View::json(['success' => true]);
            return;
        }

        $groupChat = \Core\Database::fetch("SELECT * FROM chat_groupchat WHERE group_id = ?", [$groupId]);
        if ($groupChat) {
            GroupChatMessage::updateWhere('room_id = ? AND is_read = 0', ['is_read' => 1], [$groupChat['id']]);
        }

        View::json(['success' => true]);
    }
}
