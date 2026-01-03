<!-- SEARCH BAR (Polished Rounded Version) -->
<form id="jobSearchForm" class="search-bar-full" onsubmit="event.preventDefault();">

    <input type="text" name="keywords" placeholder="Job title, skills…" />

    <select name="work_mode">
        <option value="">Work Type</option>
        <option value="On-site">On-site</option>
        <option value="Work From Home">Work From Home</option>
        <option value="Hybrid">Hybrid</option>
    </select>

    <select name="skill">
        <option value="">Skill</option>
        <?php if($skills): while($sk=$skills->fetch_assoc()): ?>
            <option value="<?= $sk['id'] ?>"><?= htmlspecialchars($sk['skill_name']) ?></option>
        <?php endwhile; endif; ?>
    </select>

    <select name="state" id="stateSelect">
        <option value="">State</option>
        <?php if($states): while($st=$states->fetch_assoc()): ?>
            <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['state_name']) ?></option>
        <?php endwhile; endif; ?>
    </select>

    <select name="city" id="citySelect">
        <option value="">City</option>
    </select>

    <button class="btn-search">Search</button>

</form>
