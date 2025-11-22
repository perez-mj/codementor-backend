-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 22, 2025 at 01:52 PM
-- Server version: 8.0.44-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

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

CREATE TABLE `achievements` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `criteria` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_interactions`
--

CREATE TABLE `ai_interactions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `user_message` text NOT NULL,
  `ai_response` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Algorithms', 'Challenges focused on efficient problem-solving logic and data manipulation.', '2025-11-21 14:36:58', '2025-11-21 14:36:58'),
(2, 'Data Structures', 'Challenges focused on implementing and utilizing structures like trees, graphs, and lists.', '2025-11-21 14:36:58', '2025-11-21 14:36:58'),
(3, 'Web Development', 'Challenges focused on building front-end and back-end web components.', '2025-11-21 14:36:58', '2025-11-21 14:36:58'),
(4, 'Database', 'Challenges focused on SQL queries and database design.', '2025-11-21 14:36:58', '2025-11-21 14:36:58'),
(5, 'Security', 'Challenges focused on cybersecurity and vulnerability detection.', '2025-11-21 14:36:58', '2025-11-21 14:36:58');

-- --------------------------------------------------------

--
-- Table structure for table `challenges`
--

CREATE TABLE `challenges` (
  `id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `difficulty` enum('Easy','Medium','Hard') COLLATE utf8mb4_general_ci NOT NULL,
  `xp_reward` int NOT NULL,
  `time_limit` varchar(10) COLLATE utf8mb4_general_ci DEFAULT '1s',
  `memory_limit` varchar(20) COLLATE utf8mb4_general_ci DEFAULT '64MB',
  `solved_count` int NOT NULL DEFAULT '0',
  `total_submissions` int NOT NULL DEFAULT '0',
  `accepted_submissions` int NOT NULL DEFAULT '0',
  `category_id` int NOT NULL,
  `created_by` int NOT NULL,
  `is_published` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `challenges`
--

INSERT INTO `challenges` (`id`, `title`, `slug`, `description`, `difficulty`, `xp_reward`, `time_limit`, `memory_limit`, `solved_count`, `total_submissions`, `accepted_submissions`, `category_id`, `created_by`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'Sum of Two Numbers', 'sum-of-two-numbers', 'Write a function that returns the sum of any two integers.', 'Easy', 10, '1s', '64MB', 0, 0, 0, 1, 1, 1, '2025-11-21 14:36:58', '2025-11-21 14:36:58'),
(2, 'Implement a Stack', 'implement-a-stack', 'Create a Stack class using a dynamically sized array with push, pop, and peek operations.', 'Medium', 50, '2s', '128MB', 0, 0, 0, 2, 1, 1, '2025-11-21 14:36:58', '2025-11-21 14:36:58'),
(3, 'Build a RESTful API', 'build-a-restful-api', 'Design and implement a fully functioning REST API for a blog application with CRUD operations.', 'Hard', 150, '5s', '256MB', 0, 0, 0, 3, 1, 1, '2025-11-21 14:36:58', '2025-11-21 14:36:58'),
(4, 'Fibonacci Sequence', 'fibonacci-sequence', 'Generate the Nth number in the Fibonacci sequence efficiently using memoization or iterative approach.', 'Medium', 75, '2s', '128MB', 0, 0, 0, 1, 1, 1, '2025-11-21 14:36:58', '2025-11-21 14:36:58'),
(5, 'Palindrome Checker', 'palindrome-checker', 'Write a function that checks if a given string is a palindrome (reads the same forwards and backwards).', 'Easy', 15, '1s', '64MB', 0, 0, 0, 1, 1, 1, '2025-11-21 14:36:58', '2025-11-21 14:36:58');

-- --------------------------------------------------------

--
-- Table structure for table `challenge_hints`
--

CREATE TABLE `challenge_hints` (
  `id` int NOT NULL,
  `challenge_id` int NOT NULL,
  `hint_text` text COLLATE utf8mb4_general_ci NOT NULL,
  `order_index` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `challenge_hints`
--

INSERT INTO `challenge_hints` (`id`, `challenge_id`, `hint_text`, `order_index`, `created_at`) VALUES
(1, 1, 'Remember that addition is commutative - the order of numbers doesn\'t matter.', 1, '2025-11-21 14:36:58'),
(2, 1, 'Consider edge cases like negative numbers and zero.', 2, '2025-11-21 14:36:58'),
(3, 4, 'The Fibonacci sequence starts with 0 and 1, and each subsequent number is the sum of the previous two.', 1, '2025-11-21 14:36:58'),
(4, 4, 'For large values of N, consider using memoization to avoid redundant calculations.', 2, '2025-11-21 14:36:58');

-- --------------------------------------------------------

--
-- Table structure for table `challenge_tags`
--

CREATE TABLE `challenge_tags` (
  `id` int NOT NULL,
  `challenge_id` int NOT NULL,
  `tag_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `challenge_tags`
--

INSERT INTO `challenge_tags` (`id`, `challenge_id`, `tag_name`) VALUES
(1, 1, 'math'),
(2, 1, 'basic'),
(3, 1, 'arithmetic'),
(4, 2, 'stack'),
(5, 2, 'data-structures'),
(6, 2, 'arrays'),
(7, 4, 'recursion'),
(8, 4, 'dynamic-programming'),
(9, 4, 'sequences');

-- --------------------------------------------------------

--
-- Table structure for table `challenge_test_cases`
--

CREATE TABLE `challenge_test_cases` (
  `id` int NOT NULL,
  `challenge_id` int NOT NULL,
  `input` text COLLATE utf8mb4_general_ci NOT NULL,
  `expected_output` text COLLATE utf8mb4_general_ci NOT NULL,
  `is_example` tinyint(1) DEFAULT '0',
  `is_visible` tinyint(1) DEFAULT '1',
  `order_index` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `challenge_test_cases`
--

INSERT INTO `challenge_test_cases` (`id`, `challenge_id`, `input`, `expected_output`, `is_example`, `is_visible`, `order_index`, `created_at`) VALUES
(1, 1, '5, 3', '8', 1, 1, 1, '2025-11-21 14:36:58'),
(2, 1, '-2, 7', '5', 1, 1, 2, '2025-11-21 14:36:58'),
(3, 1, '0, 0', '0', 0, 1, 3, '2025-11-21 14:36:58'),
(4, 1, '-5, -3', '-8', 0, 1, 4, '2025-11-21 14:36:58'),
(5, 1, '100, -50', '50', 0, 1, 5, '2025-11-21 14:36:58'),
(6, 4, '0', '0', 1, 1, 1, '2025-11-21 14:36:58'),
(7, 4, '1', '1', 1, 1, 2, '2025-11-21 14:36:58'),
(8, 4, '5', '5', 1, 1, 3, '2025-11-21 14:36:58'),
(9, 4, '10', '55', 0, 1, 4, '2025-11-21 14:36:58'),
(10, 4, '15', '610', 0, 1, 5, '2025-11-21 14:36:58');

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `leaderboard_scores` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `score_type` enum('TotalXP','ChallengesSolved') COLLATE utf8mb4_general_ci NOT NULL,
  `score_value` int NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `learning_paths`
--

CREATE TABLE `learning_paths` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `total_lessons` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `learning_paths`
--

INSERT INTO `learning_paths` (`id`, `name`, `description`, `total_lessons`, `created_at`) VALUES
(1, 'JavaScript Fundamentals', 'Learn core JavaScript concepts from basics to advanced', 15, '2025-11-22 12:08:56'),
(2, 'Web Development', 'Full-stack web development with modern technologies', 20, '2025-11-22 12:08:56'),
(3, 'Data Structures & Algorithms', 'Master computer science fundamentals', 25, '2025-11-22 12:08:56');

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int NOT NULL,
  `language_id` int NOT NULL,
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `order_index` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `lesson_sections` (
  `id` int NOT NULL,
  `lesson_id` int DEFAULT NULL,
  `subtitle` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `code_example` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `example_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `order_index` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`) VALUES
(2, '1');

-- --------------------------------------------------------

--
-- Table structure for table `refresh_tokens`
--

CREATE TABLE `refresh_tokens` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refresh_tokens`
--

INSERT INTO `refresh_tokens` (`id`, `user_id`, `token`, `expires_at`) VALUES
(2, 1, 'W+usnXtFovOyE3P6iO9SeYabFNKT9p7YLdyDI5uhnMjPF8W0ggghnmYqYL1j6VV27NkP54wkYh0sxOEGgvv3NoJcX5PWOCH6M0LoE5M+4xTPSq8mNpw8pMDR3mcKY5CXqasjceZ3P/xeMEzNUup4ohryzhTR7rrNqN3InYKul16s8YtOF+N+kV2YJIHLXgtp3j16INkym2GTIryMsmVOUiCc483pJ16ObWu0PHrofwo=', '2025-11-27 12:05:05'),
(3, 1, 'W+usnXtFovOyE3P6iO9SeYabFNKT9p7YLdyDI5uhnMjPF8W0ggghnmYqYL1j6VV27NkP54wkYh0sxOEGgvv3NoJcX5PWOCH6M0LoE5M+4xSgfuX4qzwO1333enqrQVvEa/CfC+5rkqkVILzUtgeAkFGqbBcKnx8XzBMQ/eUn8jJb/rpdLDmdUaGWqQ/u44iT++he2Ae8DrmZdw6/2rJBI60gzOfU13+p2asf1tST6nc=', '2025-11-29 11:47:31');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `challenge_id` int NOT NULL,
  `code_content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `language` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('Passed','Failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `execution_time` float DEFAULT NULL,
  `memory_used` int DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('user','moderator','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_status` enum('active','suspended','banned','pending') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `rank` int DEFAULT '0',
  `total_points` int DEFAULT '0',
  `email_verified` tinyint(1) DEFAULT '0',
  `verification_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password_hash`, `role`, `account_status`, `joined_at`, `updated_at`, `last_login_at`, `rank`, `total_points`, `email_verified`, `verification_token`, `token_expires_at`) VALUES
(1, 'sadistcoder', NULL, 'sadistcoder@cm.com', '$2y$10$18V/3LL9ONBatTqjD/XqV.Tyrm5cw5gYzXI/k4GWwLfqr4Mpu1fG6', 'admin', 'pending', '2025-10-21 10:50:33', '2025-11-22 12:08:56', NULL, 1, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_achievements`
--

CREATE TABLE `user_achievements` (
  `user_id` int NOT NULL,
  `achievement_id` int NOT NULL,
  `awarded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_challenge_status`
--

CREATE TABLE `user_challenge_status` (
  `user_id` int NOT NULL,
  `challenge_id` int NOT NULL,
  `is_solved` tinyint(1) NOT NULL DEFAULT '0',
  `attempts` int NOT NULL DEFAULT '0',
  `solved_at` timestamp NULL DEFAULT NULL,
  `last_submitted_at` timestamp NULL DEFAULT NULL,
  `best_execution_time` float DEFAULT NULL,
  `best_memory_used` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_learning_paths`
--

CREATE TABLE `user_learning_paths` (
  `user_id` int NOT NULL,
  `path_id` int NOT NULL,
  `progress_percentage` decimal(5,2) DEFAULT '0.00',
  `completed_lessons` int DEFAULT '0',
  `current_lesson_id` int DEFAULT NULL,
  `enrolled_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

CREATE TABLE `user_progress` (
  `user_id` int NOT NULL,
  `total_lessons_completed` int DEFAULT '0',
  `current_lesson_id` int DEFAULT NULL,
  `percent_completion` decimal(5,2) DEFAULT '0.00',
  `time_spent` int DEFAULT '0' COMMENT 'in seconds',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `user_id` int NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_stats`
--

CREATE TABLE `user_stats` (
  `user_id` int NOT NULL,
  `xp` int DEFAULT '0',
  `current_streak` int DEFAULT '0',
  `longest_streak` int DEFAULT '0',
  `total_submissions` int DEFAULT '0',
  `challenges_solved` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `ai_interactions`
--
ALTER TABLE `ai_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id_created_at` (`user_id`,`created_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `difficulty` (`difficulty`),
  ADD KEY `is_published` (`is_published`);

--
-- Indexes for table `challenge_hints`
--
ALTER TABLE `challenge_hints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indexes for table `challenge_tags`
--
ALTER TABLE `challenge_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `challenge_id` (`challenge_id`),
  ADD KEY `tag_name` (`tag_name`);

--
-- Indexes for table `challenge_test_cases`
--
ALTER TABLE `challenge_test_cases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `challenge_id` (`challenge_id`),
  ADD KEY `is_example` (`is_example`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `slug_2` (`slug`);

--
-- Indexes for table `leaderboard_scores`
--
ALTER TABLE `leaderboard_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `learning_paths`
--
ALTER TABLE `learning_paths`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `language_id` (`language_id`,`slug`),
  ADD KEY `language_id_2` (`language_id`);

--
-- Indexes for table `lesson_sections`
--
ALTER TABLE `lesson_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_section_lesson` (`lesson_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`user_id`,`achievement_id`),
  ADD KEY `achievement_id` (`achievement_id`);

--
-- Indexes for table `user_challenge_status`
--
ALTER TABLE `user_challenge_status`
  ADD PRIMARY KEY (`user_id`,`challenge_id`),
  ADD KEY `challenge_id` (`challenge_id`),
  ADD KEY `is_solved` (`is_solved`),
  ADD KEY `solved_at` (`solved_at`);

--
-- Indexes for table `user_learning_paths`
--
ALTER TABLE `user_learning_paths`
  ADD PRIMARY KEY (`user_id`,`path_id`),
  ADD KEY `path_id` (`path_id`),
  ADD KEY `current_lesson_id` (`current_lesson_id`);

--
-- Indexes for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `current_lesson_id` (`current_lesson_id`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`user_id`,`setting_key`);

--
-- Indexes for table `user_stats`
--
ALTER TABLE `user_stats`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_interactions`
--
ALTER TABLE `ai_interactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `challenges`
--
ALTER TABLE `challenges`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `challenge_hints`
--
ALTER TABLE `challenge_hints`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `challenge_tags`
--
ALTER TABLE `challenge_tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `challenge_test_cases`
--
ALTER TABLE `challenge_test_cases`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `leaderboard_scores`
--
ALTER TABLE `leaderboard_scores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `learning_paths`
--
ALTER TABLE `learning_paths`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `lesson_sections`
--
ALTER TABLE `lesson_sections`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_interactions`
--
ALTER TABLE `ai_interactions`
  ADD CONSTRAINT `ai_interactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `challenges`
--
ALTER TABLE `challenges`
  ADD CONSTRAINT `challenges_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `challenges_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `challenge_hints`
--
ALTER TABLE `challenge_hints`
  ADD CONSTRAINT `challenge_hints_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `challenge_tags`
--
ALTER TABLE `challenge_tags`
  ADD CONSTRAINT `challenge_tags_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `challenge_test_cases`
--
ALTER TABLE `challenge_test_cases`
  ADD CONSTRAINT `challenge_test_cases_ibfk_1` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `user_challenge_status_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_challenge_status_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_learning_paths`
--
ALTER TABLE `user_learning_paths`
  ADD CONSTRAINT `user_learning_paths_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_learning_paths_ibfk_2` FOREIGN KEY (`path_id`) REFERENCES `learning_paths` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_learning_paths_ibfk_3` FOREIGN KEY (`current_lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`current_lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL;

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
