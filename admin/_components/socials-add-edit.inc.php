<form id="add-new-social" method="post" enctype="multipart/form-data" action="<?=$set['dir']?>/admin/socials.php">
    <div class="space-btwn">
        <h2><?=($create ? 'Add New' : 'Edit')?> Social Media Link</h2>
        <? if ($edit) :?>
            <button id="delete-social-link" name="delete_social" class="small red" on-click="return false;">
                <i class="fi fi-rs-trash"></i> Delete Link
            </button>
        <? endif;?>
    </div>

    <noscript>Enabling Javascript is recommended for best performance.</noscript>

    <ul class="form-list social-fields">
        <? if ($create) :?>
        <li>
            <label for="platform">Platform Defaults:</label>
            <select class="select-cond-master" id="sm-platform" name="platform" onchange="socialsAuto(this)">
                <option value="" data-url="" data-icon="" selected>
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
                <option value="X/Twitter" data-url="https://x.com/[YOUR_HANDLE]" data-icon="/assets/icons/twitter.svg">
                    X/Twitter
                </option>
                <option value="YouTube" data-url="https://www.youtube.com/user/[YOUR_HANDLE]" data-icon="/assets/icons/youtube.svg">
                    YouTube
                </option>
            </select>
        </li>
                <br/>
        <? else :?>
            <input type="hidden" name="n_link_id" value="<?=$social['ID']?>">
        <? endif;?>

        <li>
            <label for="link_name">Link Name:</label>
            <input type="text" id="link-name" name="link_name" value='<?=($edit ? $social['Link_Name'] : null)?>'>
        </li>

        <li>
            <label for="link_name">Link Text:</label>
            <input type="text" id="link-text" name="link_text" value='<?=($edit ? $social['Link_Text'] : null)?>'>
        </li>

        <li>
        <label for="url">URL:</label>
            <input type="text" id="sm-url" name="url" value="<?=($edit ? $social['URL'] : null)?>" style="width:100%;max-width:450px;"/>
        </li>

        <li id="sm-icon-fields">
            <label for="icon_img_upload">Icon:</label>
            <input type="file" id="icon-img-upload" name="icon_img_upload" onchange="previewImg('icon-img-upload', 'icon-img', true)" value="<?(!isset($_POST['icon_img_upload']) ? null : show($_POST['icon_img_upload']))?>">
            <input type="hidden" id="icon-img-stored" name="icon_img_stored" value="<?show($edit ? $social['Icon'] : null);?>">
            <div class='flex'>
                <div>
                    Current: &nbsp; <em id="icon-img-none" class="<?=($create || ($edit && !$social['Icon']) ? null : 'invis')?>">none</em>
                </div>
                <figure id="sm-icon" class="sm-icon-wrapper"> 
                    <img id="icon-img-visual" class="visual<?=($edit && $social['Icon'] ? ' block' : ' invis')?>" src="<?=($edit && $social['Icon'] ? $set['dir'].$social['Icon'] : null)?>">
                    <input type="hidden" id="icon-img-preview" name="icon_img_preview" value="">
                </figure>
                <div>
                    <div id="icon-img-rmv-info" class="rvm-file-path-info invis">&#10060; File Removed</div>
                    <button id="icon-img-rmv-btn" type="button" class="small red <?=($edit && $social['Icon'] ? null : 'invis')?>" onclick="rmvFilePath(this, 'icon-img-stored', 'sm-icon-fields')">Remove Current Image</button>
                </div>
            </div>
        </li>
        <li>
            <label for="n_hidden">Hidden:</label>
            <input type="hidden" name="n_hidden" value="0">
            <input type="checkbox" id="hidden" name="n_hidden" value="1" <?=(isset($social['Hidden'])===true ? ($social['Hidden']==1 ? 'checked' : null) : null)?>>
        </li>
    </ul>
<button name="<?=($create ? 'add_new' : 'edit')?>_social">Submit</button>
<div id="modal-home"></div>
</form>


<script src="_js/socials-auto.js"></script>
<script src="_js/preview-img.js"></script>
<script src="_js/rmv-file-paths.js"></script>
<? if ($edit) :?>
<script src="_js/modal.js"></script>
<script>
let modalHTML = `<h2>Are you sure you want to delete this social link?</h2>
                <p>This cannot be undone.</p>
                <div class="flex">
                <button type="submit" class="button red" name="delete_social">Yes, delete this social link</button>
                <button type="button" class="button modal-close">Never mind</button>
                </div>`;
const modalMItemDelete = new Modal('social-link-delete', modalHTML, false, false);
modalMItemDelete.appendToForm('modal-home');

document.getElementById('delete-social-link').addEventListener('click', function(e) {
    e.preventDefault();
    modalMItemDelete.trigger();
}, false);
</script>
<? endif;?>