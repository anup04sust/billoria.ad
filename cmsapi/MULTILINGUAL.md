# Multilingual Configuration

## Overview

The Billoria project is configured as a **multilingual website** with support for:

- **Primary Language**: English (en)
- **Secondary Language**: Bengali / বাংলা (bn)

## Enabled Modules

The following Drupal core multilingual modules are enabled:

1. **Language** - Base language management
2. **Content Translation** - Translate content entities (nodes, taxonomy terms, etc.)
3. **Configuration Translation** - Translate site configuration
4. **Interface Translation (locale)** - Translate UI strings and admin interface

## Language Configuration

### URL Structure

Language detection uses **URL path prefix** method:

| Language | Code | URL Pattern | Example |
|----------|------|-------------|---------|
| English (Default) | en | `/` | `https://billoria-ad.ddev.site/` |
| Bengali | bn | `/bn` | `https://billoria-ad.ddev.site/bn` |

### Examples:
```
Homepage (English): https://billoria-ad.ddev.site/
Homepage (Bengali):  https://billoria-ad.ddev.site/bn

Article (English):   https://billoria-ad.ddev.site/article/my-article
Article (Bengali):   https://billoria-ad.ddev.site/bn/article/amar-protibedon

API (English):       https://billoria-ad-api.ddev.site/jsonapi/node/article
API (Bengali):       https://billoria-ad-api.ddev.site/bn/jsonapi/node/article
```

## Translation Status

### Core & Contrib Modules
- 510 translations added
- 48 translations updated
- 39 configuration objects translated

### Modules with Bengali translations:
- Drupal Core
- Admin Toolbar
- Pathauto
- Token

## How to Use

### For Content Editors

#### Translating Content
1. Edit a content item (node, taxonomy term, etc.)
2. Go to the **Translate** tab
3. Click **Add** next to Bengali (বাংলা)
4. Fill in the Bengali translation
5. Save

#### Creating Multilingual Content
- Always create content in English first (default language)
- Then add Bengali translation via the Translate tab
- Both versions will be indexed by JSON:API

### For Developers

#### Enable Translation for Content Types
```bash
# Enable translation for Article content type
ddev drush config:set language.content_settings.node.article third_party_settings.content_translation.enabled true -y

# Enable translation for custom content type
ddev drush config:set language.content_settings.node.MY_TYPE third_party_settings.content_translation.enabled true -y
```

#### Access Translated Content via API

**JSON:API** automatically handles languages:

```bash
# Get English articles
curl https://billoria-ad-api.ddev.site/jsonapi/node/article

# Get Bengali articles
curl https://billoria-ad-api.ddev.site/bn/jsonapi/node/article

# Get specific article in Bengali
curl https://billoria-ad-api.ddev.site/bn/jsonapi/node/article/{uuid}
```

**Filter by language:**
```bash
curl "https://billoria-ad-api.ddev.site/jsonapi/node/article?filter[langcode][value]=bn"
```

#### Frontend Integration

**Next.js with Language Detection:**
```typescript
// app/[lang]/page.tsx
export async function generateStaticParams() {
  return [
    { lang: 'en' },
    { lang: 'bn' },
  ]
}

export default async function Page({ params }: { params: { lang: string } }) {
  const apiUrl = params.lang === 'bn'
    ? 'https://billoria-ad-api.ddev.site/bn/jsonapi/node/article'
    : 'https://billoria-ad-api.ddev.site/jsonapi/node/article'

  const response = await fetch(apiUrl)
  const data = await response.json()

  return <ArticleList articles={data.data} />
}
```

## Managing Languages

### Add a New Language
```bash
# Add Hindi (hi)
ddev drush language:add hi

# Add Spanish (es)
ddev drush language:add es
```

### Check Configured Languages
```bash
ddev drush language:info
```

### Export Translatable Strings
```bash
# Export POT file for translation
ddev drush locale:export bn > translations_bn.po
```

### Import Translations
```bash
# Import PO file
ddev drush locale:import bn translations_bn.po
```

## Configuration Files

Language configuration is stored in:
- `web/sites/default/files/sync/language.entity.*.yml`
- `web/sites/default/files/sync/language.negotiation.yml`
- `web/sites/default/files/sync/language.types.yml`

## Admin Interface

### Configure Languages
- **URL**: `/admin/config/regional/language`
- Add, edit, or remove languages
- Set default language
- Configure language detection methods

### Configure Content Translation
- **URL**: `/admin/config/regional/content-language`
- Enable translation for content types
- Configure which fields are translatable

### Translate Interface
- **URL**: `/admin/config/regional/translate`
- Search and translate UI strings
- Import/export translation files

### Translation Dashboard
- **URL**: `/admin/config/regional/translate`
- View translation coverage
- Filter by language, module, or string

## Best Practices

### Content Strategy
1. **Always create content in English first** (default language)
2. Add Bengali translations afterward
3. Keep URL aliases language-specific using Pathauto
4. Translate taxonomy terms used in content
5. Translate menu items for proper navigation

### Frontend Development
1. Use language prefixes in URLs (`/`, `/bn`)
2. Implement language switcher in header
3. Store user's language preference
4. Use appropriate fonts for Bengali (like Noto Sans Bengali)
5. Consider RTL support if needed in future

### API Development
1. Always specify language in API calls
2. Handle missing translations gracefully (fallback to English)
3. Include language in API responses
4. Use `Accept-Language` header when applicable

## Troubleshooting

### Translation Not Showing
1. Check if content type has translation enabled
2. Verify the field is marked as translatable
3. Clear Drupal cache: `ddev drush cr`
4. Re-index content if using search

### Interface Not Translated
1. Check if translations are imported: `ddev drush locale:check`
2. Update translations: `ddev drush locale:update`
3. Clear cache: `ddev drush cr`

### API Returns Wrong Language
1. Verify URL path prefix (`/bn`)
2. Check `Accept-Language` header
3. Confirm language negotiation settings
4. Clear cache and test

## Future Enhancements

Potential additions:
- [ ] Add more languages (Hindi, Urdu, etc.)
- [ ] Implement translation workflows
- [ ] Add translation management tools (TMGMT module)
- [ ] Set up automatic translation services
- [ ] Configure language-specific media
- [ ] Add Bengali language input tools

## Resources

- [Drupal Multilingual Guide](https://www.drupal.org/docs/multilingual-guide)
- [Content Translation Documentation](https://www.drupal.org/docs/8/core/modules/content-translation)
- [JSON:API Language Negotiation](https://www.drupal.org/docs/core-modules-and-themes/core-modules/jsonapi-module/api-overview)
- [Bengali Language Support](https://localize.drupal.org/translate/languages/bn)

## Command Reference

```bash
# View languages
ddev drush language:info

# Add language
ddev drush language:add <langcode>

# Check for translation updates
ddev drush locale:check

# Update translations
ddev drush locale:update

# Export translations
ddev drush locale:export <langcode> > file.po

# Import translations
ddev drush locale:import <langcode> file.po

# Enable content type translation
ddev drush config:set language.content_settings.node.<type> third_party_settings.content_translation.enabled true -y

# Clear cache
ddev drush cr
```

---

**Note**: This project uses Drupal's native multilingual system. All translations are managed within Drupal and exposed via JSON:API for the Next.js frontend.
