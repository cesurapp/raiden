/* eslint-disable no-param-reassign */
/* eslint-disable max-len */
/* eslint-disable @typescript-eslint/no-unused-vars */
/* eslint-disable no-useless-constructor */

import type { Axios, AxiosRequestConfig } from 'axios';
import { toQueryString } from './flatten';

<?php foreach ($data as $groupedRoutes) { foreach ($groupedRoutes as $route) { ?>
<?php if ($route['response']) { ?>
import type { <?php echo ucfirst($route['shortName']); ?>Response } from './Response/<?php echo ucfirst($route['shortName']); ?>Response';
<?php } ?>
<?php if ($route['request']) { ?>
import type { <?php echo ucfirst($route['shortName']); ?>Request } from './Request/<?php echo ucfirst($route['shortName']); ?>Request';
<?php } ?>
<?php if ($route['query']) { ?>
import type { <?php echo ucfirst($route['shortName']); ?>Query } from './Query/<?php echo ucfirst($route['shortName']); ?>Query';
<?php } ?>
<?php }} ?>

export default class Api {
  constructor(private client: Axios) {}
<?php foreach ($data as $groupedRoutes) { foreach ($groupedRoutes as $route) { ?>

  async <?php echo $route['shortName']; ?>(<?php echo $attrs = $helper->renderAttributes($route); ?>): Promise<<?php echo ucfirst($route['shortName']); ?>Response> {
    config.method = '<?php echo $route['routerMethod'][0]; ?>';
    config.url = <?php echo  $helper->renderEndpointPath($route['routerPath'], $attrs); ?>;
<?php if (str_contains($attrs, 'request')) { ?>
    config.data = request;
<?php } ?>

    const r = await this.client.request(config);
    return r.data as <?php echo ucfirst($route['shortName']); ?>Response;
  }
<?php }} ?>
}