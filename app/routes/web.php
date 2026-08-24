<?php

use Core\Router;
use Core\Auth;
use Core\Role;

require_once __DIR__ . '/../core/Router.php';

$router = new Router();

// Main
$router->get('/', [Controllers\MainController::class, 'index']);
$router->get('/home', [Controllers\MainController::class, 'home']);
$router->get('/dashboard', [Controllers\MainController::class, 'dashboard']);
$router->post('/set-role', [Controllers\MainController::class, 'setRole']);

// Students
$router->get('/login/', [Controllers\AuthController::class, 'loginForm']);
$router->post('/login/', [Controllers\AuthController::class, 'login']);
$router->get('/logout/', [Controllers\AuthController::class, 'logout']);

$router->get('/students/', [Controllers\StudentsController::class, 'list']);
$router->get('/students/create/', [Controllers\StudentsController::class, 'createForm']);
$router->post('/students/create/', [Controllers\StudentsController::class, 'create']);
$router->get('/students/{id}/edit/', [Controllers\StudentsController::class, 'editForm']);
$router->post('/students/{id}/edit/', [Controllers\StudentsController::class, 'edit']);
$router->post('/students/{id}/delete/', [Controllers\StudentsController::class, 'delete']);
$router->post('/students/{id}/soft-delete/', [Controllers\StudentsController::class, 'softDelete']);

$router->get('/students/groups/', [Controllers\StudentsController::class, 'groupList']);
$router->get('/students/groups/create/', [Controllers\StudentsController::class, 'groupCreateForm']);
$router->post('/students/groups/create/', [Controllers\StudentsController::class, 'groupCreate']);
$router->get('/students/groups/{id}/edit/', [Controllers\StudentsController::class, 'groupEditForm']);
$router->post('/students/groups/{id}/edit/', [Controllers\StudentsController::class, 'groupEdit']);
$router->post('/students/groups/{id}/delete/', [Controllers\StudentsController::class, 'groupDelete']);

$router->get('/students/archive/', [Controllers\StudentsController::class, 'archiveList']);
$router->get('/students/archive/{id}/restore/', [Controllers\StudentsController::class, 'archiveRestoreForm']);
$router->post('/students/archive/{id}/restore/', [Controllers\StudentsController::class, 'archiveRestore']);

$router->get('/students/export/', [Controllers\StudentsController::class, 'exportCsv']);
$router->get('/students/import/', [Controllers\StudentsController::class, 'importForm']);
$router->post('/students/import/', [Controllers\StudentsController::class, 'import']);
$router->get('/students/import/batch/', [Controllers\StudentsController::class, 'importBatchForm']);
$router->post('/students/import/batch/', [Controllers\StudentsController::class, 'importBatch']);
$router->get('/students/groups/{groupId}/report/', [Controllers\StudentsController::class, 'groupReport']);

$router->post('/impersonate/student/{id}/', [Controllers\AuthController::class, 'impersonateStudent']);
$router->get('/stop-impersonation/', [Controllers\AuthController::class, 'stopImpersonation']);
$router->post('/stop-impersonation/', [Controllers\AuthController::class, 'stopImpersonation']);

// Group announcements
$router->get('/students/groups/{groupId}/announcements/', [Controllers\GroupAnnouncementController::class, 'list']);
$router->get('/students/groups/{groupId}/announcements/create/', [Controllers\GroupAnnouncementController::class, 'createForm']);
$router->post('/students/groups/{groupId}/announcements/create/', [Controllers\GroupAnnouncementController::class, 'create']);
$router->post('/students/groups/{groupId}/announcements/{announcementId}/delete/', [Controllers\GroupAnnouncementController::class, 'delete']);

// Structure
$router->get('/structure/', [Controllers\StructureController::class, 'index']);
$router->get('/structure/universities/', [Controllers\StructureController::class, 'universities']);
$router->get('/structure/universities/create/', [Controllers\StructureController::class, 'universityCreateForm']);
$router->post('/structure/universities/create/', [Controllers\StructureController::class, 'universityCreate']);
$router->get('/structure/universities/{id}/edit/', [Controllers\StructureController::class, 'universityEditForm']);
$router->post('/structure/universities/{id}/edit/', [Controllers\StructureController::class, 'universityEdit']);
$router->post('/structure/universities/{id}/delete/', [Controllers\StructureController::class, 'universityDelete']);

$router->get('/structure/faculties/', [Controllers\StructureController::class, 'faculties']);
$router->get('/structure/faculties/create/', [Controllers\StructureController::class, 'facultyCreateForm']);
$router->post('/structure/faculties/create/', [Controllers\StructureController::class, 'facultyCreate']);
$router->get('/structure/faculties/{id}/edit/', [Controllers\StructureController::class, 'facultyEditForm']);
$router->post('/structure/faculties/{id}/edit/', [Controllers\StructureController::class, 'facultyEdit']);

$router->get('/structure/departments/', [Controllers\StructureController::class, 'departments']);
$router->get('/structure/departments/create/', [Controllers\StructureController::class, 'departmentCreateForm']);
$router->post('/structure/departments/create/', [Controllers\StructureController::class, 'departmentCreate']);
$router->get('/structure/departments/{id}/edit/', [Controllers\StructureController::class, 'departmentEditForm']);
$router->post('/structure/departments/{id}/edit/', [Controllers\StructureController::class, 'departmentEdit']);

// Subjects
$router->get('/subjects/', [Controllers\SubjectController::class, 'index']);
$router->get('/subjects/create/', [Controllers\SubjectController::class, 'createForm']);
$router->post('/subjects/create/', [Controllers\SubjectController::class, 'create']);
$router->get('/subjects/{id}/edit/', [Controllers\SubjectController::class, 'editForm']);
$router->post('/subjects/{id}/edit/', [Controllers\SubjectController::class, 'edit']);

// Course types (типы курсов и их разделы по умолчанию)
$router->get('/course-types/', [Controllers\CourseTypeController::class, 'index']);
$router->get('/course-types/create/', [Controllers\CourseTypeController::class, 'createForm']);
$router->post('/course-types/create/', [Controllers\CourseTypeController::class, 'create']);
$router->get('/course-types/{id}/edit/', [Controllers\CourseTypeController::class, 'editForm']);
$router->post('/course-types/{id}/edit/', [Controllers\CourseTypeController::class, 'edit']);
$router->post('/course-types/{id}/delete/', [Controllers\CourseTypeController::class, 'delete']);

// UMO
$router->get('/umo/', [Controllers\UmoController::class, 'index']);
$router->get('/umo/shifrs/', [Controllers\UmoController::class, 'shifrs']);
$router->get('/umo/shifrs/create/', [Controllers\UmoController::class, 'shifrCreateForm']);
$router->post('/umo/shifrs/create/', [Controllers\UmoController::class, 'shifrCreate']);
$router->get('/umo/shifrs/{id}/edit/', [Controllers\UmoController::class, 'shifrEditForm']);
$router->post('/umo/shifrs/{id}/edit/', [Controllers\UmoController::class, 'shifrEdit']);

// Courses
$router->get('/courses/', [Controllers\CourseController::class, 'index']);
$router->get('/courses/create/', [Controllers\CourseController::class, 'createForm']);
$router->post('/courses/create/', [Controllers\CourseController::class, 'create']);
$router->get('/courses/{id}/', [Controllers\CourseController::class, 'show']);
$router->get('/courses/{id}/edit/', [Controllers\CourseController::class, 'editForm']);
$router->post('/courses/{id}/edit/', [Controllers\CourseController::class, 'edit']);
$router->post('/courses/{id}/delete/', [Controllers\CourseController::class, 'delete']);
$router->post('/courses/{id}/restore/', [Controllers\CourseController::class, 'restore']);
$router->post('/courses/{id}/clone/', [Controllers\CourseController::class, 'clone']);

$router->get('/courses/{courseId}/sections/create/', [Controllers\CourseController::class, 'sectionCreateForm']);
$router->post('/courses/{courseId}/sections/create/', [Controllers\CourseController::class, 'sectionCreate']);
$router->get('/courses/{courseId}/sections/{id}/edit/', [Controllers\CourseController::class, 'sectionEditForm']);
$router->post('/courses/{courseId}/sections/{id}/edit/', [Controllers\CourseController::class, 'sectionEdit']);
$router->post('/courses/{courseId}/sections/{id}/delete/', [Controllers\CourseController::class, 'sectionDelete']);

$router->get('/courses/{courseId}/topics/create/', [Controllers\CourseController::class, 'topicCreateForm']);
$router->post('/courses/{courseId}/topics/create/', [Controllers\CourseController::class, 'topicCreate']);
$router->get('/courses/{courseId}/topics/{id}/edit/', [Controllers\CourseController::class, 'topicEditForm']);
$router->post('/courses/{courseId}/topics/{id}/edit/', [Controllers\CourseController::class, 'topicEdit']);
$router->post('/courses/{courseId}/topics/{id}/delete/', [Controllers\CourseController::class, 'topicDelete']);

$router->get('/courses/{courseId}/units/create/', [Controllers\CourseController::class, 'unitCreateForm']);
$router->post('/courses/{courseId}/units/create/', [Controllers\CourseController::class, 'unitCreate']);
$router->get('/courses/{courseId}/units/{id}/edit/', [Controllers\CourseController::class, 'unitEditForm']);
$router->post('/courses/{courseId}/units/{id}/edit/', [Controllers\CourseController::class, 'unitEdit']);
$router->post('/courses/{courseId}/units/{id}/delete/', [Controllers\CourseController::class, 'unitDelete']);

// Course permissions
$router->post('/courses/{id}/permissions/user/add/', [Controllers\CourseController::class, 'addUserPermission']);
$router->post('/courses/{id}/permissions/user/remove/', [Controllers\CourseController::class, 'removeUserPermission']);
$router->post('/courses/{id}/permissions/group/add/', [Controllers\CourseController::class, 'addGroupPermission']);
$router->post('/courses/{id}/permissions/group/remove/', [Controllers\CourseController::class, 'removeGroupPermission']);

// Steps (пошаговые единицы)
$router->get('/steps/', [Controllers\StepController::class, 'list']);
$router->get('/steps/create/', [Controllers\StepController::class, 'createForm']);
$router->post('/steps/create/', [Controllers\StepController::class, 'create']);
$router->get('/steps/{id}/edit/', [Controllers\StepController::class, 'editForm']);
$router->post('/steps/{id}/edit/', [Controllers\StepController::class, 'edit']);
$router->post('/steps/{id}/delete/', [Controllers\StepController::class, 'delete']);
$router->get('/steps/{id}/', [Controllers\StepController::class, 'show']);
$router->get('/steps/{id}/take/', [Controllers\StepController::class, 'take']);
$router->post('/steps/{id}/take/', [Controllers\StepController::class, 'take']);

// File download
$router->get('/courses/{courseId}/units/{id}/download/', [Controllers\CourseController::class, 'download']);

// Testing — прохождение теста
$router->get('/testing/{id}/take/', [Controllers\TestingController::class, 'take']);
$router->post('/testing/{id}/take/', [Controllers\TestingController::class, 'take']);

// Testing — архив
$router->get('/testing/archive/', [Controllers\TestingController::class, 'archive']);
$router->post('/testing/archive/{id}/restore/', [Controllers\TestingController::class, 'restore']);

// Testing — управление вопросами
$router->get('/testing/{testId}/questions/create/', [Controllers\TestingController::class, 'questionCreate']);
$router->post('/testing/{testId}/questions/create/', [Controllers\TestingController::class, 'questionCreate']);
$router->get('/testing/{testId}/questions/{questionId}/edit/', [Controllers\TestingController::class, 'questionEdit']);
$router->post('/testing/{testId}/questions/{questionId}/edit/', [Controllers\TestingController::class, 'questionEdit']);
$router->post('/testing/{testId}/questions/{questionId}/delete/', [Controllers\TestingController::class, 'questionDelete']);

// Testing — управление вариантами ответов
$router->get('/testing/{testId}/questions/{questionId}/choices/create/', [Controllers\TestingController::class, 'choiceCreate']);
$router->post('/testing/{testId}/questions/{questionId}/choices/create/', [Controllers\TestingController::class, 'choiceCreate']);
$router->get('/testing/{testId}/questions/{questionId}/choices/{choiceId}/edit/', [Controllers\TestingController::class, 'choiceEdit']);
$router->post('/testing/{testId}/questions/{questionId}/choices/{choiceId}/edit/', [Controllers\TestingController::class, 'choiceEdit']);
$router->post('/testing/{testId}/questions/{questionId}/choices/{choiceId}/delete/', [Controllers\TestingController::class, 'choiceDelete']);

// Student answers
$router->get('/courses/{courseId}/units/{unitId}/submit/', [Controllers\StudentAnswerController::class, 'submitForm']);
$router->post('/courses/{courseId}/units/{unitId}/submit/', [Controllers\StudentAnswerController::class, 'submit']);
$router->get('/courses/{courseId}/answers/', [Controllers\StudentAnswerController::class, 'list']);
$router->get('/courses/{courseId}/answers/{answerId}/check/', [Controllers\StudentAnswerController::class, 'checkForm']);
$router->post('/courses/{courseId}/answers/{answerId}/check/', [Controllers\StudentAnswerController::class, 'check']);

// Announcements
$router->post('/courses/{id}/announcements/create/', [Controllers\CourseController::class, 'announcementCreate']);
$router->post('/announcements/{id}/hide/', [Controllers\CourseController::class, 'announcementHide']);
$router->post('/announcements/{id}/dismiss/', [Controllers\CourseController::class, 'announcementDismiss']);
$router->post('/announcements/{id}/edit/', [Controllers\CourseController::class, 'announcementEdit']);
$router->post('/announcements/{id}/delete/', [Controllers\CourseController::class, 'announcementDelete']);

// Testing
$router->get('/testing/', [Controllers\TestingController::class, 'index']);
$router->get('/testing/create/', [Controllers\TestingController::class, 'createForm']);
$router->post('/testing/create/', [Controllers\TestingController::class, 'create']);
$router->get('/testing/{id}/', [Controllers\TestingController::class, 'show']);
$router->get('/testing/{id}/edit/', [Controllers\TestingController::class, 'editForm']);
$router->post('/testing/{id}/edit/', [Controllers\TestingController::class, 'edit']);
$router->post('/testing/{id}/delete/', [Controllers\TestingController::class, 'delete']);

// Chat
$router->get('/chat/', [Controllers\ChatController::class, 'index']);
$router->get('/chat/groups/', [Controllers\GroupChatController::class, 'index']);
$router->get('/chat/course/{courseId}/', [Controllers\ChatController::class, 'enterCourseChat']);
$router->post('/chat/read-all/', [Controllers\ChatController::class, 'markAllRead']);
$router->get('/chat/{roomId}/', [Controllers\ChatController::class, 'room']);
$router->post('/chat/{roomId}/send/', [Controllers\ChatController::class, 'send']);
$router->post('/chat/{roomId}/read/', [Controllers\ChatController::class, 'markRead']);

// Group Chat
$router->get('/chat/groups/{groupId}/', [Controllers\GroupChatController::class, 'room']);
$router->post('/chat/groups/{groupId}/send/', [Controllers\GroupChatController::class, 'send']);
$router->post('/chat/groups/{groupId}/read/', [Controllers\GroupChatController::class, 'markRead']);

// Accounts
$router->get('/accounts/', [Controllers\AccountsController::class, 'index']);
$router->get('/accounts/admins/', [Controllers\AccountsController::class, 'admins']);
$router->get('/accounts/admins/create/', [Controllers\AccountsController::class, 'adminCreateForm']);
$router->post('/accounts/admins/create/', [Controllers\AccountsController::class, 'adminCreate']);
$router->get('/accounts/admins/{id}/edit/', [Controllers\AccountsController::class, 'adminEditForm']);
$router->post('/accounts/admins/{id}/edit/', [Controllers\AccountsController::class, 'adminEdit']);
$router->post('/accounts/admins/{id}/delete/', [Controllers\AccountsController::class, 'adminDelete']);
$router->get('/accounts/teachers/', [Controllers\AccountsController::class, 'teachers']);
$router->get('/accounts/teachers/create/', [Controllers\AccountsController::class, 'teacherCreateForm']);
$router->post('/accounts/teachers/create/', [Controllers\AccountsController::class, 'teacherCreate']);
$router->get('/accounts/teachers/{id}/edit/', [Controllers\AccountsController::class, 'teacherEditForm']);
$router->post('/accounts/teachers/{id}/edit/', [Controllers\AccountsController::class, 'teacherEdit']);
$router->post('/accounts/teachers/{id}/delete/', [Controllers\AccountsController::class, 'teacherDelete']);

$router->get('/accounts/groups/', [Controllers\AccountsController::class, 'groups']);
$router->get('/accounts/groups/create/', [Controllers\AccountsController::class, 'groupCreateForm']);
$router->post('/accounts/groups/create/', [Controllers\AccountsController::class, 'groupCreate']);
$router->get('/accounts/groups/{id}/edit/', [Controllers\AccountsController::class, 'groupEditForm']);
$router->post('/accounts/groups/{id}/edit/', [Controllers\AccountsController::class, 'groupEdit']);
$router->post('/accounts/groups/{id}/delete/', [Controllers\AccountsController::class, 'groupDelete']);

$router->get('/accounts/groups/{groupId}/announcements/', [Controllers\AccountsController::class, 'announcements']);
$router->get('/accounts/groups/{groupId}/announcements/create/', [Controllers\AccountsController::class, 'announcementCreateForm']);
$router->post('/accounts/groups/{groupId}/announcements/create/', [Controllers\AccountsController::class, 'announcementCreate']);
$router->post('/accounts/groups/{groupId}/announcements/{announcementId}/delete/', [Controllers\AccountsController::class, 'announcementDelete']);

$router->get('/accounts/permissions/course/{courseId}/', [Controllers\AccountsController::class, 'coursePermissions']);

$router->post('/accounts/permissions/course/{id}/user/add/', [Controllers\AccountsController::class, 'addCourseUserPermission']);
$router->post('/accounts/permissions/course/{id}/user/remove/', [Controllers\AccountsController::class, 'removeCourseUserPermission']);
$router->post('/accounts/permissions/course/{id}/group/add/', [Controllers\AccountsController::class, 'addCourseGroupPermission']);
$router->post('/accounts/permissions/course/{id}/group/remove/', [Controllers\AccountsController::class, 'removeCourseGroupPermission']);

$router->dispatch();
