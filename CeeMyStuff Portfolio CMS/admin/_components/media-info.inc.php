<?
    $rPath = $set['dir'].$dir.$file;
    $aPath = $baseURL.$dir.$file;
    $rootPath = $root.$dir.$file;
    $fType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (file_exists($rootPath) && $fType) :
    $filesize = formatSizeUnits(filesize($rootPath));
    if (in_array($fType, $imgTypes)) {
        $isImg=true;
        $display = '<figure class="media-image"><img src="'.$rPath.'" alt="'.$aPath.'"></figure>';
        $imgInfo = getimagesize($aPath);
    } else {
        $isImg=false;
        $display = '<a href="'.$rPath.'" target="_blank">[link]</a>';
        $imgInfo = null;
    }
    ?>

            <h1>File Info</h1>
        <section>
            <ul class="form-list">
                <li>
                    <b>File Name:</b> <?=$file?>
                </li>
                <li>
                    <b>Realtive Path:</b> 
                    <div class="word-break-all"><?=$rPath?></div>
                    <b>Absolute Path:</b> 
                    <div class="word-break-all"><?=$aPath?></div>
                </li>
                <li>
                    <b>File Size:</b> <?=$filesize?>
                </li>
                <li>
                    <b>Displays as Image:</b> <?=($isImg ? 'Yes' : 'No')?>
                    <? if ($isImg) :?>
                    <br/>
                        => <b>Image Dimensions:</b> <?=$imgInfo[3]?> pixels
                    <? endif;?>
                </li>
                <li>
                    <?=$display?>
                </li>
            </ul>
            <form method="post" action="<?=$set['dir']?>/admin/media-manager.php">
            <input type="hidden" name="name" value="<?=$file?>">
                <button class="red" id="delete-file" name="admin_delete_file">Delete File</button>
                <div id="modal-home"></div>
            </form>
        </section>


        <script src="_js/modal.js"></script>
<script>
let modalHTML = `<h2>Are you sure you want to delete '<?=($file)?>'?</h2>
                <p>Deleting this file in the Media Manager will not only make it disappear from this index.
                <em class="red">It will disappear from everywhere that you have used this file path</em>.</p>
                <div class="flex">
                <button type="submit" class="button red" name="admin_delete_file"/>That's fine, delete this file</button>
                <button class="button modal-close" onclick="event.preventDefault()"/>Never mind</button>
                </div>`;
const modalItemDelete = new Modal('modal-file-delete', modalHTML, false, false);
modalItemDelete.appendToForm('modal-home');

document.getElementById('delete-file').addEventListener('click', function(e) {
    e.preventDefault();
    modalItemDelete.trigger();
}, false);
</script>
    <? endif;?>