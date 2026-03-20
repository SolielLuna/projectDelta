<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data from session for pre-filling
include "db.php";
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$user_username = $user['username'];
$user_email = $user['email'];

// Handle form submission feedback
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Scholarship - ScholarFlow</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
        background: #771111;
        }

        .application-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(25, 118, 210, 0.1);
        }

        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .progress-bar::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 4px;
            background: #e3f2fd;
            z-index: 0;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .step-number {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #e3f2fd;
            color: #771111;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            transition: all 0.3s;
            border: 3px solid #e3f2fd;
        }

        .progress-step.active .step-number {
            background: #771111;
            color: white;
            border-color: #DDAA02;
        }

        .progress-step.completed .step-number {
            background: #4caf50;
            color: white;
            border-color: #4caf50;
        }

        .step-label {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }

        .progress-step.active .step-label {
            color: #771111;
            font-weight: 600;
        }

        .form-section {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .form-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .section-header {
            color: #771111;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e3f2fd;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #424242;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group label .required {
            color: #f44336;
            margin-left: 4px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e3f2fd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #771111;
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
        }

        .form-group input:read-only {
            background: #f5f5f5;
            cursor: not-allowed;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .file-upload {
            position: relative;
            border: 2px dashed #bbdefb;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: #fafafa;
        }

        .file-upload:hover {
            border-color: #771111;
            background: #e3f2fd;
        }

        .file-upload input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-icon {
            font-size: 40px;
            color: #771111;
            margin-bottom: 10px;
        }

        .file-name {
            margin-top: 10px;
            color: #666;
            font-size: 14px;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e3f2fd;
        }

        .btn-nav {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-prev {
            background: #f5f5f5;
            color: #666;
        }

        .btn-prev:hover {
            background: #e0e0e0;
        }

        .btn-next {
            background: #771111;
            color: white;
        }

        .btn-next:hover {
            background-color: #ddaa02;
            cursor: pointer; 
            transition: background-color 0.3s ease;
        }

        .btn-submit {
            background: #4caf50;
            color: white;
        }

        .btn-submit:hover {
            background: #388e3c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        .char-counter {
            text-align: right;
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .char-counter.warning {
            color: #ff9800;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .application-container {
                margin: 20px;
                padding: 20px;
            }
            
            .step-label {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="application-container">

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <span>✓</span> <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <span>✗</span> <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <h2 style="text-align: center; color: #771111; margin-bottom: 10px;">Scholarship Application</h2>
    <p style="text-align: center; color: #666; margin-bottom: 30px;">Complete all sections to submit your application</p>

    <!-- Progress Bar -->
    <div class="progress-bar">
        <div class="progress-step active" data-step="1">
            <div class="step-number">1</div>
            <div class="step-label">Personal</div>
        </div>
        <div class="progress-step" data-step="2">
            <div class="step-number">2</div>
            <div class="step-label">Education</div>
        </div>
        <div class="progress-step" data-step="3">
            <div class="step-number">3</div>
            <div class="step-label">Financial</div>
        </div>
        <div class="progress-step" data-step="4">
            <div class="step-number">4</div>
            <div class="step-label">Essay</div>
        </div>
        <div class="progress-step" data-step="5">
            <div class="step-number">5</div>
            <div class="step-label">Documents</div>
        </div>
    </div>

    <form id="scholarshipForm" action="process_application.php" method="POST" enctype="multipart/form-data">

        <!-- Section 1: Personal Information -->
        <div class="form-section active" data-section="1">
            <h3 class="section-header">👤 Personal Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>User Name <span class="required">*</span></label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user_username); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
                </div>
                
                <!-- Name Fields -->
                <div class="form-group">
                    <label>Last Name <span class="required">*</span></label>
                    <input type="text" name="last_name" placeholder="Enter your last name" required>
                </div>
                <div class="form-group">
                    <label>First Name <span class="required">*</span></label>
                    <input type="text" name="first_name" placeholder="Enter your first name" required>
                </div>
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" placeholder="Enter your middle name (optional)">
                </div>
                <div class="form-group">
                    <label>Suffix (Jr., Sr., III, etc.)</label>
                    <input type="text" name="suffix" placeholder="e.g., Jr., Sr., III">
                </div>
                
                <!-- Birth Information -->
                <div class="form-group">
                    <label>Date of Birth <span class="required">*</span></label>
                    <input type="date" name="date_of_birth" required>
                </div>
                <div class="form-group">
                    <label>Place of Birth <span class="required">*</span></label>
                    <input type="text" name="place_of_birth" placeholder="City, Province" required>
                </div>
                <div class="form-group">
                    <label>Gender <span class="required">*</span></label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Civil Status <span class="required">*</span></label>
                    <select name="civil_status" required>
                        <option value="">Select Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Widowed">Widowed</option>
                        <option value="Separated">Separated</option>
                    </select>
                </div>
                
                <!-- Contact & Address -->
                <div class="form-group">
                    <label>Contact Number <span class="required">*</span></label>
                    <input type="tel" name="contact" placeholder="09XX XXX XXXX" pattern="[0-9]{11}" required>
                </div>
                <div class="form-group">
                    <label>Alternative Contact (Optional)</label>
                    <input type="tel" name="alt_contact" placeholder="09XX XXX XXXX" pattern="[0-9]{11}">
                </div>
                
                <div class="form-group full-width">
                    <label>Complete Address <span class="required">*</span></label>
                    <input type="text" name="address" placeholder="House No., Street, Barangay, City, Province, Zip Code" required>
                </div>
                
                <!-- IDs -->
                <div class="form-group">
                    <label>Nationality <span class="required">*</span></label>
                    <input type="text" name="nationality" value="Filipino" required>
                </div>
                <div class="form-group">
                    <label>Religion <span class="required">*</span></label>
                    <input type="text" name="religion" placeholder="e.g., Roman Catholic, Christian, Islam" required>
                </div>
            </div>
        </div>

        <!-- Section 2: Education -->
        <div class="form-section" data-section="2">
            <h3 class="section-header">🎓 Educational Background</h3>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>School/University <span class="required">*</span></label>
                    <input type="text" name="school" placeholder="Name of Institution" required>
                </div>
                <div class="form-group">
                    <label>Course/Program <span class="required">*</span></label>
                    <input type="text" name="course" placeholder="e.g., BS Computer Science" required>
                </div>
                <div class="form-group">
                    <label>Year Level <span class="required">*</span></label>
                    <select name="year_level" required>
                        <option value="">Select Year</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                        <option value="5th Year">5th Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>GPA / GWA <span class="required">*</span></label>
                    <input type="number" name="gpa" step="0.01" min="1.0" max="5.0" placeholder="e.g., 1.25" required>
                </div>
            </div>
        </div>

        <!-- Section 3: Family & Financial -->
        <div class="form-section" data-section="3">
            <h3 class="section-header">💰 Family & Financial Status</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Annual Family Income <span class="required">*</span></label>
                    <select name="family_income" required>
                        <option value="">Select Range</option>
                        <option value="Below 100,000">Below ₱100,000</option>
                        <option value="100,000 - 200,000">₱100,000 - ₱200,000</option>
                        <option value="200,000 - 300,000">₱200,000 - ₱300,000</option>
                        <option value="300,000 - 500,000">₱300,000 - ₱500,000</option>
                        <option value="Above 500,000">Above ₱500,000</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Number of Dependents</label>
                    <input type="number" name="dependents" min="1" placeholder="Including yourself">
                </div>
            </div>
        </div>

        <!-- Section 4: Essay -->
        <div class="form-section" data-section="4">
            <h3 class="section-header">✍️ Personal Statement</h3>
            <div class="form-group full-width">
                <label>Why do you deserve this scholarship? <span class="required">*</span></label>
                <textarea name="essay" id="essay" maxlength="1000" placeholder="Describe your achievements, goals, and financial need..." required></textarea>
                <div class="char-counter" id="charCounter">0 / 1000 characters</div>
            </div>
        </div>

        <!-- Section 5: Documents -->
        <div class="form-section" data-section="5">
            <h3 class="section-header">📎 Required Documents</h3>
            <div class="form-group full-width">
                <label>Upload Documents <span class="required">*</span></label>
                <div class="file-upload" onclick="document.getElementById('document').click()">
                    <input type="file" id="document" name="document" accept=".pdf,.doc,.docx" required onchange="updateFileName(this)">
                    <div class="file-upload-icon">📄</div>
                    <p><strong>Click to upload</strong> or drag and drop</p>
                    <p style="font-size: 12px; color: #999;">PDF, DOC, DOCX up to 5MB</p>
                    <div class="file-name" id="fileName">No file selected</div>
                </div>
            </div>
            
            <div style="background: #fff3e0; padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #ff9800;">
                <strong>📋 Required Documents:</strong>
                <ul style="margin: 10px 0 0 20px; color: #666;">
                    <li>Transcript of Records (TOR)</li>
                    <li>Certificate of Enrollment</li>
                    <li>Income Tax Return / Certificate of Indigency</li>
                </ul>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="navigation-buttons">
            <button type="button" class="btn-nav btn-prev" id="prevBtn" onclick="changeStep(-1)" style="visibility: hidden;">← Previous</button>
            <button type="button" class="btn-nav btn-next" id="nextBtn" onclick="changeStep(1)">Next →</button>
            <button type="submit" class="btn-nav btn-submit" id="submitBtn" style="display: none;">Submit Application</button>
        </div>

    </form>
</div>

<script>
    let currentStep = 1;
    const totalSteps = 5;

    function changeStep(direction) {
        // Validate current step before proceeding
        if (direction === 1 && !validateStep(currentStep)) {
            return;
        }

        // Hide current section
        document.querySelector(`.form-section[data-section="${currentStep}"]`).classList.remove('active');
        document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.remove('active');
        
        if (direction === 1) {
            document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.add('completed');
        } else {
            document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.remove('completed');
        }

        // Update step
        currentStep += direction;

        // Show new section
        document.querySelector(`.form-section[data-section="${currentStep}"]`).classList.add('active');
        document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.add('active');

        // Update buttons
        document.getElementById('prevBtn').style.visibility = currentStep === 1 ? 'hidden' : 'visible';
        
        if (currentStep === totalSteps) {
            document.getElementById('nextBtn').style.display = 'none';
            document.getElementById('submitBtn').style.display = 'block';
        } else {
            document.getElementById('nextBtn').style.display = 'block';
            document.getElementById('submitBtn').style.display = 'none';
        }
    }

    function validateStep(step) {
        const section = document.querySelector(`.form-section[data-section="${step}"]`);
        const inputs = section.querySelectorAll('input[required], select[required], textarea[required]');
        
        for (let input of inputs) {
            if (!input.value.trim()) {
                input.focus();
                input.style.borderColor = '#f44336';
                setTimeout(() => {
                    input.style.borderColor = '#e3f2fd';
                }, 2000);
                return false;
            }
        }
        return true;
    }

    function updateFileName(input) {
        const fileName = document.getElementById('fileName');
        if (input.files && input.files[0]) {
            fileName.textContent = input.files[0].name;
            fileName.style.color = '#1976d2';
            fileName.style.fontWeight = '600';
        }
    }

    // Character counter for essay
    document.getElementById('essay').addEventListener('input', function() {
        const counter = document.getElementById('charCounter');
        const current = this.value.length;
        counter.textContent = `${current} / 1000 characters`;
        
        if (current > 900) {
            counter.classList.add('warning');
        } else {
            counter.classList.remove('warning');
        }
    });

    // Form submission validation
    document.getElementById('scholarshipForm').addEventListener('submit', function(e) {
        if (!validateStep(currentStep)) {
            e.preventDefault();
        }
    });
</script>

</body>
</html>