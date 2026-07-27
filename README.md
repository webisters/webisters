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

## Install (Global)

```bash
composer global require webisters/webisters
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

### Restricted networks

If you install behind a proxy, mirror, or other restricted network, make sure Composer can reach Packagist and GitHub before running the global install. The global installer and the `new-*` commands both shell out to Composer, so anything that makes `composer` work on your network makes Webisters work too.

**Behind a proxy**

- Set proxy variables for the current shell, for example:

  ```bash
  export HTTP_PROXY="http://user:pass@proxy.example.com:8080"
  export HTTPS_PROXY="http://user:pass@proxy.example.com:8080"
  export NO_PROXY="localhost,127.0.0.1"
  ```

- Or configure the proxy in Composer itself so it persists across shells:

  ```bash
  composer config -g http-proxy  http://proxy.example.com:8080
  composer config -g https-proxy http://proxy.example.com:8080
  ```

- If your proxy performs TLS inspection, point Composer/PHP at your corporate CA bundle:

  ```bash
  composer config -g cafile /path/to/corporate-ca-bundle.pem
  ```

**Using a package mirror**

```bash
composer config -g repos.packagist composer https://packagist.mirror.example.com
```

**Fully offline / air-gapped environments**

1. On a machine that *can* reach the network, warm the Composer cache and install once:

   ```bash
   composer global require webisters/webisters
   ```

2. Copy the Composer home directory to the restricted machine. Find it with `composer config -g home`.
3. On the restricted machine:

   ```bash
   export COMPOSER_CACHE_DIR="/path/to/copied/cache"
   composer global require webisters/webisters --prefer-dist
   ```

**Creating projects offline**

```bash
webisters new-app my-app --no-install
```

Then run `composer install` yourself once dependencies are reachable. Use `--with-install` to force the install step when the network is available.

**Diagnosing failures**

- Run `webisters check` to confirm PHP, required extensions, and Composer are detected.
- Rerun with high verbosity to see the underlying network error:

  ```bash
  composer global require webisters/webisters -vvv
  ```

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

The CLI downloads the project template using `composer create-project` and then asks whether to run `composer install`.

Use `--no-install` to skip the install step or `--with-install` to run it without prompting.
Use `--dry-run` to preview the scaffolded actions without writing files.

You can also use the grouped form:

```bash
webisters new app <name>
webisters new api <name>
webisters new one <name>
webisters new site <name>
```

## Commands

### Project scaffolding

| Command | Description |
|---|---|
| `new-app <name>` | Create an App (full MVC) project |
| `new-api <name>` | Create an API project |
| `new-one <name>` | Create a One (single-file) project |
| `new-site <name>` | Create a Static Site project |

### Code generators (run inside a project)

| Command | Description |
|---|---|
| `make:controller <Name>` | Create a controller in `app/Controllers/` |
| `make:model <Name>` | Create a model in `app/Models/` |
| `make:view <path/name>` | Create a view in `app/Views/` |

Nested paths are supported with `/` separators, e.g. `make:controller Admin/Users`.

### Routing

| Command | Description |
|---|---|
| `route:list` | Display all registered routes (run inside a project) |

### Diagnostics

| Command | Description |
|---|---|
| `check` / `doctor` | Report PHP, extension, and Composer status |
| `list` | Show all available commands |

### Environment

| Command | Description |
|---|---|
| `setup` | (Windows) Add Composer global bin-dir to user PATH |
| `update` | Update the global Webisters CLI via Composer |

### Shell completion

```bash
# bash
webisters completion bash > /etc/bash_completion.d/webisters
# or append to ~/.bashrc:
webisters completion bash >> ~/.bashrc

# zsh
webisters completion zsh > "${fpath[1]}/_webisters"
# then restart your shell
```

## Support

- Issues: https://github.com/webisters/webisters/issues
- Source: https://github.com/webisters/webisters
- Documentation: https://webisters.com
- Forum: https://github.com/webisters/forum
- Email: support@webisters.com

## License

MIT
