-- Create questions table
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    question TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX (created_at)
);

-- Create answers table
CREATE TABLE IF NOT EXISTS answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    INDEX (question_id),
    INDEX (created_at)
); 