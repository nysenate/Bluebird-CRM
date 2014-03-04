<?php
if (opcache_reset() == true) {
  echo 'The PHP opcode cache has been reset at '.date('r');
}
else {
  echo 'The PHP opcode cache is disabled and cannot be reset.';
}
?>
