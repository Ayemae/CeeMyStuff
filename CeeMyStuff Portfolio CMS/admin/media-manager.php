<?php 
$admin_panel = true;
include_once '../components/info-head.php';
$page_title = 'Media Manager';
include '_components/admin-header.inc.php';
$dir = '/assets/uploads/media/';
$files = scandir('..'.$dir);
?>

<main>
<? if (isset($_GET['file'])) :
    $file = $_GET['file'];
    include_once '_components/media-info.inc.php';
else:
?>
<h1><i class="fi fi-rs-file-upload"></i> Media Manager</h1>

<p>The Media Manager is for uploading any extra files that you don't want or need to create a whole item for. 
    If you prefer, you can also upload fies to the 'media' folder within '<?=$set['dir']?>/assets/uploads/' via your [S]FTP client, and they will be visible here.</p>

    <form id="upload-form" method="post" enctype="multipart/form-data">

        <noscript>Enable Javascript for more dynamic features.</noscript>

        <ul class="form-list">
            <li>
                <label for="upload[]">Upload File(s):</label>
                <input type="file" id="upload" name="upload[]" multiple="multiple">
                <?if ($set['has_max_upld_storage']):?>
                    <p>Your max upload size for an individual file is <?=$set['max_upld_storage']?> MB.</p>
                <? endif;?>
            </li>
        </ul>
        <button type="submit" name="admin_media_upload">Submit</button>
    </form>

    <div class="admin-table media-manager">
    <ul class="desktop-only table-head">
        <li class="flex center">File Name</li>
        <li class="js-check flex row center">Copy Path
            <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                <article class="help-text">
                    Clicking these buttons will copy the file path to your clipboard, so that you can paste it elsewhere. 
                    A 'relative' path assumes that you are using the file within this website and will only work here.
                    An 'absolute' path will work anywhere, but keep in mind that it will still be using bandwidth from your site.
                </article>
            </i>
        </li>
        <li class="text-right">File Size</li>
        <li class="flex center">Info Link</li>
    </ul>
    <ul class="table-index">
        <? foreach ($files AS $file) :
        $rPath = $set['dir'].$dir.$file;
        $aPath = $baseURL.$dir.$file;
        $rootPath = $root.$dir.$file;
        $fType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
         if (file_exists($rootPath) && $fType) :
            $filesize = formatSizeUnits(filesize($rootPath));
            ?>
        <li class="table-index">
                <div class="word-break-all">
                    <b class="mobile-only">File Name:<br/></b> <?=$file?>
                </div>
                <div class="js-check flex row center wrap">
                    <a class="button small" data-copy="<?=$rPath?>" onclick="copyToClipboard(this)">
                        Relative
                    </a>
                    <a class="button small" data-copy="<?=$aPath?>" onclick="copyToClipboard(this)">
                        Absolute
                    </a>
                </div>
                <div class="text-right">
                    <b class="mobile-only">File Size:<br/></b> <?=$filesize?>
                </div>
                <div class="flex center">
                    <a href="?file=<?=$file?>">See Info</a>
                </div>
            </li>
        <? endif;
    endforeach;?>
    </ul>
    </div>
        
    </section>

    <?endif;?>
</main>

<script>
function copyToClipboard(input) {
    if (typeof input !== "string") {
        input = input.dataset.copy;
        if (!input) {
            input.innerText;
        }
    }
    const dummyElem = document.createElement("textarea");
    document.body.appendChild(dummyElem);
    dummyElem.value=input;
    dummyElem.select();
    document.execCommand("copy");
    document.body.removeChild(dummyElem);
}
</script>

<?php
include '_components/admin-footer.php';