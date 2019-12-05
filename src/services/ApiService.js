
import * as serverConfig  from './../../_env/main.config.json';


import $store from './../store/index';

import axios from 'axios'

export default {
    axios: axios,
    apiUrl:  serverConfig.default.apiUrl,
    enableAuth: serverConfig.default.apiUrl,

    get(endpoint) {
        return this.submit('get', endpoint);
    },

    post(endpoint, data) {
        return this.submit('post', endpoint,data);
    },

    put(endpoint, data) {
        return this.submit('put', endpoint,data);
    },

    patch(endpoint, data) {
        return this.submit('patch', endpoint,data);
    },

    delete(endpoint,data) {
        return this.submit('delete', endpoint,data);
    },

    submit(request_type, endpoint, data, nested_resolve, nested_reject) {
        var self = this;
        if(typeof data != 'object')
        {
            data = {};
        }

        data.token = this.getCookie('token');

       // var originalData = JSON.parse(JSON.stringify(data));

        return new Promise((resolve, reject) => {

            if(typeof nested_resolve == 'function') 	resolve = nested_resolve;
            if(typeof nested_reject == 'function') 	reject = nested_reject;

            if(request_type == 'get')
                var call = axios.get(this.apiUrl+endpoint);
            else if(request_type == 'delete')
            {
                var call = axios.delete(this.apiUrl+endpoint);
            }
            else
                var call = axios[request_type](self.apiUrl+endpoint, data);

            call.then(response => {
                resolve(response.data);
            })
                .catch(error => {

                    console.log('API ERROR', error.response);

                    if(error.response.status == 401)
                    {
                        $store.dispatch('user/showLogin');
                    }
                    else
                    {
                        reject(error.response);
                    }

                });
        });
    },
    getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }
}
