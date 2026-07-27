<?php declare(strict_types=1);
/*
 * This file is part of Webisters Command Line Tool.
 *
 * (c) Hafiz Muhammad Moaz <thewebisters@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Webisters\Commands;

use Framework\CLI\CLI;
use Framework\CLI\Command;

/**
 * Class Completion.
 *
 * Prints a shell completion script for the `webisters` CLI. The command
 * names are read from the live console registration, so the generated
 * script never drifts out of sync with the commands that actually exist.
 *
 * @package webisters
 */
class Completion extends Command
{
    protected string $name = 'completion';
    protected string $description = 'Prints a bash or zsh completion script.';
    protected string $usage = 'completion [bash|zsh]';
    protected string $group = 'Diagnostics';

    /**
     * The subtypes accepted by the grouped `new <type>` form.
     *
     * @var array<int, string>
     */
    private const NEW_TYPES = ['app', 'api', 'one', 'site'];

    public function run() : void
    {
        $shell = $this->console->getArgument(0) ?? 'bash';

        if ($shell !== 'bash' && $shell !== 'zsh') {
            \fwrite(\STDERR, 'Unsupported shell: ' . $shell . \PHP_EOL);
            \fwrite(\STDERR, 'Usage: webisters completion [bash|zsh]' . \PHP_EOL);
            return;
        }

        $script = $shell === 'bash'
            ? $this->bashScript($this->commandNames())
            : $this->zshScript($this->commandNames());

        CLI::write($script);
    }

    /**
     * Every registered command name, excluding the completion command
     * itself, sorted for a stable script.
     *
     * @return array<int, string>
     */
    protected function commandNames() : array
    {
        $names = [];

        foreach ($this->console->getCommands() as $name => $command) {
            if ($name === $this->name) {
                continue;
            }

            $names[] = $name;
        }

        \sort($names);

        return $names;
    }

    /**
     * @param array<int, string> $commands
     */
    private function bashScript(array $commands) : string
    {
        $commandList = \implode(' ', $commands);
        $typeList = \implode(' ', self::NEW_TYPES);

        return <<<BASH
            # webisters bash completion
            # Install: webisters completion bash > /etc/bash_completion.d/webisters
            #   or:     webisters completion bash >> ~/.bashrc
            _webisters_complete()
            {
                local cur prev
                cur="\${COMP_WORDS[COMP_CWORD]}"
                prev="\${COMP_WORDS[COMP_CWORD-1]}"

                if [ "\$COMP_CWORD" -eq 1 ]; then
                    COMPREPLY=( \$(compgen -W "{$commandList}" -- "\$cur") )
                    return 0
                fi

                if [ "\$prev" = "new" ]; then
                    COMPREPLY=( \$(compgen -W "{$typeList}" -- "\$cur") )
                    return 0
                fi

                COMPREPLY=()
                return 0
            }
            complete -F _webisters_complete webisters
            BASH;
    }

    /**
     * @param array<int, string> $commands
     */
    private function zshScript(array $commands) : string
    {
        $commandList = \implode(' ', $commands);
        $typeList = \implode(' ', self::NEW_TYPES);

        return <<<ZSH
            # webisters zsh completion
            # Install: webisters completion zsh > "\${fpath[1]}/_webisters"
            #   then restart your shell.
            #compdef webisters
            _webisters()
            {
                local -a commands types
                commands=({$commandList})
                types=({$typeList})

                if (( CURRENT == 2 )); then
                    compadd -- \$commands
                    return
                fi

                if [ "\${words[CURRENT-1]}" = "new" ]; then
                    compadd -- \$types
                    return
                fi
            }
            _webisters "\$@"
            ZSH;
    }
}
