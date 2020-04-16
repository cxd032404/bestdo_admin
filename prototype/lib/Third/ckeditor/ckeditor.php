<?php
/*
 * Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

/*! \mainpage CKEditor - PHP server side intergation
 * \section intro_sec CKEditor
 * Visit <a href="http://ckeditor.com">CKEditor web site</a> to find more information about the editor.
 * \section install_sec Installation
 * \subsection step1 Include ckeditor.php in your PHP web site.
 * @code
 * <?php
 * include("ckeditor/ckeditor.php");
 * ?>
 * @endcode
 * \subsection step2 Create CKEditor class instance and use one of available methods to insert CKEditor.
 * @code
 * <?php
 * $CKEditor = new CKEditor();
 * echo $CKEditor->textarea("field1", "<p>Initial value.</p>");
 * ?>
 * @endcode
 */

class Third_ckeditor_ckeditor
{
    public static function render($name)
    {
        $script = "
        <script>
	ClassicEditor.create( document.querySelector( '#".$name."' ), {
		language: 'zh-cn',

		ckfinder: {
			// To avoid issues, set it to an absolute path that does not start with dots, e.g. '/ckfinder/core/php/(...)'
			uploadUrl: '/callback/upload.php?type=img',
		},

   		toolbar: [ 'heading', '|', 'bold',  'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote','imageUpload','insertTable','mediaEmbed','undo','redo'],

		heading: {
			options: [
				{ model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
				{ model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
				{ model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
			]
		}

	})
			.then( function( editor ) {
				// console.log( editor );
			} )
			.catch( function( error ) {
				console.error( error );
			});
</script>";
        echo $script;
    }


}


