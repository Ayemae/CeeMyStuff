<section>
    <h2>Change Email Address</h2>
    <p>An email will be sent to verify the new address.</p>

    <form id="change-email" method='post' action="<?=$set['dir']?>/admin/settings.php">
    <?=$adminInputs?>
        <ul class="form-list">
            <li>
                <label for="email">New Email:</label>
                <input type="email" id="email" name="new_email" max-length="255" value="<?=$account['Email']?>" class="width-300px">
            </li>
        </ul>
        <button name="change_email">Submit New Email</button>
    </form>
</section>