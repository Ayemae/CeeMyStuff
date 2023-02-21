<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Social Media';
include '_components/admin-header.inc.php';
//$socials = getSocials();

if (isset($_GET['task'])) {
    $task = $_GET['task'];
} else {
    $task = false;
}
if (isset($_GET['id'])) {
    $smID = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
} else {
    $smID = 0;
}
?>


<main>

<h1>Social Media Links</h1>

<noscript>Enabling Javascript is recommended for best performance.</noscript>

<form id="add-new-social">
    <div class="">
        <div>
            <label for="sm-platform">Platform:</label>
            <select id="sm-platform" name="platform" onchange="socialsAuto(this)">
                <option value="Custom" data-url="" data-icon="" selected>
                    Custom
                </option>
                <option value="Bandcamp" data-url="https://[YOUR_HANDLE].bandcamp.com/" data-icon="/assets/icons/bandcamp.svg">
                    Bandcamp
                </option>
                <option value="Instagram" data-url="https://www.instagram.com/[YOUR_HANDLE]/" data-icon="/assets/icons/instagram.svg">
                    Instagram
                </option>
                <option value="Facebook" data-url="https://www.facebook.com/[YOUR_HANDLE]/" data-icon="/assets/icons/facebook.svg">
                    Facebook
                </option>
                <option value="Ko-Fi" data-url="https://ko-fi.com/[YOUR_HANDLE]" data-icon="/assets/icons/ko-fi.svg">
                    Ko-Fi
                </option>
                <option value="LinkedIn" data-url="https://www.linkedin.com/in/[YOUR_HANDLE]/" data-icon="/assets/icons/linkedin.svg">
                    LinkedIn
                </option>
                <option value="Patreon" data-url="https://www.patreon.com/[YOUR_HANDLE]" data-icon="/assets/icons/patreon.svg">
                    Patreon
                </option>
                <option value="Spotify" data-url="https://open.spotify.com/user/[YOUR_ID_NUMBER]" data-icon="/assets/icons/spotify.svg">
                    Spotify
                </option>
                <option value="SoundCloud" data-url="https://soundcloud.com/[YOUR_HANDLE]" data-icon="/assets/icons/soundcloud.svg">
                    SoundCloud
                </option>
                <option value="Tumblr" data-url="https://[YOUR_HANDLE].tumblr.com/" data-icon="/assets/icons/tumblr.svg">
                    Tumblr
                </option>
                <option value="Twitch" data-url="https://www.twitch.tv/[YOUR_HANDLE]" data-icon="/assets/icons/twitch.svg">
                    Twitch
                </option>
                <option value="Twitter" data-url="https://twitter.com/[YOUR_HANDLE]" data-icon="/assets/icons/twitter.svg">
                    Twitter
                </option>
                <option value="YouTube" data-url="https://www.youtube.com/user/[YOUR_HANDLE]" data-icon="/assets/icons/youtube.svg">
                    YouTube
                </option>
            </select>
        </div>
        <div>
        <label for="sm-url">URL:</label>
            <input type="text" id="sm-url" name="url" style="width:100%;max-width:450px;"/>
        </div>

        <div>
        <label for="sm-icon">Icon:</label>
            <figure id="sm-icon" class="sm-icon-wrapper"></figure>
        </div>
    </div>
<button name="add_new_social">Submit</button>
</form>

<form id="social-media-form" method="post">
    <div class="social-media-table">
        <ul class="social-media-table-head">
            <li>Order</li>
            <li>Platform</li>
            <li>URL</li>
            <li>Icon Image</li>
            <li>Hidden</li>
        </ul>
    <ul class="socials-item-list">
        <?php foreach ($menu AS $option) :?>
        <li class="socials-item">
            <div>
                <!--<i class="fi fi-rs-expand-arrows"></i>-->
                <input type="number" class="menu-item-order" name="option[<?show($option['ID'])?>][n_index]" value="<?show($option['Index_Order'])?>">
            </div>
            <div>
                <?show($option['External_Link'] < 1 ? $option['Page_Name'] : $option['Ext_Link_Name'])?>
                <?show($option['Img_Path'] ? '<img src="'.$option['Img_Path'].'" alt="">' : null)?>
                <input type="hidden" name="option[<?show($option['ID'])?>][link]" value="<?show($option['Ext_Url'])?>">
                <!-- link to edit? -->
            </div>
            <div>
                <?php if ($option['ID']>0) :?>
                    <input type="hidden" name="option[<?show($option['ID'])?>][n_dropdown]" value="0">
                    <input type="checkbox" id="dropdown" name="option[<?show($option['ID'])?>][n_dropdown]" value="1" <?(!isset($option['In_Dropdown']) ? null : show((!$option['In_Dropdown'] ? null : 'checked')))?>>
                <?php else:?>
                    <input type="checkbox" title="Home pages cannot be in a dropdown." disabled>
                <?php endif;?>
            </div>
            <div>
                <input type="hidden" name="option[<?show($option['ID'])?>][n_hidden]" value="0">
                <input type="checkbox" id="hidden" name="option[<?show($option['ID'])?>][n_hidden]" value="1" <? echo (isset($option['Hidden'])===true ? ($option['Hidden']==1 ? 'checked' : null) : null)?>>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    </div>
<button name="save_menu">Save</button>
</form>

</main>

<script src="_js/socials-auto.js"></script>
<?php
include '_components/admin-footer.php';