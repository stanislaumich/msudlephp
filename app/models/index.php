<?php

namespace Models;

use Core\Database;

class User extends Model
{
    protected static string $table = 'auth_user';

    public static function findByUsername(string $username): ?array
    {
        return static::findOne("username = ?", [$username]);
    }

    public static function findByEmail(string $email): ?array
    {
        return static::findOne("email = ?", [$email]);
    }

    public static function findByUsernameOrEmail(string $login): ?array
    {
        return static::findOne("username = ? OR email = ?", [$login, $login]);
    }

    public static function passwordVerify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function passwordHash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function groups(int $userId): array
    {
        return Database::fetchAll(
            "SELECT g.* FROM auth_group g JOIN auth_user_groups ug ON g.id = ug.group_id WHERE ug.user_id = ?",
            [$userId]
        );
    }
}

class Student extends Model
{
    protected static string $table = 'students_student';

    public static function findByLogin(string $login): ?array
    {
        return static::findOne("login = ?", [$login]);
    }

    public static function passwordHash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function passwordVerify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

class StudentGroup extends Model
{
    protected static string $table = 'students_studentgroup';
}

class DeletedStudent extends Model
{
    protected static string $table = 'students_deletedstudent';
}

class University extends Model
{
    protected static string $table = 'structure_university';
}

class Faculty extends Model
{
    protected static string $table = 'structure_faculty';
}

class Department extends Model
{
    protected static string $table = 'structure_department';
}

class Subject extends Model
{
    protected static string $table = 'subject_subject';
}

class Shifr extends Model
{
    protected static string $table = 'umo_shifr';
}

class Course extends Model
{
    protected static string $table = 'course_course';
}

class CourseSection extends Model
{
    protected static string $table = 'course_coursesection';
}

class CourseTopic extends Model
{
    protected static string $table = 'course_coursetopic';
}

class LearningUnit extends Model
{
    protected static string $table = 'course_learningunit';
}

class CourseUserPermission extends Model
{
    protected static string $table = 'course_courseuserpermission';
}

class CourseGroupPermission extends Model
{
    protected static string $table = 'course_coursegrouppermission';
}

class CourseGroupStudent extends Model
{
    protected static string $table = 'course_coursegroupstudent';
}

class StudentAnswer extends Model
{
    protected static string $table = 'course_studentanswer';

    public static function getUncheckedCountsByCourseIds(array $courseIds): array
    {
        if (empty($courseIds)) return [];

        $placeholders = implode(',', array_fill(0, count($courseIds), '?'));

        $topicUnchecked = \Core\Database::fetchAll(
            "SELECT cs.course_id, COUNT(*) as cnt
             FROM course_studentanswer sa
             JOIN course_learningunit lu ON sa.learning_unit_id = lu.id
             JOIN course_coursetopic ct ON lu.topic_id = ct.id
             JOIN course_coursesection cs ON ct.section_id = cs.id
             WHERE sa.checked = 0 AND cs.course_id IN ({$placeholders})
             GROUP BY cs.course_id",
            $courseIds
        );

        $directUnchecked = \Core\Database::fetchAll(
            "SELECT cs.course_id, COUNT(*) as cnt
             FROM course_studentanswer sa
             JOIN course_learningunit lu ON sa.learning_unit_id = lu.id
             JOIN course_coursesection cs ON lu.section_id = cs.id
             WHERE sa.checked = 0 AND cs.course_id IN ({$placeholders})
             GROUP BY cs.course_id",
            $courseIds
        );

        $counts = [];
        foreach ($topicUnchecked as $row) $counts[$row['course_id']] = ($counts[$row['course_id']] ?? 0) + $row['cnt'];
        foreach ($directUnchecked as $row) $counts[$row['course_id']] = ($counts[$row['course_id']] ?? 0) + $row['cnt'];

        return $counts;
    }
}

class CourseAnnouncement extends Model
{
    protected static string $table = 'course_courseannouncement';
}

class AnnouncementDismiss extends Model
{
    protected static string $table = 'course_announcementdismiss';
}

class Step extends Model
{
    protected static string $table = 'course_step';
}

class StepQuestion extends Model
{
    protected static string $table = 'course_stepquestion';
}

class StepChoice extends Model
{
    protected static string $table = 'course_stepchoice';
}

class StepProgress extends Model
{
    protected static string $table = 'course_stepprogress';
}

class Test extends Model
{
    protected static string $table = 'testing_test';
}

class Question extends Model
{
    protected static string $table = 'testing_question';
}

class Choice extends Model
{
    protected static string $table = 'testing_choice';
}

class DeletedTest extends Model
{
    protected static string $table = 'testing_deletedtest';
}

class TestResult extends Model
{
    protected static string $table = 'testing_result';
}

class ChatRoom extends Model
{
    protected static string $table = 'chat_chatroom';
}

class GroupChat extends Model
{
    protected static string $table = 'chat_groupchat';
}

class GroupChatMessage extends Model
{
    protected static string $table = 'chat_groupchatmessage';
}

class ChatMessage extends Model
{
    protected static string $table = 'chat_chatmessage';

    public static function unreadCount(): int
    {
        if (!\Core\Auth::check()) return 0;

        $user = \Core\Auth::user();
        $debug = ["user_id" => $user['id'] ?? 'N/A', "has_fio" => isset($user['fio']), "is_superuser" => $user['is_superuser'] ?? 'NOT SET'];

        if (isset($user['fio'])) {
            $rooms = \Core\Database::fetchAll(
                "SELECT id FROM chat_chatroom WHERE student_id = ? AND is_deleted = 0",
                [$user['id']]
            );
        } else {
            $role = \Core\Role::getActiveRole($user);
            $visibleIds = \Core\Role::getVisibleCourseIds($user, $role);
            if ($visibleIds === null || (empty($visibleIds) && $role === null && !empty($user['is_superuser']))) {
                $courseIds = array_column(\Models\Course::where('is_deleted = 0'), 'id');
            } else {
                $courseIds = $visibleIds ?? [];
            }
            $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
            $rooms = \Core\Database::fetchAll(
                "SELECT id FROM chat_chatroom WHERE course_id IN ({$placeholders}) AND is_deleted = 0",
                $courseIds
            );
        }

        $roomIds = array_column($rooms, 'id');

        $total = 0;
        if (!empty($roomIds)) {
            $placeholders = implode(',', array_fill(0, count($roomIds), '?'));
            $result = \Core\Database::fetch(
                "SELECT COUNT(*) as cnt FROM chat_chatmessage WHERE room_id IN ({$placeholders}) AND is_read = 0",
                $roomIds
            );
            $total += (int)($result['cnt'] ?? 0);
        }

        if (isset($user['fio'])) {
            $groupIds = \Core\Database::fetchAll(
                "SELECT gc.id FROM chat_groupchat gc JOIN students_studentgroup sg ON gc.group_id = sg.id WHERE sg.id = ?",
                [$user['group_id']]
            );
            $groupRoomIds = array_column($groupIds, 'id');
            if (!empty($groupRoomIds)) {
                $placeholders = implode(',', array_fill(0, count($groupRoomIds), '?'));
                $result = \Core\Database::fetch(
                    "SELECT COUNT(*) as cnt FROM chat_groupchatmessage WHERE room_id IN ({$placeholders}) AND is_read = 0",
                    $groupRoomIds
                );
                $total += (int)($result['cnt'] ?? 0);
            }
        } else {
            $role = \Core\Role::getActiveRole($user);
            $visibleIds = \Core\Role::getVisibleCourseIds($user, $role);
            if ($visibleIds === null || (empty($visibleIds) && $role === null && !empty($user['is_superuser']))) {
                $courseIds = array_column(\Models\Course::where('is_deleted = 0'), 'id');
            } else {
                $courseIds = $visibleIds ?? [];
            }
            if ($courseIds) {
                $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
                $groupRooms = \Core\Database::fetchAll(
                    "SELECT gc.id FROM chat_groupchat gc
                    JOIN course_coursegroupstudent cgs ON gc.group_id = cgs.group_id
                    WHERE cgs.course_id IN ({$placeholders})",
                    $courseIds
                );
                $groupRoomIds = array_column($groupRooms, 'id');
                if (!empty($groupRoomIds)) {
                    $placeholders = implode(',', array_fill(0, count($groupRoomIds), '?'));
                    $result = \Core\Database::fetch(
                        "SELECT COUNT(*) as cnt FROM chat_groupchatmessage WHERE room_id IN ({$placeholders}) AND is_read = 0",
                        $groupRoomIds
                    );
                    $total += (int)($result['cnt'] ?? 0);
                }
            }
        }

        return $total;
    }
}

class TeacherProfile extends Model
{
    protected static string $table = 'accounts_teacherprofile';
}

class TeacherGroup extends Model
{
    protected static string $table = 'accounts_teachergroup';
}

class TeacherGroupAnnouncement extends Model
{
    protected static string $table = 'accounts_teachergroupannouncement';
}

class GroupAnnouncement extends Model
{
    protected static string $table = 'students_groupannouncement';
}

class CourseType extends Model
{
    protected static string $table = 'course_coursetype';
}

class CourseTypeSection extends Model
{
    protected static string $table = 'course_coursetypesection';
}
