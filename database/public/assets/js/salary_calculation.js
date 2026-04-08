
function createEmployeeSalary(percentType, salaryComponents, employeeSalary) {
    return {
        init() {

            if (employeeSalary)
            {
                this.annual_salary = employeeSalary.annual_salary;
                let monthSalary = this.annual_salary / 12;
                this.monthly_basic_salary = employeeSalary.monthly_basic_salary;
                this.monthly_fixed_allowance = monthSalary - this.monthly_basic_salary;
                this.annual_fixed_allowance = this.monthly_fixed_allowance * 12;
                this.basic_salary_value = employeeSalary.basic_salary_value;
                this.basic_salary_type = employeeSalary.basic_salary_type;
                this.annual_basic_salary = employeeSalary.annual_basic_salary;

                let componentData = calculateSalaryComponent(salaryComponents, this.monthly_fixed_allowance, this.annual_fixed_allowance, this.annual_salary, this.monthly_basic_salary);

                this.incomes = componentData[0];
                this.deductions = componentData[1];


                this.monthly_fixed_allowance = Number(componentData[2]).toFixed(2);
                this.annual_fixed_allowance = Number(componentData[3]).toFixed(2);


                let totalInc = this.incomes.reduce((monthly, field) => monthly + Number(field.monthly), 0);

                this.monthly_total = Number(this.monthly_basic_salary) + Number(this.monthly_fixed_allowance) + Number(totalInc);

                this.annual_total = Number(this.monthly_total * 12).toFixed(2);

                let totalDed = this.deductions.reduce((monthly, field) => monthly + Number(field.monthly), 0);

                this.total_monthly_deduction = Number(totalDed).toFixed(2);

                let totalAnDed = this.deductions.reduce((annual, field) => annual + Number(field.annual), 0);

                this.total_annual_deduction = Number(totalAnDed).toFixed(2);


                let netMonthlySalary = Number(this.monthly_total) - Number(this.total_monthly_deduction);

                this.net_monthly_salary = Number(netMonthlySalary).toFixed(2);
                let netAnnualSalary = Number(this.annual_salary) - Number(this.total_annual_deduction);

                this.net_annual_salary = Number(netAnnualSalary).toFixed(2);

                this.salary_group_id = employeeSalary.salary_group_id;

                this.hour_rate = employeeSalary.hour_rate;
                this.weekly_hour = (this.annual_salary / 52) / this.hour_rate;
                this.weekly_hour = Number(this.weekly_hour).toFixed(2);
                this.salary_base = '';

            } else {

                let componentData = calculateSalaryComponent(salaryComponents, 0, 0, 0, 0);

                this.incomes = componentData[0];
                this.deductions = componentData[1];
                this.monthly_fixed_allowance = componentData[2];
                this.annual_fixed_allowance = componentData[3];

                this.hour_rate = '';
                this.weekly_hour = '';
                this.salary_base = '';
                this.annual_salary = '';
                this.basic_salary_value = 60;
                this.basic_salary_type = percentType;
                this.monthly_basic_salary = 0;
                this.annual_basic_salary = 0;
                this.monthly_total = 0;
                this.annual_total = 0;
                this.total_monthly_deduction = 0;
                this.total_annual_deduction = 0;
                this.net_monthly_salary = 0;
                this.net_annual_salary = 0;
                this.salary_group_id = '';
            }

        },
        incomes: [],
        deductions: [],
        salary_base: '',
        hour_rate: '',
        weekly_hour: '',
        annual_salary: '',
        salary_group_id: '',
        basic_salary_value: 60,
        basic_salary_type: percentType,
        monthly_basic_salary: 0,
        annual_basic_salary: 0,
        monthly_fixed_allowance: 0,
        annual_fixed_allowance: 0,
        monthly_total: 0,
        annual_total: 0,
        total_monthly_deduction: 0,
        total_annual_deduction: 0,
        net_monthly_salary: 0,
        net_annual_salary: 0,


        calculateSalary() {

            if( this.basic_salary_type == percentType && (this.basic_salary_value < 0 || this.basic_salary_value >100)){
                Swal.fire({
                    position: "center",
                    icon: "warning",
                    text: "Basic Salary must be between 0 and 100",
                    showConfirmButton: false,
                    timer: 1500
                });
                this.basic_salary_value = '';
            }

            let monthlySalary = this.annual_salary / 12;
            if (this.basic_salary_type == percentType) {
                this.monthly_basic_salary = (this.basic_salary_value/ 100) * monthlySalary;
                this.monthly_basic_salary = Number(this.monthly_basic_salary).toFixed(2);
                this.annual_basic_salary = (this.basic_salary_value == 100) ? this.annual_salary : Number(this.monthly_basic_salary * 12).toFixed(2);
                this.monthly_fixed_allowance = (this.basic_salary_value == 100) ? 0 : monthlySalary - this.monthly_basic_salary;
                this.annual_fixed_allowance = this.monthly_fixed_allowance * 12;
            } else {
                this.monthly_basic_salary = this.basic_salary_value;
                this.annual_basic_salary =  this.basic_salary_value * 12;
                this.monthly_fixed_allowance =   monthlySalary - this.basic_salary_value;
                this.annual_fixed_allowance =this.monthly_fixed_allowance * 12;
            }


            let componentData = calculateSalaryComponent(salaryComponents, this.monthly_fixed_allowance, this.annual_fixed_allowance, this.annual_salary, this.monthly_basic_salary);


            this.incomes = componentData[0];
            this.deductions = componentData[1];

            this.monthly_fixed_allowance = Number(componentData[2]).toFixed(2);
            this.annual_fixed_allowance = Number(componentData[3]).toFixed(2);


            let totalMonthlyDeduction = () => {
                const deductionsArray = this.deductions && this.deductions.length ? this.deductions : [];
                return deductionsArray.reduce((monthly, field) => monthly + Number(field.monthly || 0), 0);
            };
            this.total_monthly_deduction = Number(totalMonthlyDeduction()).toFixed(2);

            let totalAnnualDeduction = () => {
                const deductionsArray = this.deductions && this.deductions.length ? this.deductions : [];
                return deductionsArray.reduce((annual, field) => annual + Number(field.annual || 0), 0);
            };

            this.total_annual_deduction = Number(totalAnnualDeduction()).toFixed(2);

            // calculate monthly total income

            this.monthly_total = Number(monthlySalary).toFixed(2);


            this.annual_total = Number((Number(monthlySalary) *12)).toFixed(2);

            this.monthly_basic_salary = Number(this.monthly_basic_salary).toFixed(2);
            this.annual_basic_salary = Number(this.annual_basic_salary).toFixed(2);
            this.net_monthly_salary = Number(monthlySalary) - Number(totalMonthlyDeduction());
            this.net_monthly_salary = Number(this.net_monthly_salary).toFixed(2);
            this.net_annual_salary = Number(this.annual_total) - Number(totalAnnualDeduction());
            this.net_annual_salary = Number(this.net_annual_salary).toFixed(2);

        },
        calculateAnnualSalary(){
           let annualSalary = (Number(this.weekly_hour) * Number(this.hour_rate)) * 52;
            this.annual_salary = Number(annualSalary).toFixed(2);
            this.calculateSalary();
        }

    }
}


function calculateComponent(valueType, annualValue, annualSalary, basicSalary)
{
    let componentValue = 0;
    if(valueType == 'fixed')
    {
        componentValue = annualValue/12;
    }else if(valueType == 'ctc')
    {
        componentValue = ((annualValue / 100) * annualSalary)/12;
    }else if(valueType == 'basic')
    {
        componentValue = (annualValue / 100) * basicSalary;
    }else{
        componentValue = 0;
    }
    return Number(componentValue).toFixed(2);
}

function calculateSalaryComponent(salaryComponents, monthlyFixedAllowance, annualFixedAllowance, annualSalary, basicSalary) {
    let incomes = [];
    let deductions = [];

    if (salaryComponents && salaryComponents.length > 0) {
        salaryComponents.forEach(component => {
            let extraIncome = 0;
            let extraDeduction = 0;

            if (component.component_type == 'deductions') {
                extraDeduction = calculateComponent(component.value_type, component.annual_component_value, annualSalary, basicSalary);

                let annualD = extraDeduction*12;
                deductions.push({
                    name: component.name,
                    value_type: component.value_type,
                    annual_component_value: component.annual_component_value,
                    monthly: extraDeduction,
                    annual: Number(annualD).toFixed(2),
                });
            }

            if (component.component_type == 'earning') {
                extraIncome = calculateComponent(component.value_type, component.annual_component_value, annualSalary, basicSalary);
                let annualI = extraIncome * 12;
                incomes.push({
                    name: component.name,
                    value_type: component.value_type,
                    annual_component_value: component.annual_component_value,
                    monthly: extraIncome,
                    annual: Number(annualI).toFixed(2),
                });

                // Use a temporary variable for updates0

                let tempMonthlyFixedAllowance = monthlyFixedAllowance - extraIncome;
                let tempAnnualFixedAllowance = tempMonthlyFixedAllowance * 12;

                // Update after calculating extra income
                monthlyFixedAllowance = tempMonthlyFixedAllowance < 0 ? 0 : tempMonthlyFixedAllowance;
                annualFixedAllowance = tempAnnualFixedAllowance < 0 ? 0 : tempAnnualFixedAllowance;
            }
        });
    }

    return [incomes, deductions, monthlyFixedAllowance, annualFixedAllowance];
}






