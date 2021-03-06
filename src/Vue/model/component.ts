declare function require(name:string);
import Vue from '{{vuejs}}';
import injector from '{{injector}}';
import {{cName}} from './{{cName}}';
import './{{cName}}.scss';

let name = 'vue-{{cNameLower}}';
let tpl = injector.inject(require('./{{cName}}.html'), name);

let {{cName}}Component = Vue.component(name, {
    data: (f) => {
        return {
            vue: {{cName}}
        }
    },
    beforeMount: (f) => {
        
    },
    template: tpl
});
export default {{cName}}Component;