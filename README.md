# Webisters CLI

Webisters is a command line tool for creating and managing Webisters projects.

## Requirements
- PHP `>=8.2`
- Composer 2.x

### PHP extensions
Enable these **before** running `composer global require webisters/webisters` or any `webisters new-*` command. Missing extensions are the most common cause of install failures.

- Framework runtime: `ext-intl`, `ext-sodium`, `ext-gd`, `ext-mysqli`, `ext-curl`, `ext-fileinfo`, `ext-json`, `ext-simplexml`, `ext-dom`, `ext-libxml`
- Composer download/extract (needed during install): `ext-zip`, `ext-openssl`
  - Without `zip`, Composer falls back to slow source downloads and shows `Failed to download ... skipping`.
  - Without `openssl`, Composer cannot fetch packages over HTTPS.

> `json`, `fileinfo`, `openssl`, `dom`, and `simplexml` ship with most PHP builds; `intl`, `sodium`, `gd`, `mysqli`, `curl`, and `zip` usually need to be enabled manually.

#### Quick enablement notes
- Windows: locate your `php.ini` with `php --ini`, uncomment the matching `extension=...` lines (`intl`, `sodium`, `gd`, `mysqli`, `curl`, `openssl`, `zip`), and restart your terminal/web server.
- Ubuntu/Debian (example):

```bash
sudo apt update
sudo apt install php-intl php-sodium php-gd php-mysqli php-curl php-zip
sudo systemctl restart php8.2-fpm # or restart your PHP service
```
- Verify with `php -m` (the printed list should include the extensions above).

## Included libraries
The Webisters framework repository includes many reusable libraries located under the `libraries/` directory. Enable the PHP extensions listed below before installing to avoid installation or runtime errors.

- autoload
- cache
- cli
- coding-standard
- config
- crypto
- database
- database-extra
- date
- debug
- dev-commands
- email
- events
- factories
- front
- helpers
- http
- http-client
- image
- language
- log
- minify
- mvc
- pagination
- routing
- session
- testing
- theme
- validation

## Extensions required by libraries
These extensions are declared in the individual libraries' `composer.json` files. Make sure the following extensions are enabled on your system before installing the framework or any packages.

- `ext-intl` - required by: `autoload`, `date`, `language`, `validation`
- `ext-sodium` - required by: `crypto`
- `ext-gd` - required by: `image`
- `ext-json` - required by: `cache`, `date`, `http`, `http-client`, `image`, `mvc`, `pagination`
- `ext-fileinfo` - required by: `email`, `http`, `http-client`, `validation`
- `ext-curl` - required by: `http-client`
- `ext-mysqli` - required by: `database`, `mvc`
- `ext-simplexml` - required by: `config`
- `ext-dom`, `ext-libxml` - required by: `minify`

If any of these extensions are missing you may see errors during `composer install` or when running the framework. See the quick enablement notes above for common platform commands, or consult your OS / PHP distribution documentation for how to enable extensions.

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
- `list`: show available `new-*` and `make:*` generators
- `setup` (Windows): adds Composer global bin-dir to PATH
- `update`: update the global Webisters CLI with Composer
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
