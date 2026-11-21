const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const viewPath = path.join(__dirname, '../../resources/views/admin/payroll-queue');
const page = fs.readFileSync(path.join(viewPath, 'index.blade.php'), 'utf8');
const modal = fs.readFileSync(path.join(viewPath, 'partials/disburse-modal.blade.php'), 'utf8');
const feather = require('../../public/assets/vendors/feather-icons/feather.js');
const openModal = page.slice(
    page.indexOf('    function openDisburseModal('),
    page.indexOf("    document.getElementById('btnQuickUpdateBonus')"),
);

function setup(response) {
    const elements = new Map(Array.from(modal.matchAll(/id="([^"]+)"/g), ([, id]) => [
        id, {style: {}, dataset: {}, options: [], appendChild(option) { this.options.push(option); }},
    ]));
    const alerts = [];
    const model = {visible: false, show() { this.visible = true; }, hide() { this.visible = false; }};
    const context = vm.createContext({
        document: {getElementById: id => elements.get(id), createElement: () => ({})},
        window: {},
        alert: message => alerts.push(message),
        fetch: async (url, options) => {
            assert.equal(url, '/hrs/admin/payroll-queue/ticket/42/breakdown');
            assert.equal(options.headers.Accept, 'application/json');
            return response;
        },
        paymentUrl: (action, ticketId) => `/hrs/admin/payroll-queue/ticket/${ticketId}/${action}`,
        disburseModal: model,
        loadingSpinner: elements.get('modalLoadingSpinner'),
        contentContainer: elements.get('modalContentContainer'),
        btnConfirm: elements.get('btnConfirmDisburse'),
    });
    vm.runInContext(openModal, context);
    return {open: () => context.openDisburseModal(42), elements, alerts, model};
}

test('payroll queue page uses icons supported by its bundled Feather library', () => {
    for (const view of [page, modal]) {
        for (const [, name] of view.matchAll(/data-feather="([^"]+)"/g)) {
            assert.equal(typeof feather.icons[name]?.toSvg, 'function', name);
        }
    }
});

test('opening a valid payroll ticket leaves the details visible and payment enabled', async () => {
    const dialog = setup({
        ok: true,
        json: async () => ({
            success: true,
            data: {
                employee: {name: 'Employee'},
                ticket: {ticket_number: 'A-001', session: {month: '2026-11'}},
                payslip: {bonus: 0},
                deductions: {},
                earnings: {},
                net_salary: 125,
                payment_methods: [{id: 2, name: 'Cash'}],
            },
        }),
    });
    await dialog.open();
    assert.deepEqual(dialog.alerts, []);
    assert.equal(dialog.model.visible, true);
    assert.equal(dialog.elements.get('modalNetSalaryDisplay').textContent, '$125.00');
    assert.equal(dialog.elements.get('modalContentContainer').style.display, 'block');
    assert.equal(dialog.elements.get('btnConfirmDisburse').disabled, false);
});

test('an expired session shows the sign-in message and keeps payment disabled', async () => {
    const dialog = setup({redirected: true, json: async () => assert.fail('Login HTML is not payroll JSON')});
    await dialog.open();
    assert.match(dialog.alerts[0], /សូមចូលគណនីម្តងទៀត/);
    assert.equal(dialog.model.visible, false);
    assert.equal(dialog.elements.get('btnConfirmDisburse').disabled, true);
});

test('a rejected ticket shows the reason returned by Laravel', async () => {
    const message = 'This ticket is no longer active at a payroll counter.';
    const dialog = setup({ok: false, status: 400, json: async () => ({success: false, message})});
    await dialog.open();
    assert.deepEqual(dialog.alerts, [message]);
    assert.equal(dialog.model.visible, false);
    assert.equal(dialog.elements.get('btnConfirmDisburse').disabled, true);
});
