/* eslint-disable max-len */

export namespace <?php echo ucfirst($namespace); ?> {
<?php foreach ($data as $panelName => $packagePerms) { ?>
<?php foreach ($packagePerms as $packageName => $enums) { ?>
  export enum <?php echo ucfirst(strtolower($panelName)).ucfirst(str_replace('Permission', '', $packageName)); ?> {
  <?php foreach ($enums as $enum) {
      $process = explode('_', $enum); ?>
  <?php echo sprintf("%s = '%s',\n", end($process), $enum); ?>
  <?php } ?>
}
<?php } ?>
<?php } ?>
}