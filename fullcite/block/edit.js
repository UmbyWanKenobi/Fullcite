import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  SelectControl,
  ToggleControl
} from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
  const blockProps = useBlockProps();

  // 👓 Anteprima citazione
  const preview = `[${attributes.showIcon ? '📖 ' : ''}${attributes.author}, ${attributes.title}, ${attributes.location}, ${attributes.date}]`;

  // 🔬 BibTeX
  const bibtex = `@misc{${attributes.author.toLowerCase().replace(/[^a-z0-9]/g, '')}${attributes.date},
  author = {${attributes.author}},
  title = {${attributes.title}},
  year = {${attributes.date}},
  institution = {${attributes.location}},
  url = {${attributes.uri}}
}`;

  // 🧪 CSL JSON
  const csl = JSON.stringify([{
    type: "article",
    title: attributes.title,
    author: [{ family: attributes.author }],
    issued: { "date-parts": [[attributes.date]] },
    publisher: attributes.location,
    URL: attributes.uri
  }], null, 2);

  return (
    <div {...blockProps}>
      {/* 🎛️ Controlli nella sidebar */}
      <InspectorControls>
        <PanelBody title="Dettagli citazione">
          <TextControl label="Autore" value={attributes.author} onChange={(val) => setAttributes({ author: val })} />
          <TextControl label="Titolo" value={attributes.title} onChange={(val) => setAttributes({ title: val })} />
          <TextControl label="Anno" value={attributes.date} onChange={(val) => setAttributes({ date: val })} />
          <TextControl label="Luogo/Editore" value={attributes.location} onChange={(val) => setAttributes({ location: val })} />
          <TextControl label="Fonte URL" value={attributes.uri} onChange={(val) => setAttributes({ uri: val })} />
          <SelectControl
            label="Stile"
            value={attributes.style}
            options={[
              { label: 'Chicago', value: 'chicago' },
              { label: 'APA', value: 'apa' },
              { label: 'MLA', value: 'mla' },
              { label: 'AMA', value: 'ama' }
            ]}
            onChange={(val) => setAttributes({ style: val })}
          />
          <ToggleControl
            label="Iconcina libro 📖"
            checked={attributes.showIcon}
            onChange={(val) => setAttributes({ showIcon: val })}
          />
          <ToggleControl
            label="Anteprima BibTeX"
            checked={attributes.showBibtex}
            onChange={(val) => setAttributes({ showBibtex: val })}
          />
          <ToggleControl
            label="Anteprima CSL JSON"
            checked={attributes.showCSL}
            onChange={(val) => setAttributes({ showCSL: val })}
          />
        </PanelBody>
      </InspectorControls>

      {/* 👓 Anteprima blocco */}
      <p><strong>Anteprima citazione:</strong> {preview}</p>

      {attributes.showBibtex && (
        <div>
          <p><strong>BibTeX:</strong></p>
          <pre><code>{bibtex}</code></pre>
          <button onClick={() => navigator.clipboard.writeText(bibtex)}>📋 Copia BibTeX</button>
        </div>
      )}

      {attributes.showCSL && (
        <div>
          <p><strong>CSL JSON:</strong></p>
          <pre><code>{csl}</code></pre>
          <button onClick={() => navigator.clipboard.writeText(csl)}>📋 Copia CSL JSON</button>
        </div>
      )}
    </div>
  );
}
