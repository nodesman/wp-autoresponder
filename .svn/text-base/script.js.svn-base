var listOfEditors= new Array();;
/*
Contract: number->boolean
Precondition: The editor doesn't already exist.
Postcondition: The editor is created and the contents of the text editor, if any, is loaded into the wysiwyg editor.
*/


function createEditor(editorId)
{
     //there is already a editor, no need to create it again
     var editor = eval("listOfEditors["+editorId+"]");
     if (editor)
     {
     	return;     
     }

     //get the contents of the current text editor
/*
     var textAreaNumber= "htmlbody-"+editorId;
     var sourceCode = document.getElementById(textAreaNumber).value;
     document.getElementById("editor-"+editorId).removeChild(document.getElementById(textAreaNumber));
  */
     //create the editor
	document.getElementById("editor-"+editorId);
	listOfEditors[editorId] = CKEDITOR.replace( 'htmlbody-'+editorId, {
											   skin: 'office2003',
        toolbar :
        [
            


            ['Source','Maximize','-','ShowBlocks','-','Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
			'/',
			['Bold', 'Italic','Underline','Strike', '-', 'NumberedList', 'BulletedList','-','Subscript','Superscript','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', '-','Outdent','Indent','Blockquote','-', 'Link','Image','SpellChecker'],['Link','Unlink','Anchor',],

            '/',

            ['Format','Font','FontSize','TextColor','BGColor','-','-','Table','CreateDiv'],


        ]

    } );
	
}

function removeEditor(editorId)
{
      //get the editor
      if (!listOfEditors[editorId])
          return;
      var html = listOfEditors[editorId].getData();      
      listOfEditors[editorId].destroy();
	  
      delete listOfEditors[editorId];
      
      
      
}


function toggleStatus(editorId,status)
{
    //document.getElementById("editorformitems-"+editorId).style.display=(!status)?"inline":"none";
}

function toggleCustomization(editorId,status)
{
    jQuery("#customizationsform-"+editorId+" *").attr({ disabled: status });

    if (status)
        removeEditor(editorId);
    else
        createEditor(editorId);
}

function toggleHtmlBody(editorId,status)
{
    document.getElementById("htmlformitems-"+editorId).style.display=(status)?"inline":"none";
}

function changeTemplate(editor,nameOfTextArea)
{
      if (editor)
      {
          //
      }
}

