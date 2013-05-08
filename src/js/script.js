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
											   skin: 'moono',
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

            var initializeTabbedInterface = function() {
                jQuery('#compose_tabs').tabs({
                    'name': 'wpr-add-autoresponder-message-tabs',
                    'expires': 1
                });
            };


            var initializeWYSIWYG = function() {
                "use strict";

                CKEDITOR.replace('rich_body_field', {
                    'width': '713px',
                    'height': '330px',
                    'baseHref': WPRConfig.ckeditor_baseHref,
                    'language': 'en',
                    'defaultLanguage': 'en',
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


                var editor = CKEDITOR.instances['rich_body_field'];

                editor.on('instanceReady', function() {
                    jQuery(".rich-text-loader").hide();
                });

            };

            var switchToAppropriateTab = function () {
                var htmlbody = document.getElementById('rich_body_field').value;
                var textbody = document.getElementById('text_body_field').value;

                if (htmlbody.length === 0 && textbody.length !==0  && (window.whetherSecondRendering === true || jQuery('.edit_autoresponder_message').length > 0) )
                {
                    jQuery("#compose_tabs").tabs('select', 1);
                }
            };

            var whetherSubjectEdited = false;

            initializeTabbedInterface();
            switchToAppropriateTab();
            initializeWYSIWYG();

        };

        var Dialog = new ComposePositionDialog();

    });
    
})();


(function manageAutorespondersScreen() {
    jQuery(document).ready(function () {
       
       if (0 === jQuery('.autoresponder-manage').length)
           return;
           
        
        var ManageCompositionDialog = function () {
        
              var addConfirmationDialogToAllDeleteButtons = function () {
                  
                  jQuery(".delete-autoresponder-message").click(function() {
                      return window.confirm("Are you sure you want to delete this follow-up message?");
                  });
              };
              
              addConfirmationDialogToAllDeleteButtons();
        };
        
        var Dialog = new ManageCompositionDialog();
        
    });
})()