<?php
// migration_add_lesson_sections.php
require 'bootstrap.php'; // your DB connection and autoload

$db = new PDO('mysql:host=localhost;dbname=codementordb;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Step 1: create lesson_sections table if it doesn't exist
$db->exec("
CREATE TABLE IF NOT EXISTS lesson_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT NOT NULL,
    subtitle VARCHAR(150) NOT NULL,
    content LONGTEXT NOT NULL,
    code_example TEXT NULL,
    example_id VARCHAR(50) NULL,
    order_index INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_section_lesson FOREIGN KEY (lesson_id)
        REFERENCES lessons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

echo "lesson_sections table ready.\n";

// Step 2: fetch all lessons
$stmt = $db->query("SELECT * FROM lessons");
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($lessons as $lesson) {
    $insert = $db->prepare("
        INSERT INTO lesson_sections 
        (lesson_id, subtitle, content, code_example, example_id, order_index, created_at, updated_at)
        VALUES (:lesson_id, :subtitle, :content, :code_example, :example_id, :order_index, NOW(), NOW())
    ");

    $insert->execute([
        ':lesson_id'   => $lesson['id'],
        ':subtitle'    => $lesson['title'],
        ':content'     => $lesson['content'] ?? '',
        ':code_example'=> $lesson['code_example'] ?? null,
        ':example_id'  => $lesson['example_id'] ?? null,
        ':order_index' => $lesson['order_index'] ?? 0,
    ]);

    echo "Migrated lesson '{$lesson['title']}' -> section.\n";
}

echo "All lessons migrated to lesson_sections.\n";
