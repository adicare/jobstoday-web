<style>
.sidebar {
    width: 240px;
    min-height: 100%;
    background: #0a4c90;
    color: white;
    padding: 20px;
    border-radius: 8px;
}

.sidebar h4 {
    font-size: 20px;
    margin-bottom: 20px;
    font-weight: bold;
}

.sidebar a {
    display: block;
    padding: 12px;
    color: white;
    text-decoration: none;
    margin-bottom: 8px;
    border-radius: 5px;
    font-weight: bold;
    transition: 0.2s;
}

.sidebar a.active {
    background: #06376a;
}

.sidebar a:hover {
    background: #094983;
}
</style>


<div class="sidebar">
    <h4><i class="bi bi-person-lines-fill me-2"></i> My Profile</h4>

    <a href="/jobsweb/applicant/edit-profile.php"
       class="<?= basename($_SERVER['PHP_SELF'])=='edit-profile.php' ? 'active' : '' ?>">
       Personal Details
    </a>

    <a href="/jobsweb/applicant/upload-photo.php"
       class="<?= basename($_SERVER['PHP_SELF'])=='upload-photo.php' ? 'active' : '' ?>">
       Profile Photo
    </a>

    <a href="/jobsweb/applicant/upload-resume.php"
       class="<?= basename($_SERVER['PHP_SELF'])=='upload-resume.php' ? 'active' : '' ?>">
       Resume Upload
    </a>

    <a href="/jobsweb/applicant/skills.php"
       class="<?= basename($_SERVER['PHP_SELF'])=='skills.php' ? 'active' : '' ?>">
       Skills
    </a>

    <a href="/jobsweb/applicant/qualification.php"
       class="<?= basename($_SERVER['PHP_SELF'])=='qualification.php' ? 'active' : '' ?>">
       Qualification
    </a>

    <a href="/jobsweb/applicant/preferred-industry.php"
       class="<?= basename($_SERVER['PHP_SELF'])=='preferred-industry.php' ? 'active' : '' ?>">
       Preferred Industry
    </a>

    <a href="/jobsweb/applicant/experience.php"
       class="<?= basename($_SERVER['PHP_SELF'])=='experience.php' ? 'active' : '' ?>">
       Job Experience (Add/Edit)
    </a>

    <hr style="border-color: rgba(255,255,255,0.3);">

    <a href="/jobsweb/index.php">
        ← Back to My Home
    </a>
</div>
