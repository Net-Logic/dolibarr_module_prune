# DoliStore sync Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Publishing a GitHub release of a Net-Logic module updates its DoliStore product (zip, version, compatibility, texts, categories, screenshots) by driving the DoliStore edit form, starting with the prune module (product id 646).

**Architecture:** A new shared repository `Net-Logic/dolistore-sync` holds a Node ESM tool: pure modules (version, data loading, validation, form values) that are unit-tested without a network, and a Playwright module that logs in and fills `edit-module-product.php?id=<id>`. A composite GitHub Action wraps it. Each module repository only carries a `dolistore/` data folder and a workflow job.

**Tech Stack:** Node 20 (ESM, `node:test`), `playwright` (chromium), `yaml`, `marked`, GitHub Actions.

**Spec:** `docs/superpowers/specs/2026-10-11-dolistore-sync-design.md` (branch `dolistore-sync-spec` of `Net-Logic/dolibarr_module_prune`).

## Global Constraints

- Final status of the product is never touched: the `status` radios are left as they are.
- Existing DoliStore products only; no creation of new products.
- English (`en.md`) is mandatory; other languages: `fr`, `de`, `it`, `es` (the languages of the form: `name_<lang>`, `short_description_<lang>`, `description_<lang>`).
- At most 3 categories, only labels existing in `CATEGORIES` (task 1); at most 5 screenshots; zip at most 80 MB.
- The module version is read from `$this->version` in the module descriptor, never stored in `dolistore/`. Dashes become dots (`2026-09-15` -> `2026.09.15`); the result must match `^\d+(\.\d+){0,2}$`.
- Secrets: `DOLISTORE_LOGIN`, `DOLISTORE_PASSWORD`, never printed, never written to artifacts.
- Manual `workflow_dispatch` defaults to `dry-run: true`; a real submission only on a published release.
- Code style: tabs for indentation in the module repositories (PHP); the Node tool uses 2 spaces (own repository).
- Commit messages in the new repository: `TYPE: Short description` with `NEW`, `FIX`, `CLOSE`. Do not add `Co-Authored-By` lines in Net-Logic repositories outside `dolibarr/`.

## Review Focus

- A language file with a title but an empty long description must fail validation (DoliStore refuses empty descriptions), not submit a half-empty product.
- A version like `2026-09-15` or `1.2.3-beta`: the first becomes `2026.09.15`, the second must fail (DoliStore only accepts digits and dots).
- A `product.yml` listing 4 categories, or a label with a typo (`Interfacage externes`): must fail before any browser is started, naming the offending label.
- Dolibarr `min` greater than `max` (`V21` / `V14`): must fail.
- A product that already has screenshots must never get new ones added or existing ones removed.
- A markdown long description containing `<script>`: it is the maintainer's own text, but the HTML must be escaped to text rather than executed in the DoliStore form (marked's output is passed through a strict allow-list in task 3).
- Login failure (wrong password) must stop with a clear message and never retry in a loop (account lockout).

## File Structure (new repository `Net-Logic/dolistore-sync`)

```
package.json
action.yml
bin/dolistore-sync.mjs        # CLI: parse args, call run()
src/constants.mjs             # LANGS, DOLIBARR_VERSIONS, CATEGORIES, limits
src/version.mjs               # readModuleVersion, toStoreVersion
src/markdown.mjs              # toHtml (marked + sanitising)
src/data.mjs                  # parseLangFile, loadModuleData
src/validate.mjs              # validate(data, ctx) -> string[]
src/form.mjs                  # buildFormValues(data, version) -> plain object
src/browser.mjs               # login, fillForm, uploadZip, uploadScreenshots, submit
src/run.mjs                   # orchestration, dry-run output
test/*.test.mjs               # node:test unit tests (no network)
test/fixtures/                # sample module with dolistore/ folder
README.md
```

In the module repositories (pilot: prune): `dolistore/product.yml`, `dolistore/en.md`, `dolistore/fr.md`, `dolistore/screenshots/` (optional), and a job in `.github/workflows/main.yml`.

---

### Task 1: Repository scaffold and constants

**Files:**
- Create: `package.json`, `.gitignore`, `src/constants.mjs`, `test/constants.test.mjs`

**Interfaces:**
- Produces: `LANGS` (`string[]`), `DOLIBARR_VERSIONS` (`Record<string,string>` label -> select value), `CATEGORIES` (`Record<string,string>` label -> checkbox value), `MAX_CATEGORIES`, `MAX_SCREENSHOTS`, `MAX_ZIP_BYTES`, `dolibarrNumber(label): number`.

- [ ] **Step 1: Create the repository and the package file**

```bash
gh repo create Net-Logic/dolistore-sync --private --clone
cd dolistore-sync
```

`package.json`:

```json
{
  "name": "dolistore-sync",
  "version": "0.1.0",
  "type": "module",
  "private": true,
  "bin": { "dolistore-sync": "bin/dolistore-sync.mjs" },
  "scripts": { "test": "node --test test/" },
  "engines": { "node": ">=20" },
  "dependencies": {
    "marked": "^12.0.0",
    "playwright": "^1.48.0",
    "yaml": "^2.5.0"
  }
}
```

`.gitignore`:

```
node_modules/
out/
```

Run: `npm install`

- [ ] **Step 2: Write the failing test** `test/constants.test.mjs`

```js
import test from 'node:test'
import assert from 'node:assert/strict'
import { DOLIBARR_VERSIONS, CATEGORIES, LANGS, dolibarrNumber } from '../src/constants.mjs'

test('Dolibarr versions map to the select values of the DoliStore form', () => {
  assert.equal(DOLIBARR_VERSIONS.V24, '120')
  assert.equal(DOLIBARR_VERSIONS.V10, '89')
  assert.equal(DOLIBARR_VERSIONS.V9, '109')
  assert.equal(Object.keys(DOLIBARR_VERSIONS).length, 19)
})

test('categories use the exact DoliStore labels', () => {
  assert.equal(CATEGORIES['Modules/Plugins'], '67')
  assert.equal(CATEGORIES['Interfaçage externes'], '74')
  assert.equal(CATEGORIES['Autres'], '80')
  assert.equal(Object.keys(CATEGORIES).length, 21)
})

test('languages and version number helper', () => {
  assert.deepEqual(LANGS, ['en', 'fr', 'de', 'it', 'es'])
  assert.equal(dolibarrNumber('V14'), 14)
})
```

- [ ] **Step 3: Run it to verify it fails**

Run: `npm test`
Expected: FAIL, `Cannot find module '../src/constants.mjs'`

- [ ] **Step 4: Write `src/constants.mjs`** (values read from the live form on 2026-10-11)

```js
export const LANGS = ['en', 'fr', 'de', 'it', 'es']

// Value of the <select name="version_min|version_max"> options of edit-module-product.php
export const DOLIBARR_VERSIONS = {
  V24: '120', V23: '116', V22: '112', V21: '101', V20: '100', V19: '98', V18: '97',
  V17: '96', V16: '95', V15: '94', V14: '93', V13: '92', V12: '91', V11: '90',
  V10: '89', V9: '109', V8: '108', V7: '107', V6: '106',
}

// Value of the <input type="checkbox" name="categories[]"> of the same form
export const CATEGORIES = {
  'Modules/Plugins': '67',
  'IA': '110',
  'GED - Gestion de documents': '70',
  'CRM - Gestion relation client': '68',
  'Comptabilité': '73',
  'Interfaçage externes': '74',
  'Produits, services ou stocks': '75',
  'Projets ou collaboratif': '76',
  'Reporting ou recherche': '77',
  'Interface utilisateurs': '78',
  'Portails ou ECommerce': '69',
  'POS (Point de Vente)': '71',
  'RH - Ressource humaines': '72',
  'Outils systèmes': '79',
  'Autres': '80',
  'Thèmes et modèles': '81',
  'Modèles de documents': '82',
  'Modèles de sites web': '84',
  'Thèmes': '83',
  'Documentation': '85',
  'Outils externes et appli mobiles': '86',
}

export const MAX_CATEGORIES = 3
export const MAX_SCREENSHOTS = 5
export const MAX_ZIP_BYTES = 80 * 1024 * 1024

export function dolibarrNumber(label) {
  return Number.parseInt(String(label).slice(1), 10)
}
```

- [ ] **Step 5: Run the tests**

Run: `npm test`
Expected: PASS (3 tests)

- [ ] **Step 6: Commit**

```bash
git add package.json package-lock.json .gitignore src test
git commit -m "NEW: scaffold and DoliStore form constants"
```

---

### Task 2: Version helpers

**Files:**
- Create: `src/version.mjs`, `test/version.test.mjs`

**Interfaces:**
- Produces: `readModuleVersion(descriptorSource: string): string`, `toStoreVersion(version: string): string` (throws `Error` on an invalid version).

- [ ] **Step 1: Write the failing test** `test/version.test.mjs`

```js
import test from 'node:test'
import assert from 'node:assert/strict'
import { readModuleVersion, toStoreVersion } from '../src/version.mjs'

test('reads the version from a Dolibarr module descriptor', () => {
  assert.equal(readModuleVersion("\t\t$this->version = '2.0.2';\n"), '2.0.2')
  assert.equal(readModuleVersion("$this->version = '2026-09-15';"), '2026-09-15')
})

test('refuses a descriptor without a numeric version', () => {
  assert.throws(() => readModuleVersion("$this->version = 'development';"), /version/i)
})

test('dashes become dots and only digits and dots are accepted', () => {
  assert.equal(toStoreVersion('2026-09-15'), '2026.09.15')
  assert.equal(toStoreVersion('1.2'), '1.2')
  assert.equal(toStoreVersion('3'), '3')
  assert.throws(() => toStoreVersion('1.2.3-beta'), /digits and dots/)
  assert.throws(() => toStoreVersion('1.2.3.4'), /digits and dots/)
})
```

- [ ] **Step 2: Run it to verify it fails**

Run: `node --test test/version.test.mjs`
Expected: FAIL, module not found

- [ ] **Step 3: Write `src/version.mjs`**

```js
export function readModuleVersion(descriptorSource) {
  const match = descriptorSource.match(/\$this->version\s*=\s*'([0-9][^']*)'/)
  if (!match) {
    throw new Error('Module version not found in the descriptor ($this->version = \'x.y.z\')')
  }
  return match[1]
}

export function toStoreVersion(version) {
  const converted = version.replace(/-/g, '.')
  if (!/^\d+(\.\d+){0,2}$/.test(converted)) {
    throw new Error(`Version "${version}" is not accepted by DoliStore: digits and dots only, x, x.y or x.y.z`)
  }
  return converted
}
```

- [ ] **Step 4: Run the tests**

Run: `node --test test/version.test.mjs`
Expected: PASS (3 tests)

- [ ] **Step 5: Commit**

```bash
git add src/version.mjs test/version.test.mjs
git commit -m "NEW: read and normalise the module version"
```

---

### Task 3: Markdown to HTML

**Files:**
- Create: `src/markdown.mjs`, `test/markdown.test.mjs`

**Interfaces:**
- Produces: `toHtml(markdown: string): string` — HTML limited to `p, br, h2-h4, ul, ol, li, strong, em, code, pre, a[href], table, thead, tbody, tr, th, td, blockquote, hr, img[src|alt]`; raw HTML in the source is escaped to text.

- [ ] **Step 1: Write the failing test** `test/markdown.test.mjs`

```js
import test from 'node:test'
import assert from 'node:assert/strict'
import { toHtml } from '../src/markdown.mjs'

test('converts basic markdown', () => {
  const html = toHtml('# Title\n\nSome **bold** text\n\n- one\n- two')
  assert.match(html, /<h1>Title<\/h1>|<h2>Title<\/h2>/)
  assert.match(html, /<strong>bold<\/strong>/)
  assert.match(html, /<li>one<\/li>/)
})

test('raw html in the source is escaped, never executed', () => {
  const html = toHtml('Hello <script>alert(1)</script> world')
  assert.doesNotMatch(html, /<script>/)
  assert.match(html, /&lt;script&gt;/)
})

test('javascript: links are neutralised', () => {
  const html = toHtml('[x](javascript:alert(1))')
  assert.doesNotMatch(html, /href="javascript:/i)
})
```

- [ ] **Step 2: Run it to verify it fails**

Run: `node --test test/markdown.test.mjs`
Expected: FAIL, module not found

- [ ] **Step 3: Write `src/markdown.mjs`**

```js
import { Marked } from 'marked'

const escapeHtml = (text) =>
  text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')

const marked = new Marked({
  gfm: true,
  renderer: {
    // Raw HTML typed in the markdown is shown as text
    html({ text }) {
      return escapeHtml(text)
    },
    link({ href, title, tokens }) {
      const safe = /^(https?:|mailto:|\/|#)/i.test(href) ? href : '#'
      const label = this.parser.parseInline(tokens)
      return `<a href="${escapeHtml(safe)}"${title ? ` title="${escapeHtml(title)}"` : ''}>${label}</a>`
    },
  },
})

export function toHtml(markdown) {
  return marked.parse(markdown, { async: false })
}
```

- [ ] **Step 4: Run the tests**

Run: `node --test test/markdown.test.mjs`
Expected: PASS (3 tests). If the `html` renderer receives a string in the installed marked version, change the signature to `html(text)` and use `escapeHtml(typeof text === 'string' ? text : text.text)`; keep the same assertions.

- [ ] **Step 5: Commit**

```bash
git add src/markdown.mjs test/markdown.test.mjs
git commit -m "NEW: markdown to html with escaped raw html and safe links"
```

---

### Task 4: Load the module data

**Files:**
- Create: `src/data.mjs`, `test/data.test.mjs`, `test/fixtures/goodmodule/dolistore/product.yml`, `test/fixtures/goodmodule/dolistore/en.md`, `test/fixtures/goodmodule/dolistore/fr.md`, `test/fixtures/goodmodule/dolistore/screenshots/b.png`, `test/fixtures/goodmodule/dolistore/screenshots/a.png`

**Interfaces:**
- Consumes: `LANGS` from `src/constants.mjs`.
- Produces:
  - `parseLangFile(text: string): { title: string, short: string, long: string }` (throws when the front matter is missing)
  - `loadModuleData(moduleDir: string): { product: object, langs: Record<string, {title,short,long}>, screenshots: string[] }` — `screenshots` are absolute paths sorted by file name.

- [ ] **Step 1: Create the fixtures**

`test/fixtures/goodmodule/dolistore/product.yml`:

```yaml
id: 646
dolibarr: { min: V10, max: V24 }
php: { min: "7.4", max: "8.5" }
access_days: 365
price: "0,00"
support: dolibarr@netlogic.fr
categories: [ "Modules/Plugins", "Outils systèmes" ]
keywords: [ prune, autoload ]
```

`test/fixtures/goodmodule/dolistore/en.md`:

```markdown
---
title: Prune libraries for Net Logic modules
short: Collection of libraries for Net Logic modules
---
Set of **libraries** used by the other Net Logic modules.
```

`test/fixtures/goodmodule/dolistore/fr.md`:

```markdown
---
title: Librairies Prune pour modules Net Logic
short: Collection de librairies pour modules Net Logic
---
Ensemble de **librairies** utilisées par les autres modules Net Logic.
```

Create two empty files `screenshots/b.png` and `screenshots/a.png` (`touch`).

- [ ] **Step 2: Write the failing test** `test/data.test.mjs`

```js
import test from 'node:test'
import assert from 'node:assert/strict'
import { basename, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { parseLangFile, loadModuleData } from '../src/data.mjs'

const fixture = join(fileURLToPath(new URL('.', import.meta.url)), 'fixtures', 'goodmodule')

test('parses the front matter and the body of a language file', () => {
  const lang = parseLangFile('---\ntitle: T\nshort: S\n---\nBody **x**\n')
  assert.deepEqual(lang, { title: 'T', short: 'S', long: 'Body **x**' })
})

test('a language file without front matter is refused', () => {
  assert.throws(() => parseLangFile('Just text'), /front matter/i)
})

test('loads product.yml, the languages present and the sorted screenshots', () => {
  const data = loadModuleData(fixture)
  assert.equal(data.product.id, 646)
  assert.deepEqual(Object.keys(data.langs), ['en', 'fr'])
  assert.equal(data.langs.fr.title, 'Librairies Prune pour modules Net Logic')
  assert.deepEqual(data.screenshots.map((p) => basename(p)), ['a.png', 'b.png'])
})

test('a module without a dolistore folder gives a clear error', () => {
  assert.throws(() => loadModuleData(join(fixture, 'nothing')), /dolistore/)
})
```

- [ ] **Step 3: Run it to verify it fails**

Run: `node --test test/data.test.mjs`
Expected: FAIL, module not found

- [ ] **Step 4: Write `src/data.mjs`**

```js
import { existsSync, readFileSync, readdirSync } from 'node:fs'
import { join } from 'node:path'
import { parse } from 'yaml'
import { LANGS } from './constants.mjs'

export function parseLangFile(text) {
  const match = text.match(/^---\r?\n([\s\S]*?)\r?\n---\r?\n?([\s\S]*)$/)
  if (!match) {
    throw new Error('Language file without front matter (--- title / short ---)')
  }
  const meta = parse(match[1]) || {}
  return {
    title: String(meta.title ?? '').trim(),
    short: String(meta.short ?? '').trim(),
    long: match[2].trim(),
  }
}

export function loadModuleData(moduleDir) {
  const dir = join(moduleDir, 'dolistore')
  if (!existsSync(join(dir, 'product.yml'))) {
    throw new Error(`No dolistore/product.yml in ${moduleDir}`)
  }
  const product = parse(readFileSync(join(dir, 'product.yml'), 'utf8')) || {}
  const langs = {}
  for (const lang of LANGS) {
    const file = join(dir, `${lang}.md`)
    if (existsSync(file)) {
      langs[lang] = parseLangFile(readFileSync(file, 'utf8'))
    }
  }
  const shotsDir = join(dir, 'screenshots')
  const screenshots = existsSync(shotsDir)
    ? readdirSync(shotsDir).filter((f) => /\.(png|jpe?g)$/i.test(f)).sort().map((f) => join(shotsDir, f))
    : []
  return { product, langs, screenshots }
}
```

- [ ] **Step 5: Run the tests**

Run: `node --test test/data.test.mjs`
Expected: PASS (4 tests)

- [ ] **Step 6: Commit**

```bash
git add src/data.mjs test/data.test.mjs test/fixtures
git commit -m "NEW: load the dolistore folder of a module"
```

---

### Task 5: Validation

**Files:**
- Create: `src/validate.mjs`, `test/validate.test.mjs`

**Interfaces:**
- Consumes: `CATEGORIES`, `DOLIBARR_VERSIONS`, `dolibarrNumber`, `MAX_CATEGORIES`, `MAX_SCREENSHOTS`, `MAX_ZIP_BYTES` (task 1); `toStoreVersion` (task 2); the `data` object of task 4.
- Produces: `validate(data, { version: string, zipBytes: number }): string[]` — the list of error messages, empty when valid.

- [ ] **Step 1: Write the failing tests** `test/validate.test.mjs`

```js
import test from 'node:test'
import assert from 'node:assert/strict'
import { validate } from '../src/validate.mjs'

const good = () => ({
  product: {
    id: 646,
    dolibarr: { min: 'V10', max: 'V24' },
    php: { min: '7.4', max: '8.5' },
    access_days: 365,
    price: '0,00',
    support: 'dolibarr@netlogic.fr',
    categories: ['Modules/Plugins', 'Outils systèmes'],
    keywords: ['prune'],
  },
  langs: { en: { title: 'T', short: 'S', long: 'L' }, fr: { title: 'T', short: 'S', long: 'L' } },
  screenshots: [],
})
const ctx = { version: '2.0.2', zipBytes: 1000 }

test('valid data gives no error', () => {
  assert.deepEqual(validate(good(), ctx), [])
})

test('English is mandatory', () => {
  const d = good(); delete d.langs.en
  assert.match(validate(d, ctx).join('\n'), /en\.md/)
})

test('a language with an empty long description fails', () => {
  const d = good(); d.langs.fr.long = ''
  assert.match(validate(d, ctx).join('\n'), /fr.*long/i)
})

test('more than 3 categories and unknown labels fail', () => {
  const d = good()
  d.product.categories = ['Modules/Plugins', 'Autres', 'Documentation', 'Thèmes']
  assert.match(validate(d, ctx).join('\n'), /3 categories/)
  d.product.categories = ['Interfacage externes']
  assert.match(validate(d, ctx).join('\n'), /Interfacage externes/)
})

test('Dolibarr min greater than max fails', () => {
  const d = good(); d.product.dolibarr = { min: 'V21', max: 'V14' }
  assert.match(validate(d, ctx).join('\n'), /min.*max/i)
})

test('unknown Dolibarr version and bad php version fail', () => {
  const d = good(); d.product.dolibarr = { min: 'V99', max: 'V24' }; d.product.php = { min: '7', max: '8.5' }
  const errors = validate(d, ctx).join('\n')
  assert.match(errors, /V99/)
  assert.match(errors, /php/i)
})

test('bad module version, zip too large and too many screenshots fail', () => {
  const d = good(); d.screenshots = ['1', '2', '3', '4', '5', '6']
  const errors = validate(d, { version: '1.2.3-beta', zipBytes: 81 * 1024 * 1024 }).join('\n')
  assert.match(errors, /1\.2\.3-beta/)
  assert.match(errors, /80 MB/)
  assert.match(errors, /5 screenshots/)
})

test('missing product keys are reported', () => {
  const d = good(); delete d.product.id; delete d.product.support
  const errors = validate(d, ctx).join('\n')
  assert.match(errors, /id/)
  assert.match(errors, /support/)
})
```

- [ ] **Step 2: Run it to verify it fails**

Run: `node --test test/validate.test.mjs`
Expected: FAIL, module not found

- [ ] **Step 3: Write `src/validate.mjs`**

```js
import {
  CATEGORIES, DOLIBARR_VERSIONS, MAX_CATEGORIES, MAX_SCREENSHOTS, MAX_ZIP_BYTES, dolibarrNumber,
} from './constants.mjs'
import { toStoreVersion } from './version.mjs'

const REQUIRED = ['id', 'dolibarr', 'php', 'access_days', 'price', 'support', 'categories']

export function validate(data, { version, zipBytes }) {
  const errors = []
  const p = data.product || {}

  for (const key of REQUIRED) {
    if (p[key] === undefined || p[key] === null || p[key] === '') {
      errors.push(`product.yml: "${key}" is missing`)
    }
  }
  if (p.id !== undefined && !/^\d+$/.test(String(p.id))) {
    errors.push(`product.yml: "id" must be numeric, got "${p.id}"`)
  }

  if (!data.langs || !data.langs.en) {
    errors.push('dolistore/en.md is mandatory (English description)')
  }
  for (const [lang, text] of Object.entries(data.langs || {})) {
    for (const [field, label] of [['title', 'title'], ['short', 'short description'], ['long', 'long description']]) {
      if (!text[field]) {
        errors.push(`dolistore/${lang}.md: empty ${label} (${field})`)
      }
    }
  }

  const categories = Array.isArray(p.categories) ? p.categories : []
  if (categories.length > MAX_CATEGORIES) {
    errors.push(`At most ${MAX_CATEGORIES} categories, got ${categories.length}`)
  }
  for (const label of categories) {
    if (!(label in CATEGORIES)) {
      errors.push(`Unknown category "${label}" (exact DoliStore label required)`)
    }
  }

  const dol = p.dolibarr || {}
  for (const side of ['min', 'max']) {
    if (dol[side] !== undefined && !(dol[side] in DOLIBARR_VERSIONS)) {
      errors.push(`Unknown Dolibarr version "${dol[side]}" for ${side} (V6 to V24)`)
    }
  }
  if (dol.min in DOLIBARR_VERSIONS && dol.max in DOLIBARR_VERSIONS && dolibarrNumber(dol.min) > dolibarrNumber(dol.max)) {
    errors.push(`Dolibarr min (${dol.min}) is greater than max (${dol.max})`)
  }

  const php = p.php || {}
  for (const side of ['min', 'max']) {
    if (php[side] !== undefined && !/^\d+\.\d+$/.test(String(php[side]))) {
      errors.push(`php.${side} must look like 7.4, got "${php[side]}"`)
    }
  }

  try {
    toStoreVersion(version)
  } catch (error) {
    errors.push(error.message)
  }
  if (zipBytes > MAX_ZIP_BYTES) {
    errors.push(`The zip is larger than 80 MB (DoliStore limit)`)
  }
  if ((data.screenshots || []).length > MAX_SCREENSHOTS) {
    errors.push(`At most ${MAX_SCREENSHOTS} screenshots, got ${data.screenshots.length}`)
  }
  return errors
}
```

- [ ] **Step 4: Run the tests**

Run: `node --test test/validate.test.mjs`
Expected: PASS (8 tests)

- [ ] **Step 5: Commit**

```bash
git add src/validate.mjs test/validate.test.mjs
git commit -m "NEW: validate the module data before opening a browser"
```

---

### Task 6: Form values

**Files:**
- Create: `src/form.mjs`, `test/form.test.mjs`

**Interfaces:**
- Consumes: `CATEGORIES`, `DOLIBARR_VERSIONS`, `LANGS` (task 1); `toStoreVersion` (task 2); `toHtml` (task 3); `data` (task 4).
- Produces: `buildFormValues(data, version): { text: Record<string,string>, selects: { version_min: string, version_max: string }, categories: string[] }`
  - `text` keys are the real form field names: `name_<lang>`, `short_description_<lang>`, `description_<lang>` (HTML), `version`, `phpmin`, `phpmax`, `validityduration`, `sale_price`, `contact_support`, `keywords`.

- [ ] **Step 1: Write the failing test** `test/form.test.mjs`

```js
import test from 'node:test'
import assert from 'node:assert/strict'
import { buildFormValues } from '../src/form.mjs'

const data = {
  product: {
    id: 646,
    dolibarr: { min: 'V10', max: 'V24' },
    php: { min: '7.4', max: '8.5' },
    access_days: 365,
    price: '0,00',
    support: 'dolibarr@netlogic.fr',
    categories: ['Modules/Plugins', 'Outils systèmes'],
    keywords: ['prune', 'autoload'],
  },
  langs: { en: { title: 'T', short: 'S', long: 'Some **bold**' } },
  screenshots: [],
}

test('maps the data to the real field names of the DoliStore form', () => {
  const v = buildFormValues(data, '2026-09-15')
  assert.equal(v.text.version, '2026.09.15')
  assert.equal(v.text.name_en, 'T')
  assert.equal(v.text.short_description_en, 'S')
  assert.match(v.text.description_en, /<strong>bold<\/strong>/)
  assert.equal(v.text.phpmin, '7.4')
  assert.equal(v.text.phpmax, '8.5')
  assert.equal(v.text.validityduration, '365')
  assert.equal(v.text.sale_price, '0,00')
  assert.equal(v.text.contact_support, 'dolibarr@netlogic.fr')
  assert.equal(v.text.keywords, 'prune, autoload')
  assert.deepEqual(v.selects, { version_min: '89', version_max: '120' })
  assert.deepEqual(v.categories, ['67', '79'])
})

test('languages without a file are not touched', () => {
  const v = buildFormValues(data, '1.0.0')
  assert.equal('name_fr' in v.text, false)
  assert.equal('description_de' in v.text, false)
})
```

- [ ] **Step 2: Run it to verify it fails**

Run: `node --test test/form.test.mjs`
Expected: FAIL, module not found

- [ ] **Step 3: Write `src/form.mjs`**

```js
import { CATEGORIES, DOLIBARR_VERSIONS, LANGS } from './constants.mjs'
import { toHtml } from './markdown.mjs'
import { toStoreVersion } from './version.mjs'

export function buildFormValues(data, version) {
  const p = data.product
  const text = {
    version: toStoreVersion(version),
    phpmin: String(p.php.min),
    phpmax: String(p.php.max),
    validityduration: String(p.access_days),
    sale_price: String(p.price),
    contact_support: String(p.support),
    keywords: (p.keywords || []).join(', '),
  }
  for (const lang of LANGS) {
    const l = data.langs[lang]
    if (!l) {
      continue
    }
    text[`name_${lang}`] = l.title
    text[`short_description_${lang}`] = l.short
    text[`description_${lang}`] = toHtml(l.long)
  }
  return {
    text,
    selects: {
      version_min: DOLIBARR_VERSIONS[p.dolibarr.min],
      version_max: DOLIBARR_VERSIONS[p.dolibarr.max],
    },
    categories: p.categories.map((label) => CATEGORIES[label]),
  }
}
```

- [ ] **Step 4: Run the full test suite**

Run: `npm test`
Expected: PASS (all tests of tasks 1 to 6)

- [ ] **Step 5: Commit**

```bash
git add src/form.mjs test/form.test.mjs
git commit -m "NEW: build the values of the DoliStore form from the module data"
```

---

### Task 7: Browser automation (login, fill, upload, submit)

**Files:**
- Create: `src/browser.mjs`, `src/run.mjs`, `bin/dolistore-sync.mjs`

This task drives the real site, so it is verified by a `dry-run` against the live form (step 6), not by unit tests. Field names below were read from the live form on 2026-10-11 (`name_en`, `short_description_en`, `description_en`, `keywords`, `version`, `version_min`, `version_max`, `phpmin`, `phpmax`, `validityduration`, `status`, `sale_price`, `contact_support`, `categories[]`, `virtual_product_file`, `dolibarr_terms`, submit button `#sub`; screenshots: `#addImageBtn2` + hidden `#fileInput2`; existing screenshot rows: `#table-body-edit tr[data-id]`).

**Interfaces:**
- Consumes: `loadModuleData` (task 4), `validate` (task 5), `buildFormValues` (task 6), `readModuleVersion` (task 2).
- Produces: `run({ moduleDir, descriptor, zipPath, dryRun, outDir, login, password }): Promise<{ submitted: boolean, values: object }>`; CLI `dolistore-sync --module <dir> --descriptor <relative path> --zip <file> [--dry-run] [--out <dir>]`.

- [ ] **Step 1: Write `src/browser.mjs`**

```js
const BASE = 'https://www.dolistore.com'

export async function login(page, { login: email, password }) {
  await page.goto(`${BASE}/login-register.php`)
  // The sign-in form is the first one with an e-mail and a password field
  await page.getByLabel(/e-?mail/i).first().fill(email)
  await page.locator('input[type="password"]').first().fill(password)
  await Promise.all([
    page.waitForLoadState('domcontentloaded'),
    page.getByRole('button', { name: /connexion|sign in|se connecter/i }).first().click(),
  ])
  if (page.url().includes('login-register.php') || await page.getByText(/authentification|invalid|incorrect/i).count()) {
    throw new Error('DoliStore login failed: check DOLISTORE_LOGIN / DOLISTORE_PASSWORD (not retrying, to avoid locking the account)')
  }
}

export async function openProduct(page, id) {
  await page.goto(`${BASE}/edit-module-product.php?id=${id}`)
  await page.locator('#product-creation-form').waitFor({ state: 'attached' })
}

async function setText(page, name, value) {
  const field = page.locator(`#product-creation-form [name="${name}"]`)
  if (await field.count() === 0) {
    throw new Error(`DoliStore form changed: field "${name}" not found`)
  }
  // Long descriptions are CKEditor instances backed by a textarea of the same name
  await page.evaluate(({ name: n, value: v }) => {
    const el = document.querySelector(`#product-creation-form [name="${n}"]`)
    const editor = window.CKEDITOR && window.CKEDITOR.instances && window.CKEDITOR.instances[n]
    if (editor) {
      editor.setData(v)
    }
    el.value = v
    el.dispatchEvent(new Event('input', { bubbles: true }))
    el.dispatchEvent(new Event('change', { bubbles: true }))
  }, { name, value })
}

export async function fillForm(page, values) {
  for (const [name, value] of Object.entries(values.text)) {
    await setText(page, name, value)
  }
  for (const [name, value] of Object.entries(values.selects)) {
    await page.locator(`#product-creation-form select[name="${name}"]`).selectOption(value)
  }
  // Categories: exactly the wanted ones are checked, others are unchecked
  await page.evaluate((wanted) => {
    for (const box of document.querySelectorAll('#product-creation-form input[name="categories[]"]')) {
      if (box.checked !== wanted.includes(box.value)) {
        box.click()
      }
    }
  }, values.categories)
  const terms = page.locator('#dolibarr_terms')
  if (!(await terms.isChecked())) {
    throw new Error('The seller terms checkbox is not accepted on this product: accept it once by hand')
  }
}

export async function uploadZip(page, zipPath) {
  await page.locator('#virtual_product_file').setInputFiles(zipPath)
}

// Adds screenshots only when the product has none yet; never touches existing images
export async function uploadScreenshots(page, files) {
  const existing = await page.locator('#table-body-edit tr[data-id]').count()
  if (existing > 0 || files.length === 0) {
    return { added: 0, skipped: existing > 0 ? 'product already has screenshots' : 'no screenshots in the repository' }
  }
  for (const file of files) {
    await page.locator('#fileInput2').setInputFiles(file)
  }
  return { added: files.length }
}

export async function submit(page, id, zipName) {
  await Promise.all([page.waitForLoadState('domcontentloaded'), page.locator('#sub').click()])
  await page.goto(`${BASE}/manage-products.php`)
  const row = page.locator('tr', { has: page.locator(`a[href="edit-module-product.php?id=${id}"]`) })
  const text = await row.first().innerText()
  if (!text.includes(zipName)) {
    throw new Error(`Submission not confirmed: "${zipName}" not found in the product row of manage-products.php`)
  }
}
```

- [ ] **Step 2: Write `src/run.mjs`**

```js
import { mkdirSync, readFileSync, statSync, writeFileSync } from 'node:fs'
import { basename, join } from 'node:path'
import { chromium } from 'playwright'
import { loadModuleData } from './data.mjs'
import { validate } from './validate.mjs'
import { buildFormValues } from './form.mjs'
import { readModuleVersion } from './version.mjs'
import { fillForm, login, openProduct, submit, uploadScreenshots, uploadZip } from './browser.mjs'

export async function run({ moduleDir, descriptor, zipPath, dryRun, outDir, login: email, password }) {
  const data = loadModuleData(moduleDir)
  const version = readModuleVersion(readFileSync(join(moduleDir, descriptor), 'utf8'))
  const errors = validate(data, { version, zipBytes: statSync(zipPath).size })
  if (errors.length > 0) {
    throw new Error(`Invalid dolistore data:\n- ${errors.join('\n- ')}`)
  }
  if (!email || !password) {
    throw new Error('DOLISTORE_LOGIN and DOLISTORE_PASSWORD are required')
  }
  const values = buildFormValues(data, version)
  mkdirSync(outDir, { recursive: true })

  const browser = await chromium.launch()
  const context = await browser.newContext()
  const page = await context.newPage()
  try {
    await login(page, { login: email, password })
    await openProduct(page, data.product.id)
    await fillForm(page, values)
    await uploadZip(page, zipPath)
    const screenshots = await uploadScreenshots(page, data.screenshots)
    // Never write the credentials: only the values sent to the form
    writeFileSync(join(outDir, 'dolistore-values.json'), JSON.stringify({ values, screenshots, zip: basename(zipPath) }, null, 2))
    await page.screenshot({ path: join(outDir, 'dolistore-form.png'), fullPage: true })
    if (dryRun) {
      return { submitted: false, values }
    }
    await submit(page, data.product.id, basename(zipPath))
    return { submitted: true, values }
  } catch (error) {
    await page.screenshot({ path: join(outDir, 'dolistore-error.png'), fullPage: true }).catch(() => {})
    throw error
  } finally {
    await browser.close()
  }
}
```

- [ ] **Step 3: Write `bin/dolistore-sync.mjs`**

```js
#!/usr/bin/env node
import { parseArgs } from 'node:util'
import { resolve } from 'node:path'
import { run } from '../src/run.mjs'

const { values } = parseArgs({
  options: {
    module: { type: 'string' },
    descriptor: { type: 'string' },
    zip: { type: 'string' },
    out: { type: 'string', default: 'out' },
    'dry-run': { type: 'boolean', default: false },
  },
})

for (const key of ['module', 'descriptor', 'zip']) {
  if (!values[key]) {
    console.error(`Missing --${key}`)
    process.exit(2)
  }
}

try {
  const result = await run({
    moduleDir: resolve(values.module),
    descriptor: values.descriptor,
    zipPath: resolve(values.zip),
    outDir: resolve(values.out),
    dryRun: values['dry-run'],
    login: process.env.DOLISTORE_LOGIN,
    password: process.env.DOLISTORE_PASSWORD,
  })
  console.log(result.submitted ? 'Product updated on DoliStore.' : 'Dry run done, nothing submitted.')
} catch (error) {
  console.error(error.message)
  process.exit(1)
}
```

Run: `chmod +x bin/dolistore-sync.mjs`

- [ ] **Step 4: Check the syntax and that the pure tests still pass**

Run: `node --check src/browser.mjs && node --check src/run.mjs && node --check bin/dolistore-sync.mjs && npm test`
Expected: no syntax error, all tests PASS

- [ ] **Step 5: Install the browser and verify the login selectors on the live page**

Run: `npx playwright install chromium`

Run, with the maintainer's credentials in the shell (never pasted in a chat or committed):

```bash
export DOLISTORE_LOGIN='...' DOLISTORE_PASSWORD='...'
node -e "
import('./src/browser.mjs').then(async ({ login }) => {
  const { chromium } = await import('playwright')
  const b = await chromium.launch(); const p = await b.newPage()
  await login(p, { login: process.env.DOLISTORE_LOGIN, password: process.env.DOLISTORE_PASSWORD })
  console.log('logged in:', p.url()); await b.close()
})"
```

Expected: `logged in: https://www.dolistore.com/myaccount.php`. If the e-mail label or the sign-in button name does not match, adjust the two locators in `login()` to what the page really exposes (inspect with `npx playwright codegen https://www.dolistore.com/login-register.php`) and re-run until it passes.

- [ ] **Step 6: Dry run on the pilot form**

Prepare the pilot data first (task 8), then:

```bash
node bin/dolistore-sync.mjs --module ../dolibarr_module_prune --descriptor core/modules/modPrune.class.php --zip ../dolibarr_module_prune/build/module_prune-2.0.2.zip --dry-run --out out
```

Expected: "Dry run done, nothing submitted." and `out/dolistore-form.png` + `out/dolistore-values.json`. Open the screenshot and compare each field with the values file. Nothing is saved on DoliStore in a dry run. If a field is reported as not found, the form changed: fix the field name in `fillForm`.

Verify specifically: (a) the CKEditor long descriptions show the new text, (b) exactly the three categories are checked, (c) the screenshot upload (`#fileInput2`) adds a row when a file is given: if it does not, inspect what the "Ajouter photo" button does on the edit page and adapt `uploadScreenshots` (this part of the form is not verified before this step).

- [ ] **Step 7: Commit**

```bash
git add src bin
git commit -m "NEW: drive the DoliStore edit form with Playwright (dry run and submit)"
```

---

### Task 8: GitHub Action and pilot data in prune

**Files (repository `dolistore-sync`):**
- Create: `action.yml`, `README.md`

**Files (repository `dolibarr_module_prune`):**
- Create: `dolistore/product.yml`, `dolistore/en.md`, `dolistore/fr.md`
- Modify: `.github/workflows/main.yml`

**Interfaces:**
- Consumes: the CLI of task 7.
- Produces: action `Net-Logic/dolistore-sync@main` with inputs `module-path` (default `.`), `descriptor` (required), `zip` (required), `dry-run` (default `'true'`), secrets passed as `login` and `password`.

- [ ] **Step 1: Write `action.yml` in `dolistore-sync`**

```yaml
name: DoliStore sync
description: Update a DoliStore product from the dolistore/ folder of a module and a release zip
inputs:
  module-path:
    description: Module repository path
    default: '.'
  descriptor:
    description: Module descriptor path inside the module (for the version)
    required: true
  zip:
    description: Package zip to upload
    required: true
  dry-run:
    description: "'true' fills the form and stops; 'false' submits"
    default: 'true'
  login:
    description: DoliStore login
    required: true
  password:
    description: DoliStore password
    required: true
runs:
  using: composite
  steps:
    - uses: actions/setup-node@v4
      with:
        node-version: '20'
    - name: Install the tool
      shell: bash
      working-directory: ${{ github.action_path }}
      run: |
        npm ci
        npx playwright install --with-deps chromium
    - name: Run
      shell: bash
      env:
        DOLISTORE_LOGIN: ${{ inputs.login }}
        DOLISTORE_PASSWORD: ${{ inputs.password }}
      run: |
        ARGS=""
        if [ "${{ inputs.dry-run }}" = "true" ]; then ARGS="--dry-run"; fi
        node "${{ github.action_path }}/bin/dolistore-sync.mjs" \
          --module "${{ inputs.module-path }}" \
          --descriptor "${{ inputs.descriptor }}" \
          --zip "${{ inputs.zip }}" \
          --out "${{ runner.temp }}/dolistore-out" $ARGS
    - name: Keep the form screenshot
      if: always()
      uses: actions/upload-artifact@v4
      with:
        name: dolistore-form
        path: ${{ runner.temp }}/dolistore-out
        if-no-files-found: ignore
        retention-days: 7
```

`README.md`: five lines saying what the tool does, the `dolistore/` layout of the spec, the two secrets, and that `dry-run` is the default.

Run: `git add action.yml README.md && git commit -m "NEW: reusable GitHub Action and readme" && git push -u origin main`

- [ ] **Step 2: Create the pilot data in prune** (`dolistore/product.yml`)

```yaml
id: 646
dolibarr: { min: V10, max: V24 }
php: { min: "7.4", max: "8.5" }
access_days: 365
price: "0,00"
support: dolibarr@netlogic.fr
categories: [ "Modules/Plugins", "Outils systèmes", "Interfaçage externes" ]
keywords: [ prune, autoload, v10, v11, v12, v13, v14, v15, v16, v17, v18, v19, v20, v21, v22, v23, v24 ]
```

`dolistore/en.md`:

```markdown
---
title: Prune libraries for Net Logic modules
short: Collection of libraries for Net Logic modules
---
Prune is the shared library module of the Net Logic modules (Microsoft Graph, Google API and others).

It bundles the PHP libraries they need, the OAuth token storage (tokens are encrypted in the database with Dolibarr's own encryption) and a cache layer (file system or Memcached).

**Install:** download the zip, then use Home > Setup > Modules > Deploy an external module.

**Support:** dolibarr@netlogic.fr
```

`dolistore/fr.md`:

```markdown
---
title: Librairies Prune pour modules Net Logic
short: Collection de librairies pour modules Net Logic
---
Prune est le module de librairies partagées des modules Net Logic (Microsoft Graph, Google API et autres).

Il regroupe les librairies PHP nécessaires, le stockage des jetons OAuth (chiffrés en base avec le chiffrement de Dolibarr) et une couche de cache (système de fichiers ou Memcached).

**Installation :** téléchargez le zip, puis utilisez Accueil > Configuration > Modules > Déployer un module externe.

**Support :** dolibarr@netlogic.fr
```

(The maintainer reviews these texts and the three categories before the first real submission; the DoliStore versions V10 to V24 and PHP 7.4 to 8.5 come from the module descriptor `need_dolibarr_version [10, 0]`, `phpmin [7, 4]` and the maximum tested.)

- [ ] **Step 3: Add the workflow job in prune** (`.github/workflows/main.yml`, a new job after `package`)

```yaml
  # Update the DoliStore product from dolistore/: dry run by hand, real submission on a published release
  dolistore:
    needs: package
    if: github.event_name == 'release' || github.event_name == 'workflow_dispatch'
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/download-artifact@v4
        with:
          name: module_prune
          path: build
      - name: Find the package
        run: echo "ZIP=$(ls build/module_prune-*.zip)" >> "$GITHUB_ENV"
      - uses: Net-Logic/dolistore-sync@main
        with:
          descriptor: core/modules/modPrune.class.php
          zip: ${{ env.ZIP }}
          dry-run: ${{ github.event_name == 'release' && 'false' || 'true' }}
          login: ${{ secrets.DOLISTORE_LOGIN }}
          password: ${{ secrets.DOLISTORE_PASSWORD }}
```

- [ ] **Step 4: Create the secrets** (by the maintainer, in the GitHub web UI, never in a chat): organisation `Net-Logic` > Settings > Secrets and variables > Actions > New organisation secret: `DOLISTORE_LOGIN` and `DOLISTORE_PASSWORD`, repository access limited to the module repositories using the tool.

- [ ] **Step 5: Dry run from GitHub**

Run the `CI` workflow of prune by hand (`workflow_dispatch`).
Expected: the `dolistore` job succeeds in dry-run, the artifact `dolistore-form` contains `dolistore-form.png` and `dolistore-values.json`. This checks the two unknowns of the spec: login from a GitHub runner, and no blocking by DoliStore. If the login is blocked, stop here: fall back to the local run of task 7 step 6.

- [ ] **Step 6: One real submission on the pilot** (maintainer's decision)

Publish a release of prune. Expected: the `dolistore` job runs with `dry-run: false`, the product 646 shows the new version and zip in "Gérer mes produits", and the status is unchanged. Compare the product page with `dolistore/`.

- [ ] **Step 7: Commit in prune**

```bash
git add dolistore .github/workflows/main.yml
git commit -m "NEW: DoliStore product data and sync job"
```

---

### Task 9: Roll out to the other products (one module at a time)

For each of: microsoftgraph 647, dolidocusign 845, emailtemplatemgr 807, imagegallery 1029, googlerecaptcha 1246, productsalepricebyproject 378, relancefactureglobale 792, dolitrashcan 846, yousignfordolibarr 659, doliesign 1512.

- [ ] **Step 1:** Read the current product form (`edit-module-product.php?id=<id>`) and copy its texts per language into `dolistore/<lang>.md` (English mandatory; replace English text in `de`/`it`/`es` only if a real translation exists, otherwise do not create that language file).
- [ ] **Step 2:** Write `dolistore/product.yml` with the id above, the Dolibarr and PHP range from the module descriptor, and at most 3 categories chosen among the 21 labels of `CATEGORIES` (proposed per module, validated by the maintainer).
- [ ] **Step 3:** Add the `dolistore` job to the module workflow (copy the prune job, change the descriptor path and artifact name).
- [ ] **Step 4:** Run the workflow by hand (dry-run), review the artifact, then do the real submission at the next release.
- [ ] **Step 5:** Commit and open a PR per module: `NEW: DoliStore product data and sync job`.

---

## Self-Review

**Spec coverage:** shared repository (task 1, 7, 8) · `dolistore/` folder with `product.yml`, language files, screenshots (task 4) · version from the descriptor (task 2, 7) · validations of the spec (task 5) · fill of every field and the zip (task 6, 7) · screenshots only when none exist, max 5 (task 5, 7) · status never touched (task 7: no code touches the `status` radios) · dry-run default on manual runs, real submission on release only (task 8) · secrets in the organisation (task 8) · pilot prune then rollout (task 8, 9). Not in the plan on purpose: creation of new products, generation of screenshots, price changes (out of scope in the spec).

**Placeholder scan:** the only unverified parts are marked as verification steps with an explicit procedure: the login locators (task 7 step 5) and the screenshot upload mechanics (task 7 step 6 c).

**Type consistency:** `loadModuleData` returns `{ product, langs, screenshots }`, used unchanged by `validate` and `buildFormValues`; `buildFormValues` returns `{ text, selects, categories }`, consumed by `fillForm`; `toStoreVersion` is used by `validate` and `buildFormValues`.
