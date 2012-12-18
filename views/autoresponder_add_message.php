<div class="wrap compose_message" id="wpr-chrome">
    <h2>Add Message</h2>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">


    <div id="compose-sidebar">
        <strong>For autoresponder:</strong> <p id="autoresponder-name-sidebar"><?php echo $autoresponder->getName(); ?></p>
        <strong>To be sent on:</strong> <p id="autoresponder-offset"><input type="text" size="3" value="0" id="offset"/> days after subscription</p>
    </div>


    <input type="text" name="subject" id="post-compose-subject" value="Subject..."/>



    <div id="composition-section">
        <div id="compose_tabs">
            <ul>
                <li><a href="#rich_body">Rich Text</a></li>
                <li><a href="#text_body">Text</a></li>
            </ul>

            <div id="rich_body">
                <textarea name="rich_text" id="rich_body_field"></textarea>
            </div>
            <div id="text_body">
                <textarea name="text_body" id="text_body_field"></textarea>

            </div>
        </div>
    </div>





</form>
</div>