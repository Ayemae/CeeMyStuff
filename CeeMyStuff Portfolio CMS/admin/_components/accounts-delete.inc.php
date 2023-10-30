<section>
    <h2>Delete Account</h2>

    <p>Deleting an account will remove all references to it. <em>This cannot be undone.</em><br/>
        <strong class="red">If you want to retain references to this account, such as for records of post authorship, considering deactivating it instead.</strong></p>

        <form id="delete-account" method="post" action="<?=$set['dir']?>/admin/settings.php">
            <?=$adminInputs?>
            <button class="red" type="submit" id="delete-account-btn" name="admin_delete_account">Delete Account</button>
            <div id="modal-home"></div>
        </form>

        <script src="_js/modal.js"></script>
        <script>
            let modalHTML = `<h2>Are you sure you want to delete <?=($account['Username'])?>'s account?</h2>
                            <p>This cannot be undone.</p>
                            <div class="flex">
                            <button type="submit" class="button red" name="admin_delete_account"/>Yes, delete this account</button>
                            <button class="button modal-close" onclick="event.preventDefault()"/>Never mind</button>
                            </div>`;
            const modalItemDelete = new Modal('modal-account-delete', modalHTML, false, false); 
            modalItemDelete.appendToForm('modal-home');

            document.getElementById('delete-account-btn').addEventListener('click', function(e) {
                e.preventDefault();
                modalItemDelete.trigger();
            }, false);
        </script>
</section>