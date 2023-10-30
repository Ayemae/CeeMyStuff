<div class="space-btwn">
    <h1><i class="fi fi-rs-following"></i> Social Media Links</h1>
    <a class="button" href="?task=create"><i class="fi fi-rs-plus"></i> New Link</a>
</div>

<section>
    <form id="social-media-form" method="post">
        <div class="admin-table social-media">
            <ul class="table-head">
                <li>Order</li>
                <li>Name</li>
                <li>Text</li>
                <li>URL</li>
                <li>Icon</li>
                <li>Hidden</li>
                <li><!--Tools--></li>
            </ul>
        <ul class="table-index">
            <?php foreach ($socials AS $social) :
                if (!($social['ID']===0 && (int)$set['has_rss']===0)) :?>
            <li class="social-media-item">
                <input type="hidden" name="option[<?show($social['ID'])?>][n_link_id]" value="<?=$social['ID']?>">
                <div>
                    <!--<i class="fi fi-rs-expand-arrows"></i>-->
                    <input type="number" class="socials-order" name="option[<?show($social['ID'])?>][n_index]" value="<?show($social['Index_Order'])?>" style="width:4em;">
                </div>
                <div>
                    <?show($social['Link_Name'])?>
                </div>
                <div>
                    <?show($social['Link_Text'])?>
                </div>
                <div class="word-break-all">
                    <a href="<?show($social['URL'])?>" target="_blank"><?show($social['URL'])?></a>
                </div>
                <div>
                    <? if ($social['Icon']) :?>
                    <img src="<?show($set['dir'].$social['Icon'])?>" alt="<?show($social['Link_Name'])?>">
                    <? else : ?>
                        n/a
                    <? endif;?>
                </div>
                <div>
                    <input type="hidden" name="option[<?show($social['ID'])?>][n_hidden]" value="0">
                    <input type="checkbox" id="hidden" name="option[<?show($social['ID'])?>][n_hidden]" value="1" <?=(isset($social['Hidden'])===true ? ($social['Hidden']==1 ? 'checked' : null) : null)?>>
                </div>
                <div>
                    <a href="?task=edit&id=<?=$social['ID']?>">Edit</a>
                </div>
            </li>
            <?php endif;
        endforeach; ?>
        </ul>
        </div>
    <button name="save_socials_list">Save</button>
    </form>
</section>

<hr>

<section>
    <h2>RSS Feed</h2>
    <p><a href="https://rss.com/blog/how-do-rss-feeds-work/">Learn more about RSS feeds here.</a></p>
    <form id="enable-rss" method="post">
        <ul class="form-list">
            <li class="flex">
                <? $checkbox = '';
                    if ($rss['Type']=== 'checkbox') :
                        if ($rss['Value'] === 'checked') {
                            $checkbox = 'checked=checked';
                        }
                        $rss['Value'] = 'checked'; 
                    endif;?>
                <label for="<?=($rss['Key']);?>">Enable RSS Feed:</label>
                <input type="hidden" name="<?=($rss['Key']);?>" value="">
                <input type="<?=($rss['Type']);?>" id="<?=($rss['Key']);?>" name="<?=($rss['Key']);?>" value="<?=($rss['Value'])?>" <?=($checkbox);?>>
            </li>
            <li>
                <a class="button" href="<?=$baseURL?>/admin/rss.php">Edit RSS Feed Settings</a>
            </li>
            <button name="save_settings">Save Settings</button>
        </ul>
    </form>
</section>

<script src="_js/enumerate.js"></script>
<script>
    enumerate('socials-order', 'class');
</script>