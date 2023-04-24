/* eslint-disable max-len */
<?php if (count($resources)) echo PHP_EOL ?>
<?php echo implode("\n", array_map(fn ($v) => sprintf("import { %s } from '../Resource/%s';", $v, $v), $resources)); ?>
<?php if (count($resources)) echo PHP_EOL ?>

export type <?php echo ucfirst($namespace); ?> = {
<?php echo $helper->renderVariables($data); ?>

}