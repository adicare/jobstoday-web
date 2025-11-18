<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>JobsToday – Your Career Starts Here</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
    /* GLOBAL THEME */
    body {
        background: #eaf3ff !important; /* lighter, modern background */
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }

    /* Header rows */
    .brand-logo { width:48px; }
    .profile-icon { width:44px; height:44px; border-radius:50%; object-fit:cover; }

    /* Menu bar */
    .navbar {
        background: linear-gradient(90deg, #004aad, #0077c8) !important; /* modern blue gradient */
    }
    .menu-row {
        background: linear-gradient(90deg, #004aad, #0077c8) !important;
    }

    /* Search */
    .search-container { max-width:900px; margin:24px auto; }
    .input-group-lg > .form-control { border-radius:12px 0 0 12px; }
    .input-group-lg > .btn { border-radius:0 12px 12px 0; background:#0A4DA3; /* solid blue like job page header */ border:none; }

    /* Cards: unified look with gradient top strip */
    .card {
        background: #ffffff !important; /* clean white cards */
        border: 1px solid #d6e4ff;
        border-top: 5px solid #004aad;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 40, 90, 0.08);
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .card:hover { transform:translateY(-6px); box-shadow:0 12px 28px rgba(12,38,63,0.12); }

    .feature-card .card-body { padding:1.6rem; }
    .feature-card h5 { margin-bottom:0.6rem; font-weight:700; }
    .feature-card p {
        color:#333; /* darker text for better readability */
    }

    /* Training / Expert */
    .training-card {
        background: #ffffff !important;
        border-top: 5px solid #004aad;
        border-radius: 16px;
    }
    .expert-card {
        background: #ffffff !important;
        color: #000 !important;
        border: 1px solid #d6e4ff;
        border-top: 5px solid #004aad;
        border-radius: 16px;
    }
    .expert-card img { width:84px; height:84px; object-fit:cover; border-radius:50%; }

    /* Responsive tweaks */
    @media (max-width:767px) {
        .brand-logo { width:40px; }
        .profile-icon { width:36px; height:36px; }
    }
    </style>
</head>

<body>

<!-- ROW1: Logo + Tagline (C1 VERSION - CLEAN, NO PROFILE ICON) -->
<div class="py-2" style="background:#0A4DA3; /* solid blue like job page header */">
  <div class="container text-start">
      <a class="navbar-brand d-flex flex-row align-items-center" href="#" style="color:white; text-decoration:none;">
          <img src="assets/logo-A.png" class="brand-logo me-2" alt="logo" />
          <span class="fw-bold fs-2 mt-1">JobsToday</span>
      </a>
      <div class="" style="color:#8fd1a8 !important;" mt-1" style="font-size:1rem; letter-spacing:0.3px;">
          <span style='color:#a8ffcf;'>Empowering Careers • Enabling Growth</span>
      </div>
  </div>
</div>

<!-- MENU moved up inside header -->
<div style="border-top:1px solid #ffffff;">
<nav class="navbar navbar-expand-lg menu-row" style="background:#0A4DA3; padding-top:0; padding-bottom:0;">
  <div class="container">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu2">
          <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="menu2">
          <ul class="navbar-nav ms-auto mb-1 fw-bold" style="font-size:0.95rem;">
              <li class="nav-item"><a class="nav-link text-white" href="#">Home</a></li>
              <li class="nav-item"><a class="nav-link text-white" href="#">Jobs</a></li>
              <li class="nav-item"><a class="nav-link text-white" href="#">Courses</a></li>
              <li class="nav-item"><a class="nav-link text-white" href="#">Employers</a></li>
              <li class="nav-item"><a class="nav-link text-white" href="#">Career Experts & Trainers</a></li>
              
              <li class="nav-item"><a class="nav-link text-white" href="#">Contact</a></li>
              
              <li class="nav-item"><a class="nav-link text-white" href="public/login.php">Login</a></li>
          </ul>
      </div>
  </div>
</nav>
</div>

<!-- HERO / Tagline -->
<div class="text-center py-4">
    <h2 class="fw-bold text-dark">Your Career Starts Here</h2>
    <p class="text-muted">Find your perfect job. Improve your skills. Grow your future.</p>
</div>

<!-- SEARCH -->
<div class="search-container">
    <div class="input-group input-group-lg shadow-sm">
        <input type="text" class="form-control" placeholder="Search for jobs (title, skills, company)" />
        <button class="btn btn-primary" style="background:#0A4DA3; border:none;">Search</button>
    </div>
</div>

<!-- FEATURE BLOCKS (4) -->
<div class="container mt-5">
    <div class="row g-4">

        <div class="col-md-3">
            <div class="card feature-card text-center p-3">
                <div class="card-body">
                    <h5 class="fw-bold">Browse Jobs</h5>
                    <p>Explore latest openings across industries and roles.</p>
                    <a href="#" class="btn btn-primary" style="background:#0A4DA3; border:none;">Search Jobs</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card feature-card text-center p-3">
                <div class="card-body">
                    <h5 class="fw-bold">Resume Builder</h5>
                    <p>Create a professional CV quickly with our guided builder.</p>
                    <a href="#" class="btn btn-primary" style="background:#0A4DA3; border:none;">Build Resume</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card feature-card text-center p-3">
                <div class="card-body">
                    <h5 class="fw-bold">For Employers</h5>
                    <p>Post jobs, search candidates, and manage applications.</p>
                    <a href="#" class="btn btn-primary" style="background:#0A4DA3; border:none;">Post a Job</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card feature-card text-center p-3">
                <div class="card-body">
                    <h5 class="fw-bold">Top Companies</h5>
                    <p>Discover leading employers and their open roles.</p>
                    <a href="#" class="btn btn-primary" style="background:#0A4DA3; border:none;">Get Companies</a>
                </div>

        

    </div>
</div>
<!-- TRAINING + CAREER EXPERT (two columns) -->
<div class="container mt-5 mb-5">
    <div class="row g-4">

        <div class="col-lg-8">
            <div class="card p-3 training-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Training Courses</h5>
                    <a href="#" class="text-primary">View Courses</a>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Project Management</span>
                        <span class="badge bg-success">Online · Certificate</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Python Data Analysis</span>
                        <span class="badge bg-info text-dark">Self-paced</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Digital Marketing</span>
                        <span class="badge bg-primary">Popular</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-lg-4">
    <div class="card p-3 expert-card text-start" style="min-height:100%;">
        <h5 class="fw-bold mb-3" style="color:#000;">Career Experts</h5>
        <div class="d-flex align-items-center mb-3">
            <img src="assets/mentor.png" alt="expert" style="width:70px; height:70px; border-radius:50%; object-fit:cover;" class="me-3" />
            <div>
                <h6 class="fw-bold mb-1" style="color:#000;">Joe Anderson</h6>
                <p class="small mb-0" style="color:#555;">Career Advisor</p>
            </div>
        </div>
        <a href="#" class="btn btn-primary w-100 mt-2" style="background:#0A4DA3; border:none;">Find Trainer & Advisor</a>
    </div>
</div>
    </div>
</div>

    </div>
</div>
</div>

    </div>
</div>

<!-- Footer -->
<footer class="py-4 bg-white border-top">
  <div class="container d-flex justify-content-between">
    <div>
      <strong>JobsToday</strong>
      <p class="mb-0 text-muted small">Empowering Careers • Enabling Growth</p>
    </div>
    <div class="text-end small">
      <div>Contact: support@jobstoday.local</div>
      <div class="mt-2">© 2025 JobsToday</div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
