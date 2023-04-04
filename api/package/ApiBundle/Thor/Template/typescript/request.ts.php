/* eslint-disable max-len */

<?php if ($enums = $helper->renderEnum($data['request'])) {
    echo implode("\n", array_map(static fn($i) => sprintf("import type { %s } from './../Enum/%s';", $i, $i), $enums));
    echo PHP_EOL . PHP_EOL;
} ?>
export type <?php echo ucfirst($data['shortName']); ?>Request = {
<?php echo $helper->renderVariables($data['request']); ?>

}
