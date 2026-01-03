<?php
/*
   FILE: /includes/widgets-panel.php
   PURPOSE:
   Right-side insights panel (AFTER LOGIN ONLY).
   Shows metrics: Profile Viewed, Application Status, Saved Jobs,
   Recommended Jobs, Alerts — including Courses & Experts section.
*/
?>

<div class="widgets-container">

    <h3 class="widget-title">Your Dashboard</h3>

    <ul class="widget-list">

        <li>
            <span>Profile Viewed</span>
            <strong id="w_profile_viewed">0</strong>
        </li>

        <li>
            <span>Application Status</span>
            <strong id="w_app_count">0</strong>
        </li>

        <li>
            <span>Saved Jobs</span>
            <strong id="w_saved_count">0</strong>
        </li>

        <li>
            <span>Recommended Jobs</span>
            <strong id="w_reco_count">0</strong>
        </li>

        <li>
            <span>Alerts</span>
            <strong id="w_alerts">0</strong>
        </li>

    </ul>

    <hr style="border-color: rgba(255,255,255,0.3); margin: 16px 0;">

    <div class="widget-subtitle">Quick Courses</div>
    <div class="widget-link">View Courses</div>

    <div class="widget-subtitle">Experts for You</div>
    <div class="widget-link">View Experts</div>

</div>
