<?php
// SQLite database configuration
define('DB_PATH', __DIR__ . '/../database/test_management.db');

function getDatabase() {
    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die('Database connection failed: ' . $e->getMessage());
    }
}

function initDatabase() {
    // Create database directory if it doesn't exist
    $dbDir = dirname(DB_PATH);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }

    $pdo = getDatabase();
    
    // Create tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS question_groups (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS questions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            group_id INTEGER,
            question_group TEXT,
            question_text TEXT NOT NULL,
            option_a TEXT NOT NULL,
            option_b TEXT NOT NULL,
            option_c TEXT NOT NULL,
            option_d TEXT NOT NULL,
            correct_answer CHAR(1) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (group_id) REFERENCES question_groups(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            time_limit INTEGER DEFAULT 0,
            is_active BOOLEAN DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS test_questions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            test_id INTEGER,
            question_id INTEGER,
            FOREIGN KEY (test_id) REFERENCES tests(id),
            FOREIGN KEY (question_id) REFERENCES questions(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS test_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            test_id INTEGER,
            participant_name VARCHAR(255),
            participant_email VARCHAR(255),
            score INTEGER DEFAULT 0,
            total_questions INTEGER DEFAULT 0,
            time_taken INTEGER DEFAULT 0,
            started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            completed_at DATETIME,
            FOREIGN KEY (test_id) REFERENCES tests(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS test_answers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            attempt_id INTEGER,
            question_id INTEGER,
            selected_answer CHAR(1),
            is_correct BOOLEAN DEFAULT 0,
            FOREIGN KEY (attempt_id) REFERENCES test_attempts(id),
            FOREIGN KEY (question_id) REFERENCES questions(id)
        )
    ");

    // Create test_results table for student random tests
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS test_results (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            test_id TEXT NOT NULL,
            student_id TEXT NOT NULL,
            score REAL NOT NULL,
            correct_answers INTEGER NOT NULL,
            total_questions INTEGER NOT NULL,
            time_taken INTEGER NOT NULL,
            completed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Insert sample data if tables are empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM question_groups");
    if ($stmt->fetchColumn() == 0) {
        insertSampleData($pdo);
    }
}

function insertSampleData($pdo) {
    // Insert sample question groups
    $pdo->exec("
        INSERT INTO question_groups (name, description) VALUES 
        ('Mathematics', 'Basic mathematics questions'),
        ('Science', 'General science questions'),
        ('History', 'World history questions'),
        ('Programming', 'Basic programming concepts')
    ");

    // Insert sample questions with more variety for random tests
    $questions = [
        // Mathematics (need 40+ questions for random tests)
        [1, 'Mathematics', 'What is 2 + 2?', '3', '4', '5', '6', 'B'],
        [1, 'Mathematics', 'What is 10 × 5?', '45', '50', '55', '60', 'B'],
        [1, 'Mathematics', 'What is 15 - 7?', '6', '7', '8', '9', 'C'],
        [1, 'Mathematics', 'What is 36 ÷ 6?', '5', '6', '7', '8', 'B'],
        [1, 'Mathematics', 'What is 3²?', '6', '9', '12', '15', 'B'],
        [1, 'Mathematics', 'What is √16?', '2', '3', '4', '5', 'C'],
        [1, 'Mathematics', 'What is 25% of 80?', '15', '20', '25', '30', 'B'],
        [1, 'Mathematics', 'What is 7 × 8?', '54', '56', '58', '60', 'B'],
        [1, 'Mathematics', 'What is 100 - 37?', '63', '67', '73', '77', 'A'],
        [1, 'Mathematics', 'What is 12 + 19?', '29', '31', '33', '35', 'B'],
        [1, 'Mathematics', 'What is 144 ÷ 12?', '10', '11', '12', '13', 'C'],
        [1, 'Mathematics', 'What is 5³?', '15', '25', '125', '625', 'C'],
        [1, 'Mathematics', 'What is 0.5 × 20?', '5', '10', '15', '20', 'B'],
        [1, 'Mathematics', 'What is 2/3 of 30?', '15', '18', '20', '25', 'C'],
        [1, 'Mathematics', 'What is 45 + 28?', '71', '73', '75', '77', 'B'],
        [1, 'Mathematics', 'What is 9 × 7?', '56', '63', '70', '77', 'B'],
        [1, 'Mathematics', 'What is 81 ÷ 9?', '7', '8', '9', '10', 'C'],
        [1, 'Mathematics', 'What is 6²?', '12', '24', '36', '48', 'C'],
        [1, 'Mathematics', 'What is 75% of 40?', '25', '30', '35', '40', 'B'],
        [1, 'Mathematics', 'What is 13 × 4?', '48', '50', '52', '54', 'C'],
        [1, 'Mathematics', 'What is 200 - 87?', '113', '117', '123', '127', 'A'],
        [1, 'Mathematics', 'What is √25?', '3', '4', '5', '6', 'C'],
        [1, 'Mathematics', 'What is 18 + 26?', '42', '44', '46', '48', 'B'],
        [1, 'Mathematics', 'What is 8 × 9?', '70', '72', '74', '76', 'B'],
        [1, 'Mathematics', 'What is 96 ÷ 8?', '10', '11', '12', '13', 'C'],
        [1, 'Mathematics', 'What is 4³?', '12', '16', '48', '64', 'D'],
        [1, 'Mathematics', 'What is 0.25 × 60?', '12', '15', '18', '20', 'B'],
        [1, 'Mathematics', 'What is 3/4 of 32?', '20', '22', '24', '26', 'C'],
        [1, 'Mathematics', 'What is 67 + 38?', '103', '105', '107', '109', 'B'],
        [1, 'Mathematics', 'What is 11 × 6?', '64', '66', '68', '70', 'B'],
        [1, 'Mathematics', 'What is 121 ÷ 11?', '9', '10', '11', '12', 'C'],
        [1, 'Mathematics', 'What is 7²?', '14', '28', '42', '49', 'D'],
        [1, 'Mathematics', 'What is 60% of 50?', '25', '30', '35', '40', 'B'],
        [1, 'Mathematics', 'What is 14 × 3?', '40', '42', '44', '46', 'B'],
        [1, 'Mathematics', 'What is 150 - 73?', '75', '77', '79', '81', 'B'],
        [1, 'Mathematics', 'What is √36?', '4', '5', '6', '7', 'C'],
        [1, 'Mathematics', 'What is 29 + 47?', '74', '76', '78', '80', 'B'],
        [1, 'Mathematics', 'What is 12 × 7?', '82', '84', '86', '88', 'B'],
        [1, 'Mathematics', 'What is 168 ÷ 14?', '10', '11', '12', '13', 'C'],
        [1, 'Mathematics', 'What is 5²?', '10', '15', '20', '25', 'D'],
        [1, 'Mathematics', 'What is 0.75 × 80?', '50', '55', '60', '65', 'C'],
        [1, 'Mathematics', 'What is 2/5 of 45?', '15', '18', '20', '22', 'B'],
        [1, 'Mathematics', 'What is 84 + 59?', '141', '143', '145', '147', 'B'],
        [1, 'Mathematics', 'What is 15 × 4?', '58', '60', '62', '64', 'B'],
        [1, 'Mathematics', 'What is 132 ÷ 12?', '9', '10', '11', '12', 'C'],

        // Science (40+ questions)
        [2, 'Science', 'What is the chemical symbol for water?', 'H2O', 'CO2', 'O2', 'N2', 'A'],
        [2, 'Science', 'How many planets are in our solar system?', '7', '8', '9', '10', 'B'],
        [2, 'Science', 'What gas do plants absorb from the atmosphere?', 'Oxygen', 'Carbon Dioxide', 'Nitrogen', 'Hydrogen', 'B'],
        [2, 'Science', 'What is the hardest natural substance?', 'Gold', 'Iron', 'Diamond', 'Silver', 'C'],
        [2, 'Science', 'What is the speed of light?', '300,000 km/s', '150,000 km/s', '450,000 km/s', '600,000 km/s', 'A'],
        [2, 'Science', 'What is the chemical symbol for gold?', 'Go', 'Gd', 'Au', 'Ag', 'C'],
        [2, 'Science', 'How many bones are in the human body?', '196', '206', '216', '226', 'B'],
        [2, 'Science', 'What is the largest organ in the human body?', 'Heart', 'Brain', 'Liver', 'Skin', 'D'],
        [2, 'Science', 'What gas makes up most of Earth\'s atmosphere?', 'Oxygen', 'Carbon Dioxide', 'Nitrogen', 'Hydrogen', 'C'],
        [2, 'Science', 'What is the chemical formula for salt?', 'NaCl', 'KCl', 'CaCl2', 'MgCl2', 'A'],
        [2, 'Science', 'What is the smallest unit of matter?', 'Molecule', 'Atom', 'Electron', 'Proton', 'B'],
        [2, 'Science', 'What is the boiling point of water?', '90°C', '100°C', '110°C', '120°C', 'B'],
        [2, 'Science', 'What is the chemical symbol for iron?', 'Ir', 'Fe', 'In', 'I', 'B'],
        [2, 'Science', 'How many chambers does a human heart have?', '2', '3', '4', '5', 'C'],
        [2, 'Science', 'What is the largest planet in our solar system?', 'Saturn', 'Jupiter', 'Neptune', 'Uranus', 'B'],
        [2, 'Science', 'What is the chemical symbol for silver?', 'Si', 'Ag', 'Al', 'S', 'B'],
        [2, 'Science', 'What is the freezing point of water?', '-10°C', '0°C', '10°C', '32°C', 'B'],
        [2, 'Science', 'What is the chemical formula for carbon dioxide?', 'CO', 'CO2', 'C2O', 'C2O2', 'B'],
        [2, 'Science', 'What is the closest star to Earth?', 'Alpha Centauri', 'Sirius', 'The Sun', 'Polaris', 'C'],
        [2, 'Science', 'What is the chemical symbol for oxygen?', 'Ox', 'O', 'O2', 'Oy', 'B'],
        [2, 'Science', 'How many teeth does an adult human have?', '28', '30', '32', '34', 'C'],
        [2, 'Science', 'What is the smallest bone in the human body?', 'Stapes', 'Malleus', 'Incus', 'Radius', 'A'],
        [2, 'Science', 'What is the chemical symbol for carbon?', 'Ca', 'C', 'Cr', 'Co', 'B'],
        [2, 'Science', 'What is the largest mammal?', 'Elephant', 'Blue Whale', 'Giraffe', 'Hippopotamus', 'B'],
        [2, 'Science', 'What is the chemical formula for methane?', 'CH4', 'C2H4', 'C2H6', 'C3H8', 'A'],
        [2, 'Science', 'What is the normal human body temperature?', '36°C', '37°C', '38°C', '39°C', 'B'],
        [2, 'Science', 'What is the chemical symbol for helium?', 'H', 'He', 'Hl', 'Hm', 'B'],
        [2, 'Science', 'How many lungs do humans have?', '1', '2', '3', '4', 'B'],
        [2, 'Science', 'What is the fastest land animal?', 'Lion', 'Cheetah', 'Leopard', 'Tiger', 'B'],
        [2, 'Science', 'What is the chemical symbol for sodium?', 'So', 'Na', 'S', 'N', 'B'],
        [2, 'Science', 'What is the largest ocean on Earth?', 'Atlantic', 'Indian', 'Arctic', 'Pacific', 'D'],
        [2, 'Science', 'What is the chemical formula for ammonia?', 'NH3', 'NH4', 'N2H4', 'N2H6', 'A'],
        [2, 'Science', 'What is the hardest part of the human body?', 'Bone', 'Tooth enamel', 'Nail', 'Cartilage', 'B'],
        [2, 'Science', 'What is the chemical symbol for potassium?', 'P', 'K', 'Po', 'Pt', 'B'],
        [2, 'Science', 'How many continents are there?', '5', '6', '7', '8', 'C'],
        [2, 'Science', 'What is the chemical formula for hydrogen peroxide?', 'H2O', 'H2O2', 'HO2', 'H3O2', 'B'],
        [2, 'Science', 'What is the longest bone in the human body?', 'Tibia', 'Femur', 'Humerus', 'Radius', 'B'],
        [2, 'Science', 'What is the chemical symbol for calcium?', 'C', 'Ca', 'Cl', 'Cr', 'B'],
        [2, 'Science', 'What is the smallest planet in our solar system?', 'Mars', 'Venus', 'Mercury', 'Pluto', 'C'],
        [2, 'Science', 'What is the chemical formula for sulfuric acid?', 'H2SO4', 'H2SO3', 'HSO4', 'H3SO4', 'A'],
        [2, 'Science', 'What is the main gas in the sun?', 'Oxygen', 'Hydrogen', 'Helium', 'Carbon', 'B'],
        [2, 'Science', 'What is the chemical symbol for nitrogen?', 'Ni', 'N', 'No', 'Ne', 'B'],
        [2, 'Science', 'How many sides does a hexagon have?', '5', '6', '7', '8', 'B'],
        [2, 'Science', 'What is the chemical formula for glucose?', 'C6H12O6', 'C6H10O6', 'C5H12O6', 'C6H12O5', 'A'],
        [2, 'Science', 'What is the largest bird?', 'Eagle', 'Ostrich', 'Penguin', 'Albatross', 'B'],

        // History (40+ questions)
        [3, 'History', 'In which year did World War II end?', '1944', '1945', '1946', '1947', 'B'],
        [3, 'History', 'Who was the first person to walk on the moon?', 'Buzz Aldrin', 'Neil Armstrong', 'John Glenn', 'Alan Shepard', 'B'],
        [3, 'History', 'In which year did the Titanic sink?', '1910', '1911', '1912', '1913', 'C'],
        [3, 'History', 'Who was the first President of the United States?', 'Thomas Jefferson', 'George Washington', 'John Adams', 'Benjamin Franklin', 'B'],
        [3, 'History', 'In which year did World War I begin?', '1912', '1913', '1914', '1915', 'C'],
        [3, 'History', 'Who painted the Mona Lisa?', 'Vincent van Gogh', 'Pablo Picasso', 'Leonardo da Vinci', 'Michelangelo', 'C'],
        [3, 'History', 'In which year did the Berlin Wall fall?', '1987', '1988', '1989', '1990', 'C'],
        [3, 'History', 'Who was the first woman to win a Nobel Prize?', 'Marie Curie', 'Mother Teresa', 'Jane Addams', 'Bertha von Suttner', 'A'],
        [3, 'History', 'In which year did Columbus reach the Americas?', '1490', '1491', '1492', '1493', 'C'],
        [3, 'History', 'Who was the longest-reigning British monarch?', 'Victoria', 'Elizabeth II', 'George III', 'Henry VIII', 'B'],
        [3, 'History', 'In which year did the American Civil War end?', '1863', '1864', '1865', '1866', 'C'],
        [3, 'History', 'Who wrote "Romeo and Juliet"?', 'Charles Dickens', 'William Shakespeare', 'Jane Austen', 'Mark Twain', 'B'],
        [3, 'History', 'In which year did the French Revolution begin?', '1787', '1788', '1789', '1790', 'C'],
        [3, 'History', 'Who was the leader of Nazi Germany?', 'Heinrich Himmler', 'Adolf Hitler', 'Joseph Goebbels', 'Hermann Göring', 'B'],
        [3, 'History', 'In which year did the Soviet Union collapse?', '1989', '1990', '1991', '1992', 'C'],
        [3, 'History', 'Who was the first person to circumnavigate the globe?', 'Christopher Columbus', 'Vasco da Gama', 'Ferdinand Magellan', 'James Cook', 'C'],
        [3, 'History', 'In which year did the Great Depression begin?', '1927', '1928', '1929', '1930', 'C'],
        [3, 'History', 'Who was the Egyptian queen who had relationships with Julius Caesar and Mark Antony?', 'Nefertiti', 'Cleopatra', 'Hatshepsut', 'Ankhesenamun', 'B'],
        [3, 'History', 'In which year did India gain independence?', '1945', '1946', '1947', '1948', 'C'],
        [3, 'History', 'Who was the first Emperor of Rome?', 'Julius Caesar', 'Augustus', 'Nero', 'Caligula', 'B'],
        [3, 'History', 'In which year did the Black Death peak in Europe?', '1347', '1348', '1349', '1350', 'B'],
        [3, 'History', 'Who was the leader of the Soviet Union during World War II?', 'Vladimir Lenin', 'Joseph Stalin', 'Nikita Khrushchev', 'Leonid Brezhnev', 'B'],
        [3, 'History', 'In which year did the United States declare independence?', '1774', '1775', '1776', '1777', 'C'],
        [3, 'History', 'Who was the first person to fly solo across the Atlantic?', 'Amelia Earhart', 'Charles Lindbergh', 'Wiley Post', 'Howard Hughes', 'B'],
        [3, 'History', 'In which year did the Chernobyl disaster occur?', '1984', '1985', '1986', '1987', 'C'],
        [3, 'History', 'Who was the ancient Greek philosopher who taught Alexander the Great?', 'Socrates', 'Plato', 'Aristotle', 'Pythagoras', 'C'],
        [3, 'History', 'In which year did the Spanish Civil War end?', '1937', '1938', '1939', '1940', 'C'],
        [3, 'History', 'Who was the first person to reach the South Pole?', 'Ernest Shackleton', 'Roald Amundsen', 'Robert Falcon Scott', 'Edmund Hillary', 'B'],
        [3, 'History', 'In which year did the Cuban Missile Crisis occur?', '1960', '1961', '1962', '1963', 'C'],
        [3, 'History', 'Who was the Roman general who crossed the Rubicon?', 'Pompey', 'Julius Caesar', 'Mark Antony', 'Octavian', 'B'],
        [3, 'History', 'In which year did the Vietnam War end?', '1973', '1974', '1975', '1976', 'C'],
        [3, 'History', 'Who was the first person to climb Mount Everest?', 'George Mallory', 'Edmund Hillary', 'Tenzing Norgay', 'Reinhold Messner', 'B'],
        [3, 'History', 'In which year did the Korean War begin?', '1948', '1949', '1950', '1951', 'C'],
        [3, 'History', 'Who was the Mongol leader who created one of the largest empires in history?', 'Kublai Khan', 'Genghis Khan', 'Ögedei Khan', 'Möngke Khan', 'B'],
        [3, 'History', 'In which year did the Suez Crisis occur?', '1954', '1955', '1956', '1957', 'C'],
        [3, 'History', 'Who was the first person to use the printing press?', 'Johannes Gutenberg', 'William Caxton', 'Aldus Manutius', 'Nicolas Jenson', 'A'],
        [3, 'History', 'In which year did the Bay of Pigs invasion occur?', '1959', '1960', '1961', '1962', 'C'],
        [3, 'History', 'Who was the Chinese leader during the Cultural Revolution?', 'Sun Yat-sen', 'Chiang Kai-shek', 'Mao Zedong', 'Deng Xiaoping', 'C'],
        [3, 'History', 'In which year did the Watergate scandal break?', '1970', '1971', '1972', '1973', 'C'],
        [3, 'History', 'Who was the first person to discover penicillin?', 'Louis Pasteur', 'Alexander Fleming', 'Joseph Lister', 'Robert Koch', 'B'],
        [3, 'History', 'In which year did the Iranian Revolution occur?', '1977', '1978', '1979', '1980', 'C'],
        [3, 'History', 'Who was the leader of the Indian independence movement?', 'Jawaharlal Nehru', 'Mahatma Gandhi', 'Subhas Chandra Bose', 'Sardar Patel', 'B'],
        [3, 'History', 'In which year did the Falklands War occur?', '1980', '1981', '1982', '1983', 'C'],
        [3, 'History', 'Who was the first person to propose the theory of evolution?', 'Gregor Mendel', 'Charles Darwin', 'Alfred Wallace', 'Jean-Baptiste Lamarck', 'B'],
        [3, 'History', 'In which year did the Gulf War begin?', '1989', '1990', '1991', '1992', 'C'],

        // Programming (40+ questions)
        [4, 'Programming', 'What does HTML stand for?', 'Hypertext Markup Language', 'High Tech Modern Language', 'Home Tool Markup Language', 'Hyperlink and Text Markup Language', 'A'],
        [4, 'Programming', 'Which programming language is known for web development?', 'C++', 'Java', 'JavaScript', 'Python', 'C'],
        [4, 'Programming', 'What does CSS stand for?', 'Computer Style Sheets', 'Cascading Style Sheets', 'Creative Style Sheets', 'Colorful Style Sheets', 'B'],
        [4, 'Programming', 'Which symbol is used for comments in Python?', '//', '/*', '#', '--', 'C'],
        [4, 'Programming', 'What does SQL stand for?', 'Structured Query Language', 'Simple Query Language', 'Standard Query Language', 'System Query Language', 'A'],
        [4, 'Programming', 'Which of these is a JavaScript framework?', 'Django', 'React', 'Laravel', 'Spring', 'B'],
        [4, 'Programming', 'What does API stand for?', 'Application Programming Interface', 'Advanced Programming Interface', 'Automated Programming Interface', 'Applied Programming Interface', 'A'],
        [4, 'Programming', 'Which language is primarily used for iOS development?', 'Java', 'Kotlin', 'Swift', 'C#', 'C'],
        [4, 'Programming', 'What does JSON stand for?', 'JavaScript Object Notation', 'Java Standard Object Notation', 'JavaScript Oriented Notation', 'Java Script Object Network', 'A'],
        [4, 'Programming', 'Which of these is a Python web framework?', 'Angular', 'Django', 'Vue.js', 'Express', 'B'],
        [4, 'Programming', 'What does HTTP stand for?', 'HyperText Transfer Protocol', 'High Transfer Text Protocol', 'HyperText Transport Protocol', 'High Text Transfer Protocol', 'A'],
        [4, 'Programming', 'Which symbol is used for comments in Java?', '#', '//', '<!--', '**', 'B'],
        [4, 'Programming', 'What does IDE stand for?', 'Integrated Development Environment', 'Interactive Development Environment', 'Internal Development Environment', 'Intelligent Development Environment', 'A'],
        [4, 'Programming', 'Which of these is a NoSQL database?', 'MySQL', 'PostgreSQL', 'MongoDB', 'SQLite', 'C'],
        [4, 'Programming', 'What does URL stand for?', 'Universal Resource Locator', 'Uniform Resource Locator', 'Universal Reference Locator', 'Uniform Reference Locator', 'B'],
        [4, 'Programming', 'Which language is known as the "mother of all languages"?', 'Assembly', 'C', 'FORTRAN', 'COBOL', 'B'],
        [4, 'Programming', 'What does XML stand for?', 'eXtensible Markup Language', 'eXtended Markup Language', 'eXtra Markup Language', 'eXecutable Markup Language', 'A'],
        [4, 'Programming', 'Which of these is a version control system?', 'Git', 'Docker', 'Jenkins', 'Kubernetes', 'A'],
        [4, 'Programming', 'What does OOP stand for?', 'Object Oriented Programming', 'Objective Oriented Programming', 'Object Operational Programming', 'Objective Operational Programming', 'A'],
        [4, 'Programming', 'Which language is primarily used for Android development?', 'Swift', 'Objective-C', 'Java', 'C#', 'C'],
        [4, 'Programming', 'What does CRUD stand for?', 'Create Read Update Delete', 'Create Retrieve Update Delete', 'Create Read Upload Delete', 'Create Retrieve Upload Delete', 'A'],
        [4, 'Programming', 'Which of these is a JavaScript runtime?', 'Apache', 'Node.js', 'Nginx', 'IIS', 'B'],
        [4, 'Programming', 'What does MVC stand for?', 'Model View Controller', 'Model View Component', 'Multiple View Controller', 'Multiple View Component', 'A'],
        [4, 'Programming', 'Which symbol is used for comments in HTML?', '//', '/*', '<!--', '#', 'C'],
        [4, 'Programming', 'What does REST stand for?', 'Representational State Transfer', 'Remote State Transfer', 'Representational System Transfer', 'Remote System Transfer', 'A'],
        [4, 'Programming', 'Which of these is a CSS preprocessor?', 'Sass', 'React', 'Angular', 'Vue', 'A'],
        [4, 'Programming', 'What does DOM stand for?', 'Document Object Model', 'Data Object Model', 'Document Oriented Model', 'Data Oriented Model', 'A'],
        [4, 'Programming', 'Which language is known for machine learning?', 'PHP', 'Python', 'Ruby', 'Perl', 'B'],
        [4, 'Programming', 'What does AJAX stand for?', 'Asynchronous JavaScript and XML', 'Advanced JavaScript and XML', 'Asynchronous Java and XML', 'Advanced Java and XML', 'A'],
        [4, 'Programming', 'Which of these is a relational database?', 'MongoDB', 'CouchDB', 'MySQL', 'Redis', 'C'],
        [4, 'Programming', 'What does FTP stand for?', 'File Transfer Protocol', 'Fast Transfer Protocol', 'File Transport Protocol', 'Fast Transport Protocol', 'A'],
        [4, 'Programming', 'Which language is used for styling web pages?', 'HTML', 'JavaScript', 'CSS', 'PHP', 'C'],
        [4, 'Programming', 'What does TCP stand for?', 'Transmission Control Protocol', 'Transfer Control Protocol', 'Transmission Communication Protocol', 'Transfer Communication Protocol', 'A'],
        [4, 'Programming', 'Which of these is a cloud platform?', 'GitHub', 'AWS', 'Stack Overflow', 'CodePen', 'B'],
        [4, 'Programming', 'What does SSL stand for?', 'Secure Socket Layer', 'System Security Layer', 'Secure System Layer', 'Socket Security Layer', 'A'],
        [4, 'Programming', 'Which language is primarily used for data analysis?', 'C++', 'R', 'Assembly', 'COBOL', 'B'],
        [4, 'Programming', 'What does DNS stand for?', 'Domain Name System', 'Data Name System', 'Domain Network System', 'Data Network System', 'A'],
        [4, 'Programming', 'Which of these is a containerization platform?', 'Git', 'Docker', 'Jenkins', 'Ansible', 'B'],
        [4, 'Programming', 'What does RAM stand for?', 'Random Access Memory', 'Rapid Access Memory', 'Remote Access Memory', 'Real Access Memory', 'A'],
        [4, 'Programming', 'Which language is known for its use in blockchain?', 'Python', 'Solidity', 'Ruby', 'PHP', 'B'],
        [4, 'Programming', 'What does CPU stand for?', 'Central Processing Unit', 'Computer Processing Unit', 'Central Program Unit', 'Computer Program Unit', 'A'],
        [4, 'Programming', 'Which of these is a testing framework?', 'React', 'Jest', 'Express', 'Django', 'B'],
        [4, 'Programming', 'What does GPU stand for?', 'Graphics Processing Unit', 'General Processing Unit', 'Graphics Program Unit', 'General Program Unit', 'A'],
        [4, 'Programming', 'Which language is primarily used for system programming?', 'JavaScript', 'Python', 'C', 'Ruby', 'C'],
        [4, 'Programming', 'What does SSD stand for?', 'Solid State Drive', 'System State Drive', 'Secure State Drive', 'Standard State Drive', 'A']
    ];

    $stmt = $pdo->prepare("INSERT INTO questions (group_id, question_group, question_text, option_a, option_b, option_c, option_d, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($questions as $question) {
        $stmt->execute($question);
    }
}
?>