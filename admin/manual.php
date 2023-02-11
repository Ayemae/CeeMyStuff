<?php 
$admin_panel = true;
$loginArea = true;
include '../components/info-head.php';
$page_title = 'Admin Panel';
include '_components/admin-header.inc.php';?>

<main class="user-manual">
    <h1>CeeMyStuff User Manual</h1>

    <hr>

    <section class="manual-sect" id="welcome">
        <h2>Welcome to CeeMyStuff!</h2>

        <p>CeeMyStuff is a lightweight portfolio CMS (aka, 'Content Management System'). Developed as an alternative to bulkier systems such as WordPress, its aim is to be simple to use and install, while retaining the versatility to customize with a little <a href="https://www.w3schools.com/html/html_intro.asp" target="_blank">HTML</a> and <a href="https://www.w3schools.com/css/css_intro.asp" target="_blank">CSS</a> know-how. Although originally created with visual artists in mind, it has potential for many uses, including music, text works, and basic blogging.</p>
        
    </section>
    <section class="manual-sect" id="pages-sections-items">
        <h2>Pages > Sections > Items</h2>

        <p>CeeMyStuff has three website building blocks: <em class="rose">Pages</em>, <em class="powblu">Sections</em>, and <em class="teal">Items</em>.</p>

        <blockquote>
            <b class="rose">Pages</b> - These are the webpages that make up your site. When you first install CeeMyStuff, it already has one Page: 'Home', which is where your visitors will land when they first direct to your site's web address. You can add as many other Pages as you see fit! More on Pages in the 'About Pages' section. (coming soon)<br/>
            <b class="powblu">Sections</b> - 'Sections' contain the content of your Pages. These can be standlone blocks of content, or they can contain Items. By default, Pages only have one Section, but you can enable your Pages to include as many Sections as you like. More on Sections in 'About Sections'. (coming soon)<br/>
            <b class="teal">Items</b> - 'Items' are individual pieces or entries that you can add to a Section. If you are using CeeMyStuff to create a drawing portfolio site, your 'Items' may be your individual drawings; if you are creating an archive of your music, your 'Items' may be your songs; if you are creating a blog, your 'Items' may be your blog entries; etc.! More on 'Items' in 'About Items'. (coming soon)
        </blockquote>

        <p>CeeMyStuff's three building blocks are a hierarchy as follows: <span class="rose">Pages</span> > <span class="powblu">Sections</span> > <span class="teal">Items</span>. In other words, <span class="rose">Pages</span> contain <span class="powblu">Sections</span>, which contain <span class="teal">Items</span>.</p>

        <p>All of these building blocks can be assigned a 'format' for how they display on the site. More on that in 'Formats'. (coming soon)</p>
    </section>
    <section class="manual-sect" id="about-pages" style="display:none;">
        <h2>About Pages</h2>

        <p>Pages are on the top of the hierarchy out of CeeMyStuff's three building blocks.</p>
         - multi-section pages!!!!!!!
         - formats?
         - menu link images??
         - 'hidden' pages
    </section>
    <section class="manual-sect" id="about-sections" style="display:none;">
        <h2>About Sections</h2>

        <p></p>
        - Section Display Settings!
    </section>
    <section class="manual-sect" id="about-items" style="display:none;">
        <h2>About Items</h2>

        <p></p>
    </section>
    <section class="manual-sect" id="about-items" style="display:none;">
        <h2>Formats</h2>

        <p></p>
    </section>

</main>

<?php
include '_components/admin-footer.php';