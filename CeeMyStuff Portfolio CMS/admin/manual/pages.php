<?php 
$admin_panel = true;
$loginArea = true;
include '../../components/info-head.php';
$page_title = 'CeeMyStuff Manual';
include '../_components/admin-header.inc.php';?>

<main class="user-manual">
    <h1>CeeMyStuff User Manual: Pages</h1>

    <hr>

    <section class="manual-sect" id="about-pages">
        <h2>About <span class="rose">Pages</span></h2>

        <p>
            <b class="rose">Pages</b> are the core of your website, and the top of the hierarchy out of CeeMyStuff's three building blocks. 
            For any content to be displayed to your site's visitors, it must be contained within a Page.
        </p>
        <p>
            By default, pages contain only one <b class="powblu">Section</b>, but this can be toggled to allow Multi-Section in that page's settings.
        </p>

        <h3>Your Home Page</h3>

        <p> 
            When you first open up your installation of CeeMyStuff, it should already contain one page: your <strong>Home</strong> page, 
            which is where your visitors will land when they are first directed to your site's web address. You can edit your Home page as you
            like, including changing its name, but it is one of the only pages you cannot delete.
        </p>

        <h3>Creating & Editing Pages</h3>

        <p>
            Open the 'Pages' tab in your admin panel menu. The 'Page List' index will open. This will have a list of tabs for 
            all of your site's current pages.
        </p>
        <p>
            To <strong>create</strong> a new page, click the button labeled <strong>'+ New Page'</strong> in the upper right corner to open
            the 'Create New Page' form. To <strong>edit</strong> a page, open the tab of the page you would like to edit, and click the 
            <strong>'Edit Page Settings'</strong> button.
        </p>
        <div class="text-center">
            <img src="examples/create-edits-pages-btns.png" alt="An image showing the '+ New Page' and 'Edit Page Settings' buttons hightlighted with a blue outline.">
        </div>

        <div style="display:none;">
            - multi-section pages!!!!!!!
            - formats?
            - menu link images??
            - 'hidden' pages

            <h4>Page 'Formats'</h4>
            - applicable variables
        </div>
    </section>
</main>

<?php
include $root.'/admin/_components/admin-footer.php';