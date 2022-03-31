/* eslint-disable prettier/prettier */
/* eslint-disable max-len */

import type { AxiosResponse } from 'axios';

<?php foreach ($data['response'] as $code => $responseData) { ?>
interface <?php echo ucfirst($data['shortName']); ?>Response<?php echo $code; ?> {
<?php echo $helper->renderVariables($responseData); ?>

}
<?php } ?>
export type <?php echo ucfirst($data['shortName']); ?>Response = AxiosResponse<<?php echo implode('|', array_map(fn($code) => ucfirst($data['shortName']).'Response'.$code, array_keys($data['response']))); ?>>;
