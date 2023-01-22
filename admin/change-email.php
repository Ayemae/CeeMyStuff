<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Admin Panel : Change Email';
include '_components/admin-header.inc.php';
?>

<section>
    <h2>Change Email Address</h2>

    <form method='post'>
        <ul class="form-list">
            <li>
                <label for="email">New Email</label>
                <input type="email" id="email" name="new_email" max-length="255">
            </li>
        </ul>
        <button name="change_email">Submit</button>
    </form>
</section>