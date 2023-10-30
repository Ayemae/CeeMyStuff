<section>
    <h2>Change Icon</h2>

    <p><strong>Icon image must be 120px in width and 120px in height (120px/120px)</strong>, and must be a jpg/jpeg, png, gif, or webp image.<br/>
    <em><b class="red">Developer's note:</b> Icons aren't used for anything yet, but feel free to upload one if you want!</em></p>

    <form id="change-icon" method='post' enctype="multipart/form-data" action="<?=$set['dir']?>/admin/settings.php">
        <?=$adminInputs?>
        <ul class="form-list">
            <li class="flex column">
                <label for="username">Upload New Icon:</label>
                <input type="file" id="icon-upload" name="icon_upload" onchange="previewImg('icon-upload', 'icon')">
                <input type="hidden" id="icon-stored" name="icon_stored" value="<?show($account['Icon_Path'])?>">
                <div class="sub-form-list">
                    <label>Current:</label>
                    <div id="icon_current" class="flex row user-icon-current-image-wrapper">
                        <img id="icon-visual" class="visual<?=($account['Icon_Path'] ? ' block' : ' invis')?>" src="<?=($account['Icon_Path'] ? $set['dir'].$account['Icon_Path'] : null)?>" alt="<?show($account['Username'])?>'s Full Icon">
                        <input type="hidden" id="icon-preview" name="icon_preview" value="">
                        <div id="icon-rmv-info" class="rvm-file-path-info invis">&#10060; Image Removed</div>
                        <em id="icon-none" class="<?=(!$account['Icon_Path'] ? null : 'invis')?>">none</em>
                    </div>
                    <? if ($account['Icon_Path']>'') :?>
                        <p><label>Image Paths:</label> 
                            <ul class="text-small">
                                <li>
                                    <?show($account['Icon_Path'] ? $set['dir'].$account['Icon_Path'] : '<i>None</i>')?>
                                </li>
                                <li>
                                    <?show($account['Icon_Path'] ? $set['dir'].insertFilenameTag($account['Icon_Path'],"50px") : '<i>None</i>')?>
                                </li>
                            </ul>
                        </p>
                    <? endif;?>
                        <button id="img-rmv-btn" type="button" class="small red<?=(!$account['Icon_Path'] ? ' invis' : null)?>" onclick="rmvFilePath(this, 'icon-stored', 'change-icon')">Remove Icon Image</button>
                    <div>
                </div>
            </li>
        </ul>
        <button name="change_user_icon">Submit Icon</button>
    </form>
</section>