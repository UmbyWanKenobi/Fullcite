tinymce.PluginManager.add('fullcite', function(editor) {
  editor.addButton('fullcite', {
    text: 'Fullcite',
    icon: false,
    onclick: function() {
      editor.windowManager.open({
        title: 'Inserisci citazione bibliografica',
        body: [
          { type: 'textbox', name: 'author',   label: 'Autore' },
          { type: 'textbox', name: 'title',    label: 'Titolo' },
          { type: 'textbox', name: 'date',     label: 'Anno' },
          { type: 'textbox', name: 'location', label: 'Editore/Luogo' },
          { type: 'textbox', name: 'uri',      label: 'Link' },
          { type: 'listbox', name: 'style',    label: 'Stile', values: [
              { text: 'APA',     value: 'apa'     },
              { text: 'MLA',     value: 'mla'     },
              { text: 'AMA',     value: 'ama'     },
              { text: 'Chicago', value: 'chicago' }
          ]},
          { type: 'textbox',  name: 'zoteroid', label: 'Token Zotero (itemKey)' },
          { type: 'listbox',  name: 'showicon', label: 'Mostra Iconcina', values: [
              { text: 'Sì', value: 'yes' },
              { text: 'No', value: 'no'  }
          ]}
        ],
        onsubmit: function(e) {
          var s = '[fullcite';
          if (e.data.author)    s += ' author="'   + e.data.author   + '"';
          if (e.data.title)     s += ' title="'    + e.data.title    + '"';
          if (e.data.date)      s += ' date="'     + e.data.date     + '"';
          if (e.data.location)  s += ' location="' + e.data.location + '"';
          if (e.data.uri)       s += ' uri="'      + e.data.uri      + '"';
          if (e.data.style)     s += ' style="'    + e.data.style    + '"';
          if (e.data.zoteroid)  s += ' zoteroid="' + e.data.zoteroid + '"';
          if (e.data.showicon)  s += ' showicon="' + e.data.showicon + '"';
          s += ']';
          editor.insertContent(s);
        }
      });
    }
  });
});
