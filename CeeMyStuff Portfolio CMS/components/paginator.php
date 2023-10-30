<div class="paginator">
    <a class="paginator-link <?=($backURL ? null : 'hidden')?>" href="<?show($firstURL)?>">First</a>
    <a class="paginator-link <?=($backURL ? null : 'hidden')?>" href="<?show($backURL)?>">Back</a>
    <?=show($pageDropdown)?>
    <a class="paginator-link <?=($nextURL ? null : 'hidden')?>" href="<?show($nextURL)?>">Next</a>
    <a class="paginator-link <?=($nextURL ? null : 'hidden')?>" href="<?show($lastURL)?>">Last</a>
</div>