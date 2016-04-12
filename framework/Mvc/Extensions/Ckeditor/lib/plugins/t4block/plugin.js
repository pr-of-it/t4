CKEDITOR.plugins.add( 't4block', {
    icons: 't4block',
    init: function( editor ) {
        editor.addCommand( 't4block', new CKEDITOR.dialogCommand( 't4blockDialog' ) );

        editor.ui.addButton( 't4block', {
            label: 'Вставить блок T4',
            command: 't4block',
            toolbar: 't4block'
        });

        CKEDITOR.dialog.add( 't4blockDialog', this.path + 'dialogs/t4block.js' );
    },
    onLoad: function() {
        CKEDITOR.addCss(
            'div.t4block:after {' +
                'content: "T4:block";' +
                'color: #333;' +
                'background-color: #fc0;' +
                'padding: 5px;' +
                'font-size: 20px;' +
            '}' +
            'div.t4block {' +
                'padding: 10px;' +
            '}'
        );
    }
});