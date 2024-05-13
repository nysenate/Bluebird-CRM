# PHP Git Ignore

This is a maintenance fork of https://github.com/TOGoS/PHPGitIgnore to
provide compatibility with newer versions of PHP.

## Installation

```bash
composer require civicrm/gitignore
```

## Testing

The `Makefile` defines targets for building and testing. If you have GNU Make and PHP, then just run:

```bash
make
```

If you don't have GNU Make and PHP, then you can use `nix-shell` ([Nix package manager](https://nixos.org/download/)), eg

```bash
nix-shell -A php83
make
```

Or you can string together several calls, with alternating versions of PHP:

```bash
## Run the tests with multiple versions of PHP
nix-shell -A php83 --run 'make clean run-unit-tests'
nix-shell -A php82 --run 'make clean run-unit-tests'
nix-shell -A php81 --run 'make clean run-unit-tests'
nix-shell -A php74 --run 'make clean run-unit-tests'
nix-shell -A php70 --run 'make run-unit-tests'
nix-shell -A php56 --run 'make run-unit-tests'
## (Each phpXX should be listed in shell.nix)
```
