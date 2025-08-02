tinymce.PluginManager.add('fullcite', function(editor) {
  editor.addButton('fullcite', {
    text: 'Fullcite',
    icon: false,
    onclick: function() {
      editor.windowManager.open({
        title: 'Inserisci citazione Fullcite',
        body: [
          { type: 'textbox', name: 'zoteroid', label: 'Token Zotero (itemKey)' },
          { type: 'listbox', name: 'style', label: 'Stile', values: [
              { text: 'APA', value: 'apa' }
          ]},
          { type: 'listbox', name: 'showicon', label: 'Mostra iconcina', values: [
              { text: 'Sì', value: 'yes' },
              { text: 'No', value: 'no' }
          ]}
        ],
        onsubmit: function(e) {
          var shortcode = '[fullcite';
          if (e.data.zoteroid) shortcode += ' zoteroid="' + e.data.zoteroid + '"';
          if (e.data.style)     shortcode += ' style="'    + e.data.style    + '"';
          if (e.data.showicon)  shortcode += ' showicon="' + e.data.showicon + '"';
          shortcode += ']';
          editor.insertContent(shortcode);
        }
      });
    }
  });
});
