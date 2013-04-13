<?php

add_action("_wpr_autoresponder_process_subscriber_day_zero", array("AutoresponderProcessor", "day_zero_for_subscriber"), 1, 1);