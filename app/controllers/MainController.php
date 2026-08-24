<?php

namespace Controllers;

use Core\Auth;
use Core\Role;
use Core\Router;
use Core\View;
use Models\Course;
use Models\CourseAnnouncement;
use Models\AnnouncementDismiss;
use Models\StudentAnswer;
use Models\ChatRoom;
use Models\ChatMessage;

class MainController
{
    public function index(): void
    {
        if (!Auth::check()) {
            View::render('main.index');
            return;
        }

        if (Auth::guard() === 'student') {
            Router::redirect('/dashboard');
        }

        Router::redirect('/home');
    }

    public function home(): void
    {
        \Middleware\Middleware::teacher();

        $user = Auth::user();
        $role = Role::getActiveRole($user);

        if (Role::isAdmin($user, $role)) {
            Router::redirect('/dashboard');
        }

        if (Role::isUmo($user, $role)) {
            View::render('main.umo_home', ['user' => $user, 'role' => $role]);
            return;
        }

        $visibleIds = Role::getVisibleCourseIds($user, $role);
        $editableIds = Role::getEditableCourseIds($user, $role);

        if ($visibleIds !== null) {
            $courseIds = $visibleIds;
        } else {
            $courseIds = array_column(Course::where('is_deleted = 0'), 'id');
        }

        if ($editableIds !== null) {
            $announceCourseIds = array_intersect($editableIds, $courseIds);
        } else {
            $announceCourseIds = $courseIds;
        }

        $dismissedIds = [];
        if (!empty($announceCourseIds)) {
            $placeholders = implode(',', array_fill(0, count($announceCourseIds), '?'));
            $dismissedIds = array_column(
                \Core\Database::fetchAll(
                    "SELECT announcement_id FROM course_announcementdismiss WHERE user_id = ?",
                    [$user['id']]
                ),
                'announcement_id'
            );
        }

        $announcements = [];
        if (!empty($announceCourseIds)) {
            $placeholders = implode(',', array_fill(0, count($announceCourseIds), '?'));
            $announcements = \Core\Database::fetchAll(
                "SELECT ca.*, c.short_name as course_short_name FROM course_courseannouncement ca
                 JOIN course_course c ON ca.course_id = c.id
                 WHERE ca.course_id IN ({$placeholders})
                 AND ca.id NOT IN (" . implode(',', array_fill(0, count($dismissedIds), '?')) . ")
                 ORDER BY ca.created_at DESC LIMIT 20",
                array_merge($announceCourseIds, $dismissedIds)
            );
        }

        $uncheckedCourses = [];
        if (!empty($courseIds)) {
            $counts = \Models\StudentAnswer::getUncheckedCountsByCourseIds($courseIds);

            if ($counts) {
                $coursePlaceholders = implode(',', array_fill(0, count($counts), '?'));
                $courses = \Core\Database::fetchAll(
                    "SELECT c.id, c.full_name, d.full_name as department_name
                     FROM course_course c
                     JOIN subject_subject s ON c.subject_id = s.id
                     JOIN structure_department d ON s.department_id = d.id
                     WHERE c.id IN ({$coursePlaceholders})",
                    array_keys($counts)
                );
                foreach ($courses as $c) {
                    $uncheckedCourses[] = [
                        'id' => $c['id'],
                        'full_name' => $c['full_name'],
                        'department' => $c['department_name'],
                        'unchecked_count' => $counts[$c['id']],
                    ];
                }
                usort($uncheckedCourses, fn($a, $b) => $b['unchecked_count'] <=> $a['unchecked_count']);
            }
        }

        $unreadChats = [];
        if (!empty($courseIds)) {
            $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
            $rooms = \Core\Database::fetchAll(
                "SELECT * FROM chat_chatroom WHERE course_id IN ({$placeholders}) AND is_deleted = 0",
                $courseIds
            );
            foreach ($rooms as $room) {
                $unread = \Core\Database::fetchAll(
                    "SELECT * FROM chat_chatmessage WHERE room_id = ? AND sender_student_id IS NOT NULL AND is_read = 0 ORDER BY created_at ASC",
                    [$room['id']]
                );
                if ($unread) {
                    $unreadChats[] = [
                        'room_id' => $room['id'],
                        'course_id' => $room['course_id'],
                        'student_id' => $room['student_id'],
                        'messages' => $unread,
                        'unread_count' => count($unread),
                    ];
                }
            }
        }

        View::render('main.home', [
            'user' => $user,
            'role' => $role,
            'announcements' => $announcements,
            'unchecked_courses' => $uncheckedCourses,
            'unread_chats' => $unreadChats,
        ]);
    }

    public function dashboard(): void
    {
        \Middleware\Middleware::auth();

        $user = Auth::user();

        if (Auth::guard() === 'student') {
            $enrolled = \Core\Database::fetchAll(
                "SELECT c.* FROM course_course c
                 JOIN course_coursegroupstudent cgs ON c.id = cgs.course_id
                 WHERE cgs.group_id = ? AND c.is_deleted = 0",
                [$user['group_id']]
            );

            $studentCourseIds = array_column($enrolled, 'id');

            $dismissedIds = [];
            if ($studentCourseIds) {
                $placeholders = implode(',', array_fill(0, count($studentCourseIds), '?'));
                $dismissedIds = array_column(
                    \Core\Database::fetchAll(
                        "SELECT announcement_id FROM course_announcementdismiss WHERE student_id = ?",
                        [$user['id']]
                    ),
                    'announcement_id'
                );
            }

            $announcements = [];
            if ($studentCourseIds) {
                $placeholders = implode(',', array_fill(0, count($studentCourseIds), '?'));
                $dismissedPlaceholders = implode(',', array_fill(0, count($dismissedIds), '?'));
                $where = "ca.course_id IN ({$placeholders})";
                $params = $studentCourseIds;
                if ($dismissedIds) {
                    $where .= " AND ca.id NOT IN ({$dismissedPlaceholders})";
                    $params = array_merge($params, $dismissedIds);
                }
                $announcements = \Core\Database::fetchAll(
                    "SELECT ca.*, c.short_name as course_short_name FROM course_courseannouncement ca
                     JOIN course_course c ON ca.course_id = c.id
                     WHERE {$where}
                     ORDER BY ca.created_at DESC LIMIT 20",
                    $params
                );
            }

            $coursesData = [];
            foreach ($enrolled as $course) {
                $controlUnits = \Core\Database::fetchAll(
                    "SELECT lu.* FROM course_learningunit lu
                     JOIN course_coursesection cs ON lu.section_id = cs.id
                     WHERE cs.course_id = ? AND lu.content_type = 'control'
                     UNION
                     SELECT lu.* FROM course_learningunit lu
                     JOIN course_coursetopic ct ON lu.topic_id = ct.id
                     JOIN course_coursesection cs ON ct.section_id = cs.id
                     WHERE cs.course_id = ? AND lu.content_type = 'control'
                     ORDER BY `order`, id",
                    [$course['id'], $course['id']]
                );

                if (empty($controlUnits)) continue;

                $unitIds = array_column($controlUnits, 'id');
                $placeholders = implode(',', array_fill(0, count($unitIds), '?'));
                $answers = \Core\Database::fetchAll(
                    "SELECT * FROM course_studentanswer WHERE student_id = ? AND learning_unit_id IN ({$placeholders})",
                    array_merge([$user['id']], $unitIds)
                );
                $answerMap = [];
                foreach ($answers as $a) $answerMap[$a['learning_unit_id']] = $a;

                $totalScore = 0;
                $cells = [];
                foreach ($controlUnits as $unit) {
                    $answer = $answerMap[$unit['id']] ?? null;
                    if ($answer && $answer['checked'] && $answer['score'] !== null) {
                        $totalScore += $answer['score'];
                    }
                    $cells[] = ['unit' => $unit, 'answer' => $answer];
                }

                $avgScore = count($controlUnits) > 0 ? round($totalScore / count($controlUnits), 1) : 0;

                $teacherIds = [];
                $perms = \Core\Database::fetchAll(
                    "SELECT user_id FROM course_courseuserpermission WHERE course_id = ? AND permission IN ('edit','create_delete','full_access')",
                    [$course['id']]
                );
                $teacherIds = array_column($perms, 'user_id');

                $groupPerms = \Core\Database::fetchAll(
                    "SELECT group_id FROM course_coursegrouppermission WHERE course_id = ? AND permission IN ('edit','create_delete','full_access')",
                    [$course['id']]
                );
                $groupIds = array_column($groupPerms, 'group_id');
                if ($groupIds) {
                    $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
                    $extraTeachers = \Core\Database::fetchAll(
                        "SELECT user_id FROM auth_user_groups WHERE group_id IN ({$placeholders})",
                        $groupIds
                    );
                    $teacherIds = array_unique(array_merge($teacherIds, array_column($extraTeachers, 'user_id')));
                }

                $teachers = [];
                if ($teacherIds) {
                    $placeholders = implode(',', array_fill(0, count($teacherIds), '?'));
                    $teachers = \Core\Database::fetchAll(
                        "SELECT id, first_name, last_name, username FROM auth_user WHERE id IN ({$placeholders})",
                        $teacherIds
                    );
                }

                $coursesData[] = [
                    'course' => $course,
                    'control_units' => $controlUnits,
                    'cells' => $cells,
                    'total_score' => $totalScore,
                    'avg_score' => $avgScore,
                    'teachers' => $teachers,
                ];
            }

            $unreadChats = [];
            if ($studentCourseIds) {
                $placeholders = implode(',', array_fill(0, count($studentCourseIds), '?'));
                $rooms = \Core\Database::fetchAll(
                    "SELECT * FROM chat_chatroom WHERE course_id IN ({$placeholders}) AND student_id = ?",
                    array_merge($studentCourseIds, [$user['id']])
                );
                foreach ($rooms as $room) {
                    $unread = \Core\Database::fetchAll(
                        "SELECT * FROM chat_chatmessage WHERE room_id = ? AND sender_user_id IS NOT NULL AND is_read = 0 ORDER BY created_at ASC",
                        [$room['id']]
                    );
                    if ($unread) {
                        $unreadChats[] = [
                            'room_id' => $room['id'],
                            'course_id' => $room['course_id'],
                            'student_id' => $user['id'],
                            'messages' => $unread,
                            'unread_count' => count($unread),
                        ];
                    }
                }
            }

            View::render('main.student_home', [
                'user' => $user,
                'courses_data' => $coursesData,
                'announcements' => $announcements,
                'unread_chats' => $unreadChats,
            ]);
            return;
        }

        $role = Role::getActiveRole($user);
        $visibleIds = Role::getVisibleCourseIds($user, $role);

        if ($visibleIds !== null) {
            $courses = Course::where('is_deleted = 0');
            $courses = array_filter($courses, fn($c) => in_array($c['id'], $visibleIds));
        } else {
            $courses = Course::where('is_deleted = 0');
        }

        $courseIds = array_column($courses, 'id');

        $userPerms = [];
        if ($courseIds) {
            $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
            $perms = \Core\Database::fetchAll(
                "SELECT course_id, permission FROM course_courseuserpermission WHERE user_id = ? AND course_id IN ({$placeholders})",
                array_merge([$user['id']], $courseIds)
            );
            foreach ($perms as $p) $userPerms[$p['course_id']] = $p['permission'];
        }

        $groupPerms = [];
        $userGroupIds = array_column(\Models\User::groups($user['id']), 'id');
        if ($userGroupIds && $courseIds) {
            $groupPlaceholders = implode(',', array_fill(0, count($userGroupIds), '?'));
            $coursePlaceholders = implode(',', array_fill(0, count($courseIds), '?'));
            $gPerms = \Core\Database::fetchAll(
                "SELECT course_id, permission FROM course_coursegrouppermission WHERE group_id IN ({$groupPlaceholders}) AND course_id IN ({$coursePlaceholders})",
                array_merge($userGroupIds, $courseIds)
            );
            foreach ($gPerms as $p) {
                $existing = $groupPerms[$p['course_id']] ?? '';
                if (_permWeight($p['permission']) > _permWeight($existing)) {
                    $groupPerms[$p['course_id']] = $p['permission'];
                }
            }
        }

        $allPerms = array_merge($groupPerms, $userPerms);

        usort($courses, function ($a, $b) use ($allPerms) {
            $pa = $allPerms[$a['id']] ?? '';
            $pb = $allPerms[$b['id']] ?? '';
            return _permWeight($pb) <=> _permWeight($pa) ?: strcmp($a['short_name'], $b['short_name']);
        });

        $isFullAccess = Role::isAdmin($user, $role);

        $creatorPerms = [];
        if ($courseIds) {
            $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
            $cPerms = \Core\Database::fetchAll(
                "SELECT course_id, user_id FROM course_courseuserpermission WHERE course_id IN ({$placeholders}) AND permission = 'full_access'",
                $courseIds
            );
            foreach ($cPerms as $cp) {
                if (!isset($creatorPerms[$cp['course_id']])) {
                    $u = \Models\User::find($cp['user_id']);
                    $creatorPerms[$cp['course_id']] = [
                        'full_name' => $u ? trim(($u['last_name'] ?? '') . ' ' . ($u['first_name'] ?? '')) : '',
                        'short_name' => $u ? _shortName($u) : '',
                    ];
                }
            }
        }

        $uncheckedCounts = [];
        if ($courseIds) {
            $uncheckedCounts = \Models\StudentAnswer::getUncheckedCountsByCourseIds($courseIds);
        }

        $dismissedIds = array_column(
            \Core\Database::fetchAll(
                "SELECT announcement_id FROM course_announcementdismiss WHERE user_id = ?",
                [$user['id']]
            ),
            'announcement_id'
        );

        $announcements = [];
        if ($courseIds) {
            $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
            $dismissedPlaceholders = !empty($dismissedIds) ? implode(',', array_fill(0, count($dismissedIds), '?')) : '0';
            $announcements = \Core\Database::fetchAll(
                "SELECT ca.*, c.short_name as course_short_name FROM course_courseannouncement ca
                 JOIN course_course c ON ca.course_id = c.id
                 WHERE ca.course_id IN ({$placeholders})
                 AND ca.id NOT IN ({$dismissedPlaceholders})
                 ORDER BY ca.created_at DESC LIMIT 20",
                array_merge($courseIds, $dismissedIds)
            );
        }

        $courseData = [];
        foreach ($courses as $course) {
            $dept = '';
            if ($course['subject_id']) {
                $subject = \Models\Subject::find($course['subject_id']);
                if ($subject && $subject['department_id']) {
                    $d = \Core\Database::fetch("SELECT full_name FROM structure_department WHERE id = ?", [$subject['department_id']]);
                    $dept = $d ? $d['full_name'] : '';
                }
            }
            $perm = $isFullAccess ? 'Полный доступ' : ($allPerms[$course['id']] ?? 'Нет прав');
            $courseData[] = [
                'id' => $course['id'],
                'full_name' => $course['full_name'],
                'department' => $dept,
                'permission' => $perm,
                'unchecked_count' => $uncheckedCounts[$course['id']] ?? 0,
                'course_type_name' => '',
                'creator_full_name' => $creatorPerms[$course['id']]['full_name'] ?? '',
                'creator_short_name' => $creatorPerms[$course['id']]['short_name'] ?? '',
            ];
        }

        $unreadChats = [];
        if ($courseIds) {
            $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
            $rooms = \Core\Database::fetchAll(
                "SELECT id FROM chat_chatroom WHERE course_id IN ({$placeholders}) AND is_deleted = 0",
                $courseIds
            );
            $roomIds = array_column($rooms, 'id');
            if ($roomIds) {
                $ph = implode(',', array_fill(0, count($roomIds), '?'));
                $results = \Core\Database::fetchAll(
                    "SELECT room_id, COUNT(*) as cnt FROM chat_chatmessage WHERE room_id IN ({$ph}) AND sender_student_id IS NOT NULL AND is_read = 0 GROUP BY room_id",
                    $roomIds
                );
                foreach ($results as $r) {
                    $unreadChats[] = ['room_id' => $r['room_id'], 'unread_count' => (int)$r['cnt']];
                }
            }
        }

        View::render('main.dashboard', [
            'user' => $user,
            'role' => $role,
            'course_data' => $courseData,
            'announcements' => $announcements,
            'unread_chats' => $unreadChats,
        ]);
    }

    public function setRole(): void
    {
        \Middleware\Middleware::teacher();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::redirect('/');
            return;
        }

        $user = Auth::user();
        $role = $_POST['role'] ?? '';

        if (Role::setActiveRole($role, $user)) {
            $newRole = Role::getActiveRole($user);
            \Core\Database::query("INSERT INTO logs (user_id, action, created_at) VALUES (?, ?, NOW())", [$user['id'], "Role changed to {$newRole}"]);
        }

        Router::back();
    }
}

function _permWeight(string $perm): int
{
    $weights = ['Полный доступ' => 4, 'Создание и удаление' => 3, 'Редактирование' => 2, 'Только просмотр' => 1];
    return $weights[$perm] ?? 0;
}

function _shortName(array $user): string
{
    $parts = [];
    if (!empty($user['last_name'])) $parts[] = $user['last_name'];
    if (!empty($user['first_name'])) $parts[] = mb_substr($user['first_name'], 0, 1) . '.';
    if (!empty($user['middle_name'])) $parts[] = mb_substr($user['middle_name'], 0, 1) . '.';
    return implode(' ', $parts) ?: $user['username'];
}
