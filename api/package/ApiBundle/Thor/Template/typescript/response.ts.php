/* eslint-disable max-len */
<?php if (count($resources)) echo PHP_EOL ?>
<?php echo implode("\n", array_map(fn ($v) => sprintf("import { %s } from '../Resource/%s';", $v, $v), $resources)); ?>
<?php if (count($resources)) echo PHP_EOL ?>

<?php foreach ($data['response'] as $code => $responseData) { ?>
export interface <?php echo ucfirst($data['shortName']); ?>Response {
<?php echo $helper->renderVariables($responseData); ?>

}
<?php } ?>
