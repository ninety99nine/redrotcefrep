export const RATE_TYPES = {
    FLAT: 'flat',
    PERCENTAGE: 'percentage'
};

export const TIP_TYPES = {
    NONE: { type: 'none', value: 'none' },
    FLAT: { type: 'flat', value: 'specify' },
    PERCENTAGE: (value) => ({ type: 'percentage', value })
};