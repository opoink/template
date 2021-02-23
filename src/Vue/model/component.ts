declare function require(name:string);
import Vue from '{{vuejs}}';
import {{cName}} from './{{cName}}';

Vue.component('{{cName}}', {
    data: (f) => {
        return {
            vue: {{cName}}
        }
    },
    beforeMount: (f) => {
        
    },
    template: require('./{{cName}}.html')
});