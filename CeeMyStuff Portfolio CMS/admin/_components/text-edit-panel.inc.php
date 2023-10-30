<div id="text-edit-panel" class="js-check">
    <button type="button" class="edit-btn wrap-style-btn" value="h1">header</button>
    <button type="button" class="edit-btn wrap-style-btn" value="h2">sub-header</button>
    <button type="button" class="edit-btn wrap-style-btn" value="strong"><b>bold</b></button>
    <button type="button" class="edit-btn wrap-style-btn" value="em"><i>italic</i></button>
    <button type="button" class="edit-btn drop-style-btn" value="<hr/>">divider</button>
    <button type='button' class='edit-btn add-source-btn' value='a' onclick='srcBtn("<a href=\"URL_HERE\">", "TEXT_HERE", "</a>");'>link</button>
    <button type='button' class='edit-btn add-source-btn' value='img' onclick='srcBtn("<img src=\"", "IMAGE_URL_HERE", "\" alt=\"IMAGE_DESCRIPTION_HERE\"/>");'>image</button>
    <? if ($isItem && $sectInfo['Show_Item_Text']===3) :?>
        <button type="button" class="edit-btn drop-style-btn green" value="<!--truncate-->">truncate <i class="help icon"><i class="fi fi-rs-interrogation"></i>
                <article class="help-text">
                    Insert this in a single spot to stop the text at that position in the Section/Page view. 
                    This is an option that has been enabled in this Section's settings (<i>'Truncate Text at Custom Position'</i>).
                </article></i></button>
    <? endif; ?>
</div>