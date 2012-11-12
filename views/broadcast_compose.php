<div class="wrap">
    <h2>Compose Broadcast</h2>

    <div class="subject field">

        <label for="subject">
            Subject of E-Mail
        </label>

        <input type="text" name="subject" id="subject">

    </div>

    <div id="tabs">

        <ul>
            <li><a href="#html-compose">E-Mail Body</a></li>
            <li><a href="#text-compose">Text Body</a></li>
        </ul>

        <div id="html-compose">
            <textarea id="compose-html" name="html_body" rows="5" cols="50"></textarea>
        </div>

        <div id="text-compose">
            <textarea style="width: 100%" name="text_body"></textarea>
        </div>

    </div>


</div>

    <script>
        jQuery(document).ready(function() {
        });
    </script>

<script>
    jQuery(function() {
        jQuery( "#tabs" ).tabs();
    });
</script>

