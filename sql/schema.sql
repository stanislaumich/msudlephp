-- ==============================================
-- MSUDLE — Database Schema (MySQL)
-- ==============================================

-- Django auth
CREATE TABLE IF NOT EXISTS auth_user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    last_login DATETIME NULL,
    is_superuser TINYINT(1) NOT NULL DEFAULT 0,
    username VARCHAR(150) NOT NULL UNIQUE,
    first_name VARCHAR(150) NOT NULL DEFAULT '',
    last_name VARCHAR(150) NOT NULL DEFAULT '',
    email VARCHAR(254) NOT NULL DEFAULT '',
    is_staff TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    date_joined DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS auth_group (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS auth_group_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    permission_id INT NOT NULL,
    UNIQUE KEY group_permission (group_id, permission_id),
    FOREIGN KEY (group_id) REFERENCES auth_group(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS auth_user_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    group_id INT NOT NULL,
    UNIQUE KEY user_group (user_id, group_id),
    FOREIGN KEY (user_id) REFERENCES auth_user(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES auth_group(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS auth_permission (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    content_type_id INT NOT NULL,
    codename VARCHAR(100) NOT NULL,
    UNIQUE KEY content_type_codename (content_type_id, codename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Structure
CREATE TABLE IF NOT EXISTS structure_university (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(500) NOT NULL,
    short_name VARCHAR(100) NOT NULL,
    identifier VARCHAR(50) UNIQUE NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS structure_faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    university_id INT NOT NULL,
    full_name VARCHAR(500) NOT NULL,
    short_name VARCHAR(100) NOT NULL,
    identifier VARCHAR(50) UNIQUE NULL,
    dean_id INT NULL,
    group_numbers VARCHAR(200) NULL,
    FOREIGN KEY (university_id) REFERENCES structure_university(id) ON DELETE CASCADE,
    FOREIGN KEY (dean_id) REFERENCES auth_user(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS structure_department (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    full_name VARCHAR(500) NOT NULL,
    short_name VARCHAR(100) NOT NULL,
    identifier VARCHAR(50) UNIQUE NULL,
    head_id INT NULL,
    FOREIGN KEY (faculty_id) REFERENCES structure_faculty(id) ON DELETE CASCADE,
    FOREIGN KEY (head_id) REFERENCES auth_user(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subjects
CREATE TABLE IF NOT EXISTS subject_subject (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    full_name VARCHAR(500) NOT NULL,
    short_name VARCHAR(100) NOT NULL,
    identifier VARCHAR(50) UNIQUE NULL,
    FOREIGN KEY (department_id) REFERENCES structure_department(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- UMO
CREATE TABLE IF NOT EXISTS umo_shifr (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(300) NULL,
    qualification VARCHAR(300) NULL,
    faculty_id INT NULL,
    FOREIGN KEY (faculty_id) REFERENCES structure_faculty(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Students
CREATE TABLE IF NOT EXISTS students_studentgroup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_number VARCHAR(50) NOT NULL,
    shifr_id INT NULL,
    enrollment_year SMALLINT NULL,
    study_duration_years SMALLINT NULL,
    study_duration_months SMALLINT NULL,
    faculty_id INT NULL,
    education_form VARCHAR(20) NULL,
    FOREIGN KEY (shifr_id) REFERENCES umo_shifr(id) ON DELETE SET NULL,
    FOREIGN KEY (faculty_id) REFERENCES structure_faculty(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS students_student (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fio VARCHAR(300) NOT NULL,
    group_id INT NULL,
    login VARCHAR(100) NOT NULL UNIQUE,
    record_book_number VARCHAR(50) NOT NULL DEFAULT '',
    password VARCHAR(255) NOT NULL DEFAULT '',
    last_login DATETIME NULL,
    FOREIGN KEY (group_id) REFERENCES students_studentgroup(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS students_deletedstudent (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_id INT NOT NULL,
    fio VARCHAR(300) NOT NULL,
    login VARCHAR(100) NOT NULL,
    record_book_number VARCHAR(50) NOT NULL DEFAULT '',
    password VARCHAR(255) NOT NULL,
    group_id INT NULL,
    group_name VARCHAR(100) NOT NULL,
    last_login DATETIME NULL,
    deleted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS students_groupannouncement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    author_id INT NOT NULL,
    text TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES students_studentgroup(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES auth_user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Accounts
CREATE TABLE IF NOT EXISTS accounts_teacherprofile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    middle_name VARCHAR(100) NOT NULL DEFAULT '',
    department VARCHAR(200) NOT NULL DEFAULT '',
    position VARCHAR(200) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES auth_user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS accounts_teacherprofile_faculties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacherprofile_id INT NOT NULL,
    faculty_id INT NOT NULL,
    UNIQUE KEY teacherprofile_faculty (teacherprofile_id, faculty_id),
    FOREIGN KEY (teacherprofile_id) REFERENCES accounts_teacherprofile(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES structure_faculty(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS accounts_teachergroup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS accounts_teachergroup_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teachergroup_id INT NOT NULL,
    user_id INT NOT NULL,
    UNIQUE KEY teachergroup_user (teachergroup_id, user_id),
    FOREIGN KEY (teachergroup_id) REFERENCES accounts_teachergroup(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES auth_user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS accounts_teachergroupannouncement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_group_id INT NOT NULL,
    author_id INT NOT NULL,
    text TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_group_id) REFERENCES accounts_teachergroup(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES auth_user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Course
CREATE TABLE IF NOT EXISTS course_coursetype (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL UNIQUE,
    description TEXT NULL,
    `order` SMALLINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_coursetypesection (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_type_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    `order` SMALLINT NOT NULL DEFAULT 0,
    UNIQUE KEY course_type_name (course_type_id, name),
    FOREIGN KEY (course_type_id) REFERENCES course_coursetype(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_course (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    full_name VARCHAR(500) NOT NULL,
    short_name VARCHAR(100) NOT NULL,
    identifier VARCHAR(50) UNIQUE NULL,
    course_type_id INT NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    deleted_at DATETIME NULL,
    FOREIGN KEY (subject_id) REFERENCES subject_subject(id) ON DELETE CASCADE,
    FOREIGN KEY (course_type_id) REFERENCES course_coursetype(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_coursesection (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    `order` SMALLINT NOT NULL DEFAULT 0,
    visible TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY course_section (course_id, name),
    FOREIGN KEY (course_id) REFERENCES course_course(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_coursetopic (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_id INT NOT NULL,
    entity_title VARCHAR(100) NOT NULL,
    content VARCHAR(500) NOT NULL,
    `order` SMALLINT NOT NULL DEFAULT 0,
    visible TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (section_id) REFERENCES course_coursesection(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_learningunit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT NULL,
    section_id INT NULL,
    title VARCHAR(300) NOT NULL,
    content_type VARCHAR(30) NOT NULL DEFAULT 'methodical',
    file VARCHAR(500) NULL,
    `link` VARCHAR(500) NULL,
    `order` SMALLINT NOT NULL DEFAULT 0,
    visible TINYINT(1) NOT NULL DEFAULT 1,
    grading_type VARCHAR(20) NULL,
    test_id INT NULL,
    max_score INT NOT NULL DEFAULT 10,
    created_by_id INT NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    deleted_at DATETIME NULL,
    FOREIGN KEY (topic_id) REFERENCES course_coursetopic(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES course_coursesection(id) ON DELETE CASCADE,
    FOREIGN KEY (test_id) REFERENCES testing_test(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by_id) REFERENCES auth_user(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_courseuserpermission (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    permission VARCHAR(20) NOT NULL,
    UNIQUE KEY course_user (course_id, user_id),
    FOREIGN KEY (course_id) REFERENCES course_course(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES auth_user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_coursegrouppermission (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    group_id INT NOT NULL,
    permission VARCHAR(20) NOT NULL,
    UNIQUE KEY course_group (course_id, group_id),
    FOREIGN KEY (course_id) REFERENCES course_course(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES auth_group(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_coursegroupstudent (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    group_id INT NOT NULL,
    UNIQUE KEY course_group_student (course_id, group_id),
    FOREIGN KEY (course_id) REFERENCES course_course(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES students_studentgroup(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_studentanswer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    learning_unit_id INT NOT NULL,
    answer_file VARCHAR(500) NULL,
    answer_text TEXT NULL,
    checked TINYINT(1) NOT NULL DEFAULT 0,
    score INT NULL,
    passed TINYINT(1) NULL,
    comment TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    checked_at DATETIME NULL,
    checked_modified_at DATETIME NULL,
    UNIQUE KEY student_unit (student_id, learning_unit_id),
    FOREIGN KEY (student_id) REFERENCES students_student(id) ON DELETE CASCADE,
    FOREIGN KEY (learning_unit_id) REFERENCES course_learningunit(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_courseannouncement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    author_id INT NOT NULL,
    text TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES course_course(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES auth_user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_announcementdismiss (
    id INT AUTO_INCREMENT PRIMARY KEY,
    announcement_id INT NOT NULL,
    student_id INT NULL,
    user_id INT NULL,
    dismissed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY announcement_user (announcement_id, user_id),
    FOREIGN KEY (announcement_id) REFERENCES course_courseannouncement(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students_student(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES auth_user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_step (
    id INT AUTO_INCREMENT PRIMARY KEY,
    learning_unit_id INT NOT NULL,
    title VARCHAR(300) NOT NULL,
    content TEXT NULL,
    `order` SMALLINT NOT NULL DEFAULT 0,
    FOREIGN KEY (learning_unit_id) REFERENCES course_learningunit(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_stepquestion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    step_id INT NOT NULL,
    text TEXT NOT NULL,
    `order` SMALLINT NOT NULL DEFAULT 0,
    FOREIGN KEY (step_id) REFERENCES course_step(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_stepchoice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    text VARCHAR(500) NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES course_stepquestion(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_stepprogress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    step_id INT NOT NULL,
    completed TINYINT(1) NOT NULL DEFAULT 0,
    completed_at DATETIME NULL,
    answers JSON NULL,
    UNIQUE KEY student_step (student_id, step_id),
    FOREIGN KEY (student_id) REFERENCES students_student(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES course_step(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Testing
CREATE TABLE IF NOT EXISTS testing_test (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NOT NULL,
    subject_id INT NOT NULL,
    name VARCHAR(300) NOT NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES auth_user(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subject_subject(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS testing_question (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id INT NOT NULL,
    text TEXT NOT NULL,
    question_type VARCHAR(10) NOT NULL DEFAULT 'single',
    `order` SMALLINT NOT NULL DEFAULT 0,
    score INT NOT NULL DEFAULT 1,
    FOREIGN KEY (test_id) REFERENCES testing_test(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS testing_choice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    text VARCHAR(500) NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES testing_question(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS testing_deletedtest (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_id INT NOT NULL,
    author_id INT NOT NULL,
    author_name VARCHAR(300) NOT NULL,
    subject_id INT NOT NULL,
    subject_name VARCHAR(500) NOT NULL,
    name VARCHAR(300) NOT NULL,
    description TEXT NULL,
    export_data TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    deleted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Chat
CREATE TABLE IF NOT EXISTS chat_chatroom (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    deleted_at DATETIME NULL,
    UNIQUE KEY course_student (course_id, student_id),
    FOREIGN KEY (course_id) REFERENCES course_course(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students_student(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_groupchat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES students_studentgroup(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_groupchatmessage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    sender_student_id INT NOT NULL,
    text TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES chat_groupchat(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_student_id) REFERENCES students_student(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_chatmessage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    sender_student_id INT NULL,
    sender_user_id INT NULL,
    text TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES chat_chatroom(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_student_id) REFERENCES students_student(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_user_id) REFERENCES auth_user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
