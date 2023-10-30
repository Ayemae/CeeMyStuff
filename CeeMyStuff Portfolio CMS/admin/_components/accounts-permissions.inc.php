<section>
    <h2>Change Permissions</h2>

    <p>Change this account's permissions.</p>
    <span class="red">
        <strong>IMPORTANT:</strong>
    </span>
    <ul class="no-margin">
        <li><b>Master Accounts</b> can invite people to make admin accounts, and change the information of all other accounts, 
        including email addresses and passwords.</li>
        <li><b>Deactivated Accounts</b> cannot log in.</li>
        
    </ul>
    <p>Excerise discretion when giving other accounts these permissions.</p>

    <form id="change-permissions" method="post">
    <?=$adminInputs?>
        <ul class="form-list">
            <li>
                <label for="n_permissions">Account Permissions:</label>
                <select name="n_permissions">
                    <option value="0" <?=(!$account['Permissions'] ? 'selected' : null)?>>Deactivated</option>
                    <option value="<?=$set['standard_permissions']?>" <?=formCmp($account['Permissions'],$set['standard_permissions'],'s')?>>Standard</option>
                    <option value="<?=$set['full_permissions']?>" <?=formCmp($account['Permissions'],$set['full_permissions'],'s')?>>Master</option>
                </select>
            </li>
        </ul>

        <button name="admin_change_permissions">Submit Permissions Changes</button>
    </form>
</section>