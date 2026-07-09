/**
 * Format a number as Peruvian Soles currency.
 * @param {number|string} amount
 * @returns {string} e.g. "S/. 1,234.56"
 */
export function formatCurrency(amount) {
    return 'S/. ' + Number(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}
