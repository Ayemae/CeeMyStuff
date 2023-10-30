<section>
    <h2>Change Username</h2>
    <em><b class="red">Developer's note:</b> Usernames don't do anything yet, and are for future features/settings.</em></p>

    <form id="change-username" method='post' action="<?=$set['dir']?>/admin/settings.php">
        <?=$adminInputs?>
        <ul class="form-list">
            <li>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" max-length="255" value="<?=$account['Username']?>" class="width-300px">
            </li>
        </ul>
        <button name="change_username">Submit Username</button>
    </form>
</section>