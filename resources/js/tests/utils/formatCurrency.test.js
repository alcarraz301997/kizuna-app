import { describe, it, expect } from 'vitest';
import { formatCurrency } from '@/utils/formatCurrency';

describe('formatCurrency', () => {
    it('formats a whole number with two decimals', () => {
        expect(formatCurrency(1000)).toBe('S/. 1,000.00');
    });

    it('formats a decimal amount', () => {
        expect(formatCurrency(1234.56)).toBe('S/. 1,234.56');
    });

    it('formats zero', () => {
        expect(formatCurrency(0)).toBe('S/. 0.00');
    });

    it('formats a string number', () => {
        expect(formatCurrency('5000')).toBe('S/. 5,000.00');
    });

    it('formats negative amounts', () => {
        expect(formatCurrency(-1000)).toBe('S/. -1,000.00');
    });

    it('formats large amounts with commas', () => {
        expect(formatCurrency(1234567.89)).toBe('S/. 1,234,567.89');
    });
});
