CREATE DATABASE IF NOT EXISTS maruti;
USE maruti;

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'family_member') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS family_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    age INT,
    gender ENUM('male', 'female', 'other'),
    relationship VARCHAR(50),
    medical_history TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS health_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    family_member_id INT,
    record_date DATE NOT NULL,
    weight DECIMAL(5,2),
    height DECIMAL(5,2),
    blood_pressure VARCHAR(20),
    heart_rate INT,
    symptoms TEXT,
    diagnosis TEXT,
    medications TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (family_member_id) REFERENCES family_members(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    family_member_id INT,
    appointment_date DATETIME NOT NULL,
    doctor_name VARCHAR(100),
    specialization VARCHAR(100),
    reason TEXT,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (family_member_id) REFERENCES family_members(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS medications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    family_member_id INT,
    medicine_name VARCHAR(100) NOT NULL,
    dosage VARCHAR(50),
    frequency VARCHAR(50),
    start_date DATE,
    end_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (family_member_id) REFERENCES family_members(id) ON DELETE CASCADE
);

CREATE INDEX idx_family_members_user_id ON family_members(user_id);
CREATE INDEX idx_health_records_family_member_id ON health_records(family_member_id);
CREATE INDEX idx_medications_family_member_id ON medications(family_member_id);
CREATE INDEX idx_appointments_family_member_id ON appointments(family_member_id);
CREATE INDEX idx_appointments_status ON appointments(status);
CREATE INDEX idx_medications_dates ON medications(start_date, end_date);
CREATE INDEX idx_health_records_date ON health_records(record_date);
CREATE INDEX idx_appointments_date ON appointments(appointment_date);

INSERT INTO users (username, password, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'admin'),
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user1@example.com', 'family_member');

INSERT INTO family_members (user_id, name, age, gender, relationship, medical_history) VALUES
(1, 'John Doe', 35, 'male', 'Self', 'No major medical history'),
(1, 'Jane Doe', 32, 'female', 'Spouse', 'Allergic to penicillin'),
(2, 'Mike Smith', 40, 'male', 'Self', 'Hypertension');

INSERT INTO health_records (family_member_id, record_date, weight, height, blood_pressure, heart_rate, symptoms, diagnosis, medications, notes) VALUES
(1, '2024-01-15', 75.5, 180.0, '120/80', 72, 'Fever, cough', 'Common cold', 'Paracetamol', 'Rest and hydration recommended'),
(2, '2024-01-16', 65.0, 165.0, '118/75', 68, 'Headache', 'Migraine', 'Ibuprofen', 'Avoid triggers');

INSERT INTO medications (family_member_id, medicine_name, dosage, frequency, start_date, end_date, notes) VALUES
(1, 'Paracetamol', '500mg', 'Every 6 hours', '2024-01-15', '2024-01-20', 'Take with food'),
(2, 'Ibuprofen', '400mg', 'Every 8 hours', '2024-01-16', '2024-01-18', 'Take after meals');

INSERT INTO appointments (family_member_id, appointment_date, doctor_name, specialization, reason, status) VALUES
(1, '2024-01-20 10:00:00', 'Dr. Smith', 'General Physician', 'Regular checkup', 'scheduled'),
(2, '2024-01-18 14:30:00', 'Dr. Johnson', 'Neurologist', 'Migraine consultation', 'scheduled'); 