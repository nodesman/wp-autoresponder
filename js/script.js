var listOfEditors= new Array();;



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
  document.getElementById("editorformitems-"+editorId).style.display=(!status)?"inline":"none";
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


(function composeMessage() {

    jQuery(document).ready(function() {


        if (0 === jQuery('.compose_message').size())
            return;

        var ComposePositionDialog = function() {

            var getCurrentSubjectFieldValue = function() {
                return jQuery('#post-compose-subject').val();
            };
            var bindSubjectFieldEvents = function() {

                jQuery('#post-compose-subject').on('click', function() {
                    if (whetherSubjectEdited === true)
                        return;
                    this.value= '';
                }).on('blur', function () {
                    if (whetherSubjectEdited === true) {
                        return;
                    }
                    this.value = subjectFieldDefaultValue;
                }).on('keydown', function() {
                    whetherSubjectEdited = true;
                });
            };

            var initializeTabbedInterface = function() {
                jQuery('#compose_tabs').tabs();
            };


            var initializeWYSIWYG = function() {
                "use strict";

                CKEDITOR.replace('rich_body_field', {
                    'width': '700px',
                    'toolbarGroups': [
                        { name: 'source', items: ['Source']},
                        { name: 'document' },
                        { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
                        { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
                        { name: 'links' },
                        '/',
                        { name: 'styles'},
                        { name: 'basicstyles'},
                        { name: 'colors' },
                        '/',
                        { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] }
                    ]
                });
            };



            var whetherSubjectEdited = false;
            var subjectFieldDefaultValue = getCurrentSubjectFieldValue();

            bindSubjectFieldEvents();
            initializeTabbedInterface();
            initializeWYSIWYG();

        };

        var Dialog = new ComposePositionDialog();



    });



})();