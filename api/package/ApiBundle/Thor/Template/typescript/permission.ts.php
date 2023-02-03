/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

export namespace <?php echo ucfirst($namespace); ?> {
<?php foreach ($data as $panelName => $packagePerms) { ?>
<?php foreach ($packagePerms as $packageName => $enums) { ?>
  export enum <?php echo ucfirst(strtolower($panelName)) . ucfirst(str_replace('Permission', '', $packageName)); ?> {
  <?php foreach ($enums as $enum) { ?>
  <?php echo sprintf("%s = '%s',\n", str_replace(['ROLE_', strtoupper(str_replace('Permission', '', $packageName)) . '_'], ['', ''], $enum), $enum); ?>
  <?php } ?>
}
<?php } ?>
<?php } ?>
}