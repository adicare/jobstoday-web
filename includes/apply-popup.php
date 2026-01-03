<?php
/*
   FILE: /includes/apply-popup.php
   PURPOSE:
   Job application popup (modal)
   Used when user clicks APPLY on job preview.
*/
?>

<div class="apply-popup" id="applyPopup">

    <div class="apply-popup-card">

        <button class="apply-close" onclick="closeApplyPopup()">×</button>

        <h2>Apply for Job</h2>

        <div class="apply-section">
            <label>Select resume to submit</label><br>

            <label>
                <input type="radio" name="resume_option" value="default" checked>
                Use my default resume 
                <small id="defaultResumeLabel">(loading...)</small>
            </label>

            <br><br>

            <label>
                <input type="radio" name="resume_option" value="upload">
                Upload a new resume
            </label>

            <input type="file" id="newResumeFile" class="apply-input" accept=".pdf,.doc,.docx">
            <small>Allowed: pdf, doc, docx — Max 3MB</small>
        </div>

        <div class="apply-section">
            <label>Additional note (optional)</label>
            <textarea id="applyNote" rows="3" class="apply-textarea"
              placeholder="A short note to employer (optional)"></textarea>
        </div>

        <div class="apply-actions">
            <button class="btn" onclick="closeApplyPopup()">Cancel</button>
            <button class="btn btn-primary" onclick="submitJobApplication()">Submit Application</button>
        </div>

        <div class="apply-msg" id="applyMsg"></div>

    </div>

</div>

<script>
function openApplyPopup(){
    document.getElementById("applyPopup").classList.add("show");
}

function closeApplyPopup(){
    document.getElementById("applyPopup").classList.remove("show");
}

function submitJobApplication(){
    let jobId = document.getElementById("applyPopup").dataset.job;

    let formData = new FormData();
    formData.append("job_id", jobId);
    formData.append("resume_option", document.querySelector("[name='resume_option']:checked").value);
    formData.append("note", document.getElementById("applyNote").value);

    let file = document.getElementById("newResumeFile").files[0];
    if(file){ formData.append("resume_file", file); }

    fetch("/jobsweb/ajax/apply-job.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.text())
    .then(msg => {
        document.getElementById("applyMsg").innerHTML = msg;

        if(msg.toLowerCase().includes("success")){
            document.getElementById("applyMsg").classList.add("success");
        } else {
            document.getElementById("applyMsg").classList.add("error");
        }
    });
}
</script>
