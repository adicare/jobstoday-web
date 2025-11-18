<!-- ===============================================================
     JAVASCRIPT: AJAX for job preview + pagination + APPLY button
     =============================================================== -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
/* ------------------------------------------
   AUTO LOAD FIRST JOB
------------------------------------------ */
$(document).ready(function() {
    $.post("/jobsweb/ajax/get-initial-job.php", {}, function(jobId) {

        if(jobId > 0){
            $('.job-row[data-id="'+jobId+'"]').addClass("job-active");

            $.post("/jobsweb/ajax/get-job.php", { id: jobId }, function(data){
                $("#jobPreview").html(data);
            });
        }

    });
});


/* ------------------------------------------
   CLICK TO LOAD JOB DETAILS
------------------------------------------ */
$(document).on("click", ".job-row", function() {

    let jobId = $(this).data("id");

    $(".job-row").removeClass("job-active");
    $(this).addClass("job-active");

    $.post("/jobsweb/ajax/get-job.php", { id: jobId }, function(data) {
        $("#jobPreview").html(data);
    });
});


/* ------------------------------------------
   PAGINATION
------------------------------------------ */
let page = 1;

$("#nextBtn").click(function(){
    page++;
    loadJobs();
});
$("#prevBtn").click(function(){
    if(page > 1) page--;
    loadJobs();
});

function loadJobs() {
    $.post("/jobsweb/ajax/get-jobs.php", { page: page }, function(data) {
        $("#jobList").html(data);
    });
}


/* ===============================================================
   APPLY BUTTON HANDLER
   PURPOSE:
   - Detect Apply button click
   - If user is logged in → apply via AJAX
   - If not logged in → user gets redirected by get-job.php
   =============================================================== */

$(document).on("click", ".applyJobBtn", function() {

    let jobId = $(this).data("jobid");

    $.post("/jobsweb/ajax/apply-job.php", 
        { job_id: jobId }, 
        function(response) {
            alert(response);  // You can beautify later
        }
    );
});
</script>
