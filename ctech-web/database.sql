-- Drop the database if it exists (with new name)
DROP DATABASE IF EXISTS career_tech_db;

-- Create the database with new name
CREATE DATABASE career_tech_db;

-- Use the new database
USE career_tech_db;

-- Create the career_profiles table
CREATE TABLE career_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    skills TEXT NOT NULL,
    education TEXT NOT NULL,
    salary_range VARCHAR(255) NOT NULL,
    job_outlook TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Create inspiring_stories table
CREATE TABLE inspiring_stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(255) NOT NULL,
    company VARCHAR(255) NOT NULL,
    image_path VARCHAR(255),
    short_quote TEXT NOT NULL,
    full_story TEXT NOT NULL,
    audio_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create story_careers table (for many-to-many relationship)
CREATE TABLE  story_careers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    story_id INT NOT NULL,
    career_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (story_id) REFERENCES inspiring_stories(id) ON DELETE CASCADE,
    FOREIGN KEY (career_id) REFERENCES career_profiles(id) ON DELETE CASCADE
);

-- Insert sample career profiles
INSERT INTO career_profiles (title, description, skills, education, salary_range, job_outlook) VALUES
('Software Developer', 'Develops and maintains software applications', 'Programming, Problem Solving, Teamwork', 'Bachelor\'s in Computer Science', 'GHC 3,000 - GHC 8,000', 'High demand in Ghana'),
('Data Scientist', 'Analyzes complex data to help organizations make better decisions', 'Statistics, Machine Learning, Python', 'Master\'s in Data Science', 'GHC 4,000 - GHC 10,000', 'Growing field in Ghana'),
('UI/UX Designer', 'Creates user-friendly interfaces and experiences', 'Design, User Research, Prototyping', 'Bachelor\'s in Design', 'GHC 2,500 - GHC 7,000', 'Increasing demand'),
('Network Engineer', 'Designs and maintains computer networks', 'Networking, Security, Troubleshooting', 'Bachelor\'s in IT', 'GHC 3,500 - GHC 9,000', 'Stable growth'),
('AI Engineer', 'Develops artificial intelligence systems and applications', 'Machine Learning, Python, TensorFlow', 'Master\'s in AI/ML', 'GHC 5,000 - GHC 12,000', 'Emerging field'),
('Cloud Architect', 'Designs and manages cloud infrastructure', 'AWS, Azure, Cloud Security', 'Bachelor\'s in CS/IT', 'GHC 4,500 - GHC 11,000', 'High growth'),
('Cybersecurity Analyst', 'Protects systems from cyber threats', 'Security, Risk Assessment, Ethical Hacking', 'Bachelor\'s in Cybersecurity', 'GHC 4,000 - GHC 10,000', 'Critical need'),
('DevOps Engineer', 'Bridges development and operations', 'CI/CD, Docker, Kubernetes', 'Bachelor\'s in CS/IT', 'GHC 4,000 - GHC 10,000', 'Growing demand'),
('Game Developer', 'Creates video games and interactive experiences', 'Unity, C#, Game Design', 'Bachelor\'s in Game Development', 'GHC 3,000 - GHC 8,000', 'Creative industry'),
('Mobile App Developer', 'Develops applications for mobile devices', 'Flutter, React Native, Swift', 'Bachelor\'s in CS/Mobile Development', 'GHC 3,500 - GHC 9,000', 'High demand'),
('Blockchain Developer', 'Builds decentralized applications', 'Solidity, Ethereum, Smart Contracts', 'Bachelor\'s in CS/Blockchain', 'GHC 5,000 - GHC 12,000', 'Emerging field'),
('Database Administrator', 'Manages and maintains databases', 'SQL, Database Design, Performance Tuning', 'Bachelor\'s in CS/IT', 'GHC 3,500 - GHC 9,000', 'Stable growth'),
('IT Project Manager', 'Leads technology projects', 'Project Management, Agile, Leadership', 'Bachelor\'s in CS/IT + PMP', 'GHC 4,500 - GHC 11,000', 'Leadership role'),
('Technical Writer', 'Creates technical documentation', 'Writing, Technical Communication, Research', 'Bachelor\'s in Technical Writing', 'GHC 2,500 - GHC 7,000', 'Support role'),
('Quality Assurance Engineer', 'Tests software for quality', 'Testing, Automation, Bug Tracking', 'Bachelor\'s in CS/IT', 'GHC 3,000 - GHC 8,000', 'Quality focus'),
('AR/VR Developer', 'Creates augmented and virtual reality experiences', 'Unity, 3D Modeling, AR/VR SDKs', 'Bachelor\'s in CS/Game Development', 'GHC 4,000 - GHC 10,000', 'Innovative field'),
('Embedded Systems Engineer', 'Develops hardware-software systems', 'C/C++, Microcontrollers, Electronics', 'Bachelor\'s in Computer Engineering', 'GHC 3,500 - GHC 9,000', 'Hardware focus'),
('IT Consultant', 'Advises on technology solutions', 'Business Analysis, IT Strategy, Communication', 'Bachelor\'s in CS/IT + Experience', 'GHC 4,000 - GHC 10,000', 'Advisory role'),
('Machine Learning Engineer', 'Builds and deploys ML models', 'Python, TensorFlow, Data Science', 'Master\'s in ML/AI', 'GHC 5,000 - GHC 12,000', 'AI focus'),
('Web Developer', 'Creates websites and web applications', 'HTML, CSS, JavaScript, Frameworks', 'Bachelor\'s in CS/Web Development', 'GHC 3,000 - GHC 8,000', 'Web focus');

-- Insert sample inspiring stories
INSERT INTO inspiring_stories (name, role, company, image_path, short_quote, full_story, audio_path) VALUES
('Ama Ofori', 'Senior Software Engineer', 'Google', 'assets/images/ama_ofori.jpg', 'From Accra to Silicon Valley - my journey in tech', 'I started my journey in tech at the University of Ghana...', 'assets/audio/ama_ofori.mp3'),
('Kwame Mensah', 'Data Science Lead', 'MTN Ghana', 'assets/images/kwame_mensah.jpg', 'Using data to drive positive change in Africa', 'My passion for data science began during my undergraduate studies...', 'assets/audio/kwame_mensah.mp3'),
('Esi Bonsu', 'Network Security Specialist', 'Ghana National Security', 'assets/images/esi_bonsu.jpg', 'Protecting Ghana digital infrastructure', 'As a Network Security Specialist, I play a crucial role...', 'assets/audio/esi_bonsu.mp3');

-- Insert story-career relationships
INSERT INTO story_careers (story_id, career_id) VALUES
(1, 1), -- Ama Ofori - Software Developer
(1, 3), -- Ama Ofori - UI/UX Designer
(2, 2), -- Kwame Mensah - Data Scientist
(3, 4); -- Esi Bonsu - Network Engineer 

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tech_words (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word VARCHAR(100) NOT NULL,
    definition TEXT NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE app_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT
);

CREATE TABLE user_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert admin user
INSERT INTO users (firstname, lastname, email, password, role)
VALUES ('Rahinatu', 'Lawal', 'rahinatulawal02@gmail.com', '$2y$10$e0NRQw6Qw6Qw6Qw6Qw6QOeQw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6Qw6', 'admin');

CREATE TABLE word_careers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    word_id INT NOT NULL,
    career_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (word_id) REFERENCES tech_words(id) ON DELETE CASCADE,
    FOREIGN KEY (career_id) REFERENCES career_profiles(id) ON DELETE CASCADE
);

-- Insert some sample tech words into the tech_words table for initial data
INSERT INTO tech_words (word, definition) VALUES
('Algorithm', 'A set of rules or steps used to solve a problem or perform a task.'),
('API', 'A set of functions and protocols that allow different software applications to communicate with each other.'),
('Bug', 'An error or flaw in a computer program that causes it to produce incorrect or unexpected results.'),
('Cloud Computing', 'The delivery of computing services over the internet, allowing for on-demand access to resources.'),
('Database', 'An organized collection of data, generally stored and accessed electronically from a computer system.'),
('Encryption', 'The process of converting information or data into a code to prevent unauthorized access.'),
('Framework', 'A platform for developing software applications that provides a foundation on which software developers can build programs for a specific platform.'),
('Open Source', 'Software with source code that anyone can inspect, modify, and enhance.'),
('UI', 'User Interface; the space where interactions between humans and machines occur.'),
('UX', 'User Experience; the overall experience a user has when interacting with a product or service.');

CREATE TABLE quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option ENUM('A', 'B', 'C', 'D') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE quiz_results_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    career_id INT NOT NULL,
    weight INT DEFAULT 1,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (career_id) REFERENCES career_profiles(id) ON DELETE CASCADE
);


-- Insert student-friendly quiz questions for career discovery
INSERT INTO quiz_questions (question, option_a, option_b, option_c, option_d, correct_option) VALUES
('Which school subject do you enjoy the most?', 'Art or Design', 'Math or Science', 'Literature or Languages', 'Social Studies or Business', NULL),
('When working on a group project, what role do you prefer?', 'Coming up with creative ideas', 'Organizing and planning', 'Researching and writing', 'Solving technical problems', NULL),
('What do you like to do in your free time?', 'Draw, design, or create things', 'Play strategy or logic games', 'Write stories or help friends with homework', 'Take apart gadgets or try new apps', NULL),
('How do you feel about technology?', 'I like making things look cool and easy to use', 'Im curious about how things work behind the scenes', 'I want to use technology to help people', 'I want to invent new things with technology', NULL),
('Which of these sounds most fun to you?', 'Designing a poster or website', 'Figuring out how to fix a computer problem', 'Explaining how something works to a friend', 'Building a robot or a game', NULL);

-- Q1: Which school subject do you enjoy the most?
INSERT INTO quiz_results_mapping (question_id, career_id, weight) VALUES
(11, 3, 2), (11, 9, 2), (11, 16, 2), -- A: UI/UX Designer, Game Developer, AR/VR Developer
(11, 2, 2), (11, 5, 2), (11, 19, 2), (11, 1, 2), -- B: Data Scientist, AI Engineer, Machine Learning Engineer, Software Developer
(11, 14, 2), (11, 18, 2), -- C: Technical Writer, IT Consultant
(11, 13, 2), (11, 18, 2), (11, 6, 2); -- D: IT Project Manager, IT Consultant, Cloud Architect

-- Q2: When working on a group project, what role do you prefer?
INSERT INTO quiz_results_mapping (question_id, career_id, weight) VALUES
(12, 3, 2), (12, 9, 2), -- A: UI/UX Designer, Game Developer
(12, 13, 2), (12, 8, 2), -- B: IT Project Manager, DevOps Engineer
(12, 14, 2), (12, 2, 2), -- C: Technical Writer, Data Scientist
(12, 1, 2), (12, 4, 2), (12, 17, 2); -- D: Software Developer, Network Engineer, Embedded Systems Engineer

-- Q3: What do you like to do in your free time?
INSERT INTO quiz_results_mapping (question_id, career_id, weight) VALUES
(13, 3, 2), (13, 9, 2), (13, 16, 2), -- A: UI/UX Designer, Game Developer, AR/VR Developer
(13, 2, 2), (13, 1, 2), (13, 15, 2), -- B: Data Scientist, Software Developer, Quality Assurance Engineer
(13, 14, 2), (13, 18, 2), -- C: Technical Writer, IT Consultant
(13, 17, 2), (13, 10, 2), (13, 4, 2); -- D: Embedded Systems Engineer, Mobile App Developer, Network Engineer

-- Q4: How do you feel about technology?
INSERT INTO quiz_results_mapping (question_id, career_id, weight) VALUES
(14, 3, 2), (14, 20, 2), -- A: UI/UX Designer, Web Developer
(14, 1, 2), (14, 12, 2), (14, 7, 2), -- B: Software Developer, Database Administrator, Cybersecurity Analyst
(14, 18, 2), (14, 14, 2), -- C: IT Consultant, Technical Writer
(14, 5, 2), (14, 19, 2), (14, 11, 2); -- D: AI Engineer, Machine Learning Engineer, Blockchain Developer

-- Q5: Which of these sounds most fun to you?
INSERT INTO quiz_results_mapping (question_id, career_id, weight) VALUES
(15, 3, 2), (15, 20, 2), -- A: UI/UX Designer, Web Developer
(15, 4, 2), (15, 7, 2), (15, 12, 2), -- B: Network Engineer, Cybersecurity Analyst, Database Administrator
(15, 14, 2), (15, 18, 2), -- C: Technical Writer, IT Consultant
(15, 17, 2), (15, 9, 2), (15, 5, 2); -- D: Embedded Systems Engineer, Game Developer, AI Engineer

