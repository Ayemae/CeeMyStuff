<?php 
$admin_panel = true;
$loginArea = true;
include '../../components/info-head.php';
$page_title = 'CeeMyStuff Manual';
include '../_components/admin-header.inc.php';?>

<main class="user-manual cms-basics">

    <h1>CeeMyStuff Basics</h1>

    <hr>

    <section class="manual-sect" id="building-blocks">

        <h2>Your Settings</h2>
        
        <p>
            (Coming soon)
        </p>

        <h2>Building Blocks: Pages > Sections > Items</h2>

        <p>CeeMyStuff has three website building blocks: <em class="rose">Pages</em>, <em class="powblu">Sections</em>, and <em class="teal">Items</em>.</p>

        <blockquote>
            <b class="rose">Pages</b> - These are the webpages that make up your site. When you first install CeeMyStuff, it already has one Page: 'Home'. You can add as many other Pages as you see fit! More on Pages in the 'About Pages' section. (coming soon)<br/>
            <b class="powblu">Sections</b> - 'Sections' contain the content of your Pages. These can be standalone blocks of content, or they can contain 'Items'. By default, Pages only have one Section, but you can enable Pages to include as many Sections as you like. More on Sections in 'About Sections'. (coming soon)<br/>
            <b class="teal">Items</b> - 'Items' are individual pieces or entries that you can add to a Section. If you are using CeeMyStuff to create a drawing portfolio site, your 'Items' may be your individual drawings; if you are creating an archive of your music, your 'Items' may be your songs; if you are creating a blog, your 'Items' may be your blog entries; etc.! More on 'Items' in 'About Items'. (coming soon)
        </blockquote>

        <p>CeeMyStuff's three building blocks are a hierarchy as follows: <span class="rose">Pages</span> > <span class="powblu">Sections</span> > <span class="teal">Items</span>. In other words, <span class="rose">Pages</span> contain <span class="powblu">Sections</span>, which contain <span class="teal">Items</span>.</p>
    </section>


    <section class="manual-sect" id="automenu" style="display:none;">
        <h2>CeeMyStuff Automenu</h2>

        <p></p>
        - submenus

    </section>

    <section class="manual-sect" id="social-media-index" style="display:none;">
        <h2>Social Media Index</h2>

        <p></p>
        
    </section>

    <section class="manual-sect" id="media-manager" style="display:none;">
        <h2>Media Manager</h2>

        <p></p>
        
    </section>

    <section class="manual-sect" id="themes">
        <h2>Themes</h2>

        <p>
            Your site's <strong>theme</strong> contains all of the stylings and aesthetics of your CeeMyStuff site. 
            New themes can be installed by dropping them in the 'themes' folder via FTP, and can be changed in the Settings tab, under 'Display Settings'.
        </p>
        <p class="invis">
            To learn more about themes, go to the Themes Page.
        </p>
    </section>

    <section class="manual-sect" id="about-formats">
        <h2>Formats</h2>

        <p>Most aspects of a CeeMyStuff site &mdash; including Pages, Sections, and Items &mdash; can be assigned a 'format', which determine how they will display on the site.
            There are two different classes of formats: <strong>Universal Formats</strong>, which can be used with any theme, and <strong>Theme Formats</strong>, which
            are specific to one theme and can only be used with the theme it belongs to.
        </p>
        <p class="invis">
            To learn more about Formats, go to the Formats Page.
        </p>
    </section>

</main>

<?php
include $root.'/admin/_components/admin-footer.php';