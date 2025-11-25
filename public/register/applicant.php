<!-- ============================================================
     FILE: register-applicant.php
     USE: Applicant Registration Form (APPROVED VERSION-1 PLAN)
     RESPONSE #: 2
     ============================================================ -->

<!DOCTYPE html>
<html>
<head>
    <title>Applicant Registration - CareerJano</title>

    <style>
        body { 
            font-family: Arial; 
            background:#f2f2f2; 
        }
        .reg-container {
            width: 430px;
            margin: 40px auto;
            background: #ffffff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 7px 0 15px 0;
            border: 1px solid #cccccc;
            border-radius: 6px;
            font-size: 15px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        h2 {
            text-align:center;
            margin-bottom:15px;
        }
    </style>
</head>

<body>

<div class="reg-container">

    <h2>Create Applicant Account</h2>

    <!-- ============================================================
         FORM: Basic Registration Form 
         Submits to Step-3: send-email-otp.php (Email OTP mandatory)
         ============================================================ -->
    <form id="regForm" method="POST" action="send-email-otp.php">

        <!-- FULL NAME -->
        <label>Full Name</label>
        <input type="text" name="full_name" required>

        <!-- EMAIL (UNIQUE) -->
        <label>Email Address</label>
        <input type="email" name="email" required>

        <!-- MOBILE (NOT UNIQUE AS PER YOUR REQUIREMENT) -->
        <label>Mobile Number</label>
        <input type="text" name="mobile" required>

        <!-- GENDER -->
        <label>Gender</label>
        <select name="gender">
            <option value="">Select</option>
            <option>Male</option>
            <option>Female</option>
            <option>Other</option>
        </select>

        <!-- DOB -->
        <label>Date of Birth</label>
        <input type="date" name="dob">

        <!-- STATE -->
        <label>State</label>
        <input type="text" name="state">

        <!-- CITY -->
        <label>City</label>
        <input type="text" name="city">

        <!-- SUBMIT BUTTON -->
        <button type="submit">Register & Send OTP</button>

    </form>
    <!-- ============================================================ -->
</div>

</body>
</html>
