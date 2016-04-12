$(function(){
    $('textarea.editor').ckeditor({
        uiColor: '#FAFAFA',
        toolbarCanCollapse: true,
        toolbarGroups: [
            { name: 'document',    groups: [ 'mode'/*, 'document', 'doctools' */] },
            { name: 'clipboard',   groups: [ 'clipboard' ] },
            { name: 'undo',   groups: [ 'undo' ] },
            //{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
            { name: 'links' },
            { name: 'insert' },
            //{ name: 'forms' },
            { name: 'tools' },
            { name: 'others' },
            '/',
            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
            { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
            { name: 'styles' },
            { name: 'colors' },
            { name: 't4block'}
            //{ name: 'about' }
        ],
        extraPlugins: 't4block'
    });
});