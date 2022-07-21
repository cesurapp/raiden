/* eslint-disable no-param-reassign */
/* eslint-disable max-len */
/* eslint-disable @typescript-eslint/no-unused-vars */
/* eslint-disable no-useless-constructor */

import type { AxiosInstance, AxiosRequestConfig, Method } from 'axios';
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
  constructor(private client: AxiosInstance) {}
<?php foreach ($data as $groupedRoutes) { foreach ($groupedRoutes as $route) { ?>

  async <?php echo $route['shortName']; ?>(<?php echo $attrs = $helper->renderAttributes($route); ?>): Promise<<?php echo ucfirst($route['shortName']); ?>Response> {
    return this.rq('<?php echo $route['routerMethod'][0]; ?>', <?php echo  $helper->renderEndpointPath($route['routerPath'], $attrs); ?>, config, <?php echo str_contains($attrs, 'request') ? 'request' : 'null' ?>)
  }
<?php }} ?>

  async rq(method: Method, url: string, config: AxiosRequestConfig = {}, data?: any) {
    config.method = method;
    config.url = url;
    if (data) {
      config.data = data;
    }

    return await this.client.request(config);
  }
}