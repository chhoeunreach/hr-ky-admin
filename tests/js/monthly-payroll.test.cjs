const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/monthly-payroll.js'), 'utf8');
const flush = () => new Promise(setImmediate);
function setup(failed = false) {
    const handlers = {}, requests = [], historyCalls = [], timers = [];
    const fields = ['month','branch_id','department_id','q','status','per_page'].map(name => ({name, value: ({month:'2026-10',branch_id:'1',per_page:'25'})[name] || '',
        addEventListener(type, handler) {handlers[name+'.'+type] = handler;}}));
    fields.forEach(field => {fields[field.name] = field;});
    fields.department_id.options = [{value:'',dataset:{}},{value:'1',dataset:{branch:'1'}},{value:'2',dataset:{branch:'2'}}];
    const form = {action:'/admin/monthly-payroll',elements:fields,dataset:{defaultMonth:'2026-10'},
        addEventListener(type, handler) {handlers['form.'+type] = handler;}, requestSubmit() {handlers['form.submit']({preventDefault() {}});}};
    const error = {};
    const block = label => ({label, attributes:{}, scroller:{scrollLeft:90}, querySelector() {return this.scroller;},
        setAttribute(key,value) {this.attributes[key]=value;}, replaceWith(next) {root=next;}});
    let root = block('original');
    const page = {dataset:{error:'Refresh failed'},querySelector:()=>error,addEventListener(type, handler) {handlers['page.'+type] = handler;}};
    const location = {href:'https://hr.test/admin/monthly-payroll',origin:'https://hr.test'};
    vm.runInNewContext(source, {
        document:{querySelector:()=>page,getElementById:id=>id==='monthlyPayrollFilters'?form:root,
            addEventListener(type,handler) {handlers['document.'+type]=handler;}},
        window:{addEventListener(type,handler) {handlers['window.'+type]=handler;}},
        location, history:{pushState(_state,_title,url) {historyCalls.push(url);location.href=url;}}, URL,URLSearchParams,AbortController,
        FormData:class {constructor() {return fields.map(field=>[field.name,field.value]);}},
        DOMParser:class {parseFromString() {return {getElementById:()=>block('updated')};}},
        fetch:async (url,options)=>{requests.push({url,options});return {ok:!failed,text:async()=>'<div></div>'};},
        setTimeout:callback=>{timers.push(callback);return timers.length;},clearTimeout() {},
    });
    return {handlers,requests,historyCalls,timers,fields,error,form,location,root:()=>root};
}
test('filters and page size refresh only the monthly payroll results', async () => {
    const state=setup(); let prevented=false;
    state.handlers['form.submit']({preventDefault() {prevented=true;}}); await flush();
    assert.equal(prevented,true);
    assert.equal(state.root().label,'updated');
    assert.equal(state.requests[0].options.headers['X-Monthly-Payroll-Fragment'],'1');
    assert.equal(new URL(state.requests[0].url).searchParams.get('month'),'2026-10');
    assert.equal(state.root().scroller.scrollLeft,90);
    state.fields.per_page.value='10'; state.handlers['per_page.change'](); await flush();
    assert.equal(new URL(state.requests[1].url).searchParams.get('per_page'),'10');
});
test('export links use the current filter controls', () => {
    const state=setup(); state.fields.q.value='ky20';
    const link={dataset:{monthlyExport:'excel'}};
    state.handlers['page.click']({target:{closest:selector=>selector.includes('export')?link:null}});
    const url=new URL(link.href);
    assert.equal(url.searchParams.get('q'),'ky20');
    assert.equal(url.searchParams.get('export'),'excel');
    assert.equal(url.searchParams.get('month'),'2026-10');
});
test('branch selection hides unrelated departments', () => {
    const state=setup();
    assert.equal(state.fields.department_id.options[2].disabled,true);
    state.fields.branch_id.value='2'; state.handlers['branch_id.change']();
    assert.equal(state.fields.department_id.options[1].disabled,true);
    assert.equal(state.fields.department_id.options[2].disabled,false);
});
test('failed requests keep the current report and show an error', async () => {
    const state=setup(true);
    state.form.requestSubmit(); await flush();
    assert.equal(state.root().label,'original');
    assert.equal(state.error.textContent,'Refresh failed');
    assert.equal(state.root().attributes['aria-busy'],'false');
});
test('Back restores filters without adding a new history entry', async () => {
    const state=setup();state.location.href='https://hr.test/admin/monthly-payroll?month=2026-09&per_page=10&q=Previous';
    state.handlers['window.popstate'](); await flush();
    assert.equal(state.fields.month.value,'2026-09');
    assert.equal(state.fields.q.value,'Previous');
    assert.equal(state.historyCalls.length,0);
});
