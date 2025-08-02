tinymce.PluginManager.add('bibtex', function(editor) {
  editor.addButton('bibtex', {
    text: 'BibTeX',
    icon: false,
    onclick: function() {
      editor.insertContent('[bibtex]');
    }
  });
});
