<?php

function CreateNewTemplateSwitcherButton($nameOfCKEditorObject,$nameOfTextArea,$number="")
{
    $pluginpath = str_replace(basename(__FILE__),"",__FILE__);	
	
    $templateFilesDirectory = "$pluginpath/htmltemplates/";
    $dir = opendir($templateFilesDirectory);
    $listOfTemplates = array();
    while ($item = readdir($dir))
    {
        
        if (preg_match("@(\.html|\.htm)$@",$item))//if the file ends with .html, add to the list.
        {
             $listOfTemplates[$item] = preg_replace("@(.html|.htm)@","",str_replace("_"," ",$item));
        }        
    }

    $home = home_url();
    $path = PLUGINDIR;
	
    $fullpath = $home."/?wpr-template="
    ?>
<script>
    var fullPath = "<?php echo $fullpath ?>";
//list of documents
function changeTemplate<?php echo $number?>(editorObject, nameOfTextArea,selectObject)
{
    var filename = selectObject.options[selectObject.selectedIndex].value;
    if (!filename)
        return;
    urloffiletoget = fullPath+filename;
    var return_value= jQuery.ajax({ type: "GET", url: urloffiletoget, async: false }).responseText;
    editorObject.setData(return_value);
        
}
function getCode()
{
    

}

function codeReady()
{
    
}
</script>
<?php
    
    $formItem = '<div style="float:right; display:block;">Choose Template: <select name="templateChanger" onchange="changeTemplate'.$number.'('.$nameOfCKEditorObject.',\''.$nameOfTextArea.'\',this)"><option></option>';
    foreach ($listOfTemplates as $filename=>$templateName)
    {
	$formItem .=   '<option value="'.$filename.'">'.$templateName.'</option>';
    }
    $formItem .= "</select></div>";
    echo $formItem;
}
