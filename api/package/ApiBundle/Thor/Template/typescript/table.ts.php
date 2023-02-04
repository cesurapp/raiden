/* eslint-disable max-len */

export default [
<?php foreach ($data['table'] as $key => $items) { ?>
  { name: '<?php echo $key ?>', <?php echo trim(str_replace("\n ", '', $helper->renderVariables($items['table']))); ?> },
<?php } ?>
];