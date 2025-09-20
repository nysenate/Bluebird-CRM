let
  pins = {
    ## nixpkgs v22.05: Good for php74
    v2205 = import (fetchTarball {
      url = "https://github.com/nixos/nixpkgs/archive/ce6aa13369b667ac2542593170993504932eb836.tar.gz";
      sha256 = "0d643wp3l77hv2pmg2fi7vyxn4rwy0iyr8djcw1h5x72315ck9ik";
    }) {};
    ## nixpkgs v23.11: Good for php83
    v2311 = import (fetchTarball {
      url = "https://github.com/nixos/nixpkgs/archive/057f9aecfb71c4437d2b27d3323df7f93c010b7e.tar.gz";
      sha256 = "1ndiv385w1qyb3b18vw13991fzb9wg4cl21wglk89grsfsnra41k";
    }) {};

    v1803 = import (fetchTarball https://github.com/NixOS/nixpkgs-channels/archive/862fb5215f076833b74c7599fb4e8218d4dfeac2.tar.gz) {};
    v1903 = import (fetchTarball https://github.com/NixOS/nixpkgs-channels/archive/34c7eb7545d155cc5b6f499b23a7cb1c96ab4d59.tar.gz) {};
  };

  makePhpShell = { pkgs, php, composer }: pkgs.mkShell {
    nativeBuildInputs = [
      php
      composer

      ## php.packages.composer ##   <<== Works inv 22.05+ but not earlier

      ## Commonly needed for shells, but not specifically assessed re:PHPGitIgnore
      pkgs.bash-completion
      pkgs.bzip2
      pkgs.coreutils
      pkgs.curl
      pkgs.gnumake
      pkgs.gnutar
      pkgs.patch
      pkgs.unzip
      pkgs.which
      pkgs.zip
    ];
    shellHook = ''
      source ${pkgs.bash-completion}/etc/profile.d/bash_completion.sh
    '';
  };

in {

  php56 = makePhpShell {
    pkgs = pins.v1803;
    php = pins.v1803.php56;
    composer = pins.v1803.php56Packages.composer;
  };

  php70 = makePhpShell {
    pkgs = pins.v1803;
    php = pins.v1803.php70;
    composer = pins.v1803.php70Packages.composer;
  };

  php71 = makePhpShell {
    pkgs = pins.v1903;
    php = pins.v1903.php71;
    composer = pins.v1903.php71Packages.composer;
  };

  php74 = makePhpShell {
    pkgs = pins.v2205;
    php = pins.v2205.php74.buildEnv {
      # extensions = { all, enabled}: with all; enabled++ [opcache ];
      extraConfig = ''
        memory_limit=-1
      '';
    };
    composer = pins.v2205.php74.packages.composer;
  };

  php81 = makePhpShell {
    pkgs = pins.v2311;
    php = pins.v2311.php81.buildEnv {
      # extensions = { all, enabled}: with all; enabled++ [opcache ];
      extraConfig = ''
        memory_limit=-1
      '';
    };
    composer = pins.v2311.php81.packages.composer;
  };

  php82 = makePhpShell {
    pkgs = pins.v2311;
    php = pins.v2311.php82.buildEnv {
      # extensions = { all, enabled}: with all; enabled++ [opcache ];
      extraConfig = ''
        memory_limit=-1
      '';
    };
    composer = pins.v2311.php82.packages.composer;
  };

  php83 = makePhpShell {
    pkgs = pins.v2311;
    php = pins.v2311.php83.buildEnv {
      # extensions = { all, enabled}: with all; enabled++ [opcache ];
      extraConfig = ''
        memory_limit=-1
      '';
    };
    composer = pins.v2311.php83.packages.composer;
  };

}
