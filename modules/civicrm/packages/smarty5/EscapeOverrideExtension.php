<?php

  use Smarty\Extension\Base;

   class EscapeOverrideExtension extends Base {

  public function getModifierCompiler(string $modifier): ?\Smarty\Compile\Modifier\ModifierCompilerInterface {
    require_once 'EscapeModifierCompilerOverride.php';
    switch ($modifier) {
      case 'escape': return new EscapeModifierCompilerOverride();
    }

    return NULL;
  }

}
