<?php 
$admin_panel = true;
$loginArea = true;
include '../../components/info-head.php';
$page_title = 'CeeMyStuff Manual';
include '../_components/admin-header.inc.php';?>

<main class="user-manual">
    <h1>CeeMyStuff User Manual</h1>

    <hr>

    <section class="manual-sect" id="welcome">
        <h2>Welcome to CeeMyStuff!</h2>

        <p>CeeMyStuff is a lightweight portfolio CMS (aka, 'Content Management System'). Developed as an alternative to bulkier systems such as WordPress, its aim is to be simple to use and install, while retaining the versatility to customize with a little <a href="https://www.w3schools.com/html/html_intro.asp" target="_blank">HTML</a> and <a href="https://www.w3schools.com/css/css_intro.asp" target="_blank">CSS</a> know-how. Although originally created with visual artists in mind, it has potential for many uses, including music, text works, and basic blogging.</p>
        
    </section>

    <section class="manual-sect" id="manual-index">
        <ul class="manual-links">
            <li>
                <a href="basics.php">CeeMyStuff Basics</a>
            </li>
            <li class="invis">
                <a href="pages.php">Pages</a>
            </li>
        </ul>
    </section>
</main>

<?php
include $root.'/admin/_components/admin-footer.php';