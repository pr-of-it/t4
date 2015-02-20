CKEDITOR.dialog.add( 't4blockDialog', function( editor ) {
    return {
        title: 'Вставить блок',
        minWidth: 400,
        minHeight: 200,
        contents: [
            {
                id: 'tab-basic',
                elements: [
                    {
                        id: 'path',
                        type: 'select',
                        label: 'Блок',
                        items: [
                            ['Выберите блок', '']
                        ],
                        onLoad: function(element) {
                            element = this;
                            $.getJSON('/admin/blocks/default.json', function( data ) {
                                var blocksAvailable = data.blocksAvailable;
                                $.each(blocksAvailable, function(key, val) {
                                    element.add(val.title, key);
                                });
                            });
                        }
                    },
                    {
                        type : 'text',
                        id : 'params',
                        label : 'Параметры',
                        title : 'Параметры'
                    }
                ]
            }
        ],
        onOk: function() {
            var dialog = this;
            var path = dialog.getValueOf('tab-basic', 'path');
            var params = dialog.getValueOf('tab-basic', 'params');

            editor.insertHtml('<div class="t4block"><t4:block path="' + path + '" ' + params + ' /></div>');
        }
    };
});