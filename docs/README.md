# Hugo Documentation Site

This directory contains the project documentation built with [Hugo](https://gohugo.io/) using Hugo Modules.

## Local Development

### Prerequisites

- [Hugo Extended](https://gohugo.io/installation/) (v0.139.0 or later)
- [Go](https://go.dev/dl/) (1.21 or later, for Hugo Modules)

### Setup

```bash
cd docs

# Download theme module
hugo mod get

# Run development server
hugo server -D
```

Visit http://localhost:1313/thirtybees-docker-compose/ to view the site.

### Building

```bash
hugo --gc --minify
```

The built site will be in `docs/public/`.

### Updating Theme

```bash
hugo mod get -u
hugo mod tidy
```

## Deployment

Documentation is automatically deployed to GitHub Pages when changes are pushed to the `main` branch. The workflow is defined in `.github/workflows/hugo.yml`.

**Live site:** https://wilsonify.github.io/thirtybees-docker-compose/

## Structure

```
docs/
├── hugo.toml           # Hugo configuration
├── go.mod              # Go module definition
├── go.sum              # Go module checksums
├── archetypes/         # Content templates
├── content/            # Markdown content
│   ├── _index.md       # Homepage
│   ├── users/          # User documentation
│   ├── developers/     # Developer documentation
│   └── admins/         # Admin documentation
└── public/             # Built site (gitignored)
```

## Adding Content

Create new pages:

```bash
hugo new users/new-page.md
hugo new developers/new-page.md
hugo new admins/new-page.md
```

Edit the generated file in `content/` and set `draft: false` when ready to publish.
