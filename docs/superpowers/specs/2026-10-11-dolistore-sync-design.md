# DoliStore sync: update a DoliStore product from a GitHub release

Date: 2026-10-11. Status: draft, to be reviewed. Pilot module: prune (DoliStore product id 646).

## Goal

Publishing a GitHub release of a Net-Logic module updates its DoliStore product (zip, version, compatibility, texts, categories, screenshots) without manual work. The texts and parameters live in the module repository, not only in the DoliStore web form.

DoliStore has no API and no token. The only way to update a product is the logged-in form
`edit-module-product.php?id=<id>` ("Ajouter ou mettre à jour un module ou un addon"). The sync therefore drives that form with a browser (Playwright).

## Decisions already taken

- Final status: **leave the product status as it is** (never touch the Actif / Désactivé / "Soumettre pour approbation" radio).
- First version: **one pilot module (prune)**, then the other products once the tool is proven.
- Architecture: **one shared tool repository**, plus a data folder in each module repository.

## Out of scope (first version)

- Creating new DoliStore products ("Soumettre un nouveau module"). Existing products only.
- Generating screenshots. Only images committed by the module author are uploaded.
- Price changes, sales statistics, "Related price".
- Any DoliStore account other than the Net-Logic one.

## Architecture

### Shared repository `Net-Logic/dolistore-sync` (new)

- `sync.mjs`: Playwright script. Reads the module `dolistore/` folder, logs in, fills the form, uploads, optionally submits.
- `action.yml`: reusable composite GitHub Action `Net-Logic/dolistore-sync@main`, inputs: `module-path`, `zip`, `dry-run`.
- Playwright and its dependencies live only here (modules stay free of Node dependencies).

### In each module repository

```
dolistore/
  product.yml        # parameters (below)
  en.md              # title, short and long description (English: mandatory)
  fr.md              # one file per language: fr, de, es, it, ...
  screenshots/       # 0 to 5 images (png/jpg)
.github/workflows/main.yml   # a job calling the action on `release`
```

`product.yml`:

```yaml
id: 646                    # DoliStore product id (edit-module-product.php?id=646)
dolibarr: { min: V14, max: V24 }   # DoliStore values V6..V24
php: { min: "7.4", max: "8.5" }
access_days: 365           # "Durée d'accès aux mises à jour et aux téléchargements"
price: "0,00"              # hors taxes, as in the form
support: dolibarr@netlogic.fr
categories: [ "Modules/Plugins", "Interfaçage externes" ]   # 3 maximum, exact DoliStore labels
keywords: [ prune, autoload ]
```

Per-language file (`en.md`), parsed by front matter and two sections:

```markdown
---
title: Prune libraries for Net Logic modules
short: Collection of libraries for Net Logic modules
---
Long description in Markdown (converted to HTML for the CKEditor field).
```

Not stored in the repository, always computed at release time:

- the **module version**: read from `$this->version` in the module descriptor (same source as the zip name, so they cannot drift);
- the **zip**: the asset of the release, built by `build/makepack.sh` (`module_<name>-<version>.zip`).

## Flow on `release: published`

1. The module workflow builds the zip (existing `package` job) and calls the action with the zip path.
2. `sync.mjs` validates the data **before opening a browser** (see Validation).
3. It logs in with the secrets `DOLISTORE_LOGIN` and `DOLISTORE_PASSWORD` (organisation secrets).
4. It opens `edit-module-product.php?id=<id>` and compares the page with `product.yml`.
5. It fills: version, Dolibarr min/max, PHP min/max, access days, price, support, keywords, the three or fewer categories (other categories are unchecked), the name / short description / long description of every language that has a file, and sends the zip in "Paquet distribué".
6. Screenshots: if the product has **no** additional image and `dolistore/screenshots/` has images, it uploads at most 5 (sorted by file name). It never removes or replaces existing images.
7. With `dry-run: true` it stops here and saves a full-page screenshot and the filled values as workflow artifacts. Otherwise it clicks "Soumettre", then checks the page afterwards (version and zip name displayed in "Gérer mes produits").

Manual `workflow_dispatch` runs default to `dry-run: true`. A real submission only happens on a published release.

## Validation (fails the job before any browser action)

- `product.yml` present, `id` numeric, all required keys present.
- `en.md` exists with a non-empty title, short and long description. Other languages need the same three fields if their file exists.
- At most 3 categories, each one among the labels DoliStore offers (list kept in the shared repository and refreshed with the script in `--list-categories` mode).
- Dolibarr min <= max; PHP versions are `x.y`.
- The version is `x`, `x.y` or `x.y.z` (digits and dots; dates like `2026-09-15` become `2026.09.15`, same rule as the zip name).
- The zip exists and is at most 80 MB (DoliStore limit).
- At most 5 screenshots.

## Error handling

- Login failure, form changed (an expected field is missing), upload refused or post-submission check failed: the job fails with a message naming the step, uploads the screenshot of the page as an artifact, and never retries a submission automatically.
- The password and the session cookie are never printed (masked secrets, no trace/video upload on success).
- Playwright traces are enabled only on failure and kept as artifacts for 7 days.

## Known risks

1. **Automated login is untested.** The form may have a captcha or bot protection, which was not visible while manually logged in. First check is a `dry-run` from GitHub Actions. If blocked, the fallback is to run the same script locally by the maintainer (`node sync.mjs --module <path>`), logged in once by hand with a persisted browser profile. The data layout does not change.
2. **Behaviour of updating an "Actif" product is unknown**: DoliStore may re-validate it. This is why the status is never changed and why the first real submission is done on the pilot and checked by hand.
3. **Terms of service**: DoliStore does not forbid or document automation. Ask the DoliStore team (contactez-nous) before enabling it on every module.
4. **The form is not an API**: field names can change without notice. The script fails closed (missing field = error, nothing submitted).

## Testing

- Unit tests (no network): parsing of `product.yml` / language files, every validation rule, version normalisation, Markdown to HTML.
- Dry-run against the real form with the pilot (prune), reviewed by the maintainer from the artifact screenshot.
- One real submission on prune by the maintainer's decision, then compare the DoliStore page with `dolistore/`.

## Pilot rollout

1. Create `Net-Logic/dolistore-sync` with the script, the action and the unit tests.
2. In prune: add `dolistore/` (`product.yml` with id 646, `en.md`, `fr.md`; texts proposed from the existing DoliStore product, to be corrected by the maintainer), exclude `docs/` from the package, add the workflow job.
3. Dry-run from GitHub, review, one real submission on a release.
4. Roll out to the other existing products one by one (ids: microsoftgraph 647, dolidocusign 845, emailtemplatemgr 807, imagegallery 1029, googlerecaptcha 1246, productsalepricebyproject 378, relancefactureglobale 792, dolitrashcan 846, yousignfordolibarr 659, doliesign 1512).

## Open points to confirm

- The exact DoliStore category labels to use for each module (3 maximum, the most fitting); proposed per module at rollout time, validated by the maintainer.
- Which languages to maintain beyond fr and en (the product form has fr, de, it, es buttons).
- Whether the secrets are created at the organisation level (shared by all module repositories) or per repository.
