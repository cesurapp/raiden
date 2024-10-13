/* eslint-disable no-param-reassign */
/* eslint-disable max-len */
/* eslint-disable @typescript-eslint/no-unused-vars */
/* eslint-disable no-useless-constructor */

import type { AxiosInstance } from 'axios';
import Auth from './auth'
import Main from './main'
import Admin from './admin'

export interface ApiInstance {
    auth: Auth;
    main: Main;
    admin: Admin;
}

export interface ApiConstructor {
    new (client: AxiosInstance): ApiInstance;
}

export default class Api implements ApiInstance {
    public auth: Auth;
    public main: Main;
    public admin: Admin;

    constructor(client: AxiosInstance) {
        this.auth = new Auth(client);
        this.main = new Main(client);
        this.admin = new Admin(client);
    }
}