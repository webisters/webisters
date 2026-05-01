# Webisters CLI

Webisters is a command line tool for creating and managing Webisters projects.

## Requirements
- PHP `>=8.2`
- Composer 2.x

## Install (Global)
```bash
composer global require webisters/webisters
```

### Windows: enable `webisters` command (recommended)
This adds Composer's global `bin-dir` to your user PATH.

```bash
composer global exec webisters setup
```

Restart your terminal after running setup.

## Create a Project
Preferred (after PATH setup):

```bash
webisters new-app my-app
webisters new-api my-api
webisters new-one my-one
webisters new-site my-site
```

No-PATH fallback (works on any OS):

```bash
composer global exec webisters new-app my-app
```

The CLI will download the project template using `composer create-project` and then ask whether to run `composer install`.

## Commands
- `setup` (Windows): adds Composer global bin-dir to PATH
- `new-app <name>`: create an App project
- `new-api <name>`: create an API project
- `new-one <name>`: create a One project
- `new-site <name>`: create a Static Site project

You can also use the grouped form:

```bash
webisters new app <name>
webisters new api <name>
webisters new one <name>
webisters new site <name>
```

## Support
- Issues: https://github.com/webisters/webisters/issues
- Source: https://github.com/webisters/webisters
- Documentation: https://webisters.com
- Forum: https://github.com/webisters/forum
- Email: support@webisters.com

## License
MIT
