<div class="wrap">

    <h2>Import Subscribers: Identify Columns</h2>

    Identify the columns in the csv file. Shown below are the first 100 rows. 
    <form action="admin.php?page=_wpr/importexport&subact=step5" method="post">

<?php

if (count($list) > 0)
{
    $columnCount =  count($list[0]);
        ?>
        <table class="widefat" style="width: auto;">
            <thead>
            <tr>
                <th></th>
                    <?php
                    for ($i=0;$i<$columnCount;$i++)
                    {
                ?><th>
                    <select class="colfield" name="column_<?php echo $i ?>">
                        <option></option>
                    <?php
                        foreach ($columns as $name=>$label)
                        {
                            ?>
                        <option value="<?php echo $name ?>"><?php echo $label ?></option>
                        <?php
                        
                        }
                        ?>
                    </select>
                </th>
                <?php
                    }

                    ?>
                
            </tr>
            </thead>
            <?php
            foreach ($list as $row)
            {
                ?>
            <tr>
                <td></td>
                <?php for ($i=0;$i<$columnCount;$i++)
                {
                  ?>
                <td><?php echo $row[$i]; ?></td>
                <?php
                }
                ?>
            </tr>
            <?php
            }

?>

        </table>
        <?php
}
else
{
    ?>The CSV File your provided was incomprehensible. Please modify it or use a different file.<?php
}
    ?>
            
        <input type="hidden" name="wpr_form" value="wpr_import_finish" />
        <input onclick="return _wpr_fifth_validateForm();" type="submit" value="Next: Finish &raquo;" class="button-primary">
    </form>    
</div>

<script>

    function _wpr_fifth_validateForm()
    {
        var name=false;
        var email=false;
        jQuery(".colfield").each(function() {

            if (this.value=="name")
                {
                    if (name==true)
                        {
                            alert("You have selected more than one field as the name field. Please select just one.");
                            return false;
                        }
                        else
                            name=true;
                }

                if (this.value=="email")
                {
                    if (email==true)
                        {
                            alert("You have selected more than one field as the email field. Please select one.");
                            return false;
                        }
                        else
                            email=true;
                }

        });

        if (name==true && email==true)
            {
                return true;
            }
            else
            {
                alert("You must select the columns corresponding to the name AND email addresses. Both haven't been selected.");
				return false;
            }
        
    }

</script>