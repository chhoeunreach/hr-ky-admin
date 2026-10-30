const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/attendance/monthly.blade.php'), 'utf8');
const start = blade.indexOf('            const initMonthlyTableSorting =');
const end = blade.indexOf('            initMonthlyCardFiltering();', start) + '            initMonthlyCardFiltering();'.length;
const source = blade.slice(start, end);
function setup() {
    const classes = () => {const values = new Set();return {add: value => values.add(value), remove: value => values.delete(value), contains: value => values.has(value)};};
    const button = (dataset) => ({dataset, classList: classes(), icon: {},
        querySelector() {return this.icon;}, addEventListener(type, handler) {this.click = handler;}});
    const name = button({sortColumn: '0', sortType: 'text'});
    const late = button({sortColumn: '1', sortType: 'number', sortDefault: 'desc'});
    const rows = [['Zoe',8],['Amy',2],['Bob',10],['Dara',0]].map(([name, count]) => ({name, count, hidden: false, number: {},
        children: [{dataset: {}, textContent: name}, {dataset: {sortValue: String(count)}}],
        querySelector() {return this.number;}, getAttribute() {return String(this.count);}}));
    const tbody = {querySelectorAll: () => rows, querySelector: () => null,
        appendChild(row) {rows.splice(rows.indexOf(row),1);rows.push(row);}};
    const table = {dataset: {rowStart: '1'}, querySelector: selector => selector === 'tbody' ? tbody : late, querySelectorAll: () => [name, late]};
    const lateCard = button({tableFilter: 'late'});
    vm.runInNewContext(source, {document: {querySelector: () => table, querySelectorAll: () => [lateCard]}, monthlyStatusSortRank: {}, i18n: {}});
    return {rows, name, late, lateCard};
}
test('Late sorts numerically from highest to lowest on its first click', () => {
    const state = setup();state.late.click();
    assert.deepEqual(state.rows.map(row => row.count), [10,8,2,0]);
    assert.equal(state.late.dataset.sortDirection, 'desc');assert.equal(state.late.icon.innerHTML, '&darr;');
    assert.deepEqual(state.rows.map(row => row.number.textContent), [1,2,3,4]);
});
test('Late toggles ascending and returns to descending after another column is used', () => {
    const state = setup();state.late.click();state.late.click();
    assert.deepEqual(state.rows.map(row => row.count), [0,2,8,10]);
    state.name.click();assert.deepEqual(state.rows.map(row => row.name), ['Amy','Bob','Dara','Zoe']);
    state.late.click();assert.deepEqual(state.rows.map(row => row.count), [10,8,2,0]);
});
test('the Late summary card also puts the highest late count first', () => {
    const state = setup();state.lateCard.click();
    assert.deepEqual(state.rows.filter(row => !row.hidden).map(row => row.count), [10,8,2]);
    assert.equal(state.late.dataset.sortDirection, 'desc');
});
