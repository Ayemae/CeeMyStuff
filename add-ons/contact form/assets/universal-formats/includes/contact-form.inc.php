<form id="contact-mailto" class="grid-form contact-form" method="post" action="">
    <p>Please fill out all of the following fields:</p>
    <div>
        <label for="contact_email">Email:</label>
        <input type="email" id="contact-email" name="contact_email" value="" required>
    </div>
    <div>
        <label for="contact_message">Message:</label>
        <textarea id="contact-message" name="contact_message" required></textarea>
    </div>
    <div>
        <label for="contact_name">From:</label>
        <input type="text" id="contact-name" name="contact_name" value="" required>
    </div>
    <div class="button-area">
        <button type="submit" for="contact-mailto" name="send_message_from_contact">Submit Message</button>
    </div>
</form>