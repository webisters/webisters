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

### Restricted networks
If you install behind a proxy, mirror, or other restricted network, make sure Composer can reach Packagist and GitHub before running the global install. The global installer and the `new-*` commands both shell out to Composer, so anything that makes `composer` work on your network makes Webisters work too.

**Behind a proxy**

- Set proxy variables for the current shell, for example:

  ```bash
  export HTTP_PROXY="http://user:pass@proxy.example.com:8080"
  export HTTPS_PROXY="http://user:pass@proxy.example.com:8080"
  export NO_PROXY="localhost,127.0.0.1"   # hosts that must bypass the proxy
  ```

- Or configure the proxy in Composer itself so it persists across shells:

  ```bash
  composer config -g http-proxy  http://proxy.example.com:8080
  composer config -g https-proxy http://proxy.example.com:8080
  ```

- If your proxy performs TLS inspection, point Composer/PHP at your corporate CA bundle so certificate verification succeeds:

  ```bash
  composer config -g cafile /path/to/corporate-ca-bundle.pem
  ```

**Using a package mirror**

If Packagist itself is blocked but a mirror is available, route Composer through it:

```bash
composer config -g repos.packagist composer https://packagist.mirror.example.com
```

**Fully offline / air-gapped environments**

1. On a machine that *can* reach the network, warm the Composer cache and install once:

   ```bash
   composer global require webisters/webisters
   ```

2. Copy the Composer home directory (contains the cache and the global `vendor/`) to the restricted machine. Find it with `composer config -g home` (commonly `~/.composer` or `~/.config/composer`).
3. On the restricted machine, run Composer with the cache treated as authoritative so it never reaches the network:

   ```bash
   export COMPOSER_CACHE_DIR="/path/to/copied/cache"
   composer global require webisters/webisters --prefer-dist
   ```

**Creating projects offline**

The `new-*` commands download the project template with
`composer create-project` and then optionally run `composer install`. In a
restricted environment, scaffold without touching the network for the
dependency install step by skipping it:

```bash
webisters new-app my-app --no-install
```

Then run `composer install` yourself once dependencies are reachable (or from
a warmed cache as above). Use `--with-install` to force the install step when
you know the network is available.

**Diagnosing failures**

- Run `webisters check` to confirm PHP, required extensions, and Composer are detected.
- If downloads still fail, rerun the failing command with high verbosity to see the underlying network error:

  ```bash
  composer global require webisters/webisters -vvv
  ```

### Windows: enable `webisters` command (recommended)
This adds Composer's global `bin-dir` to your user PATH.

```bash
composer global exec webisters setup
```

Setup writes to your **user** PATH, so open a **new** terminal afterward. Command Prompt,
PowerShell, and Git Bash all read the updated user PATH, but any session that was already
open will not see the change until you reopen it. In the new terminal, verify with
`webisters --version` (or `where webisters`).

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

Use `--no-install` to skip the install step or `--with-install` to run it without prompting.
Use `--dry-run` to preview the scaffolded actions without writing files.

## Commands
- `check` / `doctor`: report PHP, extension, and Composer status
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
