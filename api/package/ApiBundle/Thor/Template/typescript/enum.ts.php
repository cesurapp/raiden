/* eslint-disable max-len */

export enum <?php echo ucfirst($namespace); ?> {
<?php foreach ($data as $enum) { ?>
  <?php echo sprintf("%s = '%s',\n", $enum->name, $enum->value); ?>
<?php } ?>
}
