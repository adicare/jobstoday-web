<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CareerJano Theme Preview</title>

  <!-- Load Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Inline All-Blue Theme -->
  <style>
    /* ===============================
       CareerJano Blue Harmony Theme
       =============================== */

    body {
      background-color: #d9ecff; /* very light blue background */
      color: #102a43;
      font-family: 'Segoe UI', Roboto, sans-serif;
    }

    /* Navbar */
    .navbar {
      background: linear-gradient(90deg, #003973, #00aaff);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .navbar-brand {
      color: #ffffff !important;
      font-weight: 700;
      font-size: 1.4rem;
      letter-spacing: 0.5px;
    }

    .navbar .nav-link {
      color: #e3f2fd !important;
      font-weight: 500;
      margin: 0 12px;
      transition: all 0.3s ease;
    }

    .navbar .nav-link:hover {
      color: #ffffff !important;
      text-shadow: 0 0 8px rgba(255,255,255,0.6);
    }

    /* Container Card */
    .card {
      background: linear-gradient(180deg, #f2f8ff, #e6f0ff);
      border: 1px solid #c6defa;
      border-radius: 16px;
      box-shadow: 0 6px 15px rgba(0, 80, 160, 0.1);
    }

    /* Accent Headings */
    .text-accent {
      color: #0066cc;
      font-weight: 700;
    }

    /* Buttons */
    .btn-primary {
      background-color: #007bff;
      border: none;
      box-shadow: 0 2px 5px rgba(0, 123, 255, 0.3);
    }

    .btn-primary:hover {
      background-color: #0056b3;
      box-shadow: 0 4px 8px rgba(0, 86, 179, 0.4);
    }

    .btn-danger {
      background-color: #e63946;
      border: none;
    }

    .btn-success {
      background-color: #2a9d8f;
      border: none;
    }

    /* Input */
    .form-control {
      border-radius: 8px;
      border: 1px solid #b0c9e8;
      background-color: #f9fcff;
      transition: all 0.2s ease;
    }

    .form-control:focus {
      border-color: #007bff;
      box-shadow: 0 0 6px rgba(0, 123, 255, 0.4);
    }

    /* Table */
    .table {
      background-color: #f9fcff;
      border-radius: 10px;
      overflow: hidden;
    }

    .table thead {
      background-color: #007bff;
      color: white;
    }

    .table tbody tr:hover {
      background-color: #e3f1ff;
    }

    /* Footer */
    footer {
      margin-top: 50px;
      padding: 12px 0;
      background: linear-gradient(90deg, #003973, #00aaff);
      color: white;
      text-align: center;
      font-size: 0.9rem;
      box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg px-4">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="#">CareerJano</a>
      <div class="navbar-nav ms-auto gap-3">
        <a class="nav-link" href="#">Dashboard</a>
        <a class="nav-link" href="#">Add Job</a>
        <a class="nav-link" href="#">My Jobs</a>
        <a class="nav-link" href="#">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container my-5">
    <div class="card p-4">
      <h2 class="text-accent mb-3">CareerJano Theme Preview</h2>
      <p>This page demonstrates your <strong>blue-harmony theme</strong> in action.</p>
      <hr>
      <h5 class="fw-semibold">Sample Buttons</h5>
      <button class="btn btn-primary w-100 mb-2">Primary Button</button>
      <button class="btn btn-danger w-100 mb-2">Danger Button</button>
      <button class="btn btn-success w-100 mb-2">Success Button</button>
      <hr>
      <h5 class="fw-semibold">Sample Input</h5>
      <input type="text" class="form-control" placeholder="Enter text...">
      <hr>
      <h5 class="fw-semibold">Sample Table</h5>
      <table class="table mt-3">
        <thead>
          <tr><th>ID</th><th>Name</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td>1</td><td>Example Item</td><td>Active</td></tr>
          <tr><td>2</td><td>Another Item</td><td>Pending</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <footer>
    <p>© <?= date('Y'); ?> CareerJano — Blue Harmony Theme Preview</p>
  </footer>
</body>
</html>
