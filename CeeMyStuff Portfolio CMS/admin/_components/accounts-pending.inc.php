<section class="flex column center text-center">
            <? if ($account['Activation_Expired']) :?>
                <p class="red">The activation key for the account connected to <em><strong><?=$account['Temp_Email']?></strong></em> expired at 
                <strong><?=timestampToDate($account['Activation_Timestamp'])?></strong>.</p>
                <p>If you would like to resend the invitation, keep in mind that <strong>the activation key will expire 48 hours after it is sent</strong>.</p>
                <form method="post">
                    <input type="hidden" name="n_user_id" value="<?=$account['ID']?>">
                    <input type="hidden" name="account_email" value="<?=$account['Temp_Email']?>">
                    <div class="flex row wrap center">
                        <button class="green" type="submit" name="resend_account_invite"><i class="fi fi-rs-sparkles"></i> Resend Account Invite</button>
                        <button class="red" type="submit" name="admin_delete_account"><i class="fi fi-rs-trash"></i> Delete Account Invite</button>
                        <div id="modal-home"></div>
                    </div>
                </form>
            <? else :?>
                <p>This account has yet to be activated by the invitee. Their activation key expires at <?=timestampToDate($account['Activation_Timestamp'])?>.
            If they do not activate their account before this time, you will be able to send another invite.</p>
            <form method="post">
                <input type="hidden" name="n_user_id" value="<?=$account['ID']?>">
                <div class="flex row wrap center">
                    <button class="red" type="submit" name="admin_delete_account"><i class="fi fi-rs-trash"></i> Delete Account Invite</button>
                    <div id="modal-home"></div>
                </div>
            </form>
            <? endif;?>

        <script src="_js/modal.js"></script>
        <script>
            let modalHTML = `<h2>Are you sure you want to delete <?=($account['Username'])?>'s account invitation?</h2>
                            <p>If you choose to invite them again in the future, you will have to open a new invitation.</p>
                            <div class="flex">
                            <button type="submit" class="button red" name="admin_delete_account"/>Yes, delete this invitation</button>
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