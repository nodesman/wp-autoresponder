<div class="tablenav">
    <div class="tablenav-pages">
        <div class="pagination-links">
            <?php

            if ($pages['start'] > 0)
            {
                if (false !== $pages['before']) {

                    ?>
                    <a href="<?php echo $base_url ?>&p=<?php echo $pages['before'] ?>" class="first-page">&laquo;</a>
                    <?php
                }
                ?>

                <?php
                $start = $pages['start'];
                $end = $pages['end'];

                for ($iter=$start;$iter<=$end;$iter++) {
                    ?>
                    <a class="next-page" href="<?php echo $base_url ?>&p=<?php echo $iter ?>"><?php echo $iter ?></a>
                    <?php
                }
                ?>
                <?php
                if (false !== $pages['after']) {

                    ?>
                    <a class="last-page" href="<?php echo $base_url ?>&p=<?php echo $pages['after'] ?>">&raquo;</a>
                    <?php
                }
            }
            ?>
        </div>
    </div>
</div>