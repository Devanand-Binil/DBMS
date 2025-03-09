

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rollno VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    class VARCHAR(100),
    department VARCHAR(100),
    address TEXT,
    profile_photo VARCHAR(255) DEFAULT 'uploads/default.png'
);

-- Faculty Table
CREATE TABLE IF NOT EXISTS faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rollno VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(100),
    profile_photo VARCHAR(255) DEFAULT 'uploads/default.png'
);

-- Classes Table (with locations)
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL,
    latitude DOUBLE NOT NULL,
    longitude DOUBLE NOT NULL
);

-- Attendance Table (linked to class and student)
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rollno VARCHAR(20) NOT NULL,
    class_id INT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    status VARCHAR(20) DEFAULT 'Present',
    FOREIGN KEY (rollno) REFERENCES students(rollno),
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Leave Applications Table
CREATE TABLE IF NOT EXISTS leave_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_rollno VARCHAR(20) NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    professor_name VARCHAR(100) NOT NULL,
    explanation TEXT NOT NULL,
    document VARCHAR(255),  -- path to uploaded file
    status VARCHAR(20) DEFAULT 'Pending',
    FOREIGN KEY (student_rollno) REFERENCES students(rollno)
);

-- Class Sessions Table (for dynamic class creation by faculty)
CREATE TABLE IF NOT EXISTS class_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    faculty_name VARCHAR(255) NOT NULL,
    latitude DOUBLE NOT NULL,
    longitude DOUBLE NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Admin Table
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rollno VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);



-- Sample Data (You can remove this if you want)

-- Sample Faculty
INSERT INTO faculty (rollno, name, password, department, profile_photo) VALUES
('F001', 'Dr. John Smith', '12345', 'Computer Science', 'uploads/faculty1.png');

-- Sample Student
INSERT INTO students (rollno, name, password, class, department, address, profile_photo) VALUES
('S001', 'Alice Johnson', '12345', 'CS101', 'Computer Science', '123 Main Street', 'uploads/student1.png');

-- Sample Classes with Location
INSERT INTO classes (class_name, latitude, longitude) VALUES
('CS101', 11.3211, 75.9345),
('ME101', 11.3220, 75.9350);

INSERT INTO admin (rollno, password) VALUES 
('A001', 'admin123');

