declare namespace ValidationEngine {}

interface JQuery {
    validationEngine(): JQuery;
    validationEngine(options: any): JQuery;
    validationEngine(action: string, options: any): JQuery;
    validationEngine(action: string, options?: any): JQuery;
    validationEngine(action: string, options?: any): boolean;
}
