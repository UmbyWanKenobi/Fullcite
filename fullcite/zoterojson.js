tinymce.PluginManager.add('zoterojson', function(editor) {
  editor.addButton('zoterojson', {
    text: 'CSL JSON',
    icon: false,
    onclick: function() {
      editor.insertContent('[zoterojson]');
    }
  });
});
