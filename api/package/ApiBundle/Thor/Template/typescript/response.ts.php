/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

<?php foreach ($data['response'] as $code => $responseData) { ?>
export interface <?php echo ucfirst($data['shortName']); ?>Response {
<?php echo $helper->renderVariables($responseData); ?>

}
<?php } ?>
