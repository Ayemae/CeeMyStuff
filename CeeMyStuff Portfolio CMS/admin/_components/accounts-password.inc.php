<section>
<h2>Change Password</h2>
    <form id="change-password" method="post" action="<?=$set['dir']?>/admin/settings.php">
    <?=$adminInputs?>
        <ul class="form-list">
            <?if (!$pullRank) :?>
                <li class="bracket bottom bright">
                    <label for="password">Current Password:</label>
                    <input type="password" id="old-password" name="old_password" max-length="255" class="width-300px"/>
                </li>
            <?endif;?>
            <li>
                <label for="password">New Password:</label>
                <input type="password" id="password" name="password" max-length="255" class="width-300px"/>
            </li>
            <li>
                <label for="password2">Confirm New Password:</label>
                <input type="password" id="password2" name="password2" max-length="255" class="width-300px"/>
            </li>
        </ul>

        <button name="<?=($pullRank ? 'admin_' : null)?>change_password">Submit New Password</button>
    </form>
</section>