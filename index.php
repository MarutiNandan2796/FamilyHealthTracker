<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Health Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .navbar.scrolled {
            padding: 0.5rem 0;
            background: rgba(102, 126, 234, 0.95);
            backdrop-filter: blur(10px);
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            transition: all 0.3s ease;
        }
        .navbar-brand i {
            transform: scale(1.2);
            margin-right: 10px;
        }
        .nav-link {
            position: relative;
            padding: 0.8rem 1.2rem !important;
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .nav-link:before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: white;
            transform: translateX(-50%);
            transition: all 0.3s ease;
        }
        .nav-link:hover:before {
            width: 80%;
        }
        .nav-link.active:before {
            width: 80%;
        }
        .hero-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%),
                        url('https://img.freepik.com/free-photo/medical-banner-with-healthcare-workers_23-2149611443.jpg?w=1380&t=st=1709883550~exp=1709884150~hmac=4f1722354fd68f8f6c9f9f3c5ab53619f8b6c5f5e2c2f5e2c5f5e2c2f5e2c2f');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 8rem 0 4rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .hero-text {
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            animation: slideIn 1s ease-out;
        }
        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 0;
            width: 100%;
            height: 100px;
            background: #f8f9fa;
            transform: skewY(-3deg);
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        @keyframes scaleIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.1);
            transition: all 0.4s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            position: relative;
            overflow: hidden;
            animation: scaleIn 0.5s ease-out forwards;
            opacity: 0;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            opacity: 0;
            transition: all 0.4s ease;
        }
        .stat-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 20px 30px rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.3);
        }
        .stat-card:hover::before {
            opacity: 1;
        }
        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
            color: #764ba2;
        }
        .stat-icon {
            font-size: 2.8rem;
            color: #667eea;
            margin-bottom: 1.5rem;
            transition: all 0.4s ease;
            position: relative;
            z-index: 1;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 1rem 0;
            transition: all 0.4s ease;
            position: relative;
            z-index: 1;
        }
        .stat-card h3 {
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        .chat-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            display: none;
            z-index: 1000;
        }
        .chat-header {
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .chat-messages {
            height: 380px;
            overflow-y: auto;
            padding: 15px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 80%;
        }
        .message.user {
            background: #667eea;
            color: white;
            margin-left: auto;
        }
        .message.bot {
            background: #f0f2f5;
            color: #333;
        }
        .chat-input {
            padding: 15px;
            border-top: 1px solid #eee;
        }
        .chat-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            z-index: 999;
        }
        .chat-toggle:hover {
            transform: scale(1.1);
        }
        .table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }
        .table thead th {
            background: #f8f9fa;
            border-bottom: none;
            color: #667eea;
            font-weight: 600;
        }
        .badge {
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 500;
        }
        .feature-card {
            text-align: center;
            padding: 2.5rem;
            background: white;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.1);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            opacity: 0;
            transition: all 0.4s ease;
        }
        .feature-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 30px rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.3);
        }
        .feature-card:hover::before {
            opacity: 1;
        }
        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }
        .feature-card:hover .btn-primary {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .feature-icon {
            transition: all 0.4s ease;
            margin-bottom: 1.5rem;
        }
        .feature-icon img {
            height: 200px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
        }
        .feature-card:hover .feature-icon img {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        .feature-card h3 {
            color: #2d3748;
            font-weight: 600;
            margin: 1rem 0;
            transition: all 0.3s ease;
        }
        .feature-card p {
            color: #718096;
            transition: all 0.3s ease;
        }
        .feature-card .btn-primary {
            transition: all 0.4s ease;
            border-radius: 12px;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
        }
        .feature-hover-info {
            max-height: 0;
            opacity: 0;
            transition: all 0.4s ease;
            overflow: hidden;
        }
        .feature-card:hover .feature-hover-info {
            max-height: 200px;
            opacity: 1;
            margin-top: 1rem;
        }
        .feature-hover-info ul li {
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            color: #4a5568;
        }
        .feature-hover-info ul li i {
            margin-right: 0.5rem;
            color: #48bb78;
        }
        .navbar-nav .nav-item {
            margin: 0 5px;
        }
        .navbar-nav .nav-link {
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            color: rgba(255, 255, 255, 0.9) !important;
        }
        .navbar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        .navbar-nav .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white !important;
            font-weight: 500;
        }
        .navbar-nav .nav-link i {
            margin-right: 8px;
            font-size: 1.1em;
        }
        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: white;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link.active::after {
            width: 80%;
        }
        .navbar-brand:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        @media (max-width: 991.98px) {
            .navbar-nav .nav-item {
                margin: 5px 0;
            }
            .navbar-nav .nav-link::after {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-heartbeat"></i>
                Family Health Tracker
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="family_members.php">
                            <i class="fas fa-users"></i> Family Members
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="health_records.php">
                            <i class="fas fa-notes-medical"></i> Health Records
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">
                            <i class="fas fa-calendar-check"></i> Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medications.php">
                            <i class="fas fa-pills"></i> Medications
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 hero-text">Welcome to Family Health Tracker</h1>
                    <p class="lead">Keep your family's health information organized and accessible.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <a href="family_members.php" class="stat-card">
                    <i class="fas fa-users stat-icon"></i>
                    <h3>Family Members</h3>
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM family_members WHERE user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $count = $stmt->fetchColumn();
                    ?>
                    <div class="stat-number"><?php echo $count; ?></div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="appointments.php" class="stat-card">
                    <i class="fas fa-calendar-check stat-icon"></i>
                    <h3>Appointments</h3>
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments a 
                        JOIN family_members f ON a.family_member_id = f.id 
                        WHERE f.user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $appointmentCount = $stmt->fetchColumn();
                    ?>
                    <div class="stat-number"><?php echo $appointmentCount; ?></div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="medications.php" class="stat-card">
                    <i class="fas fa-pills stat-icon"></i>
                    <h3>Medications</h3>
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM medications m 
                        JOIN family_members f ON m.family_member_id = f.id 
                        WHERE f.user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $medicationCount = $stmt->fetchColumn();
                    ?>
                    <div class="stat-number"><?php echo $medicationCount; ?></div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="health_records.php" class="stat-card">
                    <i class="fas fa-notes-medical stat-icon"></i>
                    <h3>Health Records</h3>
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM health_records h 
                        JOIN family_members f ON h.family_member_id = f.id 
                        WHERE f.user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $recordCount = $stmt->fetchColumn();
                    ?>
                    <div class="stat-number"><?php echo $recordCount; ?></div>
                </a>
            </div>
        </div>

        <!-- Feature Cards -->
        <div class="row mb-4">
            <div class="col-md-6">
                <a href="family_members.php" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" 
                   title="Add and manage your family members' profiles, including their basic information and medical history">
                    <div class="feature-card">
                        <div class="feature-icon mb-3">
                            <img src="https://img.freepik.com/free-vector/family-doctor-abstract-concept-vector-illustration_107173-25670.jpg" 
                                 alt="Family Health" class="img-fluid mb-3" style="height: 200px; object-fit: cover;">
                        </div>
                        <h3 class="text-dark">Family Health Management</h3>
                        <p class="text-muted">Keep track of your family's health records in one secure place.</p>
                        <div class="mt-3">
                            <span class="btn btn-primary">
                                <i class="fas fa-users me-2"></i>Manage Family Members
                            </span>
                        </div>
                        <div class="feature-hover-info">
                            <ul class="list-unstyled text-start small mt-3">
                                <li><i class="fas fa-check-circle text-success me-2"></i>Add family member profiles</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Track medical history</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Manage personal information</li>
                            </ul>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="medications.php" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" 
                   title="Track medications, set reminders, and manage prescriptions for all family members">
                    <div class="feature-card">
                        <div class="feature-icon mb-3">
                            <img src="https://img.freepik.com/free-vector/medicine-concept-illustration_114360-2802.jpg" 
                                 alt="Medication Management" class="img-fluid mb-3" style="height: 200px; object-fit: cover;">
                        </div>
                        <h3 class="text-dark">Medication Management</h3>
                        <p class="text-muted">Track medications, dosages, and schedules for your family members.</p>
                        <div class="mt-3">
                            <span class="btn btn-primary">
                                <i class="fas fa-pills me-2"></i>Manage Medications
                            </span>
                        </div>
                        <div class="feature-hover-info">
                            <ul class="list-unstyled text-start small mt-3">
                                <li><i class="fas fa-check-circle text-success me-2"></i>Track medication schedules</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Set dosage reminders</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Monitor prescriptions</li>
                            </ul>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-notes-medical me-2"></i>Recent Health Records
                        </h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Family Member</th>
                                        <th>Date</th>
                                        <th>Weight</th>
                                        <th>Blood Pressure</th>
                                        <th>Heart Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT h.*, f.name 
                                        FROM health_records h 
                                        JOIN family_members f ON h.family_member_id = f.id 
                                        WHERE f.user_id = ? 
                                        ORDER BY h.record_date DESC LIMIT 5");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    while ($row = $stmt->fetch()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['record_date']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['weight']) . " kg</td>";
                                        echo "<td>" . htmlspecialchars($row['blood_pressure']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['heart_rate']) . " bpm</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Toggle Button -->
    <div class="chat-toggle" onclick="toggleChat()">
        <i class="fas fa-comments"></i>
    </div>

    <!-- Chat Container -->
    <div class="chat-container" id="chatContainer">
        <div class="chat-header">
            <h5 class="mb-0">
                <i class="fas fa-robot me-2"></i>Health Assistant
            </h5>
            <button type="button" class="btn-close btn-close-white" onclick="toggleChat()"></button>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div class="message bot">
                Hello! I'm your health assistant. How can I help you today?
            </div>
        </div>
        <div class="chat-input">
            <div class="input-group">
                <input type="text" class="form-control" id="userInput" placeholder="Type your message...">
                <button class="btn btn-primary" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleChat() {
            const chatContainer = document.getElementById('chatContainer');
            chatContainer.style.display = chatContainer.style.display === 'none' ? 'block' : 'none';
        }

        async function sendMessage() {
            const userInput = document.getElementById('userInput');
            const message = userInput.value.trim();
            
            if (message) {
                const chatMessages = document.getElementById('chatMessages');
                
                // Add user message
                chatMessages.innerHTML += `
                    <div class="message user">
                        ${message}
                    </div>
                `;
                
                // Clear input
                userInput.value = '';
                
                try {
                    const response = await fetch('chatbot.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ message: message })
                    });
                    
                    const data = await response.json();
                    
                    // Add bot response
                    chatMessages.innerHTML += `
                        <div class="message bot">
                            ${data.response}
                        </div>
                    `;
                } catch (error) {
                    chatMessages.innerHTML += `
                        <div class="message bot">
                            I apologize, but I'm having trouble processing your request right now. Please try again later.
                        </div>
                    `;
                }
                
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Animate stats on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.stat-card, .feature-card').forEach((el) => {
                observer.observe(el);
            });
        });

        // Allow Enter key to send message
        document.getElementById('userInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
</body>
</html> 