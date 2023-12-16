<section>
    <h2>CeeMyStuff Login</h2>

    <form method='post' action="<?=$uri?>">
        <ul class="form-list">
            <li>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" max-length="255">
            </li>
            <li>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" max-length="255">
            </li>
        </ul>
        <button name="login">Login</button>
    </form>

    <a href="<?=$adminURL?>account-settings.php?task=pw-reset">Click here if you forgot your password.</a>
</section>