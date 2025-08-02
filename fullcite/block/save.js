import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
  const props = useBlockProps.save();

  // Icona SVG se attiva
  const icon = attributes.showIcon ? `<span class="fullcite-icon" aria-hidden="true">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
      width="1.5em" height="1.5em" fill="currentColor"
      style="vertical-align: text-bottom; margin-right: 0.15em;">
      <path d="M3 6v15a1 1 0 001.32.95l5.68-1.9 5.68 1.9A1 1 0 0017 21V6a1 1 0 00-1-1H4a1 1 0 00-1 1zM4 6h12v13.58l-5.32-1.78a1 1 0 00-.68 0L4 19.58V6z"/>
    </svg>
  </span>` : '';

  // Shortcode principale
  const shortcode = `[fullcite author="${attributes.author}" title="${attributes.title}" date="${attributes.date}" location="${attributes.location}" uri="${attributes.uri}" style="${attributes.style}"]`;

  // BibTeX se attivo
  const bibtex = attributes.showBibtex ? `
<pre><code>@misc{${attributes.author.toLowerCase().replace(/[^a-z0-9]/g, '')}${attributes.date},
  author = {${attributes.author}},
  title = {${attributes.title}},
  year = {${attributes.date}},
  institution = {${attributes.location}},
  url = {${attributes.uri}}
}</code></pre>
<button onclick="navigator.clipboard.writeText(this.previousElementSibling.innerText)">
📋 Copia BibTeX
</button>
` : '';

  // CSL JSON se attivo
  const csl = attributes.showCSL ? `
<pre><code>${JSON.stringify([{
    type: "article",
    title: attributes.title,
    author: [{ family: attributes.author }],
    issued: { "date-parts": [[attributes.date]] },
    publisher: attributes.location,
    URL: attributes.uri
  }], null, 2)}</code></pre>
<button onclick="navigator.clipboard.writeText(this.previousElementSibling.innerText)">
📋 Copia CSL-JSON
</button>
` : '';

  // Output finale
  return (
    <div {...props}
      dangerouslySetInnerHTML={{
        __html: icon + shortcode + bibtex + csl
      }}
    />
  );
}
