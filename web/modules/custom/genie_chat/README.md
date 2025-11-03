# Genie Chat Module

## Installation

1. Enable the module: `drush en genie_chat`
2. Configure at: `/admin/config/services/genie-chat`

## Translation Support

To enable translation for bot title and system message:

1. Enable required modules:
   ```
   drush en language config_translation
   ```

2. Add additional languages:
   - Go to `/admin/config/regional/language`
   - Add your desired languages

3. Clear cache:
   ```
   drush cr
   ```

4. Configure translations:
   - Go to `/admin/config/services/genie-chat`
   - Click the "Translate" tab
   - Add translations for each language

## Permissions

Users need "Translate configuration" permission to access translation interface.

## Parameters

All chatbubble parameters are configurable:
- Bot Name
- Bot Title (translatable)
- System Message (translatable)
- Base Logo URL - URL to the site logo displayed in the chat widget
- Primary Color
- Secondary Color
- Text Color
- Secondary Text Color
- Chat Location
- Token