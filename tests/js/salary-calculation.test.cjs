const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/salary_calculation.js'), 'utf8');

function setup(values = {}, components = []) {
    const context = {Swal: {fire() {}}};
    vm.runInNewContext(source, context);
    const salary = context.createEmployeeSalary('percent', components, {
        payroll_type: 'annual', payment_type: 'monthly', annual_salary: 1000,
        basic_salary_type: 'percent', basic_salary_value: 80, ...values,
    });
    salary.$el = {dataset: {salaryError: 'Components exceed total'},
        querySelector: () => ({setCustomValidity(message) {salary.error = message;}})};
    salary.init();
    return salary;
}

test('setup keeps annual amounts authoritative when monthly amounts need rounding', () => {
    const salary = setup();
    assert.equal(Number(salary.annual_basic_salary), 800);
    assert.equal(Number(salary.monthly_basic_salary), 66.67);
    assert.equal(Number(salary.annual_fixed_allowance), 200);
    assert.equal(Number(salary.annual_total), 1000);
});

test('earning components reduce allowance and inactive components are excluded', () => {
    const salary = setup({}, [
        {name: 'Benefit', component_type: 'earning', value_type: 'fixed', annual_component_value: 10, status: 1},
        {name: 'Bonus', component_type: 'earning', value_type: 'ctc', annual_component_value: 5, status: 1},
        {name: 'Inactive', component_type: 'earning', value_type: 'fixed', annual_component_value: 500, status: 0},
    ]);
    assert.equal(Number(salary.annual_fixed_allowance), 140);
    assert.equal(salary.incomes.length, 2);
    assert.equal(Number(salary.annual_total), 1000);
});

test('components above salary show the shortfall and block submission', () => {
    const salary = setup({}, [{name: 'Benefit', component_type: 'earning', value_type: 'fixed', annual_component_value: 300}]);
    assert.equal(Number(salary.annual_fixed_allowance), -100);
    assert.equal(salary.error, 'Components exceed total');
    salary.annual_salary = 2000;
    salary.calculateSalary();
    assert.equal(salary.error, '');
});

test('restored zero basic salary remains zero', () => {
    const salary = setup({basic_salary_value: 0});
    assert.equal(Number(salary.basic_salary_value), 0);
    assert.equal(Number(salary.annual_basic_salary), 0);
    assert.equal(Number(salary.annual_fixed_allowance), 1000);
});

test('weekly hourly setup derives the annual total from rate and hours', () => {
    const salary = setup({payroll_type: 'hourly', payment_type: 'weekly', hour_rate: 5, weekly_hours: 40, basic_salary_value: 100});
    salary.calculateAnnualSalary();
    assert.equal(Number(salary.annual_total), 10400);
    assert.equal(Number(salary.weekly_basic_salary), 200);
    assert.equal(Number(salary.weekly_fixed_allowance), 0);
});
