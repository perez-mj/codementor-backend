-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 22, 2025 at 03:44 AM
-- Server version: 9.1.0
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `codementordb`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

DROP TABLE IF EXISTS `achievements`;
CREATE TABLE IF NOT EXISTS `achievements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `criteria` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Algorithms', 'Challenges focused on efficient problem-solving logic and data manipulation.'),
(2, 'Data Structures', 'Challenges focused on implementing and utilizing structures like trees, graphs, and lists.'),
(3, 'Web Development', 'Challenges focused on building front-end and back-end web components.');

-- --------------------------------------------------------

--
-- Table structure for table `challenges`
--

DROP TABLE IF EXISTS `challenges`;
CREATE TABLE IF NOT EXISTS `challenges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `difficulty` enum('Easy','Medium','Hard') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `xp_reward` int NOT NULL,
  `solved_count` int NOT NULL DEFAULT '0',
  `category_id` int NOT NULL,
  `created_by` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `challenges`
--

INSERT INTO `challenges` (`id`, `title`, `slug`, `description`, `difficulty`, `xp_reward`, `solved_count`, `category_id`, `created_by`) VALUES
(1, 'Sum of Two Numbers', 'Sum-of-Two-Numbers', 'Write a function that returns the sum of any two integers.', 'Easy', 10, 0, 1, 1),
(2, 'Implement a Stack', 'Implement-a-Stack', 'Create a Stack class using a dynamically sized array.', 'Medium', 50, 0, 2, 1),
(3, 'Build a RESTful API', 'Build-a-RESTful-API', 'Design and implement a fully functioning REST API for a blog application.', 'Hard', 150, 0, 3, 1),
(4, 'Fibonacci Sequence', 'Fibonacci-Sequence', 'Generate the Nth number in the Fibonacci sequence efficiently.', 'Medium', 75, 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `challenge_test_cases`
--

DROP TABLE IF EXISTS `challenge_test_cases`;
CREATE TABLE IF NOT EXISTS `challenge_test_cases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `challenge_id` int NOT NULL,
  `input` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expected_output` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_example` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `challenge_id` (`challenge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
CREATE TABLE IF NOT EXISTS `languages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `slug_2` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Learn JavaScript', 'javascript', NULL),
(2, 'Learn Python', 'python', NULL),
(3, 'Learn PHP', 'php', NULL),
(4, 'Learn Java', 'java', NULL),
(5, 'Learn C#', 'csharp', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard_scores`
--

DROP TABLE IF EXISTS `leaderboard_scores`;
CREATE TABLE IF NOT EXISTS `leaderboard_scores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `score_type` enum('TotalXP','ChallengesSolved') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `score_value` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
CREATE TABLE IF NOT EXISTS `lessons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `language_id` int NOT NULL,
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `order_index` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `language_id` (`language_id`,`slug`),
  KEY `language_id_2` (`language_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `language_id`, `title`, `description`, `slug`, `content`, `order_index`, `created_at`, `updated_at`) VALUES
(1, 1, 'JavaScript Introduction', 'Learn what JavaScript is and why it is used.', 'intro', 'JavaScript is a lightweight, interpreted language that powers interactive websites.', 1, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(2, 1, 'JavaScript Variables', 'Understanding variables and data types.', 'variables', 'Variables store data values. Use let, const, or var.', 2, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(3, 1, 'JavaScript Loops', 'Learn how to repeat actions with loops.', 'loops', 'Loops let you repeat actions until a condition is met.', 3, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(4, 2, 'Python Introduction', 'Start learning Python basics.', 'intro', 'Python is known for its simplicity and readability.', 1, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(5, 2, 'Python Variables', 'How to store values in Python.', 'variables', 'Variables hold data. No need to declare types.', 2, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(6, 2, 'Python Loops', 'Using loops for repetition.', 'loops', 'For loops iterate over sequences.', 3, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(7, 3, 'PHP Introduction', 'Get started with PHP for backend web development.', 'intro', 'PHP is a server-side scripting language used to build dynamic web applications.', 1, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(8, 3, 'PHP Variables', 'Learn about variables in PHP.', 'variables', 'Variables in PHP start with a dollar sign ($).', 2, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(9, 3, 'PHP Loops', 'Execute repetitive tasks easily.', 'loops', 'PHP supports for, while, and foreach loops.', 3, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(10, 4, 'Java Introduction', 'Learn the basics of Java programming.', 'intro', 'Java is a class-based, object-oriented programming language.', 1, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(11, 4, 'Java Variables', 'Declaring variables in Java.', 'variables', 'Java is statically typed — you must specify a data type.', 2, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(12, 4, 'Java Loops', 'Repeat tasks using loops.', 'loops', 'Loops execute a block of code multiple times.', 3, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(13, 5, 'C# Introduction', 'Get started with C# programming.', 'intro', 'C# is a modern, object-oriented language developed by Microsoft.', 1, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(14, 5, 'C# Variables', 'Learn about variables in C#.', 'variables', 'Variables in C# are strongly typed.', 2, '2025-11-04 15:42:36', '2025-11-04 15:42:36'),
(15, 5, 'C# Loops', 'Learn how to repeat actions using loops.', 'loops', 'C# supports for, while, and foreach loops.', 3, '2025-11-04 15:42:36', '2025-11-04 15:42:36');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_sections`
--

DROP TABLE IF EXISTS `lesson_sections`;
CREATE TABLE IF NOT EXISTS `lesson_sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lesson_id` int DEFAULT NULL,
  `subtitle` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `code_example` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `example_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `order_index` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_section_lesson` (`lesson_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lesson_sections`
--

INSERT INTO `lesson_sections` (`id`, `lesson_id`, `subtitle`, `content`, `code_example`, `example_id`, `order_index`, `created_at`, `updated_at`) VALUES
(1, 1, 'JavaScript Introduction', 'JavaScript is a lightweight, interpreted language that powers interactive websites.', 'console.log(\"Hello, World!\");', 'js_intro_1', 1, '2025-11-05 06:28:05', '2025-11-05 06:28:05'),
(2, 2, 'JavaScript Variables', 'Variables store data values. Use let, const, or var.', 'let name = \"Mark\"; console.log(name);', 'js_variables_1', 2, '2025-11-05 06:28:05', '2025-11-05 06:28:05'),
(3, 3, 'JavaScript Loops', 'Loops let you repeat actions until a condition is met.', 'for (let i = 0; i < 5; i++) { console.log(i); }', 'js_loops_1', 3, '2025-11-05 06:28:05', '2025-11-05 06:28:05'),
(4, 4, 'Python Introduction', 'Python is known for its simplicity and readability.', 'print(\"Hello, World!\")', 'py_intro_1', 1, '2025-11-05 06:28:05', '2025-11-05 06:28:05'),
(5, 5, 'Python Variables', 'Variables hold data. No need to declare types.', 'name = \"Mark\"\nprint(name)', 'py_variables_1', 2, '2025-11-05 06:28:05', '2025-11-05 06:28:05'),
(6, 6, 'Python Loops', 'For loops iterate over sequences.', 'for i in range(5):\n    print(i)', 'py_loops_1', 3, '2025-11-05 06:28:05', '2025-11-05 06:28:05'),
(10, 10, 'Java Introduction', 'Java is a class-based, object-oriented programming language.', 'public class Main {\n  public static void main(String[] args) {\n    System.out.println(\"Hello, World!\");\n  }\n}', 'java_intro_1', 1, '2025-11-05 06:28:05', '2025-11-05 06:28:05'),
(11, 11, 'Java Variables', 'Java is statically typed — you must specify a data type.', 'int age = 25; System.out.println(age);', 'java_variables_1', 2, '2025-11-05 06:28:05', '2025-11-05 06:28:05'),
(12, 12, 'Java Loops', 'Loops execute a block of code multiple times.', 'for (int i = 0; i < 5; i++) {\n  System.out.println(i);\n}', 'java_loops_1', 3, '2025-11-05 06:28:05', '2025-11-05 06:28:05'),
(13, 13, 'C# Introduction', 'C# is a modern, object-oriented language developed by Microsoft.', 'Console.WriteLine(\"Hello, World!\");', 'cs_intro_1', 1, '2025-11-05 06:28:05', '2025-11-05 06:28:05'),
(14, 14, 'C# Variables', 'Variables in C# are strongly typed.', 'string name = \"Mark\"; Console.WriteLine(name);', 'cs_variables_1', 2, '2025-11-05 06:28:05', '2025-11-05 06:28:05'),
(15, 15, 'C# Loops', 'C# supports for, while, and foreach loops.', 'for (int i = 0; i < 5; i++) { Console.WriteLine(i); }', 'cs_loops_1', 3, '2025-11-05 06:28:05', '2025-11-05 06:28:05'),
(16, 7, 'What is PHP?', 'PHP stands for Hypertext Preprocessor. It is a widely-used, open source scripting language used for web development.', NULL, NULL, 1, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(17, 7, 'Basic Syntax', 'PHP code is executed on the server and the result is returned to the browser as plain HTML. All PHP code must start with <?php and end with ?>.', '<?php echo \"Hello World!\"; ?>', NULL, 2, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(18, 7, 'Case Sensitivity', 'In PHP, keywords (like `if`, `else`, `while`, `echo`) are NOT case-sensitive, but all variable names ARE case-sensitive.', NULL, NULL, 3, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(19, 7, 'Comments', 'Comments in PHP can be single-line (`//` or `#`) or multi-line (`/* ... */`). They are ignored by the parser.', '<?php // Single-line comment\n/* Multi-line\n   comment */ ?>', NULL, 4, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(20, 7, 'The `echo` Statement', 'The `echo` statement is used to output data to the screen. It can output one or more strings.', '<?php echo \"PHP is fun!\"; ?>', NULL, 5, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(21, 8, 'Variable Declaration', 'Variables are used to store information, like strings, numbers, or arrays. In PHP, a variable starts with the $ sign, followed by the name of the variable.', '<?php $name = \"Alex\"; $age = 30; ?>', 'var_declaration_1', 1, '2025-11-05 06:52:23', '2025-11-05 07:07:00'),
(22, 8, 'Variable Naming Rules', 'A variable name must start with a letter or the underscore character. It cannot start with a number.', NULL, NULL, 2, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(23, 8, 'Assigning Values', 'Values are assigned using the assignment operator (`=`). PHP is a loosely typed language, meaning data types do not need to be declared.', '<?php $price = 19.99; ?>', NULL, 3, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(24, 8, 'Strings', 'A string is a sequence of characters, and can be enclosed in single or double quotes.', '<?php $greeting = \"Hello\"; ?>', NULL, 4, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(25, 8, 'Integers and Floats', 'Integers are non-decimal numbers, and Floats are numbers with a decimal point.', '<?php $count = 10; $pi = 3.14; ?>', NULL, 5, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(26, 8, 'Booleans', 'Booleans represent two possible states: TRUE or FALSE. They are often used in conditional testing.', '<?php $is_admin = TRUE; ?>', NULL, 6, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(27, 8, 'Variable Scope - Local', 'A variable declared within a function has a local scope and can only be accessed within that function.', NULL, NULL, 7, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(28, 8, 'Variable Scope - Global', 'Variables declared outside a function have a global scope and can only be accessed outside a function.', NULL, NULL, 8, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(29, 8, 'The `global` Keyword', 'The `global` keyword is used to access a global variable from within a function.', '<?php $x=5; function test() { global $x; echo $x; } ?>', NULL, 9, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(30, 9, 'What are Loops?', 'Loops are used to execute a block of code repeatedly as long as a specified condition is met.', NULL, NULL, 1, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(31, 9, 'The `while` Loop', 'The `while` loop executes a block of code as long as the specified condition is true. It checks the condition BEFORE executing the block.', '<?php $i = 1; while ($i <= 5) { echo $i++; } ?>', NULL, 2, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(32, 9, 'The `do-while` Loop', 'The `do-while` loop will always execute the block of code once, then check the condition, and repeat the loop as long as the condition is true.', '<?php $j = 1; do { echo $j++; } while ($j <= 5); ?>', NULL, 3, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(33, 9, 'The `for` Loop', 'The `for` loop is used when you know in advance how many times the script should run.', '<?php for ($k = 0; $k < 10; $k++) { echo $k; } ?>', NULL, 4, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(34, 9, 'The `foreach` Loop', 'The `foreach` loop is used specifically to loop through arrays.', '<?php $arr = [\"a\", \"b\"]; foreach ($arr as $val) { echo $val; } ?>', NULL, 5, '2025-11-05 06:52:23', '2025-11-05 06:52:23'),
(35, 9, 'Loop Control - `break`', 'The `break` statement is used to immediately exit a loop.', '<?php for ($l = 1; $l < 10; $l++) { if ($l == 4) break; echo $l; } ?>', NULL, 6, '2025-11-05 06:52:23', '2025-11-05 06:52:23');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_views`
--

DROP TABLE IF EXISTS `lesson_views`;
CREATE TABLE IF NOT EXISTS `lesson_views` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `lesson_id` int NOT NULL,
  `viewed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`) VALUES
(2, '1');

-- --------------------------------------------------------

--
-- Table structure for table `refresh_tokens`
--

DROP TABLE IF EXISTS `refresh_tokens`;
CREATE TABLE IF NOT EXISTS `refresh_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refresh_tokens`
--

INSERT INTO `refresh_tokens` (`id`, `user_id`, `token`, `expires_at`) VALUES
(2, 1, 'W+usnXtFovOyE3P6iO9SeYabFNKT9p7YLdyDI5uhnMjPF8W0ggghnmYqYL1j6VV27NkP54wkYh0sxOEGgvv3NoJcX5PWOCH6M0LoE5M+4xTPSq8mNpw8pMDR3mcKY5CXqasjceZ3P/xeMEzNUup4ohryzhTR7rrNqN3InYKul16s8YtOF+N+kV2YJIHLXgtp3j16INkym2GTIryMsmVOUiCc483pJ16ObWu0PHrofwo=', '2025-11-27 12:05:05');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

DROP TABLE IF EXISTS `submissions`;
CREATE TABLE IF NOT EXISTS `submissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `challenge_id` int NOT NULL,
  `code_content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `language` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Passed','Failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `execution_time` float DEFAULT NULL,
  `memory_used` int DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `challenge_id` (`challenge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('user','moderator','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `rank` int DEFAULT '0',
  `total_points` int DEFAULT '0',
  `email_verified` tinyint(1) DEFAULT '0',
  `verification_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `joined_at`, `last_login_at`, `rank`, `total_points`, `email_verified`, `verification_token`, `token_expires_at`) VALUES
(1, 'sadistcoder', 'sadistcoder@cm.com', '$2y$10$18V/3LL9ONBatTqjD/XqV.Tyrm5cw5gYzXI/k4GWwLfqr4Mpu1fG6', 'admin', '2025-10-21 10:50:33', NULL, 1, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_achievements`
--

DROP TABLE IF EXISTS `user_achievements`;
CREATE TABLE IF NOT EXISTS `user_achievements` (
  `user_id` int NOT NULL,
  `achievement_id` int NOT NULL,
  `awarded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`achievement_id`),
  KEY `achievement_id` (`achievement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_challenge_status`
--

DROP TABLE IF EXISTS `user_challenge_status`;
CREATE TABLE IF NOT EXISTS `user_challenge_status` (
  `user_id` int NOT NULL,
  `challenge_id` int NOT NULL,
  `is_solved` tinyint(1) NOT NULL DEFAULT '0',
  `solved_at` timestamp NULL DEFAULT NULL,
  `last_submitted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`,`challenge_id`),
  KEY `challenge_id` (`challenge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_lesson_progress`
--

DROP TABLE IF EXISTS `user_lesson_progress`;
CREATE TABLE IF NOT EXISTS `user_lesson_progress` (
  `user_id` int NOT NULL,
  `lesson_id` int NOT NULL,
  `completed` tinyint(1) DEFAULT '0',
  `time_spent` int DEFAULT '0',
  `last_accessed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`lesson_id`),
  KEY `lesson_id` (`lesson_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

DROP TABLE IF EXISTS `user_settings`;
CREATE TABLE IF NOT EXISTS `user_settings` (
  `user_id` int NOT NULL,
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`user_id`,`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_stats`
--

DROP TABLE IF EXISTS `user_stats`;
CREATE TABLE IF NOT EXISTS `user_stats` (
  `user_id` int NOT NULL,
  `xp` int DEFAULT '0',
  `current_streak` int DEFAULT '0',
  `longest_streak` int DEFAULT '0',
  `total_submissions` int DEFAULT '0',
  `challenges_solved` int DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `challenges`
--
ALTER TABLE `challenges`
  ADD CONSTRAINT `challenges_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `challenges_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `challenge_test_cases`
--
ALTER TABLE `challenge_test_cases`
  ADD CONSTRAINT `challenge_test_cases_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`);

--
-- Constraints for table `leaderboard_scores`
--
ALTER TABLE `leaderboard_scores`
  ADD CONSTRAINT `leaderboard_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `fk_lessons_language` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lesson_sections`
--
ALTER TABLE `lesson_sections`
  ADD CONSTRAINT `fk_section_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD CONSTRAINT `refresh_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`);

--
-- Constraints for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`);

--
-- Constraints for table `user_challenge_status`
--
ALTER TABLE `user_challenge_status`
  ADD CONSTRAINT `user_challenge_status_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_challenge_status_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`);

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_stats`
--
ALTER TABLE `user_stats`
  ADD CONSTRAINT `user_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
