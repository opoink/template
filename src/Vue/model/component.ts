declare function require(name:string);
import Vue from '{{vuejs}}';
import injector from '{{injector}}';
import {{cName}} from './{{cName}}';
/** uncomment to use component scss */
// import './{{cName}}.scss';

let name = 'vue-{{cNameLower}}';
let tpl = injector.inject(require('./{{cName}}.html'), name);

let {{cName}}Component = Vue.component(name, {
    data: (f) => {
        return {
            vue: {{cName}}
        }
    },
    beforeMount: (f) => {
        {{cName}}.init(); /** call the init method in component service */
    },
	/** uncomment to use if needed */
	// beforeRouteEnter (to, from, next) {
	// 	{{cName}}.init(); /** call the init method in component service */
	// 	next();
	// }
	// mounted: () => {  
    // },
    // props: [],
    template: tpl
});
export default {{cName}}Component;