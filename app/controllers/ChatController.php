<?php

namespace Controllers;

use Core\Auth;
use Core\Role;
use Core\Router;
use Core\View;
use Models\ChatRoom;
use Models\GroupChat;
use Models\GroupChatMessage;
use Models\ChatMessage;
use Models\Course;
use Models\Student;

class ChatController
{
    public function index(): void
    {
        \Middleware\Middleware::auth();
        $user = Auth::user();
        $role = Role::getActiveRole($user);

        if (Auth::guard() === 'student') {
            $studentCourseIds = \Core\Database::fetchAll(
                "SELECT course_id FROM course_coursegroupstudent WHERE group_id = ?",
                [$user['group_id']]
            );
            $courseIds = array_column($studentCourseIds, 'course_id');

            $rooms = [];
            if ($courseIds) {
                $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
                $rooms = \Core\Database::fetchAll(
                    "SELECT * FROM chat_chatroom WHERE course_id IN ({$placeholders}) AND student_id = ? AND is_deleted = 0",
                    array_merge($courseIds, [$user['id']])
                );
            }
        } else {
            $visibleIds = Role::getVisibleCourseIds($user, $role);
            if ($visibleIds !== null) {
                $courseIds = $visibleIds;
            } else {
                $courseIds = array_column(Course::where('is_deleted = 0'), 'id');
            }

            $rooms = [];
            if ($courseIds) {
                $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
                $rooms = \Core\Database::fetchAll(
                    "SELECT * FROM chat_chatroom WHERE course_id IN ({$placeholders}) AND is_deleted = 0",
                    $courseIds
                );
            }
        }

        foreach ($rooms as &$room) {
            $room['course'] = Course::find($room['course_id']);
            $room['student'] = Student::find($room['student_id']);
            $room['last_message'] = ChatMessage::findOne("room_id = ?", [$room['id']], 'created_at DESC');
        }

        View::render('chat.index', [
            'user' => $user,
            'role' => $role,
            'rooms' => $rooms,
        ]);
    }

    public function room(int $roomId): void
    {
        \Middleware\Middleware::auth();
        $user = Auth::user();
        $role = Role::getActiveRole($user);
        $room = ChatRoom::findOrFail($roomId);

        $messages = ChatMessage::where("room_id = ?", [$roomId], 'created_at ASC');
        foreach ($messages as &$m) {
            $m['sender_student'] = $m['sender_student_id'] ? Student::find($m['sender_student_id']) : null;
            $m['sender_user'] = $m['sender_user_id'] ? \Models\User::find($m['sender_user_id']) : null;
        }

        if (Auth::guard() === 'student') {
            ChatMessage::updateWhere('room_id = ? AND sender_user_id IS NOT NULL AND is_read = 0', ['is_read' => 1], [$roomId]);
        } else {
            ChatMessage::updateWhere('room_id = ? AND sender_student_id IS NOT NULL AND is_read = 0', ['is_read' => 1], [$roomId]);
        }

        View::render('chat.room', [
            'user' => $user,
            'role' => $role,
            'room' => $room,
            'messages' => $messages,
        ]);
    }

    public function send(int $roomId): void
    {
        \Middleware\Middleware::auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect("/chat/{$roomId}/");
            return;
        }

        $text = trim($_POST['text'] ?? '');
        if (!$text) {
            Router::redirect("/chat/{$roomId}/");
            return;
        }

        $user = Auth::user();

        if (Auth::guard() === 'student') {
            ChatMessage::create([
                'room_id' => $roomId,
                'sender_student_id' => $user['id'],
                'sender_user_id' => null,
                'text' => $text,
            ]);
        } else {
            ChatMessage::create([
                'room_id' => $roomId,
                'sender_student_id' => null,
                'sender_user_id' => $user['id'],
                'text' => $text,
            ]);
        }

        Router::redirect("/chat/{$roomId}/");
    }

    public function markRead(int $roomId): void
    {
        \Middleware\Middleware::auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            View::json(['success' => false]);
            return;
        }

        $user = Auth::user();

        if (Auth::guard() === 'student') {
            ChatMessage::updateWhere('room_id = ? AND sender_user_id IS NOT NULL AND is_read = 0', ['is_read' => 1], [$roomId]);
        } else {
            ChatMessage::updateWhere('room_id = ? AND sender_student_id IS NOT NULL AND is_read = 0', ['is_read' => 1], [$roomId]);
        }

        View::json(['success' => true]);
    }

    public function enterCourseChat(int $courseId): void
    {
        \Middleware\Middleware::auth();
        $user = Auth::user();

        if (Auth::guard() === 'student') {
            $room = \Core\Database::fetch(
                "SELECT * FROM chat_chatroom WHERE course_id = ? AND student_id = ? AND is_deleted = 0",
                [$courseId, $user['id']]
            );
            if (!$room) {
                $roomId = \Core\Database::insert('chat_chatroom', [
                    'course_id' => $courseId,
                    'student_id' => $user['id'],
                ]);
            } else {
                $roomId = $room['id'];
            }
            Router::redirect("/chat/{$roomId}/");
            return;
        }

        $visibleIds = Role::getVisibleCourseIds($user, Role::getActiveRole($user));
        if ($visibleIds !== null && !in_array($courseId, $visibleIds)) {
            Router::redirect('/chat/');
            return;
        }
        $room = \Core\Database::fetch(
            "SELECT * FROM chat_chatroom WHERE course_id = ? AND is_deleted = 0 ORDER BY id LIMIT 1",
            [$courseId]
        );
        if ($room) {
            Router::redirect("/chat/{$room['id']}/");
        } else {
            Router::redirect('/chat/');
        }
    }

    public function markAllRead(): void
    {
        \Middleware\Middleware::auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            View::json(['success' => false]);
            return;
        }

        $user = Auth::user();

        if (isset($user['fio'])) {
            $roomIds = array_column(
                \Core\Database::fetchAll("SELECT id FROM chat_chatroom WHERE student_id = ? AND is_deleted = 0", [$user['id']]),
                'id'
            );
            if (!empty($roomIds)) {
                $placeholders = implode(',', array_fill(0, count($roomIds), '?'));
                \Core\Database::query(
                    "UPDATE chat_chatmessage SET is_read = 1 WHERE room_id IN ({$placeholders}) AND sender_user_id IS NOT NULL AND is_read = 0",
                    $roomIds
                );
            }
            $groupChatIds = array_column(
                \Core\Database::fetchAll(
                    "SELECT gc.id FROM chat_groupchat gc JOIN students_studentgroup sg ON gc.group_id = sg.id WHERE sg.id = ?",
                    [$user['group_id']]
                ),
                'id'
            );
            if (!empty($groupChatIds)) {
                $placeholders = implode(',', array_fill(0, count($groupChatIds), '?'));
                \Core\Database::query(
                    "UPDATE chat_groupchatmessage SET is_read = 1 WHERE room_id IN ({$placeholders}) AND is_read = 0",
                    $groupChatIds
                );
            }
        } else {
            $rooms = ChatRoom::where("is_deleted = 0");
            foreach ($rooms as $room) {
                ChatMessage::updateWhere(
                    'room_id = ? AND sender_student_id IS NOT NULL AND is_read = 0',
                    ['is_read' => 1],
                    [$room['id']]
                );
            }

            $role = Role::getActiveRole($user);
            $visibleIds = \Core\Role::getVisibleCourseIds($user, $role);
            if ($visibleIds === null || (empty($visibleIds) && $role === null && !empty($user['is_superuser']))) {
                $courseIds = array_column(\Models\Course::where('is_deleted = 0'), 'id');
            } else {
                $courseIds = $visibleIds ?? [];
            }
            if ($courseIds) {
                $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
                $groupRoomIds = array_column(
                    \Core\Database::fetchAll(
                        "SELECT gc.id FROM chat_groupchat gc
                        JOIN course_coursegroupstudent cgs ON gc.group_id = cgs.group_id
                        WHERE cgs.course_id IN ({$placeholders})",
                        $courseIds
                    ),
                    'id'
                );
                if (!empty($groupRoomIds)) {
                    $placeholders = implode(',', array_fill(0, count($groupRoomIds), '?'));
                    \Core\Database::query(
                        "UPDATE chat_groupchatmessage SET is_read = 1 WHERE room_id IN ({$placeholders}) AND is_read = 0",
                        $groupRoomIds
                    );
                }
            }
        }

        View::json(['success' => true]);
    }
}
