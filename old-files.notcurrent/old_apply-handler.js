/* ============================================================
   FILE: /jobsweb/assets/js/apply-handler.js
   PURPOSE:
     - Handle Apply button click
     - AJAX request to apply-job.php
     - Prevent duplicates
     - Update UI instantly
   AUTHOR: Subhash + AI Assistant
   UPDATED: 23-Nov-2025
============================================================ */

$(document).ready(function() {

    // APPLY BUTTON CLICK
    $(document).on("click", ".applyJobBtn", function () {

        let jobId = $(this).data("jobid");
        let btn = $(this);

        if (!jobId) return;

        $.ajax({
            url: "/jobsweb/ajax/apply-job.php",
            type: "POST",
            data: { job_id: jobId },
            success: function (response) {

                // NOT LOGGED IN → redirect
                if (response.trim() === "login_required") {
                    alert("Please login to apply.");
                    window.location.href = "/jobsweb/public/login.php";
                    return;
                }

                // ALREADY APPLIED
                if (response.trim() === "already_applied") {
                    btn.prop("disabled", true)
                       .css({"background":"#28a745","color":"#fff"})
                       .text("APPLIED ✔");
                    return;
                }

                // SUCCESS
                if (response.trim() === "success") {
                    btn.prop("disabled", true)
                       .css({"background":"#28a745","color":"#fff"})
                       .text("APPLIED ✔");
                    alert("Application submitted successfully!");
                    return;
                }

                // ERROR
                alert("Something went wrong!");
            }
        });

    });

});
