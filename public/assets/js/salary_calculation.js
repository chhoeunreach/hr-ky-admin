function createEmployeeSalary(percentType, salaryComponents, employeeSalary) {
    return {
        init() {
            // Initialize defaults
            this.payroll_type = employeeSalary?.payroll_type || 'annual';
            this.payment_type = employeeSalary?.payment_type || 'monthly';
            this.salary_base = this.payroll_type == 'hourly';
            this.annual_salary = employeeSalary?.annual_salary ?? '';
            this.hour_rate = employeeSalary?.hour_rate || '';
            this.weekly_hours = employeeSalary?.weekly_hours || '';
            this.monthly_hours = employeeSalary?.monthly_hours || '';
            this.basic_salary_value = employeeSalary?.basic_salary_value ?? 60;
            this.basic_salary_type = employeeSalary?.basic_salary_type || percentType;
            this.monthly_basic_salary = employeeSalary?.monthly_basic_salary || 0;
            this.annual_basic_salary = employeeSalary?.annual_basic_salary || 0;
            this.weekly_basic_salary = employeeSalary?.weekly_basic_salary || 0;
            this.monthly_fixed_allowance = employeeSalary?.monthly_fixed_allowance || 0;
            this.annual_fixed_allowance = employeeSalary?.annual_fixed_allowance || 0;
            this.weekly_fixed_allowance = employeeSalary?.weekly_fixed_allowance || 0;
            this.salary_group_id = employeeSalary?.salary_group_id || '';

            this.updateFields();
            this.calculateSalary();
        },

        payroll_type: 'annual',
        payment_type: 'monthly',
        salary_base: false,
        hour_rate: '',
        weekly_hours: '',
        monthly_hours: '',
        annual_salary: '',
        salary_group_id: '',
        basic_salary_value: 60,
        basic_salary_type: percentType,
        monthly_basic_salary: 0,
        annual_basic_salary: 0,
        weekly_basic_salary: 0,
        monthly_fixed_allowance: 0,
        annual_fixed_allowance: 0,
        weekly_fixed_allowance: 0,
        monthly_total: 0,
        annual_total: 0,
        weekly_total: 0,
        total_weekly_deduction: 0,
        total_monthly_deduction: 0,
        total_annual_deduction: 0,
        net_weekly_salary: 0,
        net_monthly_salary: 0,
        net_annual_salary: 0,

        calculateTotals() {
            this.annual_total = Number(this.annual_salary).toFixed(2);
            this.monthly_total = Number(this.annual_total / 12).toFixed(2);
            this.weekly_total = Number(this.annual_total / 52).toFixed(2);

            let totalDed = this.deductions.reduce((monthly, field) => monthly + Number(field.monthly || 0), 0);
            this.total_monthly_deduction = Number(totalDed).toFixed(2);
            this.total_annual_deduction = Number(this.deductions.reduce((annual, field) => annual + Number(field.annual || 0), 0)).toFixed(2);
            this.total_weekly_deduction = Number(this.total_annual_deduction / 52).toFixed(2);

            this.net_monthly_salary = Number(this.monthly_total - this.total_monthly_deduction).toFixed(2);
            this.net_annual_salary = Number(this.annual_salary - this.total_annual_deduction).toFixed(2);
            this.net_weekly_salary = Number(this.net_annual_salary / 52).toFixed(2);
        },

        updateFields() {
            this.salary_base = this.payroll_type == 'hourly';

            // Only reset fields if they are not already set by employeeSalary
            if (!this.annual_salary && !this.hour_rate && !this.weekly_hours && !this.monthly_hours) {
                if (this.payroll_type == 'annual' && this.payment_type == 'weekly') {
                    this.basic_salary_value = 100;
                    this.monthly_hours = '';
                    this.monthly_basic_salary = 0;
                    this.monthly_fixed_allowance = 0;
                    this.monthly_total = 0;
                    this.total_monthly_deduction = 0;
                    this.net_monthly_salary = 0;
                } else if (this.payroll_type == 'hourly') {
                    this.annual_salary = '';
                    if (this.payment_type == 'weekly') {
                        this.basic_salary_value = 100;
                        this.monthly_hours = '';
                        this.monthly_basic_salary = 0;
                        this.monthly_fixed_allowance = 0;
                        this.monthly_total = 0;
                        this.total_monthly_deduction = 0;
                        this.net_monthly_salary = 0;
                    } else if (this.payment_type == 'monthly') {
                        this.basic_salary_value = 60;
                        this.weekly_hours = '';
                        this.weekly_basic_salary = 0;
                        this.weekly_fixed_allowance = 0;
                        this.weekly_total = 0;
                        this.total_weekly_deduction = 0;
                        this.net_weekly_salary = 0;
                    }
                } else {
                    this.hour_rate = '';
                    this.weekly_hours = '';
                    this.monthly_hours = '';
                    this.weekly_basic_salary = 0;
                    this.weekly_fixed_allowance = 0;
                    this.weekly_total = 0;
                    this.total_weekly_deduction = 0;
                    this.net_weekly_salary = 0;
                }
            }
        },

        calculateAnnualSalary() {
            let annualSalary = 0;
            if (this.payroll_type == 'hourly' || (this.payroll_type == 'annual' && this.payment_type == 'weekly')) {
                if (this.payment_type == 'weekly' && this.hour_rate && this.weekly_hours) {
                    annualSalary = (Number(this.weekly_hours) * Number(this.hour_rate)) * 52;
                    this.weekly_hours = this.weekly_hours;
                } else if (this.payment_type == 'monthly' && this.hour_rate && this.monthly_hours) {
                    annualSalary = (Number(this.monthly_hours) * Number(this.hour_rate)) * 12;
                    this.monthly_hours = this.monthly_hours;
                }
            }
            this.annual_salary = Number(annualSalary).toFixed(2);
            this.calculateSalary();
        },

        calculateSalary() {
            if (this.basic_salary_type == percentType && (this.basic_salary_value < 0 || this.basic_salary_value > 100)) {
                Swal.fire({
                    position: "center",
                    icon: "warning",
                    text: "Basic Salary must be between 0 and 100",
                    showConfirmButton: false,
                    timer: 1500
                });
                this.basic_salary_value = '';
                return;
            }

            if (this.basic_salary_type == percentType) {
                this.annual_basic_salary = Number(this.annual_salary * this.basic_salary_value / 100).toFixed(2);
                this.monthly_basic_salary = Number(this.annual_basic_salary / 12).toFixed(2);
            } else {
                this.monthly_basic_salary = Number(this.basic_salary_value).toFixed(2);
                this.annual_basic_salary = Number(this.basic_salary_value * 12).toFixed(2);
            }

            this.annual_fixed_allowance = Number(this.annual_salary - this.annual_basic_salary).toFixed(2);
            this.monthly_fixed_allowance = Number(this.annual_fixed_allowance / 12).toFixed(2);
            this.weekly_fixed_allowance = Number(this.annual_fixed_allowance / 52).toFixed(2);
            this.weekly_basic_salary = Number(this.annual_basic_salary / 52).toFixed(2);

            let componentData = calculateSalaryComponent(
                salaryComponents,
                this.annual_salary,
                this.annual_basic_salary
            );

            this.incomes = componentData[0];
            this.deductions = componentData[1];
            this.monthly_fixed_allowance = Number(componentData[2]).toFixed(2);
            this.annual_fixed_allowance = Number(componentData[3]).toFixed(2);
            this.weekly_fixed_allowance = Number(componentData[4]).toFixed(2);

            this.calculateTotals();
            const basicInput = this.$el?.querySelector('[name="basic_salary_value"]');
            basicInput?.setCustomValidity(Number(this.annual_fixed_allowance) < 0 ? this.$el.dataset.salaryError : '');
        }
    };
}

function calculateSalaryComponent(salaryComponents, annualSalary, annualBasicSalary) {
    let incomes = [];
    let deductions = [];

    if (salaryComponents && salaryComponents.length > 0) {
        salaryComponents.forEach(component => {
            if (component.status !== undefined && !Number(component.status)) return;
            let extraIncome = 0;
            let extraDeduction = 0;

            if (component.component_type == 'deductions') {
                let annualD = annualComponent(component, annualSalary, annualBasicSalary);
                extraDeduction = Number(annualD / 12).toFixed(2);
                let weeklyDeduction = annualD / 52;
                deductions.push({
                    name: component.name,
                    value_type: component.value_type,
                    annual_component_value: component.annual_component_value,
                    monthly: extraDeduction,
                    annual: Number(annualD).toFixed(2),
                    weekly: Number(weeklyDeduction).toFixed(2),
                });
            }

            if (component.component_type == 'earning') {
                let annualI = annualComponent(component, annualSalary, annualBasicSalary);
                extraIncome = Number(annualI / 12).toFixed(2);
                let weeklyIncome = annualI / 52;
                incomes.push({
                    name: component.name,
                    value_type: component.value_type,
                    annual_component_value: component.annual_component_value,
                    monthly: extraIncome,
                    annual: Number(annualI).toFixed(2),
                    weekly: Number(weeklyIncome).toFixed(2),
                });

            }
        });
    }

    const annualEarnings = (salaryComponents || []).filter(component => component.component_type === 'earning'
        && (component.status === undefined || Number(component.status)))
        .reduce((total, component) => total + annualComponent(component, annualSalary, annualBasicSalary), 0);
    // Derive the remaining allowance from the annual salary to avoid rounding each month first.
    const annualFixedAllowance = Number((Number(annualSalary) - Number(annualBasicSalary) - Number(annualEarnings.toFixed(2))).toFixed(2));
    return [incomes, deductions, annualFixedAllowance / 12, annualFixedAllowance, annualFixedAllowance / 52];
}

function annualComponent(component, annualSalary, annualBasicSalary) {
    const value = Number(component.annual_component_value || 0);
    const amount = component.value_type === 'fixed' ? value
        : component.value_type === 'ctc' ? Number(annualSalary) * value / 100
        : component.value_type === 'basic' ? Number(annualBasicSalary) * value / 100 : 0;
    return amount;
}
